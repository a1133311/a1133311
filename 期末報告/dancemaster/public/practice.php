<?php
require_once __DIR__ . '/../includes/config.php';
require_login();
$current_user = current_user();
$videos = load_videos();
$id = $_GET['id'] ?? 1;
$video = find_video($videos, $id);
if (!$video) { http_response_code(404); die('找不到影片'); }
// 權限：私人影片只有 owner 看得到
if (!can_view_video($video, $current_user['id'])) {
  http_response_code(403);
  die('這是其他使用者的私人練習影片，你沒有權限觀看。');
}
$is_private = ($video['visibility'] ?? 'public') === 'private';
$just_uploaded = isset($_GET['new']);
$author = find_user($video['owner_id'] ?? 0);
$comments = get_video_comments($video['id']);
include __DIR__ . '/../includes/header.php';
?>
<section class="practice">
  <?php if ($just_uploaded): ?>
    <div class="alert ok slim">✓ 上傳成功，已為你開好練習工作台。</div>
  <?php endif; ?>
  <div class="practice-head">
    <h1>
      <?= htmlspecialchars($video['title']) ?>
      <?php if ($is_private): ?><span class="chip priv">🔒 私人影片</span><?php endif; ?>
    </h1>
    <div class="practice-author">
      由 <?= htmlspecialchars($video['author']) ?>
      <?php if ($video['author_role'] === 1): ?><span class="verified-badge sm" title="認證舞者">✔</span><?php endif; ?>
      <?php if (!$is_private): ?>發布 · 🔥 <span id="practiceCount"><?= $video['practice_count'] ?></span> 次打卡<?php else: ?>· 只有你看得到<?php endif; ?>
    </div>
  </div>

  <div class="player-layout" id="playerLayout">
    <!-- ===== 播放器主體 ===== -->
    <div class="player-main">
      <div class="video-stage" id="videoStage">
        <?php if ($video['source'] === 'youtube'): ?>
          <div id="ytPlayer" data-video-id="<?= htmlspecialchars($video['src']) ?>"></div>
        <?php else: ?>
          <video id="localPlayer" src="<?= htmlspecialchars($video['src']) ?>" playsinline></video>
        <?php endif; ?>
        <!-- 透明點擊層：接管點擊＝自訂播放/暫停，避免觸發來源原生中央鈕 -->
        <div class="click-catcher" id="clickCatcher" title="點擊播放／暫停"></div>
        <!-- 中央播放浮標：暫停時顯示一顆自訂的播放鍵 -->
        <button class="center-play" id="centerPlay" type="button" aria-label="播放/暫停">▶</button>
        <div class="mirror-flag" id="mirrorFlag">🪞 鏡面模式</div>
        <!-- 進入沉浸練習模式 -->
        <button class="stage-immersive" id="btnImmersive" type="button" title="沉浸練習模式">⛶ 練習模式</button>
        <!-- 沉浸模式專用：退出鈕（只有進入後才顯示） -->
        <button class="stage-exit" id="btnExit" type="button" title="退出沉浸模式 (Esc)">✕ 退出</button>
      </div>

      <!-- ===== 自訂播放控制列 ===== -->
      <div class="controls">
        <!-- 播放/暫停 + 進度 -->
        <div class="ctrl-row">
          <button class="ctrl-btn play" id="btnPlay">▶</button>
          <input type="range" id="seekBar" min="0" max="100" value="0" step="0.1">
          <span class="time" id="timeLabel">0:00 / 0:00</span>
        </div>

        <!-- 精細倍速 -->
        <div class="ctrl-group">
          <label class="ctrl-label">🐢 精細倍速</label>
          <div class="speed-pills" id="speedPills">
            <button data-speed="0.3">0.3x</button>
            <button data-speed="0.5">0.5x</button>
            <button data-speed="0.6">0.6x</button>
            <button data-speed="0.7">0.7x</button>
            <button data-speed="1.0" class="active">1.0x</button>
          </div>
        </div>

        <!-- 鏡面 + A/B 循環 -->
        <div class="ctrl-group">
          <label class="ctrl-label">🛠 練舞工具</label>
          <div class="tool-row">
            <button class="tool-btn" id="btnMirror">🪞 一鍵鏡面</button>
            <button class="tool-btn" id="btnSetA">標記 A 起點</button>
            <button class="tool-btn" id="btnSetB">標記 B 終點</button>
            <button class="tool-btn loop" id="btnLoop" disabled>🔁 A/B 循環：關</button>
            <button class="tool-btn ghost" id="btnClearAB">清除</button>
          </div>
          <div class="ab-display" id="abDisplay">A：—　B：—</div>
        </div>

        <!-- 打卡（只有公開影片計入排行榜） -->
        <?php if (!$is_private): ?>
        <div class="ctrl-group checkin-group">
          <button class="btn-checkin" id="btnCheckin" data-id="<?= $video['id'] ?>">✅ 完成練習，打卡 +1</button>
          <span class="checkin-msg" id="checkinMsg"></span>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ===== 側欄：舞者資訊 + 贊助 ===== -->
    <aside class="practice-side">
      <?php if (!$is_private && $author): ?>
      <div class="author-card">
        <a class="ac-top" href="profile.php?id=<?= (int)$author['id'] ?>">
          <span class="avatar" style="background:<?= e($author['avatar_color'] ?? '#01696f') ?>"><?= e(avatar_initial($author['name'])) ?></span>
          <span class="ac-meta">
            <span class="ac-name"><?= e($author['name']) ?><?php if ((int)$author['role']===1): ?> <i class="mini-badge">✔</i><?php endif; ?></span>
            <span class="ac-sub"><b data-sub-count="<?= (int)$author['id'] ?>"><?= count_subscribers($author['id']) ?></b> 訂閱者</span>
          </span>
        </a>
        <?php if ((int)$author['id'] !== (int)$current_user['id']): ?>
          <div class="ac-actions">
            <button class="btn-sub full<?= user_subscribed($current_user['id'],$author['id'])?' on':'' ?>" data-target="<?= (int)$author['id'] ?>" data-act="subscribe">
              <span class="sub-label"><?= user_subscribed($current_user['id'],$author['id']) ? '已訂閱' : '＋ 訂閱' ?></span>
            </button>
            <button class="btn-ghost full" id="btnSponsorPractice" data-to="<?= (int)$author['id'] ?>" data-name="<?= e($author['name']) ?>">💛 贊助</button>
          </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <!-- ===== 留言區 ===== -->
      <div class="comments" id="comments">
        <h3>💬 留言區 <span class="cmt-count" id="cmtCount"><?= count($comments) ?></span></h3>
        <?php if (!$is_private): ?>
        <form class="comment-form" id="commentForm" data-vid="<?= (int)$video['id'] ?>">
          <span class="avatar sm" style="background:<?= e($current_user['avatar_color'] ?? '#01696f') ?>"><?= e(avatar_initial($current_user['name'])) ?></span>
          <div class="cf-input">
            <textarea id="commentText" placeholder="留下你的練習心得或問題…" rows="2" maxlength="500"></textarea>
            <button type="submit" class="btn-primary sm">送出</button>
          </div>
        </form>
        <?php else: ?>
          <p class="cmt-hint">這是你的私人練習影片，不開放留言。</p>
        <?php endif; ?>

        <div class="comment-list" id="commentList">
          <?php foreach ($comments as $c):
              $cu = find_user($c['user_id'] ?? 0); ?>
            <div class="comment<?= ((int)($c['user_role']??0)===1)?' pro':'' ?>">
              <span class="avatar sm" style="background:<?= e($cu['avatar_color'] ?? '#7a7a7a') ?>"><?= e(avatar_initial($c['user_name'])) ?></span>
              <div class="cmt-body">
                <div class="comment-head">
                  <span class="name"><?= e($c['user_name']) ?></span>
                  <?php if ((int)($c['user_role']??0)===1): ?><span class="verified-badge sm">✔</span><span class="pro-tag">認證舞者</span><?php endif; ?>
                  <span class="cmt-date"><?= e($c['created']) ?></span>
                </div>
                <p><?= e($c['text']) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <?php if (!$comments && $is_private===false): ?><p class="cmt-empty" id="cmtEmpty">還沒有留言，當第一個吧！</p><?php endif; ?>
      </div>
    </aside>
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

<!-- 把影片來源資訊傳給 JS -->
<script>
window.DM_VIDEO = {
  source: "<?= $video['source'] ?>",
  src: "<?= htmlspecialchars($video['src']) ?>"
};
</script>
<script src="https://www.youtube.com/iframe_api"></script>
<script src="assets/js/player.js"></script>
<script src="assets/js/social.js"></script>
<script src="assets/js/sponsor.js"></script>
<script src="assets/js/comment.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
