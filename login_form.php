<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'Inter', Arial, Helvetica, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url(icons/wireless-keyboard-mouse-earphones-table.jpg) no-repeat center center fixed;
            background-size: cover;
        }
        .login-container {
            background: #ffffffff;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
            border-radius: 9px;
            padding: 64px 76px 96px 66px;
            min-width: 370px;
            margin-left: 5%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: box-shadow 0.3s;
        }
        .login-header {
            color: #4f2cc8;
            text-align: center;
            font-size: 2.1rem;
            font-weight: bolder;
            font-family: "chiller";
            margin-bottom: 2.2rem;
            letter-spacing: 0.5px;
        }
        .login-form {
            width: 100%;
            display: flex;
            flex-direction: column;
            margin-left: -38px;
            gap: 28px;
        }
        .input-group {
            position: relative;
            margin-bottom: 0.5rem;
        }
        .input-group input {
            width: 90%;
            padding: 18px 48px 18px 18px;
            border: 1.5px solid #e5e7eb;
            border-radius: 28px;
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
        .input-group .eye-icon {
            position: absolute;
            right: 0px;
            top: 50%;
            transform: translateY(-50%);
            width: 26px;
            height: 26px;
            cursor: pointer;
            z-index: 2;
        }
        .login-btn {
            width: 110%;
            padding: 15px 0px;
            margin-bottom: 2rem;
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
        .signup-link {
            display: block;
            text-align: center;
            margin-top: 1.2rem;
            color: #4f2cc8;
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
        }
        .signup-link:hover {
            color: #a5b4fc;
            text-decoration: underline;
        }
        @media (max-width: 414px) and (max-height: 896px) {
            .login-container {
                padding: 48px 32px 64px 32px;
                min-width: 90vw;
                margin-left: 0;
            }
            .login-form {
                margin-left: 0;
            }
            .login-btn {
                width: 104%;
                margin-left: -12px;
            }
            .input-group input {
                width: 87%;
                margin-left: -12px;
            }
        }

        @keyframes ripple-animate {
            from {
                transform: scale(0);
                opacity: 0.7;
            }
            to {
                transform: scale(2.5);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">ELECTROSHOP</div>
        <form class="login-form" id="loginForm" autocomplete="off">
            <div class="input-group">
                <input type="text" id="email_or_number" name="email_or_number" required placeholder=" " autocomplete="off">
                <label for="email_or_number">Email </label>
            </div>
            <div class="input-group">
                <input type="password" id="password" name="password" required placeholder=" " autocomplete="off">
                <label for="password">Password</label>
                <img src="icons/eye.png" alt="Show Password" class="eye-icon" id="togglePassword">
            </div>
            <button type="submit" class="login-btn">Login</button>
        </form>
        <div id="popupMsg" class="popup-msg"></div>
    <style>
        .popup-msg { position: fixed; top: 30px; left: 50%; transform: translateX(-50%); background: #22c55e; color: #fff; padding: 16px 32px; border-radius: 16px; font-size: 1.1rem; font-weight: 600; box-shadow: 0 4px 24px rgba(34,197,94,0.18); display: none; z-index: 999; animation: fadeIn 0.5s; }
        @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
    </style>
        <div class="signup-row">
            <span>Don't have an account?</span>
            <a class="signup-link" id="showSignup" href="#" style="color:#a259f7;text-decoration:none;">Sign up</a>
        </div>
        <div id="signupFrameWrapper" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:999;background:rgba(255,255,255,0.95);align-items:center;justify-content:center;animation:slideIn 0.7s cubic-bezier(.4,0,.2,1);">
            <iframe id="signupFrame" src="signin.php" style="width:500px;max-width:95vw;height:90vh;border:none;border-radius:24px;box-shadow:0 8px 32px 0 rgba(31,38,135,0.18);"></iframe>
        </div>
        <style>@keyframes slideIn { from { opacity:0; transform:translateY(60px);} to { opacity:1; transform:translateY(0);} }</style>
        <script>
        document.getElementById('showSignup').onclick = function(e){
            window.location.href = 'signin.php';
        };
        window.addEventListener('message', function(event) {
            if(event.data==='close-signup') {
                document.getElementById('signupFrameWrapper').style.display='none';
                document.querySelector('.login-container').style.display='flex';
            }
        });
        </script>
    </div>
    <script>
    // Water ripple effect for login button
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
    // Password eye toggle
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    let visible = false;
    togglePassword.addEventListener('click', function() {
        visible = !visible;
        if (visible) {
            passwordInput.type = 'text';
            togglePassword.src = 'icons/eye_close.png';
            togglePassword.alt = 'Hide Password';
        } else {
            passwordInput.type = 'password';
            togglePassword.src = 'icons/eye.png';
            togglePassword.alt = 'Show Password';
        }
    });

    // Login form AJAX
    document.getElementById('loginForm').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('php logics/login_process.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            showPopup(data.message, data.status === 'success');
        });
    };
    function showPopup(msg, success) {
        const popup = document.getElementById('popupMsg');
        popup.textContent = msg;
        popup.style.background = success ? '#22c55e' : '#e11d48';
        popup.style.display = 'block';
        setTimeout(()=>{ popup.style.display = 'none'; }, 4000);
    }
    </script>
</body>
</html>