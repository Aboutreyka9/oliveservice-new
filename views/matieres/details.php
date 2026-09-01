<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$affectations = isset($affectations) ? $affectations : [];
$stats = isset($stats) ? $stats : [];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Fiche Matière : <?= htmlspecialchars($item['libelle_matiere'] ?? 'Matière') ?></h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Unités d'enseignement, professeurs assignés et classes concernées</p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>matiere/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux matières
          </a>
          <a href="<?= RACINE ?>matiere/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier la matière
          </a>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : CARACTÉRISTIQUES DE LA MATIÈRE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="book-open" style="width: 18px; height: 18px;"></i> Caractéristiques & Chiffres Clés
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Intitulé du Cours</span>
            <div style="font-size: 18px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($item['libelle_matiere'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Code : <code><?= htmlspecialchars($item['code_matiere'] ?? '-') ?></code></div>
          </div>

          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Classes Rattachées</span>
            <div style="font-size: 24px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= (int)($stats['total_classes'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Classes où le cours est dispensé</div>
          </div>

          <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Enseignants Mobilisés</span>
            <div style="font-size: 24px; font-weight: 800; color: #15803D; margin-top: 4px;"><?= (int)($stats['total_profs'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Professeurs assignés</div>
          </div>

          <div style="background: #FAF5FF; border: 1px solid #E9D5FF; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #7E22CE; text-transform: uppercase;">Évaluations / Notes</span>
            <div style="font-size: 24px; font-weight: 800; color: #7E22CE; margin-top: 4px;"><?= (int)($stats['total_notes'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Notes saisies pour cette matière</div>
          </div>
        </div>
      </div>

      <!-- CARD 2 (COL-12) : AFFECTATIONS PAR CLASSE ET ENSEIGNANT -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #EFF6FF;">
          <div>
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="layers" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Répartition Pédagogique par Classe & Enseignant (<?= count($affectations) ?>)
            </h3>
          </div>
          <a href="<?= RACINE ?>enseignant_matiere/formulaire" class="btn btn-sm btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 6px; font-size: 12px;">
            + Affecter à une classe
          </a>
        </div>

        <?php if (empty($affectations)): ?>
          <p style="color: #94A3B8; text-align: center; padding: 30px 0; font-style: italic;">Cette matière n'a pas encore été affectée à une classe ou un enseignant.</p>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 12px;">
                  <th style="padding: 10px;">Classe</th>
                  <th style="padding: 10px;">Filière & Niveau</th>
                  <th style="padding: 10px;">Enseignant Titulaire</th>
                  <th style="padding: 10px; text-align: center;">Coefficient</th>
                  <th style="padding: 10px; text-align: center;">Volume Horaire</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($affectations as $a): ?>
                  <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 10px; font-weight: 700; color: #0F172A;">
                      <a href="<?= RACINE ?>classe/details/<?= $this->validator->crypter($a['id_classe'] ?? 0) ?>" style="color: #1E3A5F; text-decoration: underline;">
                        <?= htmlspecialchars($a['libelle_classe'] ?? 'Classe') ?>
                      </a>
                    </td>
                    <td style="padding: 10px; color: #334155;">
                      <?= htmlspecialchars(($a['libelle_filiere'] ?? '-') . ' / ' . ($a['libelle_niveau'] ?? '-')) ?>
                    </td>
                    <td style="padding: 10px; color: #1E3A5F; font-weight: 600;">
                      <?= htmlspecialchars(($a['nom_prof'] ?? '') . ' ' . ($a['prenom_prof'] ?? '')) ?>
                      <?php if (!empty($a['grade_enseignant'])): ?>
                        <span style="font-size: 11px; color: #64748B; font-weight: normal;">(<?= htmlspecialchars($a['grade_enseignant']) ?>)</span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 10px; text-align: center; font-weight: 800; color: #15803D;">
                      <?= htmlspecialchars($a['coefficient_enseignant_matiere'] ?? '1') ?>
                    </td>
                    <td style="padding: 10px; text-align: center; color: #64748B;">
                      <?= (int)($a['volume_horaire'] ?? 0) ?> h
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
