<!DOCTYPE html>
<html lang='en'>
    <head>
        <title><?php echo $title;?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
    </head>
    <body>
        <div class='centre-box' id="info">
            <img src="<?php echo "/resources/$type.svg"?>" alt="<?php echo $type?>">
            <p><?php echo $message;?></p>
            <?php
            if (isset($link)) {
                $link_name = ($link = "index") ? "Home" : ucfirst($link);
                echo "<a href='/$link'>$link_name</a>";
            }
            ?>
        </div>
    </body>
</html>
