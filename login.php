<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['korisnik_id'])) {
    header('Location: index.php');
    exit;
}
require_once 'config/db.php';

$greska = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ime = trim($_POST['korisnicko_ime'] ?? '');
    $loz = $_POST['lozinka'] ?? '';

    if ($ime === '' || $loz === '') {
        $greska = 'Unesite korisni&#269;ko ime i lozinku.';
    } else {
        $stmt = getPDO()->prepare(
            'SELECT id, korisnicko_ime, lozinka FROM korisnici WHERE korisnicko_ime = ?'
        );
        $stmt->execute([$ime]);
        $korisnik = $stmt->fetch();

        if ($korisnik && password_verify($loz, $korisnik['lozinka'])) {
            $_SESSION['korisnik_id']    = $korisnik['id'];
            $_SESSION['korisnicko_ime'] = $korisnik['korisnicko_ime'];
            header('Location: index.php');
            exit;
        } else {
            $greska = 'Pogre&#353;no korisni&#269;ko ime ili lozinka.';
        }
    }
}

$pageTitle  = 'Prijava';
$activePage = '';
include 'includes/header.php';
?>
<main role="main">
    <section class="content" style="max-width:400px;margin:40px auto;">
        <h1>Prijava</h1>
        <?php if ($greska): ?>
            <p style="color:#e94560;font-weight:bold;margin-bottom:14px;">
                <?= $greska ?>
            </p>
        <?php endif; ?>
        <?php if (isset($_GET['registriran'])): ?>
            <p style="color:#1a7a4a;font-weight:bold;margin-bottom:14px;">
                Registracija uspje&#353;na! Prijavite se.
            </p>
        <?php endif; ?>
        <form method="POST" action="login.php" class="form-flex">
            <input type="text"     name="korisnicko_ime" placeholder="Korisni&#269;ko ime"
                   value="<?= htmlspecialchars($_POST['korisnicko_ime'] ?? '') ?>"
                   required autocomplete="username">
            <input type="password" name="lozinka" placeholder="Lozinka"
                   required autocomplete="current-password">
            <button type="submit">Prijavi se</button>
        </form>
        <p style="margin-top:16px;text-align:center;">
            Nemate ra&#269;un? <a href="register.php">Registrirajte se</a>
        </p>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
