<?php

class EtudiantController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelEtudiant();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/etudiants/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_etudiant'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $data = $_POST;
        unset($data['csrf_token']);
        $this->cleanPhoneFields($data);

        if (!empty($data['matricule_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'matricule_etudiant', $data['matricule_etudiant'], 'Matricule etudiant')) return;
        }
        if (!empty($data['email_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'email_etudiant', $data['email_etudiant'], 'Email etudiant')) return;
        }
        if (!empty($data['telephone_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'telephone_etudiant', $data['telephone_etudiant'], 'Telephone etudiant')) return;
        }

        $userCode = Context::user() ?? '';
        $anneeCode = Context::annee();
        $etabCode = '5454544456';
        if (empty($data['code_etudiant'])) {
            $data['code_etudiant'] = $this->validator->generateCode('etudiants', 'code_etudiant', 'ETU-', 8);
        }
        $data['statut_etudiant'] = $data['statut_etudiant'] ?? 'actif';
        $data['created_at_etudiant'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE etudiants")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Item créé avec succès!');
        } else {
            $this->error('Erreur lors de la création');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_etudiant');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        $this->cleanPhoneFields($data);

        if (!empty($data['matricule_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'matricule_etudiant', $data['matricule_etudiant'], 'Matricule etudiant', 'id_etudiant', $id)) return;
        }
        if (!empty($data['email_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'email_etudiant', $data['email_etudiant'], 'Email etudiant', 'id_etudiant', $id)) return;
        }
        if (!empty($data['telephone_etudiant'])) {
            if (!$this->checkUnique('etudiants', 'telephone_etudiant', $data['telephone_etudiant'], 'Telephone etudiant', 'id_etudiant', $id)) return;
        }

        $cols = $this->model->getCon()->query("DESCRIBE etudiants")->fetchAll(PDO::FETCH_COLUMN);
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
        $id = $this->post('id');
        if ($id && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Item introuvable');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'etudiant/list'); exit(); }

            $etudiantCode = $item['code_etudiant'];

            // 1. Parent info
            $stmtPar = $this->model->getCon()->prepare("
                SELECT * FROM parents WHERE etudiant_code = ? LIMIT 1
            ");
            $stmtPar->execute([$etudiantCode]);
            $parent = $stmtPar->fetch(PDO::FETCH_ASSOC) ?: [];

            // 2. Inscription active & Classe
            $stmtIns = $this->model->getCon()->prepare("
                SELECT ins.*, cl.libelle_classe, f.libelle_filiere, n.libelle_niveau, a.libelle_annee
                FROM inscriptions ins
                LEFT JOIN classes cl ON cl.code_classe = ins.classe_code
                LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
                LEFT JOIN annees a ON a.code_annee = ins.annee_code
                WHERE ins.etudiant_code = ? AND ins.statut_inscription = 'actif'
                ORDER BY ins.id_inscription DESC LIMIT 1
            ");
            $stmtIns->execute([$etudiantCode]);
            $inscription = $stmtIns->fetch(PDO::FETCH_ASSOC) ?: [];

            // 3. Paiements effectués
            $stmtPaiements = $this->model->getCon()->prepare("
                SELECT p.*, a.libelle_annee, ins.code_inscription
                FROM paiements p
                JOIN inscriptions ins ON ins.code_inscription = p.inscription_code
                LEFT JOIN annees a ON a.code_annee = p.annee_code
                WHERE ins.etudiant_code = ?
                ORDER BY p.date_paiement DESC
            ");
            $stmtPaiements->execute([$etudiantCode]);
            $paiements = $stmtPaiements->fetchAll(PDO::FETCH_ASSOC);

            // 4. Statistiques financières
            $scolariteTotale = (float)($inscription['montant_scolarite_inscription'] ?? 0);
            $totalPaye = 0;
            foreach ($paiements as $p) {
                if (($p['statut_paiement'] ?? '') !== 'annule') {
                    $totalPaye += (float)($p['montant_paiement'] ?? ($p['montant_paye'] ?? 0));
                }
            }
            $soldeRestant = max(0, $scolariteTotale - $totalPaye);

            // 5. Absences
            $stmtAbs = $this->model->getCon()->prepare("
                SELECT abs.*, m.libelle_matiere
                FROM absences abs
                JOIN inscriptions ins ON ins.code_inscription = abs.inscription_code
                LEFT JOIN matieres m ON m.code_matiere = abs.matiere_code
                WHERE ins.etudiant_code = ?
                ORDER BY abs.date_absence DESC
            ");
            $stmtAbs->execute([$etudiantCode]);
            $absences = $stmtAbs->fetchAll(PDO::FETCH_ASSOC);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("EtudiantController::details error: " . $e->getMessage());
            $this->renderNotFound("Le dossier étudiant demandé est introuvable ou une erreur est survenue.");
        }
        $this->loadView('../views/etudiants/details.php', [
            'item' => $item, 
            'parent' => $parent,
            'inscription' => $inscription,
            'paiements' => $paiements,
            'totalPaye' => $totalPaye,
            'soldeRestant' => $soldeRestant,
            'scolariteTotale' => $scolariteTotale,
            'absences' => $absences,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'etudiant/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'etudiant/list'); exit();
        }
        $this->loadView('../views/etudiants/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/etudiants/edit.php', ['item' => []]);
    }

    public function wizard()
    {
        $this->requireAuth();
        $this->loadView('../views/etudiants/wizard.php');
    }

    public function addWizard()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $db = $this->model->getCon();
        $userCode = Context::user() ?? '';
        $anneeCode = Context::annee();
        $etabCode = '5454544456';

        $data = $_POST;
        unset($data['csrf_token']);
        $this->cleanPhoneFields($data);

        $nomEtudiant = trim($data['nom_etudiant'] ?? '');
        $prenomEtudiant = trim($data['prenom_etudiant'] ?? '');
        $telephoneEtudiant = $data['telephone_etudiant'] ?? '';

        if (empty($nomEtudiant) || empty($prenomEtudiant) || empty($telephoneEtudiant)) {
            $this->error('Veuillez renseigner le nom, prénoms et téléphone de l\'étudiant.');
            return;
        }

        $matriculeEtudiant = trim($data['matricule_etudiant'] ?? '');
        if (empty($matriculeEtudiant)) {
            $matriculeEtudiant = $this->validator->generateCode('etudiants', 'matricule_etudiant', 'ETU-' . date('Y') . '-', 4);
        }

        $codeEtudiant = $this->validator->generateCode('etudiants', 'code_etudiant', 'ETU-', 8);

        try {
            $db->beginTransaction();

            // 1. Insert Student into `etudiants`
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
                !empty($data['date_naissance_etudiant']) ? $data['date_naissance_etudiant'] : (!empty($data['date_naissance']) ? $data['date_naissance'] : null),
                !empty($data['lieu_naissance_etudiant']) ? $data['lieu_naissance_etudiant'] : (!empty($data['lieu_naissance']) ? $data['lieu_naissance'] : null),
                !empty($data['nationalite_etudiant']) ? $data['nationalite_etudiant'] : (!empty($data['nationalite']) ? $data['nationalite'] : 'Ivoirienne'),
                $telephoneEtudiant,
                !empty($data['email_etudiant']) ? $data['email_etudiant'] : null,
                !empty($data['lieu_residence_etudiant']) ? $data['lieu_residence_etudiant'] : (!empty($data['adresse_etudiant']) ? $data['adresse_etudiant'] : null),
                $userCode,
                $etabCode
            ]);

            // 2. Insert Parent into `parents` if parent info provided
            if (!empty($data['nom_pere']) || !empty($data['nom_mere']) || !empty($data['nom_tuteur'])) {
                $codeParent = $this->validator->generateCode('parents', 'code_parent', 'PAR-', 8);
                $stmtPar = $db->prepare("
                    INSERT INTO parents 
                    (code_parent, etudiant_code, nom_pere, telephone_pere, profession_pere, nom_mere, telephone_mere, nom_tuteur, telephone_tuteur, user_code, etablissement_code, created_at_parent)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmtPar->execute([
                    $codeParent,
                    $codeEtudiant,
                    !empty($data['nom_pere']) ? $data['nom_pere'] : null,
                    !empty($data['telephone_pere']) ? $data['telephone_pere'] : null,
                    !empty($data['profession_pere']) ? $data['profession_pere'] : null,
                    !empty($data['nom_mere']) ? $data['nom_mere'] : null,
                    !empty($data['telephone_mere']) ? $data['telephone_mere'] : null,
                    !empty($data['nom_tuteur']) ? $data['nom_tuteur'] : null,
                    !empty($data['telephone_tuteur']) ? $data['telephone_tuteur'] : null,
                    $userCode,
                    $etabCode
                ]);
            }

            // 3. Insert Academic Inscription into `inscriptions` if classe_code provided
            if (!empty($data['classe_code'])) {
                $montantScolarite = (float)($data['montant_scolarite_inscription'] ?? 0);
                if ($montantScolarite <= 0) {
                    $stmtCl = $db->prepare("SELECT * FROM classes WHERE code_classe = ? LIMIT 1");
                    $stmtCl->execute([$data['classe_code']]);
                    $cl = $stmtCl->fetch(PDO::FETCH_ASSOC);
                    if ($cl) {
                        $stmtSco = $db->prepare("SELECT montant_scolarite FROM scolarites WHERE filiere_code = ? AND niveau_code = ? ORDER BY id_scolarite DESC LIMIT 1");
                        $stmtSco->execute([$cl['filiere_code'], $cl['niveau_code']]);
                        $sco = $stmtSco->fetch(PDO::FETCH_ASSOC);
                        if ($sco) {
                            $montantScolarite = (float)$sco['montant_scolarite'];
                        }
                    }
                }

                $codeInscription = $this->validator->generateCode('inscriptions', 'code_inscription', 'INS-', 8);
                $stmtIns = $db->prepare("
                    INSERT INTO inscriptions
                    (code_inscription, etudiant_code, classe_code, montant_scolarite_inscription, user_code, annee_code, etablissement_code, statut_inscription, affectation_etat, created_at_inscription)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'valide', 'non', NOW())
                ");
                $stmtIns->execute([
                    $codeInscription,
                    $codeEtudiant,
                    $data['classe_code'],
                    $montantScolarite,
                    $userCode,
                    $anneeCode,
                    $etabCode
                ]);
            }

            // 4. Insert Accessoires / Kits into `accessoire_inscription`
            if (!empty($data['accessoires']) && is_array($data['accessoires'])) {
                $stmtAcc = $db->prepare("
                    INSERT INTO accessoire_inscription
                    (code_accessoire_inscription, inscription_code, accessoire_code, annee_code, statut_accessoire_inscription, etat_retrait, user_code, etablissement_code, created_at_accessoire_inscription)
                    VALUES (?, ?, ?, ?, 'actif', 'en_attente', ?, ?, NOW())
                ");
                foreach ($data['accessoires'] as $accCode) {
                    if (empty($accCode)) continue;
                    $codeAccIns = $this->validator->generateCode('accessoire_inscription', 'code_accessoire_inscription', 'ACI-', 8);
                    $stmtAcc->execute([
                        $codeAccIns,
                        !empty($codeInscription) ? $codeInscription : '',
                        $accCode,
                        $anneeCode,
                        $userCode,
                        $etabCode
                    ]);
                }
            }

            $db->commit();
            $this->success('Dossier d\'inscription étudiant créé avec succès !', ['redirect' => RACINE . 'etudiant/list']);

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->error('Erreur lors de la création du dossier: ' . $e->getMessage());
        }
    }
}