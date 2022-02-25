<!DOCTYPE html>
<html lang="en">
    <body style="background-color: rgba(2, 115, 231, 255)">
        <div
            style="
                display: flex;
                flex-direction: column;
                align-items: center;
                background-color: white;
                border-radius: 20px;
                padding: 30px;
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                box-shadow: 0 1px 6px rgba(0, 0, 0, 0.12),
                    0 1px 20px rgba(0, 0, 0, 0.24);
            "
        >
            <p
                style="
                    font-family: 'Inter', -apple-system, BlinkMacSystemFont,
                        'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell',
                        'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
                    font-size: 20px;
                    text-align: center;
                "
            >
                <?php
                echo ($type == "email") ? "Please click the button below to verify your email" : "Please click the button below to reset your password";
                ?>
            </p>
            <a
                href="
                <?php
                echo ($type == "email") ? "http://$host/verify-email/$token" : "http://$host/reset-password/$token";
                ?>"
                style="
                    font-family: 'Inter', -apple-system, BlinkMacSystemFont,
                        'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell',
                        'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
                    text-decoration: none;
                    color: white;
                    background-color: rgba(2, 115, 231, 255);
                    font-size: 18px;
                    border-radius: 10px;
                    padding: 15px;
                    margin-top: 20px;
                    box-shadow: 0 1px 6px rgba(0, 0, 0, 0.12),
                        0 1px 10px rgba(0, 0, 0, 0.24);
                "
                ><?php
                echo ($type == "email") ? "Verify Email" : "Reset Password";
                ?></a
            >
        </div>
    </body>
</html>
