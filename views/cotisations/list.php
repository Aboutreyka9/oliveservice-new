<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<style>
@media print {
  @page {
    size: A4 landscape;
    margin: 10mm;
  }
  body, html {
    background: #FFFFFF !important;
    color: #000000 !important;
    width: 100% !important;
    height: auto !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: visible !important;
  }
  .sidebar,
  .topbar,
  .page-header-actions,
  .dataTables_length,
  .dataTables_filter,
  .dataTables_info,
  .dataTables_paginate,
  .btn,
  #table-cotisations th:last-child,
  #table-cotisations td:last-child,
  .no-print {
    display: none !important;
  }
  .app-layout,
  .main-content,
  .content-wrapper,
  .card-premium,
  div {
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    box-shadow: none !important;
    background: transparent !important;
    width: 100% !important;
    max-width: 100% !important;
    overflow: visible !important;
  }
  .page-header {
    margin-bottom: 16px !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
  }
  table#table-cotisations {
    width: 100% !important;
    border-collapse: collapse !important;
    font-size: 11px !important;
  }
  table#table-cotisations th {
    background: #F1F5F9 !important;
    color: #0F172A !important;
    border: 1px solid #CBD5E1 !important;
    padding: 8px 10px !important;
    font-weight: 800 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  table#table-cotisations td {
    border: 1px solid #E2E8F0 !important;
    padding: 8px 10px !important;
    color: #0F172A !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  table#table-cotisations span,
  table#table-cotisations code {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="wallet" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Journal des Cotisations Clients
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Suivi et registre des encaissements terrain quotidiens effectués par les agents commerciaux
            </p>
          </div>
        </div>

        <div class="page-header-actions" style="display: flex; gap: 10px;">
          <button type="button" onclick="imprimerCotisations()" class="btn" style="background: #F8FAFC; border: 1px solid #CBD5E1; color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i data-lucide="printer" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Imprimer
          </button>
        </div>
      </div>

      <!-- BARRE DE FILTRE PAR PÉRIODE -->
      <div class="card-premium no-print" style="background: #FFFFFF; border-radius: 16px; padding: 20px 24px; border: 1px solid #E2E8F0; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
          
          <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 6px; color: #1E3A5F; font-weight: 800; font-size: 14px; margin-right: 8px;">
              <i data-lucide="filter" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Filtre Période :
            </div>
            
            <div style="display: flex; align-items: center; gap: 8px;">
              <label style="font-size: 12px; font-weight: 700; color: #64748B;">Du</label>
              <input type="date" id="filter-date-debut" class="form-control" style="padding: 8px 12px; font-size: 13px; font-weight: 600; border-radius: 8px; border: 1px solid #CBD5E1; background: #F8FAFC; color: #0F172A; outline: none;" value="<?= htmlspecialchars($_GET['date_debut'] ?? '') ?>">
            </div>

            <div style="display: flex; align-items: center; gap: 8px;">
              <label style="font-size: 12px; font-weight: 700; color: #64748B;">Au</label>
              <input type="date" id="filter-date-fin" class="form-control" style="padding: 8px 12px; font-size: 13px; font-weight: 600; border-radius: 8px; border: 1px solid #CBD5E1; background: #F8FAFC; color: #0F172A; outline: none;" value="<?= htmlspecialchars($_GET['date_fin'] ?? '') ?>">
            </div>

            <button type="button" id="btn-appliquer-filtre" class="btn" style="background: #1E3A5F; color: #FFFFFF; font-weight: 800; border-radius: 8px; padding: 9px 18px; font-size: 13px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(30, 58, 95, 0.15);">
              <i data-lucide="search" style="width: 15px; height: 15px;"></i> Filtrer
            </button>

            <button type="button" id="btn-reset-filtre" class="btn" style="background: #F1F5F9; color: #475569; font-weight: 700; border-radius: 8px; padding: 9px 14px; font-size: 13px; border: 1px solid #CBD5E1; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;" title="Réinitialiser la période">
              <i data-lucide="rotate-ccw" style="width: 15px; height: 15px;"></i> Effacer
            </button>
          </div>

          <!-- RACCOURCIS RAPIDES -->
          <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
            <button type="button" class="btn btn-shortcut-date" data-range="today" style="background: #F8FAFC; color: #334155; font-weight: 700; border-radius: 8px; padding: 6px 12px; font-size: 12px; border: 1px solid #CBD5E1; cursor: pointer;">Aujourd'hui</button>
            <button type="button" class="btn btn-shortcut-date" data-range="month" style="background: #F8FAFC; color: #334155; font-weight: 700; border-radius: 8px; padding: 6px 12px; font-size: 12px; border: 1px solid #CBD5E1; cursor: pointer;">Ce Mois</button>
            <button type="button" class="btn btn-shortcut-date" data-range="year" style="background: #F8FAFC; color: #334155; font-weight: 700; border-radius: 8px; padding: 6px 12px; font-size: 12px; border: 1px solid #CBD5E1; cursor: pointer;">Cette Année</button>
          </div>

        </div>
      </div>

      <!-- CARTE ET STATISTIQUES (KPI CARDS) -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 18px; margin-bottom: 24px;" class="stats-section">
        
        <!-- KPI 1 : TOTAL ENCAISSÉ -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Total Encaissé</span>
            <div style="width: 40px; height: 40px; border-radius: 12px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(5, 150, 105, 0.15);">
              <i data-lucide="banknote" style="width: 22px; height: 22px;"></i>
            </div>
          </div>
          <div id="kpi-total-montant" style="font-size: 22px; font-weight: 900; color: #059669; line-height: 1.2;">
            <?= number_format((float)($stats['total_montant'] ?? 0), 0, ',', ' ') ?> <small style="font-size: 13px; font-weight: 700;">FCFA</small>
          </div>
          <span style="font-size: 12px; color: #64748B; font-weight: 600; margin-top: 6px; display: block;">Encaissements terrain cumulés</span>
        </div>

        <!-- KPI 2 : VOLUME DE COTISATIONS -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Total Cotisations</span>
            <div style="width: 40px; height: 40px; border-radius: 12px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(30, 58, 95, 0.15);">
              <i data-lucide="receipt" style="width: 22px; height: 22px;"></i>
            </div>
          </div>
          <div id="kpi-total-cotisations" style="font-size: 22px; font-weight: 900; color: #1E3A5F; line-height: 1.2;">
            <?= number_format((int)($stats['total_cotisations'] ?? 0), 0, ',', ' ') ?> <small style="font-size: 13px; font-weight: 700;">opér.</small>
          </div>
          <span style="font-size: 12px; color: #64748B; font-weight: 600; margin-top: 6px; display: block;">Règlements reçus au journal</span>
        </div>

        <!-- KPI 3 : JOURS RÉGULARISÉS -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Jours Régularisés</span>
            <div style="width: 40px; height: 40px; border-radius: 12px; background: #EEF2FF; color: #2563EB; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(37, 99, 235, 0.15);">
              <i data-lucide="calendar-check" style="width: 22px; height: 22px;"></i>
            </div>
          </div>
          <div id="kpi-total-jours" style="font-size: 22px; font-weight: 900; color: #2563EB; line-height: 1.2;">
            +<?= number_format((int)($stats['total_jours'] ?? 0), 0, ',', ' ') ?> <small style="font-size: 13px; font-weight: 700;">j</small>
          </div>
          <span style="font-size: 12px; color: #64748B; font-weight: 600; margin-top: 6px; display: block;">Couverture souscriptions</span>
        </div>

        <!-- KPI 4 : VALIDATION CAISSE -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Validation Caisse</span>
            <div style="width: 40px; height: 40px; border-radius: 12px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(217, 119, 6, 0.15);">
              <i data-lucide="shield-check" style="width: 22px; height: 22px;"></i>
            </div>
          </div>
          <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
            <span id="kpi-count-valide" style="background: #ECFDF5; color: #047857; font-weight: 800; font-size: 11px; padding: 4px 8px; border-radius: 10px; border: 1px solid #A7F3D0;">
              <?= (int)($stats['count_valide'] ?? 0) ?> Val.
            </span>
            <span id="kpi-count-attente" style="background: #FEF3C7; color: #B45309; font-weight: 800; font-size: 11px; padding: 4px 8px; border-radius: 10px; border: 1px solid #FDE68A;">
              <?= (int)($stats['count_attente'] ?? 0) ?> Att.
            </span>
          </div>
          <span style="font-size: 12px; color: #64748B; font-weight: 600; margin-top: 6px; display: block;">État des encaissements</span>
        </div>

      </div>

      <!-- CARTE TABLEAU PRINCIPALE (NAVY PREMIUM) -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; margin-top: 30px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-cotisations" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse; font-size: 13px;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 2px solid #E2E8F0;">
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Code / Reçu</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Date</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Client</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Code Souscription</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Commercial</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: center;">Mode</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: center;">Jours</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: right;">Montant</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>
<script src="<?= RACINE ?>public/assets/js/modules/cotisations.js?v=<?= time() ?>"></script>
<script>
function imprimerCotisations() {
  if ($.fn.DataTable.isDataTable('#table-cotisations')) {
    var dt = $('#table-cotisations').DataTable();
    var origLen = dt.page.len();
    dt.page.len(-1).draw();
    setTimeout(function() {
      window.print();
      setTimeout(function() {
        dt.page.len(origLen).draw();
      }, 500);
    }, 300);
  } else {
    window.print();
  }
}
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
