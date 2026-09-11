<?php
define('RACINE', '/oliveservice/');
require_once __DIR__ . '/../core/PrincipalRoute.php';

$_SESSION[USERS_AUTH]['id_user'] = 16;
$_SESSION[USERS_AUTH]['code_user'] = 'USR-COMM-001';
$_SESSION[USERS_AUTH]['roles'] = ['ROLE_COMMERCIAL'];
$_SESSION['etablissement_active_code'] = '5454544456';
$_SESSION['zone_active_code'] = '6QIlVfXP0LiXE9tBzHownYLAAqDi2';
$_SESSION['annee_active_code'] = 'AN-2026';

$_SERVER['REQUEST_METHOD'] = 'GET';

class TestVersementController extends VersementController {
    protected function json(array $data, int $code = 200): void {
        echo "HTTP STATUS: $code\n";
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
    }
}

$controller = new TestVersementController();
$controller->apiCommissions();
