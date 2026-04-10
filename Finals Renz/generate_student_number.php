<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hcc_schedule";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed']));
}

function generateStudentNumber($conn) {
    $sql = "SELECT student_number FROM student WHERE student_number LIKE '2026-%' ORDER BY student_number DESC LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_number = $row['student_number'];
        $last_id = intval(substr($last_number, 5));
        $new_id = $last_id + 1;
        return "2026-" . str_pad($new_id, 4, "0", STR_PAD_LEFT);
    } else {
        return "2026-0001";
    }
}

$student_number = generateStudentNumber($conn);
$conn->close();

header('Content-Type: application/json');
echo json_encode(['student_number' => $student_number]);
?>