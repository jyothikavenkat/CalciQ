<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['email'], $input['password'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$hashed = hash('sha256', $input['password']);
$stmt = $pdo->prepare('SELECT id, name, hospital FROM users WHERE email = ? AND password = ?');
$stmt->execute([$input['email'], $hashed]);
$user = $stmt->fetch();

if ($user) {
    echo json_encode([
        'status' => 'success',
        'user_id' => $user['id'],
        'name' => $user['name'],
        'hospital' => $user['hospital'],
        'token' => 'php_token_' . $user['id']
    ]);
} else {
    echo json_encode(['error' => 'Invalid credentials']);
}
?>
