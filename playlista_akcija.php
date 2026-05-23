<?php
require_once 'config/session.php';
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$akcija     = $_POST['akcija'] ?? '';
$korisnikId = (int)$_SESSION['korisnik_id'];
$pdo        = getPDO();

switch ($akcija) {

    case 'dodaj':
        $pjesmaId = (int)($_POST['pjesma_id'] ?? 0);
        if ($pjesmaId <= 0) {
            header('Location: index.php');
            exit;
        }
        $stmt = $pdo->prepare(
            'SELECT id FROM playlista WHERE korisnik_id = ? AND pjesma_id = ?'
        );
        $stmt->execute([$korisnikId, $pjesmaId]);
        if ($stmt->fetch()) {
            $_SESSION['flash'] = ['tekst' => '&#9888; Pjesma je ve&#263; u tvojoj playlisti!'];
        } else {
            $pdo->prepare(
                'INSERT INTO playlista (korisnik_id, pjesma_id) VALUES (?, ?)'
            )->execute([$korisnikId, $pjesmaId]);
        }
        header('Location: index.php');
        exit;

    case 'ukloni':
        $stavkaId = (int)($_POST['stavka_id'] ?? 0);
        if ($stavkaId > 0) {
            $pdo->prepare(
                'DELETE FROM playlista WHERE id = ? AND korisnik_id = ?'
            )->execute([$stavkaId, $korisnikId]);
        }
        header('Location: playlista.php');
        exit;

    case 'ocisti':
        $pdo->prepare(
            'DELETE FROM playlista WHERE korisnik_id = ?'
        )->execute([$korisnikId]);
        header('Location: playlista.php');
        exit;
}

header('Location: index.php');
exit;
