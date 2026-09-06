<?php
session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../core/Context.php';
require_once __DIR__ . '/../core/BaseModel.php';
require_once __DIR__ . '/../models/home/ModelHome.php';

try {
    $db = (new Database())->getCon();
    
    // Dump actual packs in table
    $stmtAllPacks = $db->query("SELECT id_pack, code_pack, libelle_pack, statut_pack, etablissement_code, zone_code, annee_code FROM packs");
    $packsInDb = $stmtAllPacks->fetchAll(PDO::FETCH_ASSOC);
    
    // Active session context
    $anneeSess = Context::annee();
    $etabSess = Context::etablissement();
    $zoneSess = Context::zone();
    
    // Query executed by getStats for packs
    $sqlPacks = "SELECT COUNT(*) FROM packs WHERE statut_pack = 'actif'";
    $pPacks = [];
    $sqlPacksCond = [];
    Context::applyTripleFilter('', $sqlPacksCond, $pPacks);
    if (!empty($sqlPacksCond)) {
        $sqlPacks .= " AND " . implode(' AND ', $sqlPacksCond);
    }
    
    $stmtPacks = $db->prepare($sqlPacks);
    $stmtPacks->execute($pPacks);
    $countResult = $stmtPacks->fetchColumn();

    // Query executed for articles
    $countArticles = $db->query("SELECT COUNT(*) FROM articles WHERE statut_article = 'actif'")->fetchColumn();

    echo json_encode([
        'session_context' => [
            'annee_code' => $anneeSess,
            'etablissement_code' => $etabSess,
            'zone_code' => $zoneSess
        ],
        'sql_query' => $sqlPacks,
        'sql_params' => $pPacks,
        'query_result_count' => (int)$countResult,
        'total_articles_count' => (int)$countArticles,
        'packs_in_db' => $packsInDb
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
