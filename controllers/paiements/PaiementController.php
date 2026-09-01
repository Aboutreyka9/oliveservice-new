<?php

class PaiementController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelPaiement();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/paiements/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_paiement'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function getStudentFinancialSummary()
    {
        $this->requireAuth();
        $inscriptionCode = $_GET['inscription_code'] ?? ($_POST['inscription_code'] ?? '');
        $etudiantCode = $_GET['etudiant_code'] ?? ($_POST['etudiant_code'] ?? '');

        $db = $this->model->getCon();

        if (!empty($inscriptionCode)) {
            $stmt = $db->prepare("
                SELECT i.*, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.code_etudiant, c.libelle_classe
                FROM inscriptions i
                LEFT JOIN etudiants e ON i.etudiant_code = e.code_etudiant
                LEFT JOIN classes c ON i.classe_code = c.code_classe
                WHERE i.code_inscription = ? OR i.id_inscription = ?
                LIMIT 1
            ");
            $stmt->execute([$inscriptionCode, is_numeric($inscriptionCode) ? (int)$inscriptionCode : 0]);
            $ins = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif (!empty($etudiantCode)) {
            $stmt = $db->prepare("
                SELECT i.*, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.code_etudiant, c.libelle_classe
                FROM etudiants e
                LEFT JOIN inscriptions i ON i.etudiant_code = e.code_etudiant
                LEFT JOIN classes c ON i.classe_code = c.code_classe
                WHERE e.code_etudiant = ? OR e.matricule_etudiant = ?
                ORDER BY i.id_inscription DESC
                LIMIT 1
            ");
            $stmt->execute([$etudiantCode, $etudiantCode]);
            $ins = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $this->json(['status' => 0, 'message' => 'Code inscription ou matricule manquant']);
            return;
        }

        if (!$ins) {
            $this->json(['status' => 0, 'message' => 'Aucun dossier d\'inscription trouvé pour cet étudiant.']);
            return;
        }

        $codeInscription = $ins['code_inscription'] ?? '';
        $scolariteDue = (float)($ins['montant_scolarite_inscription'] ?? 0);

        $stmtPay = $db->prepare("SELECT SUM(montant_paiement) FROM paiements WHERE inscription_code = ? AND statut_paiement != 'annule'");
        $stmtPay->execute([$codeInscription]);
        $totalPaye = (float)($stmtPay->fetchColumn() ?: 0);

        $soldeRestant = max(0, $scolariteDue - $totalPaye);

        $statutReglement = 'Non Réglé';
        $badgeClass = 'badge-danger';
        if ($totalPaye >= $scolariteDue && $scolariteDue > 0) {
            $statutReglement = 'Scolarité Totalement Soldée';
            $badgeClass = 'badge-success';
        } elseif ($totalPaye > 0) {
            $statutReglement = 'Acompte Payé / Solde Débiteurs';
            $badgeClass = 'badge-warning';
        }

        $nomComplet = trim(($ins['nom_etudiant'] ?? '') . ' ' . ($ins['prenom_etudiant'] ?? ''));

        $this->json([
            'status' => 1,
            'data' => [
                'code_inscription' => $codeInscription,
                'code_etudiant' => $ins['code_etudiant'] ?? '',
                'matricule' => $ins['matricule_etudiant'] ?? '-',
                'nom_complet' => $nomComplet,
                'classe' => $ins['libelle_classe'] ?? 'Classe non définie',
                'scolarite_due' => $scolariteDue,
                'scolarite_due_fmt' => number_format($scolariteDue, 0, ',', ' ') . ' FCFA',
                'total_paye' => $totalPaye,
                'total_paye_fmt' => number_format($totalPaye, 0, ',', ' ') . ' FCFA',
                'solde_restant' => $soldeRestant,
                'solde_restant_fmt' => number_format($soldeRestant, 0, ',', ' ') . ' FCFA',
                'statut_reglement' => $statutReglement,
                'badge_class' => $badgeClass
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
        $today = date('Y-m-d');
        $mode = strtolower($data['mode_paiement'] ?? 'especes');

        // Vérification de la session de caisse pour les encaissements en espèces
        if ($mode === 'especes' || $mode === 'espece' || $mode === 'cash' || empty($mode)) {
            // 1. Vérifier si la caisse a déjà été clôturée aujourd'hui
            $stmtCloture = $db->prepare("SELECT code_cloture FROM clotures_caisse WHERE date_cloture = ? AND statut_cloture != 'annule' LIMIT 1");
            $stmtCloture->execute([$today]);
            $cloture = $stmtCloture->fetch(PDO::FETCH_ASSOC);
            if ($cloture) {
                $this->error("Encaissement impossible : La caisse du jour a déjà été CLÔTURÉE (Réf: {$cloture['code_cloture']}). Aucun nouvel encaissement en espèces ne peut être enregistré.");
                return;
            }

            // 2. Vérifier si la caisse a été ouverte aujourd'hui
            $stmtOuv = $db->prepare("SELECT * FROM ouvertures_caisse WHERE date_ouverture = ? AND statut_ouverture = 'ouverte' LIMIT 1");
            $stmtOuv->execute([$today]);
            $ouv = $stmtOuv->fetch(PDO::FETCH_ASSOC);
            if (!$ouv) {
                $this->error("Encaissement impossible : La caisse du jour n'est pas encore OUVERTE. Veuillez effectuer l'ouverture de caisse avant d'encaisser.");
                return;
            }
        }

        if (empty($data['code_paiement'])) {
            $data['code_paiement'] = $this->validator->generateCode('paiements', 'code_paiement', 'PAI-', 8);
        }
        $data['statut_paiement'] = $data['statut_paiement'] ?? 'confirme';
        $data['date_paiement'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE paiements")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Règlement de caisse enregistré avec succès!', ['reload' => true]);
        } else {
            $this->error('Erreur lors de l\'enregistrement du paiement');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_paiement');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        $cols = $this->model->getCon()->query("DESCRIBE paiements")->fetchAll(PDO::FETCH_COLUMN);
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
            $allowed = ['en_attente', 'confirme', 'annule', 'rembourse', 'echoue'];
            if (!empty($statut) && in_array($statut, $allowed, true)) {
                $success = $this->model->updateStatus($id, $statut, 'statut_paiement');
            } else {
                $success = $this->model->toggleStatus($id);
            }
            if ($success) {
                $this->success('Statut du paiement mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Paiement introuvable');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $stmt = $this->model->getCon()->prepare("
                SELECT p.*, 
                       e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.telephone_etudiant, e.email_etudiant,
                       cl.libelle_classe, f.libelle_filiere, n.libelle_niveau,
                       a.libelle_annee,
                       u.nom_user as nom_caissier, u.prenom_user as prenom_caissier,
                       ins.montant_scolarite_inscription
                FROM paiements p
                LEFT JOIN etudiants e ON e.code_etudiant = p.etudiant_code
                LEFT JOIN inscriptions ins ON (ins.code_inscription = p.inscription_code OR (ins.etudiant_code = p.etudiant_code AND ins.statut_inscription = 'actif'))
                LEFT JOIN classes cl ON (cl.code_classe = ins.classe_code OR cl.code_classe = p.classe_code)
                LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
                LEFT JOIN annees a ON (a.code_annee = p.annee_code OR a.code_annee = ins.annee_code)
                LEFT JOIN users u ON u.code_user = p.user_code
                WHERE p.id_paiement = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { header('Location: ' . RACINE . 'paiement/list'); exit(); }

            // Calcul du cumul payé par l'étudiant à ce jour
            $stmtCumul = $this->model->getCon()->prepare("
                SELECT COALESCE(SUM(montant_paye), 0) FROM paiements 
                WHERE etudiant_code = ? AND statut_paiement = 'valide'
            ");
            $stmtCumul->execute([$item['etudiant_code']]);
            $totalPayeCumul = (float)$stmtCumul->fetchColumn();

            $scolarite = (float)($item['montant_scolarite_inscription'] ?? 0);
            $soldeRestant = max(0, $scolarite - $totalPayeCumul);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'paiement/list'); exit();
        }
        $this->loadView('../views/paiements/details.php', [
            'item' => $item, 
            'totalPayeCumul' => $totalPayeCumul,
            'soldeRestant' => $soldeRestant,
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
            if (!$item) { header('Location: ' . RACINE . 'paiement/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'paiement/list'); exit();
        }
        $this->loadView('../views/paiements/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/paiements/edit.php', ['item' => []]);
    }
}
