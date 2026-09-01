<?php

class HorairePressingController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelHorairePressing();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/horaires_pressings/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $pressingCode = $this->getCurrentPressingCode();

        if ($pressingCode !== null) {
            $horaires = $this->model->getByPressing($pressingCode);
        } else {
            $horaires = $this->model->getAllWithPressing();
        }

        $data = [];

        foreach ($horaires as $h) {
            $idCrypte = $this->validator->crypter($h['id_horaire']);
            $isFerme = (int)($h['est_ferme'] ?? 0) === 1;
            $data[] = [
                'code' => $h['libelle_pressing'] ?? $h['pressing_code'],
                'pressing_name' => $h['libelle_pressing'] ?? '',
                'pressing_code' => $h['pressing_code'],
                'jour' => ucfirst($h['jour'] ?? ''),
                'heure_ouverture' => (!$isFerme && $h['heure_ouverture']) ? substr($h['heure_ouverture'], 0, 5) : '-',
                'heure_fermeture' => (!$isFerme && $h['heure_fermeture']) ? substr($h['heure_fermeture'], 0, 5) : '-',
                'est_ferme' => $isFerme ? 1 : 0,
                'statut' => $isFerme ? 'Fermé' : 'Ouvert',
                'id' => $h['id_horaire'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data, 'isSuperAdmin' => $this->isSuperAdmin()]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $pressingCode = $this->getCurrentPressingCode();
        if (empty($pressingCode)) {
            $pressingCode = $this->post('pressing_code') ?: 'PRS-001';
        }

        $this->requireActiveAbonnement($pressingCode, 'gérer vos horaires d\'ouverture');

        $notEmpty = Validator::validateRequiredFields(['jour' => $_POST['jour'] ?? '']);
        if ($notEmpty !== true) {
            $this->error('Le jour est requis !');
            return;
        }

        $jour = strtolower(trim($this->post('jour')));
        $estFerme = ((int)$this->post('est_ferme') === 1) ? 1 : 0;
        $heureOuverture = !$estFerme ? ($this->post('heure_ouverture') ?: '08:00:00') : null;
        $heureFermeture = !$estFerme ? ($this->post('heure_fermeture') ?: '18:00:00') : null;

        // Vérifier si un horaire existe déjà pour ce jour dans ce pressing
        $existing = $this->model->getCon()->prepare("SELECT id_horaire FROM " . TABLES::HORAIRES_PRESSINGS . " WHERE pressing_code = ? AND jour = ? LIMIT 1");
        $existing->execute([$pressingCode, $jour]);
        $existingId = $existing->fetchColumn();

        if ($existingId) {
            $stmtUp = $this->model->getCon()->prepare("
                UPDATE " . TABLES::HORAIRES_PRESSINGS . " 
                SET heure_ouverture = ?, heure_fermeture = ?, est_ferme = ?
                WHERE id_horaire = ?
            ");
            $stmtUp->execute([$heureOuverture, $heureFermeture, $estFerme, $existingId]);
            $this->success('Horaire mis à jour avec succès !');
            return;
        }

        $data = [
            'pressing_code' => $pressingCode,
            'jour' => $jour,
            'heure_ouverture' => $heureOuverture,
            'heure_fermeture' => $heureFermeture,
            'est_ferme' => $estFerme
        ];

        if ($this->model->create($data)) {
            $this->success('Horaire ajouté avec succès!');
        } else {
            $this->error('Erreur lors de l\'ajout de l\'horaire');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $id = (int) $this->post('id_horaire');
        $item = $this->model->getById($id);
        if (!$item) {
            $this->error('Horaire introuvable');
            return;
        }

        $pressingCode = $this->getCurrentPressingCode();
        if ($pressingCode !== null) {
            if (($item['pressing_code'] ?? '') !== $pressingCode) {
                $this->error('Accès refusé', 403);
                return;
            }
        } else {
            $pressingCode = $this->post('pressing_code') ?: ($item['pressing_code'] ?? 'PRS-001');
        }

        $this->requireActiveAbonnement($pressingCode, 'modifier vos horaires d\'ouverture');

        $notEmpty = Validator::validateRequiredFields(['jour' => $_POST['jour'] ?? '']);
        if ($notEmpty !== true) {
            $this->error('Le jour est requis !');
            return;
        }

        $jour = strtolower(trim($this->post('jour')));

        // Empêcher les doublons lors d'une modification de jour
        $existing = $this->model->getCon()->prepare("SELECT id_horaire FROM " . TABLES::HORAIRES_PRESSINGS . " WHERE pressing_code = ? AND jour = ? AND id_horaire != ? LIMIT 1");
        $existing->execute([$pressingCode, $jour, $id]);
        if ($existing->fetchColumn()) {
            $this->error('Ce pressing a déjà configuré une plage horaire pour ' . ucfirst($jour) . ' !');
            return;
        }

        $estFerme = ((int)$this->post('est_ferme') === 1) ? 1 : 0;
        $heureOuverture = !$estFerme ? ($this->post('heure_ouverture') ?: '08:00:00') : null;
        $heureFermeture = !$estFerme ? ($this->post('heure_fermeture') ?: '18:00:00') : null;

        $data = [
            'id_horaire' => $id,
            'pressing_code' => $pressingCode,
            'jour' => strtolower(trim($this->post('jour'))),
            'heure_ouverture' => $heureOuverture,
            'heure_fermeture' => $heureFermeture,
            'est_ferme' => $estFerme
        ];

        if ($this->model->update($data)) {
            $this->success('Horaire modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'horaire/list');
                exit();
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'horaire/list');
            exit();
        }

        $this->loadView('../views/horaires_pressings/details.php', [
            'horaire' => $item,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'horaire/list');
                exit();
            }
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'horaire/list');
            exit();
        }

        $pressings = (new ModelPressing())->getByStatus('actif');

        $this->loadView('../views/horaires_pressings/edit.php', [
            'horaire' => $item,
            'pressings' => $pressings
        ]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $pressings = (new ModelPressing())->getByStatus('actif');
        $this->loadView('../views/horaires_pressings/edit.php', [
            'horaire' => [],
            'pressings' => $pressings
        ]);
    }
}
