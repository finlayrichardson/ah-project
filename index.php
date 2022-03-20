<?php
require('./utils/auth.php');
?>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <title>Codecanopy</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
    </head>
    <body>
        <?php include("includes/nav.php");?>
        <div class='title'>
            <h1>Tasks</h1>
            <?php
            if ($_SESSION['role'] != "student") echo "<a href='/create-task'>Create Task</a>";
            ?>
        </div>
        <?php
        $user_id = $_SESSION['user_id'];
        $result = ($_SESSION['role'] == "admin") ? mysqli_query($db, "SELECT * FROM task ORDER BY due_date DESC;") : mysqli_query($db, "SELECT * FROM task WHERE owner_id = $user_id OR task_id IN(SELECT task_recipient.task_id FROM user, `group`, group_member, task_recipient WHERE user.user_id = group_member.user_id AND group_member.group_id = group.group_id AND group.group_id = task_recipient.group_id AND user.user_id = $user_id) ORDER BY due_date DESC;");

        if (mysqli_num_rows($result) == 0) {
            echo "
                <div class='no-content' style='width: calc(100% - 100px);'>
                    <p>No tasks available</p>
                </div>
                ";
        } else {
            $counter = 0;
            while (($row = mysqli_fetch_assoc($result)) && ($counter < 5)) {
                $task_id = $row['task_id'];
                if (!is_completed($task_id, $user_id)) {
                    $title = $row['title'];
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
                        $width = ($num == 0) ? 0 : $count / $num * 100;
                    } else {
                        $group_result = mysqli_query($db, "SELECT name FROM user, `group`, group_member, task_recipient WHERE user.user_id = group_member.user_id AND group_member.group_id = group.group_id AND group.group_id = task_recipient.group_id AND user.user_id = $user_id AND task_recipient.task_id = $task_id;");
                        $groups = mysqli_fetch_assoc($group_result)['name'];
                        $teacher_result = mysqli_query($db, "SELECT first_name, last_name from user, `group`, task, task_recipient, group_member WHERE user.user_id = task.owner_id AND task.task_id = task_recipient.task_id AND task_recipient.group_id = group.group_id AND group.group_id = group_member.group_id AND group_member.user_id = $user_id and task.task_id = $task_id GROUP BY last_name;");
                        $teacher = mysqli_fetch_assoc($teacher_result);
                        $teacher_name = $teacher['first_name'] . " " . $teacher['last_name'];
                    }
        
                    echo "
                    <div class='task' onclick='location.href=\"/task/$task_id\";'>
                        <div class='info left'>
                            <p class='entity-title'><b>$title</b></p>
                            <p>$groups</p>";
                    if ($_SESSION['role'] == "student") echo "<p>$teacher_name</p>"; // figure out something with title
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
                    $counter++;
                }
            }
        }
        ?>
        <div id='more-tasks'>
            <a href='/tasks'>More Tasks</a>
        </div>
    </body>
</html>
