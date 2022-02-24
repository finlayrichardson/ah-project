<?php
require('./utils/auth.php');

if (!isset($_GET['page'])) {
    $page = 1;
  } else {
    $page = intval($_GET['page']);
  }

$sort = 'desc';
if (isset($_GET['sort'])) {
    if ($_GET['sort'] == 'asc' || $_GET['sort'] == 'desc') $sort = $_GET['sort'];
}
?>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <title>Tasks</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
    </head>
    <body>
        <?php include("includes/nav.php");?>
        <div class='title'>
            <div>
                <h1>Tasks</h1>
                <select class='dropdown' onchange='window.location="tasks?sort="+this.value;'>
                    <option value='desc'>Sort by due date (descending)</option>
                    <option value='asc'>Sort by due date (ascending)</option>
                </select>
            </div>
            <?php
            if ($_SESSION['role'] != "student") echo "<a href='/create-task'>Create Task</a>";
            echo "
                    <script>
                        document.getElementsByClassName('dropdown')[0].value = '$sort';
                    </script>";
            ?>
        </div>
        <?php
        $user_id = $_SESSION['user_id'];
        $result = ($_SESSION['role'] == "admin") ? mysqli_query($db, "SELECT * FROM task;") : mysqli_query($db, "SELECT * FROM task WHERE owner_id = $user_id OR task_id IN(SELECT task_recipient.task_id FROM user, `group`, group_member, task_recipient WHERE user.user_id = group_member.user_id AND group_member.group_id = group.group_id AND group.group_id = task_recipient.group_id AND user.user_id = $user_id);");
        $num_results = mysqli_num_rows($result);
        $num_pages = ceil($num_results / 10);
        $first_result = ($page - 1) * 10;
        $result = ($_SESSION['role'] == "admin") ? mysqli_query($db, "SELECT * FROM task ORDER BY due_date $sort LIMIT $first_result, 10;") : mysqli_query($db, "SELECT * FROM task WHERE owner_id = $user_id OR task_id IN(SELECT task_recipient.task_id FROM user, `group`, group_member, task_recipient WHERE user.user_id = group_member.user_id AND group_member.group_id = group.group_id AND group.group_id = task_recipient.group_id AND user.user_id = $user_id) ORDER BY due_date $sort LIMIT $first_result, 10;");

        while ($row = mysqli_fetch_assoc($result)) {
            $title = $row['title'];
            $task_id = $row['task_id'];
            $due_date = date('d/m/y', strtotime($row['due_date']));
            if ($_SESSION['role'] != "student") {
                $groups = array();
                $group_result = mysqli_query($db, "SELECT name FROM `group`, task_recipient WHERE group.group_id = task_recipient.group_id AND task_recipient.task_id = $task_id;");
                while ($group = mysqli_fetch_assoc($group_result)) {
                    $groups[] = $group['name'];
                }
                $groups = implode(', ', $groups);

                $count = count_submitted($task_id);
                $num_result = mysqli_query($db, "SELECT COUNT(DISTINCT(user.user_id)) FROM user, `group`, group_member, task_recipient WHERE task_recipient.group_id = group.group_id AND group_member.user_id = user.user_id AND group_member.group_id = group.group_id AND task_id = $task_id AND role = 'student';");
                $num = mysqli_fetch_array($num_result)[0];
                $width = $count / $num * 100;
            } else {
                $group_result = mysqli_query($db, "SELECT name FROM user, `group`, group_member, task_recipient WHERE user.user_id = group_member.user_id AND group_member.group_id = group.group_id AND group.group_id = task_recipient.group_id AND user.user_id = $user_id AND task_recipient.task_id = $task_id;");
                $groups = mysqli_fetch_assoc($group_result)['name'];
                $teacher_result = mysqli_query($db, "SELECT last_name from user, `group`, task, task_recipient, group_member WHERE user.user_id = task.owner_id AND task.task_id = task_recipient.task_id AND task_recipient.group_id = group.group_id AND group.group_id = group_member.group_id AND group_member.user_id = $user_id and task.task_id = $task_id GROUP BY last_name;");
                $teacher = mysqli_fetch_assoc($teacher_result)['last_name'];
            }

            echo "
            <div class='task' onclick='location.href=\"/task/$task_id\";' style='cursor: pointer;'>
                <div class='info left'>
                    <p>$title</p>
                    <p>$groups</p>";
            if ($_SESSION['role'] == "student") echo "<p>Mr $teacher</p>"; // figure out something with title
            echo "</div>";

            echo "
            <div class='info middle'>";
            if ($_SESSION['role'] != "student") echo "
            <div id='progress-bar'>
                <div style='width: $width%'></div>
            </div>";
            echo "
            <p>Due date: $due_date</p>
            </div>";
            echo "
            <div class='info right'>";
            if ($_SESSION['role'] != "student") {
                echo "<p>$count/$num Submitted</p>";
            } else {
                echo "<a href='/upload-code/$task_id'>Upload Code</a>";
            }
            
            echo "</div></div>";
        }
        $page_nums = pagination($num_results, 10, $page, 4);
        echo "<div id='page-nums'>";
        foreach ($page_nums as $page_num) {
            echo "<a href='tasks?page=$page_num&sort=$sort'>$page_num</a>";
        }
        echo "</div>";
        ?>
    </body>
</html>
