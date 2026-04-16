<?php
function each(&$array) {
    $res = array();
    $key = key($array);
    if ($key !== null) {
        next($array);
        $res[1] = $res['value'] = $array[$key];
        $res[0] = $res['key'] = $key;
    } else {
        $res = false;
    }
    return $res;
}

$id = $_GET["id"];

if (isset($_COOKIE[$id])) {
    while (list($name, $value) = each($_COOKIE[$id])) {
        setcookie($id . "[" . $name . "]", "", time() - 3600);
    }
}

// 4. 轉址回購物車頁面
header("Location: shoppingcart.php");
?>