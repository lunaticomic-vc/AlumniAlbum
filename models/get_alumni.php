<?php
session_start();
require_once("../database/connect.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit(json_encode(["status" => "ERROR", "message" => "Метод не е позволен."]));
}

$specialty       = isset($_GET['specialty']) ? trim($_GET['specialty']) : null;
$stream          = isset($_GET['stream']) ? trim($_GET['stream']) : null;
$graduation_year = isset($_GET['graduation_year']) && $_GET['graduation_year'] !== '' ? (int)$_GET['graduation_year'] : null;
$degree          = isset($_GET['degree']) ? trim($_GET['degree']) : null;
$club_id         = isset($_GET['club_id']) && $_GET['club_id'] !== '' ? (int)$_GET['club_id'] : null;
$q               = isset($_GET['q']) ? trim($_GET['q']) : null;

try {
    $db = new DB();
    $connection = $db->getConnection();

    $sql = "SELECT u.id, u.fullname, u.fn, u.email, u.specialty, u.stream, u.graduation_year, u.degree,
                   u.position, u.location, u.profile_pic, u.validated, u.visibility
            FROM users u";
    $params = [];
    $where  = ["u.is_teacher = 0"];

    if ($club_id !== null) {
        $sql .= " INNER JOIN club_members cm ON cm.user_id = u.id";
        $where[] = "cm.club_id = :club_id";
        $params["club_id"] = $club_id;
    }
    if ($specialty)       { $where[] = "u.specialty = :specialty"; $params["specialty"] = $specialty; }
    if ($stream)          { $where[] = "u.stream = :stream"; $params["stream"] = $stream; }
    if ($graduation_year) { $where[] = "u.graduation_year = :gy"; $params["gy"] = $graduation_year; }
    if ($degree)          { $where[] = "u.degree = :degree"; $params["degree"] = $degree; }
    if ($q)               { $where[] = "(u.fullname LIKE :q OR u.fn LIKE :q)"; $params["q"] = "%$q%"; }

    if (count($where) > 0) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY u.graduation_year DESC, u.fullname ASC";

    $stmt = $connection->prepare($sql);
    $stmt->execute($params);
    $alumni = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode(["status" => "SUCCESS", "alumni" => $alumni]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "ERROR", "message" => "Грешка при четене от базата: " . $e->getMessage()]);
}
?>
