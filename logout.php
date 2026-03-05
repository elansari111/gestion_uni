<?php
/**
 * logout.php — Déconnexion et destruction de session
 */
session_start();
session_unset();
session_destroy();

// Supprimer le cookie last_login (date expirée dans le passé)
setcookie('last_login', '', time() - 3600, '/');

header('Location: login.php?msg=deconnecte');
exit();
