# LearnIt - Platforma do nauki z fiszkami

Projekt zaliczeniowy z przedmiotu Aplikacje Internetowe.
Platforma pozwala na tworzenie, edytowanie i udostępnianie interaktywnych zestawów fiszek do nauki języków obcych.

## Wymagania środowiskowe
- System: Linux / Windows
- Serwer WWW: Apache
- Baza danych: MySQL / MariaDB
- Język: PHP 8.x

## Instrukcja instalacji
1. Skopiuj cały folder z projektem do katalogu serwera (np. `htdocs` w środowisku XAMPP).
2. Upewnij się, że serwery Apache i MySQL są uruchomione.
3. Otwórz przeglądarkę i wpisz adres prowadzący do pliku instalacyjnego, np.:
   `http://localhost/php---fiszki-do-nauki/install.php`
4. Skrypt instalacyjny automatycznie usunie starą bazę (jeśli istnieje), utworzy nową bazę `LearnIt`, wygeneruje struktury tabel i uzupełni system o początkowe dane testowe.

## Konta testowe do logowania
Po poprawnej instalacji systemu, można skorzystać z poniższych kont testowych:

**Konto Administratora:**
* Login: `admin`
* Hasło: `admin123`

**Konta Zwykłych Użytkowników (User):**
* Login: `janek`
* Hasło: `user123`
* Login: `anna`
* Hasło: `user123`