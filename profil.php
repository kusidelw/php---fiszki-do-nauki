<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true) {
    header("Location: login.php");
    exit;
}

$id_uz = $_SESSION['id_uzytkownika'];
$komunikat = '';


if (isset($_GET['usun_zestaw'])) {
    $id_do_usuniecia = intval($_GET['usun_zestaw']);
    $sql_usun = "DELETE FROM zestaw WHERE id_zestawu = $id_do_usuniecia AND id_uzytkownika = $id_uz";
    if (mysqli_query($conn, $sql_usun)) {
        header("Location: profil.php");
        exit;
    }
}
-
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['aktualizuj_profil'])) {
    $imie = mysqli_real_escape_string($conn, trim($_POST['imie']));
    $nazwisko = mysqli_real_escape_string($conn, trim($_POST['nazwisko']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $telefon = mysqli_real_escape_string($conn, trim($_POST['telefon']));
    $nowe_haslo = trim($_POST['nowe_haslo']);

  
    $_SESSION['imie'] = $imie;

  
    if (!empty($nowe_haslo)) {
        $hashed = password_hash($nowe_haslo, PASSWORD_DEFAULT);
        $sql_upd = "UPDATE uzytkownik SET imie='$imie', nazwisko='$nazwisko', email='$email', telefon='$telefon', haslo='$hashed' WHERE id_uzytkownika=$id_uz";
    } else {
        $sql_upd = "UPDATE uzytkownik SET imie='$imie', nazwisko='$nazwisko', email='$email', telefon='$telefon' WHERE id_uzytkownika=$id_uz";
    }

    if (mysqli_query($conn, $sql_upd)) {
        $komunikat = "<div class='success'>Twój profil został pomyślnie zaktualizowany!</div>";
    } else {
        $komunikat = "<div class='error'>Błąd podczas aktualizacji: " . mysqli_error($conn) . "</div>";
    }
}

$sql_dane = "SELECT * FROM uzytkownik WHERE id_uzytkownika = $id_uz";
$res_dane = mysqli_query($conn, $sql_dane);
$uzytkownik = mysqli_fetch_assoc($res_dane);


$sql_moje_zestawy = "SELECT z.id_zestawu, z.tytul, z.liczba_fiszek, k.nazwa AS kategoria_nazwa 
                     FROM zestaw z 
                     JOIN kategoria k ON z.id_kategorii = k.id_kategorii 
                     WHERE z.id_uzytkownika = $id_uz 
                     ORDER BY z.data_utworzenia DESC";
$res_moje_zestawy = mysqli_query($conn, $sql_moje_zestawy);
?>

<?php include 'header.php'; ?>

<main class="main-content">
    <div class="profil-header">
        <h2>Panel Użytkownika</h2>
        <p>Zarządzaj swoimi danymi oraz stworzonymi zestawami.</p>
    </div>

    <div class="profil-layout">
        
        <div class="login-container profil-form">
            <h3>Edytuj dane profilu</h3>
            <?php if (!empty($komunikat)) { echo $komunikat; } ?>
            
            <form method="post" action="profil.php">
                <label for="imie">Imię</label>
                <input type="text" id="imie" name="imie" value="<?php echo htmlspecialchars($uzytkownik['imie']); ?>" required>

                <label for="nazwisko">Nazwisko</label>
                <input type="text" id="nazwisko" name="nazwisko" value="<?php echo htmlspecialchars($uzytkownik['nazwisko']); ?>">

                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($uzytkownik['email']); ?>" required>

                <label for="telefon">Telefon</label>
                <input type="text" id="telefon" name="telefon" value="<?php echo htmlspecialchars($uzytkownik['telefon']); ?>">

                <hr class="profil-hr">
                
                <label for="nowe_haslo">Nowe hasło (zostaw puste, by nie zmieniać)</label>
                <input type="password" id="nowe_haslo" name="nowe_haslo" placeholder="Wpisz nowe hasło...">

                <button type="submit" name="aktualizuj_profil">Zapisz zmiany</button>
            </form>
        </div>

        <div class="profil-zestawy">
            <h3>Twoje zestawy (Zarządzanie)</h3>
            
            <div class="sets-grid profil-grid">
                <?php
                if (mysqli_num_rows($res_moje_zestawy) > 0) {
                    while ($row = mysqli_fetch_assoc($res_moje_zestawy)) {
                        echo '<div class="set-card">';
                        echo '<h4>' . htmlspecialchars($row['tytul']) . '</h4>';
                        echo '<span class="set-category">' . htmlspecialchars($row['kategoria_nazwa']) . '</span>';
                        echo '<p>Liczba fiszek: <strong>' . $row['liczba_fiszek'] . '</strong></p>';
                        
                        echo '<div class="action-links">';
                        echo '<a href="edytuj_zestaw.php?id=' . $row['id_zestawu'] . '" class="link-edit"><i class="fa-solid fa-pen"></i> Edytuj</a>';
                        // Przycisk usunięcia zestawu z wbudowanym ostrzeżeniem w JS
                        echo '<a href="profil.php?usun_zestaw=' . $row['id_zestawu'] . '" class="link-delete" onclick="return confirm(\'UWAGA: Trwale usuniesz ten zestaw i wszystkie jego fiszki. Czy na pewno?\')"><i class="fa-solid fa-trash"></i> Usuń</a>';
                        echo '</div>';
                        
                        echo '</div>';
                    }
                } else {
                    echo '<p class="empty-state">Nie stworzyłeś jeszcze żadnego zestawu. <a href="dodaj_zestaw.php">Kliknij tutaj, aby dodać pierwszy!</a></p>';
                }
                ?>
            </div>
        </div>
        
    </div>
</main>

<?php include 'footer.php'; ?>