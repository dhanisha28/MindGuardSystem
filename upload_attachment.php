<?php
require_once 'auth.php';
header('Content-Type: application/json');

$counsellorId = (int)$_SESSION['counsellor_id'];
$threadId = (int)($_POST['thread_id'] ?? 0);
if ($threadId <= 0 || !isset($_FILES['attachment'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid upload']);
    exit;
}

$stmt = $con->prepare('SELECT user_id, assigned_counsellor_id FROM live_chat_threads WHERE thread_id = ? LIMIT 1');
$stmt->bind_param('i', $threadId);
$stmt->execute();
$res = $stmt->get_result();
$thread = $res->fetch_assoc();
$stmt->close();

if (!$thread || (int)$thread['assigned_counsellor_id'] !== $counsellorId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$dir = dirname(__DIR__) . '/uploads/live_chat/';
if (!is_dir($dir)) mkdir($dir, 0777, true);

$original = $_FILES['attachment']['name'];
$tmp = $_FILES['attachment']['tmp_name'];
$ext = pathinfo($original, PATHINFO_EXTENSION);
$safe = 'counsellor_' . $counsellorId . '_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
$target = $dir . $safe;

if (!move_uploaded_file($tmp, $target)) {
    echo json_encode(['success' => false, 'message' => 'Upload failed']);
    exit;
}

$relative = 'uploads/live_chat/' . $safe;
$type = mime_content_type($target) ?: 'application/octet-stream';
$userId = (int)$thread['user_id'];

$stmt = $con->prepare("INSERT INTO live_chat_messages (thread_id, sender_type, sender_id, message_text, attachment_name, attachment_path, attachment_type, status, created_at)
                       VALUES (?, 'counsellor', ?, '', ?, ?, ?, 'sent', NOW())");
$stmt->bind_param('iisss', $threadId, $counsellorId, $original, $relative, $type);
$stmt->execute();
$messageId = (int)$stmt->insert_id;
$stmt->close();

$stmt = $con->prepare('INSERT INTO live_chat_notifications (user_id, thread_id, message_id, is_read, created_at) VALUES (?, ?, ?, 0, NOW())');
$stmt->bind_param('iii', $userId, $threadId, $messageId);
$stmt->execute();
$stmt->close();

$stmt = $con->prepare("UPDATE live_chat_threads SET last_message_at = NOW(), updated_at = NOW(), status = 'active' WHERE thread_id = ?");
$stmt->bind_param('i', $threadId);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);
