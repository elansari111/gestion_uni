<?php
/**
 * admin/etudiants/liste.php — Liste des étudiants avec recherche, filtre et pagination
 */
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';

$pdo = getConnexion();

$par_page  = 5;
$page      = max(1, (int)($_GET['page'] ?? 1));
$offset    = ($page - 1) * $par_page;
$recherche = trim($_GET['recherche'] ?? '');
$filtre_f  = trim($_GET['filiere']   ?? '');

// Construire la requête de base
$where  = 'WHERE 1=1';
$params = [];
if ($recherche !== '') {
    $where .= ' AND (e.Nom LIKE ? OR e.Prenom LIKE ? OR e.Code LIKE ?)';
    $params[] = "%$recherche%";
    $params[] = "%$recherche%";
    $params[] = "%$recherche%";
}
if ($filtre_f !== '') {
    $where .= ' AND e.Filiere = ?';
    $params[] = $filtre_f;
}

// Compter le total
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM etudiants e $where");
$stmtCount->execute($params);
$total     = $stmtCount->fetchColumn();
$nb_pages  = (int)ceil($total / $par_page);

// Récupérer les étudiants de la page
$sql = "SELECT e.*, f.IntituleF FROM etudiants e
        LEFT JOIN filieres f ON e.Filiere = f.CodeF
        $where ORDER BY e.Nom, e.Prenom
        LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql);
$i = 1;
foreach ($params as $p) { $stmt->bindValue($i++, $p); }
$stmt->bindValue($i++, $par_page, PDO::PARAM_INT);
$stmt->bindValue($i,   $offset,   PDO::PARAM_INT);
$stmt->execute();
$etudiants = $stmt->fetchAll();

// Liste des filières pour le filtre
$filieres = $pdo->query('SELECT CodeF, IntituleF FROM filieres ORDER BY IntituleF')->fetchAll();

// Message de succès/erreur
$msg = $_GET['msg'] ?? '';

function getMention($note) {
    if ($note === null) return ['En attente','badge-attente'];
    if ($note >= 16) return ['Très Bien','badge-tb'];
    if ($note >= 14) return ['Bien','badge-bien'];
    if ($note >= 12) return ['Assez Bien','badge-ab'];
    if ($note >= 10) return ['Passable','badge-pass'];
    return ['Insuffisant','badge-insuf'];
}

$page_title = 'Liste des étudiants';
$css_depth  = '../../assets/';
$nav_links  = [
    ['href'=>'../dashboard.php',        'label'=>'📊 Tableau de bord'],
    ['href'=>'liste.php',               'label'=>'🎓 Étudiants', 'active'=>true],
    ['href'=>'../filieres/liste.php',   'label'=>'📚 Filières'],
    ['href'=>'../../logout.php',        'label'=>'⏻ Déconnexion'],
];
require_once '../../includes/header.php';
?>

<?php if ($msg === 'supprime'): ?>
<div class="alert alert-success">✅ Étudiant supprimé avec succès.</div>
<?php elseif ($msg === 'ajoute'): ?>
<div class="alert alert-success">✅ Étudiant ajouté avec succès.</div>
<?php elseif ($msg === 'modifie'): ?>
<div class="alert alert-success">✅ Étudiant modifié avec succès.</div>
<?php endif; ?>

