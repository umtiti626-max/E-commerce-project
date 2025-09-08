<?php
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
// Connection successful
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Login</title>
    <style>
        body {
            background: linear-gradient(135deg, #4f2cc8 0%, #2196f3 100%);
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: #fff;
            padding: 2rem 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(79,44,200,0.15);
            width: 100%;
            max-width: 350px;
        }
        .login-header {
            color: #4f2cc8;
            text-align: center;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
        }
        .login-form label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2196f3;
            font-weight: 500;
        }
        .login-form input {
            width: 100%;
            padding: 0.7rem;
            margin-bottom: 1.2rem;
            border: 1px solid #ddd;
            border-radius: 0.5rem;
            font-size: 1rem;
            background: #f7f8fa;
        }
        .login-form input:focus {
            border-color: #4f2cc8;
            outline: none;
        }
        .login-form button {
            width: 100%;
            padding: 0.8rem;
            background: linear-gradient(90deg, #2196f3 60%, #4f2cc8 100%);
            color: #fff;
            border: none;
            border-radius: 0.5rem;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        .login-form button:hover {
            background: linear-gradient(90deg, #4f2cc8 60%, #2196f3 100%);
        }
        .signup-link {
            display: block;
            text-align: center;
            margin-top: 1.2rem;
            color: #4f2cc8;
            text-decoration: none;
            font-weight: 500;
        }
        .signup-link:hover {
            color: #2196f3;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">Client Login</div>
        <form class="login-form" method="POST" action="process_login.php">
            <label for="email_or_number">Email or Number</label>
            <input type="text" id="email_or_number" name="email_or_number" required placeholder="Enter email or phone number">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Enter password">

            <button type="submit">Login</button>
        </form>
        <a class="signup-link" href="register.php">Don't have an account?</a>
           </div>
</body>
</html>