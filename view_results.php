<?php
include 'db.php';
header("Content-Type: application/json");
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- Get user_id from raw JSON input ---
$input = json_decode(file_get_contents("php://input"), true);
$user_id = isset($input['user_id']) ? intval($input['user_id']) : 0;

// --- Validate user_id ---
if ($user_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid user_id"
    ]);
    exit;
}

// --- Fetch latest attempt ID ---
$query_attempt = "SELECT attempt_id 
                  FROM test_attempts 
                  WHERE user_id = ? 
                  ORDER BY attempt_id DESC 
                  LIMIT 1";
$stmt_attempt = $sql->prepare($query_attempt);
if (!$stmt_attempt) {
    echo json_encode([
        "success" => false,
        "message" => "Database prepare failed: " . $sql->error
    ]);
    exit;
}
$stmt_attempt->bind_param("i", $user_id);
$stmt_attempt->execute();
$result_attempt = $stmt_attempt->get_result();

if ($row_attempt = $result_attempt->fetch_assoc()) {
    $attempt_id = $row_attempt['attempt_id'];
} else {
    echo json_encode([
        "success" => false,
        "message" => "No attempts found for this user"
    ]);
    exit;
}
$stmt_attempt->close();

// --- Fetch questions + user answers ---
$query = "SELECT ua.question_id, ua.selected_option, ua.is_correct,
                 mq.question, mq.option_a, mq.option_b, mq.option_c, mq.option_d, mq.correct_option
          FROM user_answers ua
          JOIN mcq_questions mq ON ua.question_id = mq.question_id
          WHERE ua.attempt_id = ?";
$stmt = $sql->prepare($query);
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Database prepare failed: " . $sql->error
    ]);
    exit;
}
$stmt->bind_param("i", $attempt_id);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
$score = 0;

while ($row = $result->fetch_assoc()) {
    $questions[] = [
        "question_id" => $row['question_id'],
        "question" => $row['question'],
        "options" => [
            "A" => $row['option_a'],
            "B" => $row['option_b'],
            "C" => $row['option_c'],
            "D" => $row['option_d']
        ],
        "selected_option" => $row['selected_option'],
        "is_correct" => $row['is_correct'],
        "correct_option" => $row['correct_option']
    ];
    if ($row['is_correct'] == 1) {
        $score++;
    }
}

$total_questions = count($questions);

// --- Output final JSON ---
echo json_encode([
    "success" => true,
    "attempt_id" => $attempt_id,
    "score" => $score,
    "total_questions" => $total_questions,
    "questions" => $questions
], JSON_UNESCAPED_UNICODE);

$stmt->close();
$sql->close();
?>
