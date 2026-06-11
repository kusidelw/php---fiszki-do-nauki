<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true) {
    header("Location: login.php");
    exit;
}

$blad = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tytul = mysqli_real_escape_string($conn, trim($_POST['tytul']));
    $id_kategorii = intval($_POST['kategoria']);
    $opis = mysqli_real_escape_string($conn, trim($_POST['opis']));
    $id_uz = $_SESSION['id_uzytkownika'];

    if (empty($tytul) || empty($id_kategorii)) {
        $blad = "Tytuł i kategoria są wymagane!";
    } else {
        $sql = "INSERT INTO zestaw (id_kategorii, id_uzytkownika, tytul, opis, liczba_fiszek) 
                VALUES ($id_kategorii, $id_uz, '$tytul', '$opis', 0)";
        
        if (mysqli_query($conn, $sql)) {
            $nowy_id = mysqli_insert_id($conn);
            header("Location: edytuj_zestaw.php?id=" . $nowy_id);
            exit;
        } else {
            $blad = "Wystąpił błąd podczas dodawania zestawu: " . mysqli_error($conn);
        }
    }
}
?>

<?php include 'header.php'; ?>
<main class="main-content">
    <div class="login-container dodaj-zestaw-form">
        <h2>Stwórz nowy zestaw</h2>
        
        <?php if (!empty($blad)) { echo "<div class='error'>$blad</div>"; } ?>
        
        <form method="post" action="dodaj_zestaw.php">
            <label for="tytul">Tytuł zestawu *</label>
            <input type="text" id="tytul" name="tytul" placeholder="np. Słówka z angielskiego - jedzenie" required>

            <label for="kategoria">Kategoria (Język) *</label>
            <select id="kategoria" name="kategoria" required>
                <option value="">Wybierz kategorię...</option>
                <?php
               
                $sql_kat = "SELECT id_kategorii, nazwa FROM kategoria ORDER BY nazwa ASC";
                $res_kat = mysqli_query($conn, $sql_kat);
                
                while($row = mysqli_fetch_assoc($res_kat)) {
                    echo "<option value='" . $row['id_kategorii'] . "'>" . htmlspecialchars($row['nazwa']) . "</option>";
                }
                ?>
            </select>

            <label for="opis">Opis (opcjonalnie)</label>
            <textarea id="opis" name="opis" rows="4" placeholder="Krótki opis, czego dotyczy ten zestaw..."></textarea>

            <button type="submit">Utwórz zestaw</button>
        </form>
    </div>
</main>
<?php include 'footer.php'; ?>