<?php
require('./auth.php');
if ($_SESSION['role'] == "student") load('index');
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Validate title
    if (empty($_POST['title'])) {
        $errors[] = "Please enter a title";
    } elseif (!preg_match('/^[-a-zA-Z0-9äöüßÄÖÜ ]+$/', $_POST['title'])) {
        $errors[] = "Title must not contain special characters";
    } elseif (strlen(trim($_POST['title'])) > 100) {
        $errors[] = "Title must be max 100 characters";
    } else {
        $title = mysqli_real_escape_string($db, trim($_POST['title']));
    }
    // Validate groups
    if (empty($_POST['groups'])) {
        $errors[] = "Please enter at least 1 group";
    } else {
        $groups = $_POST['groups'];
    }
    // Validate group_ids
    if (empty($errors)) {
        foreach($groups as $group_id) {
            if (!intval($group_id)) {
                $errors[] = "Invalid Group ID: $group_id";
                break;
            }
            $result = query("SELECT group_id FROM `group` WHERE group_id = ?;", 'i', intval($group_id));
            if (mysqli_num_rows($result) == 0) {
                $errors[] = "Group not found with ID: $group_id";
            }
        }
    }
    if (empty($errors)) {
        $user_id = $_SESSION['user_id'];
        foreach($groups as $group_id) {
            if (!teacher_status($user_id, $group_id)) {
                $errors[] = "You don't have permissions to set a task to this group";
                break;
            }
        }
    }
    // Validate due date
    if (empty($_POST['due_date'])) {
        $errors[] = "Please enter a due date";
    } elseif (!preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/', $_POST['due_date'])) {
        $errors[] = "Please enter a valid date";
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
                $('.multi-input').select2({
                    placeholder: "Group(s)",
                    width: "resolve"
                });
                <?php
                if (isset($_POST['groups'])) {
                    $group_ids = implode(", ", $_POST['groups']);
                    echo "$('.multi-input').val([$group_ids]);";
                    echo "$('.multi-input').trigger('change');";
                }
                if (isset($_GET['group_id'])) {
                    $group_id = intval($_GET['group_id']);
                    echo "$('.multi-input').val($group_id);";
                    echo "$('.multi-input').trigger('change');";
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
            <form method="POST">
                <input type="text" name="title" required autofocus pattern="[-a-zA-Z0-9äöüßÄÖÜ ]+" maxlength="100" placeholder="Title" value="<?php if (isset($_POST['title'])) echo $_POST['title'];?>">
                <select name="groups[]" class="multi-input" multiple>
                    <?php
                    $result = ($_SESSION['role'] == "admin") ? mysqli_query($db, "SELECT group_id, name FROM `group`;") : mysqli_query($db, "SELECT group_id, name FROM `group` WHERE owner_id = $user_id OR group_id IN(SELECT group_member.group_id FROM user, group_member WHERE user.user_id = group_member.user_id AND group_member.user_id = $user_id);");
                    while ($row = mysqli_fetch_assoc($result)) {
                        $group_id = $row['group_id'];
                        $name = $row['name'];
                        echo "<option value=$group_id>$name</option>";
                    }
                    ?>
                </select>
                <input type="date" name="due_date" required placeholder="Due Date" value="<?php if (isset($_POST['due_date'])) echo $_POST['due_date'];?>">
                <textarea name="description" placeholder="Description" value="<?php if (isset($_POST['description'])) echo $_POST['description'];?>"></textarea>
                <input type="submit" value="Create Task">
            </form>
        </div>
    </body>
</html>
