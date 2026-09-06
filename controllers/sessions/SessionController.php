<?php

class SessionController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelSession();
    }

    public function list()
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_SESSIONS');
        $this->loadView('../views/sessions/list.php');
    }

    public function apiList()
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_SESSIONS');
        $stmt = $this->model->getCon()->prepare("
            SELECT s.*, a.libelle_annee, z.libelle_zone 
            FROM sessions s 
            LEFT JOIN annees a ON a.code_annee = s.annee_code 
            LEFT JOIN zones z ON z.code_zone = s.zone_code 
            WHERE s.etablissement_code = ? AND s.zone_code = ? AND s.annee_code = ?
            ORDER BY s.id_session DESC
        ");
        $stmt->execute([Context::etablissement(), Context::zone(), Context::annee()]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_session'];
            $idCrypte = $this->validator->crypter($id);

            $days = (int)($i['nombre_jour_session'] ?? 0);
            if ($days <= 0 && !empty($i['date_debut_session']) && !empty($i['date_fin_session'])) {
                try {
                    $d1 = new DateTime($i['date_debut_session']);
                    $d2 = new DateTime($i['date_fin_session']);
                    $days = $d1->diff($d2)->days + 1;
                } catch (\Throwable $e) {
                    $days = 0;
                }
            }
            $i['nombre_jour_session'] = $days;

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
        $this->requirePermission('GESTIONNAIRE_MANAGE_SESSIONS');
        $data = $_POST;
        unset($data['csrf_token']);

        $userCode = Context::user() ?? '';
        $anneeCode = Context::annee();
        $etabCode = Context::etablissement();
        $zoneCode = !empty($data['zone_code']) ? $data['zone_code'] : Context::zone();

        if (empty($data['code_session'])) {
            $data['code_session'] = $this->validator->generateCode('sessions', 'code_session', 'SES-', 8);
        }
        $data['statut_session'] = $data['statut_session'] ?? 'inactif';
        $data['created_at_session'] = date('Y-m-d H:i:s');
        
        $cols = $this->model->getCon()->query("DESCRIBE sessions")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        if (in_array('zone_code', $cols)) $data['zone_code'] = $zoneCode;

        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Session d\'activité créée avec succès!');
        } else {
            $this->error('Erreur lors de la création de la session');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requirePermission('GESTIONNAIRE_MANAGE_SESSIONS');
        $id = (int)$this->post('id_session');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $existing = $this->model->getById($id);
        if (!$existing || $existing['etablissement_code'] !== Context::etablissement() || $existing['zone_code'] !== Context::zone() || $existing['annee_code'] !== Context::annee()) {
            $this->error('Session introuvable ou non autorisée');
            return;
        }

        $data = $_POST;
        unset($data['csrf_token']);
        $data['updated_at_session'] = date('Y-m-d H:i:s');

        $cols = $this->model->getCon()->query("DESCRIBE sessions")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Session d\'activité modifiée avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requirePermission('GESTIONNAIRE_MANAGE_SESSIONS');
        $id = $this->post('id');
        if ($id && ($item = $this->model->getById($id))) {
            if ($item['etablissement_code'] !== Context::etablissement() || $item['zone_code'] !== Context::zone() || $item['annee_code'] !== Context::annee()) {
                $this->error('Session introuvable');
                return;
            }
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Session introuvable');
        }
    }

    public function details($details)
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_SESSIONS');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item || $item['etablissement_code'] !== Context::etablissement() || $item['zone_code'] !== Context::zone() || $item['annee_code'] !== Context::annee()) {
                $this->renderNotFound("La session demandée est introuvable.");
                return;
            }
            
            $sessionCode = $item['code_session'];
            $etabCode = Context::etablissement();
            $zoneCode = Context::zone();
            $anneeCode = Context::annee();

            // Année liée
            $stmtAnnee = $this->model->getCon()->prepare("SELECT * FROM annees WHERE code_annee = ?");
            $stmtAnnee->execute([$item['annee_code'] ?? '']);
            $annee = $stmtAnnee->fetch(PDO::FETCH_ASSOC) ?: [];

            // Packs de cette session
            $stmtPacks = $this->model->getCon()->prepare("
                SELECT * FROM packs 
                WHERE session_code = ? AND etablissement_code = ? AND zone_code = ? AND annee_code = ?
                ORDER BY libelle_pack ASC
            ");
            $stmtPacks->execute([$sessionCode, $etabCode, $zoneCode, $anneeCode]);
            $packs = $stmtPacks->fetchAll(PDO::FETCH_ASSOC);

            // Souscriptions de cette session
            $stmtSous = $this->model->getCon()->prepare("
                SELECT s.*, c.nom_client, c.prenom_client, p.libelle_pack 
                FROM souscriptions s 
                LEFT JOIN clients c ON c.code_client = s.client_code 
                LEFT JOIN packs p ON p.code_pack = s.pack_code AND p.etablissement_code = ? AND p.zone_code = ? AND p.annee_code = ?
                WHERE s.session_code = ? AND s.etablissement_code = ? AND s.zone_code = ? AND s.annee_code = ?
                ORDER BY s.id_souscription DESC 
                LIMIT 50
            ");
            $stmtSous->execute([
                $etabCode, $zoneCode, $anneeCode,
                $sessionCode,
                $etabCode, $zoneCode, $anneeCode
            ]);
            $souscriptions = $stmtSous->fetchAll(PDO::FETCH_ASSOC);

            // Stats financières
            $stmtStats = $this->model->getCon()->prepare("
                SELECT 
                    COUNT(DISTINCT s.id_souscription) as total_souscriptions,
                    COALESCE(SUM(s.montant_total_cotise), 0) as total_cotise,
                    COALESCE(SUM(s.montant_total_prevu), 0) as total_prevu
                FROM souscriptions s 
                WHERE s.session_code = ? AND s.etablissement_code = ? AND s.zone_code = ? AND s.annee_code = ?
            ");
            $stmtStats->execute([$sessionCode, $etabCode, $zoneCode, $anneeCode]);
            $stats = $stmtStats->fetch(PDO::FETCH_ASSOC) ?: ['total_souscriptions' => 0, 'total_cotise' => 0, 'total_prevu' => 0];

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            $this->renderNotFound("La session demandée est introuvable.");
            return;
        }

        $this->loadView('../views/sessions/details.php', [
            'item' => $item,
            'annee' => $annee,
            'packs' => $packs,
            'souscriptions' => $souscriptions,
            'stats' => $stats,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_SESSIONS');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item || $item['etablissement_code'] !== Context::etablissement() || $item['zone_code'] !== Context::zone() || $item['annee_code'] !== Context::annee()) {
                header('Location: ' . RACINE . 'session/list'); exit();
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'session/list'); exit();
        }

        $currentAnneeCode = $item['annee_code'] ?? '';
        $sqlAnnees = "SELECT * FROM annees WHERE statut_annee = 'actif'";
        if (!empty($currentAnneeCode)) {
            $sqlAnnees .= " OR code_annee = " . $this->model->getCon()->quote($currentAnneeCode);
        }
        $sqlAnnees .= " ORDER BY id_annee DESC";

        $annees = $this->model->getCon()->query($sqlAnnees)->fetchAll(PDO::FETCH_ASSOC);
        $zones = $this->model->getCon()->query("SELECT * FROM zones ORDER BY libelle_zone ASC")->fetchAll(PDO::FETCH_ASSOC);
        $this->loadView('../views/sessions/edit.php', [
            'item' => $item, 
            'annees' => $annees,
            'zones' => $zones,
            'encryptedId' => $encryptedId
        ]);
    }

    public function formulaire()
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_SESSIONS');
        $annees = $this->model->getCon()->query("SELECT * FROM annees WHERE statut_annee = 'actif' ORDER BY id_annee DESC")->fetchAll(PDO::FETCH_ASSOC);
        $zones = $this->model->getCon()->query("SELECT * FROM zones ORDER BY libelle_zone ASC")->fetchAll(PDO::FETCH_ASSOC);
        $this->loadView('../views/sessions/edit.php', [
            'item' => [],
            'annees' => $annees,
            'zones' => $zones
        ]);
    }
}
