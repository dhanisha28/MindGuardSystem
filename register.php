<?php
require_once 'db.php';

$error = '';
$success = '';

$full_name = '';
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if ($full_name === '' || $username === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'Please fill in all fields.';
    } elseif (!preg_match("/^[A-Za-z\s.'-]{3,100}$/", $full_name)) {
        $error = 'Full name must be at least 3 characters and contain only letters, spaces, dot, apostrophe or hyphen.';
    } elseif (!preg_match("/^[A-Za-z0-9_]{4,30}$/", $username)) {
        $error = 'Username must be 4-30 characters and contain only letters, numbers or underscore.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $error = 'Password must contain at least one lowercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain at least one number.';
    } elseif (!preg_match('/[\W_]/', $password)) {
        $error = 'Password must contain at least one special character.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $con->prepare('SELECT counsellor_id FROM counsellors WHERE username = ? OR email = ? LIMIT 1');
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->fetch_assoc()) {
            $error = 'Username or email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt2 = $con->prepare('
                INSERT INTO counsellors 
                (username, password, full_name, email, is_online, last_seen) 
                VALUES (?, ?, ?, ?, 0, NOW())
            ');
            $stmt2->bind_param('ssss', $username, $hash, $full_name, $email);

            if ($stmt2->execute()) {
                $success = 'Registration successful. You can login now.';
                $full_name = '';
                $username = '';
                $email = '';
            } else {
                $error = 'Registration failed.';
            }

            $stmt2->close();
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Counsellor Register</title>
    <style>
        body{
            margin:0;
            font-family:Arial,sans-serif;
            background:linear-gradient(135deg,#64C7F2,#A9E5FF);
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .card{
            width:440px;
            background:#fff;
            border-radius:24px;
            box-shadow:0 12px 40px rgba(0,0,0,.15);
            padding:28px;
        }

        h2{
            text-align:center;
            color:#009CFB;
            margin-bottom:18px;
        }

        input{
            width:100%;
            padding:13px 14px;
            border:1px solid #d5eaf5;
            border-radius:14px;
            margin-bottom:12px;
            font-size:15px;
            box-sizing:border-box;
            outline:none;
        }

        input:focus{
            border-color:#009CFB;
            box-shadow:0 0 0 3px rgba(0,156,251,.12);
        }

        .password-wrap{
            position:relative;
        }

        .password-wrap input{
            padding-right:46px;
        }

        .toggle-password{
            position:absolute;
            right:14px;
            top:13px;
            cursor:pointer;
            font-size:17px;
            user-select:none;
        }

        button{
            width:100%;
            padding:13px;
            border:none;
            border-radius:14px;
            background:#009CFB;
            color:#fff;
            font-weight:bold;
            font-size:16px;
            cursor:pointer;
            margin-top:4px;
        }

        button:hover{
            background:#0087da;
        }

        .msg{
            padding:12px;
            border-radius:12px;
            margin-bottom:12px;
            font-size:14px;
        }

        .err{
            background:#FFE3E3;
            color:#B00020;
        }

        .ok{
            background:#E5FFF0;
            color:#0F8A4B;
        }

        .hint{
            font-size:12px;
            color:#5d6f7a;
            margin:-5px 0 12px;
            line-height:1.4;
        }

        a{
            color:#009CFB;
            text-decoration:none;
            font-weight:bold;
        }

        .bottom{
            text-align:center;
            margin-top:15px;
            font-size:14px;
        }
    </style>
</head>
<body>
<div class="card">
    <h2>MindGuard Counsellor Register</h2>

    <?php if($error): ?>
        <div class="msg err"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="msg ok"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" onsubmit="return validateForm()">
        <input 
            type="text" 
            name="full_name" 
            id="full_name"
            placeholder="Full name" 
            value="<?= htmlspecialchars($full_name) ?>"
            required
        >

        <input 
            type="text" 
            name="username" 
            id="username"
            placeholder="Username" 
            value="<?= htmlspecialchars($username) ?>"
            required
        >

        <input 
            type="email" 
            name="email" 
            id="email"
            placeholder="Email" 
            value="<?= htmlspecialchars($email) ?>"
            required
        >

        <div class="password-wrap">
            <input 
                type="password" 
                name="password" 
                id="password"
                placeholder="Password" 
                required
            >
            <span class="toggle-password" onclick="togglePassword('password', this)">👁️</span>
        </div>

        <div class="password-wrap">
            <input 
                type="password" 
                name="confirm_password" 
                id="confirm_password"
                placeholder="Confirm password" 
                required
            >
            <span class="toggle-password" onclick="togglePassword('confirm_password', this)">👁️</span>
        </div>

        <button type="submit">Register</button>
    </form>

    <div class="bottom">
        Already have an account? <a href="login.php">Login</a>
    </div>
</div>

<script>
function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);

    if (input.type === "password") {
        input.type = "text";
        icon.textContent = "🙈";
    } else {
        input.type = "password";
        icon.textContent = "👁️";
    }
}

function validateForm() {
    const fullName = document.getElementById('full_name').value.trim();
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;

    const namePattern = /^[A-Za-z\s.'-]{3,100}$/;
    const usernamePattern = /^[A-Za-z0-9_]{4,30}$/;
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!namePattern.test(fullName)) {
        alert("Full name must be at least 3 characters and contain only letters, spaces, dot, apostrophe or hyphen.");
        return false;
    }

    if (!usernamePattern.test(username)) {
        alert("Username must be 4-30 characters and contain only letters, numbers or underscore.");
        return false;
    }

    if (!emailPattern.test(email)) {
        alert("Please enter a valid email address.");
        return false;
    }

    if (password.length < 8) {
        alert("Password must be at least 8 characters.");
        return false;
    }

    if (!/[A-Z]/.test(password)) {
        alert("Password must contain at least one uppercase letter.");
        return false;
    }

    if (!/[a-z]/.test(password)) {
        alert("Password must contain at least one lowercase letter.");
        return false;
    }

    if (!/[0-9]/.test(password)) {
        alert("Password must contain at least one number.");
        return false;
    }

    if (!/[\W_]/.test(password)) {
        alert("Password must contain at least one special character.");
        return false;
    }

    if (password !== confirm) {
        alert("Passwords do not match.");
        return false;
    }

    return true;
}
</script>
</body>
</html>