<?php
require('./auth.php');
if ($_SESSION['role'] == "student") load('index.php');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Validate action
    if (empty($_POST['action'])) {
        echo "<p>You must specify an action</p>";
        exit();
    } elseif ($_POST['action'] != "delete") {
        // Action is wrong
        echo "<p>Invalid action</p>";
        exit();
    } else {
        $action = "delete";
    }
    // Validate group_id
    if (empty($_POST['group_id'])) {
        echo "<p>You must specify a Group ID</p>";
        exit();
    } else {
        $group_id = mysqli_real_escape_string($db, trim($_POST['group_id']));
    }
    // Check group exists
    $result = query("SELECT group_id FROM `group` WHERE group_id = ?;", 'i', $group_id);
    if (mysqli_num_rows($result) == 0) {
        echo "<p>Group not found</p>";
        exit();
    }
    // Check user has permissions
    $result = query("SELECT owner_id FROM `group` WHERE group_id = ?;", 'i', $group_id);
    $user_id = $_SESSION['user_id'];
    if (mysqli_fetch_array($result)[0] != $user_id && $_SESSION['role'] != "admin") {
        echo "<p>You do not have permissions to delete this group</p>";
        exit();
    }
    // Delete group
    query("DELETE FROM `group` WHERE group_id = ?;", 'i', $group_id);
    load('groups.php');
}

// Validate ID
if (empty($_REQUEST['id'])) {
    echo "<p>No group specified</p>";
    exit();
} else {
    $group_id = intval(trim($_REQUEST['id']));
}
// Check if group exists
$group_result = query("SELECT * FROM `group` WHERE group_id = ?;", 'i', $group_id);
if (mysqli_num_rows($group_result) == 0) load('./404.html');
// Check if user is owner, member or neither
$owner_result = query("SELECT owner_id FROM `group` WHERE group_id = ?;", 'i', $group_id);
$user_id = $_SESSION['user_id'];
$member_result = query("SELECT user.user_id FROM user, group_member WHERE user.user_id = group_member.user_id AND user.role = 'teacher' AND user.user_id = ?;", 'i', $user_id);
if (mysqli_fetch_array($owner_result)[0] == $user_id || $_SESSION['role'] == "admin") {
    // User is owner or admin
    $owner = true;
} elseif (mysqli_num_rows($member_result) != 0) {
    // User is member
    $owner = false;
} else {
    // User is neither
    echo "<p>You are not part of this group</p>";
    exit();
}
?>

<!DOCTYPE html>
<html>
    <head>
        <?php
        // Get group details
        $group = mysqli_fetch_assoc($group_result);
        $group_id = $group['group_id'];
        $name = $group['name'];
        echo "<title>$name</title>";
        ?>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <?php include("includes/nav.php");
        echo "
             <div class='title'>
                 <h1>$name</h1>
             ";
        if ($owner) echo "<a href='/edit-group.php?id=$group_id'>Edit Group</a>";
        echo "</div>";
        echo "
             <div class='group'>
                 <h2>Pupils: </h2>
             ";
        $result = query("SELECT first_name, last_name FROM user, group_member WHERE user.user_id = group_member.user_id AND user.role = 'student' AND group_member.group_id = ?;", 'i', $group_id);
        while ($row = mysqli_fetch_assoc($result)) {
            $name = $row['first_name'] . " " . $row['last_name'];
            echo "<p>$name</p><br>";
        }
        echo "<h2>Other teacher(s): </h2>";
        $result = query("SELECT first_name, last_name FROM user, group_member WHERE user.user_id = group_member.user_id AND user.role = 'teacher' AND user.user_id != ? AND group_member.group_id = ?;", 'ii', $user_id, $group_id);
        while ($row = mysqli_fetch_assoc($result)) {
            $name = $row['first_name'] . " " . $row['last_name'];
            echo "<p>$name</p><br>";
        }
        echo "
             <div class='buttons'>
                 <a href='/create-task.php?group_id=$group_id'>Set Task</a>
             ";
        if ($owner) echo "
                 <form method='POST' action=''>
                     <input type='hidden' name='action' value='delete'>
                     <input type='hidden' name='group_id' value=$group_id>
                     <input type='submit' onClick=\"javascript: return confirm('Are you sure you want to delete this group? This will also remove all tasks set to this group.');\" value='Delete'>
                 </form>
             ";
        echo "</div>";
        ?>
    </body>
</html>
