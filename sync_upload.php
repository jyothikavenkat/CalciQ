<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

try {
    // Process patients
    if (isset($input['patients']) && is_array($input['patients'])) {
        foreach ($input['patients'] as $p) {
            // Ensure user_id is provided, otherwise default to empty or handle error
            $user_id = $p['user_id'] ?? ''; 
            
            $stmt = $pdo->prepare('INSERT INTO patients (id, patient_id, name, dob, gender, updated_at, user_id) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), dob=VALUES(dob), gender=VALUES(gender), updated_at=VALUES(updated_at), user_id=VALUES(user_id)');
            $stmt->execute([
                $p['id'] ?? null,
                $p['patient_id'],
                $p['name'],
                $p['dob'] ?? null,
                $p['gender'] ?? null,
                $p['updated_at'] ?? date('Y-m-d H:i:s'),
                $user_id
            ]);
        }
    }

    // Process scan results
    if (isset($input['scan_results']) && is_array($input['scan_results'])) {
        foreach ($input['scan_results'] as $s) {
            $stmt = $pdo->prepare('INSERT INTO scan_results (id, patient_id, scan_date, stone_volume_mm3, stone_diameter_mm, stone_type, density_hu, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE scan_date=VALUES(scan_date), stone_volume_mm3=VALUES(stone_volume_mm3), stone_diameter_mm=VALUES(stone_diameter_mm), stone_type=VALUES(stone_type), density_hu=VALUES(density_hu), updated_at=VALUES(updated_at)');
            $stmt->execute([
                $s['id'],
                $s['patient_id'],
                $s['scan_date'] ?? null,
                $s['stone_volume_mm3'] ?? 0,
                $s['stone_diameter_mm'] ?? null,
                $s['stone_type'] ?? null,
                $s['density_hu'] ?? null,
                $s['updated_at'] ?? date('Y-m-d H:i:s')
            ]);
        }
    }

    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
