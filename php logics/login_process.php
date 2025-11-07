<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$port=3306;
$password = '';
$dbname = "ecommerce_system";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_or_number = trim($_POST['email_or_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $conn->prepare("SELECT password FROM client WHERE email = ? OR phonenumber = ?");
    $stmt->bind_param("ss", $email_or_number, $email_or_number);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            // Fetch client_id, name, phonenumber
            $stmt2 = $conn->prepare("SELECT client_id, name, phonenumber FROM client WHERE email = ? OR phonenumber = ? LIMIT 1");
            $stmt2->bind_param("ss", $email_or_number, $email_or_number);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            if ($row = $result2->fetch_assoc()) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Login successful!",
                    "client_id" => $row['client_id'],
                    "name" => $row['name'],
                    "phonenumber" => $row['phonenumber']
                ]);
            } else {
                echo json_encode(["status" => "success", "message" => "Login successful!"]);
            }
            $stmt2->close();
        } else {
            echo json_encode(["status" => "error", "message" => "Account not found."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Account not found."]);
    }
    $stmt->close();
    exit;
}
echo json_encode(["status" => "error", "message" => "Invalid request."]);
