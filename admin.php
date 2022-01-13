<?php
require('./auth.php');
if ($_SESSION['role'] != "admin") load('index.php');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $errors = array();
    // Validate action
    if (empty($_REQUEST['action'])) {
        $errors[] = "You must specify an action";
    } elseif (!in_array($_REQUEST['action'], array('promote', 'demote', 'delete'))) {
        // Action is wrong
        $errors[] = "Invalid action";
    } else {
        $action = mysqli_real_escape_string($db, trim($_REQUEST['action']));
    }
    // Validate user_id
    if (empty($_REQUEST['user_id'])) {
        $errors[] = "You must specify a user_id";
    } else {
        $user_id = mysqli_real_escape_string($db, trim($_REQUEST['user_id']));
    }
    if (empty($errors)) {
        $result = query("SELECT user_id FROM user WHERE user_id = ?;", 'i', $user_id);
        if (mysqli_num_rows($result) == 0) $errors[] = "User not found";
    }
    // Do action or display errors
    if (empty($errors)) {
        switch($_REQUEST['action']) {
            // User needs to be promoted
            case "promote":
                // Check user's current role
                $result = query("SELECT role FROM user WHERE user_id = ?;", 'i', $user_id);
                if (mysqli_fetch_assoc($result)['role'] == "student") {
                    // Change to teacher
                    query("UPDATE user SET role = 'teacher' WHERE user_id = ?;", 'i', $user_id);
                } else {
                    // Change to admin
                    query("UPDATE user SET role = 'admin' WHERE user_id = ?;", 'i', $user_id);
                }
            case "demote":
                // Check user's current role
                $result = query("SELECT role FROM user WHERE user_id = ?;", 'i', $user_id);
                if (mysqli_fetch_assoc($result)['role'] == "admin") {
                    // Change to teacher
                    query("UPDATE user SET role = 'teacher' WHERE user_id = ?;", 'i', $user_id);
                } else {
                    // Change to student
                    query("UPDATE user SET role = 'student' WHERE user_id = ?;", 'i', $user_id);
                }
            case "delete":
                // Delete all references of user
                query("DELETE FROM token WHERE user_id = ?;", 'i', $user_id);
                query("DELETE FROM group_member WHERE user_id = ?;", 'i', $user_id);
                query("DELETE FROM task_recipient, group WHERE task_recipient.group_id = group.group_id AND group.owner_id = ?;", 'i', $user_id);
                query("DELETE FROM task_recipient, task WHERE task_recipient.task_id = task.task_id AND task.owner_id = ?;", 'i', $user_id);
                query("DELETE FROM group WHERE owner_id = ?;", 'i', $user_id);
                query("DELETE FROM task WHERE owner_id = ?;", 'i', $user_id);
        }
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
<html>
    <head>
        <title></title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <?php include("includes/nav.php");?>
        <h1>Users</h1>
        <table>
            <tr><th>User ID</th><th>Email</th><th>First Name</th><th>Last Name</th><th>Role</th><th>Verified</th><th></th><th></th><th></th></tr>
        <?php
        $result = mysqli_query($db, "SELECT * FROM user WHERE user_id != $user_id ORDER BY FIELD(role,'admin','teacher','student'), user_id;");
        while ($row = mysqli_fetch_assoc($result)) {
            $user_id = $row['user_id'];
            $email = $row['email'];
            $first_name = $row['first_name'];
            $last_name = $row['last_name'];
            $role = $row['role'];
            $verified = $row['verified'] == 1 ? "Yes" : "No";

            echo "
            <tr>
                <td>$user_id</td>
                <td>$email</td>
                <td>$first_name</td>
                <td>$last_name</td>
                <td>$role</td>
                <td>$verified</td>
            ";
            echo ($role != "admin") ? "<td><a href='admin.php?action=promote&user_id=$user_id'>Promote</a></td>" : "<td></td>"; // change these to post
            echo ($role != "student") ? "<td><a href='admin.php?action=demote&user_id=$user_id'>Demote</a></td>" : "<td></td>";
            
            echo "
                <td><a onClick=\"javascript: return confirm('Are you sure you want to delete this account? This will remove them from all groups they are part of and delete all tasks they own.');\" href='action=delete&user_id=$user_id'>Delete</a></td>
            </tr>
            ";
        }
        ?>
        </table>
    </body>
</html>
