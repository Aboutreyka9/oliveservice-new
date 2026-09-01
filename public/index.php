<?php 
require_once __DIR__ . '/../core/PrincipalRoute.php';
$route = new Router();

$homeController = new HomeController();
$userController = new UserController();
$etablissementController = new EtablissementController();
$fonctionController = new FonctionController();
$cycleController = new CycleController();
$filiereController = new FiliereController();
$filiereCycleController = new FiliereCycleController();
$niveauController = new NiveauController();
$filiereNiveauController = new FiliereNiveauController();
$salleController = new SalleController();
$anneeController = new AnneeController();
$sessionController = new SessionController();
$zoneController = new ZoneController();
$articleController = new ArticleController();
$categoriePackController = new CategoriePackController();
$packController = new PackController();
$clientController = new ClientController();
$zoneCommercialController = new ZoneCommercialController();
$souscriptionController = new SouscriptionController();
$cotisationController = new CotisationController();
$cautisationPaymentController = new CautisationPaymentController();
$distributionController = new DistributionController();
$classeController = new ClasseController();
$semestreController = new SemestreController();
$matiereController = new MatiereController();
$scolariteController = new ScolariteController();
$trancheController = new TrancheController();
$etudiantController = new EtudiantController();
$parentController = new ParentController();
$inscriptionController = new InscriptionController();
$accessoireController = new AccessoireController();
$paiementController = new PaiementController();
$ouvertureCaisseController = new OuvertureCaisseController();
$clotureCaisseController = new ClotureCaisseController();
$impayesController = new ImpayesController();
$typeDepenseController = new TypeDepenseController();
$depenseController = new DepenseController();
$enseignantController = new EnseignantController();
$enseignantMatiereController = new EnseignantMatiereController();
$emploiController = new EmploiController();
$absenceController = new AbsenceController();
$noteController = new NoteController();
$bulletinController = new BulletinController();
$evenementController = new EvenementController();
$galerieController = new GalerieController();
$documentController = new DocumentController();
$roleController = new RoleController();
$permissionController = new PermissionController();
$notificationController = new NotificationController();
$ueController = new UeController();

// Route d'accueil & Authentification
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

// Routes pour les modules GEICG

// Module: etablissement (EtablissementController)
$route->addRoute('/etablissement/config', [$etablissementController, 'config']);
$route->addRoute('/etablissement/list', [$etablissementController, 'list']);
$route->addRoute('/etablissement/apiList', [$etablissementController, 'apiList']);
$route->addRoute('/etablissement/add', [$etablissementController, 'add']);
$route->addRoute('/etablissement/edit', [$etablissementController, 'edit']);
$route->addRoute('/etablissement/changer', [$etablissementController, 'changer']);
$route->addRoute('/etablissement/details/{param}', [$etablissementController, 'details']);
$route->addRoute('/etablissement/edition/{param}', [$etablissementController, 'edition']);
$route->addRoute('/etablissement/formulaire', [$etablissementController, 'formulaire']);



// Module: fonction (FonctionController)
$route->addRoute('/fonction/list', [$fonctionController, 'list']);
$route->addRoute('/fonction/apiList', [$fonctionController, 'apiList']);
$route->addRoute('/fonction/add', [$fonctionController, 'add']);
$route->addRoute('/fonction/edit', [$fonctionController, 'edit']);
$route->addRoute('/fonction/changer', [$fonctionController, 'changer']);
$route->addRoute('/fonction/details/{param}', [$fonctionController, 'details']);
$route->addRoute('/fonction/edition/{param}', [$fonctionController, 'edition']);
$route->addRoute('/fonction/formulaire', [$fonctionController, 'formulaire']);

// Module: cycle (CycleController)
$route->addRoute('/cycle/list', [$cycleController, 'list']);
$route->addRoute('/cycle/apiList', [$cycleController, 'apiList']);
$route->addRoute('/cycle/add', [$cycleController, 'add']);
$route->addRoute('/cycle/edit', [$cycleController, 'edit']);
$route->addRoute('/cycle/changer', [$cycleController, 'changer']);
$route->addRoute('/cycle/details/{param}', [$cycleController, 'details']);
$route->addRoute('/cycle/edition/{param}', [$cycleController, 'edition']);
$route->addRoute('/cycle/formulaire', [$cycleController, 'formulaire']);

