<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// TARCZA OBRONNA: Tylko dla zalogowanego administratora
if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true || $_SESSION['id_roli'] != 1) {
    header("Location: index.php");
    exit;
}

$komunikat = '';

// --- AKCJA: USUWANIE DOWOLNEGO ZESTAWU PRZEZ ADMINA ---
if (isset($_GET['usun_zestaw'])) {
    $id_zestawu_usun = intval($_GET['usun_zestaw']);
    
    // ON DELETE CASCADE w bazie automatycznie wyczyści fiszki przypisane do tego zestawu
    $sql_del = "DELETE FROM zestaw WHERE id_zestawu = $id_zestawu_usun";
    
    if (mysqli_query($conn, $sql_del)) {
        $komunikat = "<div class='success'>Zestaw został pomyślnie usunięty z systemu przez administratora.</div>";
    } else {
        $komunikat = "<div class='error'>Błąd podczas usuwania zestawu: " . mysqli_error($conn) . "</div>";
    }
}

// Pobieranie wszystkich zestawów z całego systemu wraz z danymi o autorze i kategorii
$sql_wszystkie_zestawy = "SELECT z.id_zestawu, z.tytul, z.liczba_fiszek, z.data_utworzenia, k.nazwa AS kategoria_nazwa, u.login AS autor_login 
                          FROM zestaw z 
                          JOIN kategoria k ON z.id_kategorii = k.id_kategorii 
                          JOIN uzytkownik u ON z.id_uzytkownika = u.id_uzytkownika 
                          ORDER BY z.data_utworzenia DESC";
$res_zestawy = mysqli_query($conn, $sql_wszystkie_zestawy);
?>

<?php include 'header.php'; ?>

<main class="main-content">
    <div class="admin-header">
        <h2>Panel Administratora</h2>
        <p>Moderacja i zarządzanie wszystkimi zestawami fiszek w systemie LearnIt.</p>
        
        <div class="admin-nav">
            <a href="admin_uzytkownicy.php" class="btn-admin"><i class="fa-solid fa-users"></i> Użytkownicy</a>
            <a href="admin_kategorie.php" class="btn-admin"><i class="fa-solid fa-tags"></i> Kategorie</a>
            <a href="admin_zestawy.php" class="btn-admin active"><i class="fa-solid fa-layer-group"></i> Zestawy</a>
        </div>
    </div>

    <section class="admin-section">
        <h3>Wszystkie zestawy w systemie (<?php echo mysqli_num_rows($res_zestawy); ?>)</h3>
        <?php if (!empty($komunikat)) { echo $komunikat; } ?>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tytuł zestawu</th>
                        <th>Kategoria (Język)</th>
                        <th>Autor</th>
                        <th>Liczba fiszek</th>
                        <th>Data utworzenia</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($res_zestawy) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($res_zestawy)): ?>
                            <tr>
                                <td><?php echo $row['id_zestawu']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['tytul']); ?></strong></td>
                                <td><span class="set-category" style="margin-bottom: 0;"><?php echo htmlspecialchars($row['kategoria_nazwa']); ?></span></td>
                                <td><code><?php echo htmlspecialchars($row['autor_login']); ?></code></td>
                                <td><strong><?php echo $row['liczba_fiszek']; ?></strong></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($row['data_utworzenia'])); ?></td>
                                <td>
                                    <div class="action-links" style="margin-top: 0; padding-top: 0; border-top: none; gap: 10px; justify-content: flex-start;">
                                        <a href="zestaw.php?id=<?php echo $row['id_zestawu']; ?>" class="link-edit" style="color: var(--primary-color);"><i class="fa-solid fa-eye"></i> Podgląd</a>
                                        <a href="admin_zestawy.php?usun_zestaw=<?php echo $row['id_zestawu']; ?>" class="link-delete" onclick="return confirm('Czy na pewno chcesz MODEROWAĆ i bezpowrotnie usunąć ten zestaw wraz ze wszystkimi jego fiszkami?');"><i class="fa-solid fa-trash"></i> Skasuj</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-muted" style="text-align: center; padding: 30px;">Brak jakichkolwiek zestawów w bazie danych.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>