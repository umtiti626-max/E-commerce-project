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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'Inter', Arial, Helvetica, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url(icons/top-view-virtual-reality-headset-white-headphones.jpg) no-repeat center center fixed;
            background-size: cover;
        }
        .login-container {
            background: #fff;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
            border-radius: 8px;
            padding: 84px 56px 66px 56px;
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
            margin-bottom: 1.5rem;
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
            top: 40%;
            transform: translateY(-50%);
            background: transparent;
            color: #a1a1aa;
            font-size: 1.08rem;
            pointer-events: none;
            transition: 0.2s cubic-bezier(.4,0,.2,1);
            padding: 0px 4px;
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
        .msg {
            font-size: 0.93rem;
            margin-bottom: 25px;
            margin-top: -19px;
            min-height: 5px;
            max-width: 90%;
            word-break: break-word;
            white-space: pre-line;
            display: block;
                overflow-wrap: break-word;
                box-sizing: border-box;
        }
            .login-container, .login-form {
                max-width: 420px;
                min-width: 320px;
                width: 100%;
            }
            @media (max-width: 600px) {
                .login-container {
                    min-width: 95vw;
                    max-width: 99vw;
                    padding: 32px 4vw 32px 4vw;
                    border-radius: 12px;
                    box-shadow: 0 2px 12px 0 rgba(31,38,135,0.10);
                }
                .login-header {
                    font-size: 1.9rem;
                    margin-bottom: 1.2rem;
                }
                .login-form {
                    gap: 18px;
                }
                .input-group input {
                    width: 90%;
                    font-size: 1rem;
                    padding: 14px 40px 14px 14px;
                    border-radius: 28px;
                }
                .input-group label {
                    font-size: 1rem;
                    left: 14px;
                }
                .profile-img {
                    width: 56px;
                    height: 56px;
                }
                .login-btn, .back-btn {
                    font-size: 1rem;
                    padding: 12px 0;
                    border-radius: 18px;
                }
                .signup-row {
                    font-size: 0.98rem;
                }
            }
            @media (max-width: 378px) {
                .login-container {
                    min-width: 100vw;
                    max-width: 100vw;
                    padding: 12px 2vw 12px 2vw;
                }
                .login-header {
                    font-size: 1.4rem;
                }
                .input-group input {
                    width: 80%;
                    font-size: 1rem;
                    padding: 14px 40px 14px 14px;
                    border-radius: 28px;
                }
                .login-header {
                    font-size: 1.9rem;
                    margin-bottom: 1.2rem;
                }
            }
        .msg.error { color: #e11d48; }
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
            <div id="step1">
                <div class="input-group">
                    <input type="text" id="name" name="name" required placeholder=" ">
                    <label for="name">Client Name</label>
                    <span class="tick" id="nameTick"></span>
                </div>
                <div class="input-group">
                    <input type="email" id="signup_email" name="email" required placeholder=" ">
                    <label for="signup_email">Email</label>
                    <span class="tick" id="emailTick"></span>
                    <div id="emailMsg" class="msg"></div>
                </div>
                <div class="input-group">
                    <input type="password" id="signup_password" name="password" required placeholder=" ">
                    <label for="signup_password">Password</label>
                    <span class="tick" id="passwordTick"></span>
                    <div id="passwordMsg" class="msg"></div>
                </div>
                <div class="input-group">
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder=" ">
                    <label for="confirm_password">Confirm Password</label>
                    <span class="tick" id="confirmTick"></span>
                    <div id="confirmMsg" class="msg"></div>
                </div>
                <button type="button" class="login-btn" id="nextStepBtn">Next</button>
                <div class="signup-row" style="margin-top:10px;">
                    <span>Already have an account?</span>
                    <a class="signup-link" id="showLogin" href="login_form.php">Login</a>
                </div>
            </div>
            <div id="step2" style="display:none;animation:slideInLeft 0.5s forwards;">
                <div class="input-group" style="flex-direction:column;align-items:center;">
                    <div class="image-upload-wrapper" id="imageUploadWrapper">
                        <img src="icons/user.png" id="previewImg" class="profile-img" alt="Profile Image">
                        <input type="file" id="imageInput" name="profile_image" accept="image/*" style="display:none;">
                        <button type="button" id="uploadBtn" class="upload-btn">Upload</button>
                    </div>
                </div>
                <div class="input-group">
                    <input type="text" id="phonenumber" name="phonenumber" required placeholder=" ">
                    <label for="phonenumber">Phone Number</label>
                    <span class="tick" id="phoneTick"></span>
                </div>
                <button type="submit" class="login-btn" id="finalSignUpBtn" disabled>Sign Up</button>
                <button type="button" class="back-btn">Back</button>
            </div>
    <style>
    .input-group { transition: min-height 0.4s cubic-bezier(.4,0,.2,1), margin-bottom 0.4s cubic-bezier(.4,0,.2,1); }
    /* Remove input height increase on focus */
        .tick {
            display: inline-block;
            width: 22px;
            height: 22px;
            position: absolute;
            color: green;
            right: -20px;
            top: 50%;
            transform: translateY(-50%);
            background: url('icons/check.png') no-repeat center center/18px 18px;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .tick.active { opacity: 1; }
        .back-btn {
            position: absolute;
            left: 18px;
            bottom: 18px;
            background: #111;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 6px 18px;
            font-size: 0.98rem;
            font-weight: 500;
            cursor: pointer;
            z-index: 2;
            transition: background 0.2s;
        }
        .back-btn:hover { background: #333; }
        #step2 { position: relative; min-height: 320px; transition: min-height 0.4s cubic-bezier(.4,0,.2,1); }
        #step1 { transition: min-height 0.4s cubic-bezier(.4,0,.2,1); }
    </style>
        </form>
    <style>
        @keyframes slideInLeft {
            from { opacity:0; transform:translateX(100px); }
            to { opacity:1; transform:translateX(0); }
        }
        .back-btn {
           
            left: 8px;
            bottom: 4px;
            margin-top: 20px;
            background: #111;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 6px 18px;
            font-size: 0.98rem;
            font-weight: 500;
            cursor: pointer;
            z-index: 2;
            transition: background 0.2s;
        }
        .back-btn:hover { background: #333; }
    </style>
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
    // Step logic
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const nextStepBtn = document.getElementById('nextStepBtn');
    const signupForm = document.getElementById('signupForm');
    const phoneInput = document.getElementById('phonenumber');
    const finalSignUpBtn = document.getElementById('finalSignUpBtn');
    // Hide step2 initially
    step2.style.display = 'none';
    // Next button logic
    nextStepBtn.onclick = function() {
        // Validate all fields in step1
        let valid = true;
        step1.querySelectorAll('input').forEach(function(input){
            if (!input.value) valid = false;
        });
        if (!valid) return;
        step1.style.display = 'none';
        step2.style.display = 'block';
    };
    // Back button logic
    step2.querySelector('.back-btn').onclick = function() {
        step2.style.display = 'none';
        step1.style.display = 'block';
    };
    // Enable sign up only if phone is entered
    phoneInput.addEventListener('input', function() {
        finalSignUpBtn.disabled = !phoneInput.value.trim();
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
    // Email uniqueness check (AJAX) and tick
    document.getElementById('signup_email').addEventListener('input', function(){
        const email = this.value.trim();
        const emailMsg = document.getElementById('emailMsg');
        const emailTick = document.getElementById('emailTick');
        emailTick.classList.remove('active');
        if (!email) { emailMsg.textContent = ''; emailMsg.className = 'msg'; return; }
        if (!email.includes("@")) {
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
                emailMsg.textContent = '';
                emailTick.classList.add('active');
            }
        });
    });
    // Password strength check and tick
    document.getElementById('signup_password').addEventListener('input', function(){
        const val = this.value;
        const msg = document.getElementById('passwordMsg');
        const passwordTick = document.getElementById('passwordTick');
        passwordTick.classList.remove('active');
        if (!val) { msg.textContent = ''; msg.className = 'msg'; return; }
        if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\d]).{8,}$/.test(val)) {
            msg.textContent = 'Password must be 8+ chars, upper, lower, number, special char.';
            msg.className = 'msg error';
        } else {
            msg.textContent = '';
            passwordTick.classList.add('active');
        }
    });
    // Confirm password match and tick
    document.getElementById('confirm_password').addEventListener('input', function(){
        const val = this.value;
        const pw = document.getElementById('signup_password').value;
        const msg = document.getElementById('confirmMsg');
        const confirmTick = document.getElementById('confirmTick');
        confirmTick.classList.remove('active');
        if (!val) { msg.textContent = ''; msg.className = 'msg'; return; }
        if (val !== pw) {
            msg.textContent = 'Passwords do not match';
            msg.className = 'msg error';
        } else {
            msg.textContent = '';
            confirmTick.classList.add('active');
        }
    });
    // Name tick
    document.getElementById('name').addEventListener('input', function(){
        const nameTick = document.getElementById('nameTick');
        if (this.value.trim().length > 1) {
            nameTick.classList.add('active');
        } else {
            nameTick.classList.remove('active');
        }
    });
    // Phone tick
    document.getElementById('phonenumber').addEventListener('input', function(){
        const phoneTick = document.getElementById('phoneTick');
        if (this.value.trim().length > 5) {
            phoneTick.classList.add('active');
        } else {
            phoneTick.classList.remove('active');
        }
    });
    // Sign up form submit
    signupForm.onsubmit = function(e){
        e.preventDefault();
        if (step2.style.display !== 'block') return; // Only submit on step2
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
