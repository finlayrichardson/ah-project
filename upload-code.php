<?php
require('./auth.php');
if ($_SESSION['role'] != "student") load('index.php');
?>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <title>Upload Code</title>
        <link rel="stylesheet" href="styles.css">
        <script class="jsbin" src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
        <script>
            function upload(input) {
                if (input.files && input.files[0]) {
                    $('.file-upload-wrap').hide();
                    $('.file-upload-image').show();
                    $('.file-upload-content').show();
                    $('.file-title').html(input.files[0].name);

                    let file = input.files[0];
                    let formData = new FormData();  
                    formData.append("file", file);
                    fetch(window.location.href, {method: "POST", body: formData});
                } else {
                    removeUpload();
                }
            }

            function removeUpload() {
                $('.file-upload-input').replaceWith($('.file-upload-input').clone());
                $('.file-upload-content').hide();
                $('.file-upload-wrap').show();

                let formData = new FormData();
                        
                formData.append("delete", $('.file-title').text());
                fetch(window.location.href, {method: "POST", body: formData});
            }

            $('.file-upload-wrap').bind('dragover', function () {
                $('.file-upload-wrap').addClass('file-dropping');
            });
            $('.file-upload-wrap').bind('dragleave', function () {
                $('.file-upload-wrap').removeClass('file-dropping');
            });
        </script>
    </head>
    <body>
        <?php include("includes/nav.php");?>
        <div class="file-upload">
        <button class="file-upload-btn" type="button" onclick="$('.file-upload-input').trigger('click')">Upload Code</button>

        <div class="file-upload-wrap">
            <input class="file-upload-input" type='file' onchange="upload(this);" accept="text/*" />
            <div class="drag-text">
            <h3>Drag and drop a file or select upload code</h3>
            </div>
        </div>
        <div class="file-upload-content">
            <img class="file-upload-image" src="./python.png"/>
            <div class="file-title-wrap">
            <button type="button" onclick="removeUpload()" class="remove-file">Remove <span class="file-title">Uploaded File</span></button>
            </div>
        </div>
        </div>
    </body>
</html>
