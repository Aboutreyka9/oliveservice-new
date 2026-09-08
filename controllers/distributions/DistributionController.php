<?php

class DistributionController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelDistribution();
    }

    public function list()
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_DISTRIBUTIONS');
        $this->loadView('../views/distributions/list.php');
    }

    public function apiList()
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_DISTRIBUTIONS');
        $items = $this->model->getAllWithDetails();
        $data = [];

        foreach ($items as $d) {
            $id = $d['id_distribution'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($d, [
                'id' => $id,
                'editId' => $idCrypte,
                'nom_client_complet' => trim(($d['nom_client'] ?? '')),
                'nom_livreur_complet' => trim(($d['nom_livreur'] ?? '') . ' ' . ($d['prenom_livreur'] ?? ''))
            ]);
        }
        $this->json(['data' => $data]);
    }

    /**
     * API: Obtient les packs et le récapitulatif des articles d'une souscription soldée
     */
    public function getPacksInfo()
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_DISTRIBUTIONS');
        $souscriptionCode = trim($_GET['souscription_code'] ?? '');

        if (empty($souscriptionCode)) {
            $this->json(['status' => 0, 'message' => 'Code de souscription invalide.']);
            return;
        }

        $db = $this->model->getCon();
        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();
        $anneeCode = Context::annee();

        // 1. Récupérer la souscription et le client
        $stmtSous = $db->prepare("
            SELECT s.*, c.nom_client, c.telephone_client, c.code_client, c.lieu_residence_client, c.sexe_client
            FROM souscriptions s
            JOIN clients c ON c.code_client = s.client_code
            WHERE s.code_souscription = ? AND s.etablissement_code = ? AND s.zone_code = ? AND s.annee_code = ?
        ");
        $stmtSous->execute([$souscriptionCode, $etabCode, $zoneCode, $anneeCode]);
        $souscription = $stmtSous->fetch(PDO::FETCH_ASSOC);

        if (!$souscription) {
            $this->json(['status' => 0, 'message' => 'Souscription introuvable ou non autorisée.']);
            return;
        }

        if ($souscription['statut_souscription'] !== 'solde') {
            $this->json(['status' => 0, 'message' => 'La distribution est réservée aux souscriptions intégralement soldées.']);
            return;
        }

        // 2. Récupérer les packs et leurs articles
        $packs = $this->model->getPacksDetailsForSouscription($souscriptionCode);

        foreach ($packs as &$p) {
            $stmtArt = $db->prepare("
                SELECT pa.quantite_article, a.code_article, a.libelle_article
                FROM pack_articles pa
                JOIN articles a ON a.code_article = pa.article_code
                WHERE pa.pack_code = ?
            ");
            $stmtArt->execute([$p['pack_code']]);
            $p['articles'] = $stmtArt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        $this->json([
            'status' => 1,
            'souscription' => $souscription,
            'packs' => $packs
        ]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requirePermission('GESTIONNAIRE_MANAGE_DISTRIBUTIONS');
        $data = $_POST;
        unset($data['csrf_token']);

        $souscriptionCode = trim($data['souscription_code'] ?? '');

        if (empty($souscriptionCode)) {
            $this->error('Veuillez sélectionner une souscription !');
            return;
        }

        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();
        $anneeCode = Context::annee();
        $userCode = Context::user();

        if (empty($userCode) || empty($anneeCode) || empty($etabCode) || empty($zoneCode)) {
            $this->error("Erreur d'insertion : L'utilisateur connecté, la zone commerciale, l'année d'exercice et l'établissement sont obligatoires.");
            return;
        }

        $db = $this->model->getCon();
        $stmtSous = $db->prepare("
            SELECT * FROM souscriptions 
            WHERE code_souscription = ? AND etablissement_code = ? AND zone_code = ? AND annee_code = ?
        ");
        $stmtSous->execute([$souscriptionCode, $etabCode, $zoneCode, $anneeCode]);
        $sous = $stmtSous->fetch(PDO::FETCH_ASSOC);

        if (!$sous) {
            $this->error('Souscription introuvable !');
            return;
        }

        if ($sous['statut_souscription'] !== 'solde') {
            $this->error('La distribution n\'est possible que pour les souscriptions soldées (client ayant totalement payé).');
            return;
        }

        $codeDistribution = $this->validator->generateCode('distributions', 'code_distribution', 'DST-', 8);

        $distributionData = [
            'code_distribution' => $codeDistribution,
            'souscription_code' => $souscriptionCode,
            'client_code' => $sous['client_code'],
            'user_code' => $userCode,
            'zone_code' => $sous['zone_code'] ?? $zoneCode,
            'observation_distribution' => trim($data['observation_distribution'] ?? ''),
            'annee_code' => $anneeCode,
            'statut_distribution' => 'valide',
            'created_at_distribution' => date('Y-m-d H:i:s'),
            'etablissement_code' => $etabCode,
            'updated_at_distribution' => date('Y-m-d H:i:s')
        ];

        $rawPacks = $data['packs'] ?? '[]';
        $packsItems = is_array($rawPacks) ? $rawPacks : json_decode($rawPacks, true);

        if (empty($packsItems) || !is_array($packsItems)) {
            // Reconstitution automatique à partir de la souscription si non fourni
            $packsItems = $this->model->getPacksDetailsForSouscription($souscriptionCode);
            foreach ($packsItems as &$pi) {
                $pi['quantite_article_livree'] = $pi['quantite_article_attendue'];
            }
        }

        if ($this->model->createDistributionWithPacks($distributionData, $packsItems)) {
            $this->success("Distribution enregistrée avec succès ({$codeDistribution}) !", [
                'code_distribution' => $codeDistribution,
                'redirect' => RACINE . 'distribution/list'
            ]);
        } else {
            $this->error('Erreur lors de l\'enregistrement de la distribution');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requirePermission('GESTIONNAIRE_MANAGE_DISTRIBUTIONS');
        $id = (int)$this->post('id_distribution');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $existing = $this->model->getById($id);
        if (!$existing || $existing['etablissement_code'] !== Context::etablissement() || $existing['zone_code'] !== Context::zone() || $existing['annee_code'] !== Context::annee()) {
            $this->error('Distribution introuvable ou non autorisée');
            return;
        }

        $data = $_POST;
        unset($data['csrf_token']);

        $data['updated_at_distribution'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE distributions")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Distribution modifiée avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requirePermission('GESTIONNAIRE_MANAGE_DISTRIBUTIONS');
        $id = $this->post('id');
        if ($id && ($item = $this->model->getById($id))) {
            if ($item['etablissement_code'] !== Context::etablissement() || $item['zone_code'] !== Context::zone() || $item['annee_code'] !== Context::annee()) {
                $this->error('Distribution introuvable');
                return;
            }
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Distribution introuvable');
        }
    }

    public function details($details)
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_DISTRIBUTIONS');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item || $item['etablissement_code'] !== Context::etablissement() || $item['zone_code'] !== Context::zone() || $item['annee_code'] !== Context::annee()) {
                $this->renderNotFound("La distribution demandée est introuvable.");
                return;
            }

            $db = $this->model->getCon();
            $etabCode = Context::etablissement();
            $zoneCode = Context::zone();
            $anneeCode = Context::annee();

            // Client info
            $clientCode = $item['client_code'];
            $stmtC = $db->prepare("SELECT * FROM clients WHERE code_client = ?");
            $stmtC->execute([$clientCode]);
            $client = $stmtC->fetch(PDO::FETCH_ASSOC);

            // Souscription info
            $stmtSous = $db->prepare("SELECT * FROM souscriptions WHERE code_souscription = ?");
            $stmtSous->execute([$item['souscription_code']]);
            $souscription = $stmtSous->fetch(PDO::FETCH_ASSOC);

            // Agent de distribution info
            $stmtUser = $db->prepare("SELECT * FROM users WHERE code_user = ?");
            $stmtUser->execute([$item['user_code']]);
            $agent = $stmtUser->fetch(PDO::FETCH_ASSOC);

            // Details des packs et articles livres
            $distributionPacks = $this->model->getDistributionPacks($item['code_distribution']);

            foreach ($distributionPacks as &$dp) {
                $stmtArt = $db->prepare("
                    SELECT pa.quantite_article, a.code_article, a.libelle_article
                    FROM pack_articles pa
                    JOIN articles a ON a.code_article = pa.article_code
                    WHERE pa.pack_code = ?
                ");
                $stmtArt->execute([$dp['pack_code']]);
                $dp['articles'] = $stmtArt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            $this->renderNotFound("La distribution demandée est introuvable.");
            return;
        }

        $this->loadView('../views/distributions/details.php', [
            'item' => $item,
            'client' => $client,
            'souscription' => $souscription,
            'agent' => $agent,
            'distributionPacks' => $distributionPacks,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_DISTRIBUTIONS');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item || $item['etablissement_code'] !== Context::etablissement() || $item['zone_code'] !== Context::zone() || $item['annee_code'] !== Context::annee()) {
                header('Location: ' . RACINE . 'distribution/list'); exit();
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'distribution/list'); exit();
        }

        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();
        $anneeCode = Context::annee();

        $stmtSous = $this->model->getCon()->prepare("
            SELECT s.code_souscription, c.nom_client, s.statut_souscription 
            FROM souscriptions s 
            LEFT JOIN clients c ON c.code_client = s.client_code 
            WHERE s.statut_souscription = 'solde' AND s.etablissement_code = ? AND s.zone_code = ? AND s.annee_code = ?
        ");
        $stmtSous->execute([$etabCode, $zoneCode, $anneeCode]);
        $souscriptions = $stmtSous->fetchAll(PDO::FETCH_ASSOC);

        $agents = $this->model->getCon()->query("SELECT code_user, nom_user, prenom_user FROM users WHERE statut_user='actif'")->fetchAll(PDO::FETCH_ASSOC);

        $this->loadView('../views/distributions/edit.php', [
            'item' => $item,
            'souscriptions' => $souscriptions,
            'agents' => $agents,
            'encryptedId' => $encryptedId
        ]);
    }

    public function formulaire()
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_DISTRIBUTIONS');
        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();
        $anneeCode = Context::annee();

        // Récupérer les souscriptions soldées éligibles à la distribution (statut_distribution != 'valide')
        $stmtSous = $this->model->getCon()->prepare("
            SELECT s.code_souscription, s.client_code, c.nom_client, c.telephone_client, s.statut_souscription, s.statut_distribution, s.montant_total_prevu
            FROM souscriptions s 
            LEFT JOIN clients c ON c.code_client = s.client_code 
            WHERE s.statut_souscription = 'solde' AND (s.statut_distribution != 'valide' OR s.statut_distribution IS NULL)
              AND s.etablissement_code = ? AND s.zone_code = ? AND s.annee_code = ?
            ORDER BY s.created_at_souscription DESC
        ");
        $stmtSous->execute([$etabCode, $zoneCode, $anneeCode]);
        $souscriptions = $stmtSous->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $agents = $this->model->getCon()->query("
            SELECT u.code_user, u.nom_user, u.prenom_user, r.libelle_role as role_user 
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_code = u.code_user
            LEFT JOIN roles r ON r.code_role = ur.role_code
            WHERE u.statut_user='actif'
        ")->fetchAll(PDO::FETCH_ASSOC);

        $this->loadView('../views/distributions/edit.php', [
            'item' => [],
            'souscriptions' => $souscriptions,
            'agents' => $agents
        ]);
    }
}
