<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'C:\Program Files\PHP8\composer\vendor\autoload.php';

$gmail = "snakesoar.csprojects@gmail.com";
$password = "4zqE4sNPybtkkXio";

$mail = new PHPMailer(TRUE);

try {

    $mail->setFrom($gmail, 'Snakesoar');
    $mail->addAddress('richarfc@esms.org.uk', 'Finlay');
    $mail->Subject = 'Force';
    $mail->isHTML(TRUE);
    $mail->Body = '<html>There is a great disturbance in the <strong>Force</strong>.</html>';
    $mail->AltBody = 'There is a great disturbance in the Force.';
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
    $mail->SMTPDebug = 4;

    $mail->send();
} catch (Exception $e) {
    echo $e->errorMessage();
} catch (\Exception $e) {
    echo $e->getMessage();
}
