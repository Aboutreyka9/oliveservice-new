<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE LA FICHE PERMISSION -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="key" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Fiche Permission : <?= htmlspecialchars($item['libelle_permission'] ?? 'Permission') ?>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Consultation des données et paramètres de l'autorisation granulaire
            </p>
          </div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>permission/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour à la liste
          </a>
          <a href="<?= RACINE ?>permission/edition/<?= $encryptedId ?>" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 10px 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
            <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Éditer cette permission
          </a>
        </div>
      </div>

      <!-- CARTE DÉTAIL PERMISSION (NAVY PREMIUM) -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #F1F5F9;">
          <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 48px; height: 48px; min-width: 48px; border-radius: 12px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.2);">
              <i data-lucide="key" style="width: 24px; height: 24px;"></i>
            </div>
            <div>
              <h3 style="font-size: 17px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">Détails & Paramètres RBAC</h3>
              <span style="font-size: 12px; color: #64748B; font-weight: 600;">Réf ID #<?= htmlspecialchars($item['id_permission'] ?? '-') ?></span>
            </div>
          </div>

          <div>
            <?php if (($item['statut_permission'] ?? '') === 'actif'): ?>
              <span style="display: inline-flex; align-items: center; gap: 6px; background:#ECFDF5; color:#059669; padding:6px 16px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #A7F3D0;">
                <i data-lucide="check-circle" style="width: 14px; height: 14px;"></i> Statut : Actif
              </span>
            <?php else: ?>
              <span style="display: inline-flex; align-items: center; gap: 6px; background:#FEE2E2; color:#B91C1C; padding:6px 16px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #FECACA;">
                <i data-lucide="x-circle" style="width: 14px; height: 14px;"></i> Statut : Inactif
              </span>
            <?php endif; ?>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
          
          <div style="background: #F8FAFC; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; letter-spacing: 0.5px; margin-bottom: 6px;">Libellé de la Permission</div>
            <div style="font-size: 16px; font-weight: 800; color: #0F172A; word-break: break-word;">
              <?= htmlspecialchars($item['libelle_permission'] ?? '-') ?>
            </div>
          </div>

          <div style="background: #F8FAFC; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748B; letter-spacing: 0.5px; margin-bottom: 6px;">Code Clé Système</div>
            <div style="font-size: 14px; font-weight: 800; color: #1E3A5F; font-family: monospace; word-break: break-word;">
              <code style="background: #E2E8F0; padding: 4px 8px; border-radius: 6px; border: 1px solid #CBD5E1;">
                <?= htmlspecialchars($item['code_permission'] ?? '-') ?>
              </code>
            </div>
          </div>

          <div style="background: #F0F9FF; border-radius: 12px; padding: 18px; border: 1px solid #BAE6FD;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #0369A1; letter-spacing: 0.5px; margin-bottom: 6px;">Module Fonctionnel</div>
            <div style="font-size: 16px; font-weight: 800; color: #0284C7; word-break: break-word;">
              <?= htmlspecialchars($item['module_permission'] ?? '-') ?>
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
