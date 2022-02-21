<?php
require('./utils/auth.php');
if ($_SESSION['role'] == "student") load('index');
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Validate title
    if (empty($_POST['title'])) {
        $errors['title'] = "⚠ Please enter a title";
    } elseif (!preg_match('/^[-a-zA-Z0-9äöüßÄÖÜ ]+$/', $_POST['title'])) {
        $errors['title'] = "⚠ Title must not contain special characters";
    } elseif (strlen(trim($_POST['title'])) > 100) {
        $errors['title'] = "⚠ Title must be max 100 characters";
    } else {
        $title = mysqli_real_escape_string($db, trim($_POST['title']));
    }
    // Validate groups
    if (empty($_POST['groups'])) {
        $errors['groups'] = "⚠ Please enter at least 1 group";
    } else {
        $groups = $_POST['groups'];
    }
    // Validate group_ids
    if (empty($errors)) {
        foreach($groups as $group_id) {
            if (!intval($group_id)) {
                $errors['groups'] = "⚠ Invalid Group ID: $group_id";
                break;
            }
            $result = query("SELECT group_id FROM `group` WHERE group_id = ?;", 'i', intval($group_id));
            if (mysqli_num_rows($result) == 0) {
                $errors['groups'] = "⚠ Group not found with ID: $group_id";
            }
        }
    }
    if (empty($errors)) {
        $user_id = $_SESSION['user_id'];
        foreach($groups as $group_id) {
            if (!teacher_status($user_id, $group_id)) {
                echo "<p>You don't have permission to set a task to this group";
                exit();
            }
        }
    }
    // Validate due date
    if (empty($_POST['due_date'])) {
        $errors['due_date'] = "⚠ Please enter a due date";
    } elseif (!preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/', $_POST['due_date'])) {
        $errors['due_date'] = "⚠ Please enter a valid date";
    } else {
        $due_date = mysqli_real_escape_string($db, trim($_POST['due_date']));
    }
    // Validate description
    if (isset($_POST['description'])) {
        $description = mysqli_real_escape_string($db, trim($_POST['description']));
    } else {
        $description = "";
    }
    // Create task or display errors
    if (empty($errors)) {
        $user_id = $_SESSION['user_id'];
        query("INSERT INTO task (owner_id, title, description, due_date, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW());", 'isss', $user_id, $title, $description, $due_date);
        $result = query("SELECT task_id FROM task WHERE title = ? ORDER BY created_at DESC;", 's', $title);
        $task_id = mysqli_fetch_row($result)[0];
        foreach($groups as $group_id) {
            query("INSERT INTO task_recipient VALUES (?, ?);", 'ii', $task_id, intval($group_id));
        }
        mkdir("./code/$task_id");
        load("task/$task_id");
    }
}
?>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <script src="/resources/jquery.min.js"></script>
        <script src="/resources/select2.min.js"></script>
        <link href="/resources/select2.min.css" rel="stylesheet">
        <script>
            $(document).ready(function() {
                $('.groups').select2({
                    placeholder: "Group(s)",
                    width: "calc(50% + 8px)",
                });
                <?php
                if (isset($_POST['groups'])) {
                    $group_ids = implode(", ", $_POST['groups']);
                    echo "$('.groups').val([$group_ids]);";
                    echo "$('.groups').trigger('change');";
                }
                if (isset($_GET['group_id'])) {
                    $group_id = intval($_GET['group_id']);
                    echo "$('.groups').val($group_id);";
                    echo "$('.groups').trigger('change');";
                }
                ?>
            });
        </script>
        <title>Create Task</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
        <script type="text/javascript" src="https://livejs.com/live.js"></script>
    </head>
    <body>
        <?php include("includes/nav.php");?>
        <div class='title'>
            <h1>Create Task</h1>
        </div>
        <div class='box'>
            <form method="POST" novalidate>
                <input type="text" name="title" required autofocus pattern="[-a-zA-Z0-9äöüßÄÖÜ ]+" maxlength="100" placeholder="Title" value="<?php if (isset($_POST['title'])) echo $_POST['title'];?>">
                <?php
                if (isset($errors['title'])) {
                    $error = $errors['title'];
                    echo "<p class='error'>$error</p>";
                }
                ?>
                <select name="groups[]" required class='groups' multiple>
                    <?php
                    $result = ($_SESSION['role'] == "admin") ? mysqli_query($db, "SELECT group_id, name FROM `group`;") : mysqli_query($db, "SELECT group_id, name FROM `group` WHERE owner_id = $user_id OR group_id IN(SELECT group_member.group_id FROM user, group_member WHERE user.user_id = group_member.user_id AND group_member.user_id = $user_id);");
                    while ($row = mysqli_fetch_assoc($result)) {
                        $group_id = $row['group_id'];
                        $name = $row['name'];
                        echo "<option value=$group_id>$name</option>";
                    }
                    ?>
                </select>
                <?php
                if (isset($errors['groups'])) {
                    $error = $errors['groups'];
                    echo "<p class='error'>$error</p>";
                }
                ?>
                <input type="date" name="due_date" required placeholder="Due Date" value="<?php if (isset($_POST['due_date'])) echo $_POST['due_date'];?>">
                <?php
                if (isset($errors['due_date'])) {
                    $error = $errors['due_date'];
                    echo "<p class='error'>$error</p>";
                }
                ?>
                <textarea name="description" placeholder="Description" value="<?php if (isset($_POST['description'])) echo $_POST['description'];?>"></textarea>
                <input type="submit" value="Create Task">
            </form>
        </div>
        <script>
            function validate() {
                // Remove existing errors
                while (document.getElementsByClassName('error')[0]) {
                    document.getElementsByClassName('error')[0].remove();
                }

                let valid = true;
                const title = document.getElementsByTagName("input")['title'];
                const groups = document.getElementsByClassName("groups")[0];
                const groups_input = document.getElementsByClassName('select2-container')[0];
                const due_date = document.getElementsByTagName("input")['due_date'];
                
                // Validate title
                if (title.validity.valueMissing) {
                    title.insertAdjacentHTML('afterend', '<p class="error">⚠ Please enter a title</p>');
                    valid = false;
                } else if (title.validity.patternMismatch) {
                    title.insertAdjacentHTML('afterend', '<p class="error">⚠ Title must not contain special characters</p>');
                    valid = false;
                } else if (title.validity.rangeOverflow) {
                    title.insertAdjacentHTML('afterend', '<p class="error">⚠ Title must be max 20 characters</p>');
                    valid = false;
                }

                // Validate groups
                if (groups.validity.valueMissing) {
                    groups_input.insertAdjacentHTML('afterend', '<p class="error">⚠ Please enter at least 1 group</p>');
                    valid = false;
                }

                // Validate due date
                if (due_date.validity.valueMissing) {
                    due_date.insertAdjacentHTML('afterend', '<p class="error">⚠ Please enter a due date</p>');
                    valid = false;
                }
                return valid;
            }

            document.getElementsByTagName('form')[0].onsubmit = validate;
        </script>
    </body>
</html>
