<?php
require('./auth.php');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $errors = array();
    // Validate First Name
    if (empty($_POST['first_name'])) {
        $errors[] = "Please enter a first name";
    } elseif (!preg_match("/[a-zA-ZäöüßÄÖÜ ]/", $_POST['first_name'])) {
        $errors[] = "First name must contain only letters";
    } else {
        $first_name = mysqli_real_escape_string($db, trim($_POST['first_name']));
    }
    // Validate Last Name
    if (empty($_POST['last_name'])) {
        $errors[] = "Please enter a last name";
    } elseif (!preg_match('/[a-zA-ZäöüßÄÖÜ ]/', $_POST['last_name'])) {
        $errors[] = "Last name must contain only letters";
    } else {
        $last_name = mysqli_real_escape_string($db, trim($_POST['last_name']));
    }
    // Validate password
    if (!empty($_POST['password1'])) {
        if (empty($_POST['password2'])) {
            $errors[] = "Please confirm password";
        } else {
            if ($_POST['password1'] !== $_POST['password2']) {
                $errors[] = "Passwords must match";
            } else {
                $password = password_hash(mysqli_real_escape_string($db, trim($_POST['password1'])), PASSWORD_BCRYPT);
            }
        }
    }
    // Check if errors should be displayed or user should be updated
    if (empty($errors)) {
        // Update user details
        if (isset($password)) {
            query("UPDATE user SET first_name = ?, last_name = ?, password = ? WHERE user_id = ?;", 'sssi', $first_name, $last_name, $password, $user_id);
        } else {
            query("UPDATE user SET first_name = ?, last_name = ? WHERE user_id = ?;", 'ssi', $first_name, $last_name, $user_id);
        }
        // Change session variables
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;

        load('profile.php');
    } else {
        // Display errors
        echo "<h1>Error!</h1>
        <p>The following error(s) occured:<br>";
        foreach ($errors as $msg) {
            echo "- $msg<br>";
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
        <h1>Edit Profile</h1>
        <form method="POST" action="">
            <input type="text" name="first_name" required pattern="[a-zA-ZäöüßÄÖÜ ]+" placeholder="First Name" value="<?php echo $_SESSION['first_name'];?>"><br>
            <input type="text" name="last_name" required pattern="[a-zA-ZäöüßÄÖÜ ]+" placeholder="Last Name" value="<?php echo $_SESSION['last_name'];?>"><br>
            <input type="password" name="password1" placeholder="New Password" value="<?php if (isset($_POST['password1'])) echo $_POST['password1'];?>"><br>
            <input type="password" name="password2" placeholder="Confirm New Password" value="<?php if (isset($_POST['password2'])) echo $_POST['password2'];?>"><br>
            <input type="submit" value="Save Changes">
        </form>
    </body>
</html>