// Module: filiere (FiliereController)
$route->addRoute('/filiere/list', [$filiereController, 'list']);
$route->addRoute('/filiere/apiList', [$filiereController, 'apiList']);
$route->addRoute('/filiere/add', [$filiereController, 'add']);
$route->addRoute('/filiere/edit', [$filiereController, 'edit']);
$route->addRoute('/filiere/changer', [$filiereController, 'changer']);
$route->addRoute('/filiere/details/{param}', [$filiereController, 'details']);
$route->addRoute('/filiere/edition/{param}', [$filiereController, 'edition']);
$route->addRoute('/filiere/formulaire', [$filiereController, 'formulaire']);

// Module: filiere_cycle (FiliereCycleController)
$route->addRoute('/filiere_cycle/list', [$filiereCycleController, 'list']);
$route->addRoute('/filiere_cycle/apiList', [$filiereCycleController, 'apiList']);
$route->addRoute('/filiere_cycle/add', [$filiereCycleController, 'add']);
$route->addRoute('/filiere_cycle/edit', [$filiereCycleController, 'edit']);
$route->addRoute('/filiere_cycle/changer', [$filiereCycleController, 'changer']);
$route->addRoute('/filiere_cycle/details/{param}', [$filiereCycleController, 'details']);
$route->addRoute('/filiere_cycle/edition/{param}', [$filiereCycleController, 'edition']);
$route->addRoute('/filiere_cycle/formulaire', [$filiereCycleController, 'formulaire']);

// Module: niveau (NiveauController)
$route->addRoute('/niveau/list', [$niveauController, 'list']);
$route->addRoute('/niveau/apiList', [$niveauController, 'apiList']);
$route->addRoute('/niveau/add', [$niveauController, 'add']);
$route->addRoute('/niveau/edit', [$niveauController, 'edit']);
$route->addRoute('/niveau/changer', [$niveauController, 'changer']);
$route->addRoute('/niveau/details/{param}', [$niveauController, 'details']);
$route->addRoute('/niveau/edition/{param}', [$niveauController, 'edition']);
$route->addRoute('/niveau/formulaire', [$niveauController, 'formulaire']);

// Module: filiere_niveau (FiliereNiveauController)
$route->addRoute('/filiere_niveau/list', [$filiereNiveauController, 'list']);
$route->addRoute('/filiere_niveau/apiList', [$filiereNiveauController, 'apiList']);
$route->addRoute('/filiere_niveau/add', [$filiereNiveauController, 'add']);
$route->addRoute('/filiere_niveau/edit', [$filiereNiveauController, 'edit']);
$route->addRoute('/filiere_niveau/changer', [$filiereNiveauController, 'changer']);
$route->addRoute('/filiere_niveau/details/{param}', [$filiereNiveauController, 'details']);
$route->addRoute('/filiere_niveau/edition/{param}', [$filiereNiveauController, 'edition']);
$route->addRoute('/filiere_niveau/formulaire', [$filiereNiveauController, 'formulaire']);

// Module: salle (SalleController)
$route->addRoute('/salle/list', [$salleController, 'list']);
$route->addRoute('/salle/apiList', [$salleController, 'apiList']);
$route->addRoute('/salle/add', [$salleController, 'add']);
$route->addRoute('/salle/edit', [$salleController, 'edit']);
$route->addRoute('/salle/changer', [$salleController, 'changer']);
$route->addRoute('/salle/details/{param}', [$salleController, 'details']);
$route->addRoute('/salle/edition/{param}', [$salleController, 'edition']);
$route->addRoute('/salle/formulaire', [$salleController, 'formulaire']);

// Module: annee (AnneeController)
$route->addRoute('/annee/list', [$anneeController, 'list']);
$route->addRoute('/annee/apiList', [$anneeController, 'apiList']);
$route->addRoute('/annee/add', [$anneeController, 'add']);
$route->addRoute('/annee/edit', [$anneeController, 'edit']);
$route->addRoute('/annee/changer', [$anneeController, 'changer']);
$route->addRoute('/annee/details/{param}', [$anneeController, 'details']);
$route->addRoute('/annee/edition/{param}', [$anneeController, 'edition']);
$route->addRoute('/annee/formulaire', [$anneeController, 'formulaire']);

// Module: session (SessionController)
$route->addRoute('/session/list', [$sessionController, 'list']);
$route->addRoute('/session/apiList', [$sessionController, 'apiList']);
$route->addRoute('/session/add', [$sessionController, 'add']);
$route->addRoute('/session/edit', [$sessionController, 'edit']);
$route->addRoute('/session/changer', [$sessionController, 'changer']);
$route->addRoute('/session/details/{param}', [$sessionController, 'details']);
$route->addRoute('/session/edition/{param}', [$sessionController, 'edition']);
$route->addRoute('/session/formulaire', [$sessionController, 'formulaire']);

