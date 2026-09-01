<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$stats = isset($stats) ? $stats : [];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Fiche Semestre : <?= htmlspecialchars($item['libelle_semestre'] ?? 'Semestre') ?></h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Année académique : <strong><?= htmlspecialchars($item['libelle_annee'] ?? 'En cours') ?></strong></p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>semestre/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux semestres
          </a>
          <a href="<?= RACINE ?>semestre/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier le semestre
          </a>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : DÉTAILS DU SEMESTRE & STATISTIQUES ÉVALUATIONS -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="clock" style="width: 18px; height: 18px;"></i> Période Semestrielle & Suivi Pédagogique
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Semestre</span>
            <div style="font-size: 20px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($item['libelle_semestre'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Code : <code><?= htmlspecialchars($item['code_semestre'] ?? '-') ?></code></div>
          </div>

          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Année Académique</span>
            <div style="font-size: 18px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= htmlspecialchars($item['libelle_annee'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Session d'enseignement</div>
          </div>

          <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Notes Enregistrées</span>
            <div style="font-size: 24px; font-weight: 800; color: #15803D; margin-top: 4px;"><?= (int)($stats['total_notes'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Évaluations saisies</div>
          </div>

          <div style="background: #FAF5FF; border: 1px solid #E9D5FF; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #7E22CE; text-transform: uppercase;">Classes Évaluées</span>
            <div style="font-size: 24px; font-weight: 800; color: #7E22CE; margin-top: 4px;"><?= (int)($stats['total_classes_evaluees'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Groupes avec délibérations</div>
          </div>
        </div>

        <div style="display: flex; gap: 20px; flex-wrap: wrap; padding-top: 14px; border-top: 1px solid #F1F5F9; font-size: 13px; margin-top: 14px;">
          <div><strong style="color: #64748B;">Date de Début :</strong> <span style="font-weight: 700; color: #0F172A;"><?= !empty($item['date_debut_semestre']) ? date('d/m/Y', strtotime($item['date_debut_semestre'])) : 'Non définie' ?></span></div>
          <div><strong style="color: #64748B;">Date de Fin :</strong> <span style="font-weight: 700; color: #0F172A;"><?= !empty($item['date_fin_semestre']) ? date('d/m/Y', strtotime($item['date_fin_semestre'])) : 'Non définie' ?></span></div>
          <div><strong style="color: #64748B;">Statut :</strong> 
            <?php if (($item['statut_semestre'] ?? '') === 'actif'): ?>
              <span class="badge" style="background:#DCFCE7; color:#15803D; padding:3px 10px; border-radius:10px; font-weight:700;">Actif / Ouvert</span>
            <?php else: ?>
              <span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:3px 10px; border-radius:10px; font-weight:700;">Clôturé</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
