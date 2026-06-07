<?php
require_once 'config.php';

echo "<style>body { font-family: sans-serif; line-height: 1.6; background: #f4f0ff; color: #333; padding: 20px; }</style>";
echo "<h2>🚀 Instalacja systemu LearnIt</h2>";

// jesli baza istnieje, to tworzymy nową i starą usuwamy
mysqli_query($conn, "DROP DATABASE IF EXISTS $dbname");
$sql_db = "CREATE DATABASE $dbname DEFAULT CHARACTER SET utf8 COLLATE utf8_polish_ci";

if (mysqli_query($conn, $sql_db)) {
    echo "✅ Baza danych <b>$dbname</b> została utworzona od zera.<br>";
    mysqli_select_db($conn, $dbname);
}

// tabele, zgodne z digramem 
$queries = [
    "CREATE TABLE rola (
        id_roli INT PRIMARY KEY AUTO_INCREMENT,
        nazwa VARCHAR(30) NOT NULL UNIQUE
    ) ENGINE=InnoDB",

    "CREATE TABLE uzytkownik (
        id_uzytkownika INT PRIMARY KEY AUTO_INCREMENT,
        id_roli INT NOT NULL,
        login VARCHAR(50) UNIQUE NOT NULL,
        haslo VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL,
        telefon VARCHAR(20),
        imie VARCHAR(50),
        nazwisko VARCHAR(50),
        data_urodzenia DATE,
        czy_aktywny BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (id_roli) REFERENCES rola(id_roli)
    ) ENGINE=InnoDB",

    "CREATE TABLE kategoria (
        id_kategorii INT PRIMARY KEY AUTO_INCREMENT,
        nazwa VARCHAR(50) NOT NULL UNIQUE
    ) ENGINE=InnoDB",

    "CREATE TABLE zestaw (
        id_zestawu INT PRIMARY KEY AUTO_INCREMENT,
        id_kategorii INT NOT NULL,
        id_uzytkownika INT NOT NULL,
        tytul VARCHAR(100) NOT NULL,
        data_utworzenia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        opis TEXT,
        liczba_fiszek INT DEFAULT 0,
        FOREIGN KEY (id_kategorii) REFERENCES kategoria(id_kategorii) ON DELETE CASCADE,
        FOREIGN KEY (id_uzytkownika) REFERENCES uzytkownik(id_uzytkownika) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "CREATE TABLE fiszka (
        id_fiszki INT PRIMARY KEY AUTO_INCREMENT,
        id_zestawu INT NOT NULL,
        pojecie VARCHAR(255) NOT NULL,
        definicja TEXT NOT NULL,
        FOREIGN KEY (id_zestawu) REFERENCES zestaw(id_zestawu) ON DELETE CASCADE
    ) ENGINE=InnoDB"
];

foreach ($queries as $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "✅ Struktura tabeli utworzona poprawnie.<br>";
    } else {
        die("❌ Błąd przy tworzeniu tabeli: " . mysqli_error($conn));
    }
}

echo "<h3>📦 Wypełnianie danymi startowymi...</h3>";

// 1. Dodawanie ról
mysqli_query($conn, "INSERT INTO rola (id_roli, nazwa) VALUES (1, 'admin'), (2, 'user'), (3, 'guest')");
echo "• Dodano role systemowe.<br>";

// 2. Dodawanie języków (kategorii)
$jezyki = [
    'Angielski', 'Hiszpański', 'Niemiecki', 'Francuski', 'Włoski', 'Chiński', 'Japoński', 
    'Rosyjski', 'Portugalski', 'Niderlandzki', 'Szwedzki', 'Norweski', 'Duński', 
    'Fiński', 'Czeski', 'Słowacki', 'Polski', 'Ukraiński', 'Arabski', 'Turecki', 
    'Koreański', 'Węgierski', 'Grecki', 'Hebrajski', 'Indonezyjski', 'Wietnamski', 'Inna'
];

foreach ($jezyki as $j) {
    mysqli_query($conn, "INSERT INTO kategoria (nazwa) VALUES ('$j')");
}
echo "• Dodano " . count($jezyki) . " kategorii językowych.<br>";

// 3. Dodawanie użytkowników
$hashed_admin = password_hash('admin123', PASSWORD_DEFAULT);
$hashed_user = password_hash('user123', PASSWORD_DEFAULT);

mysqli_query($conn, "INSERT INTO uzytkownik (id_roli, login, haslo, email, imie, nazwisko, czy_aktywny) VALUES 
    (1, 'admin', '$hashed_admin', 'admin@learnit.pl', 'Weronika', 'Kusideł', 1),
    (2, 'janek', '$hashed_user', 'janek@learnit.pl', 'Jan', 'Kowalski', 1),
    (2, 'anna', '$hashed_user', 'anna@learnit.pl', 'Anna', 'Nowak', 1)
");
echo "• Utworzono konta testowe (Admin oraz 2 Zwykłych Użytkowników).<br>";

// 4. Dodawanie przykładowych zestawów fiszek
// Janek (id=2) tworzy Angielski (id=1)
// Anna (id=3) tworzy Niemiecki (id=3)
mysqli_query($conn, "INSERT INTO zestaw (id_kategorii, id_uzytkownika, tytul, opis, liczba_fiszek) VALUES 
    (1, 2, 'Angielski - Podstawowe Kolory', 'Zestaw do nauki kolorów dla początkujących.', 4),
    (3, 3, 'Niemiecki - Czasowniki nieregularne', 'Najważniejsze czasowniki w języku niemieckim.', 3)
");
echo "• Dodano przykładowe zestawy fiszek.<br>";

// 5. Dodawanie fiszek do zestawów
// Fiszki dla zestawu Janka (id_zestawu = 1)
mysqli_query($conn, "INSERT INTO fiszka (id_zestawu, pojecie, definicja) VALUES 
    (1, 'Red', 'Czerwony'),
    (1, 'Blue', 'Niebieski'),
    (1, 'Green', 'Zielony'),
    (1, 'Yellow', 'Żółty')
");

// Fiszki dla zestawu Anny (id_zestawu = 2)
mysqli_query($conn, "INSERT INTO fiszka (id_zestawu, pojecie, definicja) VALUES 
    (2, 'Machen', 'Robić'),
    (2, 'Gehen', 'Iść'),
    (2, 'Essen', 'Jeść')
");
echo "• Uzupełniono zestawy słówkami.<br>";

echo "<br><b style='color:purple;'>🎉 INSTALACJA ZAKOŃCZONA! Twoja baza LearnIt jest gotowa do pracy.</b>";
?>