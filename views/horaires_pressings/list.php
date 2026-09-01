<?php
require_once __DIR__ . '/../../public/inc/header.php';
?>

<style>
/* === MOBILE PWA UX OPTIMIZATIONS FOR HORAIRES === */
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
          <h1 style="margin: 0; font-size: 22px; font-weight: 800; color: #1E293B;">Horaires d'Ouverture du Pressing</h1>
          <p class="page-subtitle" style="margin: 4px 0 0; color: #64748B; font-size: 13px;">Plages d'ouverture de votre atelier visibles par les clients</p>
        </div>
        <div class="page-header-actions">
          <a href="<?= RACINE ?>horaire/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="plus" style="width: 18px; height: 18px;"></i> Ajouter horaire
          </a>
        </div>
      </div>

      <div class="card">
         <div class="mobile-list-container"></div>
          <div class="table-responsive-mobile">
              <table class="table" id="dataTable" data-superadmin="<?= !empty($isSuperAdmin) ? '1' : '0' ?>">
                  <thead>
                   <tr>
                     <th>N°</th>
                     <?php if (!empty($isSuperAdmin)): ?>
                       <th>Pressing</th>
                     <?php endif; ?>
                     <th>Jour</th>
                     <th>Ouverture</th>
                     <th>Fermeture</th>
                     <th>Statut</th>
                     <th>Actions</th>
                   </tr>
                </thead>
                <tbody></tbody>
             </table>
           </div>
       </div>

      <script src="<?= RACINE ?>public/json/mobile-list.js"></script>
      <script src="<?= RACINE ?>public/json/entities/horaires.js?v=5"></script>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
