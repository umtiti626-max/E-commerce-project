<?php
$servername = "localhost";
$username = "root";
$password = "";
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
        $image_path = 'uploads/' . uniqid('client_', true) . '.' . $ext;
        if (!move_uploaded_file($image['tmp_name'], $image_path)) {
            echo json_encode(["status" => "error", "field" => "image", "message" => "Image upload failed."]);
            exit;
        }
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $created_at = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO client (name, email, password, profile_image, created_at) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $hashed_password, $image_path, $created_at);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Account created successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Account creation failed."]);
    }
    $stmt->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'Inter', Arial, Helvetica, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url(icons/cover2.jpeg) no-repeat center center fixed;
            background-size: cover;
        }
        .login-container {
            background: #fff;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
            border-radius: 8px;
            padding: 44px 36px 36px 36px;
            min-width: 370px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: box-shadow 0.3s;
        }
        .login-header {
            color: #4f2cc8;
            text-align: center;
            font-size: 2.5rem;
            font-weight: bold;
            font-family: "Chiller";
            margin-bottom: 2.2rem;
            letter-spacing: 0.5px;
        }
        .login-form {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 32px;
        }
        .input-group {
            position: relative;
            margin-bottom: 0.1rem;
        }
        .input-group input {
            width: 80%;
            padding: 18px 48px 18px 18px;
            border: 1.5px solid #e5e7eb;
            border-radius: 38px;
            font-size: 1.08rem;
            background: #f7f8fa;
            transition: border 0.3s, box-shadow 0.3s;
            outline: none;
        }
        .input-group input:focus {
            border-color: #a5b4fc;
            box-shadow: 0 0 0 2px #c7d2fe55;
        }
        .input-group label {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            color: #a1a1aa;
            font-size: 1.08rem;
            pointer-events: none;
            transition: 0.2s cubic-bezier(.4,0,.2,1);
            padding: 0 4px;
        }
        .input-group input:focus + label,
        .input-group input:not(:placeholder-shown) + label {
            top: -12px;
            left: 12px;
            background: #fff;
            color: #4f2cc8;
            font-size: 0.92rem;
            padding: 0 6px;
            border-radius: 8px;
            box-shadow: 0 2px 8px 0 rgba(31, 38, 135, 0.06);
        }
        .profile-img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2.5px solid #a5b4fc; margin-bottom: 10px; box-shadow: 0 2px 8px 0 rgba(31, 38, 135, 0.10); }
        .image-upload-wrapper { position: relative; display: flex; flex-direction: column; align-items: center; }
        .upload-btn { position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); background: #a259f7; color: #fff; border: none; border-radius: 18px; padding: 4px 18px; font-size: 0.95rem; cursor: pointer; opacity: 0.95; transition: background 0.2s; z-index: 2; display:none; }
        .upload-btn:hover { background: #7c3aed; }
        .image-upload-wrapper:hover .upload-btn { display: block !important; }
        .msg { font-size: 0.93rem; margin-top: 2px; min-height: 18px; }
        .msg.error { color: #e11d48; }
        .msg.success { color: #22c55e; }
        .login-btn {
            width: 100%;
            padding: 15px 0;
            background: linear-gradient(90deg, #c084fc 0%, #a5b4fc 100%);
            color: #fff;
            border: none;
            border-radius: 32px;
            font-size: 1.15rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px 0 rgba(31, 38, 135, 0.10);
            position: relative;
            overflow: hidden;
            transition: box-shadow 0.2s, background 0.3s;
        }
        .login-btn:before {
            content: '';
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%) scale(0);
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            width: 200%;
            height: 200%;
            transition: transform 0.5s cubic-bezier(.4,0,.2,1), opacity 0.5s;
            opacity: 0;
            z-index: 1;
        }
        .login-btn:active:before {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
            transition: 0s;
        }
        .login-btn:hover {
            background: linear-gradient(90deg, #a5b4fc 0%, #c084fc 100%);
            box-shadow: 0 4px 16px 0 rgba(31, 38, 135, 0.18);
        }
        .signup-row { display: flex; justify-content: center; align-items: center; gap: 6px; margin-top: 1.2rem; }
        .signup-link { color: #a259f7; text-decoration: none; font-weight: 600; cursor:pointer; }
        .signup-link:hover { text-decoration: underline; }
        .popup-msg { position: fixed; top: 30px; left: 50%; transform: translateX(-50%); background: #22c55e; color: #fff; padding: 16px 32px; border-radius: 16px; font-size: 1.1rem; font-weight: 600; box-shadow: 0 4px 24px rgba(34,197,94,0.18); display: none; z-index: 999; animation: fadeIn 0.5s; }
        @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
        @media (max-width: 500px) {
            .login-container { min-width: 90vw; padding: 32px 8vw; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">Sign Up</div>
        <form class="login-form" id="signupForm" enctype="multipart/form-data" autocomplete="off">
            <div class="input-group" style="flex-direction:column;align-items:center;">
                <div class="image-upload-wrapper" id="imageUploadWrapper">
                    <img src="icons/user.png" id="previewImg" class="profile-img" alt="Profile Image">
                    <input type="file" id="imageInput" name="profile_image" accept="image/*" style="display:none;">
                    <button type="button" id="uploadBtn" class="upload-btn">Upload</button>
                </div>
            </div>
            <div class="input-group">
                <input type="text" id="name" name="name" required placeholder=" ">
                <label for="name">Client Name</label>
            </div>
            <div class="input-group">
                <input type="email" id="signup_email" name="email" required placeholder=" ">
                <label for="signup_email">Email</label>
                <div id="emailMsg" class="msg"></div>
            </div>
            <div class="input-group">
                <input type="password" id="signup_password" name="password" required placeholder=" ">
                <label for="signup_password">Password</label>
                <div id="passwordMsg" class="msg"></div>
            </div>
            <div class="input-group">
                <input type="password" id="confirm_password" name="confirm_password" required placeholder=" ">
                <label for="confirm_password">Confirm Password</label>
                <div id="confirmMsg" class="msg"></div>
            </div>
            <button type="submit" class="login-btn">Sign Up</button>
            <div class="signup-row" style="margin-top:10px;">
                <span>Already have an account?</span>
                <a class="signup-link" id="showLogin" href="login_form.php">Login</a>
            </div>
        </form>
    </div>
    <div id="popupMsg" class="popup-msg"></div>
    <script>
    // Ripple effect for .login-btn
    document.querySelectorAll('.login-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            ripple.className = 'ripple';
            ripple.style.left = (e.offsetX - 50) + 'px';
            ripple.style.top = (e.offsetY - 50) + 'px';
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    });
    // Image upload preview and button
    const imageInput = document.getElementById('imageInput');
    const previewImg = document.getElementById('previewImg');
    const uploadBtn = document.getElementById('uploadBtn');
    document.getElementById('imageUploadWrapper').onmouseenter = function(){ uploadBtn.style.display='block'; };
    document.getElementById('imageUploadWrapper').onmouseleave = function(){ uploadBtn.style.display='none'; };
    uploadBtn.onclick = function(){ imageInput.click(); };
    imageInput.onchange = function(e){
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(ev) { previewImg.src = ev.target.result; };
            reader.readAsDataURL(e.target.files[0]);
        }
    };
    // Email uniqueness check (AJAX)
    document.getElementById('signup_email').addEventListener('input', function(){
        const email = this.value.trim();
        const emailMsg = document.getElementById('emailMsg');
        if (!email) { emailMsg.textContent = ''; emailMsg.className = 'msg'; return; }
        if (!email.includes('@')) {
            emailMsg.textContent = "Invalid email, missing a '@' in the email";
            emailMsg.className = 'msg error';
            return;
        }
        fetch('signin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'email=' + encodeURIComponent(email)
        })
        .then(res => res.json())
        .then(data => {
            if (data.field === 'email' && data.status === 'error') {
                emailMsg.textContent = 'Email already exists';
                emailMsg.className = 'msg error';
            } else {
                emailMsg.textContent = 'Email accepted';
                emailMsg.className = 'msg success';
            }
        });
    });
    // Password strength check
    document.getElementById('signup_password').addEventListener('input', function(){
        const val = this.value;
        const msg = document.getElementById('passwordMsg');
        if (!val) { msg.textContent = ''; msg.className = 'msg'; return; }
        if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\d]).{8,}$/.test(val)) {
            msg.textContent = 'Password must be 8+ chars, upper, lower, number, special char.';
            msg.className = 'msg error';
        } else {
            msg.textContent = 'Strong password';
            msg.className = 'msg success';
        }
    });
    // Confirm password match
    document.getElementById('confirm_password').addEventListener('input', function(){
        const val = this.value;
        const pw = document.getElementById('signup_password').value;
        const msg = document.getElementById('confirmMsg');
        if (!val) { msg.textContent = ''; msg.className = 'msg'; return; }
        if (val !== pw) {
            msg.textContent = 'Passwords do not match';
            msg.className = 'msg error';
        } else {
            msg.textContent = 'Passwords match';
            msg.className = 'msg success';
        }
    });
    // Sign up form submit
    document.getElementById('signupForm').onsubmit = function(e){
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
    fetch('signin.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                showPopup(data.message, true);
                setTimeout(()=>{ window.location.href = 'login_form.php'; }, 2000);
            } else {
                if (data.field && document.getElementById(data.field+'Msg')) {
                    document.getElementById(data.field+'Msg').textContent = data.message;
                    document.getElementById(data.field+'Msg').className = 'msg error';
                } else {
                    showPopup(data.message, false);
                }
            }
        });
    };
    // Popup message
    function showPopup(msg, success) {
        const popup = document.getElementById('popupMsg');
        popup.textContent = msg;
        popup.style.background = success ? '#22c55e' : '#e11d48';
        popup.style.display = 'block';
        setTimeout(()=>{ popup.style.display = 'none'; }, 5000);
    }
    </script>
</body>
</html>