// Module: zone (ZoneController)
$route->addRoute('/zone/list', [$zoneController, 'list']);
$route->addRoute('/zone/apiList', [$zoneController, 'apiList']);
$route->addRoute('/zone/add', [$zoneController, 'add']);
$route->addRoute('/zone/edit', [$zoneController, 'edit']);
$route->addRoute('/zone/changer', [$zoneController, 'changer']);
$route->addRoute('/zone/details/{param}', [$zoneController, 'details']);
$route->addRoute('/zone/edition/{param}', [$zoneController, 'edition']);
$route->addRoute('/zone/formulaire', [$zoneController, 'formulaire']);

// Module: article (ArticleController)
$route->addRoute('/article/list', [$articleController, 'list']);
$route->addRoute('/article/apiList', [$articleController, 'apiList']);
$route->addRoute('/article/add', [$articleController, 'add']);
$route->addRoute('/article/edit', [$articleController, 'edit']);
$route->addRoute('/article/changer', [$articleController, 'changer']);
$route->addRoute('/article/details/{param}', [$articleController, 'details']);
$route->addRoute('/article/edition/{param}', [$articleController, 'edition']);
$route->addRoute('/article/formulaire', [$articleController, 'formulaire']);

// Module: categorie_pack (CategoriePackController)
$route->addRoute('/categorie_pack/list', [$categoriePackController, 'list']);
$route->addRoute('/categorie_pack/apiList', [$categoriePackController, 'apiList']);
$route->addRoute('/categorie_pack/add', [$categoriePackController, 'add']);
$route->addRoute('/categorie_pack/edit', [$categoriePackController, 'edit']);
$route->addRoute('/categorie_pack/changer', [$categoriePackController, 'changer']);
$route->addRoute('/categorie_pack/details/{param}', [$categoriePackController, 'details']);
$route->addRoute('/categorie_pack/edition/{param}', [$categoriePackController, 'edition']);
$route->addRoute('/categorie_pack/formulaire', [$categoriePackController, 'formulaire']);

// Module: pack (PackController)
$route->addRoute('/pack/list', [$packController, 'list']);
$route->addRoute('/pack/apiList', [$packController, 'apiList']);
$route->addRoute('/pack/add', [$packController, 'add']);
$route->addRoute('/pack/edit', [$packController, 'edit']);
$route->addRoute('/pack/changer', [$packController, 'changer']);
$route->addRoute('/pack/details/{param}', [$packController, 'details']);
$route->addRoute('/pack/edition/{param}', [$packController, 'edition']);
$route->addRoute('/pack/formulaire', [$packController, 'formulaire']);

// Module: client (ClientController)
$route->addRoute('/client/list', [$clientController, 'list']);
$route->addRoute('/client/apiList', [$clientController, 'apiList']);
$route->addRoute('/client/add', [$clientController, 'add']);
$route->addRoute('/client/edit', [$clientController, 'edit']);
$route->addRoute('/client/changer', [$clientController, 'changer']);
$route->addRoute('/client/details/{param}', [$clientController, 'details']);
$route->addRoute('/client/edition/{param}', [$clientController, 'edition']);
$route->addRoute('/client/formulaire', [$clientController, 'formulaire']);

// Module: zone_commercial (ZoneCommercialController)
$route->addRoute('/zone_commercial/list', [$zoneCommercialController, 'list']);
$route->addRoute('/zone_commercial/apiList', [$zoneCommercialController, 'apiList']);
$route->addRoute('/zone_commercial/add', [$zoneCommercialController, 'add']);
$route->addRoute('/zone_commercial/edit', [$zoneCommercialController, 'edit']);
$route->addRoute('/zone_commercial/changer', [$zoneCommercialController, 'changer']);
$route->addRoute('/zone_commercial/details/{param}', [$zoneCommercialController, 'details']);
$route->addRoute('/zone_commercial/edition/{param}', [$zoneCommercialController, 'edition']);
$route->addRoute('/zone_commercial/formulaire', [$zoneCommercialController, 'formulaire']);

// Module: souscription (SouscriptionController)
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

// Module: cotisation (CotisationController)
$route->addRoute('/cotisation/list', [$cotisationController, 'list']);
$route->addRoute('/cotisation/apiList', [$cotisationController, 'apiList']);
$route->addRoute('/cotisation/add', [$cotisationController, 'add']);
$route->addRoute('/cotisation/edit', [$cotisationController, 'edit']);
$route->addRoute('/cotisation/changer', [$cotisationController, 'changer']);
$route->addRoute('/cotisation/details/{param}', [$cotisationController, 'details']);
$route->addRoute('/cotisation/edition/{param}', [$cotisationController, 'edition']);
$route->addRoute('/cotisation/formulaire', [$cotisationController, 'formulaire']);

