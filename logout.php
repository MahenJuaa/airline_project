<?php
session_start();
session_unset();
session_destroy();

// Redirect ke halaman login/register
header("Location: index.php");
exit;
?>