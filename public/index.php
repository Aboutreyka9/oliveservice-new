<?php 
require_once __DIR__ . '/../core/PrincipalRoute.php';
$route = new Router();

// Instanciation des contrôleurs actifs pour Olive Service
$homeController = new HomeController();
$userController = new UserController();
$etablissementController = new EtablissementController();
$fonctionController = new FonctionController();
$anneeController = new AnneeController();
$sessionController = new SessionController();
$zoneController = new ZoneController();
$categorieArticleController = new CategorieArticleController();
$articleController = new ArticleController();
$categoriePackController = new CategoriePackController();
$packController = new PackController();
$clientController = new ClientController();
$zoneCommercialController = new ZoneCommercialController();
$souscriptionController = new SouscriptionController();
$cotisationController = new CotisationController();
$cautisationPaymentController = new CautisationPaymentController();
$distributionController = new DistributionController();
$ouvertureCaisseController = new OuvertureCaisseController();
$clotureCaisseController = new ClotureCaisseController();
$typeDepenseController = new TypeDepenseController();
$depenseController = new DepenseController();
$versementController = new VersementController();
$roleController = new RoleController();
$permissionController = new PermissionController();
$notificationController = new NotificationController();

// -------------------------------------------------------------
// Route d'accueil & Authentification
// -------------------------------------------------------------
$route->addRoute('/', [$homeController, 'index']);
$route->addRoute('/home/dashboardData', [$homeController, 'dashboardData']);
$route->addRoute('/user/connexion', [$userController, 'connexion']);
$route->addRoute('/user/decon', [$userController, 'decon']);
$route->addRoute('/user/logout', [$userController, 'logout']);
$route->addRoute('/user/profil', [$userController, 'profil']);
$route->addRoute('/user/editPassword', [$userController, 'editPassword']);
$route->addRoute('/user/list', [$userController, 'list']);
$route->addRoute('/user/apiList', [$userController, 'apiList']);
$route->addRoute('/user/add', [$userController, 'add']);
$route->addRoute('/user/edit', [$userController, 'edit']);
$route->addRoute('/user/changer', [$userController, 'changer']);
$route->addRoute('/user/edition/{param}', [$userController, 'edition']);
$route->addRoute('/user/details/{param}', [$userController, 'details']);
$route->addRoute('/user/formulaire', [$userController, 'formulaire']);

// -------------------------------------------------------------
// Module: Etablissements & Fonctions
// -------------------------------------------------------------
$route->addRoute('/etablissement/config', [$etablissementController, 'config']);
$route->addRoute('/etablissement/list', [$etablissementController, 'list']);
$route->addRoute('/etablissement/apiList', [$etablissementController, 'apiList']);
$route->addRoute('/etablissement/add', [$etablissementController, 'add']);
$route->addRoute('/etablissement/edit', [$etablissementController, 'edit']);
$route->addRoute('/etablissement/changer', [$etablissementController, 'changer']);
$route->addRoute('/etablissement/details/{param}', [$etablissementController, 'details']);
$route->addRoute('/etablissement/edition/{param}', [$etablissementController, 'edition']);
$route->addRoute('/etablissement/formulaire', [$etablissementController, 'formulaire']);

$route->addRoute('/fonction/list', [$fonctionController, 'list']);
$route->addRoute('/fonction/apiList', [$fonctionController, 'apiList']);
$route->addRoute('/fonction/add', [$fonctionController, 'add']);
$route->addRoute('/fonction/edit', [$fonctionController, 'edit']);
$route->addRoute('/fonction/changer', [$fonctionController, 'changer']);
$route->addRoute('/fonction/details/{param}', [$fonctionController, 'details']);
$route->addRoute('/fonction/edition/{param}', [$fonctionController, 'edition']);
$route->addRoute('/fonction/formulaire', [$fonctionController, 'formulaire']);

// -------------------------------------------------------------
// Module: Configuration (Années, Sessions, Zones)
// -------------------------------------------------------------
$route->addRoute('/annee/list', [$anneeController, 'list']);
$route->addRoute('/annee/apiList', [$anneeController, 'apiList']);
$route->addRoute('/annee/add', [$anneeController, 'add']);
$route->addRoute('/annee/edit', [$anneeController, 'edit']);
$route->addRoute('/annee/changer', [$anneeController, 'changer']);
$route->addRoute('/annee/details/{param}', [$anneeController, 'details']);
$route->addRoute('/annee/edition/{param}', [$anneeController, 'edition']);
$route->addRoute('/annee/formulaire', [$anneeController, 'formulaire']);

