<?php
require('./utils/auth.php');
$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$email = $_SESSION['email'];
?>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <title>Profile</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
        <script type="text/javascript" src="https://livejs.com/live.js"></script>
    </head>
    <body>
        <?php include("includes/nav.php");?>
        <div class="title">
            <h1>Profile</h1>
            <a href="/edit-profile">Edit Profile</a>
        </div>
        <div>
        <?php
        $groups = array();
        // Check for groups that the user owns
        $result = mysqli_query($db, "SELECT name FROM `group` WHERE owner_id = $user_id;");
        if (mysqli_num_rows($result) != 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $groups[] = $row['name'];
            }
        }
        // Check for groups that the user is a member of
        $result = mysqli_query($db, "SELECT name FROM `group`, group_member WHERE `group`.group_id = group_member.group_id AND group_member.user_id = $user_id;");
        if (mysqli_num_rows($result) != 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $groups[] = $row['name'];
            }
        }
        $groups = implode(', ', $groups);
        echo "
        <div class='box'>
            <div id='profile'>
                <p><strong>First Name</strong>: $first_name</p>
                <p><strong>Last Name</strong>: $last_name</p>
                <p><strong>Email</strong>: $email</p>
                <p><strong>Group(s)</strong>: $groups</p>
            </div>
        </div>
        ";
        ?>
        </div>
    </body>
</html>
