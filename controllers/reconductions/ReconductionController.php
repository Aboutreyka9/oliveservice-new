<?php

class ReconductionController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelReconduction();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/reconductions/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $stmt = $this->model->getCon()->query("
            SELECT s.*, c.nom_client, c.prenom_client, p.libelle_pack, se.libelle_session
            FROM souscriptions s 
            LEFT JOIN clients c ON c.code_client = s.client_code
            LEFT JOIN packs p ON p.code_pack = s.pack_code
            LEFT JOIN sessions se ON se.code_session = s.session_code
            WHERE s.statut_souscription = 'solde' OR s.statut_souscription = 'distribue' OR s.statut_souscription = 'actif'
            ORDER BY s.id_souscription DESC
        ");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_souscription'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte,
                'nom_client_complet' => trim(($i['nom_client'] ?? '') . ' ' . ($i['prenom_client'] ?? ''))
            ]);
        }
        $this->json(['data' => $data]);
    }
}
