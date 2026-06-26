<?php
require_once __DIR__ . '/../includes/config.php';
require_login();
$me = current_user();
$alreadyPro = (int)$me['role'] === 1;
$done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$alreadyPro) {
    if (upgrade_to_pro($me['id'])) {
        $done = true;
        $me = current_user(); // 重新讀取最新角色
    }
}

include __DIR__ . '/../includes/header.php';
?>
<section class="apply-wrap">
  <div class="apply-card">
    <span class="eyebrow">舞者認證</span>
    <h1>申請成為認證舞者</h1>

    <?php if ($alreadyPro && !$done): ?>
      <div class="alert ok slim">你已經是認證舞者了 ✔</div>
      <p class="apply-lead">你可以上傳公開教學影片，分享給整個社群。</p>
      <a class="btn-primary lg" href="upload.php">＋ 立即上傳教學</a>

    <?php elseif ($done): ?>
      <div class="apply-success">
        <div class="success-ic">🎉</div>
        <h2>恭喜！你已成為認證舞者</h2>
        <p>現在你可以上傳<b>公開教學影片</b>，出現在社群動態與排行榜，並接受其他人的訂閱與贊助。</p>
        <div class="apply-cta">
          <a class="btn-primary lg" href="upload.php">＋ 上傳第一支教學</a>
          <a class="btn-ghost" href="profile.php">查看我的個人頁</a>
        </div>
      </div>

    <?php else: ?>
      <p class="apply-lead">成為認證舞者後，你發布的影片會<b>公開</b>到社群動態牆與熱門排行榜，所有學員都能練習、按讚、留言與訂閱你。</p>
      <ul class="apply-benefits">
        <li>🌐 上傳公開教學影片（一般學員只能上傳私人練習）</li>
        <li>⭐ 個人頁顯示認證徽章與訂閱者數</li>
        <li>💛 接受社群贊助（Demo）</li>
        <li>🔥 進入首頁熱門練習排行榜</li>
      </ul>
      <div class="apply-note">這是課堂專題 Demo，按下後即時通過（不需審核）。</div>
      <form method="post">
        <button type="submit" class="btn-primary lg full">⭐ 一鍵申請成為舞者</button>
      </form>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
