<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'LearnIt'; 

$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Błąd połączenia: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
?>