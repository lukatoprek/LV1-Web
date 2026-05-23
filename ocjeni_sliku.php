<?php
require_once 'config/session.php';
require_once 'config/db.php';

if (!isset($_SESSION['korisnik_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: slike.php');
    exit;
}

$idSlika = isset($_POST['id_slika']) ? (int)$_POST['id_slika'] : 0;
$ocjena  = isset($_POST['ocjena'])   ? (int)$_POST['ocjena']   : 0;

if ($idSlika < 1 || $ocjena < 1 || $ocjena > 5) {
    $_SESSION['flash'] = ['tekst' => 'Neispravna ocjena.', 'tip' => 'greska'];
    header('Location: slike.php');
    exit;
}

$pdo = getPDO();

$stmt = $pdo->prepare('SELECT id FROM slike WHERE id = ?');
$stmt->execute([$idSlika]);
if (!$stmt->fetch()) {
    $_SESSION['flash'] = ['tekst' => 'Slika ne postoji.', 'tip' => 'greska'];
    header('Location: slike.php');
    exit;
}

$pdo->prepare(
    'INSERT INTO ocjene (id_korisnik, id_slika, ocjena)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE ocjena = VALUES(ocjena), vrijeme_ocjene = NOW()'
)->execute([$_SESSION['korisnik_id'], $idSlika, $ocjena]);

$_SESSION['flash'] = ['tekst' => 'Ocjena uspješno spremljena!', 'tip' => 'uspjeh'];
header('Location: slike.php');
exit;
