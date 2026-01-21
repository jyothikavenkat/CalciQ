<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/config.php'; // $pdo

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['user_id'])) {
    echo json_encode(['error' => 'Missing user_id']);
    exit;
}

$user_id = $input['user_id'];

try {
    $pdo->beginTransaction();
    // Delete scan results for patients belonging to this user
    $stmt = $pdo->prepare('SELECT patient_id FROM patients WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $patientIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if ($patientIds) {
        $in = implode(',', array_fill(0, count($patientIds), '?'));
        $delScans = $pdo->prepare("DELETE FROM scan_results WHERE patient_id IN ($in)");
        $delScans->execute($patientIds);
    }
    // Delete patients
    $stmtDelPatients = $pdo->prepare('DELETE FROM patients WHERE user_id = ?');
    $stmtDelPatients->execute([$user_id]);
    // Delete user
    $stmtDelUser = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmtDelUser->execute([$user_id]);
    $pdo->commit();
    echo json_encode(['status' => 'deleted']);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Database error']);
}
?>
