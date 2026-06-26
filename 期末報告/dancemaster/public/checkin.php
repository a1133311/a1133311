<?php
require_once __DIR__ . '/../includes/config.php';
require_login();

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'msg' => 'method not allowed']);
    exit;
}

$user = current_user();
$video_id = (int)($_POST['id'] ?? 0);

if (!$user || !$video_id) {
    echo json_encode(['ok' => false, 'msg' => 'bad request']);
    exit;
}

try {
    $count = bump_practice($video_id);

    $stmt = db()->prepare("
        INSERT INTO checkins (user_id, video_id, checked_at)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute([(int)$user['id'], $video_id]);

    echo json_encode([
        'ok' => true,
        'count' => (int)$count
    ]);
} catch (Throwable $e) {
    echo json_encode([
        'ok' => false,
        'msg' => 'db error'
    ]);
}