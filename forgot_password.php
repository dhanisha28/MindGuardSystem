<?php
require_once 'db.php';

$error = '';
$success = '';

$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if ($username === '' || $email === '' || $new_password === '' || $confirm_password === '') {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($new_password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $error = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $new_password)) {
        $error = 'Password must contain at least one lowercase letter.';
    } elseif (!preg_match('/[0-9]/', $new_password)) {
        $error = 'Password must contain at least one number.';
    } elseif (!preg_match('/[\W_]/', $new_password)) {
        $error = 'Password must contain at least one special character.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $con->prepare("SELECT counsellor_id FROM counsellors WHERE username = ? AND email = ? LIMIT 1");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        if (!$row) {
            $error = 'No account found with this username and email.';
        } else {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $counsellor_id = (int)$row['counsellor_id'];

            $stmt2 = $con->prepare("UPDATE counsellors SET password = ? WHERE counsellor_id = ?");
            $stmt2->bind_param("si", $hash, $counsellor_id);

            if ($stmt2->execute()) {
                $success = 'Password updated successfully. You can login now.';
                $username = '';
                $email = '';
            } else {
                $error = 'Failed to update password.';
            }

            $stmt2->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
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
            width:420px;
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

        .bottom{
            text-align:center;
            margin-top:15px;
            font-size:14px;
        }

        a{
            color:#009CFB;
            text-decoration:none;
            font-weight:bold;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Reset Password</h2>

    <?php if($error): ?>
        <div class="msg err"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="msg ok"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post">
        <input 
            type="text" 
            name="username" 
            placeholder="Username" 
            value="<?= htmlspecialchars($username) ?>"
            required
        >

        <input 
            type="email" 
            name="email" 
            placeholder="Registered Email" 
            value="<?= htmlspecialchars($email) ?>"
            required
        >

        <div class="password-wrap">
            <input 
                type="password" 
                name="new_password" 
                id="new_password"
                placeholder="New Password" 
                required
            >
            <span class="toggle-password" onclick="togglePassword('new_password', this)">👁️</span>
        </div>

        <div class="password-wrap">
            <input 
                type="password" 
                name="confirm_password" 
                id="confirm_password"
                placeholder="Confirm New Password" 
                required
            >
            <span class="toggle-password" onclick="togglePassword('confirm_password', this)">👁️</span>
        </div>

        <button type="submit">Update Password</button>
    </form>

    <div class="bottom">
        Remember password? <a href="login.php">Login</a>
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
</script>

</body>
</html>