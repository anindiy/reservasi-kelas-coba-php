<?php
// logout.php - Fixed version
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
session_destroy();
header("Location: index_fixed.php");
exit();
?>