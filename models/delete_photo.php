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
$photo_id = isset($body["photo_id"]) ? (int)$body["photo_id"] : null;
if (!$photo_id) {
    http_response_code(400);
    exit(json_encode(["status" => "ERROR", "message" => "Липсва photo_id."]));
}

try {
    $db = new DB();
    $connection = $db->getConnection();

    $sel = $connection->prepare("SELECT uploader_id, image_dir FROM photos WHERE id = :id");
    $sel->execute(["id" => $photo_id]);
    $photo = $sel->fetch(PDO::FETCH_ASSOC);

    if (!$photo) {
        http_response_code(404);
        exit(json_encode(["status" => "ERROR", "message" => "Снимката не съществува."]));
    }

    if ((int)$photo["uploader_id"] !== (int)$user_id) {
        http_response_code(403);
        exit(json_encode(["status" => "ERROR", "message" => "Може да триеш само свои снимки."]));
    }

    $del = $connection->prepare("DELETE FROM photos WHERE id = :id");
    $del->execute(["id" => $photo_id]);

    $abs = __DIR__ . "/../" . $photo["image_dir"];
    if (file_exists($abs) && strpos(realpath($abs), realpath(__DIR__ . "/uploads")) === 0) {
        @unlink($abs);
    }

    http_response_code(200);
    echo json_encode(["status" => "SUCCESS", "message" => "Снимката е изтрита."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "ERROR", "message" => "Грешка при изтриване: " . $e->getMessage()]);
}
?>
