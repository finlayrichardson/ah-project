<?php
require('./auth.php');
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Validate name
    if (empty($_POST['name'])) {
        $errors[] = "Please enter a name";
    } elseif (!preg_match("/[-a-zA-Z0-9äöüßÄÖÜ ]/", $_POST['name'])) {
        $errors[] = "Name must not contain special characters";
    } elseif (strlen(trim($_POST['name'])) > 50) {
        $errors[] = "Name must be max 50 characters";
    } else {
        $name = mysqli_real_escape_string($db, trim($_POST['name']));
    }
    // Validate users
    if (empty($_POST['students'])) {
        $errors[] = "Please enter at least 1 student";
    } else {
        $names = $_POST['students'];
        if (!empty($_POST['teachers'])) $names = array_merge($names, $_POST['teachers']);
    }
    // Validate user_ids
    if (empty($errors)) {
        foreach($names as $user_id) {
            if (!intval($user_id)) {
                $errors[] = "Invalid User ID: $user_id";
                break;
            }
            $result = query("SELECT user_id FROM user WHERE verified = true AND user_id = ?;", 'i', intval($user_id));
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
            query("INSERT INTO group_member VALUES (?, ?);", 'ii', intval($user_id), $group_id);
        }
        load("group/$group_id");
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
<html lang='en'>
    <head>
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
        <script>
            $(document).ready(function() {
                $('.students').select2({
                    placeholder: "Students"
                });

                $('.teachers').select2({
                    placeholder: "Other Teacher(s)"
                });
                <?php
                if (isset($_POST['students'])) {
                    $student_ids = implode(", ", $_POST['students']);
                    echo "$('.students').val([$student_ids]);";
                    echo "$('.students').trigger('change');";
                }
                if (isset($_POST['teachers'])) {
                    $teacher_ids = implode(", ", $_POST['teachers']);
                    echo "$('.teachers').val([$teacher_ids]);";
                    echo "$('.teachers').trigger('change');";
                }
                ?>
            });
        </script>
        <title>Create Group</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
    </head>
    <body>
        <?php include("includes/nav.php");?>
        <div class='title'>
            <h1>Create Group</h1>
        </div>
        <form method="POST">
            <input type="text" name="name" required autofocus pattern="[-a-zA-ZäöüßÄÖÜ ]+" maxlength="50" placeholder="Name" value="<?php if (isset($_POST['name'])) echo $_POST['name'];?>"><br>
            <select name="students[]" class="students" multiple>
                <?php
                $result = mysqli_query($db, "SELECT user_id, first_name, last_name FROM user WHERE role = 'student' AND verified = true;");
                while ($row = mysqli_fetch_assoc($result)) {
                    $student_id = $row['user_id'];
                    $name = $row['first_name'] . " " . $row['last_name'];
                    echo "<option value=$student_id>$name</option>";
                }
                ?>
            </select><br>
            <select name="teachers[]" class="teachers" multiple>
                <?php
                $user_id = $_SESSION['user_id'];
                $result = mysqli_query($db, "SELECT user_id, first_name, last_name FROM user WHERE role != 'student' AND verified = true AND user.user_id != $user_id;");
                while ($row = mysqli_fetch_assoc($result)) {
                    $teacher_id = $row['user_id'];
                    $name = $row['first_name'] . " " . $row['last_name'];
                    echo "<option value=$teacher_id>$name</option>";
                }
                ?>
            </select><br>
            <input type="submit" value="Create Group">
        </form>
    </body>
</html>
