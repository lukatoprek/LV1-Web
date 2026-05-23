<?php
// Requires: $pageTitle (string), $activePage ('index'|'slike'|'grafikon'|'playlista'|'')
// Optional: $extraCss (string path to second stylesheet)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$loggedIn = isset($_SESSION['korisnik_id']);
$username = $loggedIn ? htmlspecialchars($_SESSION['korisnicko_ime']) : '';
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <?php if (!empty($extraCss)): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($extraCss) ?>">
    <?php endif; ?>
    <title><?= htmlspecialchars($pageTitle ?? 'Glazba') ?></title>
</head>
<body>

<header role="banner">
    <h1>Glazba</h1>
</header>

<nav aria-label="Primarna navigacija" role="navigation">
    <input type="checkbox" id="nav-toggle" class="nav-toggle-checkbox" aria-hidden="true">
    <label for="nav-toggle" class="nav-toggle-btn" aria-label="Otvori navigacijski izbornik">&#9776; Menu</label>
    <ul class="nav-menu" role="list">
        <li role="listitem">
            <a href="index.php" <?= ($activePage ?? '') === 'index'    ? 'aria-current="page"' : '' ?>>Po&#269;etna</a>
        </li>
        <li role="listitem">
            <a href="slike.php" <?= ($activePage ?? '') === 'slike'    ? 'aria-current="page"' : '' ?>>Slike</a>
        </li>
        <li role="listitem">
            <a href="grafikon.php" <?= ($activePage ?? '') === 'grafikon' ? 'aria-current="page"' : '' ?>>Grafikon</a>
        </li>
        <?php if ($loggedIn): ?>
        <li role="listitem">
            <a href="playlista.php" <?= ($activePage ?? '') === 'playlista' ? 'aria-current="page"' : '' ?>>Moja playlista</a>
        </li>
        <li role="listitem" style="padding:12px 25px;color:rgba(255,255,255,0.7);font-size:0.9rem;">
            &#128100; <?= $username ?>
        </li>
        <li role="listitem"><a href="logout.php">Odjava</a></li>
        <?php else: ?>
        <li role="listitem"><a href="login.php">Prijava</a></li>
        <li role="listitem"><a href="register.php">Registracija</a></li>
        <?php endif; ?>
    </ul>
</nav>
