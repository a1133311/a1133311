<?php
// ==========================================
// 檔案名稱：index.php (暗戀與告白特別版)
// 功能說明：系統主控台。外觀全面改裝為極具氛圍感的暗戀告白風格！
// ==========================================
require_once 'config/db.php';

$message = '';
$message_type = '';

// 處理 A 功能：當使用者按下「新增建構」按鈕時
if (isset($_POST['action']) && $_POST['action'] == 'add_email') {
    $email = trim($_POST['email']);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM sendtowho WHERE Email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $message = "哎呀，這位暗戀對象的 Email 已經在你的心願清單裡囉！";
            $message_type = "error";
        } else {
            $stmt = $pdo->prepare("INSERT INTO sendtowho (Email) VALUES (?)");
            if ($stmt->execute([$email])) {
                $message = "✨ 成功將暗戀目標偷偷寫入資料庫日記本中。";
                $message_type = "success";
            } else {
                $message = "日記本好像鎖上了，請稍後再試。";
                $message_type = "error";
            }
        }
    } else {
        $message = "這不是通往對方心裡的正確 Email 格式，再檢查一下吧！";
        $message_type = "error";
    }
}

$stmt = $pdo->query("SELECT COUNT(*) FROM sendtowho");
$total_emails = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>💌 那些年，我不敢說出口的告白系統 💌</title>
    
    <style>
        body {
            font-family: 'Microsoft JhengHei', Arial, sans-serif;
            background-color: #fff5f6; /* 極具氛圍感的淡粉膚色 */
            color: #4a3739;
            margin: 0;
            padding: 40px 20px;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            background: #ffffff; 
            padding: 30px;
            border-radius: 16px; /* 優雅的微圓角 */
            box-shadow: 0 10px 30px rgba(235, 147, 160, 0.3); /* 溫柔的告白粉紅陰影 */
            border: 1px solid #ffd1d7; 
        }
        h2 {
            color: #c75067; /* 帶點微醺感的乾燥玫瑰紅 */
            text-align: center;
            border-bottom: 2px dashed #ffb3bf; 
            padding-bottom: 12px;
            font-size: 24px;
            letter-spacing: 2px;
        }
        h3 {
            color: #d9667b; 
            border-left: 4px solid #ff859a; /* 左側心動提示線 */
            padding-left: 10px;
            margin-top: 35px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #614649;
        }
        input[type="email"], input[type="text"], input[type="number"], select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ffe3e7;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 14px;
            background-color: #fffdfd; 
            color: #4a3739;
            transition: all 0.3s;
        }
        input:focus, textarea:focus, select:focus {
            border: 2px solid #ff859a; 
            background-color: #ffffff;
            outline: none;
            box-shadow: 0 0 8px rgba(255, 133, 154, 0.2);
        }
        button {
            background: linear-gradient(135deg, #ffa6b5 0%, #ff758c 100%); /* 柔和暗戀粉 */
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(255, 117, 140, 0.2);
            transition: all 0.2s;
        }
        button:hover {
            transform: translateY(-1px); 
            box-shadow: 0 6px 15px rgba(255, 117, 140, 0.4);
        }
        button.btn-send {
            background: linear-gradient(135deg, #e05270 0%, #ba3c57 100%); /* 勇敢告白深紅 */
            width: 100%;
            font-size: 18px;
            padding: 15px;
            letter-spacing: 1px;
        }
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
        }
        .alert-success { background-color: #ffeef0; color: #c75067; border: 1px solid #ffd1d7; }
        .alert-error { background-color: #fbebe8; color: #b74d3a; border: 1px solid #f5cfc7; }
        
        .info-box {
            background-color: #fff0f2;
            border-left: 4px solid #ff758c;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            border-radius: 0 8px 8px 0;
            text-align: center;
            color: #8c535c;
        }
        
        /* 心動進度條外觀 */
        .progress-container {
            margin-top: 25px;
            display: none;
            background: #ffebe0;
            border-radius: 8px;
            overflow: hidden;
            height: 24px;
        }
        .progress-bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #ffa6b5, #e05270); 
            text-align: center;
            line-height: 24px;
            color: white;
            font-weight: bold;
            font-size: 13px;
        }
        
        /* 暗戀筆記本 Log 紀錄框 */
        #log-container {
            margin-top: 20px;
            background: #3d2f31; /* 深朱古力木色 */
            color: #f7e6e8;
            padding: 15px;
            border-radius: 8px;
            max-height: 200px;
            overflow-y: auto;
            font-size: 13px;
            display: none;
            line-height: 1.6;
        }

        /* 名單管理表格外觀 */
        .email-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #ffe3e7;
        }
        .email-table th, .email-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ffe3e7;
        }
        .email-table th {
            background-color: #ffd1d7;
            color: #4a3739;
        }
        .email-table tr:hover {
            background-color: #fff5f6; 
        }
        .btn-delete {
            background: #c2b4b6; /* 溫和的霧灰色 */
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }
        .btn-delete:hover {
            background: #b0a0a2;
        }
    </style>
