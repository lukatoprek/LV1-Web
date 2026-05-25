<?php
require_once 'config/session.php';
require_once 'config/db.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pdo = getPDO();

// --- Filter inputs ---
$filterAutor        = trim($_GET['autor'] ?? '');
$filterZanr         = trim($_GET['zanr'] ?? '');
$filterBpmMin       = isset($_GET['bpm_min'])   && $_GET['bpm_min']   !== '' ? (int)$_GET['bpm_min']   : 0;
$filterBpmMax       = isset($_GET['bpm_max'])   && $_GET['bpm_max']   !== '' ? (int)$_GET['bpm_max']   : 0;
$filterGodinaOd     = isset($_GET['godina_od']) && $_GET['godina_od'] !== '' ? (int)$_GET['godina_od'] : 0;
$filterGodinaDo     = isset($_GET['godina_do']) && $_GET['godina_do'] !== '' ? (int)$_GET['godina_do'] : 0;
$filterRaspolozenja = array_filter((array)($_GET['raspolozenje'] ?? []));

// --- Build WHERE clause ---
$where  = [];
$params = [];

if ($filterAutor !== '') {
    $where[]  = 'autor LIKE ?';
    $params[] = '%' . $filterAutor . '%';
}
if ($filterZanr !== '') {
    $where[]  = 'zanr = ?';
    $params[] = $filterZanr;
}
if ($filterBpmMin > 0) {
    $where[]  = 'bpm >= ?';
    $params[] = $filterBpmMin;
}
if ($filterBpmMax > 0) {
    $where[]  = 'bpm <= ?';
    $params[] = $filterBpmMax;
}
if ($filterGodinaOd > 0) {
    $where[]  = 'godina >= ?';
    $params[] = $filterGodinaOd;
}
if ($filterGodinaDo > 0) {
    $where[]  = 'godina <= ?';
    $params[] = $filterGodinaDo;
}
if (!empty($filterRaspolozenja)) {
    $phs     = implode(',', array_fill(0, count($filterRaspolozenja), '?'));
    $where[] = "raspolozenje IN ($phs)";
    $params  = array_merge($params, array_values($filterRaspolozenja));
}

$sql = 'SELECT * FROM pjesme';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY naslov ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pjesme = $stmt->fetchAll();

$ukupno       = (int)$pdo->query('SELECT COUNT(*) FROM pjesme')->fetchColumn();
$zanrovi      = $pdo->query('SELECT DISTINCT zanr FROM pjesme ORDER BY zanr')->fetchAll(PDO::FETCH_COLUMN);
$raspolozenja = $pdo->query('SELECT DISTINCT raspolozenje FROM pjesme ORDER BY raspolozenje')->fetchAll(PDO::FETCH_COLUMN);
$godinaMeta   = $pdo->query('SELECT MIN(godina) as min_g, MAX(godina) as max_g FROM pjesme')->fetch();

// Songs already in user's playlist (for button state)
$stmt2 = $pdo->prepare('SELECT pjesma_id FROM playlista WHERE korisnik_id = ?');
$stmt2->execute([$_SESSION['korisnik_id']]);
$uPlaylisti = array_column($stmt2->fetchAll(), 'pjesma_id');

$pageTitle  = 'Glazba - Popis Pjesama';
$activePage = 'index';
include 'includes/header.php';
?>

