<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// Pobieramy frazę wyszukiwania, jeśli istnieje
$szukana_fraza = '';
if (isset($_GET['szukaj'])) {
    $szukana_fraza = mysqli_real_escape_string($conn, trim($_GET['szukaj']));
}

$czy_zalogowany = isset($_SESSION['zalogowany']) && $_SESSION['zalogowany'] === true;
$id_zalogowanego = $czy_zalogowany ? $_SESSION['id_uzytkownika'] : 0;
?>

<?php include 'header.php'; ?>

<main class="main-content">
    <div class="fiszki-header">
        <h2>Przeglądaj zestawy</h2>
        <p>Znajdź interesujące Cię materiały lub ucz się z zestawów stworzonych przez innych.</p>
        
        <div class="search-container">
            <form method="get" action="fiszki.php" class="search-form">
                <input type="text" name="szukaj" class="search-input" placeholder="Szukaj po nazwie zestawu..." value="<?php echo htmlspecialchars($szukana_fraza); ?>">
                <button type="submit" class="btn-search"><i class="fa-solid fa-magnifying-glass"></i> Szukaj</button>
            </form>
            <?php if (!empty($szukana_fraza)): ?>
                <div class="search-results-info">
                    <p>Wyniki wyszukiwania dla: <strong><?php echo htmlspecialchars($szukana_fraza); ?></strong></p>
                    <a href="fiszki.php" class="btn-clear-search">Wyczyść wyszukiwanie</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($czy_zalogowany && empty($szukana_fraza)): ?>
        <section class="dashboard-section">
            <h3>Twoje zestawy</h3>
            <div class="sets-grid">
                <?php
                $sql_moje = "SELECT z.id_zestawu, z.tytul, z.liczba_fiszek, k.nazwa AS kategoria_nazwa 
                             FROM zestaw z 
                             JOIN kategoria k ON z.id_kategorii = k.id_kategorii 
                             WHERE z.id_uzytkownika = $id_zalogowanego 
                             ORDER BY z.data_utworzenia DESC";
                $res_moje = mysqli_query($conn, $sql_moje);

                if (mysqli_num_rows($res_moje) > 0) {
                    while ($row = mysqli_fetch_assoc($res_moje)) {
                        echo '<div class="set-card">';
                        echo '<h4>' . htmlspecialchars($row['tytul']) . '</h4>';
                        echo '<span class="set-category">' . htmlspecialchars($row['kategoria_nazwa']) . '</span>';
                        echo '<p>Fiszki: <strong>' . $row['liczba_fiszek'] . '</strong></p>';
                        echo '<a href="zestaw.php?id=' . $row['id_zestawu'] . '" class="btn-study">Otwórz</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p class="empty-state">Nie masz jeszcze żadnych zestawów. <a href="dodaj_zestaw.php">Stwórz nowy!</a></p>';
                }
                ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="dashboard-section">
        <h3><?php echo empty($szukana_fraza) ? 'Odkrywaj zestawy' : 'Znalezione zestawy'; ?></h3>
        <div class="sets-grid">
            <?php
            // Podstawowe zapytanie - szukamy tylko zestawów innych użytkowników
            $warunek_wyszukiwania = "";
            if (!empty($szukana_fraza)) {
                $warunek_wyszukiwania = "AND z.tytul LIKE '%$szukana_fraza%'";
            }

            // Losowe pobieranie zestawów innych osób (LIMIT 8, żeby nie przytłoczyć strony)
            // Jeśli szukamy, pokazujemy po prostu pasujące
            $sortowanie = empty($szukana_fraza) ? "ORDER BY RAND() LIMIT 8" : "ORDER BY z.data_utworzenia DESC";

            $sql_inne = "SELECT z.id_zestawu, z.tytul, z.liczba_fiszek, k.nazwa AS kategoria_nazwa, u.login AS autor 
                         FROM zestaw z 
                         JOIN kategoria k ON z.id_kategorii = k.id_kategorii 
                         JOIN uzytkownik u ON z.id_uzytkownika = u.id_uzytkownika 
                         WHERE z.id_uzytkownika != $id_zalogowanego $warunek_wyszukiwania 
                         $sortowanie";
            
            $res_inne = mysqli_query($conn, $sql_inne);

            if (mysqli_num_rows($res_inne) > 0) {
                while ($row = mysqli_fetch_assoc($res_inne)) {
                    // Dodajemy klasę popular-card, żeby odróżnić zestawy społeczności
                    echo '<div class="set-card popular-card">';
                    echo '<h4>' . htmlspecialchars($row['tytul']) . '</h4>';
                    echo '<span class="set-category">' . htmlspecialchars($row['kategoria_nazwa']) . '</span>';
                    echo '<p style="margin-bottom: 5px; font-size: 0.9em; color: #888;">Autor: <strong>' . htmlspecialchars($row['autor']) . '</strong></p>';
                    echo '<p>Fiszki: <strong>' . $row['liczba_fiszek'] . '</strong></p>';
                    echo '<a href="zestaw.php?id=' . $row['id_zestawu'] . '" class="btn-study">Zobacz</a>';
                    echo '</div>';
                }
            } else {
                echo '<p class="empty-state">Brak wyników. Spróbuj wpisać inną nazwę.</p>';
            }
            ?>
        </div>
    </section>

</main>

<?php include 'footer.php'; ?>