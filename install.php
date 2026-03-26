<?php
// Pobieramy dane do połączenia z osobnego pliku "klucza"
require_once 'config.php';

// mysqli_select_db pozwoli nam pracować na konkretnej bazie, gdy już powstanie
$db_selected = mysqli_select_db($conn, $dbname);

// Jeśli bazy jeszcze nie ma, musimy ją stworzyć
if (!$db_selected) {
    $sql_db = "CREATE DATABASE IF NOT EXISTS $dbname DEFAULT CHARACTER SET utf8 COLLATE utf8_polish_ci";
    mysqli_query($conn, $sql_db);
    mysqli_select_db($conn, $dbname);
}

// Tutaj Twoje zapytania SQL tworzące tabele (rola, uzytkownik, kategoria, zestaw, fiszka)
$queries = [
    "CREATE TABLE IF NOT EXISTS rola (id_roli INT PRIMARY KEY AUTO_INCREMENT, nazwa VARCHAR(30) NOT NULL UNIQUE) ENGINE=InnoDB",
    "CREATE TABLE IF NOT EXISTS uzytkownik (id_uzytkownika INT PRIMARY KEY AUTO_INCREMENT, id_roli INT NOT NULL, login VARCHAR(50) UNIQUE NOT NULL, haslo VARCHAR(255) NOT NULL, email VARCHAR(100) NOT NULL, czy_aktywny BOOLEAN DEFAULT TRUE, 
    FOREIGN KEY (id_roli) REFERENCES rola(id_roli))
    ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS kategoria 
    (id_kategorii INT PRIMARY KEY AUTO_INCREMENT, 
    nazwa VARCHAR(50) NOT NULL UNIQUE) 
    ENGINE=InnoDB",
    "CREATE TABLE IF NOT EXISTS zestaw (id_zestawu INT PRIMARY KEY AUTO_INCREMENT, id_kategorii INT NOT NULL, id_uzytkownika INT NOT NULL, tytul VARCHAR(100) NOT NULL, FOREIGN KEY (id_kategorii) REFERENCES kategoria(id_kategorii) ON DELETE CASCADE, FOREIGN KEY (id_uzytkownika) REFERENCES uzytkownik(id_uzytkownika) ON DELETE CASCADE) ENGINE=InnoDB",
    "CREATE TABLE IF NOT EXISTS fiszka (id_fiszki INT PRIMARY KEY AUTO_INCREMENT, id_zestawu INT NOT NULL, pojecie VARCHAR(255) NOT NULL, definicja TEXT NOT NULL, FOREIGN KEY (id_zestawu) REFERENCES zestaw(id_zestawu) ON DELETE CASCADE) ENGINE=InnoDB"
];

foreach ($queries as $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "Sukces: Element bazy danych gotowy!<br>";
    }
}

echo "<h3>Instalacja struktury zakończona pomyślnie.</h3>";
?>