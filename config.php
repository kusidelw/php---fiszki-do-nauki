<?php

$host="localhost";
$user="root";
$password="";
$dbname="LearnIt";

$conn = mysqli_connect($host, $user, $password);
if (!$conn) { die("Błąd połączenia z serwerem: " . mysqli_connect_error()); }
mysqli_select_db($conn, $dbname);
mysqli_set_charset($conn, "utf8");
?>