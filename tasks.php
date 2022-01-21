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
        $result = ($_SESSION['role'] == "admin") ? mysqli_query($db, "SELECT * FROM task;") : mysqli_query($db, "SELECT * FROM task WHERE owner_id = $user_id OR task_id IN(SELECT task_recipient.task_id FROM user, group, group_member, task_recipient WHERE user.user_id = group_member.user_id AND group_member.group_id = group.group_id AND group.group_id = task_recipient.group_id AND user.user_id = $user_id);");
        $num_results = mysqli_num_rows($result);
        $num_pages = ceil($num_results / 5);
        $first_result = ($page - 1) * 5;
        $result = ($_SESSION['role'] == "admin") ? mysqli_query($db, "SELECT * FROM task LIMIT $first_result, 5;") : mysqli_query($db, "SELECT * FROM task WHERE owner_id = $user_id OR task_id IN(SELECT task_recipient.task_id FROM user, group, group_member, task_recipient WHERE user.user_id = group_member.user_id AND group_member.group_id = group.group_id AND group.group_id = task_recipient.group_id AND user.user_id = $user_id) LIMIT $first_result, 5;");

        while ($row = mysqli_fetch_assoc($result)) {
            $title = $row['title'];
            $task_id = $row['task_id'];
            $due_date = $row['due'];
            $groups = array();
            $group_result = mysqli_query($db, "SELECT name FROM `group`, task_recipient WHERE group.group_id = task_recipient.group_id AND task_recipient.task_id = $task_id;");
            while ($group = mysqli_fetch_assoc($group_result)) {
                $groups[] = $group['name'];
            }
            $groups = implode(', ', $groups);
            echo "<div class='group' onclick=\"location.href='/task.php?id=$task_id';\" style='cursor: pointer;'>
                    <p>$title</p><br>
                    <p>$groups</p><br>
                    <p>Due date: $due_date</p><br>
                    <p>??/?? Submitted</p>
                  </div>";
        }
        for ($page=1; $page <= $num_pages; $page++) {
            echo "<a href='tasks.php?page=$page'>$page </a>";
          }
        ?>
    </body>
</html>
