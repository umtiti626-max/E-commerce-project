<?php
// add_product.php: Handles adding a new product to the database
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$user = 'root';
$port= 3306;
$pass = '0000';
$db = 'ecommerce_system';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$specifications = trim($_POST['Specifications'] ?? '');
$category = trim($_POST['category'] ?? '');
$price = floatval($_POST['price'] ?? 0);
$imagePath = '';

// Handle image upload
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $imgTmp = $_FILES['image']['tmp_name'];
    $imgName = basename($_FILES['image']['name']);
    $imgExt = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $uploadDir = dirname(__DIR__) . '/Smartphones/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    if (in_array($imgExt, $allowed)) {
        $dest = $uploadDir . $imgName;
        if (!file_exists($dest)) {
            if (is_uploaded_file($imgTmp)) {
                move_uploaded_file($imgTmp, $dest);
            } else {
                copy($imgTmp, $dest);
            }
        }
        $imagePath = $imgName;
    }
}

if (!$name || !$category || !$price) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields.']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO product (name, description, Specifications, category, price, image, admin_id) VALUES (?, ?, ?, ?, ?, ?, 1)");
$stmt->bind_param('ssssds', $name, $description, $specifications, $category, $price, $imagePath);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to add product: ' . $stmt->error]);
}
$stmt->close();
$conn->close();
?>