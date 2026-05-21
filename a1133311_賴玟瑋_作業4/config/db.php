<?php
// ==========================================
// 檔案名稱：db.php
// 功能說明：負責與 MySQL 資料庫進行安全連線 (使用 PDO 驅動)
// ==========================================

$host = '127.0.0.1';     // 資料庫主機位址 (通常本地端為 127.0.0.1 或 localhost)
$db   = 'phphomework4';  // 你指定的資料庫名稱
$user = 'root';          // 你的 MySQL 帳號 (XAMPP 預設為 root)
$pass = '';              // 你的 MySQL 密碼 (XAMPP 預設為空，MAMP 預設為 root)
$charset = 'utf8mb4';    // 使用萬國碼，防止中文名單或郵件亂碼

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // 開啟錯誤追蹤模式
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // 取出資料時預設用關聯陣列
    PDO::ATTR_EMULATE_PREPARES   => false,                  // 關閉模擬預處理，提升安全性
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // 如果連線失敗，拋出錯誤訊息
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>