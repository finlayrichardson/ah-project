<!DOCTYPE html>
<html lang='en'>
    <head>
        <title><?php echo $title;?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
    </head>
    <body>
        <div class='user-form'>
            <img src="/resources/info.svg">
            <p><?php echo $info;?></p>
            <?php
            if (isset($link)) {
                $link_name = ucfirst($link);
                echo "<a href='/$link'>$link_name</a>";
            }
            ?>
        </div>
    </body>
</html>
