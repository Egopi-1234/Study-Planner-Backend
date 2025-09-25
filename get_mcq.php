<?php
header('Content-Type: application/json');

include('db.php'); // defines $sql as mysqli connection

// Get course name from query parameter
$course = isset($_GET['course']) ? $_GET['course'] : '';

if (empty($course)) {
    echo json_encode(["error" => "Please provide course name"]);
    exit;
}

// Fetch MCQs for the course
$query = "SELECT * FROM mcq_questions WHERE course_name = ?";
$stmt = $sql->prepare($query);
$stmt->bind_param("s", $course);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

echo json_encode($questions);

$stmt->close();
$sql->close();
?>
