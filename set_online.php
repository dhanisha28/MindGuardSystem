<?php
require_once 'auth.php';
header('Content-Type: application/json');

$counsellorId = (int)$_SESSION['counsellor_id'];
$isOnline = isset($_POST['is_online']) ? (int)$_POST['is_online'] : 1;
$isOnline = $isOnline === 1 ? 1 : 0;

$stmt = $con->prepare("
    UPDATE counsellors
    SET is_online = ?, last_seen = NOW()
    WHERE counsellor_id = ?
");
$stmt->bind_param('ii', $isOnline, $counsellorId);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => $isOnline ? 'Counsellor is now online' : 'Counsellor is now offline'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update status'
    ]);
}
$stmt->close();