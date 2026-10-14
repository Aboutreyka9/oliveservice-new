<?php
require_once __DIR__ . '/../core/PrincipalRoute.php';

$model = new ModelVersement();
$db = $model->getCon();

$idVersement = 2; // Versement VRS-DD52INGK
$stmtV = $db->prepare("SELECT * FROM versements_commerciaux WHERE id_versement = ?");
$stmtV->execute([$idVersement]);
$versement = $stmtV->fetch(PDO::FETCH_ASSOC);

$commCode = $versement['commercial_code'] ?? '';
$etabCode = $versement['etablissement_code'] ?? '';
$anneeCode = $versement['annee_code'] ?? '';
$zoneCode = $versement['zone_code'] ?? '';
$caisseCode = $versement['caisse_code'] ?? null;
$periodeDebut = !empty($versement['periode_versement_debut']) ? $versement['periode_versement_debut'] : (!empty($versement['periode_versement']) ? $versement['periode_versement'] : (isset($versement['created_at_versement']) ? substr($versement['created_at_versement'], 0, 10) : ''));

// 1. Details de la caisse liée
$caisseDetails = null;
if (!empty($caisseCode)) {
    $stmtCaisse = $db->prepare("
        SELECT * FROM caisses 
        WHERE code_caisse = ? AND etablissement_code = ? AND annee_code = ?
        LIMIT 1
    ");
    $stmtCaisse->execute([$caisseCode, $etabCode, $anneeCode]);
    $caisseDetails = $stmtCaisse->fetch(PDO::FETCH_ASSOC) ?: null;
}

// 2. Cotisations associées
$caisseCotisations = [];
if (!empty($caisseCode)) {
    $stmtCCotis = $db->prepare("
        SELECT c.code_cautisation_client, c.montant_cautisation_client, c.mode_paiement, c.statut_cautisation_client, c.date_cautisation, c.souscription_code, cli.nom_client, cli.telephone_client
        FROM cautisation_clients c
        LEFT JOIN clients cli ON cli.code_client = c.client_code
        WHERE c.caisse_code = ? AND c.statut_cautisation_client != 'annule'
          AND c.etablissement_code = ? AND c.annee_code = ?
        ORDER BY c.date_cautisation DESC
    ");
    $stmtCCotis->execute([$caisseCode, $etabCode, $anneeCode]);
    $caisseCotisations = $stmtCCotis->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

if (empty($caisseCotisations) && !empty($commCode)) {
    $stmtCCotis = $db->prepare("
        SELECT c.code_cautisation_client, c.montant_cautisation_client, c.mode_paiement, c.statut_cautisation_client, c.date_cautisation, c.souscription_code, cli.nom_client, cli.telephone_client
        FROM cautisation_clients c
        LEFT JOIN clients cli ON cli.code_client = c.client_code
        WHERE (c.commercial_code = ? OR c.user_code = ?)
          AND DATE(c.date_cautisation) = ?
          AND c.statut_cautisation_client != 'annule'
          AND c.etablissement_code = ? AND c.annee_code = ?
        ORDER BY c.date_cautisation DESC
    ");
    $stmtCCotis->execute([$commCode, $commCode, $periodeDebut, $etabCode, $anneeCode]);
    $caisseCotisations = $stmtCCotis->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

$totalEspeces = 0;
$totalMomo = 0;
$totalAutre = 0;
$totalGeneral = 0;

foreach ($caisseCotisations as $cc) {
    $m = (float)($cc['montant_cautisation_client'] ?? 0);
    $mode = strtolower(trim($cc['mode_paiement'] ?? 'espece'));
    $totalGeneral += $m;
    if (in_array($mode, ['espece', 'especes', 'cash'], true)) {
        $totalEspeces += $m;
    } elseif (in_array($mode, ['mobile_money', 'wave', 'orange', 'mtn', 'moov', 'momo'], true)) {
        $totalMomo += $m;
    } else {
        $totalAutre += $m;
    }
}

$montantVersement = (float)($versement['montant_versement'] ?? 0);
$caisseAttenduDef = $totalEspeces ?: ($totalGeneral ?: (float)($caisseDetails['montant_total_attendu'] ?? (float)($caisseDetails['montant_total_depot'] ?? 0)));
$caisseEcart = $montantVersement - $caisseAttenduDef;

$res = [
    'status' => 1,
    'data' => [
        'commercial_code' => $commCode,
        'versement_actuel' => $montantVersement,
        'versement_actuel_fmt' => number_format($montantVersement, 0, ',', ' ') . ' FCFA',
        'total_collecte' => $totalGeneral,
        'total_collecte_fmt' => number_format($totalGeneral, 0, ',', ' ') . ' FCFA',
        'total_especes' => $totalEspeces,
        'total_especes_fmt' => number_format($totalEspeces, 0, ',', ' ') . ' FCFA',
        'total_momo' => $totalMomo,
        'total_momo_fmt' => number_format($totalMomo, 0, ',', ' ') . ' FCFA',
        'has_linked_caisse' => !empty($caisseDetails),
        'linked_caisse_code' => $caisseCode ?: '-',
        'caisse_attendu' => $caisseAttenduDef,
        'caisse_attendu_fmt' => number_format($caisseAttenduDef, 0, ',', ' ') . ' FCFA',
        'caisse_ecart' => $caisseEcart,
        'caisse_ecart_fmt' => number_format(abs($caisseEcart), 0, ',', ' ') . ' FCFA',
        'caisse_cotisations' => array_map(function($c) {
            return [
                'code' => $c['code_cautisation_client'],
                'souscription' => $c['souscription_code'] ?? '-',
                'montant' => (float)$c['montant_cautisation_client'],
                'montant_fmt' => number_format((float)$c['montant_cautisation_client'], 0, ',', ' ') . ' FCFA',
                'mode' => strtoupper($c['mode_paiement'] ?? 'ESPECES'),
                'statut' => $c['statut_cautisation_client'],
                'date' => $c['date_cautisation'] ? date('d/m/Y H:i', strtotime($c['date_cautisation'])) : '-',
                'client' => $c['nom_client'] ?? 'Client',
                'telephone' => $c['telephone_client'] ?? '-'
            ];
        }, $caisseCotisations)
    ]
];

echo json_encode($res, JSON_PRETTY_PRINT);
