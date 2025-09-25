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

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($data)) {
    if (isset($data['course_name']) && isset($data['email_id'])) {

        $course_name = mysqli_real_escape_string($conn, $data['course_name']);
        $email_id    = mysqli_real_escape_string($conn, $data['email_id']);

        $sql = "INSERT INTO course_enrolled (course_name, email_id) VALUES ('$course_name', '$email_id')";

        if (mysqli_query($conn, $sql)) {
            echo json_encode([
                "status" => "success",
                "message" => "Enrollment successful!"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => mysqli_error($conn)
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
        "message" => "Invalid request or empty data"
    ]);
}
?>
