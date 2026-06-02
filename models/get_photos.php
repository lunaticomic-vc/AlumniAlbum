<?php
session_start();
require_once("../database/connect.php");
header('Content-Type: application/json');

$alumni_id  = isset($_GET['alumni_id']) && $_GET['alumni_id'] !== '' ? (int)$_GET['alumni_id'] : null;
$session_id = isset($_GET['session_id']) && $_GET['session_id'] !== '' ? (int)$_GET['session_id'] : null;

try {
    $db = new DB();
    $connection = $db->getConnection();

    $sql    = "SELECT p.*, u.fullname AS uploader_name
               FROM photos p
               LEFT JOIN users u ON u.id = p.uploader_id";
    $where  = [];
    $params = [];

    if ($alumni_id)  { $where[] = "p.alumni_id = :alumni";  $params["alumni"]  = $alumni_id; }
    if ($session_id) { $where[] = "p.session_id = :session"; $params["session"] = $session_id; }
    if (count($where) > 0) { $sql .= " WHERE " . implode(" AND ", $where); }
    $sql .= " ORDER BY p.uploaded_at DESC";

    $stmt = $connection->prepare($sql);
    $stmt->execute($params);
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode(["status" => "SUCCESS", "photos" => $photos]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "ERROR", "message" => "Грешка при четене на снимки: " . $e->getMessage()]);
}
?>