$route->addRoute('/session/list', [$sessionController, 'list']);
$route->addRoute('/session/apiList', [$sessionController, 'apiList']);
$route->addRoute('/session/add', [$sessionController, 'add']);
$route->addRoute('/session/edit', [$sessionController, 'edit']);
$route->addRoute('/session/changer', [$sessionController, 'changer']);
$route->addRoute('/session/details/{param}', [$sessionController, 'details']);
$route->addRoute('/session/edition/{param}', [$sessionController, 'edition']);
$route->addRoute('/session/formulaire', [$sessionController, 'formulaire']);

$route->addRoute('/zone/list', [$zoneController, 'list']);
$route->addRoute('/zone/apiList', [$zoneController, 'apiList']);
$route->addRoute('/zone/add', [$zoneController, 'add']);
$route->addRoute('/zone/edit', [$zoneController, 'edit']);
$route->addRoute('/zone/changer', [$zoneController, 'changer']);
$route->addRoute('/zone/details/{param}', [$zoneController, 'details']);
$route->addRoute('/zone/edition/{param}', [$zoneController, 'edition']);
$route->addRoute('/zone/formulaire', [$zoneController, 'formulaire']);

// -------------------------------------------------------------
// Module: Catalogue & Produit (Articles, Catégories, Packs)
// -------------------------------------------------------------
$route->addRoute('/categories_articles/list', [$categorieArticleController, 'list']);
$route->addRoute('/categories_articles/apiList', [$categorieArticleController, 'apiList']);
$route->addRoute('/categories_articles/add', [$categorieArticleController, 'add']);
$route->addRoute('/categories_articles/edit', [$categorieArticleController, 'edit']);
$route->addRoute('/categories_articles/changer', [$categorieArticleController, 'changer']);
$route->addRoute('/categories_articles/details/{param}', [$categorieArticleController, 'details']);
$route->addRoute('/categories_articles/edition/{param}', [$categorieArticleController, 'edition']);
$route->addRoute('/categories_articles/formulaire', [$categorieArticleController, 'formulaire']);

$route->addRoute('/article/list', [$articleController, 'list']);
$route->addRoute('/article/apiList', [$articleController, 'apiList']);
$route->addRoute('/article/add', [$articleController, 'add']);
$route->addRoute('/article/edit', [$articleController, 'edit']);
$route->addRoute('/article/changer', [$articleController, 'changer']);
$route->addRoute('/article/details/{param}', [$articleController, 'details']);
$route->addRoute('/article/edition/{param}', [$articleController, 'edition']);
$route->addRoute('/article/formulaire', [$articleController, 'formulaire']);

$route->addRoute('/categorie_pack/list', [$categoriePackController, 'list']);
$route->addRoute('/categorie_pack/apiList', [$categoriePackController, 'apiList']);
$route->addRoute('/categorie_pack/add', [$categoriePackController, 'add']);
$route->addRoute('/categorie_pack/edit', [$categoriePackController, 'edit']);
$route->addRoute('/categorie_pack/changer', [$categoriePackController, 'changer']);
$route->addRoute('/categorie_pack/details/{param}', [$categoriePackController, 'details']);
$route->addRoute('/categorie_pack/edition/{param}', [$categoriePackController, 'edition']);
$route->addRoute('/categorie_pack/formulaire', [$categoriePackController, 'formulaire']);

$route->addRoute('/pack/list', [$packController, 'list']);
$route->addRoute('/pack/apiList', [$packController, 'apiList']);
$route->addRoute('/pack/add', [$packController, 'add']);
$route->addRoute('/pack/edit', [$packController, 'edit']);
$route->addRoute('/pack/changer', [$packController, 'changer']);
$route->addRoute('/pack/details/{param}', [$packController, 'details']);
$route->addRoute('/pack/edition/{param}', [$packController, 'edition']);
$route->addRoute('/pack/formulaire', [$packController, 'formulaire']);

// -------------------------------------------------------------
// Module: Clients & Zones Commerciales
// -------------------------------------------------------------
$route->addRoute('/client/list', [$clientController, 'list']);
$route->addRoute('/client/apiList', [$clientController, 'apiList']);
$route->addRoute('/client/add', [$clientController, 'add']);
$route->addRoute('/client/edit', [$clientController, 'edit']);
$route->addRoute('/client/changer', [$clientController, 'changer']);
$route->addRoute('/client/details/{param}', [$clientController, 'details']);
$route->addRoute('/client/edition/{param}', [$clientController, 'edition']);
$route->addRoute('/client/formulaire', [$clientController, 'formulaire']);

$route->addRoute('/zone_commercial/list', [$zoneCommercialController, 'list']);
$route->addRoute('/zone_commercial/apiList', [$zoneCommercialController, 'apiList']);
$route->addRoute('/zone_commercial/add', [$zoneCommercialController, 'add']);
$route->addRoute('/zone_commercial/edit', [$zoneCommercialController, 'edit']);
$route->addRoute('/zone_commercial/changer', [$zoneCommercialController, 'changer']);
$route->addRoute('/zone_commercial/details/{param}', [$zoneCommercialController, 'details']);
$route->addRoute('/zone_commercial/edition/{param}', [$zoneCommercialController, 'edition']);
$route->addRoute('/zone_commercial/formulaire', [$zoneCommercialController, 'formulaire']);

