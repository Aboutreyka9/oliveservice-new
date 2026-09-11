<?php
define('RACINE', '/oliveservice/');
require_once __DIR__ . '/../core/PrincipalRoute.php';

$db = (new ModelVersement())->getCon();

$sql = "
    SELECT v.*, c.id_caisse,
           uc.nom_user as nom_commercial, uc.prenom_user as prenom_commercial, uc.commission as commission_user,
           uv.nom_user as nom_validator, uv.prenom_user as prenom_validator,
           z.libelle_zone
    FROM versements_commerciaux v
    LEFT JOIN caisses c ON c.code_caisse = v.caisse_code
    LEFT JOIN users uc ON uc.code_user = v.commercial_code
    LEFT JOIN users uv ON uv.code_user = v.user_validate
    LEFT JOIN zones z ON z.code_zone = v.zone_code
";

$stmt = $db->prepare($sql);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Nombre total de versements: " . count($items) . "\n";
foreach ($items as $item) {
    echo "ID: {$item['id_versement']}, Code: {$item['code_versement_commercial']}, Commercial: {$item['nom_commercial']} {$item['prenom_commercial']} ({$item['commercial_code']}), Montant: {$item['montant_versement']}, Statut: {$item['statut_versement']}, Commission Rate: " . ($item['commission_user'] ?? 'NULL') . "\n";
}
