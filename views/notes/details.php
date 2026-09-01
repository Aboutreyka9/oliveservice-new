<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$noteVal = (float)($item['note'] ?? ($item['valeur_note'] ?? 0));
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Fiche Note / Évaluation</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Étudiant : <strong><?= htmlspecialchars(($item['nom_etudiant'] ?? '') . ' ' . ($item['prenom_etudiant'] ?? '')) ?></strong> &bull; Matière : <strong><?= htmlspecialchars($item['libelle_matiere'] ?? '-') ?></strong></p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>note/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux notes
          </a>
          <a href="<?= RACINE ?>note/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier la note
          </a>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : SYNTHÈSE DE LA NOTE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="award" style="width: 18px; height: 18px;"></i> Résultat de l'Évaluation
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          
          <div style="background: <?= $noteVal >= 10 ? '#F0FDF4' : '#FEF2F2' ?>; border: 1px solid <?= $noteVal >= 10 ? '#BBF7D0' : '#FECACA' ?>; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: <?= $noteVal >= 10 ? '#15803D' : '#991B1B' ?>; text-transform: uppercase;">Note Obtenue</span>
            <div style="font-size: 28px; font-weight: 800; color: <?= $noteVal >= 10 ? '#15803D' : '#DC2626' ?>; margin-top: 4px;">
              <?= number_format($noteVal, 2, ',', ' ') ?> / 20
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">
              <?= $noteVal >= 10 ? 'Validation / Admis' : 'Non validé / Ajourné' ?>
            </div>
          </div>

          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Élève / Candidat</span>
            <div style="font-size: 16px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars(($item['nom_etudiant'] ?? '') . ' ' . ($item['prenom_etudiant'] ?? '')) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Matricule : <code><?= htmlspecialchars($item['matricule_etudiant'] ?? '-') ?></code></div>
          </div>

          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Matière & Coefficient</span>
            <div style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= htmlspecialchars($item['libelle_matiere'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Coefficient : <strong><?= htmlspecialchars($item['coef_cours'] ?? '1.0') ?></strong></div>
          </div>

          <div style="background: #FAF5FF; border: 1px solid #E9D5FF; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #7E22CE; text-transform: uppercase;">Classe & Semestre</span>
            <div style="font-size: 16px; font-weight: 800; color: #7E22CE; margin-top: 4px;"><?= htmlspecialchars($item['libelle_classe'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;"><?= htmlspecialchars($item['libelle_semestre'] ?? 'Semestre 1') ?></div>
          </div>

        </div>

        <div style="display: flex; gap: 20px; flex-wrap: wrap; padding-top: 16px; border-top: 1px solid #F1F5F9; font-size: 13px; margin-top: 14px;">
          <div><strong style="color: #64748B;">Date d'enregistrement :</strong> <span style="font-weight: 700; color: #0F172A;"><?= !empty($item['created_at_note']) ? date('d/m/Y H:i', strtotime($item['created_at_note'])) : '-' ?></span></div>
          <div><strong style="color: #64748B;">Statut :</strong> 
            <?php if (($item['statut_note'] ?? '') === 'actif'): ?>
              <span class="badge" style="background:#DCFCE7; color:#15803D; padding:3px 10px; border-radius:10px; font-weight:700;">Validée</span>
            <?php else: ?>
              <span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:3px 10px; border-radius:10px; font-weight:700;">Annulée</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
