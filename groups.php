<?php
require('./auth.php');
if ($_SESSION['role'] == "student") load('index');
?>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <title>Groups</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
        <script type="text/javascript" src="https://livejs.com/live.js"></script>
    </head>
    <body>
        <?php include("includes/nav.php");?>
        <div class="title">
            <h1>Groups</h1>
            <a href="/create-group">Create Group</a>
        </div>
        <div class='groups'>
            <?php
            $user_id = $_SESSION['user_id'];
            $result = ($_SESSION['role'] == "admin") ? mysqli_query($db, "SELECT * FROM `group`;") : mysqli_query($db, "SELECT * FROM `group` WHERE owner_id = $user_id OR group_id IN(SELECT group_member.group_id FROM user, group_member WHERE user.user_id = group_member.user_id AND group_member.user_id = $user_id);");
            while ($row = mysqli_fetch_assoc($result)) {
                $name = $row['name'];
                $group_id = $row['group_id'];
                $student_nums = mysqli_query($db, "SELECT COUNT(*) FROM group_member, user WHERE group_member.user_id = user.user_id AND group_member.group_id = $group_id AND role = 'student';");
                $student_num = $student_nums ? mysqli_fetch_array($student_nums)[0] : 0;

                echo "<div class='group' onclick='location.href=\"/group/$group_id\";' style='cursor: pointer;'>
                        <p>Name: $name</p><br>
                        <p>Students: $student_num</p>
                    </div>";
            }
            ?>
        </div>
    </body>
</html>
