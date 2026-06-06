<?php
session_start();
require_once 'config.php';

// Jeśli użytkownik jest już zalogowany, odsyłamy go na stronę główną
if (isset($_SESSION['zalogowany']) && $_SESSION['zalogowany'] === true) {
    header("Location: index.php");
    exit;
}

$blad = '';
$sukces = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Zabezpieczamy pobrane dane
    $login = mysqli_real_escape_string($conn, trim($_POST['login']));
    $haslo = trim($_POST['haslo']);
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $imie = mysqli_real_escape_string($conn, trim($_POST['imie']));
    $nazwisko = mysqli_real_escape_string($conn, trim($_POST['nazwisko']));
    $telefon = mysqli_real_escape_string($conn, trim($_POST['telefon']));
    $data_urodzenia = mysqli_real_escape_string($conn, trim($_POST['data_urodzenia']));

    // Sprawdzamy, czy najważniejsze pola nie są puste
    if (empty($login) || empty($haslo) || empty($email)) {
        $blad = "Proszę wypełnić wymagane pola (Login, Hasło, Email).";
    } else {
        // Sprawdzamy, czy taki login lub email już istnieje w bazie
        $check_sql = "SELECT id_uzytkownika FROM uzytkownik WHERE login = '$login' OR email = '$email'";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            $blad = "Konto z takim loginem lub adresem email już istnieje!";
        } else {
            // Haszujemy hasło dla bezpieczeństwa
            $hashed_password = password_hash($haslo, PASSWORD_DEFAULT);
            $id_roli = 2; // Przypisujemy rolę "user" (ID: 2)

            // Wstawiamy dane do bazy
            $insert_sql = "INSERT INTO uzytkownik (id_roli, login, haslo, email, telefon, imie, nazwisko, data_urodzenia, czy_aktywny) 
                           VALUES ($id_roli, '$login', '$hashed_password', '$email', '$telefon', '$imie', '$nazwisko', '$data_urodzenia', 1)";

            if (mysqli_query($conn, $insert_sql)) {
                $sukces = "Konto zostało pomyślnie utworzone! Możesz się zalogować.";
            } else {
                $blad = "Wystąpił błąd podczas rejestracji: " . mysqli_error($conn);
            }
        }
    }
}
?>

<?php include 'header.php'; ?>
<main>
    <div class="login-container">
        <h2>Rejestracja</h2>
        
        <?php 
        // Wyświetlanie komunikatów za pomocą klas
        if (!empty($blad)) { 
            echo "<div class='error'>$blad</div>"; 
        }
        if (!empty($sukces)) { 
            echo "<div class='success'>$sukces</div>"; 
        }
        ?>
        
        <form method="post" action="register.php">
            <label for="login">Login *</label>
            <input type="text" id="login" name="login" required>

            <label for="haslo">Hasło *</label>
            <input type="password" id="haslo" name="haslo" required>

            <label for="email">E-mail *</label>
            <input type="email" id="email" name="email" required>

            <label for="imie">Imię</label>
            <input type="text" id="imie" name="imie">

            <label for="nazwisko">Nazwisko</label>
            <input type="text" id="nazwisko" name="nazwisko">

            <label for="telefon">Telefon</label>
            <input type="text" id="telefon" name="telefon">

            <label for="data_urodzenia">Data urodzenia</label>
            <input type="date" id="data_urodzenia" name="data_urodzenia">

            <button type="submit">Załóż konto</button>
        </form>
        
        <p class="register-link">Masz już konto? <br> <a href="login.php">Zaloguj się</a></p>
    </div>
</main>
<?php include 'footer.php'; ?>