<?php
require_once("./check_JSON_validity.php");
require_once("./login.php");

$data = file_get_contents("php://input");

if (strlen($data) > 0 && check_json($data)) {
    $user_data = json_decode($data, true);
} else {
    http_response_code(400);
    exit(json_encode(["status" => "ERROR", "message" => "Невалиден JSON формат!"]));
}

$user = [
    "email"    => $user_data["email"],
    "password" => $user_data["password"]
];

$response = login($user);

http_response_code($response["code"]);
exit(json_encode(["status" => $response["status"], "message" => $response["message"]]));
?>
