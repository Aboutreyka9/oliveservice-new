<?php
require_once __DIR__ . '/../../public/inc/header.php';

$stats = $stats ?? [];
$auth = $auth ?? ($_SESSION[USERS_AUTH] ?? []);
$recentCotisations = $recentCotisations ?? [];
$recentVersements = $recentVersements ?? [];
$recentDepenses = $recentDepenses ?? [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <!-- HEADER DU TABLEAU DE BORD OLIVE SERVICE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="layout-dashboard" style="color: #1E3A5F; width: 26px; height: 26px;"></i> 
            <span>Tableau de Bord &bull; Olive Service</span>
          </h1>
          <p class="page-subtitle" style="color: #64748B; margin: 4px 0 0 0; font-size: 13px;">
            Synthèse globale des souscriptions, cotisations terrain, versements commerciaux et dépenses. Bienvenue, <strong><?= htmlspecialchars($auth['nom_user'] ?? 'Utilisateur') ?></strong>
          </p>
        </div>

        <!-- ACTIONS RAPIDES -->
        <div class="page-header-actions" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
          <a href="<?= RACINE ?>souscription/formulaire" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; background: #1E3A5F; border-color: #1E3A5F; color: #FFFFFF; padding: 10px 16px; border-radius: 8px; text-decoration: none;">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Nouvelle Souscription
          </a>
          <a href="<?= RACINE ?>versement/formulaire" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; background: #059669; border-color: #059669; color: #FFFFFF; padding: 10px 16px; border-radius: 8px; text-decoration: none;">
            <i data-lucide="arrow-down-left" style="width: 16px; height: 16px;"></i> Saisir Versement Commercial
          </a>
          <a href="<?= RACINE ?>depense/formulaire" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; background: #FFFFFF; border: 1px solid #DC2626; color: #DC2626; padding: 10px 16px; border-radius: 8px; text-decoration: none;">
            <i data-lucide="arrow-up-right" style="width: 16px; height: 16px;"></i> Saisir Dépense
          </a>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- SECTION 1 : KPI GRID ADAPTÉE STRICTEMENT À OLIVE SERVICE                  -->
      <!-- ========================================================================= -->
      
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- KPI 1 : Clients -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Clients Enregistrés</span>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="users" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div style="font-size: 26px; font-weight: 800; color: #0F172A; line-height: 1;">
            <?= number_format($stats['total_clients'] ?? 0, 0, ',', ' ') ?>
          </div>
          <div style="font-size: 12px; color: #64748B; margin-top: 6px;">
            <?= (int)($stats['total_souscriptions'] ?? 0) ?> Souscriptions actives
          </div>
        </div>

        <!-- KPI 2 : Packs Produit -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Packs & Articles</span>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #FAF5FF; color: #7E22CE; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="package" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div style="font-size: 26px; font-weight: 800; color: #7E22CE; line-height: 1;">
            <?= number_format($stats['total_packs'] ?? 0, 0, ',', ' ') ?> <span style="font-size: 13px; font-weight: 600;">Packs</span>
          </div>
          <div style="font-size: 12px; color: #64748B; margin-top: 6px;">
            <?= (int)($stats['total_articles'] ?? 0) ?> Articles au catalogue
          </div>
        </div>

        <!-- KPI 3 : Cotisations Encaissées -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Cotisations Encaissées</span>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #ECFDF5; color: #047857; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="coins" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div style="font-size: 22px; font-weight: 800; color: #047857; line-height: 1;">
            <?= number_format($stats['total_cotisations'] ?? 0, 0, ',', ' ') ?> <span style="font-size: 12px;">FCFA</span>
          </div>
          <div style="font-size: 12px; color: #64748B; margin-top: 6px;">
            Collecte terrain clients
          </div>
        </div>

        <!-- KPI 4 : Versements Validés -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Versements Validés</span>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #F0FDF4; color: #16A34A; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="check-circle-2" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div style="font-size: 22px; font-weight: 800; color: #16A34A; line-height: 1;">
            <?= number_format($stats['total_versements'] ?? 0, 0, ',', ' ') ?> <span style="font-size: 12px;">FCFA</span>
          </div>
          <div style="font-size: 12px; color: #64748B; margin-top: 6px;">
            Versements commerciaux validés
          </div>
          <?php if (!empty($stats['total_versements_en_attente'])): ?>
          <div style="font-size: 11px; color: #D97706; margin-top: 4px; font-weight: 600;">
            <?= number_format($stats['total_versements_en_attente'], 0, ',', ' ') ?> FCFA en attente
          </div>
          <?php endif; ?>
        </div>

        <!-- KPI 5 : Souscriptions Soldées -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Souscriptions Soldées</span>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #ECFDF5; color: #047857; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="circle-check-big" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div style="font-size: 22px; font-weight: 800; color: #047857; line-height: 1;">
            <?= number_format($stats['total_souscriptions_soldees'] ?? 0, 0, ',', ' ') ?> <span style="font-size: 12px;">/ <?= number_format($stats['total_souscriptions'] ?? 0, 0, ',', ' ') ?></span>
          </div>
          <div style="font-size: 12px; color: #64748B; margin-top: 6px;">
            Contrats prêts pour distribution
          </div>
        </div>

        <!-- KPI 6 : Distributions -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Distributions</span>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #FAF5FF; color: #7E22CE; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="package-check" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div style="font-size: 22px; font-weight: 800; color: #7E22CE; line-height: 1;">
            <?= number_format($stats['total_distributions_validees'] ?? 0, 0, ',', ' ') ?> <span style="font-size: 12px;">/ <?= number_format($stats['total_distributions'] ?? 0, 0, ',', ' ') ?></span>
          </div>
          <div style="font-size: 12px; color: #64748B; margin-top: 6px;">
            Packs livrés aux clients
          </div>
        </div>

        <!-- KPI 7 : Dépenses Engagées -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Dépenses & Charges</span>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #FEF2F2; color: #DC2626; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="arrow-up-right" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div style="font-size: 22px; font-weight: 800; color: #DC2626; line-height: 1;">
            <?= number_format($stats['total_depenses'] ?? 0, 0, ',', ' ') ?> <span style="font-size: 12px;">FCFA</span>
          </div>
          <div style="font-size: 12px; color: #64748B; margin-top: 6px;">
            Charges d'exploitation
          </div>
        </div>

        <!-- KPI 8 : Solde Net -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Solde Net Trésorerie</span>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #F8FAFC; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="scale" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div style="font-size: 22px; font-weight: 800; color: <?= ($stats['solde_net'] ?? 0) >= 0 ? '#15803D' : '#DC2626' ?>; line-height: 1;">
            <?= number_format($stats['solde_net'] ?? 0, 0, ',', ' ') ?> <span style="font-size: 12px;">FCFA</span>
          </div>
          <div style="font-size: 12px; color: #64748B; margin-top: 6px;">
            Recettes Nettes &minus; Dépenses
          </div>
        </div>

      </div>

      <!-- ========================================================================= -->
      <!-- SECTION 2 : RACCOURCIS D'ACTIONS METIERS                                 -->
      <!-- ========================================================================= -->
      <div style="margin-bottom: 24px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 14px 0; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="zap" style="color: #1E3A5F; width: 18px; height: 18px;"></i> Accès Rapide aux Modules
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
          <a href="<?= RACINE ?>client/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center; font-size: 18px;">
              <i data-lucide="users"></i>
            </div>
            <div>
              <strong style="color: #1E293B; font-size: 13px; display: block;">Gestion des Clients</strong>
              <small style="color: #64748B;">Repertoire & souscriptions</small>
            </div>
          </a>

          <a href="<?= RACINE ?>souscription/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #ECFDF5; color: #047857; display: flex; align-items: center; justify-content: center; font-size: 18px;">
              <i data-lucide="file-text"></i>
            </div>
            <div>
              <strong style="color: #1E293B; font-size: 13px; display: block;">Souscriptions Packs</strong>
              <small style="color: #64748B;">Suivi des engagements</small>
            </div>
          </a>

          <a href="<?= RACINE ?>cotisation/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #FFFBEB; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 18px;">
              <i data-lucide="coins"></i>
            </div>
            <div>
              <strong style="color: #1E293B; font-size: 13px; display: block;">Cotisations Terrain</strong>
              <small style="color: #64748B;">Paiements quotidiens</small>
            </div>
          </a>

          <a href="<?= RACINE ?>versement/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #F0FDF4; color: #16A34A; display: flex; align-items: center; justify-content: center; font-size: 18px;">
              <i data-lucide="arrow-down-left"></i>
            </div>
            <div>
              <strong style="color: #1E293B; font-size: 13px; display: block;">Versements Commerciaux</strong>
              <small style="color: #64748B;">Validations de caisse</small>
            </div>
          </a>

          <a href="<?= RACINE ?>depense/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #FEF2F2; color: #DC2626; display: flex; align-items: center; justify-content: center; font-size: 18px;">
              <i data-lucide="arrow-up-right"></i>
            </div>
            <div>
              <strong style="color: #1E293B; font-size: 13px; display: block;">Dépenses d'Exploitation</strong>
              <small style="color: #64748B;">Charges & décaissements</small>
            </div>
          </a>

          <a href="<?= RACINE ?>distribution/list" class="card" style="margin: 0; padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; border-radius: 12px; border: 1px solid #E2E8F0;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #FAF5FF; color: #7E22CE; display: flex; align-items: center; justify-content: center; font-size: 18px;">
              <i data-lucide="truck"></i>
            </div>
            <div>
              <strong style="color: #1E293B; font-size: 13px; display: block;">Distributions Articles</strong>
              <small style="color: #64748B;">Remises des packs aux clients</small>
            </div>
          </a>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- SECTION 3 : TABLEAUX RÉCAPITULATIFS RÉCENTS                               -->
      <!-- ========================================================================= -->
      
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 20px;">
        
        <!-- TABLEAU 1 : Dernières Cotisations Clients -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #F1F5F9; padding-bottom: 12px;">
            <h3 style="font-size: 15px; font-weight: 700; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="coins" style="width: 18px; height: 18px; color: #D97706;"></i> Dernières Cotisations Clients
            </h3>
            <a href="<?= RACINE ?>cotisation/list" style="font-size: 12px; font-weight: 600; color: #D97706; text-decoration: none;">Voir tout</a>
          </div>

          <div style="overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 1px solid #E2E8F0;">
                  <th style="padding: 10px 12px;">Code Cotisation</th>
                  <th style="padding: 10px 12px;">Client</th>
                  <th style="padding: 10px 12px; text-align: right;">Montant</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($recentCotisations)): ?>
                  <tr>
                    <td colspan="3" style="padding: 16px; text-align: center; color: #94A3B8;">Aucune cotisation récente</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($recentCotisations as $c): ?>
                    <tr style="border-bottom: 1px solid #F1F5F9;">
                      <td style="padding: 10px 12px; font-weight: 700; color: #D97706; font-family: monospace;">
                        <?= htmlspecialchars($c['code_cautisation_client'] ?? '-') ?>
                      </td>
                      <td style="padding: 10px 12px; font-weight: 600; color: #0F172A;"><?= htmlspecialchars($c['nom_client'] ?? '-') ?></td>
                      <td style="padding: 10px 12px; text-align: right; font-weight: 700; color: #059669;"><?= number_format((float)($c['montant_cautisation_client'] ?? 0), 0, ',', ' ') ?> FCFA</td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- TABLEAU 2 : Derniers Versements Commerciaux -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #F1F5F9; padding-bottom: 12px;">
            <h3 style="font-size: 15px; font-weight: 700; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="arrow-down-left" style="width: 18px; height: 18px; color: #059669;"></i> Derniers Versements Commerciaux
            </h3>
            <a href="<?= RACINE ?>versement/list" style="font-size: 12px; font-weight: 600; color: #059669; text-decoration: none;">Voir tout</a>
          </div>

          <div style="overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 1px solid #E2E8F0;">
                  <th style="padding: 10px 12px;">Commercial</th>
                  <th style="padding: 10px 12px;">Zone</th>
                  <th style="padding: 10px 12px; text-align: right;">Montant</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($recentVersements)): ?>
                  <tr>
                    <td colspan="3" style="padding: 16px; text-align: center; color: #94A3B8;">Aucun versement commercial enregistré</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($recentVersements as $v): ?>
                    <tr style="border-bottom: 1px solid #F1F5F9;">
                      <td style="padding: 10px 12px; font-weight: 600; color: #0F172A;">
                        <?= htmlspecialchars(trim(($v['nom_commercial'] ?? '') . ' ' . ($v['prenom_commercial'] ?? ''))) ?>
                      </td>
                      <td style="padding: 10px 12px; color: #64748B;"><?= htmlspecialchars($v['libelle_zone'] ?? 'Non spécifiée') ?></td>
                      <td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #047857;"><?= number_format((float)($v['montant_versement'] ?? 0), 0, ',', ' ') ?> FCFA</td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- TABLEAU 3 : Dernières Dépenses Engagées -->
        <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #F1F5F9; padding-bottom: 12px;">
            <h3 style="font-size: 15px; font-weight: 700; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="arrow-up-right" style="width: 18px; height: 18px; color: #DC2626;"></i> Dernières Dépenses Engagées
            </h3>
            <a href="<?= RACINE ?>depense/list" style="font-size: 12px; font-weight: 600; color: #DC2626; text-decoration: none;">Voir tout</a>
          </div>

          <div style="overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 1px solid #E2E8F0;">
                  <th style="padding: 10px 12px;">Code Dépense</th>
                  <th style="padding: 10px 12px;">Type / Motif</th>
                  <th style="padding: 10px 12px; text-align: right;">Montant</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($recentDepenses)): ?>
                  <tr>
                    <td colspan="3" style="padding: 16px; text-align: center; color: #94A3B8;">Aucune dépense enregistrée</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($recentDepenses as $dep): ?>
                    <tr style="border-bottom: 1px solid #F1F5F9;">
                      <td style="padding: 10px 12px; font-weight: 700; color: #DC2626; font-family: monospace;">
                        <?= htmlspecialchars($dep['code_depense'] ?? '-') ?>
                      </td>
                      <td style="padding: 10px 12px; font-weight: 600; color: #0F172A;"><?= htmlspecialchars($dep['libelle_type_depense'] ?? ($dep['description_depense'] ?? 'Charge générale')) ?></td>
                      <td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #DC2626;"><?= number_format((float)($dep['montant_depense'] ?? 0), 0, ',', ' ') ?> FCFA</td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>

    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) {
    lucide.createIcons();
  }
});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
