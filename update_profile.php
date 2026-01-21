<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['id'])) {
    echo json_encode(['error' => 'Missing user id']);
    exit;
}

$id = $input['id'];
$name = $input['name'] ?? null;
$hospital = $input['hospital'] ?? null;
$updated_at = $input['updated_at'] ?? date('Y-m-d H:i:s');

// Build update query dynamically
$fields = [];
$params = [];

if ($name !== null) {
    $fields[] = 'name = ?';
    $params[] = $name;
}
if ($hospital !== null) {
    $fields[] = 'hospital = ?';
    $params[] = $hospital;
}
$fields[] = 'updated_at = ?';
$params[] = $updated_at;

$params[] = $id; // For WHERE clause

if (empty($fields)) {
    echo json_encode(['status' => 'success', 'message' => 'No changes']);
    exit;
}

$sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute($params);
    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
