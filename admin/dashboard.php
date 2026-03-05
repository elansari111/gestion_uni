<?php
/**
 * admin/dashboard.php — Tableau de bord administrateur
 */
require_once '../includes/auth_check_admin.php';
require_once '../config/database.php';

$pdo = getConnexion();

// Statistiques globales
$stats = $pdo->query('
    SELECT
        (SELECT COUNT(*) FROM etudiants) AS nb_etudiants,
        (SELECT COUNT(*) FROM filieres)  AS nb_filieres,
        (SELECT COUNT(*) FROM documents) AS nb_documents,
        (SELECT COUNT(*) FROM etudiants WHERE Note >= 10)        AS nb_recus,
        (SELECT COUNT(*) FROM etudiants WHERE Note < 10 OR Note IS NULL) AS nb_ajornes,
        (SELECT ROUND(AVG(Note),2) FROM etudiants WHERE Note IS NOT NULL) AS moyenne_generale,
        (SELECT COUNT(*) FROM etudiants WHERE Note IS NULL)      AS nb_sans_note
')->fetch();

// Meilleur étudiant
$meilleur = $pdo->query('
    SELECT e.Nom, e.Prenom, e.Note, f.IntituleF
    FROM etudiants e
    LEFT JOIN filieres f ON e.Filiere = f.CodeF
    WHERE e.Note IS NOT NULL
    ORDER BY e.Note DESC
    LIMIT 1
')->fetch();

// Classement général
$classement = $pdo->query('
    SELECT e.Code, e.Nom, e.Prenom, e.Note, f.IntituleF,
           RANK() OVER (ORDER BY e.Note DESC) AS rang
    FROM etudiants e
    LEFT JOIN filieres f ON e.Filiere = f.CodeF
    WHERE e.Note IS NOT NULL
    ORDER BY e.Note DESC
    LIMIT 10
')->fetchAll();

// Répartition par filière
$repartition = $pdo->query('
    SELECT f.CodeF, f.IntituleF,
        COUNT(e.Code) AS total,
        SUM(CASE WHEN e.Note >= 16 THEN 1 ELSE 0 END) AS tb,
        SUM(CASE WHEN e.Note >= 14 AND e.Note < 16 THEN 1 ELSE 0 END) AS bien,
        SUM(CASE WHEN e.Note >= 12 AND e.Note < 14 THEN 1 ELSE 0 END) AS ab,
        SUM(CASE WHEN e.Note >= 10 AND e.Note < 12 THEN 1 ELSE 0 END) AS passable,
        SUM(CASE WHEN e.Note < 10 OR e.Note IS NULL THEN 1 ELSE 0 END) AS insuffisant
    FROM filieres f
    LEFT JOIN etudiants e ON e.Filiere = f.CodeF
    GROUP BY f.CodeF, f.IntituleF
    ORDER BY f.CodeF
')->fetchAll();

// Distribution des notes par tranches
$distribution = $pdo->query('
    SELECT
        SUM(CASE WHEN Note >= 0  AND Note <  5  THEN 1 ELSE 0 END) AS t0_5,
        SUM(CASE WHEN Note >= 5  AND Note < 10  THEN 1 ELSE 0 END) AS t5_10,
        SUM(CASE WHEN Note >= 10 AND Note < 15  THEN 1 ELSE 0 END) AS t10_15,
        SUM(CASE WHEN Note >= 15 AND Note <= 20 THEN 1 ELSE 0 END) AS t15_20
    FROM etudiants WHERE Note IS NOT NULL
')->fetch();

// Filière avec meilleure / moins bonne moyenne
$moy_filieres = $pdo->query('
    SELECT f.IntituleF, ROUND(AVG(e.Note),2) AS moy
    FROM filieres f
    JOIN etudiants e ON e.Filiere = f.CodeF
    WHERE e.Note IS NOT NULL
    GROUP BY f.CodeF, f.IntituleF
    ORDER BY moy DESC
')->fetchAll();

function getMention($note) {
    if ($note === null) return ['Attente','badge-attente'];
    if ($note >= 16) return ['Très Bien','badge-tb'];
    if ($note >= 14) return ['Bien','badge-bien'];
    if ($note >= 12) return ['Assez Bien','badge-ab'];
    if ($note >= 10) return ['Passable','badge-pass'];
    return ['Insuffisant','badge-insuf'];
}

$page_title = 'Tableau de bord';
$css_depth  = '../assets/';
$nav_links  = [
    ['href'=>'dashboard.php',           'label'=>'📊 Tableau de bord', 'active'=>true],
    ['href'=>'etudiants/liste.php',     'label'=>'🎓 Étudiants'],
    ['href'=>'filieres/liste.php',      'label'=>'📚 Filières'],
    ['href'=>'../logout.php',           'label'=>'⏻ Déconnexion'],
];
require_once '../includes/header.php';
?>

<!-- Statistiques rapides -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="number"><?= $stats['nb_etudiants'] ?></div>
        <div class="label">Étudiants</div>
    </div>
    <div class="stat-card pink">
        <div class="number"><?= $stats['nb_filieres'] ?></div>
        <div class="label">Filières</div>
    </div>
    <div class="stat-card green">
        <div class="number"><?= $stats['nb_recus'] ?></div>
        <div class="label">Reçus (≥10)</div>
    </div>
    <div class="stat-card red">
        <div class="number"><?= $stats['nb_ajornes'] ?></div>
        <div class="label">Ajornés / En attente</div>
    </div>
    <div class="stat-card orange">
        <div class="number"><?= $stats['moyenne_generale'] ?? 'N/A' ?></div>
        <div class="label">Moyenne générale</div>
    </div>
    <div class="stat-card">
        <div class="number"><?= $stats['nb_documents'] ?></div>
        <div class="label">Documents uploadés</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;flex-wrap:wrap;">

<!-- Infos session -->
<div class="card">
    <h2>ℹ️ Session en cours</h2>
    <p><strong>Login :</strong> <?= htmlspecialchars($_SESSION['login']) ?></p>
    <p><strong>Rôle :</strong> Administrateur</p>
    <p><strong>Connecté depuis :</strong> <?= htmlspecialchars($_SESSION['heure_connexion']) ?></p>
    <?php if ($meilleur): ?>
    <hr style="margin:12px 0;">
    <p><strong>🥇 Meilleur étudiant :</strong>
        <?= htmlspecialchars($meilleur['Prenom'] . ' ' . $meilleur['Nom']) ?>
        — <span class="note-vert"><?= $meilleur['Note'] ?>/20</span>
        (<?= htmlspecialchars($meilleur['IntituleF'] ?? '') ?>)
    </p>
    <?php endif; ?>
    <?php if (!empty($moy_filieres)): ?>
    <p style="margin-top:8px;"><strong>🏆 Meilleure filière :</strong> <?= htmlspecialchars($moy_filieres[0]['IntituleF']) ?> (moy. <?= $moy_filieres[0]['moy'] ?>)</p>
    <?php if (count($moy_filieres) > 1): ?>
    <p><strong>📉 Moins bonne :</strong> <?= htmlspecialchars(end($moy_filieres)['IntituleF']) ?> (moy. <?= end($moy_filieres)['moy'] ?>)</p>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Distribution notes -->
<div class="card">
    <h2>📊 Distribution des notes</h2>
    <?php if ($distribution): ?>
    <table>
        <thead><tr><th>Tranche</th><th>Nombre d'étudiants</th></tr></thead>
        <tbody>
            <tr><td>[0 – 5[</td><td><?= $distribution['t0_5'] ?></td></tr>
            <tr><td>[5 – 10[</td><td><?= $distribution['t5_10'] ?></td></tr>
            <tr><td>[10 – 15[</td><td><?= $distribution['t10_15'] ?></td></tr>
            <tr><td>[15 – 20]</td><td><?= $distribution['t15_20'] ?></td></tr>
            <tr><td><em>Sans note</em></td><td><?= $stats['nb_sans_note'] ?></td></tr>
        </tbody>
    </table>
    <?php else: ?>
    <p class="alert alert-info">Aucune donnée de notes disponible.</p>
    <?php endif; ?>
</div>

</div><!-- /grid -->

<!-- Classement général -->
<?php if (!empty($classement)): ?>
<div class="card" style="margin-top:20px;">
    <h2>🏅 Classement général (Top 10)</h2>
    <table>
        <thead>
            <tr><th>#</th><th>Code</th><th>Nom</th><th>Prénom</th><th>Filière</th><th>Note</th><th>Mention</th></tr>
        </thead>
        <tbody>
        <?php foreach ($classement as $e): ?>
            <?php [$mention, $badge] = getMention($e['Note']); ?>
            <tr>
                <td><strong>#<?= $e['rang'] ?></strong></td>
                <td><?= htmlspecialchars($e['Code']) ?></td>
                <td><?= htmlspecialchars($e['Nom']) ?></td>
                <td><?= htmlspecialchars($e['Prenom']) ?></td>
                <td><?= htmlspecialchars($e['IntituleF'] ?? '—') ?></td>
                <td><span class="<?= $e['Note'] >= 12 ? 'note-vert' : ($e['Note'] >= 10 ? 'note-orange' : 'note-rouge') ?>"><?= $e['Note'] ?>/20</span></td>
                <td><span class="badge <?= $badge ?>"><?= $mention ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Répartition par filière -->
<?php if (!empty($repartition)): ?>
<div class="card" style="margin-top:20px;">
    <h2>📚 Répartition des mentions par filière</h2>
    <table>
        <thead>
            <tr><th>Filière</th><th>Total</th><th>Très Bien</th><th>Bien</th><th>Assez Bien</th><th>Passable</th><th>Non validé</th></tr>
        </thead>
        <tbody>
        <?php foreach ($repartition as $r): ?>
            <tr>
                <td><strong><?= htmlspecialchars($r['IntituleF']) ?></strong></td>
                <td><?= $r['total'] ?></td>
                <td><?= $r['tb'] ?></td>
                <td><?= $r['bien'] ?></td>
                <td><?= $r['ab'] ?></td>
                <td><?= $r['passable'] ?></td>
                <td><?= $r['insuffisant'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
