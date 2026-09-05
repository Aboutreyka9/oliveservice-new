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
            <i data-lucide="map-pin" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Zone Commerciale : <?= htmlspecialchars($item['libelle_zone'] ?? '') ?>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Code Zone : <code style="font-weight: 700; color: #1E3A5F; background: #F1F5F9; padding: 2px 8px; border-radius: 6px; font-size: 12px; border: 1px solid #CBD5E1;"><?= htmlspecialchars($item['code_zone'] ?? '-') ?></code>
            </p>
          </div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>zone/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour aux zones
          </a>
          <a href="<?= RACINE ?>zone/edition/<?= $encryptedId ?>" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 10px 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
            <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier la Zone
          </a>
        </div>
      </div>

      <!-- CARTE FICHE DÉTAILLÉE -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; max-width: 650px; box-sizing: border-box;">
        <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
          <i data-lucide="info" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Caractéristiques Territoriales
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Libellé de la Zone</span>
            <div style="font-size: 17px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($item['libelle_zone'] ?? '-') ?></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Date de Création</span>
            <div style="font-size: 15px; font-weight: 700; color: #334155; margin-top: 4px;"><?= htmlspecialchars($item['created_at_zone'] ?? '-') ?></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Statut d'Activité</span>
            <div style="margin-top: 6px;">
              <?php if (($item['statut_zone'] ?? '') === 'actif'): ?>
                <span style="display: inline-flex; align-items: center; gap: 6px; background:#ECFDF5; color:#059669; padding:6px 14px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #A7F3D0;">
                  <i data-lucide="check-circle" style="width:14px; height:14px;"></i> Zone Active
                </span>
              <?php else: ?>
                <span style="display: inline-flex; align-items: center; gap: 6px; background:#F1F5F9; color:#64748B; padding:6px 14px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #CBD5E1;">
                  <i data-lucide="minus-circle" style="width:14px; height:14px;"></i> Zone Inactive
                </span>
              <?php endif; ?>
            </div>
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