<main role="main" class="main-layout">

    <section class="content" aria-labelledby="tablica-naslov">
        <h1 id="tablica-naslov">Popis Pjesama</h1>

        <!-- Filter form -->
        <div class="filteri-wrapper" aria-label="Filtriranje pjesama" role="search">
            <form method="GET" action="index.php" id="filter-form">
                <div class="filteri-naslov">
                    <span class="filteri-ikona">&#9878;</span>
                    <h2>Filtriraj pjesme</h2>
                    <a href="index.php" class="reset-btn" style="text-decoration:none;">&#10006; Resetiraj</a>
                </div>

                <div class="filteri-redak">

                    <!-- Izvo&#273;a&#269; -->
                    <div class="filter-grupa">
                        <label for="filter-autor" class="filter-label">
                            <span class="filter-ikona">&#127908;</span> Izvo&#273;a&#269;
                        </label>
                        <input type="text" id="filter-autor" name="autor" class="filter-select"
                               placeholder="Pretra&#382;i izvo&#273;a&#269;a..."
                               value="<?= htmlspecialchars($filterAutor) ?>">
                    </div>

                    <!-- &#381;anr -->
                    <div class="filter-grupa">
                        <label for="filter-zanr" class="filter-label">
                            <span class="filter-ikona">&#127925;</span> &#381;anr
                        </label>
                        <select id="filter-zanr" name="zanr" class="filter-select">
                            <option value="">Svi &#382;anrovi</option>
                            <?php foreach ($zanrovi as $z): ?>
                                <option value="<?= htmlspecialchars($z) ?>"
                                    <?= $filterZanr === $z ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($z) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- BPM -->
                    <div class="filter-grupa">
                        <label class="filter-label">
                            <span class="filter-ikona">&#9835;</span> Tempo (BPM)
                        </label>
                        <div class="bpm-inputs">
                            <input type="number" name="bpm_min" id="filter-bpm-min" class="filter-number"
                                   placeholder="Min" min="0" max="300"
                                   value="<?= $filterBpmMin > 0 ? $filterBpmMin : '' ?>">
                            <span class="bpm-separator">&#8212;</span>
                            <input type="number" name="bpm_max" id="filter-bpm-max" class="filter-number"
                                   placeholder="Max" min="0" max="300"
                                   value="<?= $filterBpmMax > 0 ? $filterBpmMax : '' ?>">
                        </div>
                    </div>

                    <!-- Godina range -->
                    <div class="filter-grupa filter-godina-grupa">
                        <label class="filter-label">
                            <span class="filter-ikona">&#128197;</span> Godina:
                            <span class="godina-prikaz">
                                <span id="godina-od-val"><?= ($filterGodinaOd > 0 ? $filterGodinaOd : $godinaMeta['min_g']) ?>.</span>
                                &#8212;
                                <span id="godina-do-val"><?= ($filterGodinaDo > 0 ? $filterGodinaDo : $godinaMeta['max_g']) ?>.</span>
                            </span>
                        </label>
                        <div class="range-wrapper">
                            <input type="range" name="godina_od" id="filter-godina-od"
                                   class="filter-range range-od"
                                   min="<?= $godinaMeta['min_g'] ?>" max="<?= $godinaMeta['max_g'] ?>"
                                   value="<?= $filterGodinaOd > 0 ? $filterGodinaOd : $godinaMeta['min_g'] ?>" step="1">
                            <input type="range" name="godina_do" id="filter-godina-do"
                                   class="filter-range range-do"
                                   min="<?= $godinaMeta['min_g'] ?>" max="<?= $godinaMeta['max_g'] ?>"
                                   value="<?= $filterGodinaDo > 0 ? $filterGodinaDo : $godinaMeta['max_g'] ?>" step="1">
                            <div class="range-track"><div class="range-fill" id="range-fill"></div></div>
                        </div>
                        <div class="range-labele" aria-hidden="true">
                            <span><?= $godinaMeta['min_g'] ?>.</span>
                            <span><?= $godinaMeta['max_g'] ?>.</span>
                        </div>
                    </div>

                    <!-- Raspolo&#382;enje -->
                    <div class="filter-grupa filter-raspolozenje-grupa">
                        <label class="filter-label">
                            <span class="filter-ikona">&#128149;</span> Raspolo&#382;enje
                        </label>
                        <div class="raspolozenje-tagovi" role="group" aria-label="Odaberi raspolo&#382;enje">
                            <?php foreach ($raspolozenja as $r): ?>
                                <?php $aktivan = in_array($r, (array)$filterRaspolozenja); ?>
                                <label class="raspolozenje-tag <?= $aktivan ? 'aktivan' : '' ?>">
                                    <input type="checkbox" name="raspolozenje[]"
                                           value="<?= htmlspecialchars($r) ?>"
                                           <?= $aktivan ? 'checked' : '' ?>
                                           >
                                    <span><?= htmlspecialchars($r) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>

                <div class="filteri-status">
                    <span class="rezultati-broj <?= count($pjesme) < $ukupno ? 'filtrirano' : '' ?>">
                        <?= count($pjesme) < $ukupno
                            ? count($pjesme) . ' od ' . $ukupno . ' pjesama'
                            : 'Prikazano ' . $ukupno . ' pjesama' ?>
                    </span>
                    <button type="submit" style="display:inline;width:auto;padding:5px 14px;font-size:0.82rem;border-radius:20px;margin-left:10px;">
                        Pretra&#382;i
                    </button>
                </div>
            </form>
        </div>

        <?php if ($flash): ?>
            <div class="playlist-poruka" style="margin-bottom:16px;">
                <?= htmlspecialchars($flash['tekst']) ?>
            </div>
        <?php endif; ?>

        <div style="margin-bottom:16px;">
            <a href="dodaj_pjesmu.php" class="gallery-btn">+ Dodaj novu pjesmu</a>
        </div>

        <div class="table-wrapper">
            <table id="glazba-tablica" aria-label="Tablica s popisom glazbenih pjesama">
                <thead>
                    <tr>
                        <th scope="col">Pjesma</th>
                        <th scope="col">Izvo&#273;a&#269;</th>
                        <th scope="col">&#381;anr</th>
                        <th scope="col">Godina</th>
                        <th scope="col">BPM</th>
                        <th scope="col">Raspolo&#382;enje</th>
                        <th scope="col" aria-label="Dodaj u playlistu"></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($pjesme)): ?>
                    <tr>
                        <td colspan="7" class="nema-rezultata">&#128265; Nema pjesama koje odgovaraju odabranim filterima.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pjesme as $p): ?>
                        <?php $dodano = in_array($p['id'], $uPlaylisti); ?>
                        <tr>
                            <td><?= htmlspecialchars($p['naslov']) ?></td>
                            <td><?= htmlspecialchars($p['autor']) ?></td>
                            <td><?= htmlspecialchars($p['zanr']) ?></td>
                            <td><?= (int)$p['godina'] ?></td>
                            <td><?= (int)$p['bpm'] ?></td>
                            <td><?= htmlspecialchars($p['raspolozenje']) ?></td>
                            <td>
                                <?php if ($dodano): ?>
                                    <button class="dodaj-btn dodano" disabled aria-disabled="true">
                                        &#10003; Dodano
                                    </button>
                                <?php else: ?>
                                    <form method="POST" action="playlista_akcija.php">
                                        <input type="hidden" name="akcija"    value="dodaj">
                                        <input type="hidden" name="pjesma_id" value="<?= (int)$p['id'] ?>">
                                        <button type="submit" class="dodaj-btn"
                                                aria-label="Dodaj <?= htmlspecialchars($p['naslov']) ?> u playlistu">
                                            + Dodaj
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <aside aria-label="Galerija slika" class="aside-gallery" role="complementary">
        <h2>Galerija slika</h2>
        <picture>
            <source media="(min-width: 768px)" srcset="https://picsum.photos/seed/guitar-stage/400/300" width="400" height="300">
            <img src="https://picsum.photos/seed/guitar-stage/600/200" alt="Glazbeni instrument"
                 width="600" height="200" loading="lazy" class="aside-img">
        </picture>
        <picture>
            <source media="(min-width: 768px)" srcset="https://picsum.photos/seed/rock-concert/400/300" width="400" height="300">
            <img src="https://picsum.photos/seed/rock-concert/600/200" alt="Koncert"
                 width="600" height="200" loading="lazy" class="aside-img">
        </picture>
        <a href="slike.php" class="gallery-btn" role="button">Pogledaj sve slike &#8594;</a>
    </aside>

