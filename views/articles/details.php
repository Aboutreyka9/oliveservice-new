<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="package" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Fiche Article : <?= htmlspecialchars($item['libelle_article'] ?? '') ?>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Code Article : <code style="font-weight: 700; color: #1E3A5F; background: #F1F5F9; padding: 2px 8px; border-radius: 6px; font-size: 12px; border: 1px solid #CBD5E1;"><?= htmlspecialchars($item['code_article'] ?? '') ?></code>
            </p>
          </div>
        </div>

        <a href="<?= RACINE ?>article/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;">
          <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour au catalogue
        </a>
      </div>

      <!-- CARTE DETAILS PRINCIPALE -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); max-width: 680px; box-sizing: border-box;">
        <div style="display: flex; flex-direction: column; gap: 20px;">
          
          <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #F1F5F9; padding-bottom: 14px;">
            <span style="font-size: 13px; font-weight: 700; color: #64748B;">Nom / Désignation</span>
            <span style="font-size: 15px; font-weight: 800; color: #0F172A;"><?= htmlspecialchars($item['libelle_article'] ?? '-') ?></span>
          </div>

          <div style="display: flex; align-items: flex-start; justify-content: space-between; border-bottom: 1px solid #F1F5F9; padding-bottom: 14px;">
            <span style="font-size: 13px; font-weight: 700; color: #64748B;">Description</span>
            <span style="font-size: 14px; font-weight: 500; color: #334155; text-align: right; max-width: 380px;">
              <?= !empty($item['description_article']) ? nl2br(htmlspecialchars($item['description_article'])) : '<em style="color:#94A3B8;">Aucune description renseignée</em>' ?>
            </span>
          </div>

          <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #F1F5F9; padding-bottom: 14px;">
            <span style="font-size: 13px; font-weight: 700; color: #64748B;">Statut du produit</span>
            <?php $isActif = ($item['statut_article'] ?? '') === 'actif'; ?>
            <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 800; padding: 4px 12px; border-radius: 20px; background: <?= $isActif ? '#ECFDF5' : '#F1F5F9' ?>; color: <?= $isActif ? '#059669' : '#64748B' ?>; border: 1px solid <?= $isActif ? '#A7F3D0' : '#E2E8F0' ?>;">
              <i data-lucide="<?= $isActif ? 'check-circle' : 'x-circle' ?>" style="width: 14px; height: 14px;"></i>
              <?= ucfirst($item['statut_article'] ?? 'inactif') ?>
            </span>
          </div>

        </div>

        <div style="margin-top: 32px; display: flex; justify-content: flex-end; gap: 12px; padding-top: 20px; border-top: 1px solid #E2E8F0;">
          <a href="<?= RACINE ?>article/edition/<?= $encryptedId ?>" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 12px 24px; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
            <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Éditer l'article
          </a>
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
