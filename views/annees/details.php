<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$stats = isset($stats) ? $stats : [];
$classes = isset($classes) ? $classes : [];
$semestres = isset($semestres) ? $semestres : [];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="calendar" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Année Académique : <?= htmlspecialchars($item['libelle_annee'] ?? 'Année') ?>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Code Année : <code style="font-weight: 700; color: #1E3A5F; background: #F1F5F9; padding: 2px 8px; border-radius: 6px; font-size: 12px; border: 1px solid #CBD5E1;"><?= htmlspecialchars($item['code_annee'] ?? '') ?></code>
            </p>
          </div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>annee/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour à la liste
          </a>
          <a href="<?= RACINE ?>annee/edition/<?= $encryptedId ?>" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 10px 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
            <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier l'année
          </a>
        </div>
      </div>

      <!-- CARTE SYNTHÈSE & STATISTIQUES -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
          <i data-lucide="activity" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Fiche Synthétique & Indicateurs Clés
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 20px;">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Libellé Année</span>
            <div style="font-size: 18px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($item['libelle_annee'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Code : <code><?= htmlspecialchars($item['code_annee'] ?? '-') ?></code></div>
          </div>

          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 12px; padding: 18px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px;">Étudiants Inscrits</span>
            <div style="font-size: 24px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= number_format((int)($stats['total_etudiants'] ?? 0), 0, ',', ' ') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px; font-weight: 500;">Inscriptions actives</div>
          </div>

          <div style="background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 12px; padding: 18px;">
            <span style="font-size: 11px; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 0.5px;">Classes Ouvertes</span>
            <div style="font-size: 24px; font-weight: 800; color: #059669; margin-top: 4px;"><?= (int)($stats['total_classes'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px; font-weight: 500;">Groupes pédagogiques</div>
          </div>

          <div style="background: #FAF5FF; border: 1px solid #E9D5FF; border-radius: 12px; padding: 18px;">
            <span style="font-size: 11px; font-weight: 700; color: #7E22CE; text-transform: uppercase; letter-spacing: 0.5px;">Recouvrement Réalisé</span>
            <div style="font-size: 20px; font-weight: 800; color: #7E22CE; margin-top: 4px;"><?= number_format((float)($stats['total_recouvrement'] ?? 0), 0, ',', ' ') ?> F</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px; font-weight: 500;">Total paiements validés</div>
          </div>
        </div>

        <div style="display: flex; gap: 24px; flex-wrap: wrap; padding-top: 16px; border-top: 1px solid #F1F5F9; font-size: 13px;">
          <div><strong style="color: #64748B;">Date de Début :</strong> <span style="font-weight: 700; color: #0F172A;"><?= !empty($item['date_debut_annee']) ? date('d/m/Y', strtotime($item['date_debut_annee'])) : 'Non définie' ?></span></div>
          <div><strong style="color: #64748B;">Date de Fin :</strong> <span style="font-weight: 700; color: #0F172A;"><?= !empty($item['date_fin_annee']) ? date('d/m/Y', strtotime($item['date_fin_annee'])) : 'Non définie' ?></span></div>
          <div><strong style="color: #64748B;">Pénalité Reconduction :</strong> <span style="font-weight: 800; color: #D97706; background:#FEF3C7; padding:2px 8px; border-radius:6px; border:1px solid #FDE68A;"><?= (float)($item['penalite_annee'] ?? 0) ?> %</span></div>
          <div><strong style="color: #64748B;">Statut :</strong> 
            <?php if (($item['statut_annee'] ?? '') === 'actif'): ?>
              <span style="display: inline-flex; align-items: center; gap: 4px; background:#ECFDF5; color:#059669; padding:3px 10px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #A7F3D0;">Actif</span>
            <?php else: ?>
              <span style="display: inline-flex; align-items: center; gap: 4px; background:#FEE2E2; color:#B91C1C; padding:3px 10px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #FECACA;">Clôturé</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>
<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
