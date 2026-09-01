<?php

class AbonnementPressingController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelAbonnementPressing();
    }

    public function list()
    {
        $this->requireAuth();
        $db = $this->model->getCon();

        // Récupérer les pressings actifs
        $pressings = $db->query("SELECT code_pressing, libelle_pressing FROM " . TABLES::PRESSINGS . " WHERE statut_pressing = 'actif' ORDER BY libelle_pressing ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Récupérer les forfaits actifs
        $forfaits = $db->query("SELECT code_forfait, libelle_forfait, montant_forfait, duree_mois_forfait FROM " . TABLES::FORFAITS . " WHERE statut_forfait = 'actif' ORDER BY montant_forfait ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->loadView('../views/abonnements_pressings/list.php', [
            'pressings' => $pressings,
            'forfaits' => $forfaits,
            'isSuperAdmin' => $this->isSuperAdmin(),
            'currentPressingCode' => $this->getCurrentPressingCode()
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $pressingCode = $this->getCurrentPressingCode();
        $items = $this->model->getAllWithDetails($pressingCode);
        $data = [];

        foreach ($items as $i) {
            $idCrypte = $this->validator->crypter($i['id_abonnement_pressing']);
            $data[] = [
                'code' => $i['code_abonnement_pressing'],
                'pressing_code' => $i['pressing_code'] ?? '',
                'pressing' => $i['libelle_pressing'] ?? ($i['pressing_code'] ?? ''),
                'forfait_code' => $i['forfait_code'] ?? '',
                'forfait' => $i['libelle_forfait'] ?? ($i['forfait_code'] ?? ''),
                'montant' => (float)($i['montant_reel'] ?? 0),
                'date_debut' => !empty($i['date_debut_abonnement']) ? date('d/m/Y', strtotime($i['date_debut_abonnement'])) : '-',
                'date_fin' => !empty($i['date_fin_abonnement']) ? date('d/m/Y', strtotime($i['date_fin_abonnement'])) : '-',
                'date_debut_raw' => $i['date_debut_abonnement'] ?? '',
                'date_fin_raw' => $i['date_fin_abonnement'] ?? '',
                'jours_restants' => $i['jours_restants'] ?? null,
                'statut' => $i['statut_abonnement_pressing'] ?? 'actif',
                'id' => $i['id_abonnement_pressing'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function checkActive()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $pressingCode = trim($this->post('pressing_code'));
        if (empty($pressingCode)) {
            $this->json(['status' => 1, 'has_active' => false]);
            return;
        }

        $db = $this->model->getCon();
        $stmt = $db->prepare("
            SELECT 
                ap.*, 
                f.libelle_forfait, 
                f.montant_forfait, 
                DATEDIFF(ap.date_fin_abonnement, CURDATE()) as jours_restants,
                p.libelle_pressing
            FROM " . TABLES::ABONNEMENTS_PRESSINGS . " ap
            LEFT JOIN " . TABLES::FORFAITS . " f ON ap.forfait_code = f.code_forfait
            LEFT JOIN " . TABLES::PRESSINGS . " p ON ap.pressing_code = p.code_pressing
            WHERE ap.pressing_code = ? 
              AND ap.statut_abonnement_pressing = 'actif'
              AND ap.date_fin_abonnement >= CURDATE()
            ORDER BY ap.id_abonnement_pressing DESC 
            LIMIT 1
        ");
        $stmt->execute([$pressingCode]);
        $active = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($active) {
            $this->json([
                'status' => 1,
                'has_active' => true,
                'abonnement' => [
                    'id' => (int)$active['id_abonnement_pressing'],
                    'code' => $active['code_abonnement_pressing'],
                    'pressing_code' => $active['pressing_code'],
                    'pressing' => $active['libelle_pressing'] ?? $active['pressing_code'],
                    'forfait_code' => $active['forfait_code'],
                    'forfait' => $active['libelle_forfait'] ?? $active['forfait_code'],
                    'montant' => (float)($active['montant_abonnement'] ?? 0),
                    'date_debut' => !empty($active['date_debut_abonnement']) ? date('d/m/Y', strtotime($active['date_debut_abonnement'])) : '-',
                    'date_fin' => !empty($active['date_fin_abonnement']) ? date('d/m/Y', strtotime($active['date_fin_abonnement'])) : '-',
                    'date_fin_raw' => $active['date_fin_abonnement'],
                    'jours_restants' => (int)$active['jours_restants']
                ]
            ]);
        } else {
            $this->json(['status' => 1, 'has_active' => false]);
        }
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();

        if (!$this->isSuperAdmin()) {
            $this->error('Action réservée aux administrateurs', 403);
            return;
        }

        $pressingCode = trim($this->post('pressing_code'));
        $forfaitCode = trim($this->post('forfait_code'));

        if (empty($pressingCode) || empty($forfaitCode)) {
            $this->error('Veuillez sélectionner un pressing et un forfait B2B !');
            return;
        }

        $db = $this->model->getCon();

        // Vérifier si un abonnement est déjà actif
        $forceReplace = ($this->post('force_replace') === '1');
        if (!$forceReplace) {
            $stmtCheck = $db->prepare("
                SELECT ap.*, f.libelle_forfait, DATEDIFF(ap.date_fin_abonnement, CURDATE()) as jours_restants
                FROM " . TABLES::ABONNEMENTS_PRESSINGS . " ap
                LEFT JOIN " . TABLES::FORFAITS . " f ON ap.forfait_code = f.code_forfait
                WHERE ap.pressing_code = ? 
                  AND ap.statut_abonnement_pressing = 'actif'
                  AND ap.date_fin_abonnement >= CURDATE()
                LIMIT 1
            ");
            $stmtCheck->execute([$pressingCode]);
            $existingActive = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($existingActive) {
                $forfaitNom = $existingActive['libelle_forfait'] ?? $existingActive['forfait_code'];
                $dateFinFr = date('d/m/Y', strtotime($existingActive['date_fin_abonnement']));
                $jours = (int)$existingActive['jours_restants'];
                $this->json([
                    'status' => 0,
                    'has_active' => true,
                    'active_info' => $existingActive,
                    'message' => "Ce pressing possède déjà un abonnement actif ({$forfaitNom}) valable jusqu'au {$dateFinFr} ({$jours} jour(s) restant(s)). Vous pouvez soit le renouveler, soit le remplacer."
                ], 200);
                return;
            }
        }

        // Récupérer le forfait
        $stmtF = $db->prepare("SELECT * FROM " . TABLES::FORFAITS . " WHERE code_forfait = ? LIMIT 1");
        $stmtF->execute([$forfaitCode]);
        $forfait = $stmtF->fetch(PDO::FETCH_ASSOC);

        if (!$forfait) {
            $this->error('Forfait introuvable');
            return;
        }

        $dureeMois = (int)($this->post('duree_mois') ?: ($forfait['duree_mois_forfait'] ?? 1));
        if ($dureeMois < 1) $dureeMois = 1;

        $montant = (float)($this->post('montant_abonnement') !== '' ? $this->post('montant_abonnement') : ($forfait['montant_forfait'] ?? 0));
        $dateDebut = $this->post('date_debut_abonnement') ?: date('Y-m-d');
        $dateFin = $this->post('date_fin_abonnement') ?: date('Y-m-d', strtotime("+$dureeMois months", strtotime($dateDebut)));

        $code = 'ABN-' . strtoupper(substr(uniqid(), -6));

        // Désactiver les anciens abonnements actifs du même pressing
        $db->prepare("UPDATE " . TABLES::ABONNEMENTS_PRESSINGS . " SET statut_abonnement_pressing = 'expire' WHERE pressing_code = ? AND statut_abonnement_pressing = 'actif'")
           ->execute([$pressingCode]);

        $data = [
            'code_abonnement_pressing' => $code,
            'pressing_code' => $pressingCode,
            'forfait_code' => $forfaitCode,
            'montant_abonnement' => $montant,
            'date_debut_abonnement' => $dateDebut,
            'date_fin_abonnement' => $dateFin,
            'statut_abonnement_pressing' => 'actif'
        ];

        if ($this->model->create($data)) {
            $this->success("Abonnement {$code} activé avec succès pour le pressing !", ['code' => $code]);
        } else {
            $this->error("Erreur lors de la création de l'abonnement");
        }
    }

    public function renouveler()
    {
        $this->requirePost(false);
        $this->requireAuth();

        if (!$this->isSuperAdmin()) {
            $this->error('Action réservée aux administrateurs', 403);
            return;
        }

        $id = (int)$this->post('id_abonnement_pressing');
        $item = $this->model->getById($id);

        if (!$item) {
            $this->error('Abonnement introuvable');
            return;
        }

        $db = $this->model->getCon();

        // Forfait sélectionné ou actuel
        $forfaitCode = trim($this->post('forfait_code')) ?: $item['forfait_code'];
        $stmtF = $db->prepare("SELECT * FROM " . TABLES::FORFAITS . " WHERE code_forfait = ? LIMIT 1");
        $stmtF->execute([$forfaitCode]);
        $forfait = $stmtF->fetch(PDO::FETCH_ASSOC);

        $dureeMois = (int)($this->post('duree_mois') ?: ($forfait['duree_mois_forfait'] ?? 1));
        if ($dureeMois < 1) $dureeMois = 1;

        $montant = (float)($this->post('montant_abonnement') !== '' ? $this->post('montant_abonnement') : ($forfait['montant_forfait'] ?? $item['montant_abonnement']));

        // Calcul de la nouvelle date de fin
        $curDateFin = $item['date_fin_abonnement'] ?? date('Y-m-d');
        $baseDate = (strtotime($curDateFin) >= strtotime(date('Y-m-d'))) ? $curDateFin : date('Y-m-d');
        $newDateFin = date('Y-m-d', strtotime("+$dureeMois months", strtotime($baseDate)));

        $data = [
            'id_abonnement_pressing' => $id,
            'forfait_code' => $forfaitCode,
            'montant_abonnement' => $montant,
            'date_fin_abonnement' => $newDateFin,
            'statut_abonnement_pressing' => 'actif'
        ];

        if ($this->model->update($data)) {
            $this->success("Abonnement renouvelé avec succès jusqu'au " . date('d/m/Y', strtotime($newDateFin)) . " !");
        } else {
            $this->error("Erreur lors du renouvellement de l'abonnement");
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();

        if (!$this->isSuperAdmin()) {
            $this->error('Action réservée aux administrateurs', 403);
            return;
        }

        $id = (int)$this->post('id');
        $item = $this->model->getById($id);

        if (!$item) {
            $this->error('Abonnement introuvable');
            return;
        }

        $currentStatut = $item['statut_abonnement_pressing'] ?? 'actif';
        $newStatut = ($currentStatut === 'actif') ? 'suspendu' : 'actif';

        $data = [
            'id_abonnement_pressing' => $id,
            'statut_abonnement_pressing' => $newStatut
        ];

        if ($this->model->update($data)) {
            $this->success("Statut de l'abonnement changé en " . ucfirst($newStatut) . " !");
        } else {
            $this->error("Erreur lors du changement de statut");
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();

        if (!$this->isSuperAdmin()) {
            $this->error('Action réservée aux administrateurs', 403);
            return;
        }

        $id = (int)$this->post('id_abonnement_pressing');
        $item = $this->model->getById($id);

        if (!$item) {
            $this->error('Abonnement introuvable');
            return;
        }

        $data = [
            'id_abonnement_pressing' => $id,
            'forfait_code' => $this->post('forfait_code') ?: $item['forfait_code'],
            'montant_abonnement' => (float)$this->post('montant_abonnement'),
            'date_debut_abonnement' => $this->post('date_debut_abonnement') ?: $item['date_debut_abonnement'],
            'date_fin_abonnement' => $this->post('date_fin_abonnement') ?: $item['date_fin_abonnement'],
            'statut_abonnement_pressing' => $this->post('statut_abonnement_pressing') ?: $item['statut_abonnement_pressing']
        ];

        if ($this->model->update($data)) {
            $this->success("Abonnement mis à jour avec succès !");
        } else {
            $this->error("Erreur lors de la modification");
        }
    }
}
