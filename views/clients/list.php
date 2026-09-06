<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$stats = $stats ?? [
    'total_clients' => 0,
    'clients_actifs' => 0,
    'clients_inactifs' => 0,
    'clients_souscripteurs' => 0,
    'nouveaux_ce_mois' => 0,
    'total_cotise' => 0,
    'taux_engagement' => 0,
    'taux_actifs' => 0
];
?>
<style>
/* ================= STYLES RÉPERTOIRE CLIENTS PREMIUM ================= */
.cli-page-container {
  padding: 24px;
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

/* En-tête de page */
.cli-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 24px;
}

.cli-title-wrap h1 {
  font-size: 22px;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 10px;
}

.cli-title-wrap p {
  color: #64748B;
  font-size: 13px;
  margin: 4px 0 0 0;
}

.cli-header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.btn-refresh-cli {
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

.btn-refresh-cli:hover {
  background: #F1F5F9;
  color: #0F172A;
  border-color: #94A3B8;
}

.btn-add-sous {
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
  box-shadow: 0 2px 6px rgba(30, 58, 95, 0.2);
  transition: all 0.2s ease;
}

.btn-add-sous:hover {
  background: #152B47;
  color: #FFFFFF;
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(30, 58, 95, 0.25);
}

.btn-add-client {
  background: #FFFFFF;
  border: 1.5px solid #1E3A5F;
  color: #1E3A5F;
  border-radius: 8px;
  padding: 9px 16px;
  font-weight: 700;
  font-size: 13px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn-add-client:hover {
  background: #EFF6FF;
  border-color: #1E3A5F;
  color: #1E3A5F;
}

/* Grille KPI */
.cli-kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
  gap: 18px;
  margin-bottom: 24px;
}

.cli-kpi-card {
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

.cli-kpi-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: var(--kpi-accent, #1E3A5F);
  border-radius: 14px 14px 0 0;
}

.cli-kpi-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 20px -5px rgba(15, 23, 42, 0.08);
}

.cli-kpi-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.cli-kpi-title {
  font-size: 12px;
  font-weight: 700;
  color: #64748B;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.cli-kpi-icon {
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

.cli-kpi-card:hover .cli-kpi-icon {
  transform: scale(1.1);
}

.cli-kpi-val {
  font-size: 24px;
  font-weight: 800;
  line-height: 1.15;
  letter-spacing: -0.5px;
  color: #0F172A;
}

.cli-kpi-footer {
  font-size: 12px;
  color: #64748B;
  margin-top: 10px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.cli-kpi-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
}

/* Barre de filtres rapides */
.cli-filter-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 18px;
  background: #FFFFFF;
  border-radius: 12px;
  padding: 12px 18px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}

.cli-filter-group {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.btn-cli-filter {
  background: #F8FAFC;
  border: 1px solid #CBD5E1;
  color: #475569;
  border-radius: 20px;
  padding: 6px 14px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s ease;
}

.btn-cli-filter:hover {
  background: #F1F5F9;
  color: #1E3A5F;
  border-color: #94A3B8;
}

.btn-cli-filter.active {
  background: #1E3A5F;
  border-color: #1E3A5F;
  color: #FFFFFF;
  box-shadow: 0 2px 6px rgba(30, 58, 95, 0.2);
}

.btn-cli-filter .count-pill {
  background: rgba(0, 0, 0, 0.08);
  padding: 2px 7px;
  border-radius: 10px;
  font-size: 11px;
}

.btn-cli-filter.active .count-pill {
  background: rgba(255, 255, 255, 0.25);
  color: #FFFFFF;
}

/* Carte Table DataTables */
.cli-table-card {
  background: #FFFFFF;
  border-radius: 14px;
  padding: 24px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

.cli-table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
  padding-bottom: 14px;
  border-bottom: 1px solid #F1F5F9;
}

.cli-table-header h2 {
  font-size: 16px;
  font-weight: 700;
  color: #1E293B;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Badges code client */
.client-code-badge {
  background: #EFF6FF;
  color: #1D4ED8;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 6px;
  border: 1px solid #BFDBFE;
  font-size: 11.5px;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

/* Avatar initiales */
.client-avatar-mini {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%);
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 13px;
  flex-shrink: 0;
  box-shadow: 0 2px 5px rgba(30, 58, 95, 0.2);
}

/* Stylisation DataTables */
#table-clients {
  border-collapse: collapse !important;
  width: 100% !important;
}

#table-clients thead th {
  background: #F8FAFC !important;
  color: #475569 !important;
  font-size: 11px !important;
  font-weight: 700 !important;
  text-transform: uppercase !important;
  letter-spacing: 0.5px !important;
  border-bottom: 2px solid #E2E8F0 !important;
  padding: 12px 10px !important;
}

#table-clients tbody td {
  padding: 12px 10px !important;
  vertical-align: middle !important;
  border-bottom: 1px solid #F1F5F9 !important;
  font-size: 13px !important;
  color: #1E293B !important;
}