</head>
<body>

<div class="container">
    
    <h2>💌 那些年，我不敢說出口的告白系統 </h2>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="info-box">
        💭 在悄悄寫下的日記本裡，默默收藏了 <strong><?php echo $total_emails; ?></strong> 個暗戀的名字。
    </div>

    <h3>A. 寫下悄悄注意到的 Email</h3>
    <form action="index.php" method="POST" class="form-group">
        <input type="hidden" name="action" value="add_email">
        <label for="email">輸入對方的 Email（期待某天在通訊錄不期而遇）：</label>
        <div style="display: table; width: 100%;">
            <div style="display: table-cell; width: 75%; padding-right: 10px;">
                <input type="email" id="email" name="email" placeholder="someone_special@example.com" required>
            </div>
            <div style="display: table-cell; width: 25%; vertical-align: top;">
                <button type="submit" style="width: 100%;">藏進心裡</button>
            </div>
        </div>
    </form>

    <h3>B. 醞釀已久的勇氣告白設定</h3>
    <form id="mailForm" onsubmit="startSending(event)">
        
        <div class="form-group">
            <label for="subject">告白主旨（打開對方收件匣那一瞬間的悸動）：</label>
            <input type="text" id="subject" name="subject" value="其實，我有個藏了很久的秘密想告訴你…… 🫣" required>
        </div>

        <div class="form-group">
            <label for="content">告白內容（把所有不敢直視的眼神，都寫進去）：</label>
            <textarea id="content" name="content" rows="4" required>常常在走廊上、在課堂間，不經意地看向你的方向。<br>這封信背後沒有華麗的演算法，只有一行行真誠的 PHP 程式碼。<br>也許你收到時會感到意外，但我的心動指標，早就在資料庫裡滿載了。總之，我喜歡你。</textarea>
        </div>

        <div class="form-group">
            <label for="send_mode">勇氣的傳遞範圍：</label>
            <select id="send_mode" name="send_mode" onchange="toggleRandomCount()">
                <option value="all">毫無保留的坦白 (發送給日記本裡的所有人)</option>
                <option value="random">交給緣分的偶遇 (隨機抽取幾位對象試探)</option>
            </select>
        </div>

        <div class="form-group" id="random_count_div" style="display: none;">
            <label for="random_count">隨機挑選的對象人數：</label>
            <input type="number" id="random_count" name="random_count" min="1" max="<?php echo ($total_emails > 0) ? $total_emails : 1; ?>" value="1">
        </div>

        <div class="form-group">
            <label for="interval">戀愛發酵等待秒數 (每封信發送間隔秒數)：</label>
            <input type="number" id="interval" min="0" max="60" value="3" required>
        </div>

        <button type="submit" class="btn-send">💓 按下送出，把藏在心底的勇氣寄出去 💓</button>
    </form>

    <div class="progress-container" id="progressContainer">
        <div class="progress-bar" id="progressBar">勇敢指數 0%</div>
    </div>

    <div id="log-container"></div>
</div>

<h3 style="margin-top: 40px; text-align: center; color: #c75067;">C. 暗戀秘密日記管理</h3>
<table class="email-table" style="max-width: 700px; margin: 20px auto;">
    <thead>
        <tr>
            <th style="width: 20%;">編號</th>
            <th style="width: 60%;">收藏的 Email 名單</th>
            <th style="width: 20%;">管理</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $list_stmt = $pdo->query("SELECT * FROM sendtowho ORDER BY No ASC");
        $rows = $list_stmt->fetchAll();
        
        if (count($rows) > 0):
            foreach ($rows as $row):
        ?>
            <tr>
                <td>No. <?php echo $row['No']; ?></td>
                <td><?php echo htmlspecialchars($row['Email']); ?></td>
                <td>
                    <button type="button" class="btn-delete" onclick="deleteEmail(event, <?php echo $row['No']; ?>, '<?php echo htmlspecialchars($row['Email']); ?>')">放下這段暗戀</button>
                </td>
            </tr>
        <?php 
            endforeach;
        else:
        ?>
            <tr>
                <td colspan="3" style="text-align: center; color: #7f8c8d;">心裡目前空空的，還沒有遇到那個讓你心動的人。</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
