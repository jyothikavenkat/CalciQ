<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['user_id'], $input['token'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Missing credentials']);
    exit;
}

$expected = 'php_token_' . $input['user_id'];
if ($input['token'] !== $expected) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token']);
    exit;
}

$stmt = $pdo->prepare('SELECT id, name, hospital FROM users WHERE id = ?');
$stmt->execute([$input['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'User not found']);
    exit;
}

echo json_encode([
    'status' => 'success',
    'user_id' => $user['id'],
    'name' => $user['name'],
    'hospital' => $user['hospital'],
    'token' => $input['token']
]);
?>
