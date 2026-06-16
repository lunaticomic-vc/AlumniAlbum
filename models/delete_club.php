<?php
session_start();
require_once("../database/connect.php");
require_once("./check_JSON_validity.php");
header('Content-Type: application/json');

$user_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
if (!$user_id) {
    http_response_code(401);
    exit(json_encode(["status" => "ERROR", "message" => "Влезте в системата."]));
}

$data = file_get_contents("php://input");
if (strlen($data) <= 0 || !check_json($data)) {
    http_response_code(400);
    exit(json_encode(["status" => "ERROR", "message" => "Невалиден JSON формат!"]));
}

$body = json_decode($data, true);
$club_id = isset($body["club_id"]) ? (int)$body["club_id"] : null;
if (!$club_id) {
    http_response_code(400);
    exit(json_encode(["status" => "ERROR", "message" => "Липсва club_id."]));
}

try {
    $db = new DB();
    $connection = $db->getConnection();

    $sel = $connection->prepare("SELECT owner_id FROM clubs WHERE id = :id");
    $sel->execute(["id" => $club_id]);
    $club = $sel->fetch(PDO::FETCH_ASSOC);

    if (!$club) {
        http_response_code(404);
        exit(json_encode(["status" => "ERROR", "message" => "Клубът не съществува."]));
    }

    if ($club["owner_id"] !== null && (int)$club["owner_id"] !== (int)$user_id) {
        http_response_code(403);
        exit(json_encode(["status" => "ERROR", "message" => "Може да триеш само свои клубове."]));
    }

    $del_members = $connection->prepare("DELETE FROM club_members WHERE club_id = :id");
    $del_members->execute(["id" => $club_id]);

    $del = $connection->prepare("DELETE FROM clubs WHERE id = :id");
    $del->execute(["id" => $club_id]);

    http_response_code(200);
    echo json_encode(["status" => "SUCCESS", "message" => "Клубът е изтрит."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "ERROR", "message" => "Грешка при изтриване: " . $e->getMessage()]);
}
?>
