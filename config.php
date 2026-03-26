<?php

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'flashlearn_db';


$conn = mysqli_connect($host, $user, $password);

if (!$conn) {
    die("Błąd połączenia: " . mysqli_connect_error());
}
?>