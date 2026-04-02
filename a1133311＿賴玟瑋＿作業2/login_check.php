<?php
$user = $_POST['user'];
$pwd = $_POST['pwd'];

if ($user == "zichi" && $pwd == "1217") {
    header("Location: HOMEWORK.php");  
} else {
    echo "<script>";
    echo "alert('登入失敗！帳號或密碼錯誤');";
    echo "location.href='login.php';"; 
    echo "</script>";
}
?>