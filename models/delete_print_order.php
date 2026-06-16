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
$order_id = isset($body["order_id"]) ? (int)$body["order_id"] : null;
if (!$order_id) {
    http_response_code(400);
    exit(json_encode(["status" => "ERROR", "message" => "Липсва order_id."]));
}

try {
    $db = new DB();
    $connection = $db->getConnection();

    $sel = $connection->prepare("SELECT alumni_id FROM print_orders WHERE id = :id");
    $sel->execute(["id" => $order_id]);
    $order = $sel->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        exit(json_encode(["status" => "ERROR", "message" => "Поръчката не съществува."]));
    }

    if ((int)$order["alumni_id"] !== (int)$user_id) {
        http_response_code(403);
        exit(json_encode(["status" => "ERROR", "message" => "Може да триеш само свои поръчки."]));
    }

    $del = $connection->prepare("DELETE FROM print_orders WHERE id = :id");
    $del->execute(["id" => $order_id]);

    http_response_code(200);
    echo json_encode(["status" => "SUCCESS", "message" => "Поръчката е изтрита."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "ERROR", "message" => "Грешка при изтриване: " . $e->getMessage()]);
}
?>
