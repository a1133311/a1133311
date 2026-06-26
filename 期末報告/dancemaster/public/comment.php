<?php
/** 留言 API：POST {video_id, text} → 回傳新留言 HTML 片段需要的資料 */
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'msg'=>'method not allowed']); exit; }
$user = current_user();
if (!$user) { http_response_code(401); echo json_encode(['ok'=>false,'msg'=>'請先登入']); exit; }
$vid = (int)($_POST['video_id'] ?? 0);
$text = trim($_POST['text'] ?? '');
$videos = load_videos();
if (!find_video($videos, $vid)) { http_response_code(404); echo json_encode(['ok'=>false,'msg'=>'video not found']); exit; }
if ($text === '') { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>'留言不能空白']); exit; }
$c = add_comment($vid, $user, $text);
echo json_encode(['ok'=>true,'comment'=>[
    'id'=>$c['id'],
    'user_name'=>$c['user_name'],
    'user_role'=>(int)$c['user_role'],
    'text'=>$c['text'],
    'created'=>$c['created'],
    'initial'=>avatar_initial($c['user_name']),
    'color'=>$user['avatar_color'] ?? '#01696f',
]], JSON_UNESCAPED_UNICODE);
