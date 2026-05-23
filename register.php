<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['korisnik_id'])) {
    header('Location: index.php');
    exit;
}
require_once 'config/db.php';

$greske = [];
$podaci = ['korisnicko_ime' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ime  = trim($_POST['korisnicko_ime'] ?? '');
    $mail = trim($_POST['email']          ?? '');
    $loz  = $_POST['lozinka']             ?? '';
    $loz2 = $_POST['lozinka_potvrda']     ?? '';

    $podaci = ['korisnicko_ime' => $ime, 'email' => $mail];

    if ($ime === '')                                       $greske[] = 'Korisni&#269;ko ime je obavezno.';
    elseif (strlen($ime) > 50)                            $greske[] = 'Korisni&#269;ko ime je preduga&#269;ko (max 50 znakova).';
    if ($mail === '')                                      $greske[] = 'Email je obavezan.';
    elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL))     $greske[] = 'Email adresa nije ispravna.';
    if ($loz === '')                                       $greske[] = 'Lozinka je obavezna.';
    elseif (strlen($loz) < 8)                             $greske[] = 'Lozinka mora imati najmanje 8 znakova.';
    elseif ($loz !== $loz2)                               $greske[] = 'Lozinke se ne podudaraju.';

    if (empty($greske)) {
        $stmt = getPDO()->prepare(
            'SELECT id FROM korisnici WHERE korisnicko_ime = ? OR email = ?'
        );
        $stmt->execute([$ime, $mail]);
        if ($stmt->fetch()) {
            $greske[] = 'Korisni&#269;ko ime ili email ve&#263; postoji.';
        } else {
            $hash = password_hash($loz, PASSWORD_DEFAULT);
            getPDO()->prepare(
                'INSERT INTO korisnici (korisnicko_ime, lozinka, email) VALUES (?, ?, ?)'
            )->execute([$ime, $hash, $mail]);
            header('Location: login.php?registriran=1');
            exit;
        }
    }
}

$pageTitle  = 'Registracija';
$activePage = '';
include 'includes/header.php';
?>
<main role="main">
    <section class="content" style="max-width:400px;margin:40px auto;">
        <h1>Registracija</h1>
        <?php if (!empty($greske)): ?>
            <ul style="color:#e94560;font-weight:bold;margin-bottom:14px;padding-left:18px;">
                <?php foreach ($greske as $g): ?>
                    <li><?= $g ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form method="POST" action="register.php" class="form-flex">
            <input type="text"     name="korisnicko_ime"  placeholder="Korisni&#269;ko ime"
                   value="<?= htmlspecialchars($podaci['korisnicko_ime']) ?>"
                   required maxlength="50" autocomplete="username">
            <input type="email"    name="email"           placeholder="Email"
                   value="<?= htmlspecialchars($podaci['email']) ?>"
                   required maxlength="100" autocomplete="email">
            <input type="password" name="lozinka"         placeholder="Lozinka (min. 8 znakova)"
                   required autocomplete="new-password">
            <input type="password" name="lozinka_potvrda" placeholder="Potvrdite lozinku"
                   required autocomplete="new-password">
            <button type="submit">Registriraj se</button>
        </form>
        <p style="margin-top:16px;text-align:center;">
            Ve&#263; imate ra&#269;un? <a href="login.php">Prijavite se</a>
        </p>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
