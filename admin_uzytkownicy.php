<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true || $_SESSION['id_roli'] != 1) {
    header("Location: index.php");
    exit;
}

$komunikat = '';

if (isset($_GET['zmien_status']) && isset($_GET['id'])) {
    $id_do_zmiany = intval($_GET['id']);
    $nowy_status = intval($_GET['zmien_status']);
    if ($id_do_zmiany !== $_SESSION['id_uzytkownika']) {
        $sql_status = "UPDATE uzytkownik SET czy_aktywny = $nowy_status WHERE id_uzytkownika = $id_do_zmiany";
        if (mysqli_query($conn, $sql_status)) {
            $komunikat = "<div class='success'>Status użytkownika został pomyślnie zaktualizowany.</div>";
        } else {
            $komunikat = "<div class='error'>Błąd zmiany statusu: " . mysqli_error($conn) . "</div>";
        }
    } else {
        $komunikat = "<div class='error'>Nie możesz zablokować własnego konta!</div>";
    }
}
$sql_uzytkownicy = "SELECT u.id_uzytkownika, u.login, u.email, u.imie, u.nazwisko, u.czy_aktywny, r.nazwa AS rola_nazwa 
                    FROM uzytkownik u 
                    JOIN rola r ON u.id_roli = r.id_roli 
                    ORDER BY u.id_uzytkownika ASC";
$res_uzytkownicy = mysqli_query($conn, $sql_uzytkownicy);
?>

<?php include 'header.php'; ?>

<main class="main-content">
    <div class="admin-header">
        <h2>Panel Administratora</h2>
        <p>Zarządzaj użytkownikami całego systemu.</p>
        
        <div class="admin-nav">
            <a href="admin_uzytkownicy.php" class="btn-admin active"><i class="fa-solid fa-users"></i> Użytkownicy</a>
            <a href="admin_kategorie.php" class="btn-admin"><i class="fa-solid fa-tags"></i> Kategorie</a>
            <a href="admin_zestawy.php" class="btn-admin"><i class="fa-solid fa-layer-group"></i> Zestawy</a>
        </div>
    </div>

    <section class="admin-section">
        <h3>Lista zarejestrowanych użytkowników</h3>
        <?php if (!empty($komunikat)) { echo $komunikat; } ?>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Login</th>
                        <th>Imię i Nazwisko</th>
                        <th>Email</th>
                        <th>Rola</th>
                        <th>Status</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($res_uzytkownicy)): ?>
                        <tr>
                            <td><?php echo $row['id_uzytkownika']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['login']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <span class="badge-rola <?php echo $row['rola_nazwa'] === 'admin' ? 'rola-admin' : 'rola-user'; ?>">
                                    <?php echo htmlspecialchars($row['rola_nazwa']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['czy_aktywny']): ?>
                                    <span class="status-aktywny"><i class="fa-solid fa-check-circle"></i> Aktywny</span>
                                <?php else: ?>
                                    <span class="status-zablokowany"><i class="fa-solid fa-ban"></i> Zablokowany</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['id_uzytkownika'] !== $_SESSION['id_uzytkownika']): ?>
                                    <?php if ($row['czy_aktywny']): ?>
                                        <a href="admin_uzytkownicy.php?zmien_status=0&id=<?php echo $row['id_uzytkownika']; ?>" class="btn-action btn-block" onclick="return confirm('Czy na pewno chcesz zablokować tego użytkownika?');">Zablokuj</a>
                                    <?php else: ?>
                                        <a href="admin_uzytkownicy.php?zmien_status=1&id=<?php echo $row['id_uzytkownika']; ?>" class="btn-action btn-unblock">Odblokuj</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Twoje konto</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>