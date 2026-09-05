<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$item = $item ?? [];
$commercial = $commercial ?? [];
$zone = $zone ?? [];
$nomCommercial = trim(($commercial['nom_user'] ?? '') . ' ' . ($commercial['prenom_user'] ?? ''));
$montant = (float)($item['montant_versement'] ?? 0);
$statut = $item['statut_versement'] ?? 'En attente';
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE LA FICHE VERSEMENT -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="arrow-down-left" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Bordereau Versement : <code style="font-weight: 800; color: #1E3A5F; font-size: 20px; background: #F1F5F9; padding: 3px 10px; border-radius: 6px; border: 1px solid #CBD5E1;"><?= htmlspecialchars($item['code_versement_commercial'] ?? '-') ?></code>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Dépôt de fonds effectué du <strong><?= htmlspecialchars($item['periode_versement_debut'] ?? '-') ?></strong> au <strong><?= htmlspecialchars($item['periode_versement_fin'] ?? '-') ?></strong>
            </p>
          </div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>versement/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour aux versements
          </a>
          <a href="<?= RACINE ?>versement/edition/<?= $encryptedId ?>" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 10px 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
            <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier
          </a>
        </div>
      </div>

      <!-- CARTE DÉTAIL VERSEMENT (NAVY PREMIUM) -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
          <i data-lucide="arrow-down-left" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Informations sur le Dépôt de Fonds
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div style="background: #F8FAFC; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Agent Commercial</span>
            <div style="font-size: 17px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($nomCommercial ?: 'Non renseigné') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px; font-weight: 500;">
              Code : <code style="font-weight: 700; color: #1E3A5F; background: #E2E8F0; padding: 2px 6px; border-radius: 4px;"><?= htmlspecialchars($item['commercial_code'] ?? '-') ?></code>
            </div>
          </div>

          <div style="background: #F8FAFC; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Zone d'Activité</span>
            <div style="font-size: 17px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($zone['libelle_zone'] ?? ($item['zone_code'] ?? '-')) ?></div>
          </div>

          <div style="background: #ECFDF5; border-radius: 12px; padding: 18px; border: 1px solid #A7F3D0;">
            <span style="font-size: 11px; font-weight: 700; color: #047857; text-transform: uppercase; letter-spacing: 0.5px;">Montant Versé</span>
            <div style="font-size: 22px; font-weight: 800; color: #059669; margin-top: 4px;">+<?= number_format($montant, 0, ',', ' ') ?> FCFA</div>
          </div>

          <div style="background: #F8FAFC; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Référence Versement</span>
            <div style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin-top: 4px; font-family: monospace;"><?= htmlspecialchars($item['reference_versement'] ?? 'Aucune') ?></div>
          </div>

          <div style="background: #F8FAFC; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Statut Validation</span>
            <div>
              <?php if ($statut === 'valide'): ?>
                <span style="display: inline-flex; align-items: center; gap: 6px; background:#ECFDF5; color:#059669; padding:6px 14px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #A7F3D0;">
                  <i data-lucide="check-circle" style="width: 14px; height: 14px;"></i> Validé en Caisse
                </span>
              <?php elseif ($statut === 'ennule' || $statut === 'annule'): ?>
                <span style="display: inline-flex; align-items: center; gap: 6px; background:#FEE2E2; color:#DC2626; padding:6px 14px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #FECACA;">
                  <i data-lucide="x-circle" style="width: 14px; height: 14px;"></i> Annulé
                </span>
              <?php else: ?>
                <span style="display: inline-flex; align-items: center; gap: 6px; background:#FEF3C7; color:#D97706; padding:6px 14px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #FDE68A;">
                  <i data-lucide="clock" style="width: 14px; height: 14px;"></i> En attente
                </span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <?php if (!empty($item['commentaire_validation'])): ?>
        <div style="margin-top: 24px; padding: 16px 20px; background: #F8FAFC; border-left: 4px solid #1E3A5F; border-radius: 8px; font-size: 13px; color: #334155; border: 1px solid #E2E8F0; border-left-width: 4px;">
          <strong style="color: #0F172A; font-weight: 800;">Commentaire de validation :</strong>
          <div style="margin-top: 4px; line-height: 1.5; font-weight: 500;"><?= nl2br(htmlspecialchars($item['commentaire_validation'])) ?></div>
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
