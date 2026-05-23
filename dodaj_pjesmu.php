<?php
require_once 'config/session.php';
require_once 'config/db.php';

$greske = [];
$podaci = [
    'naslov' => '', 'autor' => '', 'zanr' => '',
    'trajanje' => '', 'bpm' => '', 'godina' => '', 'raspolozenje' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naslov       = trim($_POST['naslov']       ?? '');
    $autor        = trim($_POST['autor']        ?? '');
    $zanr         = trim($_POST['zanr']         ?? '');
    $trajanje     = trim($_POST['trajanje']     ?? '');
    $bpm          = trim($_POST['bpm']          ?? '');
    $godina       = trim($_POST['godina']       ?? '');
    $raspolozenje = trim($_POST['raspolozenje'] ?? '');

    $podaci = compact('naslov', 'autor', 'zanr', 'trajanje', 'bpm', 'godina', 'raspolozenje');

    if ($naslov === '')                                                    $greske[] = 'Naslov je obavezan.';
    elseif (strlen($naslov) > 150)                                        $greske[] = 'Naslov je preduga&#269;ak (max 150 znakova).';
    if ($autor === '')                                                     $greske[] = 'Izvo&#273;a&#269; je obavezan.';
    elseif (strlen($autor) > 150)                                         $greske[] = 'Ime izvo&#273;a&#269;a je preduga&#269;ko (max 150 znakova).';
    if ($zanr === '')                                                      $greske[] = '&#381;anr je obavezan.';
    elseif (strlen($zanr) > 80)                                           $greske[] = '&#381;anr je preduga&#269;ak (max 80 znakova).';
    if ($bpm === '')                                                       $greske[] = 'BPM je obavezan.';
    elseif (!ctype_digit($bpm) || (int)$bpm < 40 || (int)$bpm > 300)    $greske[] = 'BPM mora biti cijeli broj izme&#273;u 40 i 300.';
    if ($godina === '')                                                    $greske[] = 'Godina je obavezna.';
    elseif (!ctype_digit($godina) || (int)$godina < 1900 || (int)$godina > 2025)
                                                                          $greske[] = 'Godina mora biti izme&#273;u 1900 i 2025.';
    if ($raspolozenje === '')                                              $greske[] = 'Raspolo&#382;enje je obavezno.';
    elseif (strlen($raspolozenje) > 80)                                   $greske[] = 'Raspolo&#382;enje je preduga&#269;ko (max 80 znakova).';

    if (empty($greske)) {
        getPDO()->prepare(
            'INSERT INTO pjesme (naslov, autor, zanr, bpm, godina, raspolozenje, trajanje)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $naslov, $autor, $zanr,
            (int)$bpm, (int)$godina,
            $raspolozenje,
            $trajanje !== '' ? $trajanje : null
        ]);
        header('Location: index.php');
        exit;
    }
}

$pageTitle  = 'Dodaj pjesmu';
$activePage = 'index';
include 'includes/header.php';
?>
<main role="main">
    <section class="content" style="max-width:600px;margin:30px auto;">
        <h1>Dodaj novu pjesmu</h1>

        <?php if (!empty($greske)): ?>
            <ul style="color:#e94560;font-weight:bold;margin-bottom:16px;padding-left:18px;">
                <?php foreach ($greske as $g): ?>
                    <li><?= $g ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="POST" action="dodaj_pjesmu.php" class="form-flex">
            <input type="text"   name="naslov"       placeholder="Naslov pjesme *"                   maxlength="150"
                   value="<?= htmlspecialchars($podaci['naslov']) ?>"       required>
            <input type="text"   name="autor"        placeholder="Izvo&#273;a&#269; *"               maxlength="150"
                   value="<?= htmlspecialchars($podaci['autor']) ?>"        required>
            <input type="text"   name="zanr"         placeholder="&#381;anr *"                       maxlength="80"
                   value="<?= htmlspecialchars($podaci['zanr']) ?>"         required>
            <input type="text"   name="trajanje"     placeholder="Trajanje (npr. 3:45) &#8212; opcionalno" maxlength="10"
                   value="<?= htmlspecialchars($podaci['trajanje']) ?>">
            <input type="number" name="bpm"          placeholder="BPM * (40&#8211;300)"              min="40" max="300"
                   value="<?= htmlspecialchars($podaci['bpm']) ?>"          required>
            <input type="text"   name="godina"       placeholder="Godina * (npr. 2024)"              maxlength="4" inputmode="numeric" pattern="[0-9]{4}"
                   value="<?= htmlspecialchars($podaci['godina']) ?>"       required>
            <input type="text"   name="raspolozenje" placeholder="Raspolo&#382;enje *"               maxlength="80"
                   value="<?= htmlspecialchars($podaci['raspolozenje']) ?>" required>
            <button type="submit">Spremi pjesmu</button>
        </form>

        <p style="margin-top:16px;text-align:center;">
            <a href="index.php">&#8592; Natrag na popis</a>
        </p>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
