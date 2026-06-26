# 💃 DanceMaster — 舞蹈深度練習平台（課堂專題 Demo）

PHP + 前端（HTML/CSS/JS）實作。核心賣點：**精細倍速 / 一鍵鏡面 / A-B 段落循環**。

## 【Stage 1 新增】登入 + 社群 + 個人頁 + 互動

本版在原有練習平台上，加上一整套社群功能：

- **預設需登入**：未登入會自動導到登入頁（`login.php`）。採簡易登入（輸入名稱即可，不需密碼），輸入新名稱會自動註冊。
- **社群首頁**：動態牆卡片，可 **按讚 / 分享 / 訂閱** 舞者（都是真實計數、即時生效）。
- **個人頁**（`profile.php`）：使用者資訊、訂閱者數、發布的公開教學、累計贊助。
- **申請成為舞者**（`apply_pro.php`）：一鍵升級 role 0→1，之後可上傳公開教學。
- **留言區**：練習頁可發表、即時顯示、永久儲存留言。
- **Demo 贊助**：贊助按鈕 + 金額選擇視窗（不接金流，純功能展示）。

> 原本的「影片上傳」與「明亮簡約質感」改版都保留。

## 如何執行

需要 PHP 7.4+（建議 8.x）。

> 需要 PHP 含 **mbstring** 模組（XAMPP / Laragon 預設都有，不用特別裝），用於中文字串處理。

**方法 A：內建伺服器（最快）**
```bash
cd dancemaster/public
php -S localhost:8000
```
瀏覽器開 http://localhost:8000/index.php（未登入會自動導到登入頁）

**方法 B：XAMPP / Laragon**
1. 把整個 `dancemaster` 資料夾放進 htdocs（Laragon 是 www）
2. 啟動 Apache
3. 瀏覽器開 http://localhost/dancemaster/public/index.php
   （網址結尾一定要 `/public/index.php`，因為入口在 public 資料夾）

> 資料與上傳功能需要 `data/` 與 `public/uploads/` 兩個資料夾的「寫入權限」。
> 在 XAMPP/Laragon 下通常預設就可寫；若上傳失敗，請確認這兩個資料夾可寫。
>
> **Demo 帳號（首次執行自動生成）**：Chaewon（學員）、林老師（認證舞者）、Dance Lab（認證舞者）。
> 登入頁下方有「Demo 快速登入」可一鍵切換身分測試。

## 目錄結構

```
dancemaster/
├─ public/                  ← 網站根目錄 (docroot)
│  ├─ index.php             社群首頁（動態牆 + 讚/分享/訂閱 + TOP3 側欄）
│  ├─ login.php             ★ 登入頁（簡易登入，輸名即可）
│  ├─ logout.php            ★ 登出
│  ├─ profile.php           ★ 個人頁（資訊/訂閱數/發布影片/贊助）
│  ├─ apply_pro.php         ★ 申請成為認證舞者（role 0→1）
│  ├─ practice.php          核心：練習工作台（播放器 + 留言區 + 贊助）
│  ├─ upload.php            上傳影片（檔案 / YouTube 連結）
│  ├─ library.php           我的練習（個人私人影片庫）
│  ├─ checkin.php           打卡 API、like.php / subscribe.php / comment.php / sponsor.php  ★ 互動後端 API
│  ├─ uploads/              上傳影片實際存放處（含 .htaccess 防執行）
│  └─ assets/
│     ├─ css/style.css      全站樣式（明亮簡約質感）
│     └─ js/                player.js、social.js（讚/訂閱/分享）、comment.js、sponsor.js
├─ data/                    ★ 首次執行自動產生（持久化）
│  ├─ users.json            使用者（含 role 身分）
│  ├─ videos.json           影片
│  ├─ comments.json         留言
│  └─ interactions.json     讚 / 訂閱 / 贊助
└─ includes/
   ├─ config.php            設定 + 資料存取層 + 登入/互動函式
   ├─ header.php            共用頁首（登入狀態、頭像選單）
   └─ footer.php            共用頁尾
```

## 上傳功能與權限模型（本次重點）

| 身分 | 上傳行為 | 結果 |
|------|---------|------|
| **認證舞者**（role=1） | 上傳 = **公開發布教學** | 進首頁排行榜、所有人可見、可被打卡與點評 |
| **一般學員**（role=0） | 上傳 = **私人練習影片** | **只有自己看得到**、不進排行榜，純粹用來練自己想學的舞 |

- 兩種來源皆支援：**上傳 .mp4 檔案**（webm/mov/m4v 亦可）或 **貼 YouTube 連結**。
- 上傳成功後 **直接進入練習工作台**，立即可用倍速 / 鏡面 / A-B 循環。
- 私人影片有權限保護：他人嘗試開啟會回傳 **403**（程式在 `practice.php` 用 `can_view_video()` 把關）。
- 身分來自「登入帳號」：一般學員（role=0）只能上傳私人影片；申請成為認證舞者（role=1）後才能公開發布教學。

## 已實作功能總覽

| 功能 | 位置 | 說明 |
|------|------|------|
| 公開教學排行榜 TOP5 | index.php | 只算 public 影片，依打卡數排序 |
| 影片上傳（檔案/YouTube） | upload.php | 真實存檔到 uploads/，寫入 videos.json |
| 我的私人練習庫 | library.php | 只列出自己 owner 的私人影片 |
| 精細倍速 0.3x–1.0x | player.js | YouTube/mp4 共用同一介面 |
| 一鍵鏡面 Mirror | player.js + css | CSS `scaleX(-1)` |
| A/B 段落循環 | player.js | 標記起終點自動重播 |
| 練舞打卡 +1（真實寫入） | checkin.php + player.js | fetch POST → 後端寫回 JSON |
| 認證舞者金框點評 | practice.php | 金色邊框 + V 標章 |
| 身分權限控制 | config.php / practice.php | 公開 vs 私人、ACL |

## 資料儲存（之後可無痛換成 MySQL）

本版用 **JSON 檔（`data/videos.json`）做持久化**——影片真的會存下來、打卡數真的會累加。
資料存取都集中在 `config.php` 這幾個函式：

- `load_videos()` → `SELECT * FROM videos`
- `append_video()` → `INSERT INTO videos (...)`
- `bump_practice($id)` → `UPDATE videos SET practice_count = practice_count + 1 WHERE id = ?`
- `get_top_videos()` → `... WHERE visibility='public' ORDER BY practice_count DESC LIMIT N`
- `get_my_private_videos($uid)` → `... WHERE visibility='private' AND owner_id = ?`

要接 MySQL 時，**只改這幾個函式內部即可，其他頁面完全不用動**。

## 重要技術與法律提醒（寫報告請納入）

- **IG Reels 抓 .mp4 原始網址不建議當正式賣點**：CDN 網址會過期、Meta 持續封鎖、且涉及著作權與服務條款風險。本 Demo 的 YouTube 走官方 IFrame Player API（合法穩定）；IG 一律改為「使用者自行上傳檔案」。
- **上傳安全**：後端用「副檔名 + MIME 雙重檢查」、隨機檔名、單檔上限 80MB，並在 uploads/ 放 `.htaccess` 禁止執行 PHP，避免上傳惡意腳本。
- **鏡面模式限制**：若影片含文字字幕，翻轉後會變反字（屬鏡面教學固有特性，UI 已用標籤提示）。
- **差異化定位**：YouTube 本身已有倍速與循環，本平台真正差異在 **鏡面 + A/B 精準打點 + 認證舞者社群**，報告建議聚焦這三點。
