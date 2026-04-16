<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: login3.php");
    exit();
}

$myRole = $_SESSION['role'];
$myID = $_COOKIE['user_id'];

echo "<h1>首頁</h1>";

// A-3: 顯示 Cookie 儲存的使用者 ID
echo "當前登入 ID (來自 Cookie): " . $myID . "<br>";

// A-1: 顯示角色
echo "你是: " . $myRole . "<br>";

echo "<hr>";

// A-2: 根據 Session 角色控制顯示內容
if ($myRole == '管理者') {
    echo "<h3>管理者後台：</h3>";
    echo "<p>你可以看到所有帳號資訊</p>";
}

if ($myRole == '教師') {
    echo "<h3>教師休息室：</h3>";
    echo "<p>你可以輸入學生分數</p>";
}

if ($myRole == '學生') {
    echo "<h3>學生專區：</h3>";
    echo "<p>你只能看到自己的成績</p>";
}

echo "<hr>";

// A-2 & A-3: 提供登出連結
echo "<a href='logout3.php'>登出並清除所有紀錄</a>";
?>