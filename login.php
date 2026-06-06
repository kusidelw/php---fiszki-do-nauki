<?php
session_start();
require_once 'config.php';


if (isset($_SESSION['zalogowany']) && $_SESSION['zalogowany'] === true) {
    header("Location: index.php");
    exit;
}

$blad = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = mysqli_real_escape_string($conn, trim($_POST['login']));
    $haslo = trim($_POST['haslo']);

    if (empty($login) || empty($haslo)) {
        $blad = "Proszę wpisać login i hasło.";
    } else {
        $sql = "SELECT id_uzytkownika, login, haslo, id_roli, imie FROM uzytkownik WHERE login = '$login' AND czy_aktywny = 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            
            if (password_verify($haslo, $row['haslo'])) {
                $_SESSION['zalogowany'] = true;
                $_SESSION['id_uzytkownika'] = $row['id_uzytkownika'];
                $_SESSION['login'] = $row['login'];
                $_SESSION['id_roli'] = $row['id_roli'];
                $_SESSION['imie'] = $row['imie'];
                header("Location: index.php");
                exit;
            } else {
                $blad = "Nieprawidłowe hasło.";
            }
        } else {
            $blad = "Nie znaleziono konta z takim loginem lub jest ono nieaktywne.";
        }
    }
}
?>

<?php include 'header.php'; ?>
<main>
    <div class="login-container">
        <h2>Logowanie</h2>
        <?php if (!empty($blad)) { echo "<p class='error'>$blad</p>"; } ?>
        <form method="post" action="login.php">
            <label for="login">Login:</label>
            <input type="text" id="login" name="login" required>

            <label for="haslo">Hasło:</label>
            <input type="password" id="haslo" name="haslo" required>

            <button type="submit">Zaloguj</button>
            <p class="register-link">Nie masz jeszcze konta? <strong><a href="register.php"> Zarejestruj się tutaj</a></strong></p>
    </div>
        </form>
    </div>
</main>
<?php include 'footer.php'; ?>