// Module: cautisation_payment (CautisationPaymentController)
$route->addRoute('/cautisation-payment/search-form', [$cautisationPaymentController, 'searchForm']);
$route->addRoute('/cautisation-payment/search', [$cautisationPaymentController, 'search']);
$route->addRoute('/cautisation-payment/situation', [$cautisationPaymentController, 'situation']);
$route->addRoute('/cautisation-payment/situation-details', [$cautisationPaymentController, 'situationDetails']);
$route->addRoute('/cautisation-payment/history', [$cautisationPaymentController, 'history']);
$route->addRoute('/cautisation-payment/savepayment', [$cautisationPaymentController, 'savepayment']);

// Module: distribution (DistributionController)
$route->addRoute('/distribution/list', [$distributionController, 'list']);
$route->addRoute('/distribution/apiList', [$distributionController, 'apiList']);
$route->addRoute('/distribution/add', [$distributionController, 'add']);
$route->addRoute('/distribution/edit', [$distributionController, 'edit']);
$route->addRoute('/distribution/changer', [$distributionController, 'changer']);
$route->addRoute('/distribution/details/{param}', [$distributionController, 'details']);
$route->addRoute('/distribution/edition/{param}', [$distributionController, 'edition']);
$route->addRoute('/distribution/formulaire', [$distributionController, 'formulaire']);

// Module: classe (ClasseController)
$route->addRoute('/classe/list', [$classeController, 'list']);
$route->addRoute('/classe/apiList', [$classeController, 'apiList']);
$route->addRoute('/classe/add', [$classeController, 'add']);
$route->addRoute('/classe/edit', [$classeController, 'edit']);
$route->addRoute('/classe/changer', [$classeController, 'changer']);
$route->addRoute('/classe/details/{param}', [$classeController, 'details']);
$route->addRoute('/classe/edition/{param}', [$classeController, 'edition']);
$route->addRoute('/classe/formulaire', [$classeController, 'formulaire']);

// Module: semestre (SemestreController)
$route->addRoute('/semestre/list', [$semestreController, 'list']);
$route->addRoute('/semestre/apiList', [$semestreController, 'apiList']);
$route->addRoute('/semestre/add', [$semestreController, 'add']);
$route->addRoute('/semestre/edit', [$semestreController, 'edit']);
$route->addRoute('/semestre/changer', [$semestreController, 'changer']);
$route->addRoute('/semestre/details/{param}', [$semestreController, 'details']);
$route->addRoute('/semestre/edition/{param}', [$semestreController, 'edition']);
$route->addRoute('/semestre/formulaire', [$semestreController, 'formulaire']);



// Module: ue / unites_enseignement (UeController)
$route->addRoute('/ue/list', [$ueController, 'list']);
$route->addRoute('/ue/apiList', [$ueController, 'apiList']);
$route->addRoute('/ue/add', [$ueController, 'add']);
$route->addRoute('/ue/edit', [$ueController, 'edit']);
$route->addRoute('/ue/changer', [$ueController, 'changer']);
$route->addRoute('/ue/details/{param}', [$ueController, 'details']);
$route->addRoute('/ue/edition/{param}', [$ueController, 'edition']);
$route->addRoute('/ue/formulaire', [$ueController, 'formulaire']);
$route->addRoute('/unites_enseignement/list', [$ueController, 'list']);
$route->addRoute('/unites_enseignement/apiList', [$ueController, 'apiList']);
$route->addRoute('/unites_enseignement/details/{param}', [$ueController, 'details']);
$route->addRoute('/unites_enseignement/edition/{param}', [$ueController, 'edition']);

// Module: matiere (MatiereController)
$route->addRoute('/matiere/list', [$matiereController, 'list']);
$route->addRoute('/matiere/apiList', [$matiereController, 'apiList']);
$route->addRoute('/matiere/add', [$matiereController, 'add']);
$route->addRoute('/matiere/edit', [$matiereController, 'edit']);
$route->addRoute('/matiere/changer', [$matiereController, 'changer']);
$route->addRoute('/matiere/details/{param}', [$matiereController, 'details']);
$route->addRoute('/matiere/edition/{param}', [$matiereController, 'edition']);
$route->addRoute('/matiere/formulaire', [$matiereController, 'formulaire']);

