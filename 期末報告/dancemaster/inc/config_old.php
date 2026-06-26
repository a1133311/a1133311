<?php
/**
 * DanceMaster - 全域設定與資料存取層（Stage 1：登入 + 社群 + 互動）
 * 課堂 Demo 版本：用 JSON 檔做持久化儲存。
 * 之後要接 MySQL，只要把各 load／save 函式內部換成 SQL 即可，頁面不用動。
 */

session_start();

define('APP_NAME', 'DanceMaster');
define('DATA_DIR', __DIR__ . '/../data');
define('USERS_JSON', DATA_DIR . '/users.json');
define('VIDEOS_JSON', DATA_DIR . '/videos.json');
define('COMMENTS_JSON', DATA_DIR . '/comments.json');
define('INTERACTIONS_JSON', DATA_DIR . '/interactions.json'); // 讚 / 訂閱 / 贊助
define('UPLOAD_DIR', __DIR__ . '/../public/uploads');
define('UPLOAD_URL', 'uploads');
define('MAX_UPLOAD_BYTES', 80 * 1024 * 1024);

/* =====================================================================
 * 基礎：JSON 讀寫（含檔案鎖）
 * ===================================================================== */
function ensure_data() {
    if (!is_dir(DATA_DIR)) @mkdir(DATA_DIR, 0775, true);
}
function read_json($path, $default) {
    ensure_data();
    if (!file_exists($path)) {
        write_json($path, $default);
        return $default;
    }
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : $default;
}
function write_json($path, $data) {
    ensure_data();
    $fp = fopen($path, 'c+');
    if ($fp) {
        flock($fp, LOCK_EX);
        ftruncate($fp, 0); rewind($fp);
        fwrite($fp, json_encode(array_values_preserve($data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        fflush($fp); flock($fp, LOCK_UN); fclose($fp);
    }
}
/** 若是純列表就 reindex，若是關聯陣列就原樣保留 */
function array_values_preserve($data) {
    if (is_array($data) && array_keys($data) === range(0, count($data) - 1)) return array_values($data);
    return $data;
}

/* =====================================================================
 * 種子資料
 * ===================================================================== */
function seed_users() {
    return [
        ['id'=>1,'name'=>'Chaewon','handle'=>'chaewon','role'=>0,'bio'=>'資管系學生，正在學 K-pop。','avatar_color'=>'#01696f','created'=>'2026-05-01'],
        ['id'=>2,'name'=>'林老師','handle'=>'teacher_lin','role'=>1,'bio'=>'十年編舞經驗，專長 K-pop 與爵士。','avatar_color'=>'#a84b2f','created'=>'2026-04-10'],
        ['id'=>3,'name'=>'Dance Lab','handle'=>'dancelab','role'=>1,'bio'=>'街舞工作室，每週更新教學。','avatar_color'=>'#7a39bb','created'=>'2026-04-20'],
    ];
}
function seed_videos() {
    return [
        ['id'=>1,'title'=>'K-pop 副歌 8 拍分解教學','desc'=>'把副歌最難的 8 拍拆成慢動作，新手也能跟上。','source'=>'youtube','src'=>'dQw4w9WgXcQ','author'=>'林老師','author_role'=>1,'owner_id'=>2,'visibility'=>'public','practice_count'=>128,'likes'=>64,'created'=>'2026-05-28','cover'=>'https://images.unsplash.com/photo-1547153760-18fc86324498?w=800&q=70'],
        ['id'=>2,'title'=>'Hip-hop 基礎 Groove 律動','desc'=>'從零開始抓 groove，先把重拍踩穩。','source'=>'youtube','src'=>'M7lc1UVf-VE','author'=>'林老師','author_role'=>1,'owner_id'=>2,'visibility'=>'public','practice_count'=>96,'likes'=>41,'created'=>'2026-05-30','cover'=>'https://images.unsplash.com/photo-1504609773096-104ff2c73ba4?w=800&q=70'],
        ['id'=>3,'title'=>'Waacking 手臂控制練習','desc'=>'手臂線條與甩動的速度控制。','source'=>'youtube','src'=>'ScMzIvxBSi4','author'=>'Dance Lab','author_role'=>1,'owner_id'=>3,'visibility'=>'public','practice_count'=>74,'likes'=>33,'created'=>'2026-06-01','cover'=>'https://images.unsplash.com/photo-1524117074681-31bd4de22ad3?w=800&q=70'],
        ['id'=>4,'title'=>'Locking Point 定點訓練','desc'=>'Locking 的瞬間定格與 point 練習。','source'=>'youtube','src'=>'C0DPdy98e4c','author'=>'Dance Lab','author_role'=>1,'owner_id'=>3,'visibility'=>'public','practice_count'=>51,'likes'=>22,'created'=>'2026-06-02','cover'=>'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=800&q=70'],
        ['id'=>5,'title'=>'爵士 Isolation 身體分離','desc'=>'胸腔、肩膀、髖部的分離控制。','source'=>'youtube','src'=>'kffacxfA7G4','author'=>'林老師','author_role'=>1,'owner_id'=>2,'visibility'=>'public','practice_count'=>39,'likes'=>18,'created'=>'2026-06-03','cover'=>'https://images.unsplash.com/photo-1508700115892-45ecd05ae2ad?w=800&q=70'],
    ];
}
function seed_comments() {
    return [
        ['id'=>1,'video_id'=>1,'user_id'=>2,'user_name'=>'林老師','user_role'=>1,'text'=>'副歌第 3 拍重心要再壓低，建議用 0.5x 先抓 weight shift，再回到原速。','created'=>'2026-05-29'],
        ['id'=>2,'video_id'=>1,'user_id'=>1,'user_name'=>'Chaewon','user_role'=>0,'text'=>'鏡面模式救了我，終於不用左右腦反轉了 🙏','created'=>'2026-05-29'],
    ];
}
function seed_interactions() {
    // likes: [user_id => [video_id,...]]  subscriptions: [user_id => [target_user_id,...]]
    // sponsors: [['from'=>uid,'to'=>uid,'amount'=>int,'created'=>...]]
    return ['likes'=>[], 'subscriptions'=>[], 'sponsors'=>[]];
}

/* =====================================================================
 * Users
 * ===================================================================== */
function load_users() { return read_json(USERS_JSON, seed_users()); }
function save_users($u) { write_json(USERS_JSON, $u); }
function find_user($id) { foreach (load_users() as $u) if ($u['id']==(int)$id) return $u; return null; }
function find_user_by_handle($h) {
    $h = strtolower(trim($h));
    foreach (load_users() as $u) if (strtolower($u['handle'])===$h) return $u;
    return null;
}
/** 註冊或取得使用者（簡易登入：輸入名稱即可） */
function login_or_create($name) {
    $name = trim($name);
    if ($name==='') return null;
    $handle = preg_replace('/[^a-z0-9_]/','', strtolower(str_replace(' ','_',$name)));
    if ($handle==='') $handle = 'user' . random_int(1000,9999);
    $users = load_users();
    foreach ($users as $u) {
        if (strtolower($u['name'])===strtolower($name) || $u['handle']===$handle) return $u;
    }
    $maxId = 0; foreach ($users as $u) if ($u['id']>$maxId) $maxId=$u['id'];
    $colors = ['#01696f','#a84b2f','#7a39bb','#006494','#437a22','#964219'];
    $new = ['id'=>$maxId+1,'name'=>$name,'handle'=>$handle,'role'=>0,'bio'=>'','avatar_color'=>$colors[$maxId % count($colors)],'created'=>date('Y-m-d')];
    $users[] = $new; save_users($users);
    return $new;
}
function upgrade_to_pro($uid) {
    $users = load_users(); $done=false;
    foreach ($users as &$u) if ($u['id']==(int)$uid) { $u['role']=1; $done=true; }
    unset($u);
    if ($done) save_users($users);
    return $done;
}

/* =====================================================================
 * Auth（簡易登入）
 * ===================================================================== */
function current_user() {
    if (!isset($_SESSION['uid'])) return null;
    return find_user($_SESSION['uid']);
}
function is_logged_in() { return current_user() !== null; }
/** 放在需要登入的頁面最上方 */
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
function load_videos() { return read_json(VIDEOS_JSON, seed_videos()); }
function save_videos($v) { write_json(VIDEOS_JSON, $v); }
function append_video($fields) {
    $videos = load_videos();
    $maxId = 0; foreach ($videos as $v) if ($v['id']>$maxId) $maxId=$v['id'];
    $new = array_merge([
        'id'=>$maxId+1,'title'=>'未命名影片','desc'=>'','source'=>'mp4','src'=>'',
        'author'=>'匿名','author_role'=>0,'owner_id'=>0,'visibility'=>'private',
        'practice_count'=>0,'likes'=>0,'created'=>date('Y-m-d'),'cover'=>'',
    ], $fields);
    $videos[] = $new; save_videos($videos);
    return $new;
}
function bump_practice($id) {
    $videos = load_videos(); $count=null;
    foreach ($videos as &$v) if ($v['id']==(int)$id) { $v['practice_count']++; $count=$v['practice_count']; }
    unset($v);
    if ($count!==null) save_videos($videos);
    return $count;
}
function find_video($videos, $id) { foreach ($videos as $v) if ($v['id']==(int)$id) return $v; return null; }
/** 公開動態牆（最新在前） */
function get_feed($videos) {
    $pub = array_filter($videos, fn($v)=>($v['visibility']??'public')==='public');
    usort($pub, fn($a,$b)=>strcmp($b['created'].$b['id'], $a['created'].$a['id']));
    return array_values($pub);
}
function get_top_videos($videos, $limit=5) {
    $pub = array_filter($videos, fn($v)=>($v['visibility']??'public')==='public');
    usort($pub, fn($a,$b)=>$b['practice_count'] <=> $a['practice_count']);
    return array_slice($pub, 0, $limit);
}
function get_user_public_videos($videos, $uid) {
    return array_values(array_filter($videos, fn($v)=>($v['visibility']??'public')==='public' && (int)($v['owner_id']??0)===(int)$uid));
}
function get_my_private_videos($videos, $uid) {
    return array_values(array_filter($videos, fn($v)=>($v['visibility']??'public')==='private' && (int)($v['owner_id']??0)===(int)$uid));
}
function can_view_video($video, $uid) {
    if (($video['visibility']??'public')==='public') return true;
    return (int)($video['owner_id']??0)===(int)$uid;
}

/* =====================================================================
 * Comments
 * ===================================================================== */
function load_comments() { return read_json(COMMENTS_JSON, seed_comments()); }
function save_comments($c) { write_json(COMMENTS_JSON, $c); }
function get_video_comments($video_id) {
    $all = load_comments();
    $list = array_filter($all, fn($c)=>(int)$c['video_id']===(int)$video_id);
    usort($list, fn($a,$b)=>$b['id'] <=> $a['id']); // 新的在前
    return array_values($list);
}
function add_comment($video_id, $user, $text) {
    $text = trim($text);
    if ($text==='') return null;
    $all = load_comments();
    $maxId=0; foreach ($all as $c) if ($c['id']>$maxId) $maxId=$c['id'];
    $new = ['id'=>$maxId+1,'video_id'=>(int)$video_id,'user_id'=>$user['id'],
            'user_name'=>$user['name'],'user_role'=>$user['role'],
            'text'=>mb_substr($text,0,500),'created'=>date('Y-m-d')];
    $all[] = $new; save_comments($all);
    return $new;
}

/* =====================================================================
 * Interactions：讚 / 訂閱 / 贊助
 * ===================================================================== */
function load_interactions() {
    $d = read_json(INTERACTIONS_JSON, seed_interactions());
    $d['likes'] = $d['likes'] ?? [];
    $d['subscriptions'] = $d['subscriptions'] ?? [];
    $d['sponsors'] = $d['sponsors'] ?? [];
    return $d;
}
function save_interactions($d) { write_json(INTERACTIONS_JSON, $d); }

/** 切換按讚，回傳 ['liked'=>bool,'count'=>int] */
function toggle_like($uid, $video_id) {
    $d = load_interactions();
    $key = (string)$uid;
    $d['likes'][$key] = $d['likes'][$key] ?? [];
    $idx = array_search((int)$video_id, array_map('intval',$d['likes'][$key]));
    $liked = false;
    if ($idx===false) { $d['likes'][$key][] = (int)$video_id; $liked=true; }
    else { array_splice($d['likes'][$key], $idx, 1); }
    save_interactions($d);
    // 同步寫回影片的 likes 計數
    $videos = load_videos();
    foreach ($videos as &$v) if ($v['id']==(int)$video_id) {
        $v['likes'] = max(0, ($v['likes']??0) + ($liked?1:-1));
        $count = $v['likes'];
    }
    unset($v); save_videos($videos);
    return ['liked'=>$liked, 'count'=>$count ?? 0];
}
function user_liked($uid, $video_id) {
    if (!$uid) return false;
    $d = load_interactions();
    return in_array((int)$video_id, array_map('intval', $d['likes'][(string)$uid] ?? []));
}

/** 切換訂閱某位舞者，回傳 ['subscribed'=>bool,'count'=>int] */
function toggle_subscribe($uid, $target_uid) {
    if ((int)$uid===(int)$target_uid) return ['subscribed'=>false,'count'=>count_subscribers($target_uid),'self'=>true];
    $d = load_interactions();
    $key = (string)$uid;
    $d['subscriptions'][$key] = $d['subscriptions'][$key] ?? [];
    $idx = array_search((int)$target_uid, array_map('intval',$d['subscriptions'][$key]));
    $sub = false;
    if ($idx===false) { $d['subscriptions'][$key][] = (int)$target_uid; $sub=true; }
    else { array_splice($d['subscriptions'][$key], $idx, 1); }
    save_interactions($d);
    return ['subscribed'=>$sub, 'count'=>count_subscribers($target_uid)];
}
function count_subscribers($target_uid) {
    $d = load_interactions(); $n=0;
    foreach ($d['subscriptions'] as $list) {
        if (in_array((int)$target_uid, array_map('intval',$list))) $n++;
    }
    return $n;
}
function user_subscribed($uid, $target_uid) {
    if (!$uid) return false;
    $d = load_interactions();
    return in_array((int)$target_uid, array_map('intval', $d['subscriptions'][(string)$uid] ?? []));
}

/** Demo 贊助（不接金流），回傳該舞者累計贊助金額 */
function add_sponsor($from_uid, $to_uid, $amount) {
    $amount = max(0, (int)$amount);
    $d = load_interactions();
    $d['sponsors'][] = ['from'=>(int)$from_uid,'to'=>(int)$to_uid,'amount'=>$amount,'created'=>date('Y-m-d H:i')];
    save_interactions($d);
    return total_sponsored($to_uid);
}
function total_sponsored($to_uid) {
    $d = load_interactions(); $sum=0;
    foreach ($d['sponsors'] as $s) if ((int)$s['to']===(int)$to_uid) $sum += (int)$s['amount'];
    return $sum;
}

/* =====================================================================
 * 其他工具
 * ===================================================================== */
function avatar_initial($name) { return mb_strtoupper(mb_substr(trim($name),0,1)); }
function parse_video_url($url) {
    $url = trim($url);
    if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/shorts/)([A-Za-z0-9_-]{11})~', $url, $m))
        return ['source'=>'youtube','src'=>$m[1]];
    if (preg_match('~instagram\.com/(?:reel|p)/~', $url))
        return ['source'=>'instagram','src'=>null];
    return null;
}
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
