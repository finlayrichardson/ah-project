<?php
require('./tools.php');
require('./connect-db.php');
session_start();

if (empty($_SESSION['user_id'])) {
    // User isn't logged in
    if ($_SERVER['SCRIPT_NAME'] != '/forgot-password.php' && $_SERVER['SCRIPT_NAME'] != '/register.php' && $_SERVER['SCRIPT_NAME'] != '/reset-password.php') {
        load('login.php?return=' . basename($_SERVER['REQUEST_URI']));
    }
} else {
    // User is logged in
    $user_id = $_SESSION['user_id'];
    $result = mysqli_query($db, "SELECT verified FROM user WHERE user_id = $user_id;");
    $verified = mysqli_fetch_row($result)[0];
    if (!$verified && $_SERVER['SCRIPT_NAME'] != '/verify-email.php') {
        // User is not verified
        load('verify-email.php');
    }
    if ($_SERVER['SCRIPT_NAME'] == '/register.php' || $_SERVER['SCRIPT_NAME'] == '/forgot-password.php') {
        load('index.php');
    }
}