// Module: scolarite (ScolariteController)
$route->addRoute('/scolarite/list', [$scolariteController, 'list']);
$route->addRoute('/scolarite/apiList', [$scolariteController, 'apiList']);
$route->addRoute('/scolarite/add', [$scolariteController, 'add']);
$route->addRoute('/scolarite/edit', [$scolariteController, 'edit']);
$route->addRoute('/scolarite/changer', [$scolariteController, 'changer']);
$route->addRoute('/scolarite/details/{param}', [$scolariteController, 'details']);
$route->addRoute('/scolarite/edition/{param}', [$scolariteController, 'edition']);
$route->addRoute('/scolarite/formulaire', [$scolariteController, 'formulaire']);

// Module: tranche (TrancheController)
$route->addRoute('/tranche/list', [$trancheController, 'list']);
$route->addRoute('/tranche/apiList', [$trancheController, 'apiList']);
$route->addRoute('/tranche/add', [$trancheController, 'add']);
$route->addRoute('/tranche/edit', [$trancheController, 'edit']);
$route->addRoute('/tranche/changer', [$trancheController, 'changer']);
$route->addRoute('/tranche/details/{param}', [$trancheController, 'details']);
$route->addRoute('/tranche/edition/{param}', [$trancheController, 'edition']);
$route->addRoute('/tranche/formulaire', [$trancheController, 'formulaire']);

// Module: etudiant (EtudiantController)
$route->addRoute('/etudiant/list', [$etudiantController, 'list']);
$route->addRoute('/etudiant/apiList', [$etudiantController, 'apiList']);
$route->addRoute('/etudiant/add', [$etudiantController, 'add']);
$route->addRoute('/etudiant/addWizard', [$etudiantController, 'addWizard']);
$route->addRoute('/etudiant/edit', [$etudiantController, 'edit']);
$route->addRoute('/etudiant/changer', [$etudiantController, 'changer']);
$route->addRoute('/etudiant/details/{param}', [$etudiantController, 'details']);
$route->addRoute('/etudiant/edition/{param}', [$etudiantController, 'edition']);
$route->addRoute('/etudiant/formulaire', [$etudiantController, 'formulaire']);
$route->addRoute('/etudiant/wizard', [$etudiantController, 'wizard']);

// Module: parent (ParentController)
$route->addRoute('/parent/list', [$parentController, 'list']);
$route->addRoute('/parent/apiList', [$parentController, 'apiList']);
$route->addRoute('/parent/add', [$parentController, 'add']);
$route->addRoute('/parent/edit', [$parentController, 'edit']);
$route->addRoute('/parent/changer', [$parentController, 'changer']);
$route->addRoute('/parent/details/{param}', [$parentController, 'details']);
$route->addRoute('/parent/edition/{param}', [$parentController, 'edition']);
$route->addRoute('/parent/formulaire', [$parentController, 'formulaire']);

// Module: inscription (InscriptionController)
$route->addRoute('/inscription/list', [$inscriptionController, 'list']);
$route->addRoute('/inscription/apiList', [$inscriptionController, 'apiList']);
$route->addRoute('/inscription/getStudentProfileSummary', [$inscriptionController, 'getStudentProfileSummary']);
$route->addRoute('/inscription/getTuitionByClass', [$inscriptionController, 'getTuitionByClass']);
$route->addRoute('/inscription/add', [$inscriptionController, 'add']);
$route->addRoute('/inscription/edit', [$inscriptionController, 'edit']);
$route->addRoute('/inscription/changer', [$inscriptionController, 'changer']);
$route->addRoute('/inscription/details/{param}', [$inscriptionController, 'details']);
$route->addRoute('/inscription/edition/{param}', [$inscriptionController, 'edition']);
$route->addRoute('/inscription/formulaire', [$inscriptionController, 'formulaire']);

// Module: accessoire (AccessoireController)
$route->addRoute('/accessoire/list', [$accessoireController, 'list']);
$route->addRoute('/accessoire/apiList', [$accessoireController, 'apiList']);
$route->addRoute('/accessoire/apiDistributions', [$accessoireController, 'apiDistributions']);
$route->addRoute('/accessoire/apiStats', [$accessoireController, 'apiStats']);
$route->addRoute('/accessoire/toggleRetrait', [$accessoireController, 'toggleRetrait']);
$route->addRoute('/accessoire/attribuerKit', [$accessoireController, 'attribuerKit']);
$route->addRoute('/accessoire/getStudentKits', [$accessoireController, 'getStudentKits']);
$route->addRoute('/accessoire/add', [$accessoireController, 'add']);
$route->addRoute('/accessoire/edit', [$accessoireController, 'edit']);
$route->addRoute('/accessoire/changer', [$accessoireController, 'changer']);
$route->addRoute('/accessoire/details/{param}', [$accessoireController, 'details']);
$route->addRoute('/accessoire/edition/{param}', [$accessoireController, 'edition']);
$route->addRoute('/accessoire/formulaire', [$accessoireController, 'formulaire']);

