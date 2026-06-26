<?php
require_once __DIR__ . '/../includes/config.php';
require_login();

$me = current_user();
$errors = [];
$ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $bio  = trim($_POST['bio'] ?? '');

    if ($name === '') {
        $errors[] = '名字不能為空白。';
    }

    if (mb_strlen($name) > 50) {
        $errors[] = '名字不能超過 50 個字。';
    }

    if (mb_strlen($bio) > 255) {
        $errors[] = '自我介紹不能超過 255 個字。';
    }

    if (!$errors) {
        $stmt = db()->prepare("
            UPDATE users
            SET name = ?, bio = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $bio, (int)$me['id']]);

        $_SESSION['user'] = find_user((int)$me['id']);
        $me = current_user();
        $ok = true;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<section class="container" style="max-width:760px; padding-top:32px; padding-bottom:60px;">
    <div class="upload-head" style="text-align:left;">
        <h1>編輯個人資料</h1>
        <p class="lead">你可以修改自己的名字與自我介紹。</p>
    </div>

    <?php if ($ok): ?>
        <div class="alert ok">
            <div class="alert-title">更新成功</div>
            <div>你的個人資料已儲存。</div>
        </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert err">
            <div class="alert-title">無法儲存</div>
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= e($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form class="upload-card" method="post">
        <div class="field">
            <label for="name">名字</label>
            <input
                type="text"
                id="name"
                name="name"
                maxlength="50"
                value="<?= e($me['name'] ?? '') ?>"
                placeholder="請輸入你的名字"
                required
            >
        </div>

        <div class="field">
            <label for="bio">自我介紹</label>
            <textarea
                id="bio"
                name="bio"
                maxlength="255"
                rows="5"
                placeholder="簡單介紹你自己，例如：喜歡的舞風、學舞經驗、目前目標"
                style="padding:12px 15px; border:1px solid var(--line-strong); border-radius:11px; font-size:15px; font-family:inherit; background:var(--surface-2); color:var(--txt); resize:vertical;"
            ><?= e($me['bio'] ?? '') ?></textarea>
            <div class="field-hint">最多 255 字。</div>
        </div>

        <div class="upload-foot">
            <div class="as-who">目前帳號：<b>@<?= e($me['handle'] ?? '') ?></b></div>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a class="btn-ghost" href="profile.php?id=<?= (int)$me['id'] ?>">返回個人頁</a>
                <button type="submit" class="btn-primary">儲存變更</button>
            </div>
        </div>
    </form>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>