#table-clients tbody tr:hover td {
  background: #F8FAFC !important;
}

/* Téléphone interactif */
.cli-tel-link {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  color: #1E3A5F;
  font-weight: 600;
  text-decoration: none;
  font-size: 12.5px;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  padding: 3px 8px;
  border-radius: 6px;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  transition: all 0.2s ease;
}

.cli-tel-link:hover {
  background: #EFF6FF;
  border-color: #BFDBFE;
  color: #2563EB;
}

/* Boutons d'action modernes */
.cli-actions-wrap {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  justify-content: flex-end;
}

.btn-action-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #E2E8F0;
  background: #FFFFFF;
  color: #475569;
  transition: all 0.2s ease;
  text-decoration: none;
  cursor: pointer;
}

.btn-action-icon:hover {
  background: #F1F5F9;
  color: #1E3A5F;
  border-color: #94A3B8;
  transform: translateY(-1px);
}

.btn-action-icon.primary {
  background: #1E3A5F;
  border-color: #1E3A5F;
  color: #FFFFFF;
}

.btn-action-icon.primary:hover {
  background: #152B47;
  color: #FFFFFF;
  box-shadow: 0 2px 6px rgba(30, 58, 95, 0.25);
}

.btn-action-icon.secondary {
  background: #FFFFFF;
  border-color: #CBD5E1;
  color: #334155;
}

.btn-action-icon.secondary:hover {
  background: #F8FAFC;
  border-color: #94A3B8;
  color: #0F172A;
}

.btn-action-icon.success {
  background: #ECFDF5;
  border-color: #A7F3D0;
  color: #059669;
}

