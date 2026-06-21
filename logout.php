<?php
require_once 'db.php';
if (isset($_SESSION['counsellor_id'])) {
    $id = (int)$_SESSION['counsellor_id'];
    $stmt = $con->prepare('UPDATE counsellors SET is_online = 0, last_seen = NOW() WHERE counsellor_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}
session_destroy();
header('Location: login.php');
exit;
