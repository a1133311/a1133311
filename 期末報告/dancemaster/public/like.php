<?php
/** 按讚切換 API：POST {id} */
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'msg'=>'method not allowed']); exit; }
$user = current_user();
if (!$user) { http_response_code(401); echo json_encode(['ok'=>false,'msg'=>'請先登入']); exit; }
$id = (int)($_POST['id'] ?? 0);
$videos = load_videos();
if (!find_video($videos, $id)) { http_response_code(404); echo json_encode(['ok'=>false,'msg'=>'video not found']); exit; }
$r = toggle_like($user['id'], $id);
echo json_encode(['ok'=>true,'liked'=>$r['liked'],'count'=>$r['count']], JSON_UNESCAPED_UNICODE);
