<?php
// get_client_profile.php
// Returns client profile info for the logged-in user
header('Content-Type: application/json');
session_start();
require_once 'db.php'; // Assumes db.php sets up $conn

if (!isset($_SESSION['client_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}
$client_id = $_SESSION['client_id'];
$sql = "SELECT name, phone, profile_img FROM client WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $client_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'success' => true,
        'name' => $row['name'],
        'phone' => $row['phone'],
        'profile_img' => $row['profile_img'] ? $row['profile_img'] : 'icons/user.png'
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'User not found']);
}
$stmt->close();
$conn->close();
?>
