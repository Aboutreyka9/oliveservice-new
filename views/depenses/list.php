<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$stats = $stats ?? [
    'total_montant' => 0,
    'total_actif' => 0,
    'total_inactif' => 0,
    'nb_actif' => 0,
    'nb_inactif' => 0,
    'total_depenses' => 0
];
?>
<style>
/* ================= STYLES KPI & JOURNAL DES DÉPENSES ================= */
.dep-page-container {
  padding: 24px;
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

/* En-tête de page */
.dep-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 24px;
}

.dep-title-wrap h1 {
  font-size: 22px;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 10px;
}

.dep-title-wrap p {
  color: #64748B;
  font-size: 13px;
  margin: 4px 0 0 0;
}

.dep-header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.btn-refresh-dep {
  background: #FFFFFF;
  border: 1px solid #CBD5E1;
  color: #475569;
  border-radius: 8px;
  padding: 9px 14px;
  font-weight: 600;
  font-size: 13px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-refresh-dep:hover {
  background: #F1F5F9;
  color: #0F172A;
  border-color: #94A3B8;
}

.btn-add-dep {
  background: #1E3A5F;
  border: 1px solid #1E3A5F;
  color: #FFFFFF;
  border-radius: 8px;
  padding: 9px 18px;
  font-weight: 700;
  font-size: 13px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  box-shadow: 0 2px 4px rgba(30, 58, 95, 0.2);
  transition: all 0.2s ease;
}

.btn-add-dep:hover {
  background: #152B47;
  color: #FFFFFF;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(30, 58, 95, 0.25);
}

/* Grille KPI */
.dep-kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 18px;
  margin-bottom: 24px;
}

.dep-kpi-card {
  background: #FFFFFF;
  border-radius: 14px;
  padding: 20px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.02);
  transition: all 0.25s ease;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.dep-kpi-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: var(--kpi-accent, #1E3A5F);
  border-radius: 14px 14px 0 0;
}

.dep-kpi-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 20px -5px rgba(15, 23, 42, 0.08);
}

.dep-kpi-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.dep-kpi-title {
  font-size: 12px;
  font-weight: 700;
  color: #64748B;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.dep-kpi-icon {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--kpi-bg-icon, #EFF6FF);
  color: var(--kpi-color-icon, #1E3A5F);
  transition: transform 0.2s ease;
}

.dep-kpi-card:hover .dep-kpi-icon {
  transform: scale(1.1);
}

.dep-kpi-val {
  font-size: 24px;
  font-weight: 800;
  line-height: 1.15;
  letter-spacing: -0.5px;
}

.dep-kpi-footer {
  font-size: 12px;
  color: #64748B;
  margin-top: 10px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.dep-kpi-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
}

/* Carte Table DataTables */
.dep-table-card {
  background: #FFFFFF;
  border-radius: 14px;
  padding: 24px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

.dep-table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
  padding-bottom: 14px;
  border-bottom: 1px solid #F1F5F9;
}

.dep-table-header h2 {
  font-size: 16px;
  font-weight: 700;
  color: #1E293B;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Badges de code dépense */
.dep-code-badge {
  background: #F1F5F9;
  color: #0F172A;
  font-weight: 700;
  padding: 3px 7px;
  border-radius: 5px;
  border: 1px solid #CBD5E1;
  font-size: 12px;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

/* Stylisation DataTables */
#table-depenses {
  border-collapse: collapse !important;
  width: 100% !important;
}

#table-depenses thead th {
  background: #F8FAFC !important;
  color: #475569 !important;
  font-size: 11px !important;
  font-weight: 700 !important;
  text-transform: uppercase !important;
  letter-spacing: 0.5px !important;
  border-bottom: 2px solid #E2E8F0 !important;
  padding: 12px 10px !important;
}

#table-depenses tbody td {
  padding: 12px 10px !important;
  vertical-align: middle !important;
  border-bottom: 1px solid #F1F5F9 !important;
  font-size: 13px !important;
  color: #1E293B !important;
}

