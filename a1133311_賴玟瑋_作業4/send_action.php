<?php
// ==========================================
// 檔案名稱：send_action.php (手動引入 PHPMailer 版本)
// 功能說明：免除 vendor，直接手動載入 lib 底下的 PHPMailer 核心
// ==========================================

// 1. 手動引入資料庫連線 (因為 db.php 現在在 config 資料夾下)
require_once 'config/db.php';

// 2. 【核心關鍵】手動一條一條引入 PHPMailer 的核心原始碼檔案
require_once 'lib/PHPMailer/Exception.php';
require_once 'lib/PHPMailer/PHPMailer.php';
require_once 'lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// -----------------------------------------------------------
// 【優先處理】第三階段：接收前端傳來的刪除請求
// -----------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $no_to_delete = (int)$_POST['no'];
    
    $delete_stmt = $pdo->prepare("DELETE FROM sendtowho WHERE No = ?");
    if ($delete_stmt->execute([$no_to_delete])) {
        echo "delete_success"; 
    } else {
        echo "資料庫刪除失敗";
    }
    exit; 
}

// -----------------------------------------------------------
// 第一階段：回傳前端所需的發送對象 JSON 陣列
// -----------------------------------------------------------
if (isset($_GET['fetch']) && $_GET['fetch'] == 1) {
    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'all';
    $count = isset($_GET['count']) ? (int)$_GET['count'] : 1;
    
    if ($mode === 'random' && $count > 0) {
        $stmt = $pdo->prepare("SELECT Email FROM sendtowho ORDER BY RAND() LIMIT ?");
        $stmt->execute([$count]);
    } else {
        $stmt = $pdo->query("SELECT Email FROM sendtowho ORDER BY No ASC");
    }
    
    $emails = $stmt->fetchAll(PDO::FETCH_COLUMN);
    header('Content-Type: application/json');
    echo json_encode($emails);
    exit;
}

// -----------------------------------------------------------
// 第二階段：接收單一封郵件的資料，透過 SMTP 發送 (手動流版本)
// -----------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $to = $_POST['email'];
    $subject = $_POST['subject'];
    $content = $_POST['content'];
    
    $mail = new PHPMailer(true);

    try {
        // --- 【SMTP 伺服器發信環境設定】 ---
        $mail->isSMTP();                                      
        $mail->Host       = 'smtp.gmail.com';                 
        $mail->SMTPAuth   = true;                             
        $mail->Username   = 'e20051225@gmail.com';            
        $mail->Password   = 'gesrbmpwcknncdtu';               
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;      
        $mail->Port       = 465;                              
        $mail->CharSet    = 'UTF-8';                          

        // --- 【郵件收發信人設定】 ---
        $mail->setFrom('e20051225@gmail.com', '那些年，不敢說出口的告白系統'); 
        $mail->addAddress($to);                               

        // --- 【郵件內文格式設定】 ---
        $mail->isHTML(true);                                  
        $mail->Subject = $subject;                            
        $mail->Body    = $content;                            

        // 真正執行發送
        $mail->send();
        echo "success"; 
        
    } catch (Exception $e) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    }
    exit;
}
?>