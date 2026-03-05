<?php
/**
 * user/changer_password_traitement.php — Traitement changement de mot de passe
 */
require_once '../includes/auth_check_user.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: changer_password.php');
    exit();
}

$pdo             = getConnexion();
$mdp_actuel      = $_POST['mdp_actuel']      ?? '';
$mdp_nouveau     = $_POST['mdp_nouveau']     ?? '';
$mdp_confirmation= $_POST['mdp_confirmation']?? '';

// Récupérer le mot de passe actuel depuis la BDD
$stmt = $pdo->prepare('SELECT password FROM utilisateurs WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$erreurs = [];

// Vérification mot de passe actuel
if (!password_verify($mdp_actuel, $user['password'])) {
    $erreurs[] = 'Le mot de passe actuel est incorrect.';
}

// Validation nouveau mot de passe
if (strlen($mdp_nouveau) < 8) {
    $erreurs[] = 'Le nouveau mot de passe doit comporter au moins 8 caractères.';
}
if ($mdp_nouveau !== $mdp_confirmation) {
    $erreurs[] = 'Le nouveau mot de passe et la confirmation ne correspondent pas.';
}

if (!empty($erreurs)) {
    $_SESSION['form_erreurs'] = $erreurs;
    header('Location: changer_password.php');
    exit();
}

// Hacher et enregistrer
$nouveau_hash = password_hash($mdp_nouveau, PASSWORD_DEFAULT);
$upd = $pdo->prepare('UPDATE utilisateurs SET password = ? WHERE id = ?');
$upd->execute([$nouveau_hash, $_SESSION['user_id']]);

$_SESSION['form_success'] = 'Votre mot de passe a été modifié avec succès.';
header('Location: changer_password.php');
exit();
