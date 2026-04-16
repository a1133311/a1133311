<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    if ($user == 'admin' && $pass == '123') {
        $_SESSION['role'] = '管理者';
        setcookie("user_id", $user, time() + 3600);
        header("Location: index3.php");
        exit();
    } 
    elseif ($user == 'teacher' && $pass == '456') {
        $_SESSION['role'] = '教師';
        setcookie("user_id", $user, time() + 3600);
        header("Location: index3.php");
        exit();
    } 
    elseif ($user == 'student' && $pass == '789') {
        $_SESSION['role'] = '學生';
        setcookie("user_id", $user, time() + 3600);
        header("Location: index3.php");
        exit();
    } 
    else {
        $error = "帳號或密碼錯誤！";
    }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>作業 3 - 登入系統</title>
</head>
<body>
    
    <?php 
        if(isset($error)) echo "$error"; 
    ?>

    <form method="POST" action="login3.php">
        <label>帳號：</label>
        <input type="text" name="username"><br><br>
        
        <label>密碼：</label>
        <input type="password" name="password"><br><br>
        
        <input type="submit" value="登入">
    </form>
</body>
</html>