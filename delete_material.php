<?php
header('Content-Type: application/json');
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $material_id = $_POST['material_id'] ?? '';
    $user_id = $_POST['user_id'] ?? '';

    if (empty($material_id) || empty($user_id)) {
        echo json_encode([
            'status' => false,
            'message' => 'Invalid input',
            'data' => []
        ]);
        exit;
    }

    // Optional: Get file path before deletion
    $stmt = $sql->prepare("SELECT * FROM materials WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $material_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $material = $result->fetch_assoc();

    if (!$material) {
        echo json_encode([
            'status' => false,
            'message' => 'Material not found',
            'data' => []
        ]);
        exit;
    }

    $stmt = $sql->prepare("DELETE FROM materials WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $material_id, $user_id);

    if ($stmt->execute()) {
        if (!empty($material['file_path']) && file_exists($material['file_path'])) {
            unlink($material['file_path']);
        }
        echo json_encode([
            'status' => true,
            'message' => 'Material deleted successfully',
            'data' => $material
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Failed to delete material',
            'data' => []
        ]);
    }
}
?>
