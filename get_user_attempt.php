You said:
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
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Read JSON input
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'] ?? null;

if(empty($user_id)){
    echo json_encode(["error" => "user_id is required"]);
    exit;
}

// Get the latest attempt_id for this user
$attempt_sql = "SELECT attempt_id FROM test_attempts WHERE user_id = ? ORDER BY attempt_id DESC LIMIT 1";
$stmt_attempt = $conn->prepare($attempt_sql);
if(!$stmt_attempt){
    echo json_encode(["error" => "Prepare failed: ".$conn->error]);
    exit;
}

$stmt_attempt->bind_param("i", $user_id);
$stmt_attempt->execute();
$result_attempt = $stmt_attempt->get_result();

if($row_attempt = $result_attempt->fetch_assoc()){
    $attempt_id = $row_attempt['attempt_id'];
} else {
    echo json_encode(["error" => "No attempts found for this user"]);
    exit;
}
$stmt_attempt->close();

// SQL to get user answers along with question details
$sql = "SELECT ua.question_id, ua.selected_option, ua.is_correct, 
               mq.question, mq.option_a, mq.option_b, mq.option_c, mq.option_d
        FROM user_answers ua
        JOIN mcq_questions mq ON ua.question_id = mq.question_id
        WHERE ua.attempt_id = ?";

$stmt = $conn->prepare($sql);
if(!$stmt){
    echo json_encode(["error" => "Prepare failed: ".$conn->error]);
    exit;
}

$stmt->bind_param("i", $attempt_id);

if(!$stmt->execute()){
    echo json_encode(["error" => "Execute failed: ".$stmt->error]);
    exit;
}

$result = $stmt->get_result();
$questions = [];

while($row = $result->fetch_assoc()){
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
        "is_correct" => $row['is_correct']
    ];
}

echo json_encode([
    "success" => true,
    "attempt_id" => $attempt_id,
    "questions" => $questions
]);

$stmt->close();
$conn->close();
?>