function toggleRandomCount() {
    var mode = document.getElementById('send_mode').value;
    var div = document.getElementById('random_count_div');
    div.style.display = (mode === 'random') ? 'block' : 'none';
}

async function startSending(event) {
    event.preventDefault();
    var subject = document.getElementById('subject').value;
    var content = document.getElementById('content').value;
    var send_mode = document.getElementById('send_mode').value;
    var random_count = document.getElementById('random_count').value;
    var interval = parseInt(document.getElementById('interval').value) * 1000;

    var progressContainer = document.getElementById('progressContainer');
    var progressBar = document.getElementById('progressBar');
    var logContainer = document.getElementById('log-container');
    
    progressContainer.style.display = 'block';
    logContainer.style.display = 'block';
    logContainer.innerHTML = '🔮 正在深呼吸，悄悄翻開資料庫日記本...<br>';
    progressBar.style.width = '0%';
    progressBar.innerText = '勇敢指數 0%';

    var fetchUrl = 'send_action.php?fetch=1&mode=' + send_mode + '&count=' + random_count;
    
    try {
        let response = await fetch(fetchUrl);
        let emailList = await response.json();
        
        if (emailList.length === 0) {
            logContainer.innerHTML += '<span style="color:#ffa6b5;">❌ 日記本裡沒有找到任何人的信箱，勇氣無處安放。</span><br>';
            return;
        }

        logContainer.innerHTML += '✨ 找到了 ' + emailList.length + ' 個悄悄關注的信箱。調整呼吸，準備送出...<br><br>';
        let total = emailList.length;
        
        for (let i = 0; i < total; i++) {
            let currentEmail = emailList[i];
            logContainer.innerHTML += '💌 正在將沉甸甸的告白信投遞至 (' + (i+1) + '/' + total + '): ' + currentEmail + '... ';
            logContainer.scrollTop = logContainer.scrollHeight;

            let formData = new FormData();
            formData.append('email', currentEmail);
            formData.append('subject', subject);
            formData.append('content', content);

            let mailRes = await fetch('send_action.php', { method: 'POST', body: formData });
            let mailResult = await mailRes.text();

            logContainer.innerHTML += '<span style="color:#ffa6b5;">[信件已寄出，心跳漏了一拍]</span><br>';
            
            let percent = Math.round(((i + 1) / total) * 100);
            progressBar.style.width = percent + '%';
            progressBar.innerText = '勇敢指數 ' + percent + '%';

            if (i < total - 1 && interval > 0) {
                logContainer.innerHTML += '<span style="color:#ffe3e7;">⏳ 心跳過速，稍微停頓 ' + (interval/1000) + ' 秒，平復一下緊張的情緒...</span><br>';
                logContainer.scrollTop = logContainer.scrollHeight;
                await new Promise(resolve => setTimeout(resolve, interval));
            }
        }
        
        logContainer.innerHTML += '<br><strong style="color:#e05270;">💖 所有告白信皆已勇敢送達！接下來，就交給緣分吧。</strong><br>';
        logContainer.scrollTop = logContainer.scrollHeight;

    } catch (error) {
        logContainer.innerHTML += '<span style="color:#ba3c57;">❌ 告白路上遇到了意料之外的阻礙，系統發生異常：' + error + '</span><br>';
    }
}

async function deleteEmail(event, no, email) {
    event.preventDefault(); 

    if (confirm('💔 確定要將 ' + email + ' 從秘密日記裡抹去，放下這段悄悄的暗戀嗎？')) {
        let deleteData = new FormData();
        deleteData.append('action', 'delete');
        deleteData.append('no', no);

        try {
            let res = await fetch('send_action.php', {
                method: 'POST',
                body: deleteData
            });
            let result = await res.text();

            if (result.trim() === 'delete_success') {
                alert('💔 已將對方從日記中撕下，祝彼此各自安好。');
                location.reload(); 
            } else {
                alert('系統提示：' + result);
            }
        } catch (error) {
            alert('連線發生異常：' + error);
        }
    }
}
</script>
</body>
</html>