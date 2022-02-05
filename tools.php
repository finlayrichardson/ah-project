<?php
function load($page = 'login') {
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

function teacher_status($user_id, $group_id) {
    require('./connect-db.php');
    // Check if owner
    $result = query("SELECT owner_id FROM `group` WHERE group_id = ?;", 'i', $group_id);
    if (mysqli_fetch_array($result)[0] == $user_id || $_SESSION['role'] == "admin") return "owner";
    // Check if member
    $result = query("SELECT user.user_id FROM user, group_member WHERE user.user_id = group_member.user_id AND group_member.user_id = ? and group_member.group_id = ?;", 'ii', $user_id, $group_id);
    if (mysqli_num_rows($result) == 1) return "member";
}

function count_submitted($task_id) {
    $count = 0;
    $files = glob("/code/$task_id/*");
    if ($files) $count = count($files);
    return $count;
}
