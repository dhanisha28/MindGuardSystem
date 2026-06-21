<?php
require_once 'auth.php';
header('Content-Type: application/json');

$counsellorId = (int)$_SESSION['counsellor_id'];
$threadId = (int)($_POST['thread_id'] ?? 0);

if ($threadId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid thread'
    ]);
    exit;
}

/*
    Make sure this thread belongs to this counsellor
*/
$stmt = $con->prepare("
    SELECT assigned_counsellor_id
    FROM live_chat_threads
    WHERE thread_id = ?
    LIMIT 1
");
$stmt->bind_param('i', $threadId);
$stmt->execute();
$res = $stmt->get_result();
$thread = $res->fetch_assoc();
$stmt->close();

if (!$thread) {
    echo json_encode([
        'success' => false,
        'message' => 'Thread not found'
    ]);
    exit;
}

if ((int)$thread['assigned_counsellor_id'] !== $counsellorId) {
    echo json_encode([
        'success' => false,
        'message' => 'You are not assigned to this chat'
    ]);
    exit;
}

/*
    Mark all user messages in this thread as seen by counsellor
*/
$stmt = $con->prepare("
    UPDATE live_chat_messages
    SET status = 'seen', seen_at = NOW()
    WHERE thread_id = ?
      AND sender_type = 'user'
      AND status = 'sent'
");
$stmt->bind_param('i', $threadId);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Messages marked as seen'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update seen status'
    ]);
}
$stmt->close();