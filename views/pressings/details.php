<?php
require_once __DIR__ . '/../../public/inc/header.php';

$pressing   = $pressing ?? [];
$stats      = $stats ?? [];
$orders     = $orders ?? [];
$tarifs     = $tarifs ?? [];
$horaires   = $horaires ?? [];
$clients    = $clients ?? [];
$missions   = $missions ?? [];
$abonnement = $abonnement ?? null;
$owner      = $owner ?? null;
$pressingUsers = $pressingUsers ?? [];

$codePressing = $pressing['code_pressing'] ?? '';
$libelle      = $pressing['libelle_pressing'] ?? 'Pressing';
$statut       = $pressing['statut_pressing'] ?? 'actif';
$logoStr      = $pressing['logo_pressing'] ?? '';
$logo         = !empty($logoStr) ? ((strpos($logoStr, 'http') === 0) ? $logoStr : RACINE . 'public/assets/images/pressings/' . $logoStr) : null;
$miniatureStr = $pressing['miniature_pressing'] ?? '';
$miniature    = !empty($miniatureStr) ? ((strpos($miniatureStr, 'http') === 0) ? $miniatureStr : RACINE . 'public/assets/images/pressings/' . $miniatureStr) : null;
?>

<style>
/* === MOBILE PWA UX OPTIMIZATIONS FOR PRESSING DETAILS & CONFIG === */
@media (max-width: 768px) {
  .content-wrapper {
    padding: 12px 10px 80px 10px !important;
    max-width: 100vw !important;
    box-sizing: border-box !important;
  }
  .page-header {
    flex-direction: column !important;
    align-items: stretch !important;
    margin-bottom: 16px !important;
    gap: 14px !important;
  }
  .page-header-info {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 10px !important;
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
  .stats-grid {
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 10px !important;
  }
  .table-responsive-mobile {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch !important;
    max-width: 100% !important;
  }
}
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <!-- === EN-TÊTE DU HUB PRESSING 360° === -->
      <div class="page-header" style="margin-bottom: 24px;">
        <div class="page-header-info" style="display: flex; align-items: center; gap: 16px;">
          <?php if ($miniature): ?>
            <img src="<?= htmlspecialchars($miniature) ?>" alt="<?= htmlspecialchars($libelle) ?> (Miniature)" title="Miniature Lavex" style="width: 90px; height: 60px; border-radius: 12px; object-fit: cover; border: 2px solid var(--border-color, #E2E8F0); box-shadow: 0 4px 12px rgba(0,0,0,0.06);">
          <?php elseif ($logo): ?>
            <img src="<?= htmlspecialchars($logo) ?>" alt="<?= htmlspecialchars($libelle) ?>" style="width: 60px; height: 60px; border-radius: 14px; object-fit: cover; border: 2px solid var(--border-color, #E2E8F0); box-shadow: 0 4px 12px rgba(0,0,0,0.06);">
          <?php else: ?>
            <div style="width: 60px; height: 60px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F, #0F766E); color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 700; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
              <?= strtoupper(substr($libelle, 0, 2)) ?>
            </div>
          <?php endif; ?>
          <div>
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
              <h1 style="margin: 0; font-size: 24px; font-weight: 800; color: var(--text-main, #1E293B);"><?= htmlspecialchars($libelle) ?></h1>
              <span class="code-badge" style="background: #F1F5F9; color: #475569; padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 12px;"><?= htmlspecialchars($codePressing) ?></span>
              <span class="badge-status <?= $statut === 'actif' ? 'delivered' : 'cancelled' ?>" style="text-transform: uppercase; font-size: 11px; padding: 3px 8px; border-radius: 6px;">
                <?= htmlspecialchars($statut) ?>
              </span>
              <span class="badge-status delivered" style="font-size: 11px; padding: 3px 8px; border-radius: 6px; background: #F8FAFC; color: #334155; border: 1px solid #E2E8F0;">
                <i data-lucide="clock" style="width: 12px; height: 12px; vertical-align: -1px; color: #2563EB;"></i> <?= htmlspecialchars($pressing['delai_livraison_pressing'] ?? '24h - 48h') ?>
              </span>
              <?php 
                $modeLog = $pressing['mode_logistique'] ?? 'pressing';
                if ($modeLog === 'lavex'):
              ?>
                <span class="badge-status delivered" style="font-size: 11px; padding: 3px 8px; border-radius: 6px; background: #DBEAFE; color: #1E40AF; border: 1px solid #BFDBFE;">
                  <i data-lucide="rocket" style="width: 12px; height: 12px; vertical-align: -1px;"></i> Flotte Officielle Lavex
                </span>
              <?php elseif ($modeLog === 'retrait_uniquement'): ?>
                <span class="badge-status delivered" style="font-size: 11px; padding: 3px 8px; border-radius: 6px; background: #F3F4F6; color: #4B5563; border: 1px solid #E5E7EB;">
                  <i data-lucide="store" style="width: 12px; height: 12px; vertical-align: -1px;"></i> Retrait Atelier Uniquement
                </span>
              <?php else: ?>
                <span class="badge-status delivered" style="font-size: 11px; padding: 3px 8px; border-radius: 6px; background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A;">
                  <i data-lucide="truck" style="width: 12px; height: 12px; vertical-align: -1px;"></i> Livreur du Pressing (Frais: <?= number_format((float)($pressing['frais_collecte_pressing'] ?? 1000), 0, ',', ' ') ?> / <?= number_format((float)($pressing['frais_livraison_pressing'] ?? 1000), 0, ',', ' ') ?> FCFA)
                </span>
              <?php endif; ?>
              <?php if (!empty($pressing['livraison_gratuite'])): ?>
                <span class="badge-status delivered" style="font-size: 11px; padding: 3px 8px; border-radius: 6px; background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0;">
                  <i data-lucide="truck" style="width: 12px; height: 12px; vertical-align: -1px;"></i> Livraison Gratuite <?= !empty($pressing['seuil_livraison_gratuite']) ? '(dès ' . number_format($pressing['seuil_livraison_gratuite'], 0, ',', ' ') . ' FCFA)' : '' ?>
                </span>
              <?php endif; ?>
              <?php if (!isset($pressing['accepte_colis_sans_detail']) || !empty($pressing['accepte_colis_sans_detail'])): ?>
                <span class="badge-status delivered" style="font-size: 11px; padding: 3px 8px; border-radius: 6px; background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE;">
                  <i data-lucide="shopping-bag" style="width: 12px; height: 12px; vertical-align: -1px;"></i> Collecte au Sac Acceptée
                </span>
              <?php endif; ?>
            </div>
            <p class="page-subtitle" style="margin: 4px 0 0; color: #64748B; font-size: 13px;">
              <i data-lucide="map-pin" style="width: 14px; height: 14px; vertical-align: middle; display: inline;"></i> <?= htmlspecialchars($pressing['adresse_pressing'] ?? 'Adresse non renseignée') ?>
              <?php if (!empty($pressing['telephone_pressing'])): ?>
                &nbsp;•&nbsp; <i data-lucide="phone" style="width: 14px; height: 14px; vertical-align: middle; display: inline;"></i> <?= htmlspecialchars($pressing['telephone_pressing']) ?>
              <?php endif; ?>
            </p>
          </div>
        </div>
        <div class="page-header-actions" style="display: flex; gap: 8px;">
          <a href="<?= RACINE ?>pressing/edition/<?= $encryptedId ?>" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
            <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i> Modifier la fiche
          </a>
          <a href="<?= RACINE ?>pressing/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour
          </a>
        </div>
      </div>

      <!-- === CARTES KPIS 360° === -->
      <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card">
          <div class="stat-icon delivered" style="background: rgba(16, 185, 129, 0.12); color: #10B981;">
            <i data-lucide="wallet"></i>
          </div>
          <div class="stat-info">
            <h3>Chiffre d'Affaires (Livré)</h3>
            <p class="stat-value" style="color: #059669; font-weight: 800;"><?= number_format((float)($stats['ca_total'] ?? 0), 0, ',', ' ') ?> <small style="font-size: 14px; font-weight: 600;">FCFA</small></p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon clients" style="background: rgba(30, 58, 95, 0.12); color: #1E3A5F;">
            <i data-lucide="clipboard-list"></i>
          </div>
          <div class="stat-info">
            <h3>Commandes Totales</h3>
            <p class="stat-value"><?= (int)($stats['total_commandes'] ?? 0) ?> <span style="font-size: 12px; font-weight: 600; color: #D97706;">(<?= (int)($stats['commandes_en_cours'] ?? 0) ?> en cours)</span></p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon users" style="background: rgba(14, 165, 233, 0.12); color: #0EA5E9;">
            <i data-lucide="users"></i>
          </div>
          <div class="stat-info">
            <h3>Clients Uniques</h3>
            <p class="stat-value"><?= (int)($stats['total_clients'] ?? 0) ?></p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon products" style="background: rgba(139, 92, 246, 0.12); color: #8B5CF6;">
            <i data-lucide="shirt"></i>
          </div>
          <div class="stat-info">
            <h3>Articles Tarifés</h3>
            <p class="stat-value"><?= (int)($stats['total_tarifs'] ?? 0) ?></p>
          </div>
        </div>
      </div>

      <!-- === NAVIGATION PAR ONGLETS DU HUB 360° === -->
      <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 24px;">
        <div style="display: flex; border-bottom: 1px solid var(--border-color, #E2E8F0); background: #F8FAFC; overflow-x: auto; scrollbar-width: none;">
          <button type="button" class="hub-tab-btn active" onclick="switchHubTab(this, 'tab-orders')" style="display: inline-flex; align-items: center; gap: 8px; padding: 14px 20px; font-size: 14px; font-weight: 700; border: none; background: transparent; cursor: pointer; color: var(--primary-color, #1E3A5F); border-bottom: 2px solid var(--primary-color, #1E3A5F);">
            <i data-lucide="clipboard-list" style="width: 18px; height: 18px;"></i>
            <span>Commandes (<?= count($orders) ?>)</span>
          </button>
          <button type="button" class="hub-tab-btn" onclick="switchHubTab(this, 'tab-tarifs')" style="display: inline-flex; align-items: center; gap: 8px; padding: 14px 20px; font-size: 14px; font-weight: 600; border: none; background: transparent; cursor: pointer; color: #64748B; border-bottom: 2px solid transparent;">
            <i data-lucide="dollar-sign" style="width: 18px; height: 18px;"></i>
            <span>Catalogue & Tarifs (<?= count($tarifs) ?>)</span>
          </button>
          <button type="button" class="hub-tab-btn" onclick="switchHubTab(this, 'tab-horaires')" style="display: inline-flex; align-items: center; gap: 8px; padding: 14px 20px; font-size: 14px; font-weight: 600; border: none; background: transparent; cursor: pointer; color: #64748B; border-bottom: 2px solid transparent;">
            <i data-lucide="clock" style="width: 18px; height: 18px;"></i>
            <span>Horaires</span>
          </button>
          <button type="button" class="hub-tab-btn" onclick="switchHubTab(this, 'tab-clients')" style="display: inline-flex; align-items: center; gap: 8px; padding: 14px 20px; font-size: 14px; font-weight: 600; border: none; background: transparent; cursor: pointer; color: #64748B; border-bottom: 2px solid transparent;">
            <i data-lucide="users" style="width: 18px; height: 18px;"></i>
            <span>Clients (<?= count($clients) ?>)</span>
          </button>
          <button type="button" class="hub-tab-btn" onclick="switchHubTab(this, 'tab-missions')" style="display: inline-flex; align-items: center; gap: 8px; padding: 14px 20px; font-size: 14px; font-weight: 600; border: none; background: transparent; cursor: pointer; color: #64748B; border-bottom: 2px solid transparent;">
            <i data-lucide="truck" style="width: 18px; height: 18px;"></i>
            <span>Livreurs & Missions (<?= count($missions) ?>)</span>
          </button>
          <button type="button" class="hub-tab-btn" onclick="switchHubTab(this, 'tab-users')" style="display: inline-flex; align-items: center; gap: 8px; padding: 14px 20px; font-size: 14px; font-weight: 600; border: none; background: transparent; cursor: pointer; color: #64748B; border-bottom: 2px solid transparent;">
            <i data-lucide="user-check" style="width: 18px; height: 18px;"></i>
            <span>Équipe & Personnel (<?= count($pressingUsers) ?>)</span>
          </button>
          <button type="button" class="hub-tab-btn" onclick="switchHubTab(this, 'tab-infos')" style="display: inline-flex; align-items: center; gap: 8px; padding: 14px 20px; font-size: 14px; font-weight: 600; border: none; background: transparent; cursor: pointer; color: #64748B; border-bottom: 2px solid transparent;">
            <i data-lucide="info" style="width: 18px; height: 18px;"></i>
            <span>Informations & Profil</span>
          </button>
        </div>

        <div style="padding: 20px;">
          <!-- === TAB 1 : COMMANDES === -->
          <div id="tab-orders" class="hub-tab-content active">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h2 style="font-size: 16px; font-weight: 700; margin: 0; color: #1E293B;">Commandes du pressing</h2>
              <span style="font-size: 13px; color: #64748B;"><?= count($orders) ?> commande(s) enregistrée(s)</span>
            </div>

            <?php if (empty($orders)): ?>
              <div style="text-align: center; padding: 40px 16px; color: #94A3B8;">
                <i data-lucide="inbox" style="width: 48px; height: 48px; margin-bottom: 12px; stroke-width: 1.5;"></i>
                <p style="font-size: 14px; font-weight: 600; margin: 0;">Aucune commande passée dans ce pressing pour le moment.</p>
              </div>
            <?php else: ?>
              <div class="table-responsive-mobile">
                <table class="table" style="width: 100%;">
                  <thead>
                    <tr>
                      <th>Code</th>
                      <th>Client</th>
                      <th>Type</th>
                      <th>Montant Total</th>
                      <th>Statut</th>
                      <th>Date</th>
                      <th style="text-align: right;">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($orders as $o): 
                      $st = $o['statut_suivi_commande'] ?? 'creee';
                      $stClass = 'badge-status';
                      switch ($st) {
                        case 'creee':
                          $stClass = 'badge-status-pending';
                          break;
                        case 'acceptee':
                        case 'collectee':
                        case 'en_traitement':
                        case 'prete':
                        case 'en_livraison':
                          $stClass = 'badge-status-progress';
                          break;
                        case 'livree':
                          $stClass = 'delivered';
                          break;
                        case 'refusee':
                        case 'annulee':
                          $stClass = 'cancelled';
                          break;
                      }
                    ?>
                      <tr>
                        <td><strong style="color: #1E3A5F;"><?= htmlspecialchars($o['code_commande']) ?></strong></td>
                        <td>
                          <div style="font-weight: 600; color: #334155;"><?= htmlspecialchars($o['nom_client']) ?></div>
                          <div style="font-size: 12px; color: #64748B;"><?= htmlspecialchars($o['telephone_client']) ?></div>
                        </td>
                        <td>
                          <?php if (($o['type_commande'] ?? '') === 'colis'): ?>
                            <span style="display: inline-flex; align-items: center; gap: 4px; background: #FEF3C7; color: #92400E; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                              <i data-lucide="package" style="width: 12px; height: 12px;"></i> Colis (<?= $o['nb_sacs_colis'] ?? 1 ?> sac)
                            </span>
                          <?php else: ?>
                            <span style="display: inline-flex; align-items: center; gap: 4px; background: #EFF6FF; color: #1E40AF; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                              <i data-lucide="shirt" style="width: 12px; height: 12px;"></i> Détaillée
                            </span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <strong style="color: #059669;"><?= number_format((float)$o['montant_total_commande'], 0, ',', ' ') ?> FCFA</strong>
                        </td>
                        <td>
                          <span class="badge-status <?= $stClass ?>" style="text-transform: capitalize; font-size: 11px; padding: 3px 8px; border-radius: 6px;">
                            <?= str_replace('_', ' ', htmlspecialchars($st)) ?>
                          </span>
                        </td>
                        <td style="font-size: 12px; color: #64748B;">
                          <?= date('d/m/Y H:i', strtotime($o['created_at_commande'])) ?>
                        </td>
                        <td style="text-align: right;">
                          <a href="<?= RACINE ?>commande/details/<?= $o['code_commande'] ?>" class="btn btn-sm btn-secondary" style="padding: 4px 10px; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;">
                            <i data-lucide="eye" style="width: 14px; height: 14px;"></i> Voir
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>

          <!-- === TAB 2 : TARIFS & ARTICLES === -->
          <div id="tab-tarifs" class="hub-tab-content" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h2 style="font-size: 16px; font-weight: 700; margin: 0; color: #1E293B;">Catalogue & Tarifs personnalisés</h2>
              <a href="<?= RACINE ?>tarif/list" class="btn btn-sm btn-primary" style="display: inline-flex; align-items: center; gap: 4px;">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i> Gérer les tarifs
              </a>
            </div>

            <?php if (empty($tarifs)): ?>
              <div style="text-align: center; padding: 40px 16px; color: #94A3B8;">
                <i data-lucide="tag" style="width: 48px; height: 48px; margin-bottom: 12px; stroke-width: 1.5;"></i>
                <p style="font-size: 14px; font-weight: 600; margin: 0;">Aucun tarif article personnalisé pour ce pressing.</p>
              </div>
            <?php else: ?>
              <div class="table-responsive-mobile">
                <table class="table" style="width: 100%;">
                  <thead>
                    <tr>
                      <th>Catégorie</th>
                      <th>Article</th>
                      <th>Service Associé</th>
                      <th>Prix Fixé</th>
                      <th>Statut</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($tarifs as $t): ?>
                      <tr>
                        <td><span style="font-size: 12px; font-weight: 600; color: #64748B;"><?= htmlspecialchars($t['libelle_categorie']) ?></span></td>
                        <td><strong><?= htmlspecialchars($t['libelle_article']) ?></strong></td>
                        <td><span style="background: #F1F5F9; color: #1E293B; padding: 2px 8px; border-radius: 6px; font-size: 12px; font-weight: 600;"><?= htmlspecialchars($t['libelle_service']) ?></span></td>
                        <td><strong style="color: #059669;"><?= number_format((float)$t['prix_tarif'], 0, ',', ' ') ?> FCFA</strong></td>
                        <td>
                          <span class="badge-status <?= ($t['statut_tarif'] ?? 'actif') === 'actif' ? 'delivered' : 'cancelled' ?>" style="font-size: 11px;">
                            <?= htmlspecialchars($t['statut_tarif'] ?? 'actif') ?>
                          </span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>

          <!-- === TAB 3 : HORAIRES === -->
          <div id="tab-horaires" class="hub-tab-content" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h2 style="font-size: 16px; font-weight: 700; margin: 0; color: #1E293B;">Plages Horaires d'ouverture</h2>
              <a href="<?= RACINE ?>horaire/list" class="btn btn-sm btn-secondary" style="display: inline-flex; align-items: center; gap: 4px;">
                <i data-lucide="clock" style="width: 14px; height: 14px;"></i> Modifier les horaires
              </a>
            </div>

            <?php if (empty($horaires)): ?>
              <div style="text-align: center; padding: 40px 16px; color: #94A3B8;">
                <i data-lucide="calendar" style="width: 48px; height: 48px; margin-bottom: 12px; stroke-width: 1.5;"></i>
                <p style="font-size: 14px; font-weight: 600; margin: 0;">Aucun horaire d'ouverture renseigné.</p>
              </div>
            <?php else: ?>
              <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">
                <?php foreach ($horaires as $h): ?>
                  <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px;">
                    <div style="font-weight: 700; text-transform: capitalize; color: #1E293B; margin-bottom: 4px;"><?= htmlspecialchars($h['jour'] ?? '') ?></div>
                    <?php if (!empty($h['est_ferme']) && $h['est_ferme'] == 1): ?>
                      <span style="color: #DC2626; font-weight: 600; font-size: 13px;">Fermé</span>
                    <?php else: ?>
                      <span style="color: #059669; font-weight: 600; font-size: 13px;">
                        <?= substr($h['heure_ouverture'] ?? '08:00', 0, 5) ?> - <?= substr($h['heure_fermeture'] ?? '18:00', 0, 5) ?>
                      </span>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- === TAB 4 : CLIENTS DU PRESSING === -->
          <div id="tab-clients" class="hub-tab-content" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h2 style="font-size: 16px; font-weight: 700; margin: 0; color: #1E293B;">Clients rattachés à ce pressing</h2>
              <span style="font-size: 13px; color: #64748B;"><?= count($clients) ?> client(s)</span>
            </div>

            <?php if (empty($clients)): ?>
              <div style="text-align: center; padding: 40px 16px; color: #94A3B8;">
                <i data-lucide="users" style="width: 48px; height: 48px; margin-bottom: 12px; stroke-width: 1.5;"></i>
                <p style="font-size: 14px; font-weight: 600; margin: 0;">Aucun client n'a encore commandé dans ce pressing.</p>
              </div>
            <?php else: ?>
              <div class="table-responsive-mobile">
                <table class="table" style="width: 100%;">
                  <thead>
                    <tr>
                      <th>Client</th>
                      <th>Téléphone</th>
                      <th>Adresse</th>
                      <th>Commandes</th>
                      <th>Total Dépensé</th>
                      <th>Dernière Commande</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($clients as $c): ?>
                      <tr>
                        <td><strong><?= htmlspecialchars($c['nom_client'] ?? 'Client') ?></strong></td>
                        <td><?= htmlspecialchars($c['telephone_client'] ?? '-') ?></td>
                        <td><small style="color: #64748B;"><?= htmlspecialchars($c['adresse_client'] ?? '-') ?></small></td>
                        <td><span style="font-weight: 700; color: #1E3A5F;"><?= $c['nb_commandes'] ?></span></td>
                        <td><strong style="color: #059669;"><?= number_format((float)$c['total_depense'], 0, ',', ' ') ?> FCFA</strong></td>
                        <td style="font-size: 12px; color: #64748B;"><?= !empty($c['derniere_commande']) ? date('d/m/Y', strtotime($c['derniere_commande'])) : '-' ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>

          <!-- === TAB 5 : LIVREURS & MISSIONS === -->
          <div id="tab-missions" class="hub-tab-content" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h2 style="font-size: 16px; font-weight: 700; margin: 0; color: #1E293B;">Missions de collecte & livraison</h2>
              <span style="font-size: 13px; color: #64748B;"><?= count($missions) ?> mission(s)</span>
            </div>

            <?php if (empty($missions)): ?>
              <div style="text-align: center; padding: 40px 16px; color: #94A3B8;">
                <i data-lucide="truck" style="width: 48px; height: 48px; margin-bottom: 12px; stroke-width: 1.5;"></i>
                <p style="font-size: 14px; font-weight: 600; margin: 0;">Aucune mission enregistrée pour ce pressing.</p>
              </div>
            <?php else: ?>
              <div class="table-responsive-mobile">
                <table class="table" style="width: 100%;">
                  <thead>
                    <tr>
                      <th>Mission</th>
                      <th>Commande</th>
                      <th>Livreur</th>
                      <th>Client & Lieu</th>
                      <th>Type</th>
                      <th>Statut Mission</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($missions as $m): ?>
                      <tr>
                        <td><strong><?= htmlspecialchars($m['code_mission']) ?></strong></td>
                        <td><span style="color: #1E3A5F; font-weight: 700;"><?= htmlspecialchars($m['code_commande']) ?></span></td>
                        <td>
                          <div style="font-weight: 600; color: #334155;"><?= htmlspecialchars($m['nom_livreur']) ?></div>
                          <div style="font-size: 11px; color: #64748B;"><?= htmlspecialchars($m['telephone_livreur']) ?></div>
                        </td>
                        <td>
                          <div style="font-weight: 600;"><?= htmlspecialchars($m['nom_client']) ?></div>
                          <div style="font-size: 11px; color: #64748B;"><?= htmlspecialchars($m['adresse_mission'] ?? '-') ?></div>
                        </td>
                        <td>
                          <span style="font-size: 12px; font-weight: 700; text-transform: uppercase;">
                            <?= htmlspecialchars($m['type_mission']) ?>
                          </span>
                        </td>
                        <td>
                          <span class="badge-status <?= $m['statut_mission'] === 'terminee' ? 'delivered' : ($m['statut_mission'] === 'annulee' ? 'cancelled' : 'badge-status-progress') ?>" style="font-size: 11px;">
                            <?= htmlspecialchars($m['statut_mission']) ?>
                          </span>
                        </td>
                        <td style="font-size: 12px; color: #64748B;">
                          <?= date('d/m/Y H:i', strtotime($m['created_at_mission'])) ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>

          <!-- === TAB 6 : ABONNEMENT B2B === -->
          <div id="tab-abonnement" class="hub-tab-content" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h2 style="font-size: 16px; font-weight: 700; margin: 0; color: #1E293B;">Abonnement Professionnel B2B</h2>
              <a href="<?= RACINE ?>abonnement/list" class="btn btn-sm btn-primary" style="display: inline-flex; align-items: center; gap: 4px;">
                <i data-lucide="credit-card" style="width: 14px; height: 14px;"></i> Gérer les abonnements
              </a>
            </div>

            <?php if (!$abonnement): ?>
              <div style="text-align: center; padding: 40px 16px; color: #94A3B8; background: #F8FAFC; border-radius: 12px; border: 1px dashed #CBD5E1;">
                <i data-lucide="alert-circle" style="width: 48px; height: 48px; margin-bottom: 12px; color: #F59E0B;"></i>
                <p style="font-size: 14px; font-weight: 700; color: #334155; margin: 0;">Aucun forfait ou abonnement actif pour ce pressing.</p>
                <p style="font-size: 13px; color: #64748B; margin: 4px 0 16px;">Assignez un forfait pour débloquer les fonctionnalités du pressing sur la Marketplace.</p>
                <a href="<?= RACINE ?>abonnement/list" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                  <i data-lucide="plus" style="width: 16px; height: 16px;"></i> Souscrire un forfait
                </a>
              </div>
            <?php else: ?>
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
                  <div>
                    <span style="font-size: 11px; font-weight: 700; color: #2563EB; text-transform: uppercase; letter-spacing: 0.5px; background: #EFF6FF; padding: 3px 8px; border-radius: 6px; border: 1px solid #DBEAFE;">Forfait Actuel</span>
                    <h3 style="font-size: 20px; font-weight: 800; color: #1E293B; margin: 10px 0 6px;"><?= htmlspecialchars($abonnement['libelle_forfait']) ?></h3>
                    <div style="font-size: 26px; font-weight: 800; color: #059669; margin-bottom: 16px;">
                      <?= number_format((float)$abonnement['montant_abonnement'], 0, ',', ' ') ?> <span style="font-size: 13px; font-weight: 600; color: #64748B;">FCFA</span>
                    </div>
                  </div>
                  <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                    <span class="badge-status delivered" style="font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px;">
                      Statut : <?= strtoupper(htmlspecialchars($abonnement['statut_abonnement_pressing'])) ?>
                    </span>
                    <?php if (isset($abonnement['jours_restants'])): ?>
                      <span style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                        <i data-lucide="clock" style="width: 13px; height: 13px;"></i> <?= (int)$abonnement['jours_restants'] ?> jour(s) restant(s)
                      </span>
                    <?php endif; ?>
                  </div>
                </div>

                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px;">
                  <h4 style="font-size: 15px; font-weight: 700; color: #1E293B; margin: 0 0 16px;">Détails de la période</h4>
                  <div class="info-list">
                    <div class="info-item" style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #E2E8F0;">
                      <span style="color: #64748B;">Code Abonnement</span>
                      <strong style="color: #1E3A5F;"><?= htmlspecialchars($abonnement['code_abonnement_pressing']) ?></strong>
                    </div>
                    <div class="info-item" style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #E2E8F0;">
                      <span style="color: #64748B;">Date de début</span>
                      <strong><?= date('d/m/Y', strtotime($abonnement['date_debut_abonnement'])) ?></strong>
                    </div>
                    <div class="info-item" style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #E2E8F0;">
                      <span style="color: #64748B;">Date d'expiration</span>
                      <strong><?= date('d/m/Y', strtotime($abonnement['date_fin_abonnement'])) ?></strong>
                    </div>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          </div>

          <!-- === TAB : ÉQUIPE & PERSONNEL DU PRESSING === -->
          <div id="tab-users" class="hub-tab-content" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
              <div>
                <h2 style="font-size: 16px; font-weight: 700; margin: 0; color: #1E293B;">Équipe & Personnel du pressing</h2>
                <p style="font-size: 12px; color: #64748B; margin: 2px 0 0;">Liste des gérants, administrateurs et livreurs ayant accès à ce pressing.</p>
              </div>
              <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 13px; color: #64748B;"><?= count($pressingUsers) ?> utilisateur(s)</span>
                <button type="button" class="btn btn-primary btn-sm" onclick="openModalAddTeamMember()" style="display: inline-flex; align-items: center; gap: 6px;">
                  <i data-lucide="user-plus" style="width: 15px; height: 15px;"></i> Nouveau Membre
                </button>
              </div>
            </div>

            <?php if (empty($pressingUsers)): ?>
              <div style="text-align: center; padding: 40px 16px; color: #94A3B8;">
                <i data-lucide="user-check" style="width: 48px; height: 48px; margin-bottom: 12px; stroke-width: 1.5;"></i>
                <p style="font-size: 14px; font-weight: 600; margin: 0;">Aucun utilisateur spécifique rattaché à ce pressing pour le moment.</p>
              </div>
            <?php else: ?>
              <div class="table-responsive-mobile">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                  <thead>
                    <tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; text-align: left; font-size: 12px; color: #64748B;">
                      <th style="padding: 12px 16px;">Utilisateur / Gérant</th>
                      <th style="padding: 12px 16px;">Email (Login Admin)</th>
                      <th style="padding: 12px 16px;">Téléphone Contact</th>
                      <th style="padding: 12px 16px;">Rôle / Fonction</th>
                      <th style="padding: 12px 16px;">Statut</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($pressingUsers as $u): ?>
                      <tr style="border-bottom: 1px solid #F1F5F9; font-size: 13px;">
                        <td style="padding: 12px 16px;">
                          <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: #2563EB; color: #FFF; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px;">
                              <?= strtoupper(substr($u['nom_user'] ?? 'U', 0, 1)) ?>
                            </div>
                            <div>
                              <strong style="color: #1E293B; display: block;"><?= htmlspecialchars(($u['nom_user'] ?? '') . ' ' . ($u['prenom_user'] ?? '')) ?></strong>
                              <span style="font-size: 11px; color: #94A3B8;"><?= htmlspecialchars($u['code_user']) ?></span>
                            </div>
                          </div>
                        </td>
                        <td style="padding: 12px 16px; color: #475569; font-weight: 600;"><?= htmlspecialchars($u['email_user'] ?? '-') ?></td>
                        <td style="padding: 12px 16px; color: #475569;"><?= htmlspecialchars($u['telephone_user'] ?? '-') ?></td>
                        <td style="padding: 12px 16px;">
                          <span style="background: #DBEAFE; color: #1E40AF; padding: 4px 10px; border-radius: 12px; font-weight: 600; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                            <i data-lucide="shield" style="width: 12px; height: 12px;"></i> <?= htmlspecialchars($u['libelle_role'] ?? 'Gérant') ?>
                          </span>
                        </td>
                        <td style="padding: 12px 16px;">
                          <span class="badge-status <?= ($u['statut_user'] ?? 'actif') === 'actif' ? 'delivered' : 'cancelled' ?>" style="font-size: 11px;">
                            <?= htmlspecialchars($u['statut_user'] ?? 'actif') ?>
                          </span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>

          <!-- === TAB 7 : INFOS & COORDONNÉES === -->
          <div id="tab-infos" class="hub-tab-content" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h2 style="font-size: 16px; font-weight: 700; margin: 0; color: #1E293B;">Fiche détaillée de l'établissement</h2>
              <a href="<?= RACINE ?>pressing/edition/<?= $encryptedId ?>" class="btn btn-sm btn-primary" style="display: inline-flex; align-items: center; gap: 4px;">
                <i data-lucide="edit" style="width: 14px; height: 14px;"></i> Modifier
              </a>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
              <!-- CARTE 1 : GERANT / RESPONSABLE PRINCIPAL -->
              <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 14px; padding: 18px;">
                <h4 style="font-size: 14px; font-weight: 700; color: #1E40AF; margin: 0 0 14px; display: flex; align-items: center; gap: 6px;">
                  <i data-lucide="user-check" style="width: 16px; height: 16px; color: #2563EB;"></i> Responsable Principal du Pressing
                </h4>
                <?php if ($owner): ?>
                  <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: #2563EB; color: #FFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px; box-shadow: 0 2px 8px rgba(37,99,235,0.2);">
                      <?= strtoupper(substr($owner['nom_user'] ?? 'G', 0, 1)) ?>
                    </div>
                    <div>
                      <div style="font-weight: 800; color: #1E293B; font-size: 15px;"><?= htmlspecialchars(($owner['nom_user'] ?? '') . ' ' . ($owner['prenom_user'] ?? '')) ?></div>
                      <span style="font-size: 11px; background: #DBEAFE; color: #1E40AF; padding: 2px 8px; border-radius: 12px; font-weight: 700;"><?= htmlspecialchars($owner['libelle_role'] ?? 'Gérant / Propriétaire') ?></span>
                    </div>
                  </div>
                  <div style="display: flex; flex-direction: column; gap: 8px; font-size: 13px; background: #FFFFFF; padding: 12px; border-radius: 10px; border: 1px solid #DBEAFE;">
                    <div><span style="color: #64748B;">Email Login :</span> <strong style="color: #1E293B;"><?= htmlspecialchars($owner['email_user'] ?? '-') ?></strong></div>
                    <div><span style="color: #64748B;">Téléphone Mobile :</span> <strong style="color: #1E293B;"><?= htmlspecialchars($owner['telephone_user'] ?? '-') ?></strong></div>
                    <div><span style="color: #64748B;">Code Gérant :</span> <strong style="color: #2563EB;"><?= htmlspecialchars($owner['code_user'] ?? '-') ?></strong></div>
                  </div>
                <?php else: ?>
                  <p style="font-size: 13px; color: #64748B; margin: 0;">Aucun responsable rattaché directement.</p>
                <?php endif; ?>
              </div>

              <!-- CARTE 2 : COORDONNEES -->
              <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 14px; padding: 18px;">
                <h4 style="font-size: 14px; font-weight: 700; color: #1E293B; margin: 0 0 14px; display: flex; align-items: center; gap: 6px;">
                  <i data-lucide="store" style="width: 16px; height: 16px; color: #64748B;"></i> Coordonnées de l'Atelier
                </h4>
                <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                  <div><span style="color: #64748B;">Téléphone Atelier :</span> <strong><?= htmlspecialchars($pressing['telephone_pressing'] ?? '-') ?></strong></div>
                  <div><span style="color: #64748B;">Email Officiel :</span> <strong><?= htmlspecialchars($pressing['email_pressing'] ?? '-') ?></strong></div>
                  <div><span style="color: #64748B;">Adresse Physique :</span> <strong><?= htmlspecialchars($pressing['adresse_pressing'] ?? '-') ?></strong></div>
                  <div><span style="color: #64748B;">Ville / Quartier :</span> <strong><?= htmlspecialchars($pressing['ville_code'] ?? '-') ?> / <?= htmlspecialchars($pressing['quartier_code'] ?? '-') ?></strong></div>
                </div>
              </div>

              <!-- CARTE 3 : OPTIONS MARKETPLACE -->
              <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 14px; padding: 18px;">
                <h4 style="font-size: 14px; font-weight: 700; color: #1E293B; margin: 0 0 14px; display: flex; align-items: center; gap: 6px;">
                  <i data-lucide="settings" style="width: 16px; height: 16px; color: #64748B;"></i> Configuration Marketplace Client
                </h4>
                <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                  <div><span style="color: #64748B;">Livraison Gratuite :</span> <strong><?= !empty($pressing['livraison_gratuite']) ? 'Oui' : 'Non' ?></strong></div>
                  <div><span style="color: #64748B;">Collecte au Sac (Colis) :</span> <strong><?= !empty($pressing['accepte_colis_sans_detail']) ? 'Oui' : 'Non' ?></strong></div>
                  <div><span style="color: #64748B;">Option Express :</span> <strong><?= !empty($pressing['has_express']) ? 'Oui' : 'Non' ?></strong></div>
                  <div><span style="color: #64748B;">Note Client Moyenne :</span> <strong style="color: #D97706;">★ <?= number_format((float)($pressing['rating'] ?? 4.8), 1) ?> / 5</strong></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- === MODAL : AJOUT D'UN MEMBRE D'ÉQUIPE (PROPRIÉTAIRE, GESTIONNAIRE OU LIVREUR) === -->
<div class="modal fade" id="modalAddTeamMember" tabindex="-1" aria-hidden="true" style="display: none;">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 12px 36px rgba(0,0,0,0.18);">
      <div class="modal-header" style="border-bottom: 1px solid #E2E8F0; padding: 20px 24px; background: #F8FAFC; border-radius: 16px 16px 0 0;">
        <h5 class="modal-title" style="font-weight: 800; font-size: 18px; color: #1E293B; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="user-plus" style="color: #2563EB;"></i> Nouveau Membre de l'Équipe
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formAddTeamMember" style="width: 100%;">
        <input type="hidden" name="pressing_code" value="<?= htmlspecialchars($codePressing) ?>">
        <div class="modal-body" style="padding: 24px;">
          
          <div id="modalTeamAlert" style="display:none; padding: 12px 16px; border-radius: 10px; background: #FEF2F2; border: 1px solid #FCA5A5; color: #991B1B; font-weight: 700; font-size: 13px; margin-bottom: 16px; align-items: center; gap: 8px;"></div>
          
          <!-- CHOIX DU ROLE (3 COLONNES) -->
          <div style="margin-bottom: 24px;">
            <label style="font-weight: 700; font-size: 14px; color: #1E293B; margin-bottom: 10px; display: block;">Rôle attribué au membre dans ce Pressing</label>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
              
              <label style="border: 2px solid #2563EB; background: #EFF6FF; border-radius: 12px; padding: 14px 10px; cursor: pointer; text-align: center; transition: all 0.2s;" class="role-card-option" id="role-card-pro">
                <input type="radio" name="role_code" value="ROLE-PRO" checked style="display: none;" onchange="selectTeamRole('ROLE-PRO')">
                <div style="font-size: 20px; color: #2563EB; margin-bottom: 4px;"><i data-lucide="crown"></i></div>
                <div style="font-weight: 800; font-size: 13px; color: #1E40AF;">Propriétaire</div>
                <div style="font-size: 11px; color: #64748B;">Propriétaire de l'enseigne</div>
              </label>

              <label style="border: 2px solid #E2E8F0; background: #FFF; border-radius: 12px; padding: 14px 10px; cursor: pointer; text-align: center; transition: all 0.2s;" class="role-card-option" id="role-card-gest">
                <input type="radio" name="role_code" value="ROLE-GEST" style="display: none;" onchange="selectTeamRole('ROLE-GEST')">
                <div style="font-size: 20px; color: #64748B; margin-bottom: 4px;"><i data-lucide="briefcase"></i></div>
                <div style="font-weight: 800; font-size: 13px; color: #1E293B;">Gestionnaire</div>
                <div style="font-size: 11px; color: #64748B;">Gérant d'atelier au quotidien</div>
              </label>

              <label style="border: 2px solid #E2E8F0; background: #FFF; border-radius: 12px; padding: 14px 10px; cursor: pointer; text-align: center; transition: all 0.2s;" class="role-card-option" id="role-card-liv">
                <input type="radio" name="role_code" value="ROLE-LIV" style="display: none;" onchange="selectTeamRole('ROLE-LIV')">
                <div style="font-size: 20px; color: #64748B; margin-bottom: 4px;"><i data-lucide="truck"></i></div>
                <div style="font-weight: 800; font-size: 13px; color: #1E293B;">Livreur</div>
                <div style="font-size: 11px; color: #64748B;">Collecte & Livraison terrain</div>
              </label>

            </div>
          </div>

          <!-- FORM GRID FULL WIDTH -->
          <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; width: 100%;">
            
            <div style="display: flex; flex-direction: column; gap: 6px;">
              <label for="team_nom" style="font-weight: 700; font-size: 13px; color: #334155;">Nom du membre *</label>
              <input type="text" class="form-control" id="team_nom" name="nom_user" placeholder="Ex: Kouassi" required style="border-radius: 10px; height: 44px; padding: 0 14px; width: 100%;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
              <label for="team_prenom" style="font-weight: 700; font-size: 13px; color: #334155;">Prénom(s)</label>
              <input type="text" class="form-control" id="team_prenom" name="prenom_user" placeholder="Ex: Jean-Marc" style="border-radius: 10px; height: 44px; padding: 0 14px; width: 100%;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
              <label for="team_phone" style="font-weight: 700; font-size: 13px; color: #334155;">Téléphone (Login de connexion) *</label>
              <input type="tel" class="form-control" id="team_phone" name="telephone_user" placeholder="Ex: 0708091011" maxlength="10" required style="border-radius: 10px; height: 44px; padding: 0 14px; width: 100%;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
              <label for="team_email" style="font-weight: 700; font-size: 13px; color: #334155;">Email (Optionnel)</label>
              <input type="email" class="form-control" id="team_email" name="email_user" placeholder="Ex: agent@lavex.ci" style="border-radius: 10px; height: 44px; padding: 0 14px; width: 100%;">
            </div>

            <div style="grid-column: span 2; display: flex; flex-direction: column; gap: 6px;">
              <label for="team_password" style="font-weight: 700; font-size: 13px; color: #334155;">Mot de passe par défaut</label>
              <input type="text" class="form-control" id="team_password" name="password_user" value="12345" readonly style="border-radius: 10px; height: 44px; padding: 0 14px; width: 100%; background-color: #F1F5F9; color: #475569; font-weight: 700; cursor: not-allowed;">
            </div>

          </div>

        </div>
        <div class="modal-footer" style="border-top: 1px solid #E2E8F0; padding: 16px 24px; background: #F8FAFC; border-radius: 0 0 16px 16px;">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px; padding: 10px 20px;">Annuler</button>
          <button type="submit" class="btn btn-primary btn_submit_team" style="border-radius: 10px; padding: 10px 20px; font-weight: 700;">
            <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i> Enregistrer le Membre
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function switchHubTab(btn, tabId) {
  document.querySelectorAll('.hub-tab-btn').forEach(b => {
    b.classList.remove('active');
    b.style.color = '#64748B';
    b.style.borderBottom = '2px solid transparent';
  });
  document.querySelectorAll('.hub-tab-content').forEach(c => {
    c.style.display = 'none';
  });

  btn.classList.add('active');
  btn.style.color = 'var(--primary-color, #1E3A5F)';
  btn.style.borderBottom = '2px solid var(--primary-color, #1E3A5F)';

  const target = document.getElementById(tabId);
  if (target) {
    target.style.display = 'block';
  }
}

function openModalAddTeamMember() {
  $('#formAddTeamMember')[0].reset();
  $('#team_password').val('12345');
  $('#modalTeamAlert').hide().html('');
  $('#teamPhoneError').html('');
  $('#formAddTeamMember button[type="submit"]').prop('disabled', false);
  selectTeamRole('ROLE-PRO');
  var myModal = new bootstrap.Modal(document.getElementById('modalAddTeamMember'));
  myModal.show();
  if (window.lucide) window.lucide.createIcons();
}

function selectTeamRole(role) {
  $('.role-card-option').css({'border-color': '#E2E8F0', 'background': '#FFF'});
  if (role === 'ROLE-PRO') {
    $('#role-card-pro').css({'border-color': '#2563EB', 'background': '#EFF6FF'});
    $('input[name="role_code"][value="ROLE-PRO"]').prop('checked', true);
  } else if (role === 'ROLE-GEST') {
    $('#role-card-gest').css({'border-color': '#2563EB', 'background': '#EFF6FF'});
    $('input[name="role_code"][value="ROLE-GEST"]').prop('checked', true);
  } else {
    $('#role-card-liv').css({'border-color': '#2563EB', 'background': '#EFF6FF'});
    $('input[name="role_code"][value="ROLE-LIV"]').prop('checked', true);
  }
}

$(document).ready(function() {
  $('#team_phone').on('input blur', function() {
    const val = $(this).val().trim();
    let errDiv = $('#teamPhoneError');
    if (!errDiv.length) {
      $(this).after('<div id="teamPhoneError" style="font-size: 12px; margin-top: 4px; font-weight: 600;"></div>');
      errDiv = $('#teamPhoneError');
    }
    if (val.length === 10) {
      $.post(LINK + 'user/checkPhone', { telephone: val }, function(rep) {
        if (!rep.status) {
          errDiv.css('color', '#EF4444').html('<i class="fa fa-exclamation-triangle"></i> ' + rep.message);
          $('#formAddTeamMember button[type="submit"]').prop('disabled', true);
          $('#modalTeamAlert').css('display', 'flex').html('<i class="fa fa-exclamation-circle"></i> ' + rep.message);
        } else {
          errDiv.css('color', '#10B981').html('<i class="fa fa-check-circle"></i> Numéro disponible');
          $('#formAddTeamMember button[type="submit"]').prop('disabled', false);
          $('#modalTeamAlert').hide().html('');
        }
      }, 'json');
    } else {
      errDiv.html('');
      $('#formAddTeamMember button[type="submit"]').prop('disabled', false);
    }
  });

  $('#formAddTeamMember').on('submit', function(e) {
    e.preventDefault();
    $('#modalTeamAlert').hide().html('');
    const form = $(this);
    const btn = form.find('.btn_submit_team');
    const btnHtml = btn.html();

    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enregistrement...');

    $.ajax({
      url: LINK + 'pressing/addUser',
      type: 'POST',
      data: form.serialize(),
      dataType: 'json',
      success: function(rep) {
        btn.prop('disabled', false).html(btnHtml);
        if (rep.status) {
          showToast(rep.message, 'success');
          var mEl = document.getElementById('modalAddTeamMember');
          var mObj = bootstrap.Modal.getInstance(mEl);
          if (mObj) mObj.hide();
          setTimeout(() => window.location.reload(), 700);
        } else {
          $('#modalTeamAlert').css('display', 'flex').html('<i class="fa fa-exclamation-circle"></i> ' + rep.message);
          showToast(rep.message, 'error');
        }
      },
      error: function(xhr) {
        btn.prop('disabled', false).html(btnHtml);
        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Erreur lors de l\'enregistrement';
        $('#modalTeamAlert').css('display', 'flex').html('<i class="fa fa-exclamation-circle"></i> ' + msg);
        showToast(msg, 'error');
      }
    });
  });
});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
