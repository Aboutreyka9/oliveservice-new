<?php

class InscriptionController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelInscription();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/inscriptions/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_inscription'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function getStudentProfileSummary()
    {
        $this->requireAuth();
        $etudiantCode = trim($_GET['etudiant_code'] ?? ($_POST['etudiant_code'] ?? ''));

        if (empty($etudiantCode)) {
            $this->json(['status' => 0, 'message' => 'Code étudiant requis']);
            return;
        }

        $db = $this->model->getCon();

        // 1. Récupération des informations personnelles de l'étudiant et de ses parents
        $stmt = $db->prepare("
            SELECT e.*, 
                   p.nom_pere, p.telephone_pere, p.profession_pere,
                   p.nom_mere, p.telephone_mere, p.profession_mere,
                   p.nom_tuteur, p.telephone_tuteur
            FROM etudiants e
            LEFT JOIN parents p ON (p.etudiant_code = e.code_etudiant OR p.etudiant_code = e.matricule_etudiant)
            WHERE e.code_etudiant = ? OR e.matricule_etudiant = ? OR e.id_etudiant = ?
            LIMIT 1
        ");
        $stmt->execute([$etudiantCode, $etudiantCode, is_numeric($etudiantCode) ? (int)$etudiantCode : 0]);
        $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$etudiant) {
            $this->json(['status' => 0, 'message' => 'Étudiant introuvable']);
            return;
        }

        // 2. Recherche complète de l'historique de l'inscription précédente (N-1)
        $stmtPrev = $db->prepare("
            SELECT i.*, 
                   c.libelle_classe, f.libelle_filiere, n.libelle_niveau,
                   a.libelle_annee
            FROM inscriptions i
            LEFT JOIN classes c ON c.code_classe = i.classe_code
            LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
            LEFT JOIN niveaux n ON n.code_niveau = c.niveau_code
            LEFT JOIN annees a ON (a.code_annee = i.annee_code OR a.id_annee = i.annee_code)
            WHERE i.etudiant_code = ?
            ORDER BY i.id_inscription DESC
            LIMIT 1
        ");
        $stmtPrev->execute([$etudiant['code_etudiant']]);
        $prevIns = $stmtPrev->fetch(PDO::FETCH_ASSOC);

        $prevDue = 0;
        $prevPaye = 0;
        $prevSolde = 0;
        $hasHistory = false;

        if ($prevIns) {
            $hasHistory = true;
            $prevDue = (float)($prevIns['montant_scolarite_inscription'] ?? 0);
            
            // Calcul des paiements de l'inscription précédente
            $stmtP = $db->prepare("
                SELECT SUM(montant_paiement) as total_paye 
                FROM paiements 
                WHERE inscription_code = ? AND statut_paiement != 'annule'
            ");
            $stmtP->execute([$prevIns['code_inscription']]);
            $rowP = $stmtP->fetch(PDO::FETCH_ASSOC);
            $prevPaye = (float)($rowP['total_paye'] ?? 0);
            $prevSolde = max(0, $prevDue - $prevPaye);
        }

        $nomComplet = trim(($etudiant['nom_etudiant'] ?? '') . ' ' . ($etudiant['prenom_etudiant'] ?? ''));
        $dateNaissFmt = !empty($etudiant['date_naissance_etudiant']) ? date('d/m/Y', strtotime($etudiant['date_naissance_etudiant'])) : 'Non renseignée';

        // Identification du parent référent
        $parentNom = $etudiant['nom_tuteur'] ?: ($etudiant['nom_pere'] ?: ($etudiant['nom_mere'] ?: 'Non renseigné'));
        $parentTel = $etudiant['telephone_tuteur'] ?: ($etudiant['telephone_pere'] ?: ($etudiant['telephone_mere'] ?: 'Non renseigné'));
        $parentRole = $etudiant['nom_tuteur'] ? 'Tuteur' : ($etudiant['nom_pere'] ? 'Père' : ($etudiant['nom_mere'] ? 'Mère' : 'Parent'));
        $parentProf = $etudiant['profession_pere'] ?: ($etudiant['profession_mere'] ?: 'Non renseignée');

        $this->json([
            'status' => 1,
            'data' => [
                'code_etudiant' => $etudiant['code_etudiant'],
                'matricule' => $etudiant['matricule_etudiant'] ?? '-',
                'nom_famille' => $etudiant['nom_etudiant'] ?? '',
                'prenom' => $etudiant['prenom_etudiant'] ?? '',
                'nom_complet' => $nomComplet,
                'telephone' => $etudiant['telephone_etudiant'] ?? 'Non renseigné',
                'email' => $etudiant['email_etudiant'] ?? 'Non renseigné',
                'date_naissance' => $dateNaissFmt,
                'lieu_naissance' => $etudiant['lieu_naissance_etudiant'] ?? 'Non renseigné',
                'sexe' => $etudiant['sexe_etudiant'] ?? 'M',
                'nationalite' => $etudiant['nationalite_etudiant'] ?? 'Ivoirienne',
                'residence' => $etudiant['lieu_residence_etudiant'] ?? 'Non renseigné',
                'parent_nom' => $parentNom,
                'parent_tel' => $parentTel,
                'parent_role' => $parentRole,
                'parent_profession' => $parentProf,
                'nom_pere' => $etudiant['nom_pere'] ?? '',
                'telephone_pere' => $etudiant['telephone_pere'] ?? '',
                'nom_mere' => $etudiant['nom_mere'] ?? '',
                'telephone_mere' => $etudiant['telephone_mere'] ?? '',
                'nom_tuteur' => $etudiant['nom_tuteur'] ?? '',
                'telephone_tuteur' => $etudiant['telephone_tuteur'] ?? '',
                'has_history' => $hasHistory,
                'derniere_filiere' => $prevIns['libelle_filiere'] ?? 'Non définie',
                'dernier_niveau' => $prevIns['libelle_niveau'] ?? 'Non défini',
                'derniere_classe' => $prevIns['libelle_classe'] ?? 'Nouvel inscrit',
                'derniere_classe_code' => $prevIns['classe_code'] ?? '',
                'dernier_niveau_code' => $prevIns['niveau_code'] ?? '',
                'derniere_filiere_code' => $prevIns['filiere_code'] ?? '',
                'derniere_annee' => $prevIns['libelle_annee'] ?? '',
                'prev_regime' => ($prevIns['affectation_etat'] ?? '') === 'affecte' ? 'Affecté (État)' : 'Non Affecté (Privé)',
                'prev_scolarite' => $prevDue,
                'prev_paye' => $prevPaye,
                'prev_solde' => $prevSolde,
                'statut_etudiant' => $etudiant['statut_etudiant'] ?? 'actif',
                'accessoires_etudiant' => (function() use ($db, $etudiantCode) {
                    $stmt = $db->prepare("
                        SELECT a.code_accessoire, a.libelle_accessoire, COALESCE(ai.statut_accessoire_inscription, 'actif') as statut
                        FROM accessoire_inscription ai
                        JOIN inscriptions i ON i.code_inscription = ai.inscription_code
                        JOIN accessoires a ON a.code_accessoire = ai.accessoire_code
                        WHERE i.etudiant_code = ?
                        ORDER BY ai.id_accessoire_inscription DESC
                    ");
                    $stmt->execute([$etudiantCode]);
                    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (empty($res)) {
                        // Récupérer les kits/accessoires actifs configurés dans l'établissement
                        $stmtAll = $db->query("SELECT code_accessoire, libelle_accessoire, 'disponible' as statut FROM accessoires WHERE statut_accessoire = 'actif'");
                        $res = $stmtAll ? $stmtAll->fetchAll(PDO::FETCH_ASSOC) : [];
                    }
                    return $res;
                })()
            ]
        ]);
    }

    public function getTuitionByClass()
    {
        $this->requireAuth();
        $classeCode = trim($_GET['classe_code'] ?? ($_POST['classe_code'] ?? ''));

        if (empty($classeCode)) {
            $this->json(['status' => 0, 'message' => 'Classe non spécifiée']);
            return;
        }

        $db = $this->model->getCon();

        // Récupérer la classe avec ses libellés filière et niveau
        $stmtCl = $db->prepare("
            SELECT c.*, f.libelle_filiere, n.libelle_niveau 
            FROM classes c
            LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
            LEFT JOIN niveaux n ON n.code_niveau = c.niveau_code
            WHERE c.code_classe = ? 
            LIMIT 1
        ");
        $stmtCl->execute([$classeCode]);
        $classe = $stmtCl->fetch(PDO::FETCH_ASSOC);

        if (!$classe) {
            $this->json(['status' => 0, 'message' => 'Classe introuvable']);
            return;
        }

        // Trouver le tarif de scolarité (par filiere/niveau ou filiere)
        $stmtSco = $db->prepare("
            SELECT * FROM scolarites 
            WHERE (filiere_code = ? AND (niveau_code = ? OR niveau_code = '' OR niveau_code IS NULL))
               OR (filiere_code = ? OR niveau_code = ?)
            ORDER BY (CASE WHEN filiere_code = ? AND niveau_code = ? THEN 1 ELSE 2 END), id_scolarite DESC
            LIMIT 1
        ");
        $stmtSco->execute([$classe['filiere_code'], $classe['niveau_code'], $classe['filiere_code'], $classe['niveau_code'], $classe['filiere_code'], $classe['niveau_code']]);
        $sco = $stmtSco->fetch(PDO::FETCH_ASSOC);

        $montantScolarite = $sco ? (float)$sco['montant_scolarite'] : 0;
        $affectationEtat = $sco['affectation_etat'] ?? 'non_affecte';
        $codeScolarite = $sco['code_scolarite'] ?? '';

        // Récupérer les tranches associées (notamment la 1ère tranche / frais d'inscription)
        $stmtTr = $db->prepare("
            SELECT * FROM tranches_scolarite 
            WHERE (scolarite_code = ? AND scolarite_code != '')
               OR (filiere_code = ? AND niveau_code = ? AND filiere_code != '')
               OR (filiere_code = ? AND filiere_code != '')
               OR (scolarite_code = '' AND filiere_code = '' AND niveau_code = '')
            ORDER BY id_tranche ASC
        ");
        $stmtTr->execute([$codeScolarite, $classe['filiere_code'], $classe['niveau_code'], $classe['filiere_code']]);
        $tranches = $stmtTr->fetchAll(PDO::FETCH_ASSOC);

        $fraisInscription = 0;
        $libellePremiereTranche = '1ere tranche';
        $dateLimiteTranche = '';

        if (!empty($tranches)) {
            $firstTranche = $tranches[0];
            $fraisInscription = (float)$firstTranche['montant_tranche'];
            $libellePremiereTranche = $firstTranche['libelle_tranche'] ?: $libellePremiereTranche;
            $dateLimiteTranche = !empty($firstTranche['date_limite']) ? date('d/m/Y', strtotime($firstTranche['date_limite'])) : '';
        }

        $this->json([
            'status' => 1,
            'data' => [
                'classe_code' => $classe['code_classe'],
                'libelle_classe' => $classe['libelle_classe'],
                'libelle_filiere' => $classe['libelle_filiere'] ?? '',
                'libelle_niveau' => $classe['libelle_niveau'] ?? '',
                'montant_scolarite' => $montantScolarite,
                'frais_inscription' => $fraisInscription,
                'libelle_premiere_tranche' => $libellePremiereTranche,
                'date_limite_tranche' => $dateLimiteTranche,
                'tranches' => $tranches,
                'affectation_etat' => $affectationEtat
            ]
        ]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $userCode = Context::user() ?? '';
        $anneeCode = Context::annee();
        $etabCode = '5454544456';
        $data = $_POST;
        unset($data['csrf_token']);

        $db = $this->model->getCon();
        $modeInscription = $data['mode_inscription'] ?? 'existant';

        // 1. CAS NOUVEAU DOSSIER ÉTUDIANT (Création Étudiant + Parent + Inscription)
        if ($modeInscription === 'nouveau' || (!empty($data['nom_etudiant']) && !empty($data['prenom_etudiant']))) {
            $nomEtudiant = trim($data['nom_etudiant'] ?? '');
            $prenomEtudiant = trim($data['prenom_etudiant'] ?? '');
            $telephoneEtudiant = trim($data['telephone_etudiant'] ?? '');

            if (empty($nomEtudiant) || empty($prenomEtudiant)) {
                $this->error("Veuillez renseigner le nom et les prénoms de l'étudiant.");
                return;
            }

            if (empty($data['classe_code'])) {
                $this->error("Veuillez choisir la classe d'affectation.");
                return;
            }

            $matriculeEtudiant = trim($data['matricule_etudiant'] ?? '');
            if (empty($matriculeEtudiant)) {
                $matriculeEtudiant = $this->validator->generateCode('etudiants', 'matricule_etudiant', 'ETU-' . date('Y') . '-', 4);
            }

            $codeEtudiant = $this->validator->generateCode('etudiants', 'code_etudiant', 'ETU-', 8);

            try {
                $db->beginTransaction();

                // A. Création Étudiant
                $stmtEtu = $db->prepare("
                    INSERT INTO etudiants 
                    (code_etudiant, matricule_etudiant, nom_etudiant, prenom_etudiant, sexe_etudiant, date_naissance_etudiant, lieu_naissance_etudiant, nationalite_etudiant, telephone_etudiant, email_etudiant, lieu_residence_etudiant, user_code, etablissement_code, statut_etudiant, created_at_etudiant)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'actif', NOW())
                ");
                $stmtEtu->execute([
                    $codeEtudiant,
                    $matriculeEtudiant,
                    $nomEtudiant,
                    $prenomEtudiant,
                    $data['sexe_etudiant'] ?? 'M',
                    !empty($data['date_naissance_etudiant']) ? $data['date_naissance_etudiant'] : null,
                    !empty($data['lieu_naissance_etudiant']) ? $data['lieu_naissance_etudiant'] : null,
                    !empty($data['nationalite_etudiant']) ? $data['nationalite_etudiant'] : 'Ivoirienne',
                    $telephoneEtudiant,
                    !empty($data['email_etudiant']) ? $data['email_etudiant'] : null,
                    !empty($data['lieu_residence_etudiant']) ? $data['lieu_residence_etudiant'] : null,
                    $userCode,
                    $etabCode
                ]);

                // B. Création Parent / Tuteur (si renseigné)
                if (!empty($data['nom_tuteur']) || !empty($data['nom_pere']) || !empty($data['nom_mere']) || !empty($data['telephone_tuteur']) || !empty($data['telephone_pere'])) {
                    $codeParent = $this->validator->generateCode('parents', 'code_parent', 'PAR-', 8);
                    $stmtPar = $db->prepare("
                        INSERT INTO parents 
                        (code_parent, etudiant_code, nom_pere, telephone_pere, profession_pere, nom_mere, telephone_mere, profession_mere, nom_tuteur, telephone_tuteur, user_code, etablissement_code, created_at_parent)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmtPar->execute([
                        $codeParent,
                        $codeEtudiant,
                        $data['nom_pere'] ?? null,
                        $data['telephone_pere'] ?? null,
                        $data['profession_pere'] ?? null,
                        $data['nom_mere'] ?? null,
                        $data['telephone_mere'] ?? null,
                        $data['profession_mere'] ?? null,
                        $data['nom_tuteur'] ?? null,
                        $data['telephone_tuteur'] ?? null,
                        $userCode,
                        $etabCode
                    ]);
                }

                // C. Création Inscription
                $codeInscription = $this->validator->generateCode('inscriptions', 'code_inscription', 'INS-', 8);
                $stmtIns = $db->prepare("
                    INSERT INTO inscriptions 
                    (code_inscription, etudiant_code, classe_code, montant_scolarite_inscription, annee_code, user_code, etablissement_code, statut_inscription, affectation_etat, created_at_inscription)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'valide', 'non', NOW())
                ");
                $stmtIns->execute([
                    $codeInscription,
                    $codeEtudiant,
                    $data['classe_code'],
                    (float)($data['montant_scolarite_inscription'] ?? 0),
                    $anneeCode,
                    $userCode,
                    $etabCode
                ]);

                $db->commit();
                $this->success("Dossier d'inscription créé avec succès pour l'étudiant $nomEtudiant $prenomEtudiant !");
                return;
            } catch (Exception $e) {
                $db->rollBack();
                $this->error("Erreur lors de l'enregistrement du dossier complet : " . $e->getMessage());
                return;
            }
        }

        // 2. CAS RÉINSCRIPTION ÉTUDIANT EXISTANT
        if (empty($data['etudiant_code'])) {
            $this->error("Veuillez sélectionner un étudiant.");
            return;
        }

        if (empty($data['classe_code'])) {
            $this->error("Veuillez choisir la classe d'affectation.");
            return;
        }

        if (empty($data['code_inscription'])) {
            $data['code_inscription'] = $this->validator->generateCode('inscriptions', 'code_inscription', 'INS-', 8);
        }
        $data['statut_inscription'] = $data['statut_inscription'] ?? 'actif';
        $data['created_at_inscription'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE inscriptions")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Inscription enregistrée avec succès!');
        } else {
            $this->error("Erreur lors de l'inscription");
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_inscription');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        $cols = $this->model->getCon()->query("DESCRIBE inscriptions")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Item modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id');
        $statut = $this->post('statut') ?: $this->post('status');
        if ($id && $this->model->getById($id)) {
            $allowed = ['valide', 'solde', 'annule'];
            if (!empty($statut) && in_array($statut, $allowed, true)) {
                $success = $this->model->updateStatus($id, $statut, 'statut_inscription');
            } else {
                $success = $this->model->toggleStatus($id);
            }
            if ($success) {
                $this->success('Statut de l\'inscription mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Inscription introuvable');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $stmt = $this->model->getCon()->prepare("
                SELECT ins.*, 
                       e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.telephone_etudiant, e.email_etudiant, e.sexe_etudiant, e.date_naissance_etudiant,
                       cl.libelle_classe, f.libelle_filiere, n.libelle_niveau,
                       a.libelle_annee
                FROM inscriptions ins
                LEFT JOIN etudiants e ON e.code_etudiant = ins.etudiant_code
                LEFT JOIN classes cl ON cl.code_classe = ins.classe_code
                LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
                LEFT JOIN annees a ON a.code_annee = ins.annee_code
                WHERE ins.id_inscription = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { 
                $this->renderNotFound("L'inscription demandée est introuvable.");
                return;
            }

            // Paiements pour cette inscription
            $stmtP = $this->model->getCon()->prepare("
                SELECT * FROM paiements 
                WHERE inscription_code = ?
                ORDER BY date_paiement DESC
            ");
            $stmtP->execute([$item['code_inscription']]);
            $paiements = $stmtP->fetchAll(PDO::FETCH_ASSOC);

            $scolarite = (float)($item['montant_scolarite_inscription'] ?? 0);
            $totalPaye = 0;
            foreach ($paiements as $p) {
                if (($p['statut_paiement'] ?? '') !== 'annule') {
                    $totalPaye += (float)($p['montant_paiement'] ?? 0);
                }
            }
            $solde = max(0, $scolarite - $totalPaye);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("InscriptionController::details error: " . $e->getMessage());
            $this->renderNotFound("L'inscription demandée est introuvable.");
            return;
        }
        $this->loadView('../views/inscriptions/details.php', [
            'item' => $item, 
            'paiements' => $paiements,
            'totalPaye' => $totalPaye,
            'solde' => $solde,
            'scolarite' => $scolarite,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'inscription/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'inscription/list'); exit();
        }
        $this->loadView('../views/inscriptions/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/inscriptions/edit.php', ['item' => []]);
    }
}
