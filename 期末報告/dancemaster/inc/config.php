<?php
/**
 * DanceMaster - 全域設定與資料存取層（MySQL 版）
 */

session_start();

define('APP_NAME', 'DanceMaster');

define('DB_HOST', 'sql111.infinityfree.com');
define('DB_NAME', 'if0_42257938_forinfinitydancemaster');
define('DB_USER', 'if0_42257938');
define('DB_PASS', 'owo20051225');

define('UPLOAD_DIR', __DIR__ . '/../public/uploads');
define('UPLOAD_URL', 'uploads');
define('MAX_UPLOAD_BYTES', 80 * 1024 * 1024);

/* =====================================================================
 * DB
 * ===================================================================== */
function db() {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
    return $pdo;
}

/* =====================================================================
 * Users
 * ===================================================================== */
function load_users() {
    $stmt = db()->query("SELECT * FROM users ORDER BY id ASC");
    return $stmt->fetchAll();
}

function save_users($u) {
    return true;
}

function find_user($id) {
    $stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([(int)$id]);
    return $stmt->fetch() ?: null;
}

function find_user_by_handle($h) {
    $h = strtolower(trim($h));
    $stmt = db()->prepare("SELECT * FROM users WHERE handle = ?");
    $stmt->execute([$h]);
    return $stmt->fetch() ?: null;
}

