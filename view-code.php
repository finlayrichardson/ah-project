<!DOCTYPE html>
<html>

<head>
    <style>
        #code {
            float: left;
        }

        #terminal {
            float: left;
        }
    </style>
    <?php
    $task = $_GET['task_id'];
    $user = $_GET['user_id'];
    $code = str_replace("\n", "<br>", file_get_contents("../code/user$user/$task.py"));
    ?>
</head>

<body>
    <div id="code" width="750px" height="1000px">
        <?php
        echo "<p>$code</p>"
        ?>
    </div>

    <div id="terminal" width="750px" height="1000px">
        <?php
        echo "<iframe frameborder='0' width='500px' height='500px' src='https://replit.com/@richarfc/code?lite=true&outputonly=true#user$user/$task.py'></iframe>"
        ?>
    </div>

</body>

</html>