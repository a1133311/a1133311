<?php
require_once __DIR__ . '/../includes/config.php';
require_login();
$current_user = current_user();
$is_pro = (int)$current_user['role'] === 1;
// 舞者上傳 → 公開教學；學員上傳 → 只給自己練的私人影片
$visibility = $is_pro ? 'public' : 'private';

$errors = [];
$created = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $mode  = $_POST['mode'] ?? 'file';     // 'file' = 上傳檔案 / 'youtube' = 貼連結
    $cover = trim($_POST['cover'] ?? '');

    if ($title === '') $errors[] = '請輸入影片標題。';

    if ($mode === 'youtube') {
        $parsed = parse_video_url($_POST['youtube_url'] ?? '');
        if (!$parsed || $parsed['source'] !== 'youtube') {
            $errors[] = '請貼上有效的 YouTube 連結（watch / youtu.be / shorts 皆可）。IG 連結請改用檔案上傳。';
        }
        if (!$errors) {
            $created = append_video([
                'title' => $title,
                'source' => 'youtube',
                'src' => $parsed['src'],
                'author' => $current_user['name'],
                'author_role' => $current_user['role'],
                'owner_id' => $current_user['id'],
                'visibility' => $visibility,
                'cover' => $cover ?: 'https://img.youtube.com/vi/' . $parsed['src'] . '/hqdefault.jpg',
            ]);
        }
    } else {
        if (!isset($_FILES['video']) || $_FILES['video']['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = '請選擇要上傳的影片檔。';
        } elseif ($_FILES['video']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = '檔案上傳失敗（錯誤碼 ' . $_FILES['video']['error'] . '）。可能超過伺服器設定的上限。';
        } else {
            $f = $_FILES['video'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $allowed = ['mp4', 'webm', 'mov', 'm4v'];

            $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;
            $mime = $finfo ? finfo_file($finfo, $f['tmp_name']) : ($f['type'] ?? '');
            if ($finfo) finfo_close($finfo);

            if (!in_array($ext, $allowed)) {
                $errors[] = '只接受 mp4 / webm / mov / m4v 格式。';
            } elseif ($f['size'] > MAX_UPLOAD_BYTES) {
                $errors[] = '檔案太大，上限為 ' . round(MAX_UPLOAD_BYTES / 1024 / 1024) . ' MB。';
            } elseif (strpos($mime, 'video/') !== 0) {
                $errors[] = '這個檔案看起來不是影片（偵測到：' . htmlspecialchars($mime) . '）。';
            } else {
                if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0775, true);
                $safe = bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
                $dest = UPLOAD_DIR . '/' . $safe;
                if (move_uploaded_file($f['tmp_name'], $dest)) {
                    $created = append_video([
                        'title' => $title,
                        'source' => 'mp4',
                        'src' => UPLOAD_URL . '/' . $safe,
                        'author' => $current_user['name'],
                        'author_role' => $current_user['role'],
                        'owner_id' => $current_user['id'],
                        'visibility' => $visibility,
                        'cover' => $cover,
                    ]);
                } else {
                    $errors[] = '無法將檔案寫入伺服器，請確認 uploads 資料夾的寫入權限。';
                }
            }
        }
    }

    // 上傳成功 → 直接進練習台
    if ($created && !$errors) {
        header('Location: practice.php?id=' . $created['id'] . '&new=1');
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>
<section class="upload-wrap">
  <div class="upload-head">
    <span class="eyebrow"><?= $is_pro ? '發布教學' : '我的練習' ?></span>
    <h1><?= $is_pro ? '上傳教學影片' : '上傳要練的影片' ?></h1>
    <?php if ($is_pro): ?>
      <p class="lead">你是認證舞者，上傳的影片會<b>公開發布</b>到平台，出現在首頁排行榜，所有學員都能練習與打卡。</p>
    <?php else: ?>
      <p class="lead">把你想練的舞上傳上來，這支影片<b>只有你看得到</b>（不會公開、不進排行榜）。上傳後即可用精細倍速、一鍵鏡面與 A/B 段落循環來拆解練習。</p>
    <?php endif; ?>
  </div>

  <!-- 權限說明卡 -->
  <div class="perm-note <?= $is_pro ? 'pub' : 'priv' ?>">
    <span class="perm-ic"><?= $is_pro ? '🌐' : '🔒' ?></span>
    <div>
      <div class="perm-title"><?= $is_pro ? '公開教學影片' : '私人練習影片（只有你看得到）' ?></div>
      <div class="perm-desc">
        <?= $is_pro
          ? '會進入首頁排行榜，可被所有人練習、打卡、留言。'
          : '只會出現在你的「我的練習」清單，其他使用者完全看不到。想公開發布教學需要認證舞者身分。' ?>
      </div>
    </div>
  </div>

  <?php if ($errors): ?>
    <div class="alert err">
      <div class="alert-title">無法上傳</div>
      <ul>
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form class="upload-card" method="post" enctype="multipart/form-data">
    <div class="seg" id="modeSeg">
      <button type="button" class="seg-btn active" data-mode="file">📁 上傳檔案</button>
      <button type="button" class="seg-btn" data-mode="youtube">🔗 YouTube 連結</button>
    </div>
    <input type="hidden" name="mode" id="modeInput" value="file">

    <div class="field">
      <label>影片標題 <span class="req">*</span></label>
      <input type="text" name="title" placeholder="例：想練的副歌段落"
             value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
    </div>

    <div class="field mode-file">
      <label>影片檔 <span class="req">*</span></label>
      <label class="dropzone" id="dropzone">
        <input type="file" name="video" id="fileInput" accept="video/mp4,video/webm,video/quicktime,video/x-m4v" hidden>
        <div class="dz-inner" id="dzInner">
          <div class="dz-icon">⬆</div>
          <div class="dz-text">點此選擇檔案，或拖曳到這裡</div>
          <div class="dz-hint">支援 mp4 / webm / mov / m4v，上限 <?= round(MAX_UPLOAD_BYTES/1024/1024) ?>MB</div>
        </div>
      </label>
      <video id="filePreview" class="file-preview" controls hidden></video>
    </div>

    <div class="field mode-youtube" hidden>
      <label>YouTube 連結 <span class="req">*</span></label>
      <input type="text" name="youtube_url" placeholder="https://www.youtube.com/watch?v=..."
             value="<?= htmlspecialchars($_POST['youtube_url'] ?? '') ?>">
      <p class="field-hint">支援 watch / youtu.be / shorts 連結。IG Reels 請改用檔案上傳（直接抓 IG 影片不穩定且有版權風險）。</p>
    </div>

    <div class="field">
      <label>封面圖網址 <span class="opt">選填</span></label>
      <input type="text" name="cover" placeholder="留空則自動使用 YouTube 縮圖 / 預設封面"
             value="<?= htmlspecialchars($_POST['cover'] ?? '') ?>">
    </div>

    <div class="upload-foot">
      <span class="as-who">將以 <b><?= htmlspecialchars($current_user['name']) ?></b>（<?= $is_pro ? '認證舞者 · 公開' : '學員 · 私人' ?>）身分發布</span>
      <button type="submit" class="btn-primary lg">上傳並開始練習</button>
    </div>
  </form>
</section>

<script>
(function(){
  const seg = document.getElementById('modeSeg');
  const modeInput = document.getElementById('modeInput');
  const fileBlock = document.querySelector('.mode-file');
  const ytBlock = document.querySelector('.mode-youtube');
  const fileInput = document.getElementById('fileInput');
  const dz = document.getElementById('dropzone');
  const dzInner = document.getElementById('dzInner');
  const preview = document.getElementById('filePreview');

  seg.querySelectorAll('.seg-btn').forEach(b=>{
    b.addEventListener('click',()=>{
      seg.querySelectorAll('.seg-btn').forEach(x=>x.classList.remove('active'));
      b.classList.add('active');
      const m = b.dataset.mode;
      modeInput.value = m;
      fileBlock.hidden = (m!=='file');
      ytBlock.hidden = (m!=='youtube');
    });
  });

  function showFile(file){
    if(!file) return;
    dzInner.querySelector('.dz-text').textContent = file.name;
    dzInner.querySelector('.dz-hint').textContent = (file.size/1024/1024).toFixed(1)+' MB';
    preview.src = URL.createObjectURL(file); preview.hidden = false;
  }
  fileInput.addEventListener('change',()=>showFile(fileInput.files[0]));
  ['dragenter','dragover'].forEach(ev=>dz.addEventListener(ev,e=>{e.preventDefault();dz.classList.add('drag');}));
  ['dragleave','drop'].forEach(ev=>dz.addEventListener(ev,e=>{e.preventDefault();dz.classList.remove('drag');}));
  dz.addEventListener('drop',e=>{
    const f = e.dataTransfer.files[0];
    if(f){ fileInput.files = e.dataTransfer.files; showFile(f); }
  });
})();
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