// Module: paiement (PaiementController)
$route->addRoute('/paiement/list', [$paiementController, 'list']);
$route->addRoute('/paiement/apiList', [$paiementController, 'apiList']);
$route->addRoute('/paiement/getStudentFinancialSummary', [$paiementController, 'getStudentFinancialSummary']);
$route->addRoute('/paiement/add', [$paiementController, 'add']);
$route->addRoute('/paiement/edit', [$paiementController, 'edit']);
$route->addRoute('/paiement/changer', [$paiementController, 'changer']);
$route->addRoute('/paiement/details/{param}', [$paiementController, 'details']);
$route->addRoute('/paiement/edition/{param}', [$paiementController, 'edition']);
$route->addRoute('/paiement/formulaire', [$paiementController, 'formulaire']);

// Module: ouverture_caisse (OuvertureCaisseController)
$route->addRoute('/ouverture_caisse/list', [$ouvertureCaisseController, 'list']);
$route->addRoute('/ouverture_caisse/apiList', [$ouvertureCaisseController, 'apiList']);
$route->addRoute('/ouverture_caisse/add', [$ouvertureCaisseController, 'add']);
$route->addRoute('/ouverture_caisse/edit', [$ouvertureCaisseController, 'edit']);
$route->addRoute('/ouverture_caisse/changer', [$ouvertureCaisseController, 'changer']);
$route->addRoute('/ouverture_caisse/details/{param}', [$ouvertureCaisseController, 'details']);
$route->addRoute('/ouverture_caisse/edition/{param}', [$ouvertureCaisseController, 'edition']);
$route->addRoute('/ouverture_caisse/formulaire', [$ouvertureCaisseController, 'formulaire']);

// Module: cloture_caisse (ClotureCaisseController)
$route->addRoute('/cloture_caisse/list', [$clotureCaisseController, 'list']);
$route->addRoute('/cloture_caisse/apiList', [$clotureCaisseController, 'apiList']);
$route->addRoute('/cloture_caisse/getDailyTotals', [$clotureCaisseController, 'getDailyTotals']);
$route->addRoute('/cloture_caisse/add', [$clotureCaisseController, 'add']);
$route->addRoute('/cloture_caisse/edit', [$clotureCaisseController, 'edit']);
$route->addRoute('/cloture_caisse/changer', [$clotureCaisseController, 'changer']);
$route->addRoute('/cloture_caisse/details/{param}', [$clotureCaisseController, 'details']);
$route->addRoute('/cloture_caisse/edition/{param}', [$clotureCaisseController, 'edition']);
$route->addRoute('/cloture_caisse/formulaire', [$clotureCaisseController, 'formulaire']);

// Module: impayes (ImpayesController)
$route->addRoute('/impayes/list', [$impayesController, 'list']);
$route->addRoute('/impayes/apiList', [$impayesController, 'apiList']);
$route->addRoute('/impayes/add', [$impayesController, 'add']);
$route->addRoute('/impayes/edit', [$impayesController, 'edit']);
$route->addRoute('/impayes/changer', [$impayesController, 'changer']);
$route->addRoute('/impayes/details/{param}', [$impayesController, 'details']);
$route->addRoute('/impayes/edition/{param}', [$impayesController, 'edition']);
$route->addRoute('/impayes/formulaire', [$impayesController, 'formulaire']);

// Module: type_depense (TypeDepenseController)
$route->addRoute('/type_depense/list', [$typeDepenseController, 'list']);
$route->addRoute('/type_depense/apiList', [$typeDepenseController, 'apiList']);
$route->addRoute('/type_depense/add', [$typeDepenseController, 'add']);
$route->addRoute('/type_depense/edit', [$typeDepenseController, 'edit']);
$route->addRoute('/type_depense/changer', [$typeDepenseController, 'changer']);
$route->addRoute('/type_depense/details/{param}', [$typeDepenseController, 'details']);
$route->addRoute('/type_depense/edition/{param}', [$typeDepenseController, 'edition']);
$route->addRoute('/type_depense/formulaire', [$typeDepenseController, 'formulaire']);

