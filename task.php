<?php
require('./auth.php');

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
    // Validate task_id
    if (empty($_POST['task_id'])) {
        echo "<p>You must specify a Task ID</p>";
        exit();
    } else {
        $task_id = mysqli_real_escape_string($db, trim($_POST['task_id']));
    }
    // Check task exists
    $result = query("SELECT task_id FROM task WHERE task_id = ?;", 'i', $task_id);
    if (mysqli_num_rows($result) == 0) {
        echo "<p>Task not found</p>";
        exit();
    }
    // Check user has permissions
    $result = query("SELECT owner_id FROM task WHERE task_id = ?;", 'i', $task_id);
    $user_id = $_SESSION['user_id'];
    if (mysqli_fetch_array($result)[0] != $user_id && $_SESSION['role'] != "admin") {
        echo "<p>You do not have permissions to delete this group</p>";
        exit();
    }
    // Delete group
    query("DELETE FROM task WHERE task_id = ?;", 'i', $task_id);
    load('tasks');
}

// Validate ID
if (empty($_GET['id'])) {
    echo "<p>No task specified</p>";
    exit();
} else {
    $task_id = intval(trim($_GET['id']));
}
// Check if task exists
$task_result = query("SELECT * FROM task WHERE task_id = ?;", 'i', $task_id);
$task = mysqli_fetch_assoc($task_result);
if (mysqli_num_rows($task_result) == 0) load('404.html');
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
        $title = $task['title'];
        $description = $task['description'];
        $due_date = $task['due_date'];
        $created_at = $task['created_at'];
        $updated_at = $task['updated_at'];
        echo "<title>$title</title>";
        ?>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
        <script type="text/javascript" src="https://livejs.com/live.js"></script>
    </head>
    <body>
        <?php include("includes/nav.php");
        echo "
             <div class='title'>
                 <h1>$title</h1>
             ";
        if ($owner) echo "<a href='/edit-task/$task_id'>Edit Task</a>";
        echo "</div>";
        echo "
             <div class='task-box'>
                 <div>
                     <div class='description'>
                     <h2>Description: </h2>
             ";
        echo "<p>$description</p>";
        echo "</div>";
        echo "
             <div class='details'>
                 <p>Due: $due_date</p>
                 <p>Created at: $created_at</p>";
        if ($created_at != $updated_at) echo "<p>Last edited at: $updated_at</p>";
        echo "         
             </div></div>";

        echo "<div class='buttons'>";
        echo ($teacher) ? "<a href='/view-code/$task_id'>View submitted code</a>" : "<a href='/upload-code/$task_id'>Upload Code</a>";
        if ($owner) echo "
                 <form method='POST'>
                     <input type='hidden' name='action' value='delete'>
                     <input type='hidden' name='task_id' value=$task_id>
                     <input type='submit' id='delete' onClick=\"javascript: return confirm('Are you sure you want to delete this task? This will also remove all submitted solutions.');\" value='Delete'>
                 </form>
             ";
        echo "</div>
            </div>";
        ?>
    </body>
</html>
