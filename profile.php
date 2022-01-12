<?php
require('./auth.php');
$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$email = $_SESSION['email'];
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Profile</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <?php include("includes/nav.php");?>
        <div class="title">
            <h1>Profile</h1>
            <a href="/edit-profile.php">Edit Profile</a>
        </div>
        <div>
        <?php
        $groups = array();
        // Check for groups that the user owns
        $result = mysqli_query($db, "SELECT name FROM `group`, user WHERE `group`.owner_id = user.user_id AND user.user_id = $user_id;");
        if (mysqli_num_rows($result) != 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $groups[] = $row['name'];
            }
        }
        // Check for groups that the user is a member of
        $result = mysqli_query($db, "SELECT name FROM `group`, user, group_member WHERE `group`.group_id = group_member.group_id AND user.user_id = group_member.user_id AND user.user_id = $user_id;");
        if (mysqli_num_rows($result) != 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $groups[] = $row['name'];
            }
        }
        $groups = implode(', ', $groups);
        echo "
        <p>First Name: $first_name</p><br>
        <p>Last Name: $last_name</p><br>
        <p>Email: $email</p><br>
        <p>Groups: $groups</p>
        ";
        ?>
        </div>
    </body>
</html>
