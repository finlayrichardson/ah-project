<?php
require('./utils/auth.php');
if ($_SESSION['role'] == "student") load('index');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Validate action
    if (empty($_POST['action'])) {
        info("error", "Group", "You must specify an action");
    } elseif ($_POST['action'] != "delete") {
        // Action is wrong
        info("error", "Group", "Invalid action");
    } else {
        $action = "delete";
    }
    // Validate group_id
    if (empty($_POST['group_id'])) {
        info("error", "Group", "You must specify a Group ID");
    } else {
        $group_id = mysqli_real_escape_string($db, trim($_POST['group_id']));
    }
    // Check group exists
    $result = query("SELECT group_id FROM `group` WHERE group_id = ?;", 'i', $group_id);
    if (mysqli_num_rows($result) == 0) {
        info("error", "Group", "Group not found");
    }
    // Check user has permissions
    $result = query("SELECT owner_id FROM `group` WHERE group_id = ?;", 'i', $group_id);
    $user_id = $_SESSION['user_id'];
    if (mysqli_fetch_array($result)[0] != $user_id && $_SESSION['role'] != "admin") {
        info("error", "Group", "You do not have permissions to delete this group");
    }
    // Delete group
    query("DELETE FROM `group` WHERE group_id = ?;", 'i', $group_id);
    // Delete uploaded solutions for tasks this group was set
    $result = query("SELECT task_id FROM task_recipient WHERE group_id = ?;", 'i', $group_id);
    while ($row = mysqli_fetch_assoc($result)) {
        $task_id = $row['task_id'];
        $result = query("SELECT user_id FROM group_member WHERE group_id = ?", 'i', $group_id);
        while ($row = mysqli_fetch_assoc($result)) {
            $user_id = $row['user_id'];
            rmdir("./code/$task_id/$user_id");
        }
    }
    load('groups');
}

// Validate ID
if (empty($_GET['id'])) {
    info("error", "Group", "No group specified");
} else {
    $group_id = intval(trim($_GET['id']));
}
// Check if group exists
$group_result = query("SELECT * FROM `group` WHERE group_id = ?;", 'i', $group_id);
if (mysqli_num_rows($group_result) == 0) info("error", "Group", "Group doesn't exist", "groups");
// Check if user is owner, member or neither
$user_id = $_SESSION['user_id'];
$status = teacher_status($user_id, $group_id);
switch ($status) {
    case "owner":
        // User is owner or admin
        $owner = true;
        break;
    case "member":
        // User is member
        $owner = false;
        break;
    default:
        // User is neither
        load('groups');
}
?>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <?php
        // Get group details
        $group = mysqli_fetch_assoc($group_result);
        $group_id = $group['group_id'];
        $owner_id = $group['owner_id'];
        $name = $group['name'];
        $result = mysqli_query($db, "SELECT first_name, last_name FROM user WHERE user_id = $owner_id;");
        $owner_name = implode(' ', mysqli_fetch_assoc($result));
        echo "<title>$name</title>";
        ?>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
    </head>
    <body>
        <?php include("includes/nav.php");
        echo "
             <div class='title'>
                 <h1>$name</h1>
             ";
        if ($owner) echo "<a href='/edit-group/$group_id'>Edit Group</a>";
        echo "</div>";
        echo "
             <div class='box'>
                 <p id='owner'><b>Owner</b>: $owner_name</p>
                 <div class='names'>
                     <h2>Students: </h2>
             ";
        $result = query("SELECT first_name, last_name FROM user, group_member WHERE user.user_id = group_member.user_id AND role = 'student' AND group_member.group_id = ?;", 'i', $group_id);
        while ($row = mysqli_fetch_assoc($result)) {
            $name = $row['first_name'] . " " . $row['last_name'];
            echo "<p>$name</p>";
        }
        echo "<h2>Other teacher(s): </h2>";
        $user_id = $_SESSION['user_id'];
        $result = query("SELECT first_name, last_name FROM user, group_member WHERE user.user_id = group_member.user_id AND role = 'teacher' AND user.user_id != ? AND group_member.group_id = ?;", 'ii', $user_id, $group_id);
        while ($row = mysqli_fetch_assoc($result)) {
            $name = $row['first_name'] . " " . $row['last_name'];
            echo "<p>$name</p>";
        }
        echo "</div>";
        echo "
             <div class='buttons'>
                 <a href='/create-task?group_id=$group_id'>Set Task</a>
             ";
        if ($owner) echo "
                 <form method='POST' id='action'>
                     <input type='hidden' name='action' value='delete'>
                     <input type='hidden' name='group_id' value=$group_id>
                     <input type='submit' id='delete' onclick=\"javascript: return confirm('Are you sure you want to delete this group?');\" value='Delete'>
                 </form>
             ";
        echo "</div>
            </div>";
        ?>
    </body>
</html>
