<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">

      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="file-text" style="color: #1E3A5F; width: 26px; height: 26px;"></i>
            <span>Souscriptions Clients</span>
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Contrats d'abonnement packs et suivi des cotisations terrain</p>
        </div>
        <div style="display: flex; gap: 10px;">
          <a href="<?= RACINE ?>souscription/wizard" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 2px 6px rgba(30,58,95,0.25);">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle Souscription
          </a>
        </div>
      </div>

      <!-- GRILLE DES KPIS / STATISTIQUES -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 20px; margin-bottom: 24px;">

        <!-- Card 1: Total Souscriptions -->
        <div class="kpi-card-modern" style="background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
            <div>
              <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Souscriptions Totales</span>
              <h3 style="font-size: 24px; font-weight: 800; color: #0F172A; margin: 4px 0 0 0;"><?= number_format($stats['total_souscriptions'] ?? 0, 0, ',', ' ') ?></h3>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); display: flex; align-items: center; justify-content: center; color: #FFF;">
              <i data-lucide="file-text" style="width: 22px; height: 22px;"></i>
            </div>
          </div>
          <div style="display: flex; gap: 8px; font-size: 11px; font-weight: 700;">
            <span style="background: #EFF6FF; color: #1E3A5F; padding: 3px 10px; border-radius: 12px; border: 1px solid #BFDBFE;">En cours: <?= $stats['total_encours_count'] ?? 0 ?></span>
            <span style="background: #DCFCE7; color: #15803D; padding: 3px 10px; border-radius: 12px; border: 1px solid #BBF7D0;">Soldées: <?= $stats['total_solde_count'] ?? 0 ?></span>
          </div>
        </div>

        <!-- Card 2: Total Engagé -->
        <div class="kpi-card-modern" style="background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
            <div>
              <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Montant Total Engagé</span>
              <h3 style="font-size: 22px; font-weight: 800; color: #1E3A5F; margin: 4px 0 0 0;"><?= number_format($stats['total_engage'] ?? 0, 0, ',', ' ') ?> <small style="font-size: 12px;">FCFA</small></h3>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%); display: flex; align-items: center; justify-content: center; color: #FFF;">
              <i data-lucide="trending-up" style="width: 22px; height: 22px;"></i>
            </div>
          </div>
          <span style="color: #64748B; font-size: 12px; font-weight: 600;">Cumul contractuel global</span>
        </div>

        <!-- Card 3: Total Recouvré -->
        <div class="kpi-card-modern" style="background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
            <div>
              <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Total Recouvré</span>
              <h3 style="font-size: 22px; font-weight: 800; color: #15803D; margin: 4px 0 0 0;"><?= number_format($stats['total_cotise'] ?? 0, 0, ',', ' ') ?> <small style="font-size: 12px;">FCFA</small></h3>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: linear-gradient(135deg, #10B981 0%, #047857 100%); display: flex; align-items: center; justify-content: center; color: #FFF;">
              <i data-lucide="wallet" style="width: 22px; height: 22px;"></i>
            </div>
          </div>
          <div style="display: flex; align-items: center; gap: 8px;">
            <div style="flex: 1; height: 6px; background: #E2E8F0; border-radius: 3px; overflow: hidden;">
              <div style="width: <?= min(100, $stats['taux_recouvrement'] ?? 0) ?>%; background: #10B981; height: 100%;"></div>
            </div>
            <span style="font-size: 12px; font-weight: 800; color: #15803D;"><?= $stats['taux_recouvrement'] ?? 0 ?>%</span>
          </div>
        </div>

        <!-- Card 4: Reste à Recouvrer -->
        <div class="kpi-card-modern" style="background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
            <div>
              <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Reste à Recouvrer</span>
              <h3 style="font-size: 22px; font-weight: 800; color: #DC2626; margin: 4px 0 0 0;"><?= number_format($stats['reste_a_recouvrer'] ?? 0, 0, ',', ' ') ?> <small style="font-size: 12px;">FCFA</small></h3>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: linear-gradient(135deg, #EF4444 0%, #B91C1C 100%); display: flex; align-items: center; justify-content: center; color: #FFF;">
              <i data-lucide="clock" style="width: 22px; height: 22px;"></i>
            </div>
          </div>
          <span style="color: #DC2626; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
            <i data-lucide="alert-circle" style="width: 14px; height: 14px;"></i> Encaissements en attente
          </span>
        </div>

      </div>

      <!-- BARRE DE FILTRES RAPIDES -->
      <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; background: #FFFFFF; border-radius: 12px; padding: 14px 20px; border: 1px solid #E2E8F0;">
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
          <span style="font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; margin-right: 8px;">Filtrer par :</span>
          <button type="button" class="btn btn-sm btn-filter-status active" data-status="all" style="background: #1E3A5F; color: #FFFFFF; font-weight: 700; border-radius: 20px; padding: 6px 14px; border: none; font-size: 12px;">
            Toutes les souscriptions
          </button>
          <button type="button" class="btn btn-sm btn-filter-status" data-status="Validée" style="background: #F1F5F9; color: #64748B; font-weight: 700; border-radius: 20px; padding: 6px 14px; border: none; font-size: 12px;">
            En cours / Validées
          </button>
          <button type="button" class="btn btn-sm btn-filter-status" data-status="Soldée" style="background: #F1F5F9; color: #64748B; font-weight: 700; border-radius: 20px; padding: 6px 14px; border: none; font-size: 12px;">
            Soldées
          </button>
          <button type="button" class="btn btn-sm btn-filter-status" data-status="Reconduite" style="background: #F1F5F9; color: #64748B; font-weight: 700; border-radius: 20px; padding: 6px 14px; border: none; font-size: 12px;">
            Reconduites
          </button>
        </div>
      </div>

      <!-- CARTE TABLEAU PRINCIPAL -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-souscriptions" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px;">Code Souscription</th>
                <th style="padding: 12px; text-align: center;">Statut</th>
                <th style="padding: 12px;">Client</th>
                <th style="padding: 12px;">Session</th>
                <th style="padding: 12px;">Cotis. / Jour</th>
                <th style="padding: 12px; text-align: right;">Total Souscription</th>
                <th style="padding: 12px;">Progression</th>
                <th style="padding: 12px; text-align: right;">Total Cotisé</th>
                <th style="padding: 12px; text-align: right;">Reste à Payer</th>
                <th style="padding: 12px;">Date</th>
                <th style="padding: 12px; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>
<script src="<?= RACINE ?>public/assets/js/modules/souscriptions.js?v=1.1"></script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
