<?php
session_start();
require_once("../database/connect.php");
require_once("./check_JSON_validity.php");
header('Content-Type: application/json');

$user_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
if (!$user_id) {
    http_response_code(401);
    exit(json_encode(["status" => "ERROR", "message" => "Влезте в системата, за да създадете клуб."]));
}

$data = file_get_contents("php://input");
if (strlen($data) <= 0 || !check_json($data)) {
    http_response_code(400);
    exit(json_encode(["status" => "ERROR", "message" => "Невалиден JSON формат!"]));
}

$body = json_decode($data, true);
$name        = isset($body["name"]) ? trim($body["name"]) : null;
$type        = isset($body["type"]) ? trim($body["type"]) : null;
$description = isset($body["description"]) ? trim($body["description"]) : null;

$allowed_types = ['specialty', 'stream', 'year', 'teacher', 'degree'];
if (!$name || !$type || !in_array($type, $allowed_types)) {
    http_response_code(400);
    exit(json_encode(["status" => "ERROR", "message" => "Невалидни данни за клуб."]));
}

try {
    $db = new DB();
    $connection = $db->getConnection();

    $stmt = $connection->prepare(
        "INSERT INTO clubs (name, type, description, owner_id) VALUES (:name, :type, :description, :owner)"
    );
    $stmt->execute([
        "name" => $name,
        "type" => $type,
        "description" => $description,
        "owner" => $user_id
    ]);
    $club_id = $connection->lastInsertId();

    $stmt = $connection->prepare("INSERT INTO club_members (club_id, user_id) VALUES (:club, :user)");
    $stmt->execute(["club" => $club_id, "user" => $user_id]);

    http_response_code(200);
    echo json_encode(["status" => "SUCCESS", "message" => "Клубът е създаден.", "club_id" => $club_id]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "ERROR", "message" => "Грешка при създаване на клуб: " . $e->getMessage()]);
}
?>
