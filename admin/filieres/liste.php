<?php
/**
 * admin/filieres/liste.php — Liste des filières
 */
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';

$pdo = getConnexion();
$msg = $_GET['msg'] ?? '';

$filieres = $pdo->query('
    SELECT f.*, COUNT(e.Code) AS nb_etudiants
    FROM filieres f
    LEFT JOIN etudiants e ON e.Filiere = f.CodeF
    GROUP BY f.CodeF, f.IntituleF, f.responsable, f.nbPlaces, f.created_at
    ORDER BY f.IntituleF
')->fetchAll();

$page_title = 'Filières';
$css_depth  = '../../assets/';
$nav_links  = [
    ['href'=>'../dashboard.php',     'label'=>'📊 Tableau de bord'],
    ['href'=>'../etudiants/liste.php','label'=>'🎓 Étudiants'],
    ['href'=>'liste.php',             'label'=>'📚 Filières', 'active'=>true],
    ['href'=>'../../logout.php',      'label'=>'⏻ Déconnexion'],
];
require_once '../../includes/header.php';
?>

<?php if ($msg === 'ajoute'): ?>
<div class="alert alert-success">✅ Filière ajoutée avec succès.</div>
<?php endif; ?>

<div class="card">
    <h2>📚 Filières
        <a href="ajouter.php" class="btn btn-pink btn-sm" style="float:right;">+ Ajouter</a>
    </h2>
    <table>
        <thead>
            <tr><th>Code</th><th>Intitulé</th><th>Responsable</th><th>Nb places</th><th>Étudiants inscrits</th><th>Créée le</th></tr>
        </thead>
        <tbody>
        <?php if (empty($filieres)): ?>
            <tr><td colspan="6" style="text-align:center;padding:20px;color:#888;">Aucune filière.</td></tr>
        <?php else: ?>
        <?php foreach ($filieres as $f): ?>
            <tr>
                <td><strong><?= htmlspecialchars($f['CodeF']) ?></strong></td>
                <td><?= htmlspecialchars($f['IntituleF']) ?></td>
                <td><?= htmlspecialchars($f['responsable'] ?? '—') ?></td>
                <td><?= $f['nbPlaces'] ?? '—' ?></td>
                <td>
                    <span style="background:#e8ecff;color:#294898;padding:3px 10px;border-radius:20px;font-size:.85rem;font-weight:600;">
                        <?= $f['nb_etudiants'] ?>
                    </span>
                </td>
                <td><?= date('d/m/Y', strtotime($f['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../../includes/footer.php'; ?>
