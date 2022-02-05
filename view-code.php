<?php
require('./auth.php');
if ($_SESSION['role'] == "student") load('index');

$task_id = $_GET['task_id'];
$user_id = $_GET['user_id'];
echo "task $task_id";
echo "user $user_id";
?>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <title>View Code</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
    </head>
    <body>
        <div id="code">
            <?php
            // echo "<p>$code</p>"
            ?>
        </div>
        <div id="terminal">
            <?php
            // echo "<iframe frameborder='0' width='500px' height='500px' src='https://replit.com/@richarfc/code?lite=true&outputonly=true#user$user/$task.py'></iframe>"
            ?>
        </div>
    </body>
</html>
