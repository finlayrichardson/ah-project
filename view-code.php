<?php
require('./utils/auth.php');
if ($_SESSION['role'] == "student") load('index');

// Validate Task ID
if (empty($_GET['task_id'])) {
    info("error", "Task", "No task specified", "tasks");
} else {
    $task_id = intval(trim($_GET['task_id']));
}
// Check if task exists
$task_result = query("SELECT * FROM task WHERE task_id = ?;", 'i', $task_id);
$task = mysqli_fetch_assoc($task_result);
if (mysqli_num_rows($task_result) == 0) info("error", "View Code", "Task not found", "tasks");
// Check user's permissions
$user_id = $_SESSION['user_id'];
$result = query("SELECT user.user_id FROM user, `group`, group_member, task_recipient WHERE user.user_id = group_member.user_id AND group_member.group_id = group.group_id AND group.group_id = task_recipient.group_id AND user.user_id = ? AND task_recipient.task_id = ?;", 'ii', $user_id, $task_id);
if ($task['owner_id'] != $user_id && $_SESSION['role'] != "admin" && mysqli_num_rows($result) == 0) {
    info("error", "View Code", "You do not have permissions to view submitted code for this task");
}

// Get students who have been set this task
$student_result = mysqli_query($db, "SELECT DISTINCT(user.user_id), first_name, last_name FROM user, `group`, group_member, task_recipient WHERE task_recipient.group_id = group.group_id AND group_member.user_id = user.user_id AND group_member.group_id = group.group_id AND task_id = $task_id AND role = 'student';");
$student_ids = array();
$names = array();
while ($row = mysqli_fetch_assoc($student_result)) {
    $student_ids[] = $row['user_id'];
    $names[] = $row['first_name'] . " " . $row['last_name'];
}

// Validate Student ID
if (empty($_GET['student_id'])) {
    $student_id = $student_ids[0];
    $current_index = 0;
} else {
    $student_id = intval(trim($_GET['student_id']));
    // Check student has been asssigned this task
    if (!in_array($student_id, $student_ids)) {
        info("error", "View Code", "Student has not been assigned this task");
    }
    $current_index = array_search($student_id, $student_ids);
}

shell_exec("./utils/script.sh $task_id $student_id");

// $count = 0;
function line_number() {
    static $count = 1;
    $count++;
    $count = str_pad(strval($count), 3, ' ', STR_PAD_LEFT);
    return "\n$count  ";
}

if (!empty(glob("./code/$task_id/$student_id/*"))) {
    $code = htmlspecialchars("  1  " . preg_replace_callback("(\r\n|\r|\n)", "line_number", rtrim(file_get_contents(glob("./code/$task_id/$student_id/*")[0]))));
}
?>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <link rel="stylesheet" href="/resources/highlight.min.css">
        <script src="/resources/highlight.min.js"></script>
        <script>hljs.highlightAll();</script>
        <title>View Code</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
        <script type="text/javascript" src="https://livejs.com/live.js"></script>
    </head>
    <body>
        <?php include("includes/nav.php");?>
        <div class="submissions-options">
            <?php
            if (count($student_ids) != 1) {
                if ($current_index - 1 == -1) {
                    $prev_id = end($student_ids);
                    $prev_name = end($names);
                } else {
                    $prev_id = $student_ids[$current_index - 1];
                    $prev_name = $names[$current_index - 1];
                }
                echo "<a href='/view-code/$task_id/$prev_id'>$prev_name</a>";
            }
            ?>
            <select class='dropdown' onchange='window.location="/view-code/<?php echo $task_id; ?>/"+this.value;'>
                <?php
                foreach (range(0, (count($student_ids)) - 1) as $index) {
                    $id = $student_ids[$index];
                    $name = $names[$index];
                    echo "<option value=$id>$name</option>";
                }
                ?>
            </select>
            <?php
            echo "
            <script>
                document.getElementsByClassName('dropdown')[0].value = '$student_id';
            </script>";
            if (count($student_ids) != 1) {
                if ($current_index + 1 == count($student_ids)) {
                    $next_id = $student_ids[0];
                    $next_name = $names[0];
                } else {
                    $next_id = $student_ids[$current_index + 1];
                    $next_name = $names[$current_index + 1];
                }
                echo "<a href='/view-code/$task_id/$next_id'>$next_name</a>";
            }
            ?>
        </div>
        <div class='code-content'>
            <?php
            if (isset($code)) {
                echo "
                <div id='code'>
                    <pre><code class='language-python'>$code</code></pre>
                </div>
                <div id='terminal'>
                    <iframe frameborder='0' width='500px' height='500px' src='https://replit.com/@richarfc/grade-calc?lite=true&outputonly=true#grade.py'></iframe>
                </div>
                ";
            } else {
                echo "
                <div class='no-content'>
                    <p>No code submitted</p>
                </div>
                ";
            }
            ?>
        </div>
    </body>
</html>
