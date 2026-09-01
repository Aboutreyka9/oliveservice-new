<?php
require_once __DIR__ . '/../../public/inc/header.php';
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
        <div>
          <h1 style="margin: 0; font-size: 22px; font-weight: 800; color: #1E293B; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="truck" style="color: #2563EB;"></i> <?= !empty($isLivreur) ? 'Mes Missions & Courses' : 'Gestion des Missions' ?>
          </h1>
          <p class="page-subtitle" style="margin: 4px 0 0; color: #64748B; font-size: 13px;">
            <?= !empty($isLivreur) ? 'Consultez vos courses, lancez le guidage direct ou ouvrez la carte des tournées' : 'Affectation et suivi des courses des livreurs' ?>
          </p>
        </div>

        <div style="display: flex; gap: 8px;">
          <a href="<?= RACINE ?>mission/carte" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
            <i data-lucide="map" style="width: 16px; height: 16px;"></i> Carte des Tournées Live
          </a>
          <?php if (empty($isLivreur)): ?>
          <a href="<?= RACINE ?>mission/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
            <i data-lucide="plus" style="width: 16px; height: 16px;"></i> Ajouter mission
          </a>
          <?php endif; ?>
        </div>
      </div>

      <div class="card" style="padding: 20px; border-radius: 14px;">
         <div class="mobile-list-container"></div>
         <div class="table-responsive-mobile">
             <table class="table" id="dataTable" style="width: 100%;">
                 <thead>
                  <tr>
                    <th>N°</th><th>Code</th><th>Commande</th><th>Livreur</th><th>Type</th><th>Adresse</th><th>Statut</th><th style="text-align: center;">Actions</th>
                  </tr>
               </thead>
               <tbody></tbody>
            </table>
          </div>
       </div>

      <script>
        window.isLivreurUser = <?= !empty($isLivreur) ? 'true' : 'false' ?>;
      </script>
      <script src="<?= RACINE ?>public/json/mobile-list.js"></script>
      <script src="<?= RACINE ?>public/json/entities/missions.js?v=5"></script>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
