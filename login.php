<?php
require_once 'db.php';

if (isset($_SESSION['counsellor_id'])) {
    header('Location: chat.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $con->prepare('SELECT * FROM counsellors WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if ($row && password_verify($password, $row['password'])) {
        $_SESSION['counsellor_id'] = (int)$row['counsellor_id'];
        $_SESSION['counsellor_name'] = $row['full_name'];

        $stmt = $con->prepare('UPDATE counsellors SET is_online = 1, last_seen = NOW() WHERE counsellor_id = ?');
        $stmt->bind_param('i', $_SESSION['counsellor_id']);
        $stmt->execute();
        $stmt->close();

        header('Location: chat.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Counsellor Login</title>
    <style>
        body{margin:0;font-family:Arial,sans-serif;background:linear-gradient(135deg,#64C7F2,#A9E5FF);min-height:100vh;display:flex;align-items:center;justify-content:center}
        .card{width:400px;background:#fff;border-radius:24px;box-shadow:0 12px 40px rgba(0,0,0,.15);padding:28px}
        h2{text-align:center;color:#009CFB;margin-bottom:18px}
        input{width:100%;padding:13px 14px;border:1px solid #d5eaf5;border-radius:14px;margin-bottom:12px;font-size:15px;box-sizing:border-box}
        button{width:100%;padding:13px;border:none;border-radius:14px;background:#009CFB;color:#fff;font-weight:bold;font-size:16px;cursor:pointer}
        .msg{padding:12px;border-radius:12px;margin-bottom:12px;background:#FFE3E3;color:#B00020}
        a{color:#009CFB;text-decoration:none;font-weight:bold}
        .bottom{text-align:center;margin-top:15px}
        .password-wrap{position:relative;}
        .password-wrap input{padding-right:46px;}
        .toggle-password{position:absolute;right:14px;top:10px;cursor:pointer;font-size:17px;user-select:none;}
    </style>
</head>

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

<body>
<div class="card">
    <h2>MindGuard Counsellor Login</h2>
    <?php if($error): ?><div class="msg"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required>
        <div class="password-wrap">
            <input type="password" name="password" id="password" placeholder="Password" required>
            <span class="toggle-password" onclick="togglePassword('password', this)">👁️</span>
        </div>
        <button type="submit">Login</button>
    </form>
    <div class="bottom"><a href="forgot_password.php">Forgot Password</a></div>
    <div class="bottom">Don't have an account? <a href="register.php">Register</a></div>
</div>
</body>
</html>
