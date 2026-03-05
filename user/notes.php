<?php
/**
 * user/notes.php — Notes, mention et classement de l'étudiant
 */
require_once '../includes/auth_check_user.php';
require_once '../config/database.php';

$pdo = getConnexion();

// Récupérer l'étudiant
$stmt = $pdo->prepare('
    SELECT e.*, f.IntituleF FROM etudiants e
    LEFT JOIN filieres f ON e.Filiere = f.CodeF
    WHERE e.Code = ?
');
$stmt->execute([$_SESSION['etudiant_id']]);
$etudiant = $stmt->fetch();
if (!$etudiant) { header('Location: ../logout.php'); exit(); }

// Classement dans sa filière
$rang = null;
$moy_filiere = null;
if ($etudiant['Filiere'] && $etudiant['Note'] !== null) {
    // Rang
    $stmtRang = $pdo->prepare('
        SELECT COUNT(*) + 1 AS rang
        FROM etudiants
        WHERE Filiere = ? AND Note > ? AND Note IS NOT NULL
    ');
    $stmtRang->execute([$etudiant['Filiere'], $etudiant['Note']]);
    $rang = $stmtRang->fetchColumn();

    // Moyenne filière
    $stmtMoy = $pdo->prepare('SELECT ROUND(AVG(Note),2) AS moy FROM etudiants WHERE Filiere = ? AND Note IS NOT NULL');
    $stmtMoy->execute([$etudiant['Filiere']]);
    $moy_filiere = $stmtMoy->fetchColumn();
}

function getMention($note) {
    if ($note === null) return ['En attente d\'évaluation', 'badge-attente', '⏳'];
    if ($note >= 16) return ['Très Bien',  'badge-tb',    '🌟'];
    if ($note >= 14) return ['Bien',       'badge-bien',  '👍'];
    if ($note >= 12) return ['Assez Bien', 'badge-ab',    '✔️'];
    if ($note >= 10) return ['Passable',   'badge-pass',  '📋'];
    return ['Insuffisant', 'badge-insuf', '❌'];
}
[$mention, $badge, $emoji] = getMention($etudiant['Note']);
$statut = $etudiant['Note'] === null ? 'En attente' : ($etudiant['Note'] >= 10 ? '✅ Reçu' : '❌ Ajorné');

$page_title = 'Mes Notes';
$css_depth  = '../assets/';
$nav_links  = [
    ['href'=>'profil.php',           'label'=>'👤 Mon Profil'],
    ['href'=>'notes.php',            'label'=>'📊 Mes Notes', 'active'=>true],
    ['href'=>'documents.php',        'label'=>'📄 Mes Documents'],
    ['href'=>'changer_password.php', 'label'=>'🔑 Mot de passe'],
    ['href'=>'../logout.php',        'label'=>'⏻ Déconnexion'],
];
require_once '../includes/header.php';
?>

<div class="card" style="max-width:680px;margin:0 auto;">
    <h2>📊 Mes Notes et Performances</h2>

    <!-- Note principale -->
    <div style="text-align:center;padding:32px 0;border-bottom:1px solid #eee;margin-bottom:24px;">
        <div style="font-size:4rem;font-weight:800;
            color:<?= $etudiant['Note'] === null ? '#aaa' : ($etudiant['Note'] >= 12 ? '#28a745' : ($etudiant['Note'] >= 10 ? '#fd7e14' : '#dc3545')) ?>">
            <?= $etudiant['Note'] !== null ? $etudiant['Note'] . '/20' : '—' ?>
        </div>
        <div style="margin-top:10px;">
            <span class="badge <?= $badge ?>" style="font-size:1rem;padding:6px 20px;">
                <?= $emoji ?> <?= $mention ?>
            </span>
        </div>
        <div style="margin-top:10px;font-size:1.1rem;"><?= $statut ?></div>
    </div>

    <!-- Détails -->
    <table style="width:100%;font-size:.93rem;">
        <tr style="border-bottom:1px solid #f0f0f0;">
            <td style="color:#888;padding:10px 0;">Étudiant</td>
            <td><strong><?= htmlspecialchars($etudiant['Prenom'] . ' ' . $etudiant['Nom']) ?></strong></td>
        </tr>
        <tr style="border-bottom:1px solid #f0f0f0;">
            <td style="color:#888;padding:10px 0;">Filière</td>
            <td><?= htmlspecialchars($etudiant['IntituleF'] ?? '—') ?></td>
        </tr>
        <tr style="border-bottom:1px solid #f0f0f0;">
            <td style="color:#888;padding:10px 0;">Note obtenue</td>
            <td>
                <?php if ($etudiant['Note'] !== null): ?>
                    <?= $etudiant['Note'] ?> / 20
                <?php else: ?>
                    <span style="color:#aaa;">Non encore évaluée</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr style="border-bottom:1px solid #f0f0f0;">
            <td style="color:#888;padding:10px 0;">Mention</td>
            <td><span class="badge <?= $badge ?>"><?= $mention ?></span></td>
        </tr>
        <tr style="border-bottom:1px solid #f0f0f0;">
            <td style="color:#888;padding:10px 0;">Statut</td>
            <td><?= $statut ?></td>
        </tr>
        <?php if ($rang !== null): ?>
        <tr style="border-bottom:1px solid #f0f0f0;">
            <td style="color:#888;padding:10px 0;">Position dans la filière</td>
            <td><strong style="color:#294898;">#<?= $rang ?></strong></td>
        </tr>
        <?php endif; ?>
        <?php if ($moy_filiere !== null): ?>
        <tr>
            <td style="color:#888;padding:10px 0;">Moyenne de la filière</td>
            <td><?= $moy_filiere ?> / 20
                <?php if ($etudiant['Note'] !== null): ?>
                    <?php if ($etudiant['Note'] > $moy_filiere): ?>
                        <span style="color:#28a745;font-size:.85rem;">▲ au-dessus de la moyenne</span>
                    <?php elseif ($etudiant['Note'] < $moy_filiere): ?>
                        <span style="color:#dc3545;font-size:.85rem;">▼ en dessous de la moyenne</span>
                    <?php else: ?>
                        <span style="color:#fd7e14;font-size:.85rem;">= dans la moyenne</span>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>
        <?php endif; ?>
    </table>

    <div style="margin-top:20px;text-align:center;">
        <a href="profil.php" class="btn btn-outline">← Retour au profil</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
