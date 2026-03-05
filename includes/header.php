<?php
/**
 * includes/header.php
 * En-tête commun — style signature UPF
 */
$page_title = $page_title ?? 'Gestion UPF';
$nav_links  = $nav_links  ?? [];
$css_depth  = $css_depth  ?? 'assets/';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> — Gestion UPF</title>
    <link rel="stylesheet" href="<?= $css_depth ?>style.css">
</head>
<body>

<header>
    <div class="header-inner">
        <div class="logo">
            <div class="logo-mark">UPF</div>
            <div>
                <h1>Université Privée de Fès</h1>
                <span>Application de Gestion des Étudiants</span>
            </div>
        </div>
        <?php if (!empty($_SESSION['user_id'])): ?>
        <div class="header-user">
            <span>Bonjour, <strong><?= htmlspecialchars($_SESSION['login']) ?></strong></span>
            <span class="user-badge">
                <?= $_SESSION['role'] === 'admin' ? 'Administrateur' : 'Étudiant' ?>
            </span>
            <a href="<?= $css_depth === 'assets/' ? '' : '../' ?>logout.php">⏻ Déconnexion</a>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($nav_links)): ?>
    <nav>
        <?php foreach ($nav_links as $nl): ?>
        <a href="<?= htmlspecialchars($nl['href']) ?>"
           class="<?= !empty($nl['active']) ? 'active' : '' ?>">
            <?= htmlspecialchars($nl['label']) ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <?php endif; ?>
</header>

<div class="container">
