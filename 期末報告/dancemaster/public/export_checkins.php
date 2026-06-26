<?php
require_once __DIR__ . '/../includes/config.php';
require_login();

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="checkins.csv"');

$out = fopen('php://output', 'w');

fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($out, ['打卡ID', '使用者ID', '使用者名稱', '影片ID', '影片標題', '打卡時間', '打卡日期']);

$stmt = db()->query("
    SELECT
        c.id,
        c.user_id,
        u.name AS user_name,
        c.video_id,
        v.title AS video_title,
        c.checked_at,
        DATE(c.checked_at) AS checkin_date
    FROM checkins c
    JOIN users u ON c.user_id = u.id
    JOIN videos v ON c.video_id = v.id
    ORDER BY c.checked_at DESC
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($out, [
        $row['id'],
        $row['user_id'],
        $row['user_name'],
        $row['video_id'],
        $row['video_title'],
        $row['checked_at'],
        $row['checkin_date']
    ]);
}

fclose($out);
exit;