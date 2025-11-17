<?php
// config/koneksi.php
$host = 'localhost';
$db   = 'airline_db';
$user = 'root';
$pass = ''; // set your DB root password
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    die('Database connection failed: ' . $mysqli->connect_error);
}
?>