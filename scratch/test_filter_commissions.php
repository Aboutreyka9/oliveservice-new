<?php
define('RACINE', '/oliveservice/');
require_once __DIR__ . '/../core/PrincipalRoute.php';

$_SESSION['user_code'] = 'USR-ADMIN-001';
$_SESSION['roles'] = ['ROLE_ADMIN'];
$_SESSION['etablissement_code'] = '5454544456';
$_SESSION['zone_code'] = '6QIlVfXP0LiXE9tBzHownYLAAqDi2';
$_SESSION['annee_code'] = 'AN-2026';

$db = (new ModelVersement())->getCon();

// Test date and commercial filter
$dateDebut = '2026-01-01';
$dateFin = '2026-12-31';

$sql = "
    SELECT v.*, uc.nom_user, uc.prenom_user, uc.commission
    FROM versements_commerciaux v
    LEFT JOIN users uc ON uc.code_user = v.commercial_code
    WHERE v.etablissement_code = ? AND v.zone_code = ? AND v.annee_code = ?
      AND LOWER(v.statut_versement) = 'valide'
";
$params = ['5454544456', '6QIlVfXP0LiXE9tBzHownYLAAqDi2', 'AN-2026'];

if (!empty($dateDebut)) {
    $sql .= " AND (DATE(v.date_validation) >= ? OR DATE(v.periode_versement) >= ?)";
    $params[] = $dateDebut;
    $params[] = $dateDebut;
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Test Filtres SQL OK, résultats: " . count($rows) . "\n";
