<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['email'], $input['new_password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing email or new_password']);
    exit;
}

$email = $input['email'];
$new_password = $input['new_password'];
$hashed = hash('sha256', $new_password);

// Check if user exists
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if (!$stmt->fetch()) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

// Update password
$update = $pdo->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE email = ?');
try {
    $update->execute([$hashed, $email]);
    echo json_encode(['status' => 'success', 'message' => 'Password updated successfully']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
