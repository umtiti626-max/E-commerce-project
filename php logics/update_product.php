<?php
// update_product.php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$port=3306;
$password = "0000";
$dbname = "ecommerce_system";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed."]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $Specifications = trim($_POST['Specifications'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $imagePath = trim($_POST['current_image'] ?? '');
    // Always extract filename from current_image
    if ($imagePath) {
        $imagePath = basename($imagePath);
    }

    // Handle new image upload (optional)
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
    if (!$product_id) {
        echo json_encode(["error" => "Product ID is required."]);
        exit;
    }
    $stmt = $conn->prepare("UPDATE product SET name=?, description=?, Specifications=?, category=?, price=?,  image=? WHERE product_id=?");
    $stmt->bind_param("ssssdsi", $name, $description, $Specifications, $category, $price, $imagePath, $product_id);
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Update failed: " . $stmt->error]);
    }
    $stmt->close();
    exit;
}
echo json_encode(["error" => "Invalid request."]);
$conn->close();
