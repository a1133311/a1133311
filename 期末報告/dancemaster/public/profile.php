<?php
require_once __DIR__ . '/../includes/config.php';
require_login();
$me = current_user();
$uid = (int)$me['id'];

$pid = isset($_GET['id']) ? (int)$_GET['id'] : $uid;
$profile = find_user($pid);
if (!$profile) { http_response_code(404); die('找不到這位使用者'); }

$isSelf = ($pid === $uid);
$isPro  = (int)$profile['role'] === 1;
$videos = load_videos();
$publicVids = get_user_public_videos($videos, $pid);
$privateVids = $isSelf ? get_my_private_videos($videos, $pid) : [];
$subCount = count_subscribers($pid);
$subbed = user_subscribed($uid, $pid);
$sponsored = total_sponsored($pid);

include __DIR__ . '/../includes/header.php';
?>
<section class="profile">
  <div class="profile-card">
    <div class="profile-cover"></div>
    <div class="profile-head">
      <span class="avatar xl" style="background:<?= e($profile['avatar_color'] ?? '#01696f') ?>"><?= e(avatar_initial($profile['name'])) ?></span>
      <div class="ph-row">
        <div class="ph-main">
          <h1>
            <?= e($profile['name']) ?>
            <?php if ($isPro): ?><span class="verified-badge" title="認證舞者">✔</span><?php endif; ?>
          </h1>
          <div class="ph-handle">@<?= e($profile['handle']) ?> · <?= $isPro ? '認證舞者' : '學員' ?> · 加入於 <?= e($profile['created']) ?></div>
          <?php if (!empty($profile['bio'])): ?><p class="ph-bio"><?= e($profile['bio']) ?></p><?php endif; ?>
          <div class="ph-stats">
            <span><b data-sub-count="<?= $pid ?>"><?= $subCount ?></b> 訂閱者</span>
            <span><b><?= count($publicVids) ?></b> 公開教學</span>
            <?php if ($isPro): ?><span class="sponsor-stat">💛 累計贊助 <b><?= $sponsored ?></b></span><?php endif; ?>
          </div>
        </div>
        <div class="ph-actions">
          <?php if ($isSelf): ?>
            <?php if (!$isPro): ?>
              <a class="btn-primary" href="apply_pro.php">⭐ 申請成為舞者</a>
            <?php else: ?>
              <a class="btn-primary" href="upload.php">＋ 上傳教學</a>
            <?php endif; ?>

            <a class="btn-ghost" href="edit_profile.php">✏️ 編輯個人資料</a>
            <a class="btn-ghost" href="library.php">🎬 我的練習庫</a>
            <a class="btn-ghost" href="export_checkins.php">📊 匯出打卡紀錄 CSV</a>
          <?php else: ?>
            <button class="btn-sub lg<?= $subbed?' on':'' ?>" data-target="<?= $pid ?>" data-act="subscribe">
              <span class="sub-label"><?= $subbed ? '已訂閱' : '＋ 訂閱' ?></span>
            </button>
            <?php if ($isPro): ?>
              <button class="btn-ghost" id="btnSponsorProfile" data-to="<?= $pid ?>" data-name="<?= e($profile['name']) ?>">💛 贊助</button>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="profile-body">
    <h2 class="sec-title"><?= $isSelf ? '我發布的公開教學' : '公開教學' ?></h2>
    <?php if (!$publicVids): ?>
      <div class="empty"><div class="empty-ic">🎬</div><p><?= $isSelf ? '你還沒發布任何公開教學。' : '這位使用者還沒有公開教學。' ?></p></div>
    <?php else: ?>
      <div class="rank-grid">
        <?php foreach ($publicVids as $v): ?>
          <a class="rank-card" href="practice.php?id=<?= (int)$v['id'] ?>">
            <div class="rank-cover" style="background-image:url('<?= e($v['cover'] ?: 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=600&q=70') ?>')"></div>
            <div class="rank-info">
              <h3><?= e($v['title']) ?></h3>
              <div class="rank-meta">
                <span class="author">❤ <?= (int)($v['likes'] ?? 0) ?></span>
                <span class="count">🔥 <?= (int)$v['practice_count'] ?> 次</span>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($isSelf && $privateVids): ?>
      <h2 class="sec-title">🔒 我的私人練習（只有你看得到）</h2>
      <div class="rank-grid">
        <?php foreach ($privateVids as $v): ?>
          <a class="rank-card" href="practice.php?id=<?= (int)$v['id'] ?>">
            <span class="card-tag priv">🔒 私人</span>
            <div class="rank-cover" style="background-image:url('<?= e($v['cover'] ?: 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=600&q=70') ?>')"></div>
            <div class="rank-info"><h3><?= e($v['title']) ?></h3><div class="rank-meta"><span class="count">▶ 開始練</span></div></div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- 贊助 Modal（Demo） -->
<div class="modal" id="sponsorModal" hidden>
  <div class="modal-card">
    <button class="modal-x" id="sponsorClose" type="button">✕</button>
    <h3>💛 贊助 <span id="sponsorName"></span></h3>
    <p class="modal-sub">這是 Demo 版贊助，不會真的扣款，僅作為功能展示。</p>
    <div class="amount-pills" id="amountPills">
      <button type="button" data-amt="50">$50</button>
      <button type="button" data-amt="100" class="active">$100</button>
      <button type="button" data-amt="300">$300</button>
      <button type="button" data-amt="500">$500</button>
    </div>
    <button class="btn-primary lg full" id="sponsorConfirm">確認贊助</button>
    <div class="sponsor-result" id="sponsorResult" hidden></div>
  </div>
</div>

<script src="assets/js/social.js"></script>
<script src="assets/js/sponsor.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