// Module: depense (DepenseController)
$route->addRoute('/depense/list', [$depenseController, 'list']);
$route->addRoute('/depense/apiList', [$depenseController, 'apiList']);
$route->addRoute('/depense/add', [$depenseController, 'add']);
$route->addRoute('/depense/edit', [$depenseController, 'edit']);
$route->addRoute('/depense/changer', [$depenseController, 'changer']);
$route->addRoute('/depense/details/{param}', [$depenseController, 'details']);
$route->addRoute('/depense/edition/{param}', [$depenseController, 'edition']);
$route->addRoute('/depense/formulaire', [$depenseController, 'formulaire']);

// Module: enseignant (EnseignantController)
$route->addRoute('/enseignant/list', [$enseignantController, 'list']);
$route->addRoute('/enseignant/apiList', [$enseignantController, 'apiList']);
$route->addRoute('/enseignant/add', [$enseignantController, 'add']);
$route->addRoute('/enseignant/edit', [$enseignantController, 'edit']);
$route->addRoute('/enseignant/changer', [$enseignantController, 'changer']);
$route->addRoute('/enseignant/details/{param}', [$enseignantController, 'details']);
$route->addRoute('/enseignant/edition/{param}', [$enseignantController, 'edition']);
$route->addRoute('/enseignant/formulaire', [$enseignantController, 'formulaire']);

// Module: enseignant_matiere (EnseignantMatiereController)
$route->addRoute('/enseignant_matiere/list', [$enseignantMatiereController, 'list']);
$route->addRoute('/enseignant_matiere/apiList', [$enseignantMatiereController, 'apiList']);
$route->addRoute('/enseignant_matiere/add', [$enseignantMatiereController, 'add']);
$route->addRoute('/enseignant_matiere/edit', [$enseignantMatiereController, 'edit']);
$route->addRoute('/enseignant_matiere/changer', [$enseignantMatiereController, 'changer']);
$route->addRoute('/enseignant_matiere/details/{param}', [$enseignantMatiereController, 'details']);
$route->addRoute('/enseignant_matiere/edition/{param}', [$enseignantMatiereController, 'edition']);
$route->addRoute('/enseignant_matiere/formulaire', [$enseignantMatiereController, 'formulaire']);

// Module: emploi (EmploiController)
$route->addRoute('/emploi/list', [$emploiController, 'list']);
$route->addRoute('/emploi/apiList', [$emploiController, 'apiList']);
$route->addRoute('/emploi/getAssignedTeacher', [$emploiController, 'getAssignedTeacher']);
$route->addRoute('/emploi/checkScheduleConflicts', [$emploiController, 'checkScheduleConflicts']);
$route->addRoute('/emploi/add', [$emploiController, 'add']);
$route->addRoute('/emploi/edit', [$emploiController, 'edit']);
$route->addRoute('/emploi/changer', [$emploiController, 'changer']);
$route->addRoute('/emploi/details/{param}', [$emploiController, 'details']);
$route->addRoute('/emploi/edition/{param}', [$emploiController, 'edition']);
$route->addRoute('/emploi/formulaire', [$emploiController, 'formulaire']);

// Module: absence (AbsenceController)
$route->addRoute('/absence/list', [$absenceController, 'list']);
$route->addRoute('/absence/apiList', [$absenceController, 'apiList']);
$route->addRoute('/absence/add', [$absenceController, 'add']);
$route->addRoute('/absence/edit', [$absenceController, 'edit']);
$route->addRoute('/absence/changer', [$absenceController, 'changer']);
$route->addRoute('/absence/details/{param}', [$absenceController, 'details']);
$route->addRoute('/absence/edition/{param}', [$absenceController, 'edition']);
$route->addRoute('/absence/formulaire', [$absenceController, 'formulaire']);

// Module: note (NoteController)
$route->addRoute('/note/list', [$noteController, 'list']);
$route->addRoute('/note/apiList', [$noteController, 'apiList']);
$route->addRoute('/note/add', [$noteController, 'add']);
$route->addRoute('/note/edit', [$noteController, 'edit']);
$route->addRoute('/note/changer', [$noteController, 'changer']);
$route->addRoute('/note/details/{param}', [$noteController, 'details']);
$route->addRoute('/note/edition/{param}', [$noteController, 'edition']);
$route->addRoute('/note/formulaire', [$noteController, 'formulaire']);

