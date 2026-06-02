<?php
session_start();
require_once("../database/connect.php");
header('Content-Type: application/json');

$alumni_id = isset($_GET['alumni_id']) && $_GET['alumni_id'] !== '' ? (int)$_GET['alumni_id'] : null;
if (!$alumni_id) {
    http_response_code(400);
    exit(json_encode(["status" => "ERROR", "message" => "Липсва alumni_id."]));
}

try {
    $db = new DB();
    $connection = $db->getConnection();

    $stmt = $connection->prepare(
        "SELECT v.*, u.fullname AS validator_name, u.is_teacher
         FROM validations v
         INNER JOIN users u ON u.id = v.validator_id
         WHERE v.alumni_id = :a
         ORDER BY v.created_at DESC"
    );
    $stmt->execute(["a" => $alumni_id]);
    $validations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode(["status" => "SUCCESS", "validations" => $validations]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "ERROR", "message" => "Грешка при четене: " . $e->getMessage()]);
}
?>
