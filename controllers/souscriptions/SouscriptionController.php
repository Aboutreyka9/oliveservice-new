<?php

class SouscriptionController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelSouscription();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/souscriptions/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAllWithDetails();
        $data = [];

        foreach ($items as $s) {
            $id = $s['id_souscription'];
            $idCrypte = $this->validator->crypter($id);
            $soldeRestant = (float)($s['montant_total_prevu'] ?? 0) - (float)($s['montant_total_cotise'] ?? 0);
            $joursRestants = max(0, (int)($s['nombre_jour_total'] ?? 0) - (int)($s['nombre_jour_cotise'] ?? 0));
            $data[] = array_merge($s, [
                'id' => $id,
                'editId' => $idCrypte,
                'nom_client_complet' => trim($s['nom_client'] ?? ''),
                'solde_restant' => max(0, $soldeRestant),
                'jours_restants' => $joursRestants,
                'progression' => ($s['nombre_jour_total'] ?? 0) > 0 ? round((($s['nombre_jour_cotise'] ?? 0) / ($s['nombre_jour_total'] ?? 1)) * 100) : 0
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

        if (empty($data['client_code']) || empty($data['pack_code'])) {
            $this->error('Veuillez sélectionner un client et un pack !');
            return;
        }

        $stmtPack = $this->model->getCon()->prepare("SELECT * FROM packs WHERE code_pack = ?");
        $stmtPack->execute([$data['pack_code']]);
        $pack = $stmtPack->fetch(PDO::FETCH_ASSOC);

        if (!$pack) {
            $this->error('Pack sélectionné introuvable !');
            return;
        }

        $userCode = Context::user() ?? '';
        $etabCode = '5454544456';
        $anneeCode = Context::annee();
        $codeSouscription = $this->validator->generateCode('souscriptions', 'code_souscription', 'SUB-', 8);

        $nbJours = (int)($data['nombre_jour_total'] ?: ($pack['nombre_jour_pack'] ?: 170));
        $cotisJour = (float)($data['montant_cotisation_journaliere'] ?: ($pack['prix_cotisation_pack'] ?: 0));
        $montantTotal = $nbJours * $cotisJour;

        $souscriptionData = [
            'code_souscription' => $codeSouscription,
            'client_code' => $data['client_code'],
            'session_code' => $data['session_code'] ?: ($pack['session_code'] ?: null),
            'zone_code' => $data['zone_code'] ?: ($pack['zone_code'] ?: null),
            'date_debut_souscription' => $data['date_debut_souscription'] ?: date('Y-m-d'),
            'montant_total_prevu' => $montantTotal,
            'montant_cotisation_journaliere' => $cotisJour,
            'nombre_jour_total' => $nbJours,
            'nombre_jour_cotise' => 0,
            'montant_total_cotise' => 0,
            'statut_distribution' => 'En attente',
            'statut_souscription' => 'valide',
            'user_code' => $userCode,
            'etablissement_code' => $etabCode,
            'annee_code' => $anneeCode,
            'created_at_souscription' => date('Y-m-d H:i:s')
        ];

        if ($this->model->createSouscriptionWithPack($souscriptionData, $data['pack_code'], 1, $cotisJour)) {
            $this->success('Souscription créée avec succès !', ['code_souscription' => $codeSouscription]);
        } else {
            $this->error('Erreur lors de la création de la souscription');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_souscription');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        $data['updated_at_souscription'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE souscriptions")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Souscription modifiée avec succès!');
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
            $this->error('Souscription introuvable');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                $this->renderNotFound("La souscription demandée est introuvable.");
                return;
            }

            $packSouscrit = $this->model->getPackSouscrit($item['code_souscription']);
            $soldeRestant = $this->model->getSoldeRestant($item['code_souscription']);
            $joursRestants = $this->model->getJoursRestants($item['code_souscription']);

            $stmtClient = $this->model->getCon()->prepare("SELECT * FROM clients WHERE code_client = ?");
            $stmtClient->execute([$item['client_code']]);
            $client = $stmtClient->fetch(PDO::FETCH_ASSOC);

            $stmtCotis = $this->model->getCon()->prepare("
                SELECT c.*, u.nom_user, u.prenom_user
                FROM cautisation_clients c
                LEFT JOIN users u ON u.code_user = c.commercial_code
                WHERE c.souscription_code = ?
                ORDER BY c.date_cautisation DESC
            ");
            $stmtCotis->execute([$item['code_souscription']]);
            $cotisations = $stmtCotis->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            $this->renderNotFound("La souscription demandée est introuvable.");
            return;
        }
        $this->loadView('../views/souscriptions/details.php', [
            'item' => $item,
            'client' => $client,
            'packSouscrit' => $packSouscrit,
            'cotisations' => $cotisations,
            'soldeRestant' => $soldeRestant,
            'joursRestants' => $joursRestants,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'souscription/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'souscription/list'); exit();
        }
        $clients = $this->model->getCon()->query("SELECT code_client, nom_client, telephone_client FROM clients")->fetchAll(PDO::FETCH_ASSOC);
        $packs = $this->model->getCon()->query("SELECT code_pack, libelle_pack, prix_cotisation_pack, nombre_jour_pack FROM packs WHERE statut_pack='actif'")->fetchAll(PDO::FETCH_ASSOC);
        $sessions = $this->model->getCon()->query("SELECT code_session, libelle_session FROM sessions WHERE statut_session='actif'")->fetchAll(PDO::FETCH_ASSOC);
        $zones = $this->model->getCon()->query("SELECT code_zone, libelle_zone FROM zones WHERE statut_zone='actif'")->fetchAll(PDO::FETCH_ASSOC);

        $this->loadView('../views/souscriptions/edit.php', [
            'item' => $item,
            'clients' => $clients,
            'packs' => $packs,
            'sessions' => $sessions,
            'zones' => $zones,
            'encryptedId' => $encryptedId
        ]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $clients = $this->model->getCon()->query("SELECT code_client, nom_client, telephone_client FROM clients")->fetchAll(PDO::FETCH_ASSOC);
        $packs = $this->model->getCon()->query("SELECT code_pack, libelle_pack, prix_cotisation_pack, nombre_jour_pack FROM packs WHERE statut_pack='actif'")->fetchAll(PDO::FETCH_ASSOC);
        $sessions = $this->model->getCon()->query("SELECT code_session, libelle_session FROM sessions WHERE statut_session='actif'")->fetchAll(PDO::FETCH_ASSOC);
        $zones = $this->model->getCon()->query("SELECT code_zone, libelle_zone FROM zones WHERE statut_zone='actif'")->fetchAll(PDO::FETCH_ASSOC);

        $this->loadView('../views/souscriptions/edit.php', [
            'item' => [],
            'clients' => $clients,
            'packs' => $packs,
            'sessions' => $sessions,
            'zones' => $zones
        ]);
    }

    public function wizard()
    {
        $this->requireAuth();
        $sessions = $this->model->getCon()->query("SELECT code_session, libelle_session FROM sessions WHERE statut_session='actif'")->fetchAll(PDO::FETCH_ASSOC);
        $categories = $this->model->getCon()->query("SELECT code_categorie_pack, libelle_categorie_pack FROM categorie_packs WHERE statut_categorie_pack='actif'")->fetchAll(PDO::FETCH_ASSOC);

        $this->loadView('../views/souscriptions/wizard.php', [
            'sessions' => $sessions,
            'categories' => $categories
        ]);
    }

    public function wizardData()
    {
        $this->requireAuth();
        $sessionCode = $_GET['session_code'] ?? '';
        $categorieCode = $_GET['categorie_code'] ?? '';

        $sql = "SELECT p.code_pack, p.libelle_pack, p.prix_cotisation_pack, p.image_pack,
                       c.libelle_categorie_pack, c.code_categorie_pack,
                       COUNT(pa.article_code) as nombre_articles,
                       (SELECT COUNT(*) FROM pack_souscriptions ps WHERE ps.pack_code = p.code_pack) as nombre_souscriptions,
                       (SELECT nombre_jour_session FROM sessions WHERE code_session = p.session_code) as nombre_jour_session
                FROM packs p
                LEFT JOIN categorie_packs c ON c.code_categorie_pack = p.categorie_pack_code
                LEFT JOIN pack_articles pa ON pa.pack_code = p.code_pack
                WHERE p.statut_pack='actif'";
        $params = [];
        if ($sessionCode !== '') {
            $sql .= " AND p.session_code = ?";
            $params[] = $sessionCode;
        }
        if ($categorieCode !== '') {
            $sql .= " AND p.categorie_pack_code = ?";
            $params[] = $categorieCode;
        }
        $sql .= " GROUP BY p.code_pack, p.libelle_pack, p.prix_cotisation_pack, p.image_pack, c.libelle_categorie_pack, c.code_categorie_pack
                  ORDER BY c.libelle_categorie_pack, p.libelle_pack";

        $stmt = $this->model->getCon()->prepare($sql);
        $stmt->execute($params);
        $packs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->json(['status' => 1, 'data' => $packs]);
    }

    public function wizardSubmit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $data = $_POST;
        unset($data['csrf_token']);

        if (empty($data['nom_client']) || empty($data['telephone_client']) || empty($data['sexe_client']) || empty($data['lieu_residence_client'])) {
            $this->error('Veuillez remplir tous les champs obligatoires du client.');
            return;
        }

        $packs = json_decode($data['packs'] ?? '[]', true);
        if (empty($packs) || !is_array($packs) || empty($packs)) {
            $this->error('Veuillez sélectionner au moins un pack.');
            return;
        }

        $userCode = Context::user() ?? '';
        $etabCode = '5454544456';
        $anneeCode = Context::annee();

        $clientCode = $this->validator->generateCode('clients', 'code_client', 'CLI-', 8);
        $clientData = [
            'code_client' => $clientCode,
            'nom_client' => $data['nom_client'],
            'sexe_client' => $data['sexe_client'],
            'lieu_residence_client' => $data['lieu_residence_client'],
            'profession_client' => $data['profession_client'] ?? '',
            'telephone_client' => $data['telephone_client'],
            'email_client' => $data['email_client'] ?? '',
            'user_code' => $userCode,
            'zone_code' => $data['zone_code'] ?? '',
            'etablissement_code' => $etabCode,
            'statut_client' => 'actif',
            'created_at_client' => date('Y-m-d H:i:s')
        ];
        $this->model->getCon()->prepare("INSERT INTO clients (" . implode(',', array_keys($clientData)) . ") VALUES (" . implode(',', array_fill(0, count($clientData), '?')) . ")")->execute(array_values($clientData));

        $montantTotal = 0;
        $nbJoursTotal = 0;
        $sessionCode = $data['session_code'] ?? '';
        $zoneCode = $data['zone_code'] ?? '';

        $codeSouscription = $this->validator->generateCode('souscriptions', 'code_souscription', 'SUB-', 8);
        foreach ($packs as $packCode) {
            $stmtPack = $this->model->getCon()->prepare("SELECT prix_cotisation_pack FROM packs WHERE code_pack = ?");
            $stmtPack->execute([$packCode]);
            $pack = $stmtPack->fetch(PDO::FETCH_ASSOC);
            if ($pack) {
                $montantTotal += (float)($pack['prix_cotisation_pack'] ?? 0);
                $nbJoursTotal += (int)($pack['nombre_jour_pack'] ?? 0);
            }
        }

        $souscriptionData = [
            'code_souscription' => $codeSouscription,
            'client_code' => $clientCode,
            'session_code' => $sessionCode,
            'zone_code' => $zoneCode,
            'date_debut_souscription' => date('Y-m-d'),
            'montant_total_prevu' => $montantTotal,
            'montant_cotisation_journaliere' => $montantTotal > 0 && $nbJoursTotal > 0 ? round($montantTotal / $nbJoursTotal, 2) : 0,
            'nombre_jour_total' => $nbJoursTotal,
            'nombre_jour_cotise' => 0,
            'montant_total_cotise' => 0,
            'statut_distribution' => 'En attente',
            'statut_souscription' => 'valide',
            'user_code' => $userCode,
            'etablissement_code' => $etabCode,
            'annee_code' => $anneeCode,
            'created_at_souscription' => date('Y-m-d H:i:s')
        ];

        $this->model->createSouscriptionWithMultiplePacks($souscriptionData, $packs);
        $this->success('Souscription créée avec succès !', ['code_souscription' => $codeSouscription]);
    }
}
