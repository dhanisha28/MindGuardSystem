<?php
require_once 'auth.php';
header('Content-Type: application/json');

$counsellorId = (int)$_SESSION['counsellor_id'];

// release threads from offline counsellors
$con->query("UPDATE live_chat_threads t
LEFT JOIN counsellors c ON c.counsellor_id = t.assigned_counsellor_id
SET t.assigned_counsellor_id = NULL, t.status = 'waiting'
WHERE t.assigned_counsellor_id IS NOT NULL
  AND (c.counsellor_id IS NULL OR c.is_online = 0 OR c.last_seen < DATE_SUB(NOW(), INTERVAL 20 SECOND))");

$sql = "SELECT t.thread_id, t.user_id, t.assigned_counsellor_id, t.status, t.last_message_at,
               u.username, u.full_name, u.profile_image, u.dob,
               TIMESTAMPDIFF(YEAR, u.dob, CURDATE()) AS age,
               COALESCE(SUM(CASE WHEN m.sender_type='user' AND m.status='sent' THEN 1 ELSE 0 END),0) AS unread_count,
               SUBSTRING_INDEX(MAX(CONCAT(DATE_FORMAT(m.created_at,'%Y-%m-%d %H:%i:%s'),'|||',IFNULL(m.message_text,''))), '|||', -1) AS last_text
        FROM live_chat_threads t
        JOIN users u ON u.user_id = t.user_id
        LEFT JOIN live_chat_messages m ON m.thread_id = t.thread_id
        WHERE t.assigned_counsellor_id IS NULL OR t.assigned_counsellor_id = ?
        GROUP BY t.thread_id
        ORDER BY COALESCE(t.last_message_at, t.created_at) DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param('i', $counsellorId);
$stmt->execute();
$res = $stmt->get_result();
$threads = [];
while ($row = $res->fetch_assoc()) {
    $threads[] = $row;
}
$stmt->close();

echo json_encode(['success' => true, 'threads' => $threads]);
