<?php
header('Content-Type: application/json');
include 'db.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $user_id = $_POST['user_id'] ?? '';
    $materials_id = $_POST['materials_id'] ?? '';

    if (empty($user_id) || empty($materials_id )) {
        echo json_encode(['status' => false, 'message' => 'User ID and materials ID are required']);
        exit;
    }

    // Fetch task details
    $stmt = $sql->prepare("SELECT * FROM materials WHERE user_id = ? AND id = ?");
    $stmt->bind_param("ii", $user_id, $materials_id );
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $task_details = $result->fetch_assoc();
        echo json_encode(['status' => true, 'message'=> 'fetched materials details successfully' ,'data' => $task_details]);
    } else {
        echo json_encode(['status' => false, 'message' => 'No Materials found']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['status' => false, 'message' => 'Invalid request method']);
} 