</main>

<section class="content info-section" aria-labelledby="info-naslov" role="region">
    <h2 id="info-naslov">O stranici</h2>
    <p>HTML5 omogu&#263;ava semanti&#269;ku strukturu koja pobolj&#353;ava pristupa&#269;nost i SEO. Ova stranica prikazuje popis glazbenih pjesama iz raznih &#382;anrova.</p>
</section>

<article class="content" aria-labelledby="vijesti-naslov" role="article">
    <h2 id="vijesti-naslov">Zanimljivosti o glazbi</h2>
    <p>Glazba je jedna od najstarijih umjetnosti. Istra&#382;ivanja pokazuju da slu&#353;anje glazbe pozitivno utje&#269;e na raspolo&#382;enje, produktivnost i kreativnost.</p>
</article>

<script>
(function () {
    var rangeOd = document.getElementById('filter-godina-od');
    var rangeDo = document.getElementById('filter-godina-do');
    var valOd   = document.getElementById('godina-od-val');
    var valDo   = document.getElementById('godina-do-val');
    var fill    = document.getElementById('range-fill');
    var form    = document.getElementById('filter-form');

    function updateFill() {
        var min = parseFloat(rangeOd.min);
        var max = parseFloat(rangeOd.max);
        var od  = parseFloat(rangeOd.value);
        var doo = parseFloat(rangeDo.value);
        fill.style.left  = ((od  - min) / (max - min) * 100) + '%';
        fill.style.width = ((doo - od)  / (max - min) * 100) + '%';
        valOd.textContent = od  + '.';
        valDo.textContent = doo + '.';
    }

    rangeOd.addEventListener('input', function () {
        if (parseFloat(this.value) > parseFloat(rangeDo.value)) this.value = rangeDo.value;
        updateFill();
    });
    rangeDo.addEventListener('input', function () {
        if (parseFloat(this.value) < parseFloat(rangeOd.value)) this.value = rangeOd.value;
        updateFill();
    });
    updateFill();

    document.querySelectorAll('.raspolozenje-tagovi input[type="checkbox"]').forEach(function (cb) {
        cb.addEventListener('change', function () {
            this.closest('label').classList.toggle('aktivan', this.checked);
        });
    });
})();
</script>

<?php include 'includes/footer.php'; ?>
