<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Fiche Créneau Horaire / Séance de Cours</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Détails de la séance, salle, matière et enseignant assigné</p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>emploi/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour au planning
          </a>
          <a href="<?= RACINE ?>emploi/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier ce créneau
          </a>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : DÉTAILS DU CRÉNEAU -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="clock" style="width: 18px; height: 18px;"></i> Horaires & Affectation de Séance
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          
          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Jour & Créneau</span>
            <div style="font-size: 18px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= htmlspecialchars(ucfirst($item['jour'] ?? '-')) ?></div>
            <div style="font-size: 14px; font-weight: 700; color: #0F172A; font-family: monospace; margin-top: 2px;">
              <?= htmlspecialchars($item['heure_debut'] ?? '') ?> - <?= htmlspecialchars($item['heure_fin'] ?? '') ?>
            </div>
          </div>

          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Matière Enseignée</span>
            <div style="font-size: 17px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($item['libelle_matiere'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Code : <code><?= htmlspecialchars($item['matiere_code'] ?? '-') ?></code></div>
          </div>

          <div style="background: #FAF5FF; border: 1px solid #E9D5FF; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #7E22CE; text-transform: uppercase;">Classe Bénéficiaire</span>
            <div style="font-size: 17px; font-weight: 800; color: #7E22CE; margin-top: 4px;"><?= htmlspecialchars($item['libelle_classe'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;"><?= htmlspecialchars(($item['libelle_filiere'] ?? '-') . ' / ' . ($item['libelle_niveau'] ?? '-')) ?></div>
          </div>

          <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Salle de Cours</span>
            <div style="font-size: 17px; font-weight: 800; color: #15803D; margin-top: 4px;"><?= htmlspecialchars($item['libelle_salle'] ?? 'Non attribuée') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Capacité : <?= (int)($item['capacite_salle'] ?? 0) ?> places</div>
          </div>

        </div>

        <div style="display: flex; gap: 20px; flex-wrap: wrap; padding-top: 16px; border-top: 1px solid #F1F5F9; font-size: 13px; margin-top: 14px;">
          <div>
            <strong style="color: #64748B;">Enseignant Titulaire :</strong> 
            <span style="font-weight: 700; color: #0F172A;"><?= htmlspecialchars(($item['nom_prof'] ?? '') . ' ' . ($item['prenom_prof'] ?? '')) ?></span>
            <?php if (!empty($item['grade_enseignant'])): ?>
              <span style="font-size: 11px; color: #64748B;">(<?= htmlspecialchars($item['grade_enseignant']) ?>)</span>
            <?php endif; ?>
          </div>
          <div><strong style="color: #64748B;">Statut du créneau :</strong> 
            <?php if (($item['statut_emploi'] ?? '') === 'actif'): ?>
              <span class="badge" style="background:#DCFCE7; color:#15803D; padding:3px 10px; border-radius:10px; font-weight:700;">Actif</span>
            <?php else: ?>
              <span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:3px 10px; border-radius:10px; font-weight:700;">Suspendu</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
