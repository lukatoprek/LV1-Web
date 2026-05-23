<?php
require_once 'config/session.php';
require_once 'config/db.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$korisnikId = (int)$_SESSION['korisnik_id'];

$stmt = getPDO()->prepare(
    'SELECT pl.id AS stavka_id, p.naslov, p.autor, p.zanr, p.godina, p.bpm, p.raspolozenje
     FROM playlista pl
     JOIN pjesme p ON pl.pjesma_id = p.id
     WHERE pl.korisnik_id = ?
     ORDER BY pl.added_at DESC'
);
$stmt->execute([$korisnikId]);
$stavke = $stmt->fetchAll();

$pageTitle  = 'Moja playlista';
$activePage = 'playlista';
include 'includes/header.php';
?>

<main role="main">
    <section class="content playlist-sekcija info-section" aria-labelledby="playlist-naslov">

        <div class="playlist-header">
            <h2 id="playlist-naslov">
                &#9835; Moja playlista
                <?php if (!empty($stavke)): ?>
                    <span class="playlist-broj-badge"><?= count($stavke) ?></span>
                <?php endif; ?>
            </h2>
            <?php if (!empty($stavke)): ?>
                <form method="POST" action="playlista_akcija.php"
                      onsubmit="return confirm('Jeste li sigurni da &#382;elite o&#269;istiti playlistu?');">
                    <input type="hidden" name="akcija" value="ocisti">
                    <button type="submit" class="reset-btn">&#10006; O&#269;isti playlistu</button>
                </form>
            <?php endif; ?>
        </div>

        <?php if ($flash): ?>
            <div class="playlist-poruka"><?= htmlspecialchars($flash['tekst']) ?></div>
        <?php endif; ?>

        <?php if (empty($stavke)): ?>
            <div class="playlist-prazan">
                <p>Tvoja playlista je prazna. <a href="index.php">Dodaj pjesme</a> iz popisa.</p>
            </div>
        <?php else: ?>
            <table class="playlist-tablica" aria-label="Moja playlista">
                <thead>
                    <tr>
                        <th scope="col">Pjesma</th>
                        <th scope="col">Izvo&#273;a&#269;</th>
                        <th scope="col">&#381;anr</th>
                        <th scope="col">Godina</th>
                        <th scope="col">BPM</th>
                        <th scope="col">Raspolo&#382;enje</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stavke as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['naslov']) ?></td>
                        <td><?= htmlspecialchars($s['autor']) ?></td>
                        <td><?= htmlspecialchars($s['zanr']) ?></td>
                        <td><?= (int)$s['godina'] ?></td>
                        <td><?= (int)$s['bpm'] ?></td>
                        <td><?= htmlspecialchars($s['raspolozenje']) ?></td>
                        <td>
                            <form method="POST" action="playlista_akcija.php">
                                <input type="hidden" name="akcija"    value="ukloni">
                                <input type="hidden" name="stavka_id" value="<?= (int)$s['stavka_id'] ?>">
                                <button type="submit" class="ukloni-btn">&#10006; Ukloni</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </section>
</main>

<?php include 'includes/footer.php'; ?>
