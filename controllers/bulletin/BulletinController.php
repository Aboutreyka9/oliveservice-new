<?php

class BulletinController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelBulletin();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/bulletin/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $sql = "SELECT i.*, 
                       CONCAT(e.nom_etudiant, ' ', e.prenom_etudiant) AS etudiant_nom,
                       e.matricule_etudiant,
                       cl.libelle_classe AS classe_nom,
                       a.libelle_annee AS annee_nom,
                       (SELECT COUNT(*) FROM notes n WHERE n.inscription_code = i.code_inscription AND n.statut_note = 'actif') AS nb_notes
                FROM inscriptions i
                LEFT JOIN etudiants e ON e.code_etudiant = i.etudiant_code
                LEFT JOIN classes cl ON cl.code_classe = i.classe_code
                LEFT JOIN annees a ON a.code_annee = i.annee_code
                ORDER BY i.id_inscription DESC";
        $items = $this->model->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_inscription'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            
            // Récupérer l'inscription avec les détails de l'étudiant, de la classe et de l'année
            $stmt = $this->model->getCon()->prepare("
                SELECT i.*, 
                       e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.sexe_etudiant,
                       e.date_naissance_etudiant, e.lieu_naissance_etudiant, e.telephone_etudiant,
                       e.email_etudiant, e.photo_etudiant, e.lieu_residence_etudiant,
                       cl.libelle_classe, cl.code_classe,
                       a.libelle_annee, a.code_annee
                FROM inscriptions i
                LEFT JOIN etudiants e ON e.code_etudiant = i.etudiant_code
                LEFT JOIN classes cl ON cl.code_classe = i.classe_code
                LEFT JOIN annees a ON a.code_annee = i.annee_code
                WHERE i.id_inscription = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) {
                $this->renderNotFound("Le bulletin demandé est introuvable.");
                return;
            }

            // Récupérer la liste des semestres
            $semestres = $this->model->getCon()->query("
                SELECT * FROM semestres WHERE statut_semestre = 'actif' ORDER BY id_semestre ASC
            ")->fetchAll(PDO::FETCH_ASSOC);

            // Semestre sélectionné (par paramètre GET ou le premier avec des notes, ou le premier disponible)
            $selectedSemestreCode = $_GET['semestre'] ?? '';
            if (empty($selectedSemestreCode)) {
                // Trouver le premier semestre ayant des notes pour cette inscription
                $stmtCheck = $this->model->getCon()->prepare("
                    SELECT semestre_code FROM notes 
                    WHERE inscription_code = ? AND statut_note = 'actif' 
                    LIMIT 1
                ");
                $stmtCheck->execute([$item['code_inscription']]);
                $foundSem = $stmtCheck->fetchColumn();
                $selectedSemestreCode = $foundSem ?: ($semestres[0]['code_semestre'] ?? '');
            }

            // Récupérer les notes pour le semestre sélectionné avec le coefficient par classe
            $sqlNotes = "
                SELECT n.*, 
                       m.libelle_matiere, 
                       COALESCE(em.coefficient, 1.00) AS coefficient, 
                       m.code_matiere
                FROM notes n
                LEFT JOIN matieres m ON m.code_matiere = n.matiere_code
                LEFT JOIN enseignant_matiere em ON (em.matiere_code = n.matiere_code AND em.classe_code = ?)
                WHERE n.inscription_code = ? AND n.semestre_code = ? AND n.statut_note = 'actif'
                ORDER BY m.libelle_matiere ASC, n.type_evaluation_code ASC
            ";
            $stmtNotes = $this->model->getCon()->prepare($sqlNotes);
            $stmtNotes->execute([$item['classe_code'], $item['code_inscription'], $selectedSemestreCode]);
            $rawNotes = $stmtNotes->fetchAll(PDO::FETCH_ASSOC);

            // Structurer les notes par matière
            $matieresNotes = [];
            foreach ($rawNotes as $n) {
                $matCode = $n['matiere_code'] ?: 'MAT_INCONNUE';
                if (!isset($matieresNotes[$matCode])) {
                    $matieresNotes[$matCode] = [
                        'libelle' => $n['libelle_matiere'] ?: 'Matière sans nom',
                        'coefficient' => (float)($n['coefficient'] ?? 1),
                        'evaluations' => [],
                        'notes' => []
                    ];
                }
                $matieresNotes[$matCode]['evaluations'][] = [
                    'type' => $n['type_evaluation_code'],
                    'note' => (float)$n['valeur_note'],
                    'observations' => $n['observations'] ?? ''
                ];
                $matieresNotes[$matCode]['notes'][] = (float)$n['valeur_note'];
            }

            // Calculer la moyenne par matière, les points pondérés et les totaux
            $totalPoints = 0;
            $totalCoefficients = 0;
            foreach ($matieresNotes as $k => &$m) {
                $nb = count($m['notes']);
                $moyenneMatiere = $nb > 0 ? (array_sum($m['notes']) / $nb) : 0;
                $m['moyenne'] = round($moyenneMatiere, 2);
                $m['points'] = round($moyenneMatiere * $m['coefficient'], 2);

                // Appréciation automatique par matière
                if ($m['moyenne'] >= 16) $m['appreciation'] = 'Très Bien';
                elseif ($m['moyenne'] >= 14) $m['appreciation'] = 'Bien';
                elseif ($m['moyenne'] >= 12) $m['appreciation'] = 'Assez Bien';
                elseif ($m['moyenne'] >= 10) $m['appreciation'] = 'Passable';
                elseif ($m['moyenne'] >= 8)  $m['appreciation'] = 'Insuffisant';
                else $m['appreciation'] = 'Très Insuffisant';

                $totalPoints += $m['points'];
                $totalCoefficients += $m['coefficient'];
            }
            unset($m);

            // Moyenne générale
            $moyenneGenerale = $totalCoefficients > 0 ? round($totalPoints / $totalCoefficients, 2) : 0;

            // Mention générale
            $mention = 'Non évalué';
            $decision = 'En attente';
            if ($totalCoefficients > 0) {
                if ($moyenneGenerale >= 16) {
                    $mention = 'Très Bien';
                    $decision = 'Félicitations du Conseil de Faculté / Jury';
                } elseif ($moyenneGenerale >= 14) {
                    $mention = 'Bien';
                    $decision = 'Tableau d\'Honneur';
                } elseif ($moyenneGenerale >= 12) {
                    $mention = 'Assez Bien';
                    $decision = 'Encouragements';
                } elseif ($moyenneGenerale >= 10) {
                    $mention = 'Passable';
                    $decision = 'Admis(e) au semestre';
                } else {
                    $mention = 'Ajourné(e)';
                    $decision = 'Non validé / Session de rattrapage';
                }
            }

            // Calcul du rang et des statistiques de la classe pour ce semestre
            $rang = 1;
            $totalElevesClasse = 1;
            $moyenneMinClasse = $moyenneGenerale;
            $moyenneMaxClasse = $moyenneGenerale;
            $moyenneMoyClasse = $moyenneGenerale;

            if (!empty($item['classe_code']) && !empty($selectedSemestreCode)) {
                $sqlClassmates = "
                    SELECT i.code_inscription,
                           n.valeur_note,
                           COALESCE(em.coefficient, 1.00) AS coefficient
                    FROM inscriptions i
                    INNER JOIN notes n ON n.inscription_code = i.code_inscription
                    INNER JOIN matieres m ON m.code_matiere = n.matiere_code
                    LEFT JOIN enseignant_matiere em ON (em.matiere_code = n.matiere_code AND em.classe_code = i.classe_code)
                    WHERE i.classe_code = ? AND n.semestre_code = ? AND n.statut_note = 'actif'
                ";
                $stmtClass = $this->model->getCon()->prepare($sqlClassmates);
                $stmtClass->execute([$item['classe_code'], $selectedSemestreCode]);
                $classNotes = $stmtClass->fetchAll(PDO::FETCH_ASSOC);

                // Regrouper par inscription
                $etudiantsMoyennes = [];
                foreach ($classNotes as $cn) {
                    $insc = $cn['code_inscription'];
                    if (!isset($etudiantsMoyennes[$insc])) {
                        $etudiantsMoyennes[$insc] = ['total_pts' => 0, 'total_coef' => 0];
                    }
                    $etudiantsMoyennes[$insc]['total_pts'] += ($cn['valeur_note'] * $cn['coefficient']);
                    $etudiantsMoyennes[$insc]['total_coef'] += $cn['coefficient'];
                }

                if (!empty($etudiantsMoyennes)) {
                    $classeMoyennesArray = [];
                    foreach ($etudiantsMoyennes as $inscCode => $stats) {
                        if ($stats['total_coef'] > 0) {
                            $classeMoyennesArray[$inscCode] = round($stats['total_pts'] / $stats['total_coef'], 2);
                        }
                    }

                    if (!empty($classeMoyennesArray)) {
                        arsort($classeMoyennesArray);
                        $totalElevesClasse = count($classeMoyennesArray);
                        $moyenneMaxClasse = max($classeMoyennesArray);
                        $moyenneMinClasse = min($classeMoyennesArray);
                        $moyenneMoyClasse = round(array_sum($classeMoyennesArray) / $totalElevesClasse, 2);

                        $pos = 1;
                        foreach ($classeMoyennesArray as $inscCode => $moy) {
                            if ($inscCode === $item['code_inscription']) {
                                $rang = $pos;
                                break;
                            }
                            $pos++;
                        }
                    }
                }
            }

            $encryptedId = $this->validator->crypter($id);

            $this->loadView('../views/bulletin/details.php', [
                'item' => $item,
                'encryptedId' => $encryptedId,
                'semestres' => $semestres,
                'selectedSemestreCode' => $selectedSemestreCode,
                'matieresNotes' => $matieresNotes,
                'totalPoints' => $totalPoints,
                'totalCoefficients' => $totalCoefficients,
                'moyenneGenerale' => $moyenneGenerale,
                'mention' => $mention,
                'decision' => $decision,
                'rang' => $rang,
                'totalElevesClasse' => $totalElevesClasse,
                'moyenneMinClasse' => $moyenneMinClasse,
                'moyenneMaxClasse' => $moyenneMaxClasse,
                'moyenneMoyClasse' => $moyenneMoyClasse
            ]);
        } catch (Exception $e) {
            error_log("BulletinController::details error: " . $e->getMessage());
            $this->renderNotFound("Le bulletin demandé est introuvable.");
            return;
        }
    }

    public function formulaire()
    {
        $this->requireAuth();
        header('Location: ' . RACINE . 'bulletin/list');
        exit();
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->details($details);
    }
}
