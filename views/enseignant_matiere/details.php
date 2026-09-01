<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$nbNotes = $nbNotes ?? 0;
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Fiche Affectation de Cours</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Détails du binôme enseignant-matière et classe bénéficiaire</p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>enseignant_matiere/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux affectations
          </a>
          <a href="<?= RACINE ?>enseignant_matiere/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier l'affectation
          </a>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : DÉTAILS DE L'AFFECTATION -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="link" style="width: 18px; height: 18px;"></i> Spécifications Pédagogiques & Attributions
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Matière Enseignée</span>
            <div style="font-size: 18px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($item['libelle_matiere'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Code : <code><?= htmlspecialchars($item['matiere_code'] ?? '-') ?></code></div>
          </div>

          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Enseignant Titulaire</span>
            <div style="font-size: 17px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= htmlspecialchars(($item['nom_prof'] ?? '') . ' ' . ($item['prenom_prof'] ?? '')) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;"><?= htmlspecialchars($item['grade_enseignant'] ?? 'Professeur') ?></div>
          </div>

          <div style="background: #FAF5FF; border: 1px solid #E9D5FF; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #7E22CE; text-transform: uppercase;">Classe & Promotion</span>
            <div style="font-size: 18px; font-weight: 800; color: #7E22CE; margin-top: 4px;"><?= htmlspecialchars($item['libelle_classe'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;"><?= htmlspecialchars(($item['libelle_filiere'] ?? '-') . ' / ' . ($item['libelle_niveau'] ?? '-')) ?></div>
          </div>

          <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Coefficient & Volume</span>
            <div style="font-size: 24px; font-weight: 800; color: #15803D; margin-top: 4px;">Coef. <?= htmlspecialchars($item['coefficient'] ?? ($item['coefficient_enseignant_matiere'] ?? '1.0')) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;"><?= (int)($item['volume_horaire'] ?? 0) ?> heures de cours prévues</div>
          </div>
        </div>

        <div style="display: flex; gap: 20px; flex-wrap: wrap; padding-top: 14px; border-top: 1px solid #F1F5F9; font-size: 13px; margin-top: 14px;">
          <div><strong style="color: #64748B;">Évaluations / Notes Saisies :</strong> <span style="font-weight: 700; color: #0F172A;"><?= $nbNotes ?> notes enregistrées</span></div>
          <div><strong style="color: #64748B;">Statut :</strong> 
            <?php if (($item['statut_enseignant_matiere'] ?? '') === 'actif'): ?>
              <span class="badge" style="background:#DCFCE7; color:#15803D; padding:3px 10px; border-radius:10px; font-weight:700;">Actif</span>
            <?php else: ?>
              <span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:3px 10px; border-radius:10px; font-weight:700;">Inactif</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
