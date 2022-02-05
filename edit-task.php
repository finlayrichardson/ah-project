<?php
require('./auth.php');
// Validate ID
if (empty($_REQUEST['id'])) {
    echo "<p>No task specified</p>";
    exit();
} else {
    $task_id = intval(trim($_REQUEST['id']));
}
// Check if task exists
$task_result = query("SELECT * FROM task WHERE task_id = ?;", 'i', $task_id);
$task = mysqli_fetch_assoc($task_result);
if (mysqli_num_rows($task_result) == 0) load('404.html');
// Check if user is owner
$owner_result = query("SELECT owner_id FROM task WHERE task_id = ?;", 'i', $task_id);
$user_id = $_SESSION['user_id'];
if (mysqli_fetch_array($owner_result)[0] != $user_id && $_SESSION['role'] != "admin") load('groups');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Validate title
    if (empty($_POST['title'])) {
        $errors[] = "Please enter a title";
    } elseif (!preg_match("/[-a-zA-Z0-9äöüßÄÖÜ ]/", $_POST['title'])) {
        $errors[] = "Title must not contain special characters";
    } elseif (strlen(trim($_POST['title'])) > 100) {
        $errors[] = "Title must be max 100 characters";
    } else {
        $title = mysqli_real_escape_string($db, trim($_POST['title']));
    }
    // Validate groups
    if (empty($_POST['groups'])) {
        $errors[] = "Please enter at least 1 group";
    } else {
        $groups = $_POST['groups'];
    }
    // Validate group_ids
    if (empty($errors)) {
        foreach($groups as $group_id) {
            if (!intval($group_id)) {
                $errors[] = "Invalid Group ID: $group_id";
                break;
            }
            $result = query("SELECT group_id FROM `group` WHERE group_id = ?;", 'i', intval($group_id));
            if (mysqli_num_rows($result) == 0) {
                $errors[] = "Group not found with ID: $group_id";
            }
        }
    }
    if (empty($errors)) {
        $user_id = $_SESSION['user_id'];
        foreach($groups as $group_id) {
            if (!teacher_status($user_id, $group_id)) {
                $errors[] = "You don't have permissions to set a task to this group";
                break;
            }
        }
    }
    // Validate due date
    if (empty($_POST['due_date'])) {
        $errors[] = "Please enter a due date";
    } elseif (!preg_match("/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/", $_POST['due_date'])) {
        $errors[] = "Please enter a valid date";
    } else {
        $due_date = mysqli_real_escape_string($db, trim($_POST['due_date']));
    }
    // Validate description
    if (isset($_POST['description'])) {
        $description = mysqli_real_escape_string($db, trim($_POST['description']));
    } else {
        $description = "";
    }
    // Edit task or display errors
    if (empty($errors)) {
        $user_id = $_SESSION['user_id'];
        query("UPDATE task SET title = ?, description = ?, due_date = ?, updated_at = NOW() WHERE task_id = ?;", 'sssi', $title, $description, $due_date, $task_id);
        query("DELETE FROM task_recipient WHERE task_id = ?;", 'i', $task_id);
        foreach($groups as $group_id) {
            query("INSERT INTO task_recipient VALUES (?, ?);", 'ii', $task_id, intval($group_id));
        }
        load("task/$task_id");;
    } else {
        // Display errors
        echo "<h1>Error!</h1>
        <p>The following error(s) occured:<br>";
        foreach ($errors as $error) {
            echo "- $error<br>";
        }
        echo "<p>Please try again.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
        <script>
            $(document).ready(function() {
                $('.groups').select2({
                    placeholder: "Group(s)"
                });
                <?php
                // Populate groups field
                $result = query("SELECT group.group_id FROM task_recipient, `group` WHERE group.group_id = task_recipient.group_id AND task_recipient.task_id = ?;", 'i', $task_id);
                $group_ids = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $group_ids[] = strval($row['group_id']);
                }
                $group_ids = implode(", ", $group_ids);
                echo "$('.groups').val([$group_ids]);";
                echo "$('.groups').trigger('change');";
                ?>
            });
        </script>
        <title>Edit Task</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
    </head>
    <body>
        <?php include("includes/nav.php");?>
        <div class='title'>
            <h1>Edit Task</h1>
        </div>
        <form method="POST">
            <input type="text" name="title" required pattern="[-a-zA-ZäöüßÄÖÜ ]+" maxlength="100" placeholder="Title" value="<?php echo $task['title'];?>"><br>
            <select name="groups[]" class="groups" multiple>
                <?php
                $result = mysqli_query($db, "SELECT group_id, name FROM `group` WHERE owner_id = $user_id OR group_id IN(SELECT group_member.group_id FROM user, group_member WHERE user.user_id = group_member.user_id AND group_member.user_id = $user_id);");
                while ($row = mysqli_fetch_assoc($result)) {
                    $group_id = $row['group_id'];
                    $name = $row['name'];
                    echo "<option value=$group_id>$name</option>";
                }
                ?>
            </select><br>
            <input type="date" name="due_date" required placeholder="Due Date" value="<?php echo $task['due_date'];?>"><br>
            <textarea name="description" placeholder="Description"><?php echo $task['description'];?></textarea><br>
            <input type="submit" value="Edit Task">
        </form>
    </body>
</html>