<div class="card">
    <h2>🎓 Liste des Étudiants
        <a href="ajouter.php" class="btn btn-pink btn-sm" style="float:right;">+ Ajouter</a>
    </h2>

    <!-- Barre de recherche / filtre -->
    <form method="GET" action="liste.php">
        <div class="search-bar">
            <input type="text" name="recherche" class="form-control"
                   placeholder="Recherche par nom, prénom ou code…"
                   value="<?= htmlspecialchars($recherche) ?>">
            <select name="filiere" class="form-control">
                <option value="">— Toutes les filières —</option>
                <?php foreach ($filieres as $f): ?>
                <option value="<?= htmlspecialchars($f['CodeF']) ?>"
                    <?= $filtre_f === $f['CodeF'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['IntituleF']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">🔍 Filtrer</button>
            <a href="liste.php" class="btn btn-outline">✕ Réinitialiser</a>
        </div>
    </form>

    <p style="color:#666;font-size:.88rem;margin-bottom:12px;">
        <?= $total ?> étudiant(s) trouvé(s)
    </p>

    <table>
        <thead>
            <tr>
                <th>Photo</th><th>Code</th><th>Nom</th><th>Prénom</th>
                <th>Filière</th><th>Note/20</th><th>Mention</th><th>Statut</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($etudiants)): ?>
            <tr><td colspan="9" style="text-align:center;padding:20px;color:#888;">Aucun étudiant trouvé.</td></tr>
        <?php else: ?>
        <?php foreach ($etudiants as $e):
            [$mention, $badge] = getMention($e['Note']);
            $noteClass = $e['Note'] === null ? '' : ($e['Note'] >= 12 ? 'note-vert' : ($e['Note'] >= 10 ? 'note-orange' : 'note-rouge'));
            $statut    = $e['Note'] === null ? 'En attente' : ($e['Note'] >= 10 ? '✅ Reçu' : '❌ Ajorné');
            $photo     = $e['Photo'] && file_exists('../../uploads/photos/' . $e['Photo'])
                         ? '../../uploads/photos/' . htmlspecialchars($e['Photo'])
                         : '../../assets/no-photo.png';
        ?>
            <tr>
                <td>
                    <?php if ($e['Photo'] && file_exists('../../uploads/photos/' . $e['Photo'])): ?>
                        <img src="../../uploads/photos/<?= htmlspecialchars($e['Photo']) ?>" class="photo-small" alt="Photo">
                    <?php else: ?>
                        <div style="width:42px;height:42px;border-radius:50%;background:#e0e0e0;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">👤</div>
                    <?php endif; ?>
                </td>
                <td><strong><?= htmlspecialchars($e['Code']) ?></strong></td>
                <td><?= htmlspecialchars($e['Nom']) ?></td>
                <td><?= htmlspecialchars($e['Prenom']) ?></td>
                <td><?= htmlspecialchars($e['IntituleF'] ?? '—') ?></td>
                <td>
                    <?php if ($e['Note'] !== null): ?>
                        <span class="<?= $noteClass ?>"><?= $e['Note'] ?></span>
                    <?php else: ?>
                        <span style="color:#aaa;">—</span>
                    <?php endif; ?>
                </td>
                <td><span class="badge <?= $badge ?>"><?= $mention ?></span></td>
                <td><?= $statut ?></td>
                <td>
                    <a href="detail.php?code=<?= urlencode($e['Code']) ?>" class="btn btn-primary btn-sm">👁</a>
                    <a href="modifier.php?code=<?= urlencode($e['Code']) ?>" class="btn btn-warning btn-sm">✏️</a>
                    <a href="supprimer.php?code=<?= urlencode($e['Code']) ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Confirmer la suppression de <?= htmlspecialchars(addslashes($e['Prenom'] . ' ' . $e['Nom'])) ?> ?')">🗑</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($nb_pages > 1): ?>
    <?php
        $query_params = [];
        if ($recherche) $query_params['recherche'] = $recherche;
        if ($filtre_f)  $query_params['filiere']   = $filtre_f;
    ?>
    <div class="pagination" style="margin-top:16px;">
        <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($query_params, ['page'=>$page-1])) ?>">‹ Précédent</a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $nb_pages; $p++): ?>
            <?php if ($p === $page): ?>
                <span class="current"><?= $p ?></span>
            <?php else: ?>
                <a href="?<?= http_build_query(array_merge($query_params, ['page'=>$p])) ?>"><?= $p ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $nb_pages): ?>
            <a href="?<?= http_build_query(array_merge($query_params, ['page'=>$page+1])) ?>">Suivant ›</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
