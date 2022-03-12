<?php
require('./utils/auth.php');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Validate action
    if (empty($_POST['action'])) {
        info("error", "Task", "You must specify an action");
    } elseif ($_POST['action'] != "delete") {
        // Action is wrong
        info("error", "Task", "Invalid action");
    } else {
        $action = "delete";
    }
    // Validate task_id
    if (empty($_POST['task_id'])) {
        info("error", "Task", "You must specify a Task ID");
    } else {
        $task_id = mysqli_real_escape_string($db, trim($_POST['task_id']));
    }
    // Check task exists
    $result = query("SELECT task_id FROM task WHERE task_id = ?;", 'i', $task_id);
    if (mysqli_num_rows($result) == 0) {
        info("error", "Task", "Task not found");
    }
    // Check user has permissions
    $result = query("SELECT owner_id FROM task WHERE task_id = ?;", 'i', $task_id);
    $user_id = $_SESSION['user_id'];
    if (mysqli_fetch_array($result)[0] != $user_id && $_SESSION['role'] != "admin") {
        info("error", "Task", "You do not have permissions to delete this task");
    }
    // Delete task
    query("DELETE FROM task WHERE task_id = ?;", 'i', $task_id);
    load('tasks');
}

// Validate ID
if (empty($_GET['id'])) {
    info("error", "Task", "No task specified", "tasks");
} else {
    $task_id = intval(trim($_GET['id']));
}
// Check if task exists
$task_result = query("SELECT * FROM task WHERE task_id = ?;", 'i', $task_id);
$task = mysqli_fetch_assoc($task_result);
if (mysqli_num_rows($task_result) == 0) info("error", "Task", "Task doesn't exist", "tasks");
// Check user's permissions
$user_id = $_SESSION['user_id'];
// Check what role the user is
$owner = false;
if ($_SESSION['role'] == "student") {
    $teacher = false;
} else {
    $teacher = true;
    if ($task['owner_id'] == $user_id || $_SESSION['role'] == "admin") $owner = true;
}
$result = query("SELECT user.user_id FROM user, `group`, group_member, task_recipient WHERE user.user_id = group_member.user_id AND group_member.group_id = group.group_id AND group.group_id = task_recipient.group_id AND user.user_id = ? AND task_recipient.task_id = ?;", 'ii', $user_id, $task_id);
if (mysqli_num_rows($result) == 0 && !$owner) {
    load('tasks');
}
?>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <?php
        // Get task details
        $task_id = $task['task_id'];
        $owner_id = $task['owner_id'];
        $title = $task['title'];
        $description = htmlspecialchars($task['description']);
        $due_date = date('d/m/y', strtotime($task['due_date']));
        $created_at = date('H:i d/m/y', strtotime($task['created_at']));
        $updated_at = date('H:i d/m/y', strtotime($task['updated_at']));
        $result = mysqli_query($db, "SELECT first_name, last_name FROM user WHERE user_id = $owner_id;");
        $owner_name = implode(' ', mysqli_fetch_assoc($result));
        echo "<title>$title</title>";
        ?>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
    </head>
    <body>
        <?php include("includes/nav.php");
        if ($_SESSION['role'] != "student") {
            $groups = array();
            $group_result = mysqli_query($db, "SELECT name FROM `group`, task_recipient WHERE group.group_id = task_recipient.group_id AND task_recipient.task_id = $task_id;");
            while ($group = mysqli_fetch_assoc($group_result)) {
                $groups[] = $group['name'];
            }
            $groups = implode(', ', $groups);
        } else {
            $group_result = mysqli_query($db, "SELECT name FROM user, `group`, group_member, task_recipient WHERE user.user_id = group_member.user_id AND group_member.group_id = group.group_id AND group.group_id = task_recipient.group_id AND user.user_id = $user_id AND task_recipient.task_id = $task_id;");
            $groups = mysqli_fetch_assoc($group_result)['name'];
        }
        echo "
             <div class='title'>
                 <h1>$title</h1>
             ";
        if ($owner) echo "<a href='/edit-task/$task_id'>Edit Task</a>";
        echo "</div>";
        echo "
             <div class='box'>
                 <div id='content'>
                     <div class='description'>
                     <h2>Description: </h2>
             ";
        echo "<p>$description</p>";
        echo "</div>";
        echo "<div class='details'>";
        echo "<p><b>Owner</b>: $owner_name</p>";
        echo ($teacher) ? "<p><b>Group(s)</b>: $groups" : "<p><b>Group</b>: $groups";
        echo "<p><b>Due</b>: $due_date</p>";
        if ($teacher) {
            $count = count_submitted($task_id);
            $num_result = mysqli_query($db, "SELECT COUNT(DISTINCT(user.user_id)) FROM user, `group`, group_member, task_recipient WHERE task_recipient.group_id = group.group_id AND group_member.user_id = user.user_id AND group_member.group_id = group.group_id AND task_id = $task_id AND role = 'student';");
            $num = mysqli_fetch_array($num_result)[0];
            echo "<p><b>Submitted</b>: $count/$num</p>";
        }

        echo "<p><b>Created at</b>: $created_at</p>";
        if ($created_at != $updated_at) echo "<p><b>Last edited at</b>: $updated_at</p>";
        echo "         
             </div></div>";

        echo "<div class='buttons'>";
        echo ($teacher) ? "<a href='/view-code/$task_id'>View submitted code</a>" : "<a href='/upload-code/$task_id'>Upload Code</a>";
        if ($owner) echo "
                 <form method='POST' id='action'>
                     <input type='hidden' name='action' value='delete'>
                     <input type='hidden' name='task_id' value=$task_id>
                     <input type='submit' id='delete' onclick=\"javascript: return confirm('Are you sure you want to delete this task? This will also remove all submitted solutions.');\" value='Delete'>
                 </form>
             ";
        echo "</div>
            </div>";
        ?>
    </body>
</html>