.btn-action-icon.success:hover {
  background: #059669;
  border-color: #059669;
  color: #FFFFFF;
  box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25);
}
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="cli-page-container">
      <input type="hidden" id="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      
      <!-- EN-TÊTE DE PAGE -->
      <div class="cli-header">
        <div class="cli-title-wrap">
          <h1>
            <i data-lucide="users" style="width: 26px; height: 26px; color: #1E3A5F;"></i>
            <span>Répertoire des Clients & Abonnés</span>
          </h1>
          <p>Gestion du portefeuille abonnés, suivi des souscriptions de packs et cotisations terrain</p>
        </div>
        <div class="cli-header-actions">
          <button type="button" id="btn-reload-clients" class="btn-refresh-cli" title="Actualiser le tableau et les compteurs">
            <i data-lucide="rotate-cw" style="width: 15px; height: 15px;"></i>
            <span>Actualiser</span>
          </button>
          
          <?php if (Context::can('COMMERCIAL_ADD_SOUSCRIPTION', ['ROLE_COMMERCIAL', 'ROLE_ADMIN'])): ?>
          <a href="<?= RACINE ?>souscription/wizard" class="btn-add-sous" title="Lancer une nouvelle souscription pack">
            <i data-lucide="plus-circle" style="width: 17px; height: 17px;"></i>
            <span>Nouvelle Souscription</span>
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- GRILLE DES STATISTIQUES (KPI STATES) -->
      <div class="cli-kpi-grid">
        
        <!-- KPI 1 : PORTEFEUILLE GLOBAL -->
        <div class="cli-kpi-card" style="--kpi-accent: linear-gradient(90deg, #1E3A5F, #0F172A); --kpi-bg-icon: #EFF6FF; --kpi-color-icon: #1E3A5F;">
          <div class="cli-kpi-header">
            <span class="cli-kpi-title">Portefeuille Global</span>
            <div class="cli-kpi-icon">
              <i data-lucide="users" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div class="cli-kpi-val" id="kpi-total-clients" style="color: #1E3A5F;">
            <?= number_format($stats['total_clients'] ?? 0, 0, ',', ' ') ?>
          </div>
          <div class="cli-kpi-footer">
            <span class="cli-kpi-badge" style="background: #EFF6FF; color: #1E3A5F;">
              <i data-lucide="user-check" style="width: 12px; height: 12px;"></i> <span id="kpi-nb-actifs-mini"><?= $stats['clients_actifs'] ?? 0 ?></span> actifs
            </span>
            <span style="font-size: 11px; color: #94A3B8;">Abonnés enregistrés</span>
          </div>
        </div>

        <!-- KPI 2 : CLIENTS ACTIFS -->
        <div class="cli-kpi-card" style="--kpi-accent: linear-gradient(90deg, #10B981, #047857); --kpi-bg-icon: #ECFDF5; --kpi-color-icon: #059669;">
          <div class="cli-kpi-header">
            <span class="cli-kpi-title">Clients Actifs</span>
            <div class="cli-kpi-icon">
              <i data-lucide="check-circle-2" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div class="cli-kpi-val" id="kpi-clients-actifs" style="color: #059669;">
            <?= number_format($stats['clients_actifs'] ?? 0, 0, ',', ' ') ?>
          </div>
          <div class="cli-kpi-footer">
            <span class="cli-kpi-badge" style="background: #ECFDF5; color: #059669;">
              <span id="kpi-taux-actifs"><?= $stats['taux_actifs'] ?? 0 ?>%</span> du portefeuille
            </span>
            <span style="font-size: 11px; color: #94A3B8;">Comptes validés</span>
          </div>
        </div>

        <!-- KPI 3 : CLIENTS SOUSCRIPTEURS (ENGAGÉS) -->
        <div class="cli-kpi-card" style="--kpi-accent: linear-gradient(90deg, #2563EB, #1D4ED8); --kpi-bg-icon: #EEF2FF; --kpi-color-icon: #2563EB;">
          <div class="cli-kpi-header">
            <span class="cli-kpi-title">Souscripteurs Packs</span>
            <div class="cli-kpi-icon">
              <i data-lucide="file-check" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div class="cli-kpi-val" id="kpi-clients-souscripteurs" style="color: #2563EB;">
            <?= number_format($stats['clients_souscripteurs'] ?? 0, 0, ',', ' ') ?>
          </div>
          <div class="cli-kpi-footer">
            <span class="cli-kpi-badge" style="background: #EEF2FF; color: #2563EB;">
              Taux engagement : <span id="kpi-taux-engagement"><?= $stats['taux_engagement'] ?? 0 ?>%</span>
            </span>
            <span style="font-size: 11px; color: #94A3B8;">Avec contrat actif</span>
          </div>
        </div>

        <!-- KPI 4 : TOTAL COTISATIONS RECOUVRÉES -->
        <div class="cli-kpi-card" style="--kpi-accent: linear-gradient(90deg, #059669, #065F46); --kpi-bg-icon: #F0FDF4; --kpi-color-icon: #16A34A;">
          <div class="cli-kpi-header">
            <span class="cli-kpi-title">Cotisations Encaissées</span>
            <div class="cli-kpi-icon">
              <i data-lucide="wallet" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div class="cli-kpi-val" id="kpi-total-cotise" style="color: #15803D;">
            <?= number_format($stats['total_cotise'] ?? 0, 0, ',', ' ') ?> <small style="font-size: 13px; font-weight: 700;">FCFA</small>
          </div>
          <div class="cli-kpi-footer">
            <span class="cli-kpi-badge" style="background: #F0FDF4; color: #16A34A;">
              <i data-lucide="trending-up" style="width: 12px; height: 12px;"></i> Cumul encaissé
            </span>
            <span style="font-size: 11px; color: #94A3B8;">Périmètre actif</span>
          </div>
        </div>

      </div>

      <!-- BARRE DE FILTRES RAPIDES PAR STATUT -->
      <div class="cli-filter-bar">
        <div class="cli-filter-group">
          <span style="font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; margin-right: 4px;">Filtrer :</span>
          <button type="button" class="btn-cli-filter active" data-filter="all">
            <span>Tous les clients</span>
            <span class="count-pill" id="filter-count-all"><?= $stats['total_clients'] ?? 0 ?></span>
          </button>
          <button type="button" class="btn-cli-filter" data-filter="actif">
            <span>Actifs</span>
            <span class="count-pill" id="filter-count-actif"><?= $stats['clients_actifs'] ?? 0 ?></span>
          </button>
          <button type="button" class="btn-cli-filter" data-filter="inactif">
            <span>Inactifs</span>
            <span class="count-pill" id="filter-count-inactif"><?= $stats['clients_inactifs'] ?? 0 ?></span>
          </button>
          <button type="button" class="btn-cli-filter" data-filter="souscripteur">
            <span>Souscripteurs</span>
            <span class="count-pill" id="filter-count-sous"><?= $stats['clients_souscripteurs'] ?? 0 ?></span>
          </button>
        </div>
        <div style="font-size: 12px; color: #64748B;">
          <i data-lucide="info" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle;"></i> Cliquez sur le statut d'un client pour l'activer / le désactiver
        </div>
      </div>

      <!-- CARTE TABLE DATATABLES -->
      <div class="cli-table-card">
        <div class="cli-table-header">
          <h2>
            <i data-lucide="list" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
            <span>Liste Complète des Clients</span>
          </h2>
        </div>
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-clients" class="table display nowrap">
            <thead>
              <tr>
                <th style="padding: 12px; width: 45px;">N°</th>
                <th style="padding: 12px; width: 110px;">Code Client</th>
                <th style="padding: 12px;">Abonné / Nom & Prénom</th>
                <th style="padding: 12px;">Contact Téléphonique</th>
                <th style="padding: 12px;">N° CNI</th>
                <th style="padding: 12px;">Zone & Résidence</th>
                <th style="padding: 12px; text-align: center;">Souscriptions</th>
                <th style="padding: 12px; text-align: right;">Cumul Cotisé</th>
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
<script src="<?= RACINE ?>public/assets/js/modules/clients.js?v=2.0"></script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
