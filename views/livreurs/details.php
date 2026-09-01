<?php
require_once __DIR__ . '/../../public/inc/header.php';
$livreur = isset($livreur) ? $livreur : [];
$missions = isset($missions) ? $missions : [];
$stats = isset($stats) ? $stats : ['total_missions' => 0, 'terminees' => 0, 'en_cours' => 0, 'collectes' => 0, 'livraisons' => 0];
$pressingName = isset($pressingName) ? $pressingName : ($livreur['pressing_code'] ?? 'Non affecté');
$nomComplet = trim(($livreur['nom_livreur'] ?? '') . ' ' . ($livreur['prenom_livreur'] ?? '')) ?: 'Livreur';
?>

<style>
/* === MOBILE PWA UX OPTIMIZATIONS FOR LIVREUR DETAILS === */
@media (max-width: 768px) {
  .content-wrapper {
    padding: 12px 10px 80px 10px !important;
  }
  .page-header {
    flex-direction: column !important;
    align-items: stretch !important;
    margin-bottom: 16px !important;
    gap: 12px !important;
  }
  .page-header-actions {
    display: flex !important;
    flex-direction: column !important;
    gap: 8px !important;
    width: 100% !important;
  }
  .page-header-actions .btn {
    width: 100% !important;
    justify-content: center !important;
    height: 46px !important;
    font-size: 14px !important;
  }
  .livreur-stats-grid {
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 10px !important;
  }
  .livreur-detail-grid {
    grid-template-columns: 1fr !important;
    gap: 16px !important;
  }
}
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="bike" style="color: #2563EB;"></i> Fiche Livreur
          </h1>
          <p class="page-subtitle" style="color: #64748B; margin: 4px 0 0 0;">Informations complètes et historique d'activité</p>
        </div>
        <div class="page-header-actions" style="display: flex; gap: 10px;">
          <a href="<?= RACINE ?>livreur/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
            <i data-lucide="arrow-left"></i> Retour à la liste
          </a>
          <?php if (!empty($encryptedId)): ?>
            <a href="<?= RACINE ?>livreur/edition/<?= htmlspecialchars($encryptedId) ?>" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
              <i data-lucide="edit-3"></i> Modifier le profil
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- STATS DU LIVREUR -->
      <div class="livreur-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: #FFFFFF; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0; display: flex; align-items: center; gap: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="width: 48px; height: 48px; border-radius: 10px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i data-lucide="package"></i>
          </div>
          <div>
            <span style="font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase;">Total Missions</span>
            <h3 style="font-size: 22px; font-weight: 800; color: #1E293B; margin: 2px 0 0 0;"><?= (int)$stats['total_missions'] ?></h3>
          </div>
        </div>

        <div style="background: #FFFFFF; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0; display: flex; align-items: center; gap: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="width: 48px; height: 48px; border-radius: 10px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i data-lucide="check-circle"></i>
          </div>
          <div>
            <span style="font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase;">Terminées</span>
            <h3 style="font-size: 22px; font-weight: 800; color: #059669; margin: 2px 0 0 0;"><?= (int)$stats['terminees'] ?></h3>
          </div>
        </div>

        <div style="background: #FFFFFF; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0; display: flex; align-items: center; gap: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="width: 48px; height: 48px; border-radius: 10px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i data-lucide="truck"></i>
          </div>
          <div>
            <span style="font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase;">En cours</span>
            <h3 style="font-size: 22px; font-weight: 800; color: #D97706; margin: 2px 0 0 0;"><?= (int)$stats['en_cours'] ?></h3>
          </div>
        </div>

        <div style="background: #FFFFFF; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0; display: flex; align-items: center; gap: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="width: 48px; height: 48px; border-radius: 10px; background: #F3E8FF; color: #7C3AED; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i data-lucide="archive"></i>
          </div>
          <div>
            <span style="font-size: 12px; font-weight: 600; color: #64748B; text-transform: uppercase;">Collectes / Livr.</span>
            <h3 style="font-size: 20px; font-weight: 800; color: #7C3AED; margin: 2px 0 0 0;">
              <?= (int)$stats['collectes'] ?> <span style="font-size: 13px; font-weight: 500; color: #94A3B8;">/ <?= (int)$stats['livraisons'] ?></span>
            </h3>
          </div>
        </div>
      </div>

      <div class="livreur-detail-grid" style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
        <!-- CARTE PROFIL LIVREUR -->
        <div class="detail-card" style="background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); height: fit-content;">
          <div class="detail-card-header" style="background: #F8FAFC; padding: 16px 20px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 16px; font-weight: 700; color: #1E293B; margin: 0;">Identité du Livreur</h2>
            <span class="badge-status <?= ($livreur['statut_livreur'] ?? '') == 'actif' ? 'delivered' : 'cancelled' ?>" style="font-size: 12px; padding: 4px 10px; font-weight: 700;">
              <?= ($livreur['statut_livreur'] ?? '') == 'actif' ? 'Actif' : 'Inactif' ?>
            </span>
          </div>

          <div class="detail-card-body" style="padding: 20px;">
            <div style="text-align: center; margin-bottom: 20px;">
              <div style="width: 72px; height: 72px; border-radius: 50%; background: #1E3A5F; color: #FFFFFF; display: inline-flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 700; margin-bottom: 10px;">
                <?= htmlspecialchars(strtoupper(substr($livreur['nom_livreur'] ?? 'L', 0, 1) . substr($livreur['prenom_livreur'] ?? 'V', 0, 1))) ?>
              </div>
              <h3 style="font-size: 18px; font-weight: 800; color: #1E293B; margin: 0;"><?= htmlspecialchars($nomComplet) ?></h3>
              <span class="code-badge" style="display: inline-block; margin-top: 6px; background: #EFF6FF; color: #2563EB; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 12px;">
                <?= htmlspecialchars($livreur['code_livreur'] ?? '') ?>
              </span>
            </div>

            <div class="info-list" style="display: flex; flex-direction: column; gap: 14px; border-top: 1px solid #F1F5F9; padding-top: 16px;">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #64748B; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                  <i data-lucide="phone" style="width: 14px; height: 14px;"></i> Téléphone
                </span>
                <a href="tel:<?= htmlspecialchars($livreur['telephone_livreur'] ?? '') ?>" style="font-weight: 700; color: #2563EB; text-decoration: none; font-size: 14px;">
                  <?= htmlspecialchars($livreur['telephone_livreur'] ?? '-') ?>
                </a>
              </div>

              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #64748B; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                  <i data-lucide="building" style="width: 14px; height: 14px;"></i> Pressing rattaché
                </span>
                <span style="font-weight: 700; color: #1E293B; font-size: 13px;">
                  <?= htmlspecialchars($pressingName) ?>
                </span>
              </div>

              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #64748B; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                  <i data-lucide="calendar" style="width: 14px; height: 14px;"></i> Date d'ajout
                </span>
                <span style="color: #64748B; font-size: 13px;">
                  <?= !empty($livreur['created_at_livreur']) ? date('d/m/Y H:i', strtotime($livreur['created_at_livreur'])) : '-' ?>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- TABLEAU DES MISSIONS -->
        <div class="detail-card" style="background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div class="detail-card-header" style="background: #F8FAFC; padding: 16px 20px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 16px; font-weight: 700; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="map-pin" style="color: #2563EB;"></i> Historique des Missions
            </h2>
            <span style="font-size: 13px; color: #64748B; font-weight: 600;"><?= count($missions) ?> mission(s)</span>
          </div>

          <div class="detail-card-body" style="padding: 0;">
            <?php if (empty($missions)): ?>
              <div style="padding: 40px 20px; text-align: center; color: #94A3B8;">
                <i data-lucide="inbox" style="width: 42px; height: 42px; stroke-width: 1.5; margin-bottom: 10px;"></i>
                <p style="font-size: 14px; margin: 0;">Aucune mission assignée à ce livreur pour le moment.</p>
              </div>
            <?php else: ?>
              <div class="table-responsive-mobile">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                  <thead style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0;">
                    <tr>
                      <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 700; color: #64748B;">Mission</th>
                      <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 700; color: #64748B;">Commande</th>
                      <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 700; color: #64748B;">Type</th>
                      <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 700; color: #64748B;">Client / Adresse</th>
                      <th style="padding: 12px 16px; text-align: center; font-size: 12px; font-weight: 700; color: #64748B;">Statut</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($missions as $m): 
                      $isCollecte = strtolower($m['type_mission'] ?? '') === 'collecte';
                      $statutM = $m['statut_mission'] ?? 'en_attente';
                      $statutClass = ($statutM === 'terminee') ? 'delivered' : (($statutM === 'en_cours') ? 'info' : 'warning');
                    ?>
                    <tr style="border-bottom: 1px solid #F1F5F9;">
                      <td style="padding: 12px 16px; font-size: 13px; font-weight: 700; color: #1E293B;">
                        <?= htmlspecialchars($m['code_mission'] ?? ('MIS-' . $m['id_mission'])) ?>
                      </td>
                      <td style="padding: 12px 16px; font-size: 13px;">
                        <a href="<?= RACINE ?>commande/list" style="font-weight: 700; color: #2563EB; text-decoration: none;">
                          <?= htmlspecialchars($m['commande_code'] ?? '-') ?>
                        </a>
                      </td>
                      <td style="padding: 12px 16px;">
                        <?php if ($isCollecte): ?>
                          <span style="background: #FEF3C7; color: #B45309; border: 1px solid #FCD34D; border-radius: 6px; padding: 4px 8px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                            <i data-lucide="package" style="width: 13px; height: 13px;"></i> Collecte
                          </span>
                        <?php else: ?>
                          <span style="background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; border-radius: 6px; padding: 4px 8px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                            <i data-lucide="truck" style="width: 13px; height: 13px;"></i> Livraison
                          </span>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 12px 16px; font-size: 13px;">
                        <strong style="color: #334155; display: block;"><?= htmlspecialchars($m['nom_client'] ?? 'Client') ?></strong>
                        <small style="color: #64748B;"><?= htmlspecialchars($m['adresse_mission'] ?? '-') ?></small>
                      </td>
                      <td style="padding: 12px 16px; text-align: center;">
                        <span class="badge-status <?= $statutClass ?>" style="font-size: 12px; padding: 4px 8px;">
                          <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $statutM))) ?>
                        </span>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
