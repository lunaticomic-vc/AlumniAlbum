<?php
session_start();
require_once("../database/connect.php");
require_once("./check_JSON_validity.php");
header('Content-Type: application/json');

$validator_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
if (!$validator_id) {
    http_response_code(401);
    exit(json_encode(["status" => "ERROR", "message" => "Влезте в системата, за да валидирате."]));
}

$data = file_get_contents("php://input");
if (strlen($data) <= 0 || !check_json($data)) {
    http_response_code(400);
    exit(json_encode(["status" => "ERROR", "message" => "Невалиден JSON формат!"]));
}

$body = json_decode($data, true);
$alumni_id      = isset($body["alumni_id"]) ? (int)$body["alumni_id"] : null;
$evidence_text  = isset($body["evidence_text"]) ? trim($body["evidence_text"]) : null;
$evidence_photo = isset($body["evidence_photo"]) ? trim($body["evidence_photo"]) : null;
$status         = isset($body["status"]) ? trim($body["status"]) : 'approved';
$note           = isset($body["note"]) ? trim($body["note"]) : null;

if (!$alumni_id || !in_array($status, ['pending','approved','rejected'])) {
    http_response_code(400);
    exit(json_encode(["status" => "ERROR", "message" => "Невалидни данни."]));
}

try {
    $db = new DB();
    $connection = $db->getConnection();

    $stmt = $connection->prepare(
        "INSERT INTO validations (alumni_id, validator_id, evidence_text, evidence_photo, status, note)
         VALUES (:alumni, :validator, :text, :photo, :status, :note)"
    );
    $stmt->execute([
        "alumni"    => $alumni_id,
        "validator" => $validator_id,
        "text"      => $evidence_text,
        "photo"     => $evidence_photo,
        "status"    => $status,
        "note"      => $note
    ]);

    if ($status === 'approved') {
        $count_stmt = $connection->prepare(
            "SELECT COUNT(*) FROM validations WHERE alumni_id = :a AND status = 'approved'"
        );
        $count_stmt->execute(["a" => $alumni_id]);
        $approved = (int)$count_stmt->fetchColumn();

        if ($approved >= 2) {
            $upd = $connection->prepare("UPDATE users SET validated = 1 WHERE id = :id");
            $upd->execute(["id" => $alumni_id]);
        }
    }

    http_response_code(200);
    echo json_encode(["status" => "SUCCESS", "message" => "Записано."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "ERROR", "message" => "Грешка при запис: " . $e->getMessage()]);
}
?>
