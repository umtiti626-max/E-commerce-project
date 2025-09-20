<?php
// product_data.php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$port= 3306;
$password = '0000';
$dbname = "ecommerce_system";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed."]);
    exit;
}
// Fetch all products
$sql = "SELECT product_id, image, name, description, Specifications, price, category FROM product";
$result = $conn->query($sql);
$products = [];
$category_counts = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $cat = $row['category'];
        if (!isset($category_counts[$cat])) $category_counts[$cat] = 0;
        $category_counts[$cat]++;
        // Prepend Smartphones/ to image if it exists and is not already a URL
        if (!empty($row['image']) && strpos($row['image'], 'http') !== 0 && strpos($row['image'], 'Smartphones/') !== 0) {
            $row['image'] = 'Smartphones/' . $row['image'];
        }
        $products[] = $row;
    }
}
// Attach stock count to each product (number of products in its category)
foreach ($products as &$prod) {
    $prod['stock'] = $category_counts[$prod['category']];
}
// Get unique categories for dropdowns
$categories = array_keys($category_counts);
echo json_encode([
    "products" => $products,
    "categories" => $categories
]);
$conn->close();
