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
$product_type = isset($body["product_type"]) ? trim($body["product_type"]) : null;
$photo_ids    = isset($body["photo_ids"]) ? $body["photo_ids"] : [];
$quantity     = isset($body["quantity"]) ? (int)$body["quantity"] : 1;
$note         = isset($body["note"]) ? trim($body["note"]) : null;

$allowed = ['album','postcard','calendar','mug','bridge_cards'];
if (!in_array($product_type, $allowed) || $quantity < 1) {
    http_response_code(400);
    exit(json_encode(["status" => "ERROR", "message" => "Невалидни данни за поръчка."]));
}

try {
    $db = new DB();
    $connection = $db->getConnection();

    $stmt = $connection->prepare(
        "INSERT INTO print_orders (alumni_id, product_type, photo_ids, quantity, note)
         VALUES (:alumni, :product_type, :photo_ids, :quantity, :note)"
    );
    $stmt->execute([
        "alumni"       => $user_id,
        "product_type" => $product_type,
        "photo_ids"    => json_encode(array_values((array)$photo_ids)),
        "quantity"     => $quantity,
        "note"         => $note
    ]);

    http_response_code(200);
    echo json_encode(["status" => "SUCCESS", "message" => "Заявката за печат е приета.", "order_id" => $connection->lastInsertId()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "ERROR", "message" => "Грешка при запис: " . $e->getMessage()]);
}
?>
