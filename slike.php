<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

$pdo    = getPDO();
$userId = $_SESSION['korisnik_id'] ?? null;

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$stmt = $pdo->prepare(
    'SELECT s.id, s.naziv, s.seed,
            ROUND(AVG(o.ocjena), 1)                                         AS avg_ocjena,
            COUNT(o.id)                                                      AS broj_ocjena,
            MAX(CASE WHEN o.id_korisnik = :uid THEN o.ocjena ELSE NULL END) AS moja_ocjena
     FROM slike s
     LEFT JOIN ocjene o ON o.id_slika = s.id
     GROUP BY s.id
     ORDER BY s.id'
);
$stmt->execute([':uid' => $userId]);
$slike = $stmt->fetchAll();

$pageTitle  = 'Glazba - Galerija slika';
$activePage = 'slike';
$extraCss   = 'style/style_slike.css';
include 'includes/header.php';
?>
<main role="main">
<section class="galerija" aria-labelledby="galerija-naslov">

    <h1 id="galerija-naslov">Galerija slika</h1>
    <p class="galerija-opis">Kliknite na sliku za prikaz u punoj veli&#269;ini.</p>

    <?php if ($flash): ?>
    <div class="galerija-toast galerija-toast--<?= htmlspecialchars($flash['tip']) ?>" role="alert">
        <?= htmlspecialchars($flash['tekst']) ?>
    </div>
    <?php endif; ?>

    <div class="galerija-grid">
    <?php foreach ($slike as $s):
        $id         = (int)$s['id'];
        $seed       = htmlspecialchars($s['seed']);
        $naziv      = htmlspecialchars($s['naziv']);
        $thumbUrl   = "https://picsum.photos/seed/{$seed}/600/400";
        $avgOcjena  = $s['avg_ocjena'] !== null ? (float)$s['avg_ocjena'] : null;
        $brojOcjena = (int)$s['broj_ocjena'];
        $mojaOcjena = $s['moja_ocjena'] !== null ? (int)$s['moja_ocjena'] : null;
        $roundedAvg = $avgOcjena !== null ? (int)round($avgOcjena) : 0;
    ?>
    <div class="slika-kartica">

        <a href="#lightbox-<?= $id ?>" class="slika-link"
           aria-label="Otvori sliku <?= $naziv ?> u punoj veli&#269;ini">
            <img src="<?= $thumbUrl ?>" alt="<?= $naziv ?>"
                 loading="lazy" width="600" height="400">
        </a>

        <div class="kartica-tijelo">

            <!-- Average rating — Google style -->
            <div class="avg-blok">
                <span class="avg-score <?= $avgOcjena === null ? 'avg-score--prazna' : '' ?>">
                    <?= $avgOcjena !== null ? number_format($avgOcjena, 1) : '&mdash;' ?>
                </span>
                <div class="avg-detalji">
                    <div class="avg-zvjezdice-red">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="avg-zvjezdica <?= $i <= $roundedAvg ? 'avg-puna' : 'avg-prazna' ?>">&#9733;</span>
                        <?php endfor; ?>
                    </div>
                    <span class="avg-count">
                        <?= $avgOcjena !== null
                            ? $brojOcjena . ' ' . ($brojOcjena === 1 ? 'ocjena' : 'ocjena')
                            : 'Jo&#353; bez ocjene' ?>
                    </span>
                </div>
            </div>

            <div class="ocjena-separator"></div>

            <!-- User's interactive rating -->
            <?php if ($userId): ?>
            <div class="korisnicki-red">
                <span class="ocjena-label">Va&#353;a ocjena:</span>
                <form method="POST" action="ocjeni_sliku.php" class="ocjena-forma">
                    <input type="hidden" name="id_slika" value="<?= $id ?>">
                    <input type="hidden" name="ocjena"
                           value="<?= $mojaOcjena ?? 0 ?>"
                           class="ocjena-hidden-val">
                    <div class="zvjezdice"
                         data-current="<?= $mojaOcjena ?? 0 ?>"
                         aria-label="Ocijeni sliku <?= $naziv ?>">
                        <?php for ($z = 1; $z <= 5; $z++): ?>
                            <span class="zvjezdica"
                                  data-val="<?= $z ?>"
                                  title="<?= $z ?> zvjezdic<?= $z === 1 ? 'a' : 'e' ?>"
                                  role="button"
                                  tabindex="0"
                                  aria-label="<?= $z ?> od 5">&#9733;</span>
                        <?php endfor; ?>
                    </div>
                    <button type="submit" class="ocjeni-btn">Ocijeni</button>
                </form>
            </div>
            <?php else: ?>
            <p class="prijava-napomena">
                <a href="login.php">Prijavite se</a> za ocjenjivanje
            </p>
            <?php endif; ?>

        </div>
    </div>
    <?php endforeach; ?>
    </div>

    <!-- Lightboxes -->
    <?php foreach ($slike as $s):
        $id      = (int)$s['id'];
        $seed    = htmlspecialchars($s['seed']);
        $naziv   = htmlspecialchars($s['naziv']);
        $fullUrl = "https://picsum.photos/seed/{$seed}/1200/800";
    ?>
    <div id="lightbox-<?= $id ?>" class="lightbox"
         role="dialog" aria-modal="true"
         aria-label="<?= $naziv ?> u punoj veli&#269;ini">
        <a href="#" class="lightbox-zatvori" aria-label="Zatvori">&#215;</a>
        <img src="<?= $fullUrl ?>" alt="<?= $naziv ?>" loading="lazy">
        <p class="lightbox-naslov"><?= $naziv ?></p>
    </div>
    <?php endforeach; ?>

</section>
</main>

<script>
(function () {
    /* ── Toast auto-dismiss ── */
    var toast = document.querySelector('.galerija-toast');
    if (toast) {
        setTimeout(function () {
            toast.classList.add('galerija-toast--fade-out');
            setTimeout(function () { toast.remove(); }, 380);
        }, 3200);
    }

    /* ── Star rating (span-based, no radio inputs) ──
       Stars are plain <span> elements in natural 1→5 DOM order.
       JS tracks the selected value via data-current and updates
       both the visual state (.aktivan class) and the hidden input.
       Auto-submits on click; submit button is the no-JS fallback.  */
    document.querySelectorAll('.zvjezdice').forEach(function (container) {
        var form    = container.closest('form');
        var hidden  = form.querySelector('.ocjena-hidden-val');
        var btn     = form.querySelector('.ocjeni-btn');
        var stars   = Array.from(container.querySelectorAll('.zvjezdica'));
        var current = parseInt(container.dataset.current) || 0;

        if (btn) btn.style.display = 'none';

        function paint(val) {
            stars.forEach(function (s) {
                s.classList.toggle('aktivan', parseInt(s.dataset.val) <= val);
            });
        }

        paint(current);

        stars.forEach(function (star) {
            var val = parseInt(star.dataset.val);

            star.addEventListener('mouseenter', function () { paint(val); });
            star.addEventListener('mouseleave', function () { paint(current); });

            star.addEventListener('click', function () {
                current       = val;
                hidden.value  = val;
                paint(val);
                setTimeout(function () { form.submit(); }, 130);
            });

            star.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    star.click();
                }
            });
        });
    });
})();
</script>

<?php include 'includes/footer.php'; ?>
