<?php
// Sprawdzamy, czy sesja już istnieje, zanim ją wystartujemy
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
include 'header.php'; 
?>

<main class="main-content">
    <?php if(isset($_SESSION['zalogowany']) && $_SESSION['zalogowany'] === true): ?>
        
        <div class="dashboard-header">
            <h2>Witaj, <?php echo htmlspecialchars($_SESSION['imie']); ?>!</h2>
            <a href="dodaj_zestaw.php" class="btn-new-set"><i class="fa-solid fa-plus"></i> Stwórz nowy zestaw</a>
        </div>

        <section class="dashboard-section">
            <h3>Niedawno uczone się zestawy</h3>
            <div class="sets-grid">
                <?php
                $id_uz = $_SESSION['id_uzytkownika'];
                $sql_recent = "SELECT z.id_zestawu, z.tytul, z.liczba_fiszek, k.nazwa AS kategoria_nazwa 
                                FROM zestaw z 
                                JOIN kategoria k ON z.id_kategorii = k.id_kategorii 
                                WHERE z.id_uzytkownika = $id_uz 
                                ORDER BY z.data_utworzenia DESC LIMIT 4";
                $res_recent = mysqli_query($conn, $sql_recent);

                if(mysqli_num_rows($res_recent) > 0) {
                    while($row = mysqli_fetch_assoc($res_recent)) {
                        echo '<div class="set-card">';
                        echo '<h4>' . htmlspecialchars($row['tytul']) . '</h4>';
                        echo '<span class="set-category">' . htmlspecialchars($row['kategoria_nazwa']) . '</span>';
                        echo '<p>Fiszki: <strong>' . $row['liczba_fiszek'] . '</strong></p>';
                        echo '<a href="zestaw.php?id=' . $row['id_zestawu'] . '" class="btn-study">Ucz się</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p class="empty-state">Nie masz jeszcze żadnych zestawów. Stwórz swój pierwszy!</p>';
                }
                ?>
            </div>
        </section>

        <section class="dashboard-section">
            <h3>Popularne zestawy</h3>
            <div class="sets-grid">
                <?php
                $sql_popular = "SELECT z.id_zestawu, z.tytul, z.liczba_fiszek, k.nazwa AS kategoria_nazwa 
                                FROM zestaw z 
                                JOIN kategoria k ON z.id_kategorii = k.id_kategorii 
                                ORDER BY z.liczba_fiszek DESC LIMIT 4";
                $res_popular = mysqli_query($conn, $sql_popular);

                if(mysqli_num_rows($res_popular) > 0) {
                    while($row = mysqli_fetch_assoc($res_popular)) {
                        echo '<div class="set-card popular-card">';
                        echo '<h4>' . htmlspecialchars($row['tytul']) . '</h4>';
                        echo '<span class="set-category">' . htmlspecialchars($row['kategoria_nazwa']) . '</span>';
                        echo '<p>Fiszki: <strong>' . $row['liczba_fiszek'] . '</strong></p>';
                        echo '<a href="zestaw.php?id=' . $row['id_zestawu'] . '" class="btn-study">Zobacz</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p class="empty-state">Brak zestawów w systemie.</p>';
                }
                ?>
            </div>
        </section>

   <?php else: ?>
        
        <div class="hero-section">
            <h2>Opanuj każdy materiał.</h2>
            <p>Twórz własne interaktywne fiszki, organizuj je w zestawy i ucz się efektywniej. Platforma LearnIt to Twój osobisty asystent nauki.</p>
        </div>

        <section class="dashboard-section">
            <h3 style="text-align: center; border-bottom: none; margin-bottom: 30px;">Popularne zestawy</h3>
            <div class="sets-grid">
                <?php
                // Wykorzystujemy to samo zapytanie, by pokazać gościom najpopularniejsze treści
                $sql_popular_guest = "SELECT z.id_zestawu, z.tytul, z.liczba_fiszek, k.nazwa AS kategoria_nazwa 
                                      FROM zestaw z 
                                      JOIN kategoria k ON z.id_kategorii = k.id_kategorii 
                                      ORDER BY z.liczba_fiszek DESC LIMIT 4";
                $res_popular_guest = mysqli_query($conn, $sql_popular_guest);

                if(mysqli_num_rows($res_popular_guest) > 0) {
                    while($row = mysqli_fetch_assoc($res_popular_guest)) {
                        echo '<div class="set-card popular-card">';
                        echo '<h4>' . htmlspecialchars($row['tytul']) . '</h4>';
                        echo '<span class="set-category">' . htmlspecialchars($row['kategoria_nazwa']) . '</span>';
                        echo '<p>Fiszki: <strong>' . $row['liczba_fiszek'] . '</strong></p>';
                        echo '<a href="zestaw.php?id=' . $row['id_zestawu'] . '" class="btn-study">Zobacz</a>';
                        echo '</div>';
                    }
                } else {
                    // Komunikat zachęcający do zostania pierwszym twórcą
                    echo '<p class="empty-state">System jeszcze nie posiada zestawów. <a href="register.php" style="color: var(--primary-color); font-weight: bold;">Zarejestruj się</a> i stwórz pierwszy!</p>';
                }
                ?>
            </div>
        </section>

    <?php endif; ?>
</main>


<?php include 'footer.php'; ?>
