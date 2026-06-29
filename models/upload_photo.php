<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

session_start();
require_once("../database/connect.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(["status" => "ERROR", "message" => "Методът не е позволен."]));
}

$uploader_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
if (!$uploader_id) {
    http_response_code(401);
    exit(json_encode(["status" => "ERROR", "message" => "Няма активна сесия. Влезте в системата."]));
}

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] != 0) {
    http_response_code(400);
    exit(json_encode(["status" => "ERROR", "message" => "Не е избрано изображение."]));
}

$target_dir = __DIR__ . "/uploads/";
if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

$safe_name   = uniqid("photo_", true) . "_" . basename($_FILES["photo"]["name"]);
$target_file = $target_dir . $safe_name;
$image_dir   = "models/uploads/" . $safe_name;

if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
    http_response_code(400);
    exit(json_encode(["status" => "ERROR", "message" => "Грешка при записването на файла."]));
}

$alumni_id  = isset($_POST['alumni_id']) && $_POST['alumni_id'] !== '' ? (int)$_POST['alumni_id'] : null;
$session_id = isset($_POST['session_id']) && $_POST['session_id'] !== '' ? (int)$_POST['session_id'] : null;
$caption    = isset($_POST['caption']) ? $_POST['caption'] : null;
$source     = isset($_POST['source']) ? $_POST['source'] : 'personal';
$date_taken = isset($_POST['date_taken']) && $_POST['date_taken'] !== '' ? $_POST['date_taken'] : null;
$name       = $_FILES['photo']['name'];
$size       = $_FILES['photo']['size'];
$type       = $_FILES['photo']['type'];

try {
    $db = new DB();
    $connection = $db->getConnection();

    $stmt = $connection->prepare(
        "INSERT INTO photos (uploader_id, alumni_id, session_id, image_dir, name, caption, source, size, type, date_taken)
         VALUES (:uploader, :alumni, :session, :image_dir, :name, :caption, :source, :size, :type, :date_taken)"
    );
    $stmt->execute([
        "uploader"   => $uploader_id,
        "alumni"     => $alumni_id,
        "session"    => $session_id,
        "image_dir"  => $image_dir,
        "name"       => $name,
        "caption"    => $caption,
        "source"     => $source,
        "size"       => $size,
        "type"       => $type,
        "date_taken" => $date_taken
    ]);

    http_response_code(200);
    echo json_encode([
        "status" => "SUCCESS",
        "message" => "Снимката е качена.",
        "photo_id" => $connection->lastInsertId(),
        "image_dir" => $image_dir
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "ERROR", "message" => "Грешка при записване в базата: " . $e->getMessage()]);
}
?>
