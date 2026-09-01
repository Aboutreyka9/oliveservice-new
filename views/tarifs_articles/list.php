<?php
require_once __DIR__ . '/../../public/inc/header.php';
?>

<style>
/* === MOBILE PWA UX OPTIMIZATIONS FOR TARIFS === */
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
    width: 100% !important;
  }
  .page-header-actions .btn {
    width: 100% !important;
    justify-content: center !important;
    height: 48px !important;
    font-size: 15px !important;
  }
}
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1 style="margin: 0; font-size: 22px; font-weight: 800; color: #1E293B;">Grille Tarifaire des Vêtements & Services</h1>
          <p class="page-subtitle" style="margin: 4px 0 0; color: #64748B; font-size: 13px;">Catalogue des prix appliqués pour chaque vêtement et type de nettoyage</p>
        </div>
        <div class="page-header-actions">
          <a href="<?= RACINE ?>tarif/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="plus" style="width: 18px; height: 18px;"></i> Ajouter un tarif
          </a>
        </div>
      </div>

      <div class="card">
         <div class="mobile-list-container"></div>
         <div class="table-responsive-mobile">
             <table class="table" id="dataTable">
                 <thead>
                  <tr>
                    <th>N°</th><th>Code</th><th>Pressing</th><th>Vêtement / Article</th><th>Service</th><th>Prix (FCFA)</th><th>Statut</th><th>Actions</th>
                  </tr>
               </thead>
               <tbody></tbody>
            </table>
          </div>
       </div>

      <script src="<?= RACINE ?>public/json/mobile-list.js"></script>
      <script src="<?= RACINE ?>public/json/entities/tarifs.js?v=4"></script>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
