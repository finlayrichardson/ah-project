<?php
require('./utils/tools.php');

header('HTTP/1.0 403 Forbidden');
info("error", "Access denied", "You don't have permission to access this page", "index");
?>
