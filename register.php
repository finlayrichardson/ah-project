<?php
require('./auth.php');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $errors = array();
    // Validate First Name
    if (empty($_POST['first_name'])) {
        $errors['first_name'] = "⚠ Please enter a first name";
    } elseif (!preg_match('/^[a-zA-ZäöüßÄÖÜ ]+$/', $_POST['first_name'])) {
        $errors['first_name'] = "⚠ First name must contain only letters";
    } elseif (strlen(trim($_POST['first_name'])) > 20) {
        $errors['first_name'] = "⚠ First name must be max 20 characters";
    } else {
        $first_name = mysqli_real_escape_string($db, trim($_POST['first_name']));
    }
    // Validate Last Name
    if (empty($_POST['last_name'])) {
        $errors['last_name'] = "⚠ Please enter a last name";
    } elseif (!preg_match('/^[a-zA-ZäöüßÄÖÜ ]+$/', $_POST['last_name'])) {
        $errors['last_name'] = "⚠ Last name must contain only letters";
    } elseif (strlen(trim($_POST['last_name'])) > 20) {
        $errors['last_name'] = "⚠ Last name must be max 20 characters";
    } else {
        $last_name = mysqli_real_escape_string($db, trim($_POST['last_name']));
    }
    // Validate Email
    if (empty($_POST['email'])) {
        $errors['email'] = "⚠ Please enter an email";
    } elseif (!preg_match('/^.+?@esms\.org\.uk *?$/', $_POST['email'])) {
        $errors['email'] = "⚠ Please enter your school email";
    } elseif (strlen(trim($_POST['email'])) > 30) {
        $errors['email'] = "⚠ Email must be max 30 characters";
    } else {
        $email = mysqli_real_escape_string($db, trim($_POST['email']));
    }
    // Validate password
    if (empty($_POST['password1'])) {
        $errors['password1'] = "⚠ Please enter a password";
    } elseif (empty($_POST['password2'])) {
        $errors['password2'] = "⚠ Please confirm password";
    } elseif ($_POST['password1'] !== $_POST['password2']) {
        $errors['password2'] = "⚠ Passwords must match";
    } else {
        $password = password_hash(mysqli_real_escape_string($db, trim($_POST['password1'])), PASSWORD_BCRYPT);
    }
    // Check email isn't already registered
    if (empty($errors)) {
        $result = query("SELECT user_id FROM user WHERE email = ?;", 's', $email);
        if (mysqli_num_rows($result) != 0) {
            $errors['email'] = "⚠ Email already registered";
        }
    }
    // Check if errors should be displayed or user should be inserted
    if (empty($errors)) {
        // Insert user into database
        query("INSERT INTO user (email, password, first_name, last_name, role) VALUES (?, ?, ?, ?, 'student');", 'ssss', $email, $password, $first_name, $last_name);

        // Setup session and go to verify-email
        session_name("id");
        session_start();
        $result = query("SELECT user_id FROM user WHERE email = ?;", 's', $email);
        $user_id = mysqli_fetch_row($result)[0];
        $_SESSION['user_id'] = $user_id;
        $_SESSION['email'] = $email;
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;

        load('verify-email');
    }
}
?>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <title>Register</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
    </head>
    <body>
        <div class="user-form">
            <h1>Register</h1>
            <hr>
            <form method="POST" novalidate>
                <input type="text" name="first_name" required autofocus pattern="[a-zA-ZäöüßÄÖÜ ]+" maxlength="20" placeholder="First Name" value="<?php if (isset($_POST['first_name'])) echo $_POST['first_name'];?>">
                <?php
                if (isset($errors['first_name'])) {
                    $error = $errors['first_name'];
                    echo "<p class='error'>$error</p>";
                }
                ?>
                <input type="text" name="last_name" required pattern="[a-zA-ZäöüßÄÖÜ ]+" maxlength="20" placeholder="Last Name" value="<?php if (isset($_POST['last_name'])) echo $_POST['last_name'];?>">
                <?php
                if (isset($errors['last_name'])) {
                    $error = $errors['last_name'];
                    echo "<p class='error'>$error</p>";
                }
                ?>
                <input type="email" name="email" required pattern="^.+?@esms\.org\.uk *?$" maxlength="30" placeholder="Email" value="<?php if (isset($_POST['email'])) echo $_POST['email'];?>">
                <?php
                if (isset($errors['email'])) {
                    $error = $errors['email'];
                    echo "<p class='error'>$error</p>";
                }
                ?>
                <input type="password" name="password1" required placeholder="Password" value="<?php if (isset($_POST['password1'])) echo $_POST['password1'];?>">
                <?php
                if (isset($errors['password1'])) {
                    $error = $errors['password1'];
                    echo "<p class='error'>$error</p>";
                }
                ?>
                <input type="password" name="password2" required placeholder="Confirm Password" value="<?php if (isset($_POST['password2'])) echo $_POST['password2'];?>"><br>
                <?php
                if (isset($errors['password2'])) {
                    $error = $errors['password2'];
                    echo "<p class='error'>$error</p>";
                }
                ?>
                <input type="submit" value="Register">
            </form>
            <hr>
            <span>Have an account? <a href="login" class="button">Login</a></span>
        </div>
        <script>
            function validate() {
                // Remove existing errors
                while (document.getElementsByClassName('error')[0]) {
                    document.getElementsByClassName('error')[0].remove();
                }

                let valid = true;
                const first_name = document.getElementsByTagName("input")['first_name'];
                const last_name = document.getElementsByTagName("input")['last_name'];
                const email = document.getElementsByTagName("input")['email'];
                const password1 = document.getElementsByTagName("input")['password1'];
                const password2 = document.getElementsByTagName("input")['password2'];
                
                // Validate first name
                if (first_name.validity.valueMissing) {
                    first_name.insertAdjacentHTML('afterend', '<p class="error">⚠ Please enter a first name</p>');
                    valid = false;
                } else if (first_name.validity.patternMismatch) {
                    first_name.insertAdjacentHTML('afterend', '<p class="error">⚠ First name must contain only letters</p>');
                    valid = false;
                } else if (first_name.validity.rangeOverflow) {
                    first_name.insertAdjacentHTML('afterend', '<p class="error">⚠ First name must be max 20 characters</p>');
                    valid = false;
                }

                // Validate last name
                if (last_name.validity.valueMissing) {
                    last_name.insertAdjacentHTML('afterend', '<p class="error">⚠ Please enter a last name</p>');
                    valid = false;
                } else if (last_name.validity.patternMismatch) {
                    last_name.insertAdjacentHTML('afterend', '<p class="error">⚠ Last name must contain only letters</p>');
                    valid = false;
                } else if (last_name.validity.rangeOverflow) {
                    last_name.insertAdjacentHTML('afterend', '<p class="error">⚠ Last name must be max 20 characters</p>');
                    valid = false;
                }

                // Validate email
                if (email.validity.valueMissing) {
                    email.insertAdjacentHTML('afterend', '<p class="error">⚠ Please enter an email</p>');
                    valid = false;
                } else if (email.validity.patternMismatch) {
                    email.insertAdjacentHTML('afterend', '<p class="error">⚠ Please enter your school email</p>');
                    valid = false;
                } else if (email.validity.rangeOverflow) {
                    email.insertAdjacentHTML('afterend', '<p class="error">⚠ Email must be max 30 characters</p>');
                    valid = false;
                }

                // Validate password
                if (password1.validity.valueMissing) {
                    password1.insertAdjacentHTML('afterend', '<p class="error">⚠ Please enter a password</p>');
                    valid = false;
                } else if (password1.value !== password2.value) {
                    password2.insertAdjacentHTML('afterend', '<p class="error">⚠ Passwords must match</p>');
                    valid = false;
                }
                return valid;
            }

            document.getElementsByTagName('form')[0].onsubmit = validate;
        </script>
    </body>
</html>
