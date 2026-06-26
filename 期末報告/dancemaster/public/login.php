<?php
require_once __DIR__ . '/../includes/config.php';

// 已登入就直接導向首頁（或 next）
$next = $_GET['next'] ?? ($_POST['next'] ?? 'index.php');
// 只允許站內相對路徑，避免 open redirect
if (!preg_match('~^[a-zA-Z0-9_./?=&%-]+$~', $next) || str_starts_with($next, 'http')) {
    $next = 'index.php';
}
if (is_logged_in()) {
    header('Location: ' . $next);
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        $error = '請輸入一個名稱來登入。';
    } elseif (mb_strlen($name) > 20) {
        $error = '名稱請控制在 20 個字以內。';
    } else {
        $user = login_or_create($name);
        if ($user) {
            $_SESSION['uid'] = $user['id'];
            header('Location: ' . $next);
            exit;
        }
        $error = '登入失敗，請再試一次。';
    }
}

$seed = load_users(); // 顯示既有帳號方便 Demo 快速登入
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>登入 · <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700;900&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="auth-body">
<div class="auth-wrap">
  <div class="auth-card">
    <a class="brand auth-brand" href="index.php">
      <span class="brand-mark">DM</span>
      <span class="brand-name">Dance<b>Master</b></span>
    </a>
    <h1 class="auth-title">登入練舞社群</h1>
    <p class="auth-sub">輸入一個名稱即可開始。登入後可上傳練習影片、留言、訂閱舞者，並申請成為認證舞者。</p>

    <?php if ($error): ?>
      <div class="alert err slim"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" class="auth-form">
      <input type="hidden" name="next" value="<?= e($next) ?>">
      <div class="field">
        <label>你的名稱</label>
        <input type="text" name="name" placeholder="例：采源 Chaewon" maxlength="20" autofocus required>
      </div>
      <button type="submit" class="btn-primary lg full">登入 / 註冊</button>
    </form>

    <div class="auth-quick">
      <span class="quick-label">Demo 快速登入：</span>
      <div class="quick-chips">
        <?php foreach ($seed as $u): ?>
          <form method="post" class="quick-form">
            <input type="hidden" name="next" value="<?= e($next) ?>">
            <input type="hidden" name="name" value="<?= e($u['name']) ?>">
            <button type="submit" class="quick-chip" style="--qc:<?= e($u['avatar_color']) ?>">
              <span class="qc-av" style="background:<?= e($u['avatar_color']) ?>"><?= e(avatar_initial($u['name'])) ?></span>
              <?= e($u['name']) ?><?php if ($u['role']===1): ?> <i class="mini-badge">✔</i><?php endif; ?>
            </button>
          </form>
        <?php endforeach; ?>
      </div>
    </div>
    <p class="auth-note">這是課堂專題 Demo，採用簡易登入（不需密碼）。</p>
  </div>
</div>
</body>
</html>