// -------------------------------------------------------------
// Module: Souscriptions, Cotisations & Paiements
// -------------------------------------------------------------
$route->addRoute('/souscription/list', [$souscriptionController, 'list']);
$route->addRoute('/souscription/apiList', [$souscriptionController, 'apiList']);
$route->addRoute('/souscription/add', [$souscriptionController, 'add']);
$route->addRoute('/souscription/edit', [$souscriptionController, 'edit']);
$route->addRoute('/souscription/changer', [$souscriptionController, 'changer']);
$route->addRoute('/souscription/details/{param}', [$souscriptionController, 'details']);
$route->addRoute('/souscription/edition/{param}', [$souscriptionController, 'edition']);
$route->addRoute('/souscription/formulaire', [$souscriptionController, 'formulaire']);
$route->addRoute('/souscription/wizard', [$souscriptionController, 'wizard']);
$route->addRoute('/souscription/wizardData', [$souscriptionController, 'wizardData']);
$route->addRoute('/souscription/wizardSubmit', [$souscriptionController, 'wizardSubmit']);

$route->addRoute('/cotisation/list', [$cotisationController, 'list']);
$route->addRoute('/cotisation/apiList', [$cotisationController, 'apiList']);
$route->addRoute('/cotisation/add', [$cotisationController, 'add']);
$route->addRoute('/cotisation/edit', [$cotisationController, 'edit']);
$route->addRoute('/cotisation/changer', [$cotisationController, 'changer']);
$route->addRoute('/cotisation/details/{param}', [$cotisationController, 'details']);
$route->addRoute('/cotisation/edition/{param}', [$cotisationController, 'edition']);
$route->addRoute('/cotisation/formulaire', [$cotisationController, 'formulaire']);

$route->addRoute('/cautisation-payment/search-form', [$cautisationPaymentController, 'searchForm']);
$route->addRoute('/cautisation-payment/search', [$cautisationPaymentController, 'search']);
$route->addRoute('/cautisation-payment/situation', [$cautisationPaymentController, 'situation']);
$route->addRoute('/cautisation-payment/situation/{param}', [$cautisationPaymentController, 'situation']);
$route->addRoute('/cautisation-payment/situation-details', [$cautisationPaymentController, 'situationDetails']);
$route->addRoute('/cautisation-payment/history', [$cautisationPaymentController, 'history']);
$route->addRoute('/cautisation-payment/savepayment', [$cautisationPaymentController, 'savepayment']);

// -------------------------------------------------------------
// Module: Distributions
// -------------------------------------------------------------
$route->addRoute('/distribution/list', [$distributionController, 'list']);
$route->addRoute('/distribution/apiList', [$distributionController, 'apiList']);
$route->addRoute('/distribution/add', [$distributionController, 'add']);
$route->addRoute('/distribution/edit', [$distributionController, 'edit']);
$route->addRoute('/distribution/changer', [$distributionController, 'changer']);
$route->addRoute('/distribution/details/{param}', [$distributionController, 'details']);
$route->addRoute('/distribution/edition/{param}', [$distributionController, 'edition']);
$route->addRoute('/distribution/formulaire', [$distributionController, 'formulaire']);

// -------------------------------------------------------------
// Module: Caisse & Finances (Ouverture, Clôture, Dépenses, Versements)
// -------------------------------------------------------------
$route->addRoute('/ouverture_caisse/list', [$ouvertureCaisseController, 'list']);
$route->addRoute('/ouverture_caisse/apiList', [$ouvertureCaisseController, 'apiList']);
$route->addRoute('/ouverture_caisse/add', [$ouvertureCaisseController, 'add']);
$route->addRoute('/ouverture_caisse/edit', [$ouvertureCaisseController, 'edit']);
$route->addRoute('/ouverture_caisse/changer', [$ouvertureCaisseController, 'changer']);
$route->addRoute('/ouverture_caisse/details/{param}', [$ouvertureCaisseController, 'details']);
$route->addRoute('/ouverture_caisse/edition/{param}', [$ouvertureCaisseController, 'edition']);
$route->addRoute('/ouverture_caisse/formulaire', [$ouvertureCaisseController, 'formulaire']);

