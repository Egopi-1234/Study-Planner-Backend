<?php
header("Content-Type: application/json");
require_once 'db.php'; // your DB sqlection file

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get parameters
    $material_id = isset($_POST['materials_id']) ? intval($_POST['materials_id']) : 0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';

    if ($material_id > 0 && !empty($status)) {
        $stmt = $sql->prepare("UPDATE materials SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $material_id);

        if ($stmt->execute()) {
            $response['status'] = true;
            $response['message'] = "Material status updated successfully.";
        } else {
            $response['status'] = false;
            $response['message'] = "Failed to update material status.";
        }

        $stmt->close();
    } else {
        $response['status'] = false;
        $response['message'] = "Invalid input.";
    }
} else {
    $response['status'] = false;
    $response['message'] = "Invalid request method.";
}

echo json_encode($response);
$sql->close();
?>
