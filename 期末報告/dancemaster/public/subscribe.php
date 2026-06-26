<?php
/** 訂閱切換 API：POST {target} */
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'msg'=>'method not allowed']); exit; }
$user = current_user();
if (!$user) { http_response_code(401); echo json_encode(['ok'=>false,'msg'=>'請先登入']); exit; }
$target = (int)($_POST['target'] ?? 0);
if (!find_user($target)) { http_response_code(404); echo json_encode(['ok'=>false,'msg'=>'user not found']); exit; }
$r = toggle_subscribe($user['id'], $target);
if (!empty($r['self'])) { echo json_encode(['ok'=>false,'msg'=>'不能訂閱自己','count'=>$r['count']], JSON_UNESCAPED_UNICODE); exit; }
echo json_encode(['ok'=>true,'subscribed'=>$r['subscribed'],'count'=>$r['count']], JSON_UNESCAPED_UNICODE);
