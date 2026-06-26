<?php
require_once __DIR__ . '/config.php';
$current_user = current_user();           // 未登入回傳 null
$is_pro = $current_user ? ((int)$current_user['role'] === 1) : false;
$__page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= APP_NAME ?> · 舞蹈深度練習平台</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700;900&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<header class="topbar">
  <a class="brand" href="index.php">
    <span class="brand-mark">DM</span>
    <span class="brand-name">Dance<b>Master</b></span>
  </a>
  <nav class="nav">
    <a href="index.php" class="<?= $__page==='index.php'?'active':'' ?>">社群首頁</a>
    <?php if ($current_user): ?>
      <a href="library.php" class="<?= $__page==='library.php'?'active':'' ?>">我的練習</a>
      <a href="upload.php" class="nav-cta <?= $__page==='upload.php'?'active':'' ?>">＋ <?= $is_pro ? '上傳教學' : '上傳要練的影片' ?></a>
    <?php endif; ?>
  </nav>
  <div class="user-box">
    <?php if ($current_user): ?>
      <div class="user-menu" id="userMenu">
        <button class="user-trigger" id="userTrigger" type="button">
          <span class="avatar sm" style="background:<?= e($current_user['avatar_color'] ?? '#01696f') ?>"><?= e(avatar_initial($current_user['name'])) ?></span>
          <span class="user-name"><?= e($current_user['name']) ?></span>
          <?php if ($is_pro): ?><i class="mini-badge">✔</i><?php endif; ?>
          <span class="caret">▾</span>
        </button>
        <div class="user-dropdown" id="userDropdown" hidden>
          <a href="profile.php">👤 我的個人頁</a>
          <a href="library.php">🎬 我的練習</a>
          <?php if (!$is_pro): ?><a href="apply_pro.php">⭐ 申請成為舞者</a><?php endif; ?>
          <a href="logout.php" class="danger">↩ 登出</a>
        </div>
      </div>
    <?php else: ?>
      <a href="login.php" class="btn-primary sm">登入</a>
    <?php endif; ?>
  </div>
</header>
<script>
(function(){
  var t=document.getElementById('userTrigger'),d=document.getElementById('userDropdown');
  if(!t||!d)return;
  t.addEventListener('click',function(e){e.stopPropagation();d.hidden=!d.hidden;});
  document.addEventListener('click',function(){d.hidden=true;});
})();
</script>
<main class="container">
