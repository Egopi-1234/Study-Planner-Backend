<?php
header("Content-Type: application/json"); // return JSON response

// Database connection
$host     = "localhost";
$user     = "root";
$password = "";
$database = "study";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die(json_encode(["status" => "error", "message" => "Database connection failed: " . mysqli_connect_error()]));
}

// Read JSON input from Postman
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($data) && isset($data['course_name']) && isset($data['email_id'])) {

        $course_name = mysqli_real_escape_string($conn, $data['course_name']);
        $email_id    = mysqli_real_escape_string($conn, $data['email_id']);

        // Delete query
        $sql = "DELETE FROM course_enrolled WHERE course_name = '$course_name' AND email_id = '$email_id'";

        if (mysqli_query($conn, $sql)) {
            if (mysqli_affected_rows($conn) > 0) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Unenrolled successfully!"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "No enrollment found for this course and email"
                ]);
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Query failed: " . mysqli_error($conn)
            ]);
        }

    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Missing course_name or email_id"
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method (use POST)"
    ]);
}

mysqli_close($conn);
?>