/** 註冊或取得使用者（簡易登入：輸入名稱即可） */
function login_or_create($name) {
    $name = trim($name);
    if ($name === '') return null;

    $handle = preg_replace('/[^a-z0-9_]/', '', strtolower(str_replace(' ', '_', $name)));
    if ($handle === '') $handle = 'user' . random_int(1000,9999);

    $stmt = db()->prepare("SELECT * FROM users WHERE LOWER(name)=LOWER(?) OR handle=? LIMIT 1");
    $stmt->execute([$name, $handle]);
    $user = $stmt->fetch();
    if ($user) return $user;

    $colors = ['#01696f','#a84b2f','#7a39bb','#006494','#437a22','#964219'];
    $color = $colors[random_int(0, count($colors)-1)];

    $stmt = db()->prepare("
        INSERT INTO users (name, handle, role, bio, avatar_color, created)
        VALUES (?, ?, 0, '', ?, CURDATE())
    ");
    $stmt->execute([$name, $handle, $color]);

    return find_user(db()->lastInsertId());
}

function upgrade_to_pro($uid) {
    $stmt = db()->prepare("UPDATE users SET role = 1 WHERE id = ?");
    return $stmt->execute([(int)$uid]);
}

/* =====================================================================
 * Auth
 * ===================================================================== */
function current_user() {
    if (!isset($_SESSION['uid'])) return null;
    return find_user($_SESSION['uid']);
}

function is_logged_in() {
    return current_user() !== null;
}

function require_login() {
    if (!is_logged_in()) {
        $back = urlencode($_SERVER['REQUEST_URI'] ?? 'index.php');
        header('Location: login.php?next=' . $back);
        exit;
    }
}

/* =====================================================================
 * Videos
 * ===================================================================== */
function load_videos() {
    $stmt = db()->query("SELECT * FROM videos ORDER BY created DESC, id DESC");
    return $stmt->fetchAll();
}

function save_videos($v) {
    return true;
}

function append_video($fields) {
    $data = array_merge([
        'title' => '未命名影片',
        'desc' => '',
        'source' => 'mp4',
        'src' => '',
        'author' => '匿名',
        'author_role' => 0,
        'owner_id' => 0,
        'visibility' => 'private',
        'practice_count' => 0,
        'likes' => 0,
        'cover' => '',
    ], $fields);

    $stmt = db()->prepare("
        INSERT INTO videos
        (title, `desc`, source, src, author, author_role, owner_id, visibility, practice_count, likes, created, cover)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?)
    ");

    $stmt->execute([
        $data['title'],
        $data['desc'],
        $data['source'],
        $data['src'],
        $data['author'],
        (int)$data['author_role'],
        (int)$data['owner_id'],
        $data['visibility'],
        (int)$data['practice_count'],
        (int)$data['likes'],
        $data['cover']
    ]);

    $id = db()->lastInsertId();
    $stmt = db()->prepare("SELECT * FROM videos WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function bump_practice($id) {
    $stmt = db()->prepare("UPDATE videos SET practice_count = practice_count + 1 WHERE id = ?");
    $stmt->execute([(int)$id]);

    $stmt = db()->prepare("SELECT practice_count FROM videos WHERE id = ?");
    $stmt->execute([(int)$id]);
    $row = $stmt->fetch();
    return $row ? (int)$row['practice_count'] : null;
}

function find_video($videos, $id) {
    foreach ($videos as $v) {
        if ((int)$v['id'] === (int)$id) return $v;
    }
    return null;
}

function get_feed($videos) {
    $pub = array_filter($videos, fn($v) => ($v['visibility'] ?? 'public') === 'public');
    usort($pub, fn($a, $b) => strcmp($b['created'] . $b['id'], $a['created'] . $a['id']));
    return array_values($pub);
}

function get_top_videos($videos, $limit=5) {
    $pub = array_filter($videos, fn($v) => ($v['visibility'] ?? 'public') === 'public');
    usort($pub, fn($a, $b) => (int)$b['practice_count'] <=> (int)$a['practice_count']);
    return array_slice($pub, 0, $limit);
}

function get_user_public_videos($videos, $uid) {
    return array_values(array_filter($videos, fn($v) =>
        ($v['visibility'] ?? 'public') === 'public' && (int)($v['owner_id'] ?? 0) === (int)$uid
    ));
}

function get_my_private_videos($videos, $uid) {
    return array_values(array_filter($videos, fn($v) =>
        ($v['visibility'] ?? 'public') === 'private' && (int)($v['owner_id'] ?? 0) === (int)$uid
    ));
}

function can_view_video($video, $uid) {
    if (($video['visibility'] ?? 'public') === 'public') return true;
    return (int)($video['owner_id'] ?? 0) === (int)$uid;
}

/* =====================================================================
 * Comments
 * ===================================================================== */
function load_comments() {
    $stmt = db()->query("SELECT * FROM comments ORDER BY id DESC");
    return $stmt->fetchAll();
}

function save_comments($c) {
    return true;
}

function get_video_comments($video_id) {
    $stmt = db()->prepare("SELECT * FROM comments WHERE video_id = ? ORDER BY id DESC");
    $stmt->execute([(int)$video_id]);
    return $stmt->fetchAll();
}

function add_comment($video_id, $user, $text) {
    $text = trim($text);
    if ($text === '') return null;

    $stmt = db()->prepare("
        INSERT INTO comments (video_id, user_id, user_name, user_role, text, created)
        VALUES (?, ?, ?, ?, ?, CURDATE())
    ");
    $stmt->execute([
        (int)$video_id,
        (int)$user['id'],
        $user['name'],
        (int)$user['role'],
        mb_substr($text, 0, 500)
    ]);

    $id = db()->lastInsertId();
    $stmt = db()->prepare("SELECT * FROM comments WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/* =====================================================================
 * Interactions：讚 / 訂閱 / 贊助
 * ===================================================================== */
function load_interactions() {
    return ['likes'=>[], 'subscriptions'=>[], 'sponsors'=>[]];
}

function save_interactions($d) {
    return true;
}

function toggle_like($uid, $video_id) {
    $uid = (int)$uid;
    $video_id = (int)$video_id;

    $stmt = db()->prepare("SELECT 1 FROM likes WHERE user_id = ? AND video_id = ?");
    $stmt->execute([$uid, $video_id]);
    $exists = $stmt->fetch();

    if ($exists) {
        $stmt = db()->prepare("DELETE FROM likes WHERE user_id = ? AND video_id = ?");
        $stmt->execute([$uid, $video_id]);
        $liked = false;
        db()->prepare("UPDATE videos SET likes = GREATEST(likes - 1, 0) WHERE id = ?")->execute([$video_id]);
    } else {
        $stmt = db()->prepare("INSERT INTO likes (user_id, video_id) VALUES (?, ?)");
        $stmt->execute([$uid, $video_id]);
        $liked = true;
        db()->prepare("UPDATE videos SET likes = likes + 1 WHERE id = ?")->execute([$video_id]);
    }

    $stmt = db()->prepare("SELECT likes FROM videos WHERE id = ?");
    $stmt->execute([$video_id]);
    $row = $stmt->fetch();

    return ['liked' => $liked, 'count' => $row ? (int)$row['likes'] : 0];
}

function user_liked($uid, $video_id) {
    if (!$uid) return false;
    $stmt = db()->prepare("SELECT 1 FROM likes WHERE user_id = ? AND video_id = ?");
    $stmt->execute([(int)$uid, (int)$video_id]);
    return (bool)$stmt->fetch();
}

function toggle_subscribe($uid, $target_uid) {
    $uid = (int)$uid;
    $target_uid = (int)$target_uid;

    if ($uid === $target_uid) {
        return ['subscribed' => false, 'count' => count_subscribers($target_uid), 'self' => true];
    }

    $stmt = db()->prepare("SELECT 1 FROM subscriptions WHERE user_id = ? AND target_user_id = ?");
    $stmt->execute([$uid, $target_uid]);
    $exists = $stmt->fetch();

    if ($exists) {
        $stmt = db()->prepare("DELETE FROM subscriptions WHERE user_id = ? AND target_user_id = ?");
        $stmt->execute([$uid, $target_uid]);
        $sub = false;
    } else {
        $stmt = db()->prepare("INSERT INTO subscriptions (user_id, target_user_id) VALUES (?, ?)");
        $stmt->execute([$uid, $target_uid]);
        $sub = true;
    }

    return ['subscribed' => $sub, 'count' => count_subscribers($target_uid)];
}

function count_subscribers($target_uid) {
    $stmt = db()->prepare("SELECT COUNT(*) AS n FROM subscriptions WHERE target_user_id = ?");
    $stmt->execute([(int)$target_uid]);
    $row = $stmt->fetch();
    return $row ? (int)$row['n'] : 0;
}

function user_subscribed($uid, $target_uid) {
    if (!$uid) return false;
    $stmt = db()->prepare("SELECT 1 FROM subscriptions WHERE user_id = ? AND target_user_id = ?");
    $stmt->execute([(int)$uid, (int)$target_uid]);
    return (bool)$stmt->fetch();
}

function add_sponsor($from_uid, $to_uid, $amount) {
    $amount = max(0, (int)$amount);

    $stmt = db()->prepare("
        INSERT INTO sponsors (from_uid, to_uid, amount)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([(int)$from_uid, (int)$to_uid, $amount]);

    return total_sponsored($to_uid);
}

function total_sponsored($to_uid) {
    $stmt = db()->prepare("SELECT COALESCE(SUM(amount),0) AS total FROM sponsors WHERE to_uid = ?");
    $stmt->execute([(int)$to_uid]);
    $row = $stmt->fetch();
    return $row ? (int)$row['total'] : 0;
}

/* =====================================================================
 * 其他工具
 * ===================================================================== */
function avatar_initial($name) {
    return mb_strtoupper(mb_substr(trim($name), 0, 1));
}

function parse_video_url($url) {
    $url = trim($url);
    if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/shorts/)([A-Za-z0-9_-]{11})~', $url, $m)) {
        return ['source' => 'youtube', 'src' => $m[1]];
    }
    if (preg_match('~instagram\.com/(?:reel|p)/~', $url)) {
        return ['source' => 'instagram', 'src' => null];
    }
    return null;
}

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}