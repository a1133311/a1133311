<?php
require_once __DIR__ . '/../includes/config.php';
require_login();                       // 預設需登入
$current_user = current_user();
$uid = (int)$current_user['id'];
$videos = load_videos();
$feed = get_feed($videos);
$top = get_top_videos($videos, 3);
include __DIR__ . '/../includes/header.php';
?>
<section class="hero compact">
  <div class="hero-text">
    <span class="eyebrow">舞蹈練習社群</span>
    <h1>歡迎回來，<?= e($current_user['name']) ?> 👋</h1>
    <p class="hero-sub">看看舞者最新的教學動態 — 按讚、分享、訂閱喜歡的舞者，點開任一支即可用精細倍速、一鍵鏡面與 A/B 段落循環深度練習。</p>
  </div>
  <div class="hero-features">
    <div class="feat"><span>0.3x–1.0x</span><small>精細倍速</small></div>
    <div class="feat"><span>Mirror</span><small>一鍵鏡面</small></div>
    <div class="feat"><span>A ⇆ B</span><small>段落循環</small></div>
  </div>
</section>

<div class="home-grid">
  <!-- ===== 動態牆 ===== -->
  <section class="feed">
    <h2 class="feed-title">🎬 最新教學動態</h2>
    <?php foreach ($feed as $v):
        $author = find_user($v['owner_id'] ?? 0);
        $liked = user_liked($uid, $v['id']);
        $likeCount = (int)($v['likes'] ?? 0);
        $isOwner = (int)($v['owner_id'] ?? 0) === $uid;
        $subbed = $author ? user_subscribed($uid, $author['id']) : false;
        $subCount = $author ? count_subscribers($author['id']) : 0;
    ?>
      <article class="post-card">
        <div class="post-head">
          <a class="post-author" href="profile.php?id=<?= (int)($v['owner_id'] ?? 0) ?>">
            <span class="avatar" style="background:<?= e($author['avatar_color'] ?? '#01696f') ?>"><?= e(avatar_initial($v['author'])) ?></span>
            <span class="pa-meta">
              <span class="pa-name"><?= e($v['author']) ?><?php if (($v['author_role'] ?? 0)==1): ?> <i class="mini-badge">✔</i><?php endif; ?></span>
              <span class="pa-date"><?= e($v['created']) ?> · 🔥 <?= (int)$v['practice_count'] ?> 次打卡</span>
            </span>
          </a>
          <?php if ($author && !$isOwner): ?>
            <button class="btn-sub<?= $subbed?' on':'' ?>" data-target="<?= (int)$author['id'] ?>" data-act="subscribe">
              <span class="sub-label"><?= $subbed ? '已訂閱' : '＋ 訂閱' ?></span>
            </button>
          <?php endif; ?>
        </div>

        <a class="post-cover" href="practice.php?id=<?= (int)$v['id'] ?>" style="background-image:url('<?= e($v['cover'] ?: 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=800&q=70') ?>')">
          <span class="play-overlay">▶ 進入練習</span>
        </a>

        <div class="post-body">
          <h3><a href="practice.php?id=<?= (int)$v['id'] ?>"><?= e($v['title']) ?></a></h3>
          <?php if (!empty($v['desc'])): ?><p class="post-desc"><?= e($v['desc']) ?></p><?php endif; ?>
        </div>

        <div class="post-actions">
          <button class="act-btn like<?= $liked?' on':'' ?>" data-id="<?= (int)$v['id'] ?>" data-act="like">
            <span class="ic"><?= $liked ? '❤' : '🤍' ?></span>
            <span class="like-count"><?= $likeCount ?></span>
          </button>
          <button class="act-btn share" data-url="practice.php?id=<?= (int)$v['id'] ?>" data-title="<?= e($v['title']) ?>" data-act="share">
            <span class="ic">↗</span> 分享
          </button>
          <a class="act-btn" href="practice.php?id=<?= (int)$v['id'] ?>#comments">💬 留言</a>
        </div>
      </article>
    <?php endforeach; ?>
  </section>

  <!-- ===== 側欄 ===== -->
  <aside class="side">
    <div class="side-card">
      <h3>🔥 熱門練習 TOP 3</h3>
      <ol class="side-rank">
        <?php foreach ($top as $i=>$v): ?>
          <li>
            <a href="practice.php?id=<?= (int)$v['id'] ?>">
              <span class="sr-num">#<?= $i+1 ?></span>
              <span class="sr-info">
                <span class="sr-title"><?= e($v['title']) ?></span>
                <span class="sr-meta"><?= e($v['author']) ?> · 🔥 <?= (int)$v['practice_count'] ?></span>
              </span>
            </a>
          </li>
        <?php endforeach; ?>
      </ol>
    </div>
    <div class="side-card cta-card">
      <h3>想分享你的舞步？</h3>
      <p>申請成為認證舞者，把你的教學公開給整個社群。</p>
      <?php if ($is_pro): ?>
        <a class="btn-primary sm full" href="upload.php">＋ 上傳教學</a>
      <?php else: ?>
        <a class="btn-primary sm full" href="apply_pro.php">⭐ 申請成為舞者</a>
      <?php endif; ?>
    </div>
  </aside>
</div>

<script src="assets/js/social.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
