<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'LearnIt'; 

// polaczenie z serwerem MySQL
$conn = mysqli_connect($host, $user, $password);

if (!$conn) {
    die("Błąd połączenia: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
?>