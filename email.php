<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$path = str_contains(php_uname(), "Windows") ? 'C:\Program Files\PHP8\composer\vendor\autoload.php' : '/usr/share/php/composer/vendor/autoload.php';
require($path);
$gmail = "codecanopy.csprojects@gmail.com";
$password = "4zqE4sNPybtkkXio!";

$mail = new PHPMailer(TRUE);

try {

    $mail->setFrom($gmail, 'Codecanopy');
    $mail->isHTML(TRUE);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';

    /* Username (email address). */
    $mail->Username = $gmail;

    /* Google account password. */
    $mail->Password = $password;


    /* Enable SMTP debug output. */
    // $mail->SMTPDebug = 4;

} catch (Exception $e) {
    echo $e->errorMessage();
} catch (\Exception $e) {
    echo $e->getMessage();
}
