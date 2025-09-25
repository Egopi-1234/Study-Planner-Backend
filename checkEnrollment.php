<?php
header("Content-Type: application/json"); // return JSON response

// Database connection
$host = "localhost";
$user = "root"; // your DB username
$password = ""; // your DB password
$dbname = "study"; // your DB name

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode([
        "status" => false,
        "message" => "Database connection failed"
    ]);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Map Android keys to database columns
if (isset($input['email']) && isset($input['courseName'])) {
    $email = $conn->real_escape_string($input['email']);          // maps to email_id column
    $courseName = $conn->real_escape_string($input['courseName']); // maps to course_name column

    // Check if user is enrolled
    $sql = "SELECT * FROM course_enrolled WHERE email_id='$email' AND course_name='$courseName' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        echo json_encode([
            "status" => true,
            "message" => "User is already enrolled"
        ]);
    } else {
        echo json_encode([
            "status" => false,
            "message" => "User is not enrolled"
        ]);
    }

} else {
    echo json_encode([
        "status" => false,
        "message" => "Invalid input"
    ]);
}

$conn->close();
?>
