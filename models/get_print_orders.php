<?php
session_start();
require_once("../database/connect.php");
header('Content-Type: application/json');

$user_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
if (!$user_id) {
    http_response_code(401);
    exit(json_encode(["status" => "ERROR", "message" => "Влезте в системата."]));
}

try {
    $db = new DB();
    $connection = $db->getConnection();

    $stmt = $connection->prepare(
        "SELECT id, product_type, photo_ids, quantity, note, status, created_at
         FROM print_orders
         WHERE alumni_id = :a
         ORDER BY created_at DESC"
    );
    $stmt->execute(["a" => $user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode(["status" => "SUCCESS", "orders" => $orders]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "ERROR", "message" => "Грешка при четене: " . $e->getMessage()]);
}
?>
