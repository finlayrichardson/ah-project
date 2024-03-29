<?php
require('./utils/auth.php');
if ($_SESSION['role'] != "admin") load('index');
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Validate action
    if (empty($_POST['action'])) {
        info("error", "Admin", "You must specify an action");
    } elseif (!in_array($_POST['action'], array('promote', 'demote', 'delete'))) {
        // Action is wrong
        info("error", "Admin", "Invalid action");
    } else {
        $action = mysqli_real_escape_string($db, trim($_POST['action']));
    }
    // Validate user_id
    if (empty($_POST['user_id'])) {
        info("error", "Admin", "You must specify a User ID");
    } else {
        $user_id = intval(trim($_POST['user_id']));
    }
    $result = query("SELECT user_id FROM user WHERE user_id = ?;", 'i', $user_id);
    if (mysqli_num_rows($result) == 0) {
        info("error", "Admin", "User not found");
    } 
    // Do action
    switch($_POST['action']) {
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
            break;
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
            break;
        case "delete":
            // Delete user
            query("DELETE FROM user WHERE user_id = ?;", 'i', $user_id);
            remove_dir("./code/*/$user_id");
            // Delete task folders if they are teacher
            $result = query("SELECT role FROM user WHERE user_id = ?;", 'i', $user_id);
            if (mysqli_fetch_assoc($result)['role'] != "student") {
                $result = query("SELECT task_id FROM task WHERE owner_id = ?;", 'i', $user_id);
                while ($row = mysqli_fetch_assoc($result)) {
                    $task_id = $row['task_id'];
                    remove_dir("./code/$task_id");
                }
            }
            break;
    }
}
?>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <title>Admin</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
    </head>
    <body>
        <?php include("includes/nav.php");?>
        <div class='title'>
            <h1>Users</h1>
        </div>
        <div class='box' id='admin'>
            <table>
                <tr><th>User ID</th><th>Email</th><th>First Name</th><th>Last Name</th><th>Role</th><th>Verified</th><th></th><th></th><th></th></tr>
            <?php
            $user_id = $_SESSION['user_id'];
            $result = mysqli_query($db, "SELECT * FROM user WHERE user_id != $user_id ORDER BY FIELD(role,'admin','teacher','student'), user_id;");
            while ($row = mysqli_fetch_assoc($result)) {
                $user_id = $row['user_id'];
                $email = $row['email'];
                $first_name = $row['first_name'];
                $last_name = $row['last_name'];
                $role = ucfirst($row['role']);
                $verified = ($row['verified'] == 1) ? "&nbsp;&nbsp;&nbsp;&nbsp;✓" : "&nbsp;&nbsp;&nbsp;&nbsp;✕";

                echo "
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;$user_id</td>
                    <td>$email</td>
                    <td>$first_name</td>
                    <td>$last_name</td>
                    <td>$role</td>
                    <td>$verified</td>
                ";
                echo ($role != "Admin") ? "<td><form method='POST' id='action'>
                                                    <input type='hidden' name='action' value='promote'>
                                                    <input type='hidden' name='user_id' value=$user_id>
                                                    <input type='submit' value='Promote'>
                                        </form></td>" : "<td></td>";
                echo ($role != "Student") ? "<td><form method='POST' id='action'>
                                                    <input type='hidden' name='action' value='demote'>
                                                    <input type='hidden' name='user_id' value=$user_id>
                                                    <input type='submit' value='Demote'>
                                        </form></td>" : "<td></td>";
                echo "<td><form method='POST' id='action'>
                            <input type='hidden' name='action' value='delete'>
                            <input type='hidden' name='user_id' value=$user_id>
                            <input type='submit' id='delete' onclick=\"javascript: return confirm('Are you sure you want to delete this account? This will remove them from all groups they are part of and delete all tasks they own.');\" value='Delete'>
                    </form></td></tr>";
            }
            ?>
            </table>
        </div>
    </body>
</html>
