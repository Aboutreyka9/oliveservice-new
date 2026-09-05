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
            <i data-lucide="truck" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              PV Distribution : <code style="font-weight: 800; color: #1E3A5F; font-size: 18px; background: #F1F5F9; padding: 2px 10px; border-radius: 8px; border: 1px solid #CBD5E1;"><?= htmlspecialchars($item['code_distribution'] ?? '-') ?></code>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Remise effectuée le <?= !empty($item['date_distribution_effectuee']) ? date('d/m/Y à H:i', strtotime($item['date_distribution_effectuee'])) : '-' ?>
            </p>
          </div>
        </div>

        <a href="<?= RACINE ?>distribution/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;">
          <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour aux distributions
        </a>
      </div>

      <!-- CARTE INFORMATIONS BÉNÉFICIAIRE -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
          <i data-lucide="user-check" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Informations Bénéficiaire
        </h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Client Bénéficiaire</span>
            <div style="font-size: 17px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars(trim(($souscription['nom_client'] ?? '') . ' ' . ($souscription['prenom_client'] ?? ''))) ?></div>
            <div style="font-size: 13px; color: #64748B; margin-top: 2px; font-weight: 500;">Tél : <?= htmlspecialchars($souscription['telephone_client'] ?? '-') ?></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Souscription & Pack</span>
            <div style="font-size: 17px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= htmlspecialchars($souscription['code_souscription'] ?? '-') ?></div>
            <div style="font-size: 13px; color: #64748B; margin-top: 2px; font-weight: 600;">Pack : <?= htmlspecialchars($souscription['libelle_pack'] ?? '-') ?></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Statut Distribution</span>
            <div style="margin-top: 6px;">
              <?php 
                $statut = $item['statut_distribution'] ?? 'En attente';
                $badgeStyle = 'background: #FEF3C7; color: #B45309; border: 1px solid #FDE68A;';
                $icon = 'clock';
                if ($statut === 'valide') {
                  $badgeStyle = 'background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0;';
                  $icon = 'check-circle';
                } elseif ($statut === 'ennule') {
                  $badgeStyle = 'background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA;';
                  $icon = 'x-circle';
                }
              ?>
              <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-weight: 800; font-size: 12px; <?= $badgeStyle ?>">
                <i data-lucide="<?= $icon ?>" style="width: 14px; height: 14px;"></i> <?= htmlspecialchars($statut) ?>
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- CARTE DÉTAILS LOGISTIQUES -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
          <i data-lucide="map-pin" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Détails Logistiques
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Zone de Livraison</span>
            <div style="font-size: 15px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($item['libelle_zone'] ?? ($item['zone_code'] ?? '-')) ?></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Agent Livreur</span>
            <div style="font-size: 15px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars(trim(($livreur['nom_user'] ?? '') . ' ' . ($livreur['prenom_user'] ?? ''))) ?></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Date & Heure de Remise</span>
            <div style="font-size: 15px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= !empty($item['date_distribution_effectuee']) ? date('d/m/Y à H:i', strtotime($item['date_distribution_effectuee'])) : '-' ?></div>
          </div>
        </div>

        <?php if (!empty($item['observation_distribution'])): ?>
        <div style="margin-top: 24px; padding: 16px; background: #F8FAFC; border-radius: 12px; border: 1px solid #E2E8F0;">
          <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Observation / Remarques</span>
          <p style="margin: 6px 0 0 0; color: #334155; font-size: 14px; font-weight: 500; line-height: 1.5;"><?= nl2br(htmlspecialchars($item['observation_distribution'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($item['pv_reception_photo'])): ?>
        <div style="margin-top: 24px;">
          <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">PV de Réception (Photo)</span>
          <div style="margin-top: 10px;">
            <img src="<?= RACINE . htmlspecialchars($item['pv_reception_photo']) ?>" style="max-height: 240px; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
          </div>
        </div>
        <?php endif; ?>
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
