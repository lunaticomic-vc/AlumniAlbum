<?php
require_once("../database/connect.php");

function login($user) {
    try {
        $db = new DB();
        $connection = $db->getConnection();

        $select = "SELECT id, fullname, username, email, password, is_teacher, validated
                   FROM users
                   WHERE email = :email";

        $stmt = $connection->prepare($select);
        $stmt->execute(["email" => $user["email"]]);

        if ($stmt->rowCount() == 0) {
            return ["status" => "ERROR", "message" => "Не съществува потребител с посочения имейл!", "code" => 400];
        }

        $db_user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user["password"] !== $db_user["password"]) {
            return ["status" => "ERROR", "message" => "Невалидна парола!", "code" => 400];
        }
    } catch (PDOException $e) {
        return ["status" => "ERROR", "message" => "Неочаквана грешка в сървъра!", "code" => 500];
    }

    session_start();
    $_SESSION["user"] = $db_user;

    setcookie("email", $user["email"], time() + 3600, "/");

    return ["status" => "SUCCESS", "message" => "Успешно влизане в системата!", "code" => 200];
}
?>
