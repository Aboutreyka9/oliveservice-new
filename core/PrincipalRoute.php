<?php

require_once __DIR__ . '/../config/Database.php';

require_once __DIR__ . '/../models/Validator.php';
require_once __DIR__ . '/../core/PressingAware.php';
require_once __DIR__ . '/../core/NotificationService.php';
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../core/BaseModel.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Context.php';

// GEICG & Core Models
require_once __DIR__ . '/../models/home/ModelHome.php';
require_once __DIR__ . '/../models/users/ModelUser.php';
require_once __DIR__ . '/../models/roles/ModelRole.php';
require_once __DIR__ . '/../models/permissions/ModelPermission.php';
require_once __DIR__ . '/../models/modules_metier/ModelModuleMetier.php';
require_once __DIR__ . '/../models/notifications/ModelNotification.php';

// School Structure & Academic Models
require_once __DIR__ . '/../models/etablissements/ModelEtablissement.php';
require_once __DIR__ . '/../models/cycles/ModelCycle.php';
require_once __DIR__ . '/../models/filieres/ModelFiliere.php';
require_once __DIR__ . '/../models/niveaux/ModelNiveau.php';
require_once __DIR__ . '/../models/salles/ModelSalle.php';
require_once __DIR__ . '/../models/articles/ModelArticle.php';
require_once __DIR__ . '/../controllers/articles/ArticleController.php';
require_once __DIR__ . '/../models/categorie_packs/ModelCategoriePack.php';
require_once __DIR__ . '/../controllers/categorie_packs/CategoriePackController.php';
require_once __DIR__ . '/../models/clients/ModelClient.php';
require_once __DIR__ . '/../controllers/clients/ClientController.php';
require_once __DIR__ . '/../models/zone_commercials/ModelZoneCommercial.php';
require_once __DIR__ . '/../controllers/zone_commercials/ZoneCommercialController.php';
require_once __DIR__ . '/../models/souscriptions/ModelSouscription.php';
require_once __DIR__ . '/../controllers/souscriptions/SouscriptionController.php';
require_once __DIR__ . '/../models/cotisations/ModelCotisation.php';
require_once __DIR__ . '/../controllers/cotisations/CotisationController.php';
require_once __DIR__ . '/../models/distributions/ModelDistribution.php';
require_once __DIR__ . '/../controllers/distributions/DistributionController.php';
require_once __DIR__ . '/../models/type_depenses/ModelTypeDepense.php';
require_once __DIR__ . '/../controllers/type_depenses/TypeDepenseController.php';
require_once __DIR__ . '/../models/depenses/ModelDepense.php';
require_once __DIR__ . '/../controllers/depenses/DepenseController.php';
require_once __DIR__ . '/../models/versements/ModelVersement.php';
require_once __DIR__ . '/../controllers/versements/VersementController.php';
require_once __DIR__ . '/../models/packs/ModelPack.php';
require_once __DIR__ . '/../controllers/packs/PackController.php';
require_once __DIR__ . '/../models/annees/ModelAnnee.php';
require_once __DIR__ . '/../models/sessions/ModelSession.php';
require_once __DIR__ . '/../models/zones/ModelZone.php';
require_once __DIR__ . '/../controllers/sessions/SessionController.php';
require_once __DIR__ . '/../controllers/zones/ZoneController.php';
require_once __DIR__ . '/../models/classes/ModelClasse.php';
require_once __DIR__ . '/../models/semestres/ModelSemestre.php';
require_once __DIR__ . '/../models/unites_enseignement/ModelUe.php';
require_once __DIR__ . '/../models/matieres/ModelMatiere.php';

// Scolarité & Finances Models
require_once __DIR__ . '/../models/scolarites/ModelScolarite.php';
require_once __DIR__ . '/../models/tranches_scolarite/ModelTranche.php';
require_once __DIR__ . '/../models/paiements/ModelPaiement.php';
require_once __DIR__ . '/../models/ModelOuvertureCaisse.php';
require_once __DIR__ . '/../models/clotures_caisse/ModelClotureCaisse.php';
require_once __DIR__ . '/../models/type_depenses/ModelTypeDepense.php';
require_once __DIR__ . '/../models/depenses/ModelDepense.php';

// Admissions, Élèves & Parents Models
require_once __DIR__ . '/../models/etudiants/ModelEtudiant.php';
require_once __DIR__ . '/../models/parents/ModelParent.php';
require_once __DIR__ . '/../models/inscriptions/ModelInscription.php';
require_once __DIR__ . '/../models/accessoires/ModelAccessoire.php';

