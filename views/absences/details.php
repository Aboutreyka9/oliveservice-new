<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$isJustifie = (($item['justifie'] ?? '') == '1' || strtolower($item['justifie'] ?? '') === 'oui' || ($item['statut_absence'] ?? '') === 'justifie');
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Fiche Absence / Retard</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Étudiant : <strong><?= htmlspecialchars(($item['nom_etudiant'] ?? '') . ' ' . ($item['prenom_etudiant'] ?? '')) ?></strong> &bull; Classe : <strong><?= htmlspecialchars($item['libelle_classe'] ?? '-') ?></strong></p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>absence/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux absences
          </a>
          <a href="<?= RACINE ?>absence/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier la fiche
          </a>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : DÉTAILS DE L'ABSENCE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="user-x" style="width: 18px; height: 18px;"></i> Constat d'Absence ou Retard
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Étudiant</span>
            <div style="font-size: 17px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars(($item['nom_etudiant'] ?? '') . ' ' . ($item['prenom_etudiant'] ?? '')) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Matricule : <code><?= htmlspecialchars($item['matricule_etudiant'] ?? '-') ?></code></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Classe & Matière</span>
            <div style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= htmlspecialchars($item['libelle_classe'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Cours : <?= htmlspecialchars($item['libelle_matiere'] ?? 'Non spécifiée') ?></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Date & Heures</span>
            <div style="font-size: 16px; font-weight: 800; color: #0F172A; margin-top: 4px;">
              <?= !empty($item['date_absence']) ? date('d/m/Y', strtotime($item['date_absence'])) : '-' ?>
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Volume : <?= (int)($item['heures_absence'] ?? 2) ?> heure(s)</div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Justification</span>
            <div style="margin-top: 4px;">
              <?php if ($isJustifie): ?>
                <span class="badge" style="background:#DCFCE7; color:#15803D; padding:5px 12px; border-radius:10px; font-weight:700; font-size:12px;">Absence Justifiée</span>
              <?php else: ?>
                <span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:5px 12px; border-radius:10px; font-weight:700; font-size:12px;">Injustifiée</span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div style="padding-top: 18px; border-top: 1px solid #F1F5F9; margin-top: 18px;">
          <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block; margin-bottom: 4px;">Motif / Observation :</span>
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #334155; line-height: 1.5;">
            <?= htmlspecialchars($item['motif_absence'] ?? ($item['motif'] ?? 'Aucun motif renseigné.')) ?>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
