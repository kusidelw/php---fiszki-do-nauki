<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// Blokada dla niezalogowanych
if (!isset($_SESSION['zalogowany']) || $_SESSION['zalogowany'] !== true) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_zestawu = intval($_GET['id']);

$sql_zestaw = "SELECT tytul FROM zestaw WHERE id_zestawu = $id_zestawu";
$res_zestaw = mysqli_query($conn, $sql_zestaw);
if (mysqli_num_rows($res_zestaw) == 0) {
    header("Location: index.php");
    exit;
}
$zestaw = mysqli_fetch_assoc($res_zestaw);

$sql_fiszki = "SELECT pojecie, definicja FROM fiszka WHERE id_zestawu = $id_zestawu";
$res_fiszki = mysqli_query($conn, $sql_fiszki);

$fiszki = [];
while ($row = mysqli_fetch_assoc($res_fiszki)) {
    $fiszki[] = $row;
}
$ile_wszystkich = count($fiszki);
$fiszki_json = json_encode($fiszki);
?>

<?php include 'header.php'; ?>

<main class="main-content">
    <div class="nauka-header">
        <h2>Tryb pisania: <?php echo htmlspecialchars($zestaw['tytul']); ?></h2>
        <div class="nauka-opcje">
            <a href="zestaw.php?id=<?php echo $id_zestawu; ?>" class="btn-outline">&larr; Wyjdź</a>
        </div>
    </div>

    <?php if ($ile_wszystkich > 0): ?>
        <div class="pisanie-obszar">
            
            <div class="pisanie-status-panel">
                <span class="badge-runda" id="runda-tekst">Runda 1/2: Wpisz słówko</span>
                <span class="badge-pozostalo">Pozostało w rundzie: <strong id="pisanie-pozostalo">0</strong></span>
            </div>

            <div id="panel-pytania" class="login-container pisanie-box">
                <p class="pisanie-label" id="label-pytania">Pytanie (Definicja):</p>
                <h3 class="pisanie-pytanie-tekst" id="tekst-pytania">---</h3>
                
                <form id="formularz-odpowiedzi" onsubmit="sprawdzOdpowiedz(event)">
                    <label for="odpowiedz-uzytkownika">Twoja odpowiedź:</label>
                    <input type="text" id="odpowiedz-uzytkownika" autocomplete="off" required placeholder="Wpisz odpowiedź tutaj...">
                    <button type="submit" class="btn-search">Sprawdź</button>
                </form>

                <div id="feedback-panel" class="hidden">
                    <p id="feedback-status-tekst"></p>
                    <p id="feedback-szczegoly"></p>
                    <button class="btn-primary" onclick="dalejFiszka()">Dalej &rarr;</button>
                </div>
            </div>

            <div id="ekran-koncowy-pisanie" class="ekran-koncowy hidden">
                <h3 class="success-title">Pełne opanowanie! 🏆</h3>
                <p class="success-text">Brawo! Zapisałaś poprawnie wszystkie słowa oraz ich definicje. Twój wynik to 100%.</p>
                <div class="ekran-koncowy-akcje">
                    <button class="btn-primary" onclick="zresetujPisanie()"><i class="fa-solid fa-rotate-right"></i> Zacznij od nowa</button>
                    <a href="zestaw.php?id=<?php echo $id_zestawu; ?>" class="btn-outline">Wróć do zestawu</a>
                </div>
            </div>

        </div>

        <script>
    const bazaFiszek = <?php echo $fiszki_json; ?>;
    let pulaRundy = [...bazaFiszek];
    let runda = 1; // 1 = widzi definicję, wpisuje pojęcie | 2 = widzi pojęcie, wpisuje definicję
    let obecnyIndeks = 0;

    const tekstPytania = document.getElementById('tekst-pytania');
    const labelPytania = document.getElementById('label-pytania');
    const rundaTekst = document.getElementById('runda-tekst');
    const pozostaloLicznik = document.getElementById('pisanie-pozostalo');
    const inputOdpowiedz = document.getElementById('odpowiedz-uzytkownika');
    const formOdpowiedz = document.getElementById('formularz-odpowiedzi');
    const feedbackPanel = document.getElementById('feedback-panel');
    const feedbackStatus = document.getElementById('feedback-status-tekst');
    const feedbackSzczegoly = document.getElementById('feedback-szczegoly');
    const panelPytania = document.getElementById('panel-pytania');
    const ekranKoncowy = document.getElementById('ekran-koncowy-pisanie');

    function ustawPytanie() {
        if (pulaRundy.length === 0) {
            if (runda === 1) {
                runda = 2;
                pulaRundy = [...bazaFiszek];
                obecnyIndeks = 0;
                // Usunięto irytujący alert - przejście jest teraz płynne i ciche!
                ustawPytanie();
            } else {
                panelPytania.classList.add('hidden');
                ekranKoncowy.classList.remove('hidden');
            }
            return;
        }

        if (obecnyIndeks >= pulaRundy.length) {
            obecnyIndeks = 0;
        }

        pozostaloLicznik.innerText = pulaRundy.length;
        inputOdpowiedz.value = '';
        inputOdpowiedz.disabled = false;
        formOdpowiedz.querySelector('button').classList.remove('hidden');
        feedbackPanel.classList.add('hidden');
        
        // Domyślnie ukrywamy pasek ze szczegółami błędu
        feedbackSzczegoly.classList.add('hidden'); 

        if (runda === 1) {
            rundaTekst.innerText = "Runda 1/2: Wpisz słówko";
            labelPytania.innerText = "Zobacz definicję i wpisz odpowiadające pojęcie:";
            tekstPytania.innerText = pulaRundy[obecnyIndeks].definicja;
        } else {
            rundaTekst.innerText = "Runda 2/2: Wpisz definicję";
            labelPytania.innerText = "Zobacz pojęcie i wpisz jego pełną definicję:";
            tekstPytania.innerText = pulaRundy[obecnyIndeks].pojecie;
        }
        
        // Automatyczne ustawienie kursora w polu tekstowym (żeby nie trzeba było za każdym razem klikać myszką)
        inputOdpowiedz.focus();
    }

    function sprawdzOdpowiedz(e) {
        e.preventDefault();
        const wpisana = inputOdpowiedz.value.trim().toLowerCase();
        let prawidlowa = '';

        if (runda === 1) {
            prawidlowa = pulaRundy[obecnyIndeks].pojecie.trim().toLowerCase();
        } else {
            prawidlowa = pulaRundy[obecnyIndeks].definicja.trim().toLowerCase();
        }

        inputOdpowiedz.disabled = true;
        formOdpowiedz.querySelector('button').classList.add('hidden');
        feedbackPanel.classList.remove('hidden');

        if (wpisana === prawidlowa) {
            feedbackStatus.innerText = "Doskonale! Poprawna odpowiedź.";
            feedbackStatus.className = "success-msg";
            feedbackSzczegoly.innerText = "";
            // Jeśli sukces, to stanowczo ukrywamy ramkę ze szczegółami
            feedbackSzczegoly.classList.add('hidden'); 
            pulaRundy.splice(obecnyIndeks, 1);
        } else {
            feedbackStatus.innerText = "Niestety błąd...";
            feedbackStatus.className = "error-msg";
            let oczekiwana = runda === 1 ? pulaRundy[obecnyIndeks].pojecie : pulaRundy[obecnyIndeks].definicja;
            feedbackSzczegoly.innerText = "Twoja odpowiedź: '" + inputOdpowiedz.value + "'. Prawidłowa to: '" + oczekiwana + "'.";
            // Jeśli błąd, zdejmujemy ukrycie, żeby pokazać czerwoną ramkę
            feedbackSzczegoly.classList.remove('hidden'); 
            obecnyIndeks++;
        }
    }

    function dalejFiszka() {
        ustawPytanie();
    }

    function zresetujPisanie() {
        pulaRundy = [...bazaFiszek];
        runda = 1;
        obecnyIndeks = 0;
        panelPytania.classList.remove('hidden');
        ekranKoncowy.classList.add('hidden');
        ustawPytanie();
    }

    ustawPytanie();
</script>
    <?php else: ?>
        <div class="empty-state">
            Ten zestaw nie ma fiszek.
        </div>
    <?php endif; ?>
</main>

<?php include 'footer.php'; ?>