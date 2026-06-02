<?php
session_start();
require_once("../database/connect.php");
header('Content-Type: application/json');

try {
    $db = new DB();
    $connection = $db->getConnection();

    $stmt = $connection->prepare(
        "SELECT c.id, c.name, c.type, c.description, u.fullname AS owner_name,
                (SELECT COUNT(*) FROM club_members cm WHERE cm.club_id = c.id) AS member_count
         FROM clubs c
         LEFT JOIN users u ON u.id = c.owner_id
         ORDER BY c.type, c.name"
    );
    $stmt->execute();
    $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode(["status" => "SUCCESS", "clubs" => $clubs]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "ERROR", "message" => "Грешка при четене на клубовете: " . $e->getMessage()]);
}
?>
