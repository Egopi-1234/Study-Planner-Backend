<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DB Connection
$host = "localhost";
$user = "root";
$password = "";
$database = "study";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "Connection failed: " . $conn->connect_error]));
}

// Read JSON input
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'] ?? null;
$course_name = $data['course_name'] ?? '';
$answers = $data['answers'] ?? [];

if (empty($user_id) || empty($answers) || empty($course_name)) {
    echo json_encode(["success" => false, "error" => "user_id, course_name and answers are required"]);
    exit;
}

$total_questions = count($answers);
$correct_count = 0;

// STEP 1: Create a new test_attempt row (auto-increment attempt_id)
$attempt_sql = "INSERT INTO test_attempts (user_id, course_name, total_questions, score, test_date) 
                VALUES (?, ?, ?, 0, NOW())";
$stmt_attempt = $conn->prepare($attempt_sql);
if (!$stmt_attempt) {
    echo json_encode(["success" => false, "error" => "Prepare failed (attempt insert): " . $conn->error]);
    exit;
}
$stmt_attempt->bind_param("isi", $user_id, $course_name, $total_questions);
$stmt_attempt->execute();
$attempt_id = $stmt_attempt->insert_id;  // auto-incremented attempt ID
$stmt_attempt->close();

// STEP 2: Insert answers into user_answers
$insert_sql = "INSERT INTO user_answers (user_id, attempt_id, question_id, selected_option, is_correct) 
               VALUES (?, ?, ?, ?, ?)";
$stmt_insert = $conn->prepare($insert_sql);
if (!$stmt_insert) {
    echo json_encode(["success" => false, "error" => "Prepare failed (answers insert): " . $conn->error]);
    exit;
}

foreach ($answers as $ans) {
    $question_id = $ans['question_id'] ?? null;
    $selected_option = strtoupper(trim($ans['selected_option'] ?? ''));

    if (empty($question_id) || !in_array($selected_option, ['A','B','C','D'])) {
        continue; // skip invalid
    }

    // Get correct option from mcq_questions
    $q_sql = "SELECT correct_option FROM mcq_questions WHERE question_id = ?";
    $stmt_q = $conn->prepare($q_sql);
    $stmt_q->bind_param("i", $question_id);
    $stmt_q->execute();
    $res_q = $stmt_q->get_result();
    $row_q = $res_q->fetch_assoc();
    $stmt_q->close();

    if (!$row_q) {
        continue; // question not found
    }

    $correct_option = strtoupper(trim($row_q['correct_option']));
    $is_correct = ($selected_option === $correct_option) ? 1 : 0;
    if ($is_correct) {
        $correct_count++;
    }

    // Insert user answer
    $stmt_insert->bind_param("iiisi", $user_id, $attempt_id, $question_id, $selected_option, $is_correct);
    $stmt_insert->execute();
}
$stmt_insert->close();

// STEP 3: Update score in test_attempts
$update_sql = "UPDATE test_attempts SET score = ? WHERE attempt_id = ?";
$stmt_update = $conn->prepare($update_sql);
$stmt_update->bind_param("ii", $correct_count, $attempt_id);
$stmt_update->execute();
$stmt_update->close();

// Return response
echo json_encode([
    "success" => true,
    "user_id" => $user_id,
    "attempt_id" => $attempt_id,
    "score" => $correct_count,
    "total_questions" => $total_questions
]);

$conn->close();
?>
