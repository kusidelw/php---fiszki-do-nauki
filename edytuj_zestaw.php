<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_zestawu = intval($_GET['id']);
$id_uzytkownika = $_SESSION['id_uzytkownika'];
$blad = '';

// Sprawdzamy czy zestaw należy do zalogowanego
$sql_sprawdz = "SELECT tytul, opis FROM zestaw WHERE id_zestawu = $id_zestawu AND id_uzytkownika = $id_uzytkownika";
$result_sprawdz = mysqli_query($conn, $sql_sprawdz);

if (mysqli_num_rows($result_sprawdz) == 0) {
    header("Location: index.php");
    exit;
}

$zestaw = mysqli_fetch_assoc($result_sprawdz);

// --- AKCJA 1: DODAWANIE FISZKI ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dodaj_fiszke'])) {
    $pojecie = mysqli_real_escape_string($conn, trim($_POST['pojecie']));
    $definicja = mysqli_real_escape_string($conn, trim($_POST['definicja']));

    if (!empty($pojecie) && !empty($definicja)) {
        mysqli_query($conn, "INSERT INTO fiszka (id_zestawu, pojecie, definicja) VALUES ($id_zestawu, '$pojecie', '$definicja')");
        mysqli_query($conn, "UPDATE zestaw SET liczba_fiszek = liczba_fiszek + 1 WHERE id_zestawu = $id_zestawu");
        header("Location: edytuj_zestaw.php?id=" . $id_zestawu);
        exit;
    } else {
        $blad = "Pojęcie i definicja nie mogą być puste.";
    }
}

// --- AKCJA 2: ZAPISYWANIE EDYTOWANEJ FISZKI ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['zapisz_fiszke'])) {
    $id_edytowanej = intval($_POST['id_fiszki']);
    $nowe_pojecie = mysqli_real_escape_string($conn, trim($_POST['pojecie']));
    $nowa_definicja = mysqli_real_escape_string($conn, trim($_POST['definicja']));
    
    if (!empty($nowe_pojecie) && !empty($nowa_definicja)) {
        mysqli_query($conn, "UPDATE fiszka SET pojecie = '$nowe_pojecie', definicja = '$nowa_definicja' WHERE id_fiszki = $id_edytowanej AND id_zestawu = $id_zestawu");
        header("Location: edytuj_zestaw.php?id=" . $id_zestawu);
        exit;
    }
}

// --- AKCJA 3: USUWANIE FISZKI ---
if (isset($_GET['usun_fiszke'])) {
    $id_usun = intval($_GET['usun_fiszke']);
    mysqli_query($conn, "DELETE FROM fiszka WHERE id_fiszki = $id_usun AND id_zestawu = $id_zestawu");
    
    if (mysqli_affected_rows($conn) > 0) {
        mysqli_query($conn, "UPDATE zestaw SET liczba_fiszek = liczba_fiszek - 1 WHERE id_zestawu = $id_zestawu");
    }
    header("Location: edytuj_zestaw.php?id=" . $id_zestawu);
    exit;
}

// Pobieranie wszystkich fiszek
$sql_fiszki = "SELECT id_fiszki, pojecie, definicja FROM fiszka WHERE id_zestawu = $id_zestawu ORDER BY id_fiszki DESC";
$result_fiszki = mysqli_query($conn, $sql_fiszki);
?>

<?php include 'header.php'; ?>

<main class="main-content">
    <div class="zestaw-header">
        <h2>Edytujesz: <?php echo htmlspecialchars($zestaw['tytul']); ?></h2>
        <a href="zestaw.php?id=<?php echo $id_zestawu; ?>" class="btn-outline">&larr; Zakończ edycję i przejdź do nauki</a>
    </div>

    <div class="zestaw-layout">
        
        <div class="login-container dodaj-fiszke-form">
            <h3>Dodaj nową fiszkę</h3>
            <?php if (!empty($blad)) { echo "<div class='error'>$blad</div>"; } ?>
            
            <form method="post" action="edytuj_zestaw.php?id=<?php echo $id_zestawu; ?>">
                <label for="pojecie">Pojęcie (np. słówko)</label>
                <input type="text" id="pojecie" name="pojecie" required>

                <label for="definicja">Definicja / Tłumaczenie</label>
                <textarea id="definicja" name="definicja" rows="3" required></textarea>

                <button type="submit" name="dodaj_fiszke">Dodaj do zestawu</button>
            </form>
        </div>

        <div class="fiszki-lista">
            <h3>Zarządzaj fiszkami (<?php echo mysqli_num_rows($result_fiszki); ?>)</h3>
            
            <div class="fiszki-grid">
                <?php
               
                $id_w_edycji = isset($_GET['edytuj_fiszke']) ? intval($_GET['edytuj_fiszke']) : 0;

                if (mysqli_num_rows($result_fiszki) > 0) {
                    while ($fiszka = mysqli_fetch_assoc($result_fiszki)) {
                        
                        if ($id_w_edycji === $fiszka['id_fiszki']) {
                            echo '<div class="fiszka-card edit-mode">';
                            echo '<form method="post" action="edytuj_zestaw.php?id='.$id_zestawu.'">';
                            echo '<input type="hidden" name="id_fiszki" value="'.$fiszka['id_fiszki'].'">';
                            echo '<input type="text" name="pojecie" value="'.htmlspecialchars($fiszka['pojecie']).'" required class="edit-input">';
                            echo '<textarea name="definicja" rows="2" required class="edit-textarea">'.htmlspecialchars($fiszka['definicja']).'</textarea>';
                            echo '<div class="action-buttons">';
                            echo '<button type="submit" name="zapisz_fiszke" class="btn-save">Zapisz</button>';
                            echo '<a href="edytuj_zestaw.php?id='.$id_zestawu.'" class="btn-cancel">Anuluj</a>';
                            echo '</div>';
                            echo '</form>';
                            echo '</div>';
                        } else {
                            
                            echo '<div class="fiszka-card">';
                            echo '<h4>' . htmlspecialchars($fiszka['pojecie']) . '</h4>';
                            echo '<p>' . htmlspecialchars($fiszka['definicja']) . '</p>';
                            echo '<div class="action-links">';
                            echo '<a href="edytuj_zestaw.php?id='.$id_zestawu.'&edytuj_fiszke='.$fiszka['id_fiszki'].'" class="link-edit"><i class="fa-solid fa-pen"></i> Edytuj</a>';
                            
                            echo '<a href="edytuj_zestaw.php?id='.$id_zestawu.'&usun_fiszke='.$fiszka['id_fiszki'].'" class="link-delete" onclick="return confirm(\'Na pewno usunąć tę fiszkę z zestawu?\')"><i class="fa-solid fa-trash"></i> Usuń</a>';
                            echo '</div>';
                            echo '</div>';
                        }
                    }
                } else {
                    echo '<p class="empty-state">Ten zestaw jest jeszcze pusty.</p>';
                }
                ?>
            </div>
        </div>
        
    </div>
</main>

<?php include 'footer.php'; ?>