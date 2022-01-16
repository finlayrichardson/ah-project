<?php
require('./auth.php');
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Validate name
    if (empty($_POST['name'])) {
        $errors[] = "Please enter a name";
    } elseif (!preg_match("/[a-zA-Z0-9äöüßÄÖÜ ]/", $_POST['name'])) {
        $errors[] = "Name must contain only letters and numbers";
    } else {
        $name = mysqli_real_escape_string($db, trim($_POST['name']));
    }
    // Validate users
    if (empty($_POST['names'])) {
        $errors[] = "Please enter at least 1 student";
    } else {
        $names = $_POST['names'];
        if (!empty($_POST['teachers'])) $names = array_merge($names, $_POST['teachers']);
    }
    // Check all user_ids are valid
    if (empty($errors)) {
        foreach($names as $user_id) {
            if (gettype($user_id) != "integer") {
                $errors[] = "Invalid User ID";
                break;
            }
            $result = query("SELECT user_id FROM user WHERE user_id = ?", 'i', $user_id);
            if (mysqli_num_rows($result) == 0) {
                $errors[] = "User not found with ID: $user_id";
            }
        }
    }
    // Create group or display errors
    if (empty($errors)) {
        $user_id = $_SESSION['user_id'];
        query("INSERT INTO `group` (owner_id, name) VALUES (?, ?);", 'is', $user_id, $name);
        $result = query("SELECT group_id FROM `group` WHERE name = ?;", 's', $name);
        $group_id = mysqli_fetch_row($result)[0];
        foreach($names as $user_id) {
            query("INSERT INTO group_member VALUES (?, ?);", 'ii', $user_id, $group_id);
        }
        load("group.php?id=$group_id");
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
        <title>Create Group</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <?php include("includes/nav.php");?>
        <div class='title'>
            <h1>Create Group</h1>
        </div>
        <form method="POST" action="">
            <input type="text" name="name" required autofocus placeholder="Name" value="<?php if (isset($_POST['name'])) echo $_POST['name'];?>"><br>
            <input type="text" name="pupils[]" required placeholder="Pupils" value="<?php if (isset($_POST['pupils'])) echo $_POST['pupils'];?>"><br>
            <input type="text" name="teachers[]" required placeholder="Other Teachers" value="<?php if (isset($_POST['teachers'])) echo $_POST['teachers'];?>"><br>
        <input type="submit" value="Create Group">
    </form>
    </body>
</html>