// Pédagogie, Évaluations & Suivi Models
require_once __DIR__ . '/../models/enseignants/ModelEnseignant.php';
require_once __DIR__ . '/../models/emplois_temps/ModelEmploi.php';
require_once __DIR__ . '/../models/absences/ModelAbsence.php';
require_once __DIR__ . '/../models/notes/ModelNote.php';

// Médias & Communication Models
require_once __DIR__ . '/../models/evenements/ModelEvenement.php';
require_once __DIR__ . '/../models/galeries/ModelGalerie.php';
require_once __DIR__ . '/../models/documents/ModelDocument.php';

// GEICG & Core Controllers
require_once __DIR__ . '/../controllers/home/HomeController.php';
require_once __DIR__ . '/../controllers/users/UserController.php';
require_once __DIR__ . '/../controllers/roles/RoleController.php';
require_once __DIR__ . '/../controllers/permissions/PermissionController.php';
require_once __DIR__ . '/../controllers/notifications/NotificationController.php';

// School Structure & Academic Controllers
require_once __DIR__ . '/../controllers/etablissements/EtablissementController.php';
require_once __DIR__ . '/../controllers/cycles/CycleController.php';
require_once __DIR__ . '/../controllers/filieres/FiliereController.php';
require_once __DIR__ . '/../controllers/niveaux/NiveauController.php';
require_once __DIR__ . '/../controllers/salles/SalleController.php';
require_once __DIR__ . '/../controllers/annees/AnneeController.php';
require_once __DIR__ . '/../controllers/classes/ClasseController.php';
require_once __DIR__ . '/../controllers/semestres/SemestreController.php';
require_once __DIR__ . '/../controllers/unites_enseignement/UeController.php';
require_once __DIR__ . '/../controllers/matieres/MatiereController.php';

// Scolarité & Finances Controllers
require_once __DIR__ . '/../controllers/scolarites/ScolariteController.php';
require_once __DIR__ . '/../controllers/tranches_scolarite/TrancheController.php';
require_once __DIR__ . '/../controllers/paiements/PaiementController.php';
require_once __DIR__ . '/../controllers/ouvertures_caisse/OuvertureCaisseController.php';
require_once __DIR__ . '/../controllers/clotures_caisse/ClotureCaisseController.php';
require_once __DIR__ . '/../controllers/type_depenses/TypeDepenseController.php';
require_once __DIR__ . '/../controllers/depenses/DepenseController.php';

// Admissions, Élèves & Parents Controllers
require_once __DIR__ . '/../controllers/etudiants/EtudiantController.php';
require_once __DIR__ . '/../controllers/parents/ParentController.php';
require_once __DIR__ . '/../controllers/inscriptions/InscriptionController.php';
require_once __DIR__ . '/../controllers/accessoires/AccessoireController.php';

// Pédagogie, Évaluations & Suivi Controllers
require_once __DIR__ . '/../controllers/enseignants/EnseignantController.php';
require_once __DIR__ . '/../controllers/emplois_temps/EmploiController.php';
require_once __DIR__ . '/../controllers/absences/AbsenceController.php';
require_once __DIR__ . '/../controllers/notes/NoteController.php';

// Médias & Communication Controllers
require_once __DIR__ . '/../controllers/evenements/EvenementController.php';
require_once __DIR__ . '/../controllers/galeries/GalerieController.php';
require_once __DIR__ . '/../controllers/documents/DocumentController.php';

// require_once __DIR__ . '/../models/services/ModelService.php';
// require_once __DIR__ . '/../controllers/services/ServiceController.php';

require_once __DIR__ . '/../models/fonctions/ModelFonction.php';
require_once __DIR__ . '/../models/impayes/ModelImpayes.php';
require_once __DIR__ . '/../models/enseignant_matiere/ModelEnseignantMatiere.php';
require_once __DIR__ . '/../models/bulletin/ModelBulletin.php';

require_once __DIR__ . '/../controllers/fonctions/FonctionController.php';
require_once __DIR__ . '/../controllers/impayes/ImpayesController.php';
require_once __DIR__ . '/../controllers/enseignant_matiere/EnseignantMatiereController.php';
require_once __DIR__ . '/../controllers/bulletin/BulletinController.php';

require_once __DIR__ . '/../models/ModelFiliereCycle.php';
require_once __DIR__ . '/../controllers/filiere_cycles/FiliereCycleController.php';

require_once __DIR__ . '/../models/ModelFiliereNiveau.php';
require_once __DIR__ . '/../controllers/filiere_niveaux/FiliereNiveauController.php';



