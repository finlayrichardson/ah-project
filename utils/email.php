<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;

$path = str_contains(php_uname(), "Windows") ? 'C:\Program Files\PHP8\composer\vendor\autoload.php' : '/usr/share/php/composer/vendor/autoload.php';
require($path);
$gmail = "codecanopy.csprojects@gmail.com";
$password = "4zqE4sNPybtkkXio!";
$clientId = '260768408146-ohot3mq61k3akn30iuh80135j6uocmmv.apps.googleusercontent.com';
$clientSecret = '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';
$refreshToken = '1//09XWo6vS8lSp9CgYIARAAGAkSNwF-L9Ir2UEBHKT8uzMWAsmuYurijmBQOXHozVutWSIUgOeFJEAnJMHtXJ6HwYExCDfqrNenfMo';
$mail = new PHPMailer(TRUE);

try {
    $mail->setFrom($gmail, 'Codecanopy');
    $mail->isHTML(TRUE);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPSecure = 'tls';
    $mail->SMTPAuth = true;
    $mail->AuthType = 'XOAUTH2';
    $mail->Username = $gmail;
    $mail->Password = $password;
    $provider = new Google(
        [
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
        ]
    );
    $mail->setOAuth(
        new OAuth(
            [
                'provider' => $provider,
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
                'refreshToken' => $refreshToken,
                'userName' => $gmail,
            ]
        )
    );
} catch (Exception $e) {
    echo $e->errorMessage();
} catch (\Exception $e) {
    echo $e->getMessage();
}
