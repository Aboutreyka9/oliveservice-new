<?php

class CautisationPaymentController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelCotisation();
    }

    /**
     * Affiche le formulaire de recherche de souscription
     */
    public function searchForm()
    {
        $this->requireAuth();
        $this->loadView('../views/cautisations_payment/search.php');
    }

    /**
     * API: Recherche des souscriptions selon les critères
     * Critères: telephone, nom_client, code_client, code_souscription
     */
    public function search()
    {
        $this->requireAuth();
        
        // Pour les requêtes AJAX, ne pas vérifier le CSRF token
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Méthode POST requise'], 405);
            return;
        }
        
        $criteria = $this->post('criteria') ?? '';
        $type = $this->post('type') ?? 'all'; // all, phone, name, code, subscription
        
        if (empty($criteria)) {
            $this->json(['error' => 'Veuillez entrer un critère de recherche'], 400);
            return;
        }

        $souscriptions = $this->searchSouscriptions($criteria, $type);
        
        if (empty($souscriptions)) {
            $this->json(['message' => 'Aucune souscription trouvée'], 404);
            return;
        }

        $data = [];
        foreach ($souscriptions as $s) {
            $montantTotal = $this->getTotalPackAmount($s['code_souscription']);
            $data[] = [
                'code_souscription' => $s['code_souscription'],
                'nom_client' => $s['nom_client'] ?? '-',
                'prenom_client' => '',
                'nom_complet' => trim(($s['nom_client'] ?? '')),
                'telephone' => $s['telephone_client'] ?? '-',
                'libelle_session' => $s['libelle_session'] ?? '-',
                'montant_total' => $montantTotal,
                'statut' => $s['statut_souscription'] ?? '-'
            ];
        }

        $this->json(['data' => $data]);
    }

    /**
     * Affiche la situation d'une souscription
     */
    public function situation($codesouscription = null)
    {
        $this->requireAuth();
        
        $code = $codesouscription ?? ($_GET['code'] ?? null);
        if (!$code) {
            header('Location: ' . RACINE . 'cautisation-payment/search-form');
            exit();
        }

        $souscription = $this->getSouscriptionWithDetails($code);
        if (!$souscription) {
            $this->renderNotFound('Souscription introuvable');
            return;
        }

        $this->loadView('../views/cautisations_payment/situation.php', [
            'souscription' => $souscription
        ]);
    }

    /**
     * API: Récupère les détails complets d'une souscription pour affichage
     */
    public function situationDetails()
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Méthode POST requise'], 405);
            return;
        }
        
        $code = $this->post('code_souscription');
        if (!$code) {
            $this->json(['error' => 'Code souscription requis'], 400);
            return;
        }

        $souscription = $this->getSouscriptionWithDetails($code);
        if (!$souscription) {
            $this->json(['error' => 'Souscription introuvable'], 404);
            return;
        }

        $this->json(['data' => $souscription]);
    }

    /**
     * API: Récupère l'historique des paiements (cautisations) pour une souscription
     */
    public function history()
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Méthode POST requise'], 405);
            return;
        }
        
        $code = $this->post('code_souscription');
        if (!$code) {
            $this->json(['error' => 'Code souscription requis'], 400);
            return;
        }

        $cautisations = $this->model->getBySouscription($code);
        
        $data = [];
        foreach ($cautisations as $c) {
            $data[] = [
                'date_paiement' => isset($c['created_at_cautisation_client']) 
                    ? date('d-m-Y', strtotime($c['created_at_cautisation_client'])) 
                    : '-',
                'montant' => $c['montant_cautisation_client'] ?? 0,
                'nombre_jours' => $c['nombre_jour'] ?? 0,
                'mode_paiement' => $c['mode_paiement'] ?? '-',
                'statut' => $c['statut_cautisation_client'] ?? '-'
            ];
        }

        $this->json(['data' => $data]);
    }

    /**
     * API: Enregistre un paiement de cautisation
     */
    public function savepayment()
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Méthode POST requise'], 405);
            return;
        }
        
        $codeSouscription = $this->post('code_souscription');
        $montant = (float)($this->post('montant') ?? 0);
        $nombreJours = (int)($this->post('nombre_jours') ?? 0);
        $modePaiement = $this->post('mode_paiement') ?? 'especes';
        $typePaiement = $this->post('type_paiement') ?? 'montant'; // montant ou jours
        
        // Valider les données
        $validationResult = CautisationValidator::validatePaymentData([
            'code_souscription' => $codeSouscription,
            'montant' => $montant,
            'nombre_jours' => $nombreJours,
            'mode_paiement' => $modePaiement,
            'type_paiement' => $typePaiement
        ]);

        if (!$validationResult['valid']) {
            $this->error(implode(', ', $validationResult['errors']));
            return;
        }

        // Récupérer la souscription
        $souscription = $this->getSouscriptionWithDetails($codeSouscription);
        if (!$souscription) {
            $this->error('Souscription introuvable');
            return;
        }

        // Vérifier le statut
        if ($souscription['statut_souscription'] !== 'valide') {
            $this->error('Cette souscription ne peut pas être payée (statut: ' . $souscription['statut_souscription'] . ')');
            return;
        }

        // Récupérer le prix de cotisation journalière
        $prixCotisationJournaliere = (float)($souscription['prix_cotisation_pack'] ?? 0);
        if ($prixCotisationJournaliere <= 0) {
            $this->error('Prix de cotisation introuvable');
            return;
        }

        // Calculer montant et jours selon le type de paiement
        if ($typePaiement === 'jours') {
            $montant = CautisationValidator::calculateAmount($nombreJours, $prixCotisationJournaliere);
        } else {
            // Validation: le montant doit être un multiple du prix
            $validAmount = CautisationValidator::validateAmount($montant, $prixCotisationJournaliere);
            if (!$validAmount['valid']) {
                $this->error($validAmount['message'] . 
                    (isset($validAmount['suggested_amount']) ? 
                        '. Montant suggéré: ' . CautisationValidator::formatCurrency($validAmount['suggested_amount']) : ''));
                return;
            }
            $nombreJours = CautisationValidator::calculateDays($montant, $prixCotisationJournaliere);
        }

        // Vérifier le montant restant
        $montantRestant = (float)($souscription['montant_restant_a_payer'] ?? 0);
        $validMontantRestant = CautisationValidator::validateAmountNotExceeds($montant, $montantRestant);
        if (!$validMontantRestant['valid']) {
            $this->error($validMontantRestant['message']);
            return;
        }

        // Vérifier le nombre de jours restants
        $joursRestants = (int)($souscription['nombre_jours_restant'] ?? 0);
        $validDaysRestant = CautisationValidator::validateDaysNotExceeds($nombreJours, $joursRestants);
        if (!$validDaysRestant['valid']) {
            $this->error($validDaysRestant['message']);
            return;
        }

        // Générer le code de cautisation
        $codeCautisation = CautisationValidator::generateCode('CAUT-');
        
        // Préparer les données
        $userCode = Context::user() ?? '';
        $anneeCode = Context::annee() ?? $souscription['annee_code'] ?? '';
        $zoneCode = $souscription['zone_code'] ?? '';
        $etabCode = $souscription['etablissement_code'] ?? '5454544456';
        
        // Récupérer la caisse ouverte
        $caisse = $this->getOpenCaisse($zoneCode, $etabCode);
        $caisseCode = $caisse['code_caisse'] ?? 'CAISSE-DEFAULT';

        $cautisationData = [
            'code_cautisation_client' => $codeCautisation,
            'souscription_code' => $codeSouscription,
            'client_code' => $souscription['client_code'] ?? Context::user() ?? '',
            'commercial_code' => $userCode,
            'date_cautisation' => date('Y-m-d H:i:s'),
            'montant_cautisation_client' => $montant,
            'nombre_jour' => $nombreJours,
            'nombre_jour_paye' => $nombreJours,
            'statut_cautisation_client' => 'valide',
            'mode_paiement' => $modePaiement,
            'created_at_cautisation_client' => date('Y-m-d H:i:s'),
            'updated_at_cautisation_client' => date('Y-m-d H:i:s'),
            'etablissement_code' => $etabCode,
            'user_code' => $userCode,
            'annee_code' => $anneeCode,
            'zone_code' => $zoneCode,
            'caisse_code' => $caisseCode
        ];

        // Enregistrer la cautisation
        if ($this->model->createCotisation($cautisationData)) {
            // Calculer la date du prochain rendez-vous
            $dateProchainRdv = CautisationValidator::calculateNextDate($nombreJours);
            
            $this->success('Paiement enregistré avec succès !', [
                'code_cautisation' => $codeCautisation,
                'prochain_rdv' => $dateProchainRdv,
                'reload' => true
            ]);
        } else {
            $this->error('Erreur lors de l\'enregistrement du paiement');
        }
    }

    /**
     * Recherche les souscriptions selon les critères
     */
    private function searchSouscriptions(string $criteria, string $type): array
    {
        $con = $this->model->getCon();
        
        $sql = "
            SELECT s.*, 
                   c.nom_client, c.telephone_client,
                   sess.libelle_session, sess.nombre_jour_session
             FROM souscriptions s
            LEFT JOIN clients c ON c.code_client = s.client_code
            LEFT JOIN sessions sess ON sess.code_session = s.session_code
            WHERE s.statut_souscription = 'valide'
        ";

        $params = [];
        
        if ($type === 'phone' || $type === 'all') {
            $sql_phone = $sql . " AND c.telephone_client LIKE ?";
            $stmt = $con->prepare($sql_phone);
            $stmt->execute(['%' . $criteria . '%']);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($results)) {
                return $results;
            }
        }

        if ($type === 'name' || $type === 'all') {
             $sql_name = $sql . " AND c.nom_client LIKE ?";
             $stmt = $con->prepare($sql_name);
             $stmt->execute(['%' . $criteria . '%']);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($results)) {
                return $results;
            }
        }

        if ($type === 'code' || $type === 'all') {
            $sql_client_code = $sql . " AND c.code_client LIKE ?";
            $stmt = $con->prepare($sql_client_code);
            $stmt->execute(['%' . $criteria . '%']);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($results)) {
                return $results;
            }
        }

        if ($type === 'subscription' || $type === 'all') {
            $sql_sub_code = $sql . " AND s.code_souscription LIKE ?";
            $stmt = $con->prepare($sql_sub_code);
            $stmt->execute(['%' . $criteria . '%']);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($results)) {
                return $results;
            }
        }

        return [];
    }

    /**
     * Récupère les détails complets d'une souscription
     */
    private function getSouscriptionWithDetails(string $codeSouscription): ?array
    {
        $con = $this->model->getCon();
        
        $sql = "
            SELECT s.*,
                   c.code_client, c.nom_client, c.telephone_client,
                   c.sexe_client, c.lieu_residence_client, c.profession_client, c.email_client,
                   sess.libelle_session, sess.nombre_jour_session,
                   (SELECT COALESCE(SUM(p.prix_cotisation_pack), 0) FROM pack_souscriptions ps2 JOIN packs p ON p.code_pack = ps2.pack_code WHERE ps2.souscription_code = s.code_souscription) as montant_total_cautisation,
                   (SELECT COALESCE(SUM(cc.montant_cautisation_client), 0) FROM cautisation_clients cc WHERE cc.souscription_code = s.code_souscription AND cc.statut_cautisation_client = 'valide') as montant_total_paye,
                   (SELECT COALESCE(SUM(cc.nombre_jour), 0) FROM cautisation_clients cc WHERE cc.souscription_code = s.code_souscription AND cc.statut_cautisation_client = 'valide') as nombre_jours_payes
            FROM souscriptions s
            LEFT JOIN clients c ON c.code_client = s.client_code
            LEFT JOIN sessions sess ON sess.code_session = s.session_code
            WHERE s.code_souscription = ?
            LIMIT 1
        ";
        
        $stmt = $con->prepare($sql);
        $stmt->execute([$codeSouscription]);
        $souscription = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$souscription) {
            return null;
        }

        // Calculer les montants et jours restants
        $prixCotisationJournaliere = (float)($souscription['montant_total_cautisation'] ?? 0);
        $joursTotal = (int)($souscription['nombre_jour_session'] ?? 0);
        $montantTotal = $prixCotisationJournaliere * $joursTotal;
        $montantPaye = (float)($souscription['montant_total_paye'] ?? 0);
        $montantRestant = max(0, $montantTotal - $montantPaye);
        
        $joursPayes = (int)($souscription['nombre_jours_payes'] ?? 0);
        $joursRestants = max(0, $joursTotal - $joursPayes);

        $souscription['montant_total_a_payer'] = $montantTotal;
        $souscription['montant_total_paye'] = $montantPaye;
        $souscription['montant_restant_a_payer'] = $montantRestant;
        $souscription['nombre_jours_total'] = $joursTotal;
        $souscription['nombre_jours_payes'] = $joursPayes;
        $souscription['nombre_jours_restant'] = $joursRestants;
        $souscription['prix_cotisation_pack'] = $prixCotisationJournaliere;

        return $souscription;
    }

    /**
     * Récupère le montant total à payer (cotisation par jour × jours de session)
     */
    private function getTotalPackAmount(string $codeSouscription): float
    {
        $con = $this->model->getCon();
        $sql = "
            SELECT COALESCE(SUM(p.prix_cotisation_pack), 0) as daily_cotisation,
                   COALESCE(sess.nombre_jour_session, 0) as nombre_jours
            FROM souscriptions s
            LEFT JOIN pack_souscriptions ps ON ps.souscription_code = s.code_souscription
            LEFT JOIN packs p ON p.code_pack = ps.pack_code
            LEFT JOIN sessions sess ON sess.code_session = s.session_code
            WHERE s.code_souscription = ?
            GROUP BY s.id_souscription
        ";
        $stmt = $con->prepare($sql);
        $stmt->execute([$codeSouscription]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $dailyCotisation = (float)($result['daily_cotisation'] ?? 0);
        $nombreJours = (int)($result['nombre_jours'] ?? 0);
        return $dailyCotisation * $nombreJours;
    }

    /**
     * Récupère la caisse ouverte pour la zone et l'établissement
     */
    private function getOpenCaisse(string $zoneCode, string $etabCode): ?array
    {
        $con = $this->model->getCon();
        $sql = "
            SELECT * FROM caisses
            WHERE zone_code = ? AND etablissement_code = ? 
            AND statut_caisse = 'ouverte'
            ORDER BY date_ouverture DESC
            LIMIT 1
        ";
        $stmt = $con->prepare($sql);
        $stmt->execute([$zoneCode, $etabCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