$route->addRoute('/cloture_caisse/list', [$clotureCaisseController, 'list']);
$route->addRoute('/cloture_caisse/apiList', [$clotureCaisseController, 'apiList']);
$route->addRoute('/cloture_caisse/getDailyTotals', [$clotureCaisseController, 'getDailyTotals']);
$route->addRoute('/cloture_caisse/add', [$clotureCaisseController, 'add']);
$route->addRoute('/cloture_caisse/edit', [$clotureCaisseController, 'edit']);
$route->addRoute('/cloture_caisse/changer', [$clotureCaisseController, 'changer']);
$route->addRoute('/cloture_caisse/details/{param}', [$clotureCaisseController, 'details']);
$route->addRoute('/cloture_caisse/edition/{param}', [$clotureCaisseController, 'edition']);
$route->addRoute('/cloture_caisse/formulaire', [$clotureCaisseController, 'formulaire']);

$route->addRoute('/type_depense/list', [$typeDepenseController, 'list']);
$route->addRoute('/type_depense/apiList', [$typeDepenseController, 'apiList']);
$route->addRoute('/type_depense/add', [$typeDepenseController, 'add']);
$route->addRoute('/type_depense/edit', [$typeDepenseController, 'edit']);
$route->addRoute('/type_depense/changer', [$typeDepenseController, 'changer']);
$route->addRoute('/type_depense/details/{param}', [$typeDepenseController, 'details']);
$route->addRoute('/type_depense/edition/{param}', [$typeDepenseController, 'edition']);
$route->addRoute('/type_depense/formulaire', [$typeDepenseController, 'formulaire']);

$route->addRoute('/depense/list', [$depenseController, 'list']);
$route->addRoute('/depense/apiList', [$depenseController, 'apiList']);
$route->addRoute('/depense/add', [$depenseController, 'add']);
$route->addRoute('/depense/edit', [$depenseController, 'edit']);
$route->addRoute('/depense/changer', [$depenseController, 'changer']);
$route->addRoute('/depense/details/{param}', [$depenseController, 'details']);
$route->addRoute('/depense/edition/{param}', [$depenseController, 'edition']);
$route->addRoute('/depense/formulaire', [$depenseController, 'formulaire']);

$route->addRoute('/versement/list', [$versementController, 'list']);
$route->addRoute('/versement/apiList', [$versementController, 'apiList']);
$route->addRoute('/versement/add', [$versementController, 'add']);
$route->addRoute('/versement/edit', [$versementController, 'edit']);
$route->addRoute('/versement/changer', [$versementController, 'changer']);
$route->addRoute('/versement/details/{param}', [$versementController, 'details']);
$route->addRoute('/versement/edition/{param}', [$versementController, 'edition']);
$route->addRoute('/versement/formulaire', [$versementController, 'formulaire']);

// -------------------------------------------------------------
// Module: Rôles, Permissions & Notifications
// -------------------------------------------------------------
$route->addRoute('/role/list', [$roleController, 'list']);
$route->addRoute('/role/apiList', [$roleController, 'apiList']);
$route->addRoute('/role/add', [$roleController, 'add']);
$route->addRoute('/role/edit', [$roleController, 'edit']);
$route->addRoute('/role/changer', [$roleController, 'changer']);
$route->addRoute('/role/details/{param}', [$roleController, 'details']);
$route->addRoute('/role/edition/{param}', [$roleController, 'edition']);
$route->addRoute('/role/formulaire', [$roleController, 'formulaire']);

$route->addRoute('/permission/list', [$permissionController, 'list']);
$route->addRoute('/permission/apiList', [$permissionController, 'apiList']);
$route->addRoute('/permission/add', [$permissionController, 'add']);
$route->addRoute('/permission/edit', [$permissionController, 'edit']);
$route->addRoute('/permission/changer', [$permissionController, 'changer']);
$route->addRoute('/permission/details/{param}', [$permissionController, 'details']);
$route->addRoute('/permission/edition/{param}', [$permissionController, 'edition']);
$route->addRoute('/permission/formulaire', [$permissionController, 'formulaire']);
$route->addRoute('/permission/addModule', [$permissionController, 'addModule']);

$route->addRoute('/notification/list', [$notificationController, 'list']);
$route->addRoute('/notification/apiList', [$notificationController, 'apiList']);
$route->addRoute('/notification/add', [$notificationController, 'add']);
$route->addRoute('/notification/edit', [$notificationController, 'edit']);
$route->addRoute('/notification/changer', [$notificationController, 'changer']);
$route->addRoute('/notification/details/{param}', [$notificationController, 'details']);
$route->addRoute('/notification/edition/{param}', [$notificationController, 'edition']);
$route->addRoute('/notification/formulaire', [$notificationController, 'formulaire']);

// -------------------------------------------------------------
// Extraction & Exécution de l'URL
// -------------------------------------------------------------
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (strpos($url, '/geicg/public') === 0) {
    $url = str_replace('/geicg/public', '', $url);
} elseif (strpos($url, '/geicg') === 0) {
    $url = str_replace('/geicg', '', $url);
}

$url = rtrim($url, '/');
if ($url === '') {
    $url = '/';
}
$route->run($url);
