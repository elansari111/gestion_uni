<?php
/**
 * includes/auth_check_user.php
 * Vérifie que l'utilisateur connecté est un étudiant (rôle user).
 */
session_start();

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../login.php?erreur=acces');
    exit();
}
