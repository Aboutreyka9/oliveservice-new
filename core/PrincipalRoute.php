<?php

require_once __DIR__ . '/../config/Database.php';

require_once __DIR__ . '/../models/Validator.php';
require_once __DIR__ . '/../core/PressingAware.php';
require_once __DIR__ . '/../core/NotificationService.php';
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../core/BaseModel.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Context.php';
require_once __DIR__ . '/../core/CautisationValidator.php';

// Olive Service Core & Auth Models
require_once __DIR__ . '/../models/home/ModelHome.php';
require_once __DIR__ . '/../models/users/ModelUser.php';
require_once __DIR__ . '/../models/roles/ModelRole.php';
require_once __DIR__ . '/../models/permissions/ModelPermission.php';
require_once __DIR__ . '/../models/modules_metier/ModelModuleMetier.php';
require_once __DIR__ . '/../models/notifications/ModelNotification.php';
require_once __DIR__ . '/../models/fonctions/ModelFonction.php';

// Configuration Models
require_once __DIR__ . '/../models/etablissements/ModelEtablissement.php';
require_once __DIR__ . '/../models/annees/ModelAnnee.php';
require_once __DIR__ . '/../models/sessions/ModelSession.php';
require_once __DIR__ . '/../models/zones/ModelZone.php';

// Articles & Packs Models
require_once __DIR__ . '/../models/categories_articles/ModelCategorieArticle.php';
require_once __DIR__ . '/../models/articles/ModelArticle.php';
require_once __DIR__ . '/../models/categorie_packs/ModelCategoriePack.php';
require_once __DIR__ . '/../models/packs/ModelPack.php';

// Clients & Commercials Models
require_once __DIR__ . '/../models/clients/ModelClient.php';
require_once __DIR__ . '/../models/zone_commercials/ModelZoneCommercial.php';

// Souscriptions, Cotisations & Distributions Models
require_once __DIR__ . '/../models/souscriptions/ModelSouscription.php';
require_once __DIR__ . '/../models/cotisations/ModelCotisation.php';
require_once __DIR__ . '/../models/distributions/ModelDistribution.php';

// Finances, Caisse & Dépenses Models
require_once __DIR__ . '/../models/ouvertures_caisse/ModelOuvertureCaisse.php';
require_once __DIR__ . '/../models/clotures_caisse/ModelClotureCaisse.php';
require_once __DIR__ . '/../models/type_depenses/ModelTypeDepense.php';
require_once __DIR__ . '/../models/depenses/ModelDepense.php';
require_once __DIR__ . '/../models/versements/ModelVersement.php';

// Olive Service Controllers
require_once __DIR__ . '/../controllers/home/HomeController.php';
require_once __DIR__ . '/../controllers/users/UserController.php';
require_once __DIR__ . '/../controllers/roles/RoleController.php';
require_once __DIR__ . '/../controllers/permissions/PermissionController.php';
require_once __DIR__ . '/../controllers/notifications/NotificationController.php';
require_once __DIR__ . '/../controllers/fonctions/FonctionController.php';

require_once __DIR__ . '/../controllers/etablissements/EtablissementController.php';
require_once __DIR__ . '/../controllers/annees/AnneeController.php';
require_once __DIR__ . '/../controllers/sessions/SessionController.php';
require_once __DIR__ . '/../controllers/zones/ZoneController.php';

require_once __DIR__ . '/../controllers/categories_articles/CategorieArticleController.php';
require_once __DIR__ . '/../controllers/articles/ArticleController.php';
require_once __DIR__ . '/../controllers/categorie_packs/CategoriePackController.php';
require_once __DIR__ . '/../controllers/packs/PackController.php';

require_once __DIR__ . '/../controllers/clients/ClientController.php';
require_once __DIR__ . '/../controllers/zone_commercials/ZoneCommercialController.php';

require_once __DIR__ . '/../controllers/souscriptions/SouscriptionController.php';
require_once __DIR__ . '/../controllers/cotisations/CotisationController.php';
require_once __DIR__ . '/../controllers/cotisations/CautisationPaymentController.php';
require_once __DIR__ . '/../controllers/distributions/DistributionController.php';

require_once __DIR__ . '/../controllers/ouvertures_caisse/OuvertureCaisseController.php';
require_once __DIR__ . '/../controllers/clotures_caisse/ClotureCaisseController.php';
require_once __DIR__ . '/../controllers/type_depenses/TypeDepenseController.php';
require_once __DIR__ . '/../controllers/depenses/DepenseController.php';
require_once __DIR__ . '/../controllers/versements/VersementController.php';
