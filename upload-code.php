<?php
require('./utils/auth.php');
if ($_SESSION['role'] != "student") load('index');

$user_id = $_SESSION['user_id'];
// Validate ID
if (empty($_GET['id'])) {
    info("error", "Upload Code", "No task specified", "tasks");
} else {
    $task_id = intval(trim($_GET['id']));
}
// Check user can submit/delete code for this task
$result = query("SELECT task_id FROM task WHERE task_id IN(SELECT task_recipient.task_id FROM user, `group`, group_member, task_recipient WHERE user.user_id = group_member.user_id AND group_member.group_id = group.group_id AND group.group_id = task_recipient.group_id AND user.user_id = ?) AND task_id = ?;", 'ii', $user_id, $task_id);
if (mysqli_num_rows($result) != 1) {
    load('tasks');
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $errors = array();
    if (isset($_FILES['file'])) {
        if (is_dir("./code/$task_id/$user_id")) {
            $errors[] = "File already uploaded";
        } else {
            mkdir("./code/$task_id/$user_id");
            $upload_file = "./code/$task_id/$user_id/" . basename( $_FILES['file']['name']);
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $upload_file)) $errors[] = "Cannot upload file";
        }
    }

    if (isset($_POST['delete'])) {
        if (is_dir("./code/$task_id/$user_id")) {
            array_map('unlink', glob("./code/$task_id/$user_id/*.*"));
            rmdir("./code/$task_id/$user_id");
        } else {
            $errors[] = "File doesn't exist";
        }
    }

    if (!empty($errors)) {
        // Display errors
        echo "<h1>Error!</h1>
        <p>The following error(s) occured:<br>";
        foreach ($errors as $error) {
            echo "- $error<br>";
        }
        echo "<p>Please try again.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <title>Upload Code</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
        <script src="/resources/jquery.min.js"></script>
        <script>
            function upload(input) {
                if (input.files && input.files[0]) {
                    $('.file-upload-btn').hide();
                    $('.file-upload-wrap').hide();
                    if (input.files[0].name.endsWith('.py')) {
                        $('.file-upload-image').show();
                    } else {
                        $('.file-upload-image').hide();
                    }
                    $('.file-upload-content').show();
                    $('.file-title').html(input.files[0].name);

                    let file = input.files[0];
                    let formData = new FormData();  
                    formData.append("file", file);
                    fetch(window.location.href, {method: "POST", body: formData})
                    .then(function (response) {
                        return response.text();
                    });
                } else {
                    removeUpload();
                }
            }

            function removeUpload() {
                $('.file-upload-input').replaceWith($('.file-upload-input').clone());
                $('.file-upload-content').hide();
                $('.file-upload-wrap').show();
                $('.file-upload-btn').show();
                $(".file-upload-input").val(null);

                let formData = new FormData();
                        
                formData.append("delete", null);
                fetch(window.location.href, {method: "POST", body: formData});
            }

            $('.file-upload-wrap').bind('dragover', function () {
                $('.file-upload-wrap').addClass('file-dropping');
            });
            $('.file-upload-wrap').bind('dragleave', function () {
                $('.file-upload-wrap').removeClass('file-dropping');
            });
            <?php
            if (is_dir("./code/$task_id/$user_id")) {
                $file_name = implode('', array_diff(scandir("./code/$task_id/$user_id"), array('..', '.')));
                echo "
                     $(document).ready(function() {
                         $('.file-upload-btn').hide();
                         $('.file-upload-wrap').hide();
                         $('.file-upload-image').show();
                         $('.file-upload-content').show();
                         $('.file-title').html('$file_name');
                     });";
            } else {
                echo "
                $(document).ready(function() {
                    $('.file-upload-content').hide();
                });";
            }
            ?>
        </script>
    </head>
    <body>
        <?php include("includes/nav.php");?>
        <div class="file-upload">
            <button class="file-upload-btn" type="button" onclick="$('.file-upload-input').trigger('click')">Upload Code</button>
            <div class="file-upload-wrap">
                <input class="file-upload-input" type='file' onchange="upload(this);"/>
                <div class="drag-text">
                    <h3>Drag and drop a file or select upload code</h3>
                </div>
            </div>
            <div class="file-upload-content">
                <div class="file-name">
                    <img class="file-upload-image" src="/resources/python.svg"/>
                    <span class="file-title">Uploaded File</span>
                </div>
                <button type="button" onclick="removeUpload()" class="remove-file">Remove File</button>
            </div>
        </div>
    </body>
</html>
