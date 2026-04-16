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

echo "<table border='1'>";
echo "<tr><td>編號</td><td>名稱</td><td>價格</td><td>數量</td><td>功能</td></tr>";

$total = 0;
while (list($arr, $value) = each($_COOKIE)) {
    if (isset($_COOKIE[$arr]) && is_array($_COOKIE[$arr])) {
        echo "<tr>";
        $id = ""; $name = ""; $price = 0; $quantity = 0;
        
        while (list($name, $value) = each($_COOKIE[$arr])) {
            echo "<td>" . $value . "</td>";
            if ($name == "Price") $price = $value;
            if ($name == "Quantity") $quantity = $value;
        }
        $total += $price * $quantity;
        echo "<td><a href='delete.php?id=" . $arr . "'>刪除</a></td>";
        echo "</tr>";
    }
}
echo "</table>";
echo "<h3>總金額 = $" . $total . "</h3>";
?>

<br><a href='catalog.php'>繼續購物</a>