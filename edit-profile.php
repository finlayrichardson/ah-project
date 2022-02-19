<?php
require('./resources/auth.php');

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
    // Validate password
    if (!empty($_POST['password1'])) {
        if (empty($_POST['password2'])) {
            $errors['password2'] = "⚠ Please confirm password";
        } else {
            if ($_POST['password1'] !== $_POST['password2']) {
                $errors['password2'] = "⚠ Passwords must match";
            } else {
                $password = password_hash(trim($_POST['password1']), PASSWORD_BCRYPT);
            }
        }
    }
    // Check if errors should be displayed or user should be updated
    if (empty($errors)) {
        // Update user details
        if (isset($password)) {
            query("UPDATE user SET first_name = ?, last_name = ?, password = ? WHERE user_id = ?;", 'sssi', $first_name, $last_name, $password, $user_id);
        } else {
            query("UPDATE user SET first_name = ?, last_name = ? WHERE user_id = ?;", 'ssi', $first_name, $last_name, $user_id);
        }
        // Change session variables
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;

        load('profile');
    }
}
?>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <title>Edit Profile</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
    </head>
    <body>
        <?php include("includes/nav.php");?>
        <div class='title'>
            <h1>Edit Profile</h1>
        </div>
        <div class='box'>
            <form method="POST" novalidate>
                <input type="text" name="first_name" required pattern="[a-zA-ZäöüßÄÖÜ ]+" maxlength="20" placeholder="First Name" value="<?php echo $_SESSION['first_name'];?>">
                <?php
                if (isset($errors['first_name'])) {
                    $error = $errors['first_name'];
                    echo "<p class='error'>$error</p>";
                }
                ?>
                <input type="text" name="last_name" required pattern="[a-zA-ZäöüßÄÖÜ ]+" maxlength="20" placeholder="Last Name" value="<?php echo $_SESSION['last_name'];?>">
                <?php
                if (isset($errors['last_name'])) {
                    $error = $errors['last_name'];
                    echo "<p class='error'>$error</p>";
                }
                ?>
                <input type="password" name="password1" placeholder="New Password" value="<?php if (isset($_POST['password1'])) echo $_POST['password1'];?>">
                <input type="password" name="password2" placeholder="Confirm New Password" value="<?php if (isset($_POST['password2'])) echo $_POST['password2'];?>">
                <?php
                if (isset($errors['password2'])) {
                    $error = $errors['password2'];
                    echo "<p class='error'>$error</p>";
                }
                ?>
                <input type="submit" value="Save Changes">
            </form>
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

                // Validate password
                if (password1.value != "" && password2.value == "") {
                    password2.insertAdjacentHTML('afterend', '<p class="error">⚠ Please confirm password</p>');
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
