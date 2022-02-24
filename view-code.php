<?php
require('./utils/auth.php');
if ($_SESSION['role'] == "student") load('index');

// validate these
$task_id = intval($_GET['task_id']);
// thing for default page if no user id
$user_id = intval($_GET['user_id']);

$student_result = mysqli_query($db, "SELECT DISTINCT(user.user_id), first_name, last_name FROM user, `group`, group_member, task_recipient WHERE task_recipient.group_id = group.group_id AND group_member.user_id = user.user_id AND group_member.group_id = group.group_id AND task_id = $task_id AND role = 'student';");
$user_ids = array();
$names = array();
while ($row = mysqli_fetch_assoc($student_result)) {
    $user_ids[] = $row['user_id'];
    $names[] = $row['first_name'] . " " . $row['last_name'];
}

$current_index = array_search($user_id, $user_ids);

$count = 0;
function line_number($matches) {
    static $count = 1;
    $count++;
    $count = str_pad(strval($count), 3, ' ', STR_PAD_LEFT);
    return "\n$count  ";
}

if (!empty(glob("./code/$task_id/$user_id/*"))) {
    $code = "  1  " . preg_replace_callback("(\r\n|\r|\n)", "line_number", rtrim(file_get_contents(glob("./code/$task_id/$user_id/*")[0])));
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
            if (count($user_ids) != 1) {
                if ($current_index - 1 == -1) {
                    $prev_id = end($user_ids);
                    $prev_name = end($names);
                } else {
                    $prev_id = $user_ids[$current_index - 1];
                    $prev_name = $names[$current_index - 1];
                }
                echo "<a href='/view-code/$task_id/$prev_id'>$prev_name</a>";
            }
            ?>
            <select class='dropdown' onchange='window.location="/view-code/<?php echo $task_id; ?>/"+this.value;'>
                <?php
                foreach (range(0, (count($user_ids)) - 1) as $index) {
                    $student_id = $user_ids[$index];
                    $name = $names[$index];
                    echo "<option value=$student_id>$name</option>";
                }
                ?>
            </select>
            <?php
            echo "
            <script>
                document.getElementsByClassName('dropdown')[0].value = '$user_id';
            </script>";
            if (count($user_ids) != 1) {
                if ($current_index + 1 == count($user_ids)) {
                    $next_id = $user_ids[0];
                    $next_name = $names[0];
                } else {
                    $next_id = $user_ids[$current_index + 1];
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
                <div id='no-code'>
                    <p>No code submitted</p>
                </div>
                ";
            }
            ?>
        </div>
    </body>
</html>
