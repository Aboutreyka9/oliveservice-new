<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$etudiants = isset($etudiants) ? $etudiants : [];
$matieres = isset($matieres) ? $matieres : [];
$capacite = (int)($item['capacite_max_classe'] ?? 40);
$nbInscrits = count($etudiants);
$tauxRemplissage = ($capacite > 0) ? min(100, round(($nbInscrits / $capacite) * 100)) : 0;
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <!-- En-tête de page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Classe : <?= htmlspecialchars($item['libelle_classe'] ?? 'Classe') ?></h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Filière : <strong><?= htmlspecialchars($item['libelle_filiere'] ?? '-') ?></strong> &bull; Niveau : <strong><?= htmlspecialchars($item['libelle_niveau'] ?? '-') ?></strong> &bull; Année : <strong><?= htmlspecialchars($item['libelle_annee'] ?? 'En cours') ?></strong></p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>classe/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux classes
          </a>
          <a href="<?= RACINE ?>classe/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier la classe
          </a>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : SYNTHÈSE DE LA CLASSE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="users" style="width: 18px; height: 18px;"></i> Effectifs & Indicateurs Pédagogiques
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Capacité Maximale</span>
            <div style="font-size: 24px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= $capacite ?> places</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Plafond d'accueil en salle</div>
          </div>

          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Étudiants Inscrits</span>
            <div style="font-size: 24px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= $nbInscrits ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Inscriptions actives</div>
          </div>

          <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Taux de Remplissage</span>
            <div style="font-size: 24px; font-weight: 800; color: #15803D; margin-top: 4px;"><?= $tauxRemplissage ?>%</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;"><?= ($capacite - $nbInscrits) ?> place(s) disponible(s)</div>
          </div>

          <div style="background: #FAF5FF; border: 1px solid #E9D5FF; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #7E22CE; text-transform: uppercase;">Matières Programmées</span>
            <div style="font-size: 24px; font-weight: 800; color: #7E22CE; margin-top: 4px;"><?= count($matieres) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Cours affectés</div>
          </div>
        </div>
      </div>

      <!-- CARD 2 (COL-12) : LISTE DES ÉTUDIANTS DE LA CLASSE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #EFF6FF;">
          <div>
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="user-check" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Registre des Étudiants Inscrits (<?= $nbInscrits ?>)
            </h3>
          </div>
          <a href="<?= RACINE ?>inscription/formulaire" class="btn btn-sm btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 6px; font-size: 12px;">
            + Inscrire un étudiant
          </a>
        </div>

        <?php if (empty($etudiants)): ?>
          <p style="color: #94A3B8; text-align: center; padding: 30px 0; font-style: italic;">Aucun étudiant n'est encore inscrit dans cette classe.</p>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 12px;">
                  <th style="padding: 10px;">Matricule</th>
                  <th style="padding: 10px;">Nom & Prénoms</th>
                  <th style="padding: 10px; text-align: center;">Sexe</th>
                  <th style="padding: 10px;">Contact Téléphone</th>
                  <th style="padding: 10px;">Email</th>
                  <th style="padding: 10px; text-align: right;">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($etudiants as $e): ?>
                  <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 10px; font-family: monospace; font-weight: 700; color: #1E3A5F;">
                      <?= htmlspecialchars($e['matricule_etudiant'] ?? $e['code_etudiant']) ?>
                    </td>
                    <td style="padding: 10px; font-weight: 700; color: #0F172A;">
                      <a href="<?= RACINE ?>etudiant/details/<?= $this->validator->crypter($e['id_etudiant']) ?>" style="color: #1E3A5F; text-decoration: underline;">
                        <?= htmlspecialchars($e['nom_etudiant'] . ' ' . $e['prenom_etudiant']) ?>
                      </a>
                    </td>
                    <td style="padding: 10px; text-align: center; color: #64748B;"><?= htmlspecialchars($e['sexe_etudiant'] ?? '-') ?></td>
                    <td style="padding: 10px; color: #334155;"><?= htmlspecialchars($e['telephone_etudiant'] ?? '-') ?></td>
                    <td style="padding: 10px; color: #334155;"><?= htmlspecialchars($e['email_etudiant'] ?? '-') ?></td>
                    <td style="padding: 10px; text-align: right;">
                      <a href="<?= RACINE ?>etudiant/details/<?= $this->validator->crypter($e['id_etudiant']) ?>" class="btn btn-sm btn-info" style="font-weight: 600; border-radius: 6px; font-size: 12px;">
                        Fiche élève
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- CARD 3 (COL-12) : MATIÈRES ET ENSEIGNANTS AFFECTÉS -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #EFF6FF;">
          <div>
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="book-open" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Programme Pédagogique & Enseignants Assignés (<?= count($matieres) ?>)
            </h3>
          </div>
          <a href="<?= RACINE ?>enseignant_matiere/formulaire" class="btn btn-sm btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 6px; font-size: 12px;">
            + Affecter un cours
          </a>
        </div>

        <?php if (empty($matieres)): ?>
          <p style="color: #94A3B8; text-align: center; padding: 30px 0; font-style: italic;">Aucune matière n'a encore été assignée à cette classe.</p>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 12px;">
                  <th style="padding: 10px;">Matière</th>
                  <th style="padding: 10px;">Enseignant Titulaire</th>
                  <th style="padding: 10px; text-align: center;">Coefficient</th>
                  <th style="padding: 10px; text-align: center;">Volume Horaire</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($matieres as $m): ?>
                  <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 10px; font-weight: 700; color: #0F172A;">
                      <?= htmlspecialchars($m['libelle_matiere'] ?? 'Matière') ?>
                    </td>
                    <td style="padding: 10px; color: #1E3A5F; font-weight: 600;">
                      <?= htmlspecialchars(($m['nom_prof'] ?? '') . ' ' . ($m['prenom_prof'] ?? '')) ?>
                      <?php if (!empty($m['grade_enseignant'])): ?>
                        <span style="font-size: 11px; color: #64748B; font-weight: normal;">(<?= htmlspecialchars($m['grade_enseignant']) ?>)</span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 10px; text-align: center; font-weight: 800; color: #15803D;">
                      <?= htmlspecialchars($m['coefficient_enseignant_matiere'] ?? '1') ?>
                    </td>
                    <td style="padding: 10px; text-align: center; color: #64748B;">
                      <?= (int)($m['volume_horaire'] ?? 0) ?> h
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
