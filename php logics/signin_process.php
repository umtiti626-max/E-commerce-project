<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$port= 3306;
$password = '';
$dbname = "ecommerce_system";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// Handle AJAX requests for email check and signup
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Email uniqueness check
    if (isset($_POST['email']) && count($_POST) === 1) {
        $email = trim($_POST['email']);
        $stmt = $conn->prepare("SELECT client_id FROM client WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            echo json_encode(["status" => "error", "field" => "email", "message" => "Email already exists."]);
        } else {
            echo json_encode(["status" => "success", "field" => "email", "message" => "Email accepted."]);
        }
        $stmt->close();
        exit;
    }
    // Account creation
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $image = $_FILES['profile_image'] ?? null;

    // Password strength check
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\d]).{8,}$/', $password)) {
        echo json_encode(["status" => "error", "field" => "password", "message" => "Password must be at least 8 characters and include upper, lower, number, and special character."]);
        exit;
    }
    if ($password !== $confirm_password) {
        echo json_encode(["status" => "error", "field" => "confirm_password", "message" => "Passwords do not match."]);
        exit;
    }
    // Email uniqueness check again
    $stmt = $conn->prepare("SELECT client_id FROM client WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(["status" => "error", "field" => "email", "message" => "Email already exists."]);
        exit;
    }
    $stmt->close();

    // Image upload
    $image_path = null;
    if ($image && $image['tmp_name']) {
        $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($ext, $allowed)) {
            echo json_encode(["status" => "error", "field" => "image", "message" => "Invalid image type."]);
            exit;
        }
        $upload_dir = __DIR__ . '/../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $image_filename = uniqid('client_', true) . '.' . $ext;
        $full_image_path = $upload_dir . $image_filename;
        if (!move_uploaded_file($image['tmp_name'], $full_image_path)) {
            echo json_encode(["status" => "error", "field" => "image", "message" => "Image upload failed."]);
            exit;
        }
        $image_path = 'uploads/' . $image_filename;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $created_at = date('Y-m-d H:i:s');
    $phonenumber = trim($_POST['phonenumber'] ?? '');
    if (!$phonenumber) {
        echo json_encode(["status" => "error", "field" => "phonenumber", "message" => "Phone number is required."]);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO client (name, email, password, profile_image, phonenumber, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $hashed_password, $image_path, $phonenumber, $created_at);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Account created successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Account creation failed."]);
    }
    $stmt->close();
    exit;
}
?>