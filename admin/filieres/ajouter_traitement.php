<?php
/**
 * admin/filieres/ajouter_traitement.php — Traitement ajout filière
 */
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ajouter.php');
    exit();
}

$pdo        = getConnexion();
$codeF      = strtoupper(trim($_POST['CodeF']      ?? ''));
$intituleF  = trim($_POST['IntituleF']  ?? '');
$responsable= trim($_POST['responsable'] ?? '') ?: null;
$nbPlaces   = trim($_POST['nbPlaces']   ?? '') ?: null;

$erreurs = [];
if ($codeF     === '') $erreurs[] = 'Le code filière est obligatoire.';
if ($intituleF === '') $erreurs[] = 'L\'intitulé est obligatoire.';

if ($codeF !== '') {
    $chk = $pdo->prepare('SELECT CodeF FROM filieres WHERE CodeF = ?');
    $chk->execute([$codeF]);
    if ($chk->fetch()) $erreurs[] = "Le code «$codeF» existe déjà.";
}

if (!empty($erreurs)) {
    $_SESSION['form_erreurs'] = $erreurs;
    $_SESSION['form_values']  = $_POST;
    header('Location: ajouter.php');
    exit();
}

$stmt = $pdo->prepare('INSERT INTO filieres (CodeF, IntituleF, responsable, nbPlaces, created_at) VALUES (?,?,?,?,NOW())');
$stmt->execute([$codeF, $intituleF, $responsable, $nbPlaces]);

header('Location: liste.php?msg=ajoute');
exit();
