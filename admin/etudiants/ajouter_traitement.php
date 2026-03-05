<?php
/**
 * admin/etudiants/ajouter_traitement.php — Traitement ajout étudiant + upload photo
 */
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ajouter.php');
    exit();
}

$pdo = getConnexion();

// Récupération et nettoyage
$code           = strtoupper(trim($_POST['Code']           ?? ''));
$nom            = trim($_POST['Nom']            ?? '');
$prenom         = trim($_POST['Prenom']         ?? '');
$filiere        = trim($_POST['Filiere']        ?? '') ?: null;
$note_raw       = trim($_POST['Note']           ?? '');
$note           = $note_raw !== '' ? (float)$note_raw : null;
$date_naissance = trim($_POST['date_naissance'] ?? '') ?: null;
$email          = trim($_POST['email']          ?? '') ?: null;
$telephone      = trim($_POST['telephone']      ?? '') ?: null;

$erreurs = [];

// Validations
if ($code   === '') $erreurs[] = 'Le code étudiant est obligatoire.';
if ($nom    === '') $erreurs[] = 'Le nom est obligatoire.';
if ($prenom === '') $erreurs[] = 'Le prénom est obligatoire.';
if ($note !== null && ($note < 0 || $note > 20)) $erreurs[] = 'La note doit être entre 0 et 20.';

// Unicité code
if ($code !== '') {
    $chk = $pdo->prepare('SELECT Code FROM etudiants WHERE Code = ?');
    $chk->execute([$code]);
    if ($chk->fetch()) $erreurs[] = "Le code étudiant «$code» existe déjà.";
}
// Unicité email
if ($email !== null && $email !== '') {
    $chk2 = $pdo->prepare('SELECT Code FROM etudiants WHERE email = ?');
    $chk2->execute([$email]);
    if ($chk2->fetch()) $erreurs[] = "L'email «$email» est déjà utilisé.";
}

// ── Upload photo (7 étapes) ──
$photo_filename = null;
if (!empty($_FILES['photo']['name'])) {
    $f = $_FILES['photo'];

    // Étape 1 : aucune erreur d'upload
    if ($f['error'] !== UPLOAD_ERR_OK) {
        $erreurs[] = 'Erreur lors de l\'upload de la photo (code ' . $f['error'] . ').';
    } else {
        // Étape 2 : taille max 2 Mo
        if ($f['size'] > 2097152) {
            $erreurs[] = 'La photo dépasse 2 Mo.';
        } else {
            // Étape 3 : extension autorisée
            $ext_allowed = ['jpg','jpeg','png'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $ext_allowed)) {
                $erreurs[] = 'Extension de photo non autorisée (jpg, jpeg, png uniquement).';
            } else {
                // Étape 4 : type MIME réel côté serveur
                $mime = mime_content_type($f['tmp_name']);
                $mime_allowed = ['image/jpeg','image/png'];
                if (!in_array($mime, $mime_allowed)) {
                    $erreurs[] = 'Type MIME invalide pour la photo. Seuls JPG/PNG sont acceptés.';
                } else {
                    // Étape 5 : renommer le fichier
                    $photo_filename = 'photo_' . $code . '.' . $ext;
                    $dest = '../../uploads/photos/' . $photo_filename;
                    // Étape 6 : déplacer
                    if (!move_uploaded_file($f['tmp_name'], $dest)) {
                        $erreurs[] = 'Impossible de déplacer la photo sur le serveur.';
                        $photo_filename = null;
                    }
                    // Étape 7 : sera enregistrée en BDD ci-dessous
                }
            }
        }
    }
}

// Retourner en cas d'erreurs
if (!empty($erreurs)) {
    $_SESSION['form_erreurs'] = $erreurs;
    $_SESSION['form_values']  = $_POST;
    header('Location: ajouter.php');
    exit();
}

// Insertion en BDD
$stmt = $pdo->prepare('
    INSERT INTO etudiants (Code, Nom, Prenom, Filiere, Note, Photo, date_naissance, email, telephone, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
');
$stmt->execute([$code, $nom, $prenom, $filiere, $note, $photo_filename, $date_naissance, $email, $telephone]);

header('Location: liste.php?msg=ajoute');
exit();