#table-depenses tbody tr:hover td {
  background: #F8FAFC !important;
}
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="dep-page-container">
      
      <!-- EN-TÊTE DE PAGE -->
      <div class="dep-header">
        <div class="dep-title-wrap">
          <h1>
            <i data-lucide="receipt" style="width: 26px; height: 26px; color: #DC2626;"></i>
            <span>Journal des Dépenses d'Exploitation</span>
          </h1>
          <p>Suivi analytique des charges, sorties de caisse et ordonnancements</p>
        </div>
        <div class="dep-header-actions">
          <button type="button" id="btn-reload-depenses" class="btn-refresh-dep" title="Actualiser le tableau et les compteurs">
            <i data-lucide="rotate-cw" style="width: 15px; height: 15px;"></i>
            <span>Actualiser</span>
          </button>
          <?php if (Context::can('FINANCE_MANAGE_DEPENSES')): ?>
          <a href="<?= RACINE ?>depense/formulaire" class="btn-add-dep">
            <i data-lucide="plus-circle" style="width: 17px; height: 17px;"></i>
            <span>Nouvelle Dépense</span>
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- GRILLE DES STATISTIQUES (KPI STATES) -->
      <div class="dep-kpi-grid">
        
        <!-- KPI 1 : TOTAL ENGAGÉ -->
        <div class="dep-kpi-card" style="--kpi-accent: linear-gradient(90deg, #EF4444, #B91C1C); --kpi-bg-icon: #FEF2F2; --kpi-color-icon: #DC2626;">
          <div class="dep-kpi-header">
            <span class="dep-kpi-title">Total Engagé</span>
            <div class="dep-kpi-icon">
              <i data-lucide="wallet" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div class="dep-kpi-val" id="kpi-total-montant" style="color: #DC2626;">
            <?= number_format($stats['total_montant'] ?? 0, 0, ',', ' ') ?> <small style="font-size: 13px; font-weight: 700;">FCFA</small>
          </div>
          <div class="dep-kpi-footer">
            <span class="dep-kpi-badge" style="background: #FEF2F2; color: #DC2626;">
              <i data-lucide="trending-down" style="width: 12px; height: 12px;"></i> Sorties de caisse
            </span>
            <span style="font-size: 11px; color: #94A3B8;">Global</span>
          </div>
        </div>

        <!-- KPI 2 : DÉPENSES ACTIVES (VALIDÉES) -->
        <div class="dep-kpi-card" style="--kpi-accent: linear-gradient(90deg, #10B981, #047857); --kpi-bg-icon: #ECFDF5; --kpi-color-icon: #059669;">
          <div class="dep-kpi-header">
            <span class="dep-kpi-title">Dépenses Actives</span>
            <div class="dep-kpi-icon">
              <i data-lucide="check-circle-2" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div class="dep-kpi-val" id="kpi-total-actif" style="color: #059669;">
            <?= number_format($stats['total_actif'] ?? 0, 0, ',', ' ') ?> <small style="font-size: 13px; font-weight: 700;">FCFA</small>
          </div>
          <div class="dep-kpi-footer">
            <span class="dep-kpi-badge" style="background: #ECFDF5; color: #059669;">
              <span id="kpi-nb-actif"><?= $stats['nb_actif'] ?? 0 ?></span> validée(s)
            </span>
            <span style="font-size: 11px; color: #94A3B8;">Approuvées</span>
          </div>
        </div>

        <!-- KPI 3 : EN ATTENTE DE VALIDATION -->
        <div class="dep-kpi-card" style="--kpi-accent: linear-gradient(90deg, #F59E0B, #D97706); --kpi-bg-icon: #FFFBEB; --kpi-color-icon: #D97706;">
          <div class="dep-kpi-header">
            <span class="dep-kpi-title">En Attente</span>
            <div class="dep-kpi-icon">
              <i data-lucide="clock" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div class="dep-kpi-val" id="kpi-total-inactif" style="color: #D97706;">
            <?= number_format($stats['total_inactif'] ?? 0, 0, ',', ' ') ?> <small style="font-size: 13px; font-weight: 700;">FCFA</small>
          </div>
          <div class="dep-kpi-footer">
            <span class="dep-kpi-badge" style="background: #FFFBEB; color: #D97706;">
              <span id="kpi-nb-inactif"><?= $stats['nb_inactif'] ?? 0 ?></span> en attente
            </span>
            <span style="font-size: 11px; color: #94A3B8;">À approuver</span>
          </div>
        </div>

        <!-- KPI 4 : NOMBRE TOTAL D'OPÉRATIONS -->
        <div class="dep-kpi-card" style="--kpi-accent: linear-gradient(90deg, #3B82F6, #1E3A5F); --kpi-bg-icon: #EFF6FF; --kpi-color-icon: #1E3A5F;">
          <div class="dep-kpi-header">
            <span class="dep-kpi-title">Volume Opérations</span>
            <div class="dep-kpi-icon">
              <i data-lucide="layers" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div class="dep-kpi-val" id="kpi-total-ops" style="color: #1E3A5F;">
            <?= number_format($stats['total_depenses'] ?? 0, 0, ',', ' ') ?>
          </div>
          <div class="dep-kpi-footer">
            <span class="dep-kpi-badge" style="background: #EFF6FF; color: #1E3A5F;">
              <i data-lucide="file-spreadsheet" style="width: 12px; height: 12px;"></i> Écritures caisse
            </span>
            <span style="font-size: 11px; color: #94A3B8;">Exercice</span>
          </div>
        </div>

      </div>

      <!-- CARTE TABLE DES DÉPENSES -->
      <div class="dep-table-card">
        <div class="dep-table-header">
          <h2>
            <i data-lucide="list" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
            <span>Historique des Écritures de Dépenses</span>
          </h2>
        </div>
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-depenses" class="table display nowrap">
            <thead>
              <tr>
                <th style="padding: 12px;">Code</th>
                <th style="padding: 12px;">Date Enregistrée</th>
                <th style="padding: 12px;">Période</th>
                <th style="padding: 12px;">Catégorie Dépense</th>
                <th style="padding: 12px;">Motif / Description</th>
                <th style="padding: 12px;">Montant Engagé</th>
                <th style="padding: 12px;">Auteur</th>
                <th style="padding: 12px; text-align: center;">Statut</th>
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
<script src="<?= RACINE ?>public/assets/js/modules/depenses.js?v=1.3"></script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
