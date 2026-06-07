<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// Jeśli nie ma ID w linku, wracamy na stronę główną
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_zestawu = intval($_GET['id']);

// Pobranie danych zestawu, aby wyświetlić tytuł
$sql_zestaw = "SELECT tytul FROM zestaw WHERE id_zestawu = $id_zestawu";
$res_zestaw = mysqli_query($conn, $sql_zestaw);
if (mysqli_num_rows($res_zestaw) == 0) {
    header("Location: index.php");
    exit;
}
$zestaw = mysqli_fetch_assoc($res_zestaw);

// Pobranie samych słówek
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
        <h2><?php echo htmlspecialchars($zestaw['tytul']); ?></h2>
        <div class="nauka-opcje">
            <a href="zestaw.php?id=<?php echo $id_zestawu; ?>" class="btn-outline">&larr; Wróć do zestawu</a>
            <label class="switch-losowo">
                <input type="checkbox" id="losowa-kolejnosc">
                <span>Losowa kolejność</span>
            </label>
        </div>
    </div>

    <?php if ($ile_wszystkich > 0): ?>
        
        <div class="nauka-obszar">
            <div class="nauka-postep-kontener">
                <span class="badge-postep">Opanowano: <strong id="licznik-znanych">0</strong> z <?php echo $ile_wszystkich; ?></span>
                <span class="badge-pozostalo">Do powtórki: <strong id="pozostalo-wyswietlacz"><?php echo $ile_wszystkich; ?></strong></span>
            </div>
            
            <div id="obszar-karty">
                <div class="fiszka-kontener" onclick="odwrocKarte()">
                    <div class="fiszka-wewnatrz" id="karta-wewnatrz">
                        <div class="fiszka-przod">
                            <span class="fiszka-etykieta">Pojęcie</span>
                            <h3 id="tekst-przod">Ładowanie...</h3>
                            <span class="fiszka-wskazowka">Kliknij kartę, aby odwrócić</span>
                        </div>
                        <div class="fiszka-tyl">
                            <span class="fiszka-etykieta">Definicja</span>
                            <h3 id="tekst-tyl">Ładowanie...</h3>
                            <span class="fiszka-wskazowka">Kliknij kartę, aby odwrócić</span>
                        </div>
                    </div>
                </div>

                <div class="nauka-sterowanie">
                    <button class="btn-sterowanie btn-lewo" onclick="klikNieUmiem()">
                        <i class="fa-solid fa-arrow-left"></i> Nie umiem
                    </button>
                    <button class="btn-sterowanie btn-prawo" onclick="klikUmiem()">
                        Umiem <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <div id="ekran-koncowy" class="ekran-koncowy" style="display: none;">
                <h3 style="color: #5cb85c; font-size: 2.5em; margin-bottom: 10px;">Gratulacje! 🎉</h3>
                <p style="font-size: 1.2em; color: var(--text-color); margin-bottom: 30px;">Opanowałeś wszystkie słówka z tego zestawu w tej sesji.</p>
                <div style="display: flex; gap: 20px; justify-content: center;">
                    <button class="btn-primary" onclick="zresetujNauke()"><i class="fa-solid fa-rotate-right"></i> Ucz się ponownie</button>
                    <a href="zestaw.php?id=<?php echo $id_zestawu; ?>" class="btn-outline">Wróć do zestawu</a>
                </div>
            </div>
        </div>

        <script>
            // Przejmujemy zmienną z PHP
            const oryginalneFiszki = <?php echo $fiszki_json; ?>;
            let doNauki = [...oryginalneFiszki]; // "Talia" kart, które musimy jeszcze powtórzyć
            let opanowane = 0;
            let obecnyIndeks = 0;
            
            // Elementy DOM
            const tekstPrzod = document.getElementById('tekst-przod');
            const tekstTyl = document.getElementById('tekst-tyl');
            const licznikZnanych = document.getElementById('licznik-znanych');
            const pozostaloWyswietlacz = document.getElementById('pozostalo-wyswietlacz');
            const kartaWewnatrz = document.getElementById('karta-wewnatrz');
            const obszarKarty = document.getElementById('obszar-karty');
            const ekranKoncowy = document.getElementById('ekran-koncowy');
            const checkboxLosowo = document.getElementById('losowa-kolejnosc');

            function wyswietlKarte() {
                // Sprawdzamy, czy wszystkie karty zostały odgadnięte
                if(doNauki.length === 0) {
                    obszarKarty.style.display = 'none';
                    ekranKoncowy.style.display = 'block';
                    return;
                }
                
                // Jeśli doszliśmy do końca "kolejki", zaczynamy przeglądanie od początku (ale tylko tych nieodgadniętych)
                if (obecnyIndeks >= doNauki.length) {
                    obecnyIndeks = 0;
                }
                
                // Zawsze ustawiamy przód karty
                kartaWewnatrz.classList.remove('odwrocona');
                
                setTimeout(() => {
                    tekstPrzod.innerText = doNauki[obecnyIndeks].pojecie;
                    tekstTyl.innerText = doNauki[obecnyIndeks].definicja;
                    pozostaloWyswietlacz.innerText = doNauki.length;
                }, 150);
            }

            function odwrocKarte() {
                kartaWewnatrz.classList.toggle('odwrocona');
            }

            function klikUmiem() {
                if (doNauki.length === 0) return;
                
                // Usuwamy fiszkę z "talii" do nauki
                doNauki.splice(obecnyIndeks, 1);
                
                // Zwiększamy statystyki
                opanowane++;
                licznikZnanych.innerText = opanowane;
                
                // Nie zwiększamy 'obecnyIndeks', bo tablica się skurczyła i następny element automatycznie "wpadł" na to samo miejsce
                wyswietlKarte();
            }

            function klikNieUmiem() {
                if (doNauki.length === 0) return;
                
                // Przeskakujemy do następnej karty (obecna wciąż zostaje w tablicy do ponownej powtórki)
                obecnyIndeks++;
                wyswietlKarte();
            }

            function przetasuj(tablica) {
                let obecny = tablica.length, losowy;
                while (obecny !== 0) {
                    losowy = Math.floor(Math.random() * obecny);
                    obecny--;
                    [tablica[obecny], tablica[losowy]] = [tablica[losowy], tablica[obecny]];
                }
                return tablica;
            }

            function zresetujNauke() {
                // Odnawiamy całą "talię"
                doNauki = [...oryginalneFiszki];
                if (checkboxLosowo.checked) {
                    doNauki = przetasuj(doNauki);
                }
                opanowane = 0;
                obecnyIndeks = 0;
                
                // Resetujemy wyświetlacze
                licznikZnanych.innerText = opanowane;
                pozostaloWyswietlacz.innerText = doNauki.length;
                
                // Przywracamy widok karty i chowamy sukces
                obszarKarty.style.display = 'block';
                ekranKoncowy.style.display = 'none';
                wyswietlKarte();
            }

            // Obsługa przełącznika losowej kolejności
            checkboxLosowo.addEventListener('change', function() {
                zresetujNauke();
            });

            // Start pierwszej rundy!
            wyswietlKarte();
        </script>
        
    <?php else: ?>
        <div class="empty-state">
            Ten zestaw nie ma jeszcze żadnych fiszek. 
            <a href="edytuj_zestaw.php?id=<?php echo $id_zestawu; ?>">Wróć, aby je dodać.</a>
        </div>
    <?php endif; ?>
</main>

<?php include 'footer.php'; ?>