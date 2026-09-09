<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$stats = $stats ?? [
    'total_packs' => 0,
    'nb_actif' => 0,
    'nb_inactif' => 0,
    'avg_prix_jour' => 0,
    'total_articles' => 0
];
?>
<style>
/* ================= STYLES KPI & PACKS CATALOGUE ================= */
.pck-page-container {
  padding: 24px;
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

.pck-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 24px;
}

.pck-title-wrap h1 {
  font-size: 22px;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 10px;
}

.pck-title-wrap p {
  color: #64748B;
  font-size: 13px;
  margin: 4px 0 0 0;
}

.pck-header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.btn-refresh-pck {
  background: #FFFFFF;
  border: 1px solid #CBD5E1;
  color: #475569;
  border-radius: 10px;
  padding: 10px 16px;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.btn-refresh-pck:hover {
  background: #F8FAFC;
  border-color: #94A3B8;
  color: #1E293B;
}

.btn-add-pck {
  background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%);
  color: #FFFFFF;
  border-radius: 10px;
  padding: 10px 20px;
  font-weight: 800;
  font-size: 13.5px;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
  transition: all 0.2s ease;
  border: none;
}

.btn-add-pck:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(15, 23, 42, 0.3);
  color: #FFFFFF;
}

/* Grid KPI Premium */
.pck-kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
  gap: 20px;
  margin-bottom: 28px;
}

.pck-kpi-card {
  background: #FFFFFF;
  border-radius: 16px;
  padding: 22px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.pck-kpi-card:nth-child(1) { animation: fadeInUp 0.3s ease-out 0.05s backwards; }
.pck-kpi-card:nth-child(2) { animation: fadeInUp 0.3s ease-out 0.10s backwards; }
.pck-kpi-card:nth-child(3) { animation: fadeInUp 0.3s ease-out 0.15s backwards; }

.pck-kpi-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: var(--kpi-accent, #1E3A5F);
  border-radius: 16px 16px 0 0;
  transition: height 0.25s ease;
}

.pck-kpi-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 20px -5px rgba(15, 23, 42, 0.08);
  border-color: rgba(30, 58, 95, 0.3);
}

.pck-kpi-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.pck-kpi-title {
  font-size: 12px;
  font-weight: 700;
  color: #64748B;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.pck-kpi-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: var(--kpi-bg-icon, #EFF6FF);
  color: var(--kpi-color-icon, #1E3A5F);
  display: flex;
  align-items: center;
  justify-content: center;
}

.pck-kpi-val {
  font-size: 24px;
  font-weight: 800;
  color: #0F172A;
  margin-bottom: 12px;
  line-height: 1.1;
}

.pck-kpi-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 12px;
  color: #64748B;
  padding-top: 10px;
  border-top: 1px dashed #F1F5F9;
}

.pck-kpi-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.pck-table-card {
  background: #FFFFFF;
  border-radius: 16px;
  padding: 24px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
}
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="pck-page-container">
      
      <!-- EN-TÊTE DE PAGE -->
      <div class="pck-header">
        <div class="pck-title-wrap">
          <h1>
            <i data-lucide="package" style="width: 26px; height: 26px; color: #1E3A5F;"></i>
            <span>Packs & Offres Produit</span>
          </h1>
          <p>Catalogue des offres souscriptibles (Alimentaires, Électroménager, Événements...)</p>
        </div>
        <div class="pck-header-actions">
          <button type="button" id="btn-reload-packs" class="btn-refresh-pck" title="Actualiser la liste et les compteurs">
            <i data-lucide="rotate-cw" style="width: 15px; height: 15px;"></i>
            <span>Actualiser</span>
          </button>
          <?php if (Context::can('GESTIONNAIRE_MANAGE_PACKS', ['ROLE_GESTIONNAIRE', 'ROLE_ADMIN'])): ?>
          <a href="<?= RACINE ?>pack/formulaire" class="btn-add-pck">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i>
            <span>Nouveau Pack</span>
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- GRILLE DES CARTES KPI -->
      <div class="pck-kpi-grid">
        
        <!-- KPI 1 : TOTAL PACKS -->
        <div class="pck-kpi-card" style="--kpi-accent: linear-gradient(90deg, #1E3A5F, #0F172A); --kpi-bg-icon: #EFF6FF; --kpi-color-icon: #1E3A5F;">
          <div class="pck-kpi-header">
            <span class="pck-kpi-title">Offres Catalogue</span>
            <div class="pck-kpi-icon">
              <i data-lucide="package" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div class="pck-kpi-val" id="kpi-total-packs" style="color: #1E3A5F;">
            <?= number_format($stats['total_packs'] ?? 0, 0, ',', ' ') ?> <small style="font-size: 13px; font-weight: 600; color: #64748B;">Packs</small>
          </div>
          <div class="pck-kpi-footer">
            <span>Nombre de packs</span>
            <span class="pck-kpi-badge" style="background: #EFF6FF; color: #1E3A5F;">
              <span id="kpi-nb-actif"><?= $stats['nb_actif'] ?? 0 ?></span> actif(s)
            </span>
          </div>
        </div>

        <!-- KPI 2 : PACKS ACTIFS -->
        <div class="pck-kpi-card" style="--kpi-accent: linear-gradient(90deg, #059669, #047857); --kpi-bg-icon: #ECFDF5; --kpi-color-icon: #059669;">
          <div class="pck-kpi-header">
            <span class="pck-kpi-title">Packs Disponibles</span>
            <div class="pck-kpi-icon">
              <i data-lucide="check-circle-2" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div class="pck-kpi-val" id="kpi-nb-actif-val" style="color: #059669;">
            <?= number_format($stats['nb_actif'] ?? 0, 0, ',', ' ') ?>
          </div>
          <div class="pck-kpi-footer">
            <span>Souscription ouverte</span>
            <span class="pck-kpi-badge" style="background: #ECFDF5; color: #059669;">Disponibles</span>
          </div>
        </div>

        <!-- KPI 4 : ARTICLES CATALOGUE -->
        <div class="pck-kpi-card" style="--kpi-accent: linear-gradient(90deg, #0284C7, #0369A1); --kpi-bg-icon: #E0F2FE; --kpi-color-icon: #0284C7;">
          <div class="pck-kpi-header">
            <span class="pck-kpi-title">Articles Catalogue</span>
            <div class="pck-kpi-icon">
              <i data-lucide="layers" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div class="pck-kpi-val" id="kpi-total-articles" style="color: #0284C7;">
            <?= number_format($stats['total_articles'] ?? 0, 0, ',', ' ') ?> <small style="font-size: 13px; font-weight: 600; color: #64748B;">réfs</small>
          </div>
          <div class="pck-kpi-footer">
            <span>Articles utilisables</span>
            <span class="pck-kpi-badge" style="background: #E0F2FE; color: #0284C7;">Catalogue</span>
          </div>
        </div>

      </div>

      <!-- CARTE TABLEAU PRINCIPALE -->
      <div class="pck-table-card">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-packs" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px;">ID</th>
                <th style="padding: 12px;">Code</th>
                <th style="padding: 12px;">Désignation / Nom du Pack</th>
                <th style="padding: 12px;">Catégorie</th>
                <th style="padding: 12px;">Session</th>
                <th style="padding: 12px;">Zone</th>
                <th style="padding: 12px; text-align: center;">Durée (jour)</th>
                <th style="padding: 12px; text-align: right;">Cotisation / Jour</th>
                <th style="padding: 12px; text-align: right;">Montant Total</th>
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
<script src="<?= RACINE ?>public/assets/js/modules/packs.js?v=1.2"></script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
