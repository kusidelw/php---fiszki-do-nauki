<?php
// Upewniamy się, że sesja jest wystartowana, by móc z niej czytać
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LEARN IT</title> 
    <link rel="icon" type="image/x-icon" href="css/img/flash.png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<header>
    <nav class="nav">
        <h1><img src="css/img/flash.png" alt="Flash Icon">LEARN IT</h1>
        <ul class="nav-list">
            <li><a href="index.php">Strona Główna</a></li>
            <li><a href="fiszki.php">Fiszki</a></li>
            
            <?php 
            // Magia dynamicznego nagłówka!
            if(isset($_SESSION['zalogowany']) && $_SESSION['zalogowany'] === true): 
            ?>
                <li><a href="profil.php">Witaj, <?php echo htmlspecialchars($_SESSION['imie']); ?>!</a></li>
                <li><a href="logout.php">Wyloguj</a></li>
            <?php else: ?>
                <li><a href="login.php">Zaloguj</a></li>
            <?php endif; ?>
            
        </ul>
    </nav>
</header>
