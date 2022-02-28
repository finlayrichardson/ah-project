<?php
function load($page = 'login') {
    $url = "http://" . $_SERVER['HTTP_HOST'];
    $url = rtrim($url, '/\\');
    $url .= "/" . $page;
    header("Location: $url");
    exit();
}

function query($sql, $types,  ...$variables) {
    require('./utils/connect-db.php');
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$variables);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function teacher_status($user_id, $group_id) {
    require('./utils/connect-db.php');
    // Check if owner
    $result = query("SELECT owner_id FROM `group` WHERE group_id = ?;", 'i', $group_id);
    if (mysqli_fetch_array($result)[0] == $user_id || $_SESSION['role'] == "admin") return "owner";
    // Check if member
    $result = query("SELECT user.user_id FROM user, group_member WHERE user.user_id = group_member.user_id AND group_member.user_id = ? and group_member.group_id = ?;", 'ii', $user_id, $group_id);
    if (mysqli_num_rows($result) == 1) return "member";
}

function count_submitted($task_id) {
    $count = 0;
    $files = glob("./code/$task_id/*");
    if ($files) $count = count($files);
    return $count;
}

function pagination($data, $limit = null, $current = null, $adjacents = null) {
    $result = array();
    if (isset($data, $limit) === true && $data != 0) {
        $result = range(1, ceil($data / $limit));
        if (isset($current, $adjacents) === true) {
            if (($adjacents = floor($adjacents / 2) * 2 + 1) >= 1) {
                $result = array_slice($result, max(0, min(count($result) - $adjacents, intval($current) - ceil($adjacents / 2))), $adjacents);
            }
        }
    }
    return $result;
}

function info($type, $title, $message, $link = null) {
    include('./includes/info.php');
    exit();
}

function send_email($type, $email, $first_name, $last_name, $token) {
    require('./utils/email.php');
    $host = $_SERVER['HTTP_HOST'];
    $mail->addAddress($email, $first_name . ' ' . $last_name);
    switch ($type) {
        case "email":
            $mail->Subject = "Verify Email";
            $mail->AltBody =  "Please visit http://$host/verify-email/$token to verify your email.";
            break;
        case "password":
            $mail->Subject = "Reset Password";
            $mail->AltBody = "Please visit http://$host/reset-password/$token to reset your password.";
            break;
    }
    ob_start();
    include('./includes/email-template.php');
    $mail->Body = ob_get_clean();
    $mail->send();
}
