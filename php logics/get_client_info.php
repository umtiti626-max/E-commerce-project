<?php
// get_client_info.php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root"; // Change if your DB username is different
$port=3306;
$password = "";     // Change if your DB password is different
$dbname = "ecommerce_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

if (!isset($_POST['client_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing client_id']);
    $conn->close();
    exit;
}

$client_id = intval($_POST['client_id']);
$sql = "SELECT name, phonenumber, profile_image FROM client WHERE client_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $client_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'status' => 'success',
        'name' => $row['name'],
        'phonenumber' => $row['phonenumber'],
        'profile_image' => $row['profile_image'] ?? ''
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Client not found']);
}
$stmt->close();
$conn->close();
?>
