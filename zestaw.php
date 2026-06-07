<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_zestawu = intval($_GET['id']);

$sql_sprawdz = "SELECT z.tytul, z.opis, z.id_uzytkownika, u.login AS autor 
                FROM zestaw z 
                JOIN uzytkownik u ON z.id_uzytkownika = u.id_uzytkownika 
                WHERE z.id_zestawu = $id_zestawu";
$result_sprawdz = mysqli_query($conn, $sql_sprawdz);

if (mysqli_num_rows($result_sprawdz) == 0) {
    header("Location: index.php");
    exit;
}

$zestaw = mysqli_fetch_assoc($result_sprawdz);

$czy_wlasciciel = false;
$czy_zalogowany = false;
if (isset($_SESSION['zalogowany']) && $_SESSION['zalogowany'] === true) {
    $czy_zalogowany = true;
    if ($_SESSION['id_uzytkownika'] == $zestaw['id_uzytkownika']) {
        $czy_wlasciciel = true;
    }
}

$sql_fiszki = "SELECT pojecie, definicja FROM fiszka WHERE id_zestawu = $id_zestawu";
$result_fiszki = mysqli_query($conn, $sql_fiszki);
$liczba_fiszek = mysqli_num_rows($result_fiszki);
?>

<?php include 'header.php'; ?>

<main class="main-content">
    <div class="zestaw-header">
        <h2><?php echo htmlspecialchars($zestaw['tytul']); ?></h2>
        <p class="zestaw-autor">Autor: <strong><?php echo htmlspecialchars($zestaw['autor']); ?></strong></p>
        
        <?php if (!empty($zestaw['opis'])): ?>
            <p><?php echo htmlspecialchars($zestaw['opis']); ?></p>
        <?php endif; ?>
        
        <div class="zestaw-akcje-glowne">
            <a href="index.php" class="btn-outline">&larr; Wróć do kokpitu</a>
            
            <?php if ($liczba_fiszek > 0): ?>
                <?php if ($czy_zalogowany): ?>
                    <a href="nauka_podglad.php?id=<?php echo $id_zestawu; ?>" class="btn-primary btn-ucz-sie"><i class="fa-solid fa-play"></i> Przeglądanie</a>
                    <a href="nauka_pisanie.php?id=<?php echo $id_zestawu; ?>" class="btn-primary btn-ucz-sie"><i class="fa-solid fa-keyboard"></i> Pisanie</a>
                <?php else: ?>
                    <p class="locked-info-text">
                        <i class="fa-solid fa-lock"></i> <a href="login.php">Zaloguj się</a>, aby odblokować interaktywne tryby nauki.
                    </p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($czy_wlasciciel): ?>
                <a href="edytuj_zestaw.php?id=<?php echo $id_zestawu; ?>" class="btn-edit-main"><i class="fa-solid fa-pen"></i> Edytuj zestaw</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="fiszki-lista-full">
        <h3 class="fiszki-naglowek">Lista słówek (<?php echo $liczba_fiszek; ?>)</h3>
        
        <div class="fiszki-grid-large">
            <?php
            if ($liczba_fiszek > 0) {
                while ($fiszka = mysqli_fetch_assoc($result_fiszki)) {
                    echo '<div class="fiszka-card-read">';
                    echo '<h4>' . htmlspecialchars($fiszka['pojecie']) . '</h4>';
                    echo '<hr>';
                    echo '<p>' . htmlspecialchars($fiszka['definicja']) . '</p>';
                    echo '</div>';
                }
            } else {
                echo '<p class="empty-state">Ten zestaw jest jeszcze pusty.</p>';
            }
            ?>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>