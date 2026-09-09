<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #047857 0%, #065F46 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(4, 120, 87, 0.25);">
            <i data-lucide="percent" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Mes Commissions sur Versements
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Suivi dynamique des commissions perçues sur chaque versement validé en caisse
            </p>
          </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
          <a href="<?= RACINE ?>versement/list" class="btn" style="background: #FFFFFF; color: #334155; font-weight: 700; border-radius: 10px; padding: 10px 18px; font-size: 13px; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; cursor: pointer;">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Voir Versements
          </a>
        </div>
      </div>

      <!-- CSRF TOKEN -->
      <input type="hidden" id="csrf_token" value="<?= Validator::generateCsrfToken() ?>">

      <!-- CARTS DE STATISTIQUES (KPI SUMMARY CARDS) -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- CARD 1: CUMUL COMMISSIONS GAGNÉES -->
        <div style="background: linear-gradient(135deg, #047857 0%, #064E3B 100%); border-radius: 14px; padding: 20px; color: #FFFFFF; box-shadow: 0 10px 20px -5px rgba(4, 120, 87, 0.3); position: relative; overflow: hidden;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
              <span style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85;">Commissions Gagnées</span>
              <div style="font-size: 24px; font-weight: 900; margin-top: 6px; font-family: 'Outfit', sans-serif;" id="kpi-total-commissions">0 FCFA</div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255, 255, 255, 0.15); display: flex; align-items: center; justify-content: center;">
              <i data-lucide="award" style="width: 22px; height: 22px; color: #34D399;"></i>
            </div>
          </div>
          <div style="font-size: 11px; margin-top: 10px; opacity: 0.8; font-weight: 500;">
            Total cumulé sur les versements validés
          </div>
        </div>

        <!-- CARD 2: TAUX DE COMMISSION -->
        <div style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
          <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
              <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Taux de Commission</span>
              <div style="font-size: 24px; font-weight: 900; color: #1E3A5F; margin-top: 6px;" id="kpi-taux-commission">0.00 %</div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #EFF6FF; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="percent" style="width: 20px; height: 20px; color: #2563EB;"></i>
            </div>
          </div>
          <div style="font-size: 11px; color: #64748B; margin-top: 10px; font-weight: 500;">
            Pourcentage appliqué sur le montant versé
          </div>
        </div>

        <!-- CARD 3: TOTAL VERSÉ VALIDÉ -->
        <div style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
          <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
              <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Total Versé Validé</span>
              <div style="font-size: 24px; font-weight: 900; color: #0F172A; margin-top: 6px;" id="kpi-total-versements">0 FCFA</div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #ECFDF5; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="wallet" style="width: 20px; height: 20px; color: #059669;"></i>
            </div>
          </div>
          <div style="font-size: 11px; color: #64748B; margin-top: 10px; font-weight: 500;">
            Montant total des versements comptabilisés
          </div>
        </div>

        <!-- CARD 4: NOMBRE DE VERSEMENTS VALIDÉS -->
        <div style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
          <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
              <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Versements Validés</span>
              <div style="font-size: 24px; font-weight: 900; color: #0F172A; margin-top: 6px;" id="kpi-count-versements">0</div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #F1F5F9; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="check-circle-2" style="width: 20px; height: 20px; color: #475569;"></i>
            </div>
          </div>
          <div style="font-size: 11px; color: #64748B; margin-top: 10px; font-weight: 500;">
            Nombre d'opérations de versement contrôlées
          </div>
        </div>

      </div>

      <!-- CARTE TABLEAU PRINCIPALE (NAVY PREMIUM) -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box; overflow: hidden;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #F1F5F9; padding-bottom: 14px;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <i data-lucide="list" style="width: 20px; height: 20px; color: #1E3A5F;"></i>
            <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0;">Historique du Calcul des Commissions</h3>
          </div>
        </div>

        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-commissions" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse; font-size: 13px;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 2px solid #E2E8F0;">
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Code Versement</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Commercial</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Zone</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Date Validation</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Code Caisse</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: right;">Montant Versé</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: center;">Taux (%)</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: right;">Commission Gagnée</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: center;">Statut</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<script src="<?= RACINE ?>public/assets/js/modules/commissions.js?v=1.0"></script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
