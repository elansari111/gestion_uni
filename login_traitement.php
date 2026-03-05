<?php
/**
 * login_traitement.php — Traitement du formulaire de connexion
 */
session_start();
require_once 'config/database.php';

// 1. Vérifier méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

// 2. Récupérer et nettoyer les champs
$login    = trim($_POST['login']    ?? '');
$password = trim($_POST['password'] ?? '');

// 3. Vérifier que les champs ne sont pas vides
if ($login === '' || $password === '') {
    header('Location: login.php?erreur=1');
    exit();
}

// 4. Requête préparée PDO
$pdo  = getConnexion();
$stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE login = ? LIMIT 1');
$stmt->execute([$login]);
$utilisateur = $stmt->fetch();

// 5. Vérifier le mot de passe
if (!$utilisateur || !password_verify($password, $utilisateur['password'])) {
    header('Location: login.php?erreur=1');
    exit();
}

// 6. Alimenter $_SESSION
$_SESSION['user_id']      = $utilisateur['id'];
$_SESSION['login']        = $utilisateur['login'];
$_SESSION['role']         = $utilisateur['role'];
$_SESSION['etudiant_id']  = $utilisateur['etudiant_id'];
$_SESSION['heure_connexion'] = date('d/m/Y H:i:s');

// 7. Cookie last_login (30 jours)
setcookie('last_login', $utilisateur['login'], time() + 30 * 24 * 3600, '/');

// 8. Mettre à jour derniere_connexion
$upd = $pdo->prepare('UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?');
$upd->execute([$utilisateur['id']]);

// 9. Redirection selon le rôle
if ($utilisateur['role'] === 'admin') {
    header('Location: admin/dashboard.php');
} else {
    header('Location: user/profil.php');
}
exit();
