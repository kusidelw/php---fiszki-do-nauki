<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// Zabezpieczenie przed nieautoryzowanym dostępem
if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true || $_SESSION['id_uzytkownika'] != 1) {
    header("Location: index.php");
    exit;
}

$komunikat = '';

// --- AKCJA 1: DODAWANIE NOWEJ KATEGORII ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dodaj_kategorie'])) {
    $nazwa = mysqli_real_escape_string($conn, trim($_POST['nazwa_kategorii']));
    
    if (!empty($nazwa)) {
        // Sprawdzamy czy kategoria już istnieje
        $check = mysqli_query($conn, "SELECT id_kategorii FROM kategoria WHERE nazwa = '$nazwa'");
        if (mysqli_num_rows($check) == 0) {
            $sql_add = "INSERT INTO kategoria (nazwa) VALUES ('$nazwa')";
            if (mysqli_query($conn, $sql_add)) {
                $komunikat = "<div class='success'>Nowa kategoria została dodana!</div>";
            }
        } else {
            $komunikat = "<div class='error'>Taka kategoria już istnieje w systemie.</div>";
        }
    }
}

// --- AKCJA 2: ZAPISYWANIE EDYTOWANEJ KATEGORII ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['zapisz_kategorie'])) {
    $id_kat = intval($_POST['id_kategorii']);
    $nowa_nazwa = mysqli_real_escape_string($conn, trim($_POST['nazwa']));
    
    if (!empty($nowa_nazwa)) {
        $sql_upd = "UPDATE kategoria SET nazwa = '$nowa_nazwa' WHERE id_kategorii = $id_kat";
        if (mysqli_query($conn, $sql_upd)) {
            header("Location: admin_kategorie.php");
            exit;
        }
    }
}

// --- AKCJA 3: USUWANIE KATEGORII ---
if (isset($_GET['usun_kategorie'])) {
    $id_usun = intval($_GET['usun_kategorie']);
    
    // ON DELETE CASCADE w bazie zajmie się usunięciem powiązanych zestawów i fiszek
    $sql_del = "DELETE FROM kategoria WHERE id_kategorii = $id_usun";
    if (mysqli_query($conn, $sql_del)) {
        $komunikat = "<div class='success'>Kategoria została pomyślnie usunięta.</div>";
    } else {
        $komunikat = "<div class='error'>Błąd podczas usuwania: " . mysqli_error($conn) . "</div>";
    }
}

// Pobieranie wszystkich kategorii posortowanych alfabetycznie
$sql_kategorie = "SELECT id_kategorii, nazwa FROM kategoria ORDER BY nazwa ASC";
$res_kategorie = mysqli_query($conn, $sql_kategorie);
?>

<?php include 'header.php'; ?>

<main class="main-content">
    <div class="admin-header">
        <h2>Panel Administratora</h2>
        <p>Zarządzaj kategoriami (językami) dostępnymi w systemie LearnIt.</p>
        
        <div class="admin-nav">
            <a href="admin_uzytkownicy.php" class="btn-admin"><i class="fa-solid fa-users"></i> Użytkownicy</a>
            <a href="admin_kategorie.php" class="btn-admin active"><i class="fa-solid fa-tags"></i> Kategorie</a>
            <a href="admin_zestawy.php" class="btn-admin"><i class="fa-solid fa-layer-group"></i> Zestawy</a>
        </div>
    </div>

    <div class="zestaw-layout">
        
        <div class="login-container dodaj-fiszke-form">
            <h3>Dodaj nowy język</h3>
            <?php if (!empty($komunikat) && !isset($_GET['edytuj'])) { echo $komunikat; } ?>
            
            <form method="post" action="admin_kategorie.php">
                <label for="nazwa_kategorii">Nazwa kategorii (np. Szwedzki)</label>
                <input type="text" id="nazwa_kategorii" name="nazwa_kategorii" required placeholder="Wpisz nazwę...">
                <button type="submit" name="dodaj_kategorie">Dodaj do bazy</button>
            </form>
        </div>

        <div class="fiszki-lista">
            <h3>Wszystkie kategorie w bazie (<?php echo mysqli_num_rows($res_kategorie); ?>)</h3>
            
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nazwa języka</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $id_w_edycji = isset($_GET['edytuj']) ? intval($_GET['edytuj']) : 0;
                        
                        while ($row = mysqli_fetch_assoc($res_kategorie)): 
                            if ($id_w_edycji === $row['id_kategorii']):
                        ?>
                                <tr>
                                    <td><?php echo $row['id_kategorii']; ?></td>
                                    <td colspan="2">
                                        <form method="post" action="admin_kategorie.php" class="action-buttons">
                                            <input type="hidden" name="id_kategorii" value="<?php echo $row['id_kategorii']; ?>">
                                            <input type="text" name="nazwa" value="<?php echo htmlspecialchars($row['nazwa']); ?>" required class="edit-input" style="margin-bottom: 0; max-width: 250px;">
                                            <button type="submit" name="zapisz_kategorie" class="btn-save">Zapisz</button>
                                            <a href="admin_kategorie.php" class="btn-cancel">Anuluj</a>
                                        </form>
                                    </td>
                                </tr>
                        <?php else: ?>
                                <tr>
                                    <td><?php echo $row['id_kategorii']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['nazwa']); ?></strong></td>
                                    <td>
                                        <div class="action-links" style="margin-top: 0; padding-top: 0; border-top: none; justify-content: flex-start;">
                                            <a href="admin_kategorie.php?edytuj=<?php echo $row['id_kategorii']; ?>" class="link-edit"><i class="fa-solid fa-pen"></i> Edytuj</a>
                                            <a href="admin_kategorie.php?usun_kategorie=<?php echo $row['id_kategorii']; ?>" class="link-delete" onclick="return confirm('UWAGA: Usunięcie kategorii spowoduje automatyczne skasowanie WSZYSTKICH zestawów i fiszek przypisanych do tego języka przez użytkowników! Czy na pewno chcesz to zrobić?');"><i class="fa-solid fa-trash"></i> Usuń</a>
                                        </div>
                                    </td>
                                </tr>
                        <?php 
                            endif;
                        endwhile; 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
</main>

<?php include 'footer.php'; ?>