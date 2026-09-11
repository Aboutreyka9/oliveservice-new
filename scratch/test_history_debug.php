<?php
require_once __DIR__ . '/../core/PrincipalRoute.php';

$model = new ModelVersement();
$db = $model->getCon();

$stmtV = $db->query("SELECT * FROM versements_commerciaux ORDER BY id_versement DESC LIMIT 5");
$versements = $stmtV->fetchAll(PDO::FETCH_ASSOC);

echo "LAST 5 VERSEMENTS:\n";

foreach ($versements as $v) {
    $caisseCode = $v['caisse_code'] ?? '';
    $commCode = $v['commercial_code'] ?? '';
    $periode = $v['periode_versement_debut'] ?? $v['periode_versement'] ?? '';
    $zCode = $v['zone_code'] ?? '';

    echo "\n----------------------------------------\n";
    echo "Versement ID {$v['id_versement']} | Code: {$v['code_versement_commercial']} | Caisse: {$caisseCode} | Comm: {$commCode} | Zone: {$zCode} | Montant: {$v['montant_versement']}\n";

    // Query caisses
    $stmtCaisse = $db->prepare("SELECT * FROM caisses WHERE code_caisse = ?");
    $stmtCaisse->execute([$caisseCode]);
    $caisse = $stmtCaisse->fetch(PDO::FETCH_ASSOC);
    echo "Caisse details: " . json_encode($caisse) . "\n";

    // Query cotisations by caisse_code
    $stmtCotis1 = $db->prepare("SELECT COUNT(*) as nb, SUM(montant_cautisation_client) as total FROM cautisation_clients WHERE caisse_code = ?");
    $stmtCotis1->execute([$caisseCode]);
    echo "Cotisations by caisse_code ($caisseCode): " . json_encode($stmtCotis1->fetch(PDO::FETCH_ASSOC)) . "\n";

    // Query cotisations by commercial + date
    $stmtCotis2 = $db->prepare("SELECT COUNT(*) as nb, SUM(montant_cautisation_client) as total FROM cautisation_clients WHERE (commercial_code = ? OR user_code = ?) AND DATE(date_cautisation) = ?");
    $stmtCotis2->execute([$commCode, $commCode, $periode]);
    echo "Cotisations by commercial ($commCode) & date ($periode): " . json_encode($stmtCotis2->fetch(PDO::FETCH_ASSOC)) . "\n";

    // Query all cotisations by commercial
    $stmtCotis3 = $db->prepare("SELECT COUNT(*) as nb, SUM(montant_cautisation_client) as total FROM cautisation_clients WHERE commercial_code = ? OR user_code = ?");
    $stmtCotis3->execute([$commCode, $commCode]);
    echo "All Cotisations by commercial ($commCode): " . json_encode($stmtCotis3->fetch(PDO::FETCH_ASSOC)) . "\n";
}
