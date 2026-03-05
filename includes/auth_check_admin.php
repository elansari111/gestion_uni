<?php
/**
 * includes/auth_check_admin.php
 * Vérifie que l'utilisateur connecté est un administrateur.
 */
session_start();

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php?erreur=acces');
    exit();
}
