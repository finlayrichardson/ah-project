<?php
require('./resources/auth.php');
// Validate ID
if (empty($_REQUEST['id'])) {
    echo "<p>No group specified</p>";
    exit();
} else {
    $group_id = intval(trim($_REQUEST['id']));
}
// Check if group exists
$group_result = query("SELECT * FROM `group` WHERE group_id = ?;", 'i', $group_id);
$group = mysqli_fetch_assoc($group_result);
if (mysqli_num_rows($group_result) == 0) load('404.html');
// Check if user is owner
$owner_result = query("SELECT owner_id FROM `group` WHERE group_id = ?;", 'i', $group_id);
$user_id = $_SESSION['user_id'];
if (mysqli_fetch_array($owner_result)[0] != $user_id && $_SESSION['role'] != "admin") load('groups');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Validate name
    if (empty($_POST['name'])) {
        $errors['name'] = "⚠ Please enter a name";
    } elseif (!preg_match('/^[a-zA-Z0-9äöüßÄÖÜ ]+$/', $_POST['name'])) {
        $errors['name'] = "⚠ Name must contain only letters and numbers";
    } elseif (strlen(trim($_POST['name'])) > 50) {
        $errors['name'] = "⚠ Name must be max 50 characters";
    } else {
        $name = mysqli_real_escape_string($db, trim($_POST['name']));
    }
    // Validate users
    if (empty($_POST['students'])) {
        $errors['students'] = "⚠ Please enter at least 1 student";
    } else {
        $names = $_POST['students'];
        if (!empty($_POST['teachers'])) $names = array_merge($names, $_POST['teachers']);
    }
    // Validate user_ids
    if (empty($errors)) {
        foreach($names as $user_id) {
            if (!intval($user_id)) {
                $errors['students'] = "⚠ Invalid User ID: $user_id";
                break;
            }
            $result = query("SELECT user_id FROM user WHERE verified = true AND user_id = ?;", 'i', intval($user_id));
            if (mysqli_num_rows($result) == 0) {
                $errors['students'] = "⚠ User not found with ID: $user_id";
            }
        }
    }
    // Edit group or display errors
    if (empty($errors)) {
        $user_id = $_SESSION['user_id'];
        query("UPDATE `group` SET name = ? WHERE group_id = ?;", 'si', $name, $group_id);
        query("DELETE FROM group_member WHERE group_id = ?;", 'i', $group_id);
        foreach($names as $user_id) {
            query("INSERT INTO group_member VALUES (?, ?);", 'ii', intval($user_id), $group_id);
        }
        load("group/$group_id");
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
                    placeholder: "Students",
                    width: "calc(50% + 8px)"
                });

                $('.teachers').select2({
                    placeholder: "Other Teacher(s)",
                    width: "calc(50% + 8px)"
                });
                <?php
                // Populate students field
                $result = query("SELECT user.user_id FROM group_member, user WHERE user.user_id = group_member.user_id AND role = 'student' AND group_member.group_id = ?;", 'i', $group_id);
                $student_ids = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $student_ids[] = strval($row['user_id']);
                }
                $student_ids = implode(", ", $student_ids);
                echo "$('.students').val([$student_ids]);";
                echo "$('.students').trigger('change');";
                // Populate teachers field
                $result = query("SELECT user.user_id FROM group_member, user WHERE user.user_id = group_member.user_id AND role != 'student' AND group_member.group_id = ?;", 'i', $group_id);
                $teacher_ids = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $teacher_ids[] = strval($row['user_id']);
                }
                $teacher_ids = implode(", ", $teacher_ids);
                echo "$('.teachers').val([$teacher_ids]);";
                echo "$('.teachers').trigger('change');";
                ?>
            });
        </script>
        <title>Edit Group</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
    </head>
    <body>
        <?php include("includes/nav.php");?>
        <div class='title'>
            <h1>Edit Group</h1>
        </div>
        <div class='box'>
            <form method="POST" novalidate>
                <input type="text" name="name" required pattern="[-a-zA-ZäöüßÄÖÜ ]+" maxlength="50" placeholder="Name" value="<?php echo $group['name'];?>">
                <?php
                if (isset($errors['name'])) {
                    $error = $errors['name'];
                    echo "<p class='error'>$error</p>";
                }
                ?>
                <select name="students[]" required class="students" multiple>
                    <?php
                    $result = mysqli_query($db, "SELECT user_id, first_name, last_name FROM user WHERE role = 'student' AND verified = true;");
                    while ($row = mysqli_fetch_assoc($result)) {
                        $student_id = $row['user_id'];
                        $name = $row['first_name'] . " " . $row['last_name'];
                        echo "<option value=$student_id>$name</option>";
                    }
                    ?>
                </select>
                <?php
                if (isset($errors['students'])) {
                    $error = $errors['students'];
                    echo "<p class='error'>$error</p>";
                }
                ?>
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
                </select>
                <input type="submit" value="Save Changes">
            </form>
        </div>
        <script>
            function validate() {
                // Remove existing errors
                while (document.getElementsByClassName('error')[0]) {
                    document.getElementsByClassName('error')[0].remove();
                }

                let valid = true;
                const name = document.getElementsByTagName("input")['name'];
                const students = document.getElementsByClassName("students")[0];
                const students_input = document.getElementsByClassName('select2-container')[0];
                
                // Validate name
                if (name.validity.valueMissing) {
                    name.insertAdjacentHTML('afterend', '<p class="error">⚠ Please enter a title</p>');
                    valid = false;
                } else if (name.validity.patternMismatch) {
                    name.insertAdjacentHTML('afterend', '<p class="error">⚠ Name must not contain special characters</p>');
                    valid = false;
                } else if (name.validity.rangeOverflow) {
                    name.insertAdjacentHTML('afterend', '<p class="error">⚠ Name must be max 50 characters</p>');
                    valid = false;
                }

                // Validate students
                if (students.validity.valueMissing) {
                    students_input.insertAdjacentHTML('afterend', '<p class="error">⚠ Please enter at least 1 student</p>');
                    valid = false;
                }
                return valid;
            }

            document.getElementsByTagName('form')[0].onsubmit = validate;
        </script>
    </body>
</html>
