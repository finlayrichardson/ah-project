<?php
function load($page = 'login.php') {
    $url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
    $url = rtrim($url, '/\\');
    $url .= "/" . $page;
    header("Location: $url");
    exit();
}

function query($sql, $types,  ...$variables) {
    require('./connect-db.php');
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$variables);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}
