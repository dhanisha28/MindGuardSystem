<?php
require_once 'auth.php';
header('Content-Type: application/json');

$counsellorId = (int)$_SESSION['counsellor_id'];
$threadId = (int)($_POST['thread_id'] ?? 0);
$message = trim($_POST['message'] ?? '');

if ($threadId <= 0 || $message === '') {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$stmt = $con->prepare('SELECT user_id, assigned_counsellor_id FROM live_chat_threads WHERE thread_id = ? LIMIT 1');
$stmt->bind_param('i', $threadId);
$stmt->execute();
$res = $stmt->get_result();
$thread = $res->fetch_assoc();
$stmt->close();

if (!$thread) {
    echo json_encode(['success' => false, 'message' => 'Thread not found']);
    exit;
}

if ((int)$thread['assigned_counsellor_id'] !== $counsellorId) {
    echo json_encode(['success' => false, 'message' => 'You are not assigned to this chat']);
    exit;
}

$userId = (int)$thread['user_id'];

$stmt = $con->prepare("INSERT INTO live_chat_messages (thread_id, sender_type, sender_id, message_text, status, created_at)
                       VALUES (?, 'counsellor', ?, ?, 'sent', NOW())");
$stmt->bind_param('iis', $threadId, $counsellorId, $message);
$ok = $stmt->execute();
$messageId = (int)$stmt->insert_id;
$stmt->close();

if ($ok) {
    $stmt = $con->prepare('INSERT INTO live_chat_notifications (user_id, thread_id, message_id, is_read, created_at) VALUES (?, ?, ?, 0, NOW())');
    $stmt->bind_param('iii', $userId, $threadId, $messageId);
    $stmt->execute();
    $stmt->close();

    $stmt = $con->prepare("UPDATE live_chat_threads SET last_message_at = NOW(), updated_at = NOW(), status = 'active' WHERE thread_id = ?");
    $stmt->bind_param('i', $threadId);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['success' => $ok]);
