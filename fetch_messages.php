<?php
require_once 'auth.php';
header('Content-Type: application/json');

$counsellorId = (int)$_SESSION['counsellor_id'];
$threadId = (int)($_GET['thread_id'] ?? 0);
if ($threadId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid thread']);
    exit;
}

// assign if free
$stmt = $con->prepare('SELECT assigned_counsellor_id FROM live_chat_threads WHERE thread_id = ? LIMIT 1');
$stmt->bind_param('i', $threadId);
$stmt->execute();
$res = $stmt->get_result();
$thread = $res->fetch_assoc();
$stmt->close();

if (!$thread) {
    echo json_encode(['success' => false, 'message' => 'Thread not found']);
    exit;
}

$currentAssigned = (int)($thread['assigned_counsellor_id'] ?? 0);
if ($currentAssigned === 0) {
    $stmt = $con->prepare("UPDATE live_chat_threads SET assigned_counsellor_id = ?, status = 'active', updated_at = NOW() WHERE thread_id = ? AND assigned_counsellor_id IS NULL");
    $stmt->bind_param('ii', $counsellorId, $threadId);
    $stmt->execute();
    $stmt->close();
} elseif ($currentAssigned !== $counsellorId) {
    echo json_encode(['success' => false, 'message' => 'This chat is currently handled by another counsellor.']);
    exit;
}

$stmt = $con->prepare('SELECT * FROM live_chat_messages WHERE thread_id = ? ORDER BY message_id ASC');
$stmt->bind_param('i', $threadId);
$stmt->execute();
$res = $stmt->get_result();
$messages = [];
while ($row = $res->fetch_assoc()) {
    $row['attachment_url'] = !empty($row['attachment_path'])
        ? ('http://' . $_SERVER['HTTP_HOST'] . '/mindguardapp_api/' . $row['attachment_path'])
        : null;
    $messages[] = $row;
}
$stmt->close();

$stmt = $con->prepare("UPDATE live_chat_messages SET status = 'seen', seen_at = NOW() WHERE thread_id = ? AND sender_type = 'user' AND status = 'sent'");
$stmt->bind_param('i', $threadId);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true, 'messages' => $messages]);