// Module: bulletin (BulletinController)
$route->addRoute('/bulletin/list', [$bulletinController, 'list']);
$route->addRoute('/bulletin/apiList', [$bulletinController, 'apiList']);
$route->addRoute('/bulletin/add', [$bulletinController, 'add']);
$route->addRoute('/bulletin/edit', [$bulletinController, 'edit']);
$route->addRoute('/bulletin/changer', [$bulletinController, 'changer']);
$route->addRoute('/bulletin/details/{param}', [$bulletinController, 'details']);
$route->addRoute('/bulletin/edition/{param}', [$bulletinController, 'edition']);
$route->addRoute('/bulletin/formulaire', [$bulletinController, 'formulaire']);

// Module: evenement (EvenementController)
$route->addRoute('/evenement/list', [$evenementController, 'list']);
$route->addRoute('/evenement/apiList', [$evenementController, 'apiList']);
$route->addRoute('/evenement/add', [$evenementController, 'add']);
$route->addRoute('/evenement/edit', [$evenementController, 'edit']);
$route->addRoute('/evenement/changer', [$evenementController, 'changer']);
$route->addRoute('/evenement/details/{param}', [$evenementController, 'details']);
$route->addRoute('/evenement/edition/{param}', [$evenementController, 'edition']);
$route->addRoute('/evenement/formulaire', [$evenementController, 'formulaire']);

// Module: galerie (GalerieController)
$route->addRoute('/galerie/list', [$galerieController, 'list']);
$route->addRoute('/galerie/apiList', [$galerieController, 'apiList']);
$route->addRoute('/galerie/add', [$galerieController, 'add']);
$route->addRoute('/galerie/edit', [$galerieController, 'edit']);
$route->addRoute('/galerie/changer', [$galerieController, 'changer']);
$route->addRoute('/galerie/details/{param}', [$galerieController, 'details']);
$route->addRoute('/galerie/edition/{param}', [$galerieController, 'edition']);
$route->addRoute('/galerie/formulaire', [$galerieController, 'formulaire']);

// Module: document (DocumentController)
$route->addRoute('/document/list', [$documentController, 'list']);
$route->addRoute('/document/apiList', [$documentController, 'apiList']);
$route->addRoute('/document/add', [$documentController, 'add']);
$route->addRoute('/document/edit', [$documentController, 'edit']);
$route->addRoute('/document/changer', [$documentController, 'changer']);
$route->addRoute('/document/details/{param}', [$documentController, 'details']);
$route->addRoute('/document/edition/{param}', [$documentController, 'edition']);
$route->addRoute('/document/formulaire', [$documentController, 'formulaire']);

// Module: role (RoleController)
$route->addRoute('/role/list', [$roleController, 'list']);
$route->addRoute('/role/apiList', [$roleController, 'apiList']);
$route->addRoute('/role/add', [$roleController, 'add']);
$route->addRoute('/role/edit', [$roleController, 'edit']);
$route->addRoute('/role/changer', [$roleController, 'changer']);
$route->addRoute('/role/details/{param}', [$roleController, 'details']);
$route->addRoute('/role/edition/{param}', [$roleController, 'edition']);
$route->addRoute('/role/formulaire', [$roleController, 'formulaire']);

// Module: permission (PermissionController)
$route->addRoute('/permission/list', [$permissionController, 'list']);
$route->addRoute('/permission/apiList', [$permissionController, 'apiList']);
$route->addRoute('/permission/add', [$permissionController, 'add']);
$route->addRoute('/permission/edit', [$permissionController, 'edit']);
$route->addRoute('/permission/changer', [$permissionController, 'changer']);
$route->addRoute('/permission/details/{param}', [$permissionController, 'details']);
$route->addRoute('/permission/edition/{param}', [$permissionController, 'edition']);
$route->addRoute('/permission/formulaire', [$permissionController, 'formulaire']);
$route->addRoute('/permission/addModule', [$permissionController, 'addModule']);

// Module: notification (NotificationController)
$route->addRoute('/notification/list', [$notificationController, 'list']);
$route->addRoute('/notification/apiList', [$notificationController, 'apiList']);
$route->addRoute('/notification/add', [$notificationController, 'add']);
$route->addRoute('/notification/edit', [$notificationController, 'edit']);
$route->addRoute('/notification/changer', [$notificationController, 'changer']);
$route->addRoute('/notification/details/{param}', [$notificationController, 'details']);
$route->addRoute('/notification/edition/{param}', [$notificationController, 'edition']);
$route->addRoute('/notification/formulaire', [$notificationController, 'formulaire']);

// Extraction & Exécution de l'URL
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
