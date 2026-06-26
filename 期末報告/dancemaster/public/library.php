<?php
require_once __DIR__ . '/../includes/config.php';
require_login();
$current_user = current_user();
$is_pro = (int)$current_user['role'] === 1;
$videos = load_videos();

// 我的私人練習影片
$mine = get_my_private_videos($videos, $current_user['id']);
// 若是舞者，另外列出自己發布的公開教學
$myPublic = array_values(array_filter($videos, fn($v) =>
    ($v['visibility'] ?? 'public') === 'public' && (int)($v['owner_id'] ?? 0) === (int)$current_user['id']));

include __DIR__ . '/../includes/header.php';
?>
<section class="lib">
  <div class="lib-head">
    <span class="eyebrow">我的練習</span>
    <h1>我的影片庫</h1>
    <p class="lead">這裡是你自己上傳、只給自己練的私人影片。點任一支即可進入練習工作台。</p>
  </div>

  <div class="lib-section">
    <div class="lib-section-head">
      <h2>🔒 私人練習影片</h2>
      <a class="btn-ghost sm" href="upload.php">＋ 上傳要練的影片</a>
    </div>
    <?php if (!$mine): ?>
      <div class="empty">
        <div class="empty-ic">🎬</div>
        <p>你還沒上傳任何私人練習影片。</p>
        <a class="btn-primary" href="upload.php">上傳第一支影片</a>
      </div>
    <?php else: ?>
      <div class="rank-grid">
        <?php foreach ($mine as $v): ?>
          <a class="rank-card" href="practice.php?id=<?= $v['id'] ?>">
            <span class="card-tag priv">🔒 私人</span>
            <div class="rank-cover" style="background-image:url('<?= htmlspecialchars($v['cover'] ?: 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=600&q=70') ?>')"></div>
            <div class="rank-info">
              <h3><?= htmlspecialchars($v['title']) ?></h3>
              <div class="rank-meta">
                <span class="author"><?= $v['source'] === 'mp4' ? '上傳檔案' : 'YouTube' ?></span>
                <span class="count">▶ 開始練</span>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($is_pro): ?>
  <div class="lib-section">
    <div class="lib-section-head">
      <h2>🌐 我發布的公開教學</h2>
      <a class="btn-ghost sm" href="upload.php">＋ 發布新教學</a>
    </div>
    <?php if (!$myPublic): ?>
      <div class="empty"><p>你還沒發布任何公開教學。</p></div>
    <?php else: ?>
      <div class="rank-grid">
        <?php foreach ($myPublic as $v): ?>
          <a class="rank-card" href="practice.php?id=<?= $v['id'] ?>">
            <span class="card-tag pub">🌐 公開</span>
            <div class="rank-cover" style="background-image:url('<?= htmlspecialchars($v['cover']) ?>')"></div>
            <div class="rank-info">
              <h3><?= htmlspecialchars($v['title']) ?></h3>
              <div class="rank-meta">
                <span class="author">已公開發布</span>
                <span class="count">🔥 <?= $v['practice_count'] ?> 次</span>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
