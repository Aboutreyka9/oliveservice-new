<?php

class VersementController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelVersement();
    }

    public function list()
    {
        $this->requirePermission(['COMMERCIAL_MAKE_VERSEMENT', 'FINANCE_VALIDATE_VERSEMENT']);
        $this->loadView('../views/versements/list.php');
    }

    public function apiList()
    {
        $this->requirePermission(['COMMERCIAL_MAKE_VERSEMENT', 'FINANCE_VALIDATE_VERSEMENT']);
        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();
        $anneeCode = Context::annee();

        $sql = "
            SELECT v.*, 
                   uc.nom_user as nom_commercial, uc.prenom_user as prenom_commercial,
                   uv.nom_user as nom_validator, uv.prenom_user as prenom_validator,
                   z.libelle_zone
            FROM versements_commerciaux v
            LEFT JOIN users uc ON uc.code_user = v.commercial_code
            LEFT JOIN users uv ON uv.code_user = v.user_validate
            LEFT JOIN zones z ON z.code_zone = v.zone_code
            WHERE v.etablissement_code = ? AND v.zone_code = ? AND v.annee_code = ?
        ";
        $params = [$etabCode, $zoneCode, $anneeCode];

        // RÈGLE RBAC : Le commercial ne voit que ses propres versements
        if (Context::isCommercial()) {
            $sql .= " AND (v.commercial_code = ? OR v.user_code = ?)";
            $params[] = Context::user();
            $params[] = Context::user();
        }

        $sql .= " ORDER BY v.created_at_versement DESC";

        $stmt = $this->model->getCon()->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $data = [];

        foreach ($items as $v) {
            $id = $v['id_versement'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($v, [
                'id' => $id,
                'editId' => $idCrypte,
                'nom_commercial_complet' => trim(($v['nom_commercial'] ?? '') . ' ' . ($v['prenom_commercial'] ?? '')),
                'nom_validator_complet' => trim(($v['nom_validator'] ?? '') . ' ' . ($v['prenom_validator'] ?? ''))
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requirePermission('COMMERCIAL_MAKE_VERSEMENT');
        $data = $_POST;
        unset($data['csrf_token']);

        $userCode = Context::user();
        $etabCode = Context::etablissement();
        $anneeCode = Context::annee();
        $zoneCode = !empty($data['zone_code']) ? $data['zone_code'] : Context::zone();

        if (empty($userCode) || empty($anneeCode) || empty($etabCode) || empty($zoneCode)) {
            $this->error("Erreur d'insertion : L'utilisateur connecté, la zone commerciale, l'année d'exercice et l'établissement sont obligatoires et ne peuvent pas être null.");
            return;
        }

        $codeVersement = $this->validator->generateCode('versements_commerciaux', 'code_versement_commercial', 'VRS-', 8);

        $commercialCode = !empty($data['commercial_code']) ? $data['commercial_code'] : $userCode;
        if (empty($commercialCode) || empty($data['montant_versement'])) {
            $this->error('Veuillez renseigner le commercial et le montant versé !');
            return;
        }

        $periodeDebut = !empty($data['periode_versement_debut']) ? $data['periode_versement_debut'] : (!empty($data['periode_versement']) ? $data['periode_versement'] : date('Y-m-d'));

        $versementData = [
            'code_versement_commercial' => $codeVersement,
            'reference_versement' => $data['reference_versement'] ?? $codeVersement,
            'montant_versement' => (int)$data['montant_versement'],
            'commercial_code' => $commercialCode,
            'periode_versement' => $periodeDebut,
            'periode_versement_debut' => $periodeDebut,
            'periode_versement_fin' => !empty($data['periode_versement_fin']) ? $data['periode_versement_fin'] : date('Y-m-d'),
            'zone_code' => $zoneCode,
            'statut_versement' => 'En attente',
            'etablissement_code' => $etabCode,
            'annee_code' => $anneeCode,
            'user_code' => $userCode,
            'created_at_versement' => date('Y-m-d H:i:s'),
            'user_validate' => '',
            'date_validation' => '1000-01-01 00:00:00',
            'commentaire_validation' => ''
        ];

        $cols = $this->model->getCon()->query("DESCRIBE versements_commerciaux")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($versementData, array_flip($cols));

        if ($this->model->create($filteredData)) {
            // Notification pour le service Finance
            try {
                $userModel = new ModelUser();
                $commercial = $userModel->getByCode($commercialCode);
                $commNom = $commercial ? trim(($commercial['nom_user'] ?? '') . ' ' . ($commercial['prenom_user'] ?? '')) : 'Un agent commercial';

                NotificationService::notifyVersementSoumis([
                    'reference_code'     => $codeVersement,
                    'montant'            => (float)$versementData['montant_versement'],
                    'commercial_nom'     => $commNom,
                    'etablissement_code' => $etabCode,
                    'zone_code'          => $zoneCode,
                    'annee_code'         => $anneeCode
                ]);
            } catch (\Throwable $ne) {
                error_log('[VersementController] Notification error on submit: ' . $ne->getMessage());
            }

            $this->success('Versement de caisse transmis avec succès (En attente de validation finance) !', ['code' => $codeVersement]);
        } else {
            $this->error('Erreur lors de l\'enregistrement du versement');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requirePermission('FINANCE_VALIDATE_VERSEMENT');

        if (Context::isCommercial()) {
            $this->error('Action non autorisée. Les commerciaux ne peuvent pas modifier un versement transmis.');
            return;
        }

        $id = (int)$this->post('id_versement');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $existing = $this->model->getById($id);
        if (!$existing || $existing['etablissement_code'] !== Context::etablissement() || $existing['zone_code'] !== Context::zone() || $existing['annee_code'] !== Context::annee()) {
            $this->error('Versement introuvable ou non autorisé');
            return;
        }

        $data = $_POST;
        unset($data['csrf_token']);

        $cols = $this->model->getCon()->query("DESCRIBE versements_commerciaux")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Versement modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function valider()
    {
        $this->requirePost(false);
        $this->requirePermission('FINANCE_VALIDATE_VERSEMENT');

        // RÈGLE RBAC : Seul le profil Finance / Admin peut valider ou rejeter un versement
        if (!Context::isFinance() && !Context::isAdmin()) {
            $this->error('Action non autorisée. Seul le service Comptabilité / Finance ou l\'Administration peut valider un versement.');
            return;
        }

        $id = (int)$this->post('id_versement');
        $statutRaw = strtolower(trim($this->post('statut_versement') ?? 'valide'));
        $statut = in_array($statutRaw, ['valide', 'validé'], true) ? 'valide' : (in_array($statutRaw, ['annule', 'annulé', 'rejete', 'rejeté'], true) ? 'annule' : 'valide');
        $commentaire = trim($this->post('commentaire_validation') ?? '');
        if (empty($commentaire)) {
            $commentaire = ($statut === 'valide') ? 'Validé par la comptabilité' : 'Rejeté / Annulé par la comptabilité';
        }
        $userValidateCode = Context::user() ?? '';

        if (!$id) {
            $this->error('Identifiant de versement invalide');
            return;
        }

        $versement = $this->model->getById($id);
        if (!$versement || $versement['etablissement_code'] !== Context::etablissement() || $versement['zone_code'] !== Context::zone() || $versement['annee_code'] !== Context::annee()) {
            $this->error('Versement introuvable.');
            return;
        }

        // Valider le versement et basculer les cotisations associées du commercial en 'valide'
        if ($this->model->validateVersement($id, $userValidateCode, $commentaire, $statut)) {
            if ($statut === 'valide') {
                $stmtCotis = $this->model->getCon()->prepare("
                    UPDATE cautisation_clients 
                    SET statut_cautisation_client = 'valide', updated_at_cautisation_client = NOW()
                    WHERE commercial_code = ? AND statut_cautisation_client = 'en_attente'
                      AND etablissement_code = ? AND zone_code = ? AND annee_code = ?
                ");
                $stmtCotis->execute([
                    $versement['commercial_code'],
                    Context::etablissement(),
                    Context::zone(),
                    Context::annee()
                ]);
            }

            // Notification pour le commercial ayant émis le versement
            try {
                NotificationService::notifyVersementValide([
                    'reference_code'     => $versement['code_versement'] ?? ('VER-' . $id),
                    'commercial_code'    => $versement['commercial_code'],
                    'montant'            => (float)($versement['montant_versement'] ?? 0),
                    'statut'             => $statut,
                    'etablissement_code' => Context::etablissement(),
                    'zone_code'          => Context::zone(),
                    'annee_code'         => Context::annee()
                ]);
            } catch (\Throwable $ne) {
                error_log('[VersementController] Notification error on validate: ' . $ne->getMessage());
            }

            $msg = ($statut === 'valide') 
                ? 'Versement validé et cotisations du commercial actualisées avec succès !' 
                : 'Versement rejeté / annulé avec succès !';
            $this->success($msg, ['reload' => true]);
        } else {
            $this->error('Erreur lors de la validation du versement');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requirePermission('FINANCE_VALIDATE_VERSEMENT');

        if (!Context::isFinance() && !Context::isAdmin()) {
            $this->error('Action non autorisée. Seul le service Comptabilité / Finance peut modifier le statut d\'un versement.');
            return;
        }

        $id = $this->post('id');
        if ($id && ($item = $this->model->getById($id))) {
            if ($item['etablissement_code'] !== Context::etablissement() || $item['zone_code'] !== Context::zone() || $item['annee_code'] !== Context::annee()) {
                $this->error('Versement introuvable');
                return;
            }
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Versement introuvable');
        }
    }

    public function details($details)
    {
        $this->requirePermission(['COMMERCIAL_MAKE_VERSEMENT', 'FINANCE_VALIDATE_VERSEMENT']);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item || $item['etablissement_code'] !== Context::etablissement() || $item['zone_code'] !== Context::zone() || $item['annee_code'] !== Context::annee()) {
                $this->renderNotFound("Le versement demandé est introuvable.");
                return;
            }

            // Un commercial ne peut consulter que ses propres versements
            if (Context::isCommercial() && $item['commercial_code'] !== Context::user() && $item['user_code'] !== Context::user()) {
                $this->renderForbidden("Vous n'êtes pas autorisé à consulter ce versement.");
                return;
            }

            $stmtU = $this->model->getCon()->prepare("SELECT * FROM users WHERE code_user = ?");
            $stmtU->execute([$item['commercial_code']]);
            $commercial = $stmtU->fetch(PDO::FETCH_ASSOC);

            $stmtZ = $this->model->getCon()->prepare("SELECT * FROM zones WHERE code_zone = ?");
            $stmtZ->execute([$item['zone_code']]);
            $zone = $stmtZ->fetch(PDO::FETCH_ASSOC);

            $validatorUser = null;
            if (!empty($item['user_validate'])) {
                $stmtV = $this->model->getCon()->prepare("SELECT code_user, nom_user, prenom_user FROM users WHERE code_user = ?");
                $stmtV->execute([$item['user_validate']]);
                $validatorUser = $stmtV->fetch(PDO::FETCH_ASSOC);
            }

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            $this->renderNotFound("Le versement demandé est introuvable.");
            return;
        }
        $canValidate = Context::isFinance() || Context::isAdmin();
        $this->loadView('../views/versements/details.php', [
            'item' => $item,
            'commercial' => $commercial,
            'zone' => $zone,
            'validatorUser' => $validatorUser,
            'canValidate' => $canValidate,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requirePermission('FINANCE_VALIDATE_VERSEMENT');
        if (Context::isCommercial()) {
            header('Location: ' . RACINE . 'versement/list');
            exit();
        }

        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item || $item['etablissement_code'] !== Context::etablissement() || $item['zone_code'] !== Context::zone() || $item['annee_code'] !== Context::annee()) { 
                header('Location: ' . RACINE . 'versement/list'); exit(); 
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'versement/list'); exit();
        }
        $commerciaux = $this->model->getCon()->query("SELECT code_user, nom_user, prenom_user FROM users WHERE statut_user='actif'")->fetchAll(PDO::FETCH_ASSOC);
        $zones = $this->model->getCon()->query("SELECT code_zone, libelle_zone FROM zones WHERE statut_zone='actif'")->fetchAll(PDO::FETCH_ASSOC);

        $this->loadView('../views/versements/edit.php', [
            'item' => $item,
            'commerciaux' => $commerciaux,
            'zones' => $zones,
            'encryptedId' => $encryptedId
        ]);
    }

    public function formulaire()
    {
        $this->requirePermission('COMMERCIAL_MAKE_VERSEMENT');
        $commerciaux = $this->model->getCon()->query("SELECT code_user, nom_user, prenom_user FROM users WHERE statut_user='actif'")->fetchAll(PDO::FETCH_ASSOC);
        $zones = $this->model->getCon()->query("SELECT code_zone, libelle_zone FROM zones WHERE statut_zone='actif'")->fetchAll(PDO::FETCH_ASSOC);

        $this->loadView('../views/versements/edit.php', [
            'item' => [],
            'commerciaux' => $commerciaux,
            'zones' => $zones
        ]);
    }
}
