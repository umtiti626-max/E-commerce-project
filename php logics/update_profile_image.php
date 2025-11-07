<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$password = '';
$dbname = "ecommerce_system";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['client_id']) && isset($_FILES['profile_image'])) {
    $client_id = intval($_POST['client_id']);
    $img = $_FILES['profile_image'];
    $target_dir = "../uploads/profile_images/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    // Remove previous image if exists
    $stmt_prev = $conn->prepare("SELECT profile_image FROM client WHERE client_id = ?");
    $stmt_prev->bind_param('i', $client_id);
    $stmt_prev->execute();
    $stmt_prev->bind_result($prev_img);
    $stmt_prev->fetch();
    $stmt_prev->close();
    if ($prev_img && file_exists("../" . $prev_img)) {
        unlink("../" . $prev_img);
    }
    // Save new image with a fixed name per user
    $ext = pathinfo($img['name'], PATHINFO_EXTENSION);
    $filename = 'client_' . $client_id . '.' . $ext;
    $target_file = $target_dir . $filename;
    // Remove any other images for this user (with different extensions)
    foreach (glob($target_dir . 'client_' . $client_id . '.*') as $oldfile) {
        if ($oldfile !== $target_file) unlink($oldfile);
    }
    if (move_uploaded_file($img['tmp_name'], $target_file)) {
        $db_path = 'uploads/profile_images/' . $filename;
        $stmt = $conn->prepare("UPDATE client SET profile_image = ? WHERE client_id = ?");
        $stmt->bind_param('si', $db_path, $client_id);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "image" => $db_path]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update database."]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to upload image."]);
    }
    $conn->close();
    exit;
}
echo json_encode(["status" => "error", "message" => "Invalid request."]);