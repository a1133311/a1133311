<?php
/** Demo 贊助 API：POST {to, amount}（不接金流，純記錄） */
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'msg'=>'method not allowed']); exit; }
$user = current_user();
if (!$user) { http_response_code(401); echo json_encode(['ok'=>false,'msg'=>'請先登入']); exit; }
$to = (int)($_POST['to'] ?? 0);
$amount = (int)($_POST['amount'] ?? 0);
$target = find_user($to);
if (!$target) { http_response_code(404); echo json_encode(['ok'=>false,'msg'=>'user not found']); exit; }
if ($amount <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>'金額需大於 0']); exit; }
$total = add_sponsor($user['id'], $to, $amount);
echo json_encode(['ok'=>true,'total'=>$total,'amount'=>$amount,'to_name'=>$target['name']], JSON_UNESCAPED_UNICODE);
