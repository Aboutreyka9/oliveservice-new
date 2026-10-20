<?php

class AnneeController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelAnnee();
    }

    public function list()
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_ANNEES');
        $this->loadView('../views/annees/list.php');
    }

    public function apiList()
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_ANNEES');
        $items = $this->model->getAllWithDetails();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_annee'];
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
        $this->requirePermission('GESTIONNAIRE_MANAGE_ANNEES');
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['libelle_annee'])) {
            if (!$this->checkUnique('annees', 'libelle_annee', $data['libelle_annee'], 'Année d\'activité')) return;
        }

        $userCode = Context::user() ?: ($_SESSION[USERS_AUTH]['code_user'] ?? null);
        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();

        if (empty($etabCode)) {
            try {
                $stmtE = $this->model->getCon()->query("SELECT code_etablissement FROM etablissements LIMIT 1");
                $etabCode = $stmtE ? $stmtE->fetchColumn() : '';
            } catch (\Throwable $e) {}
        }

        if (empty($zoneCode)) {
            try {
                $stmtZ = $this->model->getCon()->query("SELECT code_zone FROM zones LIMIT 1");
                $zoneCode = $stmtZ ? $stmtZ->fetchColumn() : '';
            } catch (\Throwable $e) {}
        }

        if (empty($userCode) || empty($etabCode) || empty($zoneCode)) {
            $this->error("Erreur d'insertion : L'utilisateur connecté, la zone commerciale et l'établissement sont obligatoires.");
            return;
        }

        if (empty($data['code_annee'])) {
            $data['code_annee'] = $this->validator->generateCode('annees', 'code_annee', 'ANN-', 8);
        }
        $data['statut_annee'] = $data['statut_annee'] ?? 'actif';
        if (!in_array($data['statut_annee'], ['planifie', 'actif', 'cloture'])) {
            $data['statut_annee'] = 'actif';
        }
        $data['penalite_annee'] = isset($data['penalite_annee']) && $data['penalite_annee'] !== '' ? (float)$data['penalite_annee'] : 0.0;
        $data['penalite_restore'] = isset($data['penalite_restore']) && $data['penalite_restore'] !== '' ? (float)$data['penalite_restore'] : 0.0;
        $data['created_at_annee'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE annees")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('zone_code', $cols)) $data['zone_code'] = $zoneCode;
        $filteredData = array_intersect_key($data, array_flip($cols));

        // Si la nouvelle année est active, clôturer les précédentes de cet établissement
        if (($filteredData['statut_annee'] ?? '') === 'actif' && !empty($etabCode)) {
            $this->model->getCon()->prepare("
                UPDATE annees SET statut_annee = 'cloture' WHERE etablissement_code = ? AND statut_annee = 'actif'
            ")->execute([$etabCode]);
        }

        if ($this->model->create($filteredData)) {
            if (($filteredData['statut_annee'] ?? '') === 'actif') {
                $_SESSION['annee_active_code'] = $filteredData['code_annee'] ?? ($data['code_annee'] ?? '');
                $_SESSION['annee_active_libelle'] = $filteredData['libelle_annee'] ?? ($data['libelle_annee'] ?? '');
                NotificationService::resolveAnneeNonActive($etabCode);
            }
            $this->success('Année d\'activité créée avec succès !');
        } else {
            $this->error('Erreur lors de la création de l\'année');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requirePermission('GESTIONNAIRE_MANAGE_ANNEES');
        $id = (int)$this->post('id_annee');
        if (!$id) { $this->error('Identifiant invalide'); return; }

        $existing = $this->model->getById($id);
        if (!$existing) { $this->error('Exercice introuvable'); return; }
        if (($existing['statut_annee'] ?? '') === 'cloture') {
            $this->error('Cette année d\'activité est clôturée et ne peut plus être modifiée.');
            return;
        }

        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['libelle_annee'])) {
            if (!$this->checkUnique('annees', 'libelle_annee', $data['libelle_annee'], 'Année d\'activité', 'id_annee', $id)) return;
        }

        if (isset($data['penalite_annee'])) {
            $data['penalite_annee'] = $data['penalite_annee'] !== '' ? (float)$data['penalite_annee'] : 0.0;
        }
        if (isset($data['penalite_restore'])) {
            $data['penalite_restore'] = $data['penalite_restore'] !== '' ? (float)$data['penalite_restore'] : 0.0;
        }
        if (isset($data['statut_annee']) && !in_array($data['statut_annee'], ['planifie', 'actif', 'cloture'])) {
            unset($data['statut_annee']);
        }

        $cols = $this->model->getCon()->query("DESCRIBE annees")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));

        // Si on passe cette année à actif, clôturer les autres de l'établissement
        if (($filteredData['statut_annee'] ?? '') === 'actif') {
            $etabCode = $existing['etablissement_code'] ?? Context::etablissement();
            if (!empty($etabCode)) {
                $this->model->getCon()->prepare("
                    UPDATE annees SET statut_annee = 'cloture' WHERE etablissement_code = ? AND id_annee != ? AND statut_annee = 'actif'
                ")->execute([$etabCode, $id]);
            }
        }

        if ($this->model->update($filteredData, $id)) {
            if (($filteredData['statut_annee'] ?? '') === 'actif') {
                $existing = $this->model->getById($id);
                if ($existing) {
                    $_SESSION['annee_active_code'] = $existing['code_annee'];
                    $_SESSION['annee_active_libelle'] = $existing['libelle_annee'];
                }
                NotificationService::resolveAnneeNonActive(Context::etablissement());
            }
            $this->success('Année d\'activité modifiée avec succès !');
        } else {
            $this->error('Erreur lors de la modification de l\'année');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requirePermission('GESTIONNAIRE_MANAGE_ANNEES');
        $id = (int)$this->post('id');
        $statut = $this->post('statut');
        if ($id && $this->model->getById($id)) {
            $res = false;
            if (!empty($statut) && in_array($statut, ['planifie', 'actif', 'cloture'], true)) {
                $res = $this->model->updateStatut($id, $statut);
            } else {
                $res = $this->model->toggleStatus($id);
            }

            if ($res) {
                $updated = $this->model->getById($id);
                if ($updated && ($updated['statut_annee'] ?? '') === 'actif') {
                    $_SESSION['annee_active_code'] = $updated['code_annee'];
                    $_SESSION['annee_active_libelle'] = $updated['libelle_annee'];
                    NotificationService::resolveAnneeNonActive(Context::etablissement());
                }
                $this->success('Statut mis à jour avec succès !', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut.');
            }
        } else {
            $this->error('Item introuvable');
        }
    }

    public function switchSession()
    {
        $this->requireAuth();
        $this->requirePost(false);
        $code = trim($this->post('code_annee') ?? '');
        if (empty($code)) {
            $this->error('Code de l\'année d\'activité requis.');
            return;
        }

        $db = $this->model->getCon();
        $stmt = $db->prepare("
            SELECT code_annee, libelle_annee, statut_annee 
            FROM annees 
            WHERE code_annee = ? 
            LIMIT 1
        ");
        $stmt->execute([$code]);
        $annee = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$annee) {
            $this->error('Année d\'activité introuvable.');
            return;
        }

        if (!in_array($annee['statut_annee'], ['actif', 'cloture'], true)) {
            $this->error('Vous ne pouvez basculer que vers l\'exercice actif ou un exercice clôturé.');
            return;
        }

        $_SESSION['annee_active_code'] = $annee['code_annee'];
        $_SESSION['annee_active_libelle'] = $annee['libelle_annee'];

        $this->success("Exercice basculé sur " . $annee['libelle_annee'] . " avec succès.", [
            'code' => $annee['code_annee'],
            'libelle' => $annee['libelle_annee'],
            'statut' => $annee['statut_annee']
        ]);
    }

    public function details($details)
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_ANNEES');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { 
                $this->renderNotFound("L'année d'activité demandée est introuvable.");
                return;
            }
            
            $anneeCode = $item['code_annee'];
            $db = $this->model->getCon();
            
            // Métadonnées étendues (Établissement, Zone, Créateur)
            $stmtMeta = $db->prepare("
                SELECT z.libelle_zone, e.libelle_etablissement, u.nom_user, u.prenom_user
                FROM annees a
                LEFT JOIN zones z ON z.code_zone = a.zone_code
                LEFT JOIN etablissements e ON e.code_etablissement = a.etablissement_code
                LEFT JOIN users u ON u.code_user = a.user_code
                WHERE a.code_annee = ?
            ");
            $stmtMeta->execute([$anneeCode]);
            $meta = $stmtMeta->fetch(PDO::FETCH_ASSOC) ?: [];
            $item = array_merge($item, $meta);

            // Statistiques réelles Olive Service
            $stmtStats = $db->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM souscriptions WHERE annee_code = ?) as total_souscriptions,
                    (SELECT COUNT(*) FROM souscriptions WHERE annee_code = ? AND statut_souscription = 'valide') as total_valides,
                    (SELECT COUNT(*) FROM souscriptions WHERE annee_code = ? AND statut_souscription = 'solde') as total_soldees,
                    (SELECT COUNT(*) FROM souscriptions WHERE annee_code = ? AND statut_souscription = 'reconduite') as total_reconduites,
                    (SELECT COUNT(*) FROM souscriptions WHERE annee_code = ? AND statut_souscription = 'annule') as total_annulees,
                    (SELECT COUNT(*) FROM sessions WHERE annee_code = ?) as total_sessions,
                    (SELECT COALESCE(SUM(c.montant_cautisation_client), 0) 
                     FROM cautisation_clients c 
                     JOIN souscriptions sub ON sub.code_souscription = c.souscription_code 
                     WHERE c.annee_code = ? 
                       AND (c.statut_cautisation_client != 'annule' OR c.statut_cautisation_client IS NULL) 
                       AND sub.statut_souscription IN ('valide', 'solde')) as total_cotise
            ");
            $stmtStats->execute([$anneeCode, $anneeCode, $anneeCode, $anneeCode, $anneeCode, $anneeCode, $anneeCode]);
            $stats = $stmtStats->fetch(PDO::FETCH_ASSOC) ?: [
                'total_souscriptions' => 0, 'total_valides' => 0, 'total_soldees' => 0,
                'total_reconduites' => 0, 'total_annulees' => 0, 'total_sessions' => 0, 'total_cotise' => 0
            ];

            // Liste des sessions de cette année
            $stmtSess = $db->prepare("
                SELECT s.*, z.libelle_zone
                FROM sessions s
                LEFT JOIN zones z ON z.code_zone = s.zone_code
                WHERE s.annee_code = ?
                ORDER BY s.id_session DESC
            ");
            $stmtSess->execute([$anneeCode]);
            $sessions = $stmtSess->fetchAll(PDO::FETCH_ASSOC);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("AnneeController::details error: " . $e->getMessage());
            $this->renderNotFound("L'année d'activité demandée est introuvable.");
            return;
        }
        $this->loadView('../views/annees/details.php', [
            'item' => $item, 
            'stats' => $stats,
            'sessions' => $sessions,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_ANNEES');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item || ($item['statut_annee'] ?? '') === 'cloture') { 
                header('Location: ' . RACINE . 'annee/list'); 
                exit(); 
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'annee/list'); exit();
        }
        $this->loadView('../views/annees/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_ANNEES');
        $this->loadView('../views/annees/edit.php', ['item' => []]);
    }
}