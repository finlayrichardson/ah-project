<?php
require('./auth.php');

if (!isset($_GET['page'])) {
    $page = 1;
  } else {
    $page = intval($_GET['page']);
  }
?>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <title>Tasks</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <?php include("includes/nav.php");?>
        <div class="title">
            <h1>Tasks</h1>
            <?php
            if ($_SESSION['role'] != "student") echo "<a href='/create-task.php'>Create Task</a>";
            ?>
        </div>
        <?php
        $user_id = $_SESSION['user_id'];
        $result = ($_SESSION['role'] == "admin") ? mysqli_query($db, "SELECT * FROM task;") : mysqli_query($db, "SELECT * FROM task WHERE owner_id = $user_id OR task_id IN(SELECT task_recipient.task_id FROM user, `group`, group_member, task_recipient WHERE user.user_id = group_member.user_id AND group_member.group_id = group.group_id AND group.group_id = task_recipient.group_id AND user.user_id = $user_id);");
        $num_results = mysqli_num_rows($result);
        $num_pages = ceil($num_results / 10);
        $first_result = ($page - 1) * 10;
        $result = ($_SESSION['role'] == "admin") ? mysqli_query($db, "SELECT * FROM task LIMIT $first_result, 10;") : mysqli_query($db, "SELECT * FROM task WHERE owner_id = $user_id OR task_id IN(SELECT task_recipient.task_id FROM user, `group`, group_member, task_recipient WHERE user.user_id = group_member.user_id AND group_member.group_id = group.group_id AND group.group_id = task_recipient.group_id AND user.user_id = $user_id) LIMIT $first_result, 5;");

        while ($row = mysqli_fetch_assoc($result)) {
            $title = $row['title'];
            $task_id = $row['task_id'];
            $due_date = $row['due_date'];
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
                $teacher_result = mysqli_query($db, "SELECT last_name from user, `group`, task, task_recipient, group_member WHERE user.user_id = task.owner_id AND task.task_id = task_recipient.task_id AND task_recipient.group_id = group.group_id AND group.group_id = group_member.group_id AND group_member.user_id = $user_id and task.task_id = $task_id GROUP BY last_name;");
                $teacher = mysqli_fetch_assoc($teacher_result)['last_name'];
            }

            echo "<div class='task' onclick=\"location.href='/task.php?id=$task_id';\" style='cursor: pointer;'>
                    <p>$title</p><br>
                    <p>$groups</p><br>";
            if ($_SESSION['role'] == "student") echo "<p>$teacher</p><br>";
            echo "<p>Due date: $due_date</p><br>";
            if ($_SESSION['role'] != "student") {
                $count = count_submitted($task_id);
                $num_result = mysqli_query($db, "SELECT COUNT(user.user_id) FROM user, `group`, group_member, task_recipient WHERE task_recipient.group_id = group.group_id AND group_member.user_id = user.user_id AND group_member.group_id = group.group_id AND task_id = $task_id AND user.role = 'student' GROUP BY user.user_id;"); // maybe fix this awful query
                $num = mysqli_fetch_array($num_result)[0];
                echo "<p>$count/$num Submitted</p>";
            }
            echo "</div><br>";
        }
        for ($page = 1; $page <= $num_pages; $page++) {
            echo "<a href='tasks.php?page=$page'>$page</a>";
          }
        ?>
    </body>
</html>
