<?php
// Fragment wygenerowany przy użyciu narzędzia sztucznej inteligencji: Gemini, gemini.google.com [data dostępu: 12.06.2026]

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$config_file = 'config.php';
$step = isset($_GET['step']) ? intval($_GET['step']) : 0;

// --- TARCZA OBRONNA: BLOKADA PO INSTALACJI ---
if (file_exists('install.lock')) {
    die("<div style='font-family:sans-serif; text-align:center; padding:40px; background:#f4f0ff;'>
         <h2 style='color:#d9534f;'>System jest już zainstalowany!</h2>
         <p>Ze względów bezpieczeństwa instalator został zablokowany (znaleziono plik install.lock).</p>
         <br><a href='index.php' style='padding:10px 20px; background:#967bb6; color:white; text-decoration:none; border-radius:5px;'>Wróć do aplikacji</a>
         </div>");
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Instalator LearnIt</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; background: #f4f0ff; color: #333; padding: 40px; }
        .install-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 700px; margin: auto; }
        h2 { color: #967bb6; margin-top: 0; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        label { font-weight: bold; display: block; margin-top: 15px; }
        input { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .btn-action { display: inline-block; background: #967bb6; color: white; border: none; padding: 12px 20px; font-size: 1.1em; border-radius: 8px; cursor: pointer; text-decoration: none; margin-top: 20px; font-weight: bold; }
        .btn-action:hover { background: #7a6396; }
        .success { color: #28a745; font-weight: bold; background: #e8f5e9; padding: 10px; border-radius: 5px; }
        .error { color: #dc3545; font-weight: bold; background: #ffebee; padding: 10px; border-radius: 5px; }
        code { background: #eee; padding: 2px 5px; border-radius: 4px; color: #d63384; }
    </style>
</head>
<body>

<div class="install-card">
    <h2>🚀 Instalator systemu LearnIt :: Krok <?php echo $step; ?></h2>

    <?php
    switch ($step) {
        
        case 0:
            if (file_exists($config_file)) {
                if (is_writable($config_file)) {
                    echo "<p class='success'>Plik <code>$config_file</code> istnieje i posiada uprawnienia do zapisu!</p>";
                    echo "<button class='btn-action' onclick=\"window.location.href='install.php?step=1'\">Przejdź do konfiguracji &rarr;</button>";
                } else {
                    echo "<p class='error'>Brak uprawnień do zapisu pliku <code>$config_file</code>.</p>";
                    echo "<p>W systemie Linux nadaj mu uprawnienia (<code>chmod 666 $config_file</code>). W systemie Windows kliknij plik prawym przyciskiem, wybierz 'Właściwości' i odznacz opcję 'Tylko do odczytu'.</p>";
                    echo "<button class='btn-action' onclick=\"window.location.href='install.php?step=0'\">Odśwież stronę</button>";
                }
            } else {
                echo "<p class='error'>Brak pliku <code>$config_file</code>!</p>";
                echo "<p>Utwórz w głównym folderze projektu <b>pusty plik</b> o nazwie <code>$config_file</code>, aby instalator mógł zapisać w nim ustawienia.</p>";
                echo "<button class='btn-action' onclick=\"window.location.href='install.php?step=0'\">Odśwież stronę</button>";
            }
            break;

        case 1:
            echo "
            <p>Podaj dane do połączenia z serwerem bazy danych MySQL (zazwyczaj jest to panel XAMPP):</p>
            <form method='post' action='install.php?step=2'>
                <label>Serwer (Host):</label>
                <input type='text' name='host' value='127.0.0.1' required>
                
                <label>Użytkownik (User):</label>
                <input type='text' name='user' value='root' required>
                
                <label>Hasło (Password):</label>
                <input type='password' name='passwd' placeholder='(często puste w XAMPP)'>
                
                <label>Nazwa bazy danych (DB Name):</label>
                <input type='text' name='dbname' value='LearnIt' required>
                
                <button type='submit' class='btn-action'>Zapisz konfigurację &rarr;</button>
            </form>";
            break;

        case 2:
            $host = $_POST['host'] ?? '127.0.0.1';
            $user = $_POST['user'] ?? 'root';
            $passwd = $_POST['passwd'] ?? '';
            $dbname = $_POST['dbname'] ?? 'LearnIt';

            $file = fopen($config_file, "w");
            // Modyfikacja kodu konfiguracyjnego: łączymy się z serwerem, a wybór bazy zabezpieczamy warunkiem
            $config_kod = "<?php\n"
                        . "// Fragment wygenerowany przy użyciu narzędzia sztucznej inteligencji: Gemini, gemini.google.com [data dostępu: 12.06.2026]\n"
                        . "\$host=\"$host\";\n"
                        . "\$user=\"$user\";\n"
                        . "\$password=\"$passwd\";\n"
                        . "\$dbname=\"$dbname\";\n\n"
                        . "\$conn = @mysqli_connect(\$host, \$user, \$password);\n"
                        . "if (!\$conn) { die(\"Błąd połączenia z serwerem MySQL: \" . mysqli_connect_error()); }\n"
                        . "if (@mysqli_select_db(\$conn, \$dbname)) {\n"
                        . "    mysqli_set_charset(\$conn, \"utf8\");\n"
                        . "}\n"
                        . "?>";
            
            if (fwrite($file, $config_kod)) {
                echo "<p class='success'>Krok 2: Plik konfiguracyjny został pomyślnie wygenerowany.</p>";
                
                $link = @mysqli_connect($host, $user, $passwd);
                if (!$link) {
                    echo "<p class='error'>Błąd: Dane dostępowe są niepoprawne. Nie można połączyć z MySQL.</p>";
                    echo "<button class='btn-action' onclick=\"window.location.href='install.php?step=1'\">&larr; Wróć i popraw dane</button>";
                } else {
                    echo "<p class='success'>✅ Połączenie z serwerem bazy danych zostało pomyślnie zweryfikowane.</p>";
                    echo "<button class='btn-action' onclick=\"window.location.href='install.php?step=3'\">Krok 3: Wygeneruj struktury tabel &rarr;</button>";
                }
            } else {
                echo "<p class='error'>Błąd zapisu pliku <code>$config_file</code>.</p>";
            }
            fclose($file);
            break;

        case 3:
            // Wymuszamy włączenie raportowania błędów na ekranie, by w razie czego natychmiast zobaczyć komunikat serwera
            ini_set('display_errors', 1);
            error_reporting(E_ALL);

            if (!file_exists('config.php')) {
                die("<p class='error'>Błąd: Plik config.php nie istnieje!</p>");
            }
            
            include 'config.php';
            
            // Ponieważ config.php połączył się tylko z serwerem, tutaj bezpiecznie tworzymy lub wybieramy bazę
            $db_check = mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8 COLLATE utf8_polish_ci");
            mysqli_select_db($conn, $dbname);
            
            echo "<p>Inicjalizacja i tworzenie tabel w bazie <b>$dbname</b>:</p>";

            // Zapobiegamy błędom z istniejącymi kluczami obcymi podczas reinstalacji
            mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");
            mysqli_query($conn, "DROP TABLE IF EXISTS fiszka, zestaw, kategoria, uzytkownik, rola");
            mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

            $create = [
                "CREATE TABLE rola (
                    id_roli INT PRIMARY KEY AUTO_INCREMENT,
                    nazwa VARCHAR(30) NOT NULL UNIQUE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci",
                
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci",

                "CREATE TABLE kategoria (
                    id_kategorii INT PRIMARY KEY AUTO_INCREMENT,
                    nazwa VARCHAR(50) NOT NULL UNIQUE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci",

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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci",

                "CREATE TABLE fiszka (
                    id_fiszki INT PRIMARY KEY AUTO_INCREMENT,
                    id_zestawu INT NOT NULL,
                    pojecie VARCHAR(255) NOT NULL,
                    definicja TEXT NOT NULL,
                    FOREIGN KEY (id_zestawu) REFERENCES zestaw(id_zestawu) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci"
            ];

            foreach ($create as $i => $sql) {
                if(mysqli_query($conn, $sql)) {
                    echo "<p>✅ Tabela ".($i+1)." (" . substr($sql, 13, 10) . "...) została wygenerowana.</p>";
                } else {
                    echo "<p class='error'>❌ Błąd tabeli ".($i+1).": " . mysqli_error($conn) . "</p>";
                }
            }

            echo "<button class='btn-action' onclick=\"window.location.href='install.php?step=4'\">Krok 4: Wczytaj dane i zamknij instalację &rarr;</button>";
            break;
        case 4:
            require 'config.php';
            ini_set('max_execution_time', 300);

            mysqli_query($conn, "INSERT INTO rola (id_roli, nazwa) VALUES (1, 'admin'), (2, 'user'), (3, 'guest')");
            
            $jezyki = ['Angielski', 'Hiszpański', 'Niemiecki', 'Francuski', 'Włoski', 'Inna'];
            foreach ($jezyki as $j) {
                mysqli_query($conn, "INSERT INTO kategoria (nazwa) VALUES ('$j')");
            }
            $hashed_admin = password_hash('admin123', PASSWORD_DEFAULT);
            mysqli_query($conn, "INSERT INTO uzytkownik (id_roli, login, haslo, email, imie, nazwisko, czy_aktywny) 
                                 VALUES (1, 'admin', '$hashed_admin', 'admin@learnit.pl', 'Admin', 'Główny', 1)");

            $slowka = [];
            if (($handle = fopen("slowka.csv", "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                    if (count($data) >= 2) {
                        $slowka[] = [trim($data[0]), trim($data[1])];
                    }
                }
                fclose($handle);
            }

            if (empty($slowka)) {
                echo "<p class='error'>Błąd: Nie udało się wczytać słówek z pliku <code>slowka.csv</code>. Upewnij się, że plik istnieje i używa średników (;).</p>";
            } else {
                echo "<p>✅ Wczytano pomyślnie " . count($slowka) . " słówek z pliku CSV.</p>";
            }

            $paczki = array_chunk($slowka, 50);

            $hashed_user = password_hash('user123', PASSWORD_DEFAULT);
            $uzytkownicy = [
                ['tomek', 'Tomasz'], ['kasia', 'Katarzyna'], ['marek', 'Marek'],
                ['zosia', 'Zofia'], ['piotr', 'Piotr'], ['magda', 'Magdalena'],
                ['kamil', 'Kamil'], ['ola', 'Aleksandra'], ['michal', 'Michał'], ['natalia', 'Natalia']
            ];

            $indeks_zestawu = 0;

            foreach ($uzytkownicy as $u) {
                $log = $u[0]; $im = $u[1];
                $mail = $log . '@learnit.pl';
                mysqli_query($conn, "INSERT INTO uzytkownik (id_roli, login, haslo, email, imie, czy_aktywny) VALUES (2, '$log', '$hashed_user', '$mail', '$im', 1)");
                $id_uz = mysqli_insert_id($conn);

                for ($i = 0; $i < 2; $i++) {
                    if (!isset($paczki[$indeks_zestawu])) break;
                    
                    $cz = $indeks_zestawu + 1;
                    $ile = count($paczki[$indeks_zestawu]);
                    mysqli_query($conn, "INSERT INTO zestaw (id_kategorii, id_uzytkownika, tytul, opis, liczba_fiszek) VALUES (1, $id_uz, 'Angielski Top 1000 - Część $cz', 'Wygenerowano automatycznie z pliku CSV.', $ile)");
                    $id_zest = mysqli_insert_id($conn);

                    $sql_fiszki = "INSERT INTO fiszka (id_zestawu, pojecie, definicja) VALUES ";
                    $wartosci = [];
                    foreach ($paczki[$indeks_zestawu] as $p) {
                        $poj = mysqli_real_escape_string($conn, $p[0]);
                        $def = mysqli_real_escape_string($conn, $p[1]);
                        $wartosci[] = "($id_zest, '$poj', '$def')";
                    }
                    $sql_fiszki .= implode(", ", $wartosci);
                    mysqli_query($conn, $sql_fiszki);

                    $indeks_zestawu++;
                }
            }

            file_put_contents('install.lock', 'Zabezpieczenie przed nadpisaniem bazy. Aby ponownie uruchomic instalator, usun ten plik z serwera.');

            echo "<div class='success'>
                    <h3>🎉 Instalacja została poprawnie zakończona!</h3>
                    <p>Tabele wczytane. Użytkownicy testowi i fiszki wygenerowane z pliku zewnętrznego.</p>
                    <p>Utworzono plik <code>install.lock</code> - aplikacja jest bezpieczna.</p>
                  </div>";
            echo "<button class='btn-action' onclick=\"window.location.href='index.php'\">Przejdź do aplikacji &rarr;</button>";
            break;
    }
    ?>
</div>

</body>
</html>