<?php

class ClotureCaisseController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelClotureCaisse();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/clotures_caisse/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_cloture'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function getDailyTotals()
    {
        $this->requireAuth();
        $date = $_GET['date'] ?? ($_POST['date'] ?? date('Y-m-d'));
        $db = $this->model->getCon();

        $stmt = $db->prepare("
            SELECT mode_paiement, SUM(montant_paiement) as sum_mode, COUNT(*) as count_mode
            FROM paiements
            WHERE DATE(date_paiement) = ? AND statut_paiement != 'annule'
            GROUP BY mode_paiement
        ");
        $stmt->execute([$date]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalEspeces = 0;
        $totalMobileMoney = 0;
        $totalChequeVirement = 0;
        $nbEncaissements = 0;

        foreach ($rows as $r) {
            $mode = strtolower($r['mode_paiement'] ?? '');
            $sum = (float)($r['sum_mode'] ?? 0);
            $cnt = (int)($r['count_mode'] ?? 0);

            $nbEncaissements += $cnt;

            if ($mode === 'espece' || $mode === 'especes') {
                $totalEspeces += $sum;
            } elseif ($mode === 'mobile_money' || $mode === 'wave' || $mode === 'orange' || $mode === 'mtn' || $mode === 'moov') {
                $totalMobileMoney += $sum;
            } else {
                $totalChequeVirement += $sum;
            }
        }

        $totalGeneral = $totalEspeces + $totalMobileMoney + $totalChequeVirement;

        $stmtCheck = $db->prepare("SELECT * FROM clotures_caisse WHERE date_cloture = ? AND statut_cloture != 'annule' LIMIT 1");
        $stmtCheck->execute([$date]);
        $alreadyClosed = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        $this->json([
            'status' => 1,
            'data' => [
                'date' => $date,
                'is_already_closed' => !empty($alreadyClosed),
                'existing_code' => $alreadyClosed['code_cloture'] ?? null,
                'total_especes' => $totalEspeces,
                'total_mobile_money' => $totalMobileMoney,
                'total_cheque_virement' => $totalChequeVirement,
                'total_general' => $totalGeneral,
                'nb_encaissements' => $nbEncaissements,
                'total_especes_fmt' => number_format($totalEspeces, 0, ',', ' ') . ' FCFA',
                'total_mobile_money_fmt' => number_format($totalMobileMoney, 0, ',', ' ') . ' FCFA',
                'total_cheque_virement_fmt' => number_format($totalChequeVirement, 0, ',', ' ') . ' FCFA',
                'total_general_fmt' => number_format($totalGeneral, 0, ',', ' ') . ' FCFA'
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

        $dateCloture = $data['date_cloture'] ?? date('Y-m-d');
        $db = $this->model->getCon();

        // 1. Control: Single closing per day rule
        $stmtCheck = $db->prepare("SELECT * FROM clotures_caisse WHERE date_cloture = ? AND statut_cloture != 'annule' LIMIT 1");
        $stmtCheck->execute([$dateCloture]);
        $existingCloture = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existingCloture) {
            $dateFmt = date('d/m/Y', strtotime($dateCloture));
            $this->error("La caisse a déjà été clôturée pour la journée du $dateFmt (Réf: {$existingCloture['code_cloture']}). Une seule clôture de caisse par jour est autorisée.");
            return;
        }

        // Get Fond Initial from active ouverture
        $stmtOuv = $db->prepare("SELECT * FROM ouvertures_caisse WHERE date_ouverture = ? AND statut_ouverture = 'ouverte' ORDER BY id_ouverture DESC LIMIT 1");
        $stmtOuv->execute([$dateCloture]);
        $ouv = $stmtOuv->fetch(PDO::FETCH_ASSOC);

        $fondInitial = $ouv ? (float)$ouv['fond_initial'] : (float)($data['fond_initial'] ?? 0);
        $totalEspeces = (float)($data['total_especes'] ?? 0);

        $data['fond_initial'] = $fondInitial;
        $data['solde_attendu_caisse'] = $fondInitial + $totalEspeces;
        if (empty($data['code_cloture'])) {
            $data['code_cloture'] = $this->validator->generateCode('clotures_caisse', 'code_cloture', 'CLO-', 8);
        }
        $data['statut_cloture'] = 'valide';
        $data['created_at_cloture'] = date('Y-m-d H:i:s');
        $data['user_code'] = $userCode;
        $data['etablissement_code'] = $etabCode;
        $data['annee_code'] = $anneeCode;

        $cols = $db->query("DESCRIBE clotures_caisse")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));

        if ($this->model->create($filteredData)) {
            // Mark ouverture as cloturee
            $stmtClose = $db->prepare("UPDATE ouvertures_caisse SET statut_ouverture = 'cloturee' WHERE date_ouverture = ?");
            $stmtClose->execute([$dateCloture]);

            $this->success('Clôture de caisse effectuée avec succès! La caisse de la journée est désormais fermée.');
        } else {
            $this->error('Erreur lors de l\'enregistrement de la clôture de caisse');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_cloture');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        $cols = $this->model->getCon()->query("DESCRIBE clotures_caisse")->fetchAll(PDO::FETCH_COLUMN);
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
            $allowed = ['attente', 'valide', 'rejete'];
            if (!empty($statut) && in_array($statut, $allowed, true)) {
                $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? null;
                $extraData = ['statut_cloture' => $statut];
                if ($statut === 'valide') {
                    $extraData['user_validation'] = $userCode;
                }
                $success = $this->model->update($extraData, $id);
            } else {
                $success = $this->model->toggleStatus($id);
            }
            if ($success) {
                $this->success('Statut de la clôture mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Clôture de caisse introuvable');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $stmt = $this->model->getCon()->prepare("
                SELECT c.*, u.nom_user, u.prenom_user
                FROM clotures_caisse c
                LEFT JOIN users u ON u.code_user = c.user_code
                WHERE c.id_cloture = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { 
                $this->renderNotFound("Le procès-verbal de clôture de caisse demandé est introuvable.");
                return;
            }

            // Encaissements de cette journée
            $stmtP = $this->model->getCon()->prepare("
                SELECT p.*, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, cl.libelle_classe
                FROM paiements p
                JOIN inscriptions ins ON ins.code_inscription = p.inscription_code
                JOIN etudiants e ON e.code_etudiant = ins.etudiant_code
                LEFT JOIN classes cl ON cl.code_classe = ins.classe_code
                WHERE DATE(p.date_paiement) = ? AND p.statut_paiement != 'annule'
                ORDER BY p.date_paiement DESC
            ");
            $stmtP->execute([$item['date_cloture']]);
            $paiements = $stmtP->fetchAll(PDO::FETCH_ASSOC);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("ClotureCaisseController::details error: " . $e->getMessage());
            $this->renderNotFound("Le procès-verbal de clôture de caisse demandé est introuvable.");
            return;
        }
        $this->loadView('../views/clotures_caisse/details.php', [
            'item' => $item, 
            'paiements' => $paiements,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'cloture_caisse/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'cloture_caisse/list'); exit();
        }
        $this->loadView('../views/clotures_caisse/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/clotures_caisse/edit.php', ['item' => []]);
    }
}
