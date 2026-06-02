<?php
require_once("../database/connect.php");
require_once("./check_JSON_validity.php");

$data = file_get_contents("php://input");

if (strlen($data) > 0 && check_json($data)) {
    $user_data = json_decode($data, true);
} else {
    http_response_code(400);
    exit(json_encode(["status" => "ERROR", "message" => "Невалиден JSON формат!"]));
}

$fullname        = $user_data["fullname"];
$fn              = $user_data["fn"];
$username        = $user_data["username"];
$email           = $user_data["email"];
$password        = $user_data["password"];
$repeat_password = $user_data["repeat_password"];
$specialty       = isset($user_data["specialty"]) ? $user_data["specialty"] : null;
$stream          = isset($user_data["stream"]) ? $user_data["stream"] : null;
$graduation_year = isset($user_data["graduation_year"]) && $user_data["graduation_year"] !== "" ? (int)$user_data["graduation_year"] : null;
$degree          = isset($user_data["degree"]) ? $user_data["degree"] : 'bachelor';

if ($password !== $repeat_password) {
    http_response_code(400);
    exit(json_encode(["status" => "ERROR", "message" => "Паролите не съвпадат!"]));
}

try {
    $db = new DB();
    $connection = $db->getConnection();

    $search = "SELECT id FROM users WHERE email = :email OR fn = :fn";
    $stmt = $connection->prepare($search);
    $stmt->execute(["email" => $email, "fn" => $fn]);

    if ($stmt->rowCount() != 0) {
        http_response_code(400);
        exit(json_encode(["status" => "ERROR", "message" => "Потребител с такъв имейл или факултетен номер вече съществува!"]));
    }
} catch (PDOException $e) {
    http_response_code(500);
    exit(json_encode(["status" => "ERROR", "message" => "Неочаквана грешка в сървъра!"]));
}

try {
    $insert = "INSERT INTO users (fullname, fn, username, email, password, specialty, stream, graduation_year, degree)
               VALUES (:fullname, :fn, :username, :email, :password, :specialty, :stream, :graduation_year, :degree)";

    $stmt = $connection->prepare($insert);
    $ok = $stmt->execute([
        "fullname"        => $fullname,
        "fn"              => $fn,
        "username"        => $username,
        "email"           => $email,
        "password"        => $password,
        "specialty"       => $specialty,
        "stream"          => $stream,
        "graduation_year" => $graduation_year,
        "degree"          => $degree
    ]);

    if ($ok) {
        $user_id = $connection->lastInsertId();
        session_start();
        $_SESSION["user"] = [
            "id"        => $user_id,
            "fullname"  => $fullname,
            "username"  => $username,
            "email"     => $email,
            "is_teacher"=> 0,
            "validated" => 0
        ];
        setcookie("email", $email, time() + 3600, "/");

        http_response_code(200);
        exit(json_encode(["status" => "SUCCESS", "message" => "Успешна регистрация!"]));
    } else {
        http_response_code(500);
        exit(json_encode(["status" => "ERROR", "message" => "Неочаквана грешка в сървъра!"]));
    }
} catch (PDOException $e) {
    http_response_code(500);
    exit(json_encode(["status" => "ERROR", "message" => "Неочаквана грешка в сървъра!"]));
}
?>
