<?php
header('Content-Type: application/json');
include 'db.php';

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status'  => false,
        'message' => 'Invalid request method; only POST allowed.',
        'data'    => []
    ]);
    exit;
}

// Validate user_id
$user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
if ($user_id <= 0) {
    echo json_encode([
        'status'  => false,
        'message' => 'Invalid or missing user_id.',
        'data'    => []
    ]);
    exit;
}

// Get the requested path (e.g. /all, /pending, /complete, /priority)
$path = '/';
if (isset($_SERVER['PATH_INFO'])) {
    $path = trim($_SERVER['PATH_INFO'], '/');
}

// Allowed views
$allowed = ['all', 'pending', 'complete', 'priority'];
$view = in_array($path, $allowed, true) ? $path : 'all';

// Base SQL query
$sqlStr = "
    SELECT
        id,
        name       AS task,
        description,
        Date       AS date,
        time,
        Priority   AS priority,
        status,
        user_Id    AS user_id
    FROM tasks
    WHERE user_id = ?
";

// Modify query based on view
if ($view === 'pending') {
    $sqlStr .= " AND status = 'pending'";
} elseif ($view === 'complete') {
    $sqlStr .= " AND status = 'complete'";
} elseif ($view === 'priority') {
    // Order by priority manually: High first, Medium next, Low last
    $sqlStr .= " ORDER BY 
        CASE 
            WHEN priority = 'high' THEN 1
            WHEN priority = 'medium' THEN 2
            WHEN priority = 'low' THEN 3
            ELSE 4
        END";
}

// Prepare and execute the query
$stmt = $sql->prepare($sqlStr);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch results
$tasks = [];
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}

// Return JSON response
echo json_encode([
    'status'  => true,
    'message' => 'Tasks retrieved successfully.',
    'view'    => $view,
    'data'    => $tasks
]);
$stmt->close();
$sql->close();
?>