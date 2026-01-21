<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['name'], $input['email'], $input['password'], $input['hospital'])) {
    echo json_encode(['error' => 'Invalid request payload']);
    exit;
}

$name = $input['name'];
$email = $input['email'];
$password = $input['password'];
$hospital = $input['hospital'];

// Check if email already exists
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['error' => 'Email already registered']);
    exit;
}

// Generate UUID for user id
$user_id = bin2hex(random_bytes(16));
$hashed = hash('sha256', $password);
$token = 'php_token_' . $user_id;

$insert = $pdo->prepare('INSERT INTO users (id, name, email, password, hospital, updated_at) VALUES (?, ?, ?, ?, ?, NOW())');
try {
    $insert->execute([$user_id, $name, $email, $hashed, $hospital]);
    echo json_encode(['status' => 'success', 'user_id' => $user_id, 'token' => $token, 'name' => $name, 'hospital' => $hospital]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
