<?php

class DepenseController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelDepense();
    }

    public function list()
    {
        $this->requirePermission('FINANCE_MANAGE_DEPENSES');
        $this->loadView('../views/depenses/list.php');
    }

    public function apiList()
    {
        $this->requirePermission('FINANCE_MANAGE_DEPENSES');
        $items = $this->model->getAllWithDetails();
        $data = [];

        foreach ($items as $d) {
            $id = $d['id_depense'];
            $idCrypte = $this->validator->crypter($id);
            $createdAt = !empty($d['created_at_depense']) ? date('d/m/Y H:i', strtotime($d['created_at_depense'])) : '-';
            $periode = !empty($d['periode_depense']) ? date('d/m/Y', strtotime($d['periode_depense'])) : (!empty($d['date_depense']) ? date('d/m/Y', strtotime($d['date_depense'])) : '-');
            $data[] = array_merge($d, [
                'id' => $id,
                'editId' => $idCrypte,
                'date_enregistrer' => $createdAt,
                'periode' => $periode,
                'motif_depense' => $d['description_depense'] ?? ($d['motif_depense'] ?? '-'),
                'nom_auteur_complet' => trim(($d['nom_user'] ?? '') . ' ' . ($d['prenom_user'] ?? ''))
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requirePermission('FINANCE_MANAGE_DEPENSES');
        $data = $_POST;
        unset($data['csrf_token']);

        $montant = abs((float)($data['montant_depense'] ?? 0));

        if (empty($data['type_depense_code']) || $montant <= 0) {
            $this->error('Veuillez renseigner la catégorie de dépense et un montant positif strictement supérieur à zéro !');
            return;
        }

        $userCode = Context::user() ?? '';
        $anneeCode = Context::annee();
        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();

        // Validation de la catégorie de dépense en fonction de l'établissement, la zone et le statut actif
        $sqlCheckTd = "SELECT id_type_depense FROM type_depenses WHERE code_type_depense = ? AND statut_typedepense = 'actif'";
        $paramsCheckTd = [$data['type_depense_code']];
        if ($etabCode) {
            $sqlCheckTd .= " AND etablissement_code = ?";
            $paramsCheckTd[] = $etabCode;
        }
        if ($zoneCode) {
            $sqlCheckTd .= " AND zone_code = ?";
            $paramsCheckTd[] = $zoneCode;
        }
        $stmtCheckTd = $this->model->getCon()->prepare($sqlCheckTd);
        $stmtCheckTd->execute($paramsCheckTd);
        if (!$stmtCheckTd->fetch()) {
            $this->error('La catégorie de dépense sélectionnée est invalide, inactive ou non autorisée pour votre établissement et zone.');
            return;
        }

        $codeDepense = $this->validator->generateCode('depenses', 'code_depense', 'DEP-', 8);

        // Upload pièce justificative si existante (optionnelle)
        $filename = null;
        if (!empty($_FILES['piece_justificative']['name'])) {
            $uploadDir = __DIR__ . '/../../public/assets/images/depenses/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = strtolower(pathinfo($_FILES['piece_justificative']['name'], PATHINFO_EXTENSION));
            $filename = 'pj_' . time() . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['piece_justificative']['tmp_name'], $uploadDir . $filename);
        }

        // Statut par défaut : actif
        $statutInitial = 'inactif';

        $depenseData = [
            'code_depense' => $codeDepense,
            'type_depense_code' => $data['type_depense_code'],
            'description_depense' => $data['description_depense'] ?? ($data['motif_depense'] ?? ''),
            'montant_depense' => $montant,
            'periode_depense' => !empty($data['periode_depense']) ? $data['periode_depense'] : (!empty($data['date_depense']) ? $data['date_depense'] . ' ' . date('H:i:s') : date('Y-m-d H:i:s')),
            'annee_code' => $anneeCode,
            'etablissement_code' => $etabCode,
            'zone_code' => $zoneCode,
            'user_code' => $userCode,
            'statut_depense' => !empty($data['statut_depense']) ? $data['statut_depense'] : $statutInitial,
            'piece_joint' => $filename,
            'created_at_depense' => date('Y-m-d H:i:s')
        ];

        $cols = $this->model->getCon()->query("DESCRIBE depenses")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($depenseData, array_flip($cols));

        if ($this->model->create($filteredData)) {
            $this->success('Dépense enregistrée avec succès !', ['code' => $codeDepense]);
        } else {
            $this->error('Erreur lors de l\'enregistrement de la dépense');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requirePermission('FINANCE_MANAGE_DEPENSES');
        $id = (int)$this->post('id_depense');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $existing = $this->model->getById($id);
        if (!$existing || $existing['etablissement_code'] !== Context::etablissement() || $existing['zone_code'] !== Context::zone() || $existing['annee_code'] !== Context::annee()) {
            $this->error('Dépense introuvable ou non autorisée');
            return;
        }

        $data = $_POST;
        unset($data['csrf_token']);

        if (isset($data['montant_depense'])) {
            $montant = abs((float)$data['montant_depense']);
            if ($montant <= 0) {
                $this->error('Le montant de la dépense doit être un nombre positif strictement supérieur à zéro !');
                return;
            }
            $data['montant_depense'] = $montant;
        }

        // Upload nouvelle pièce justificative si fournie
        if (!empty($_FILES['piece_justificative']['name'])) {
            $uploadDir = __DIR__ . '/../../public/assets/images/depenses/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = strtolower(pathinfo($_FILES['piece_justificative']['name'], PATHINFO_EXTENSION));
            $filename = 'pj_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['piece_justificative']['tmp_name'], $uploadDir . $filename)) {
                $data['piece_joint'] = $filename;
            }
        }

        if (!empty($data['date_depense']) && empty($data['periode_depense'])) {
            $data['periode_depense'] = $data['date_depense'] . ' ' . date('H:i:s');
        }

        $data['updated_at_depense'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE depenses")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Dépense modifiée avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requirePermission('FINANCE_MANAGE_DEPENSES');
        $id = $this->post('id');
        if ($id && ($item = $this->model->getById($id))) {
            if ($item['etablissement_code'] !== Context::etablissement() || $item['zone_code'] !== Context::zone() || $item['annee_code'] !== Context::annee()) {
                $this->error('Dépense introuvable');
                return;
            }
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Dépense introuvable');
        }
    }

    public function details($details)
    {
        $this->requirePermission('FINANCE_MANAGE_DEPENSES');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item || $item['etablissement_code'] !== Context::etablissement() || $item['zone_code'] !== Context::zone() || $item['annee_code'] !== Context::annee()) {
                $this->renderNotFound("La dépense demandée est introuvable.");
                return;
            }

            $stmtTd = $this->model->getCon()->prepare("SELECT * FROM type_depenses WHERE code_type_depense = ?");
            $stmtTd->execute([$item['type_depense_code']]);
            $typeDepense = $stmtTd->fetch(PDO::FETCH_ASSOC);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            $this->renderNotFound("La dépense demandée est introuvable.");
            return;
        }
        $this->loadView('../views/depenses/details.php', [
            'item' => $item,
            'typeDepense' => $typeDepense,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requirePermission('FINANCE_MANAGE_DEPENSES');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item || $item['etablissement_code'] !== Context::etablissement() || $item['zone_code'] !== Context::zone() || $item['annee_code'] !== Context::annee()) {
                header('Location: ' . RACINE . 'depense/list'); exit();
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'depense/list'); exit();
        }

        // Filtrage en fonction de l'établissement, la zone et le statut actif (ou la catégorie déjà associée)
        $sql = "SELECT code_type_depense, libelle_type_depense FROM type_depenses WHERE (statut_typedepense = 'actif' OR code_type_depense = ?)";
        $params = [$item['type_depense_code'] ?? ''];
        if (Context::etablissement()) {
            $sql .= " AND etablissement_code = ?";
            $params[] = Context::etablissement();
        }
        if (Context::zone()) {
            $sql .= " AND zone_code = ?";
            $params[] = Context::zone();
        }
        $sql .= " ORDER BY libelle_type_depense ASC";
        $stmt = $this->model->getCon()->prepare($sql);
        $stmt->execute($params);
        $typeDepenses = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->loadView('../views/depenses/edit.php', [
            'item' => $item,
            'typeDepenses' => $typeDepenses,
            'encryptedId' => $encryptedId
        ]);
    }

    public function formulaire()
    {
        $this->requirePermission('FINANCE_MANAGE_DEPENSES');

        // Filtrage en fonction de l'établissement, la zone et le statut actif
        $sql = "SELECT code_type_depense, libelle_type_depense FROM type_depenses WHERE statut_typedepense = 'actif'";
        $params = [];
        if (Context::etablissement()) {
            $sql .= " AND etablissement_code = ?";
            $params[] = Context::etablissement();
        }
        if (Context::zone()) {
            $sql .= " AND zone_code = ?";
            $params[] = Context::zone();
        }
        $sql .= " ORDER BY libelle_type_depense ASC";
        $stmt = $this->model->getCon()->prepare($sql);
        $stmt->execute($params);
        $typeDepenses = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->loadView('../views/depenses/edit.php', [
            'item' => [],
            'typeDepenses' => $typeDepenses
        ]);
    }
}
