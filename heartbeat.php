<?php
require_once 'auth.php';
header('Content-Type: application/json');

$id = (int)$_SESSION['counsellor_id'];

$stmt = $con->prepare("
    UPDATE counsellors 
    SET is_online = 1, last_seen = NOW() 
    WHERE counsellor_id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);