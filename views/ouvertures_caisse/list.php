<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Registre des Ouvertures de Caisse</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Historique des ouvertures de caisses et fonds de caisse initiaux</p>
        </div>
        <div style="display: flex; gap: 10px;">
          <a href="<?= RACINE ?>ouverture_caisse/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; color: #FFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="unlock" style="width: 18px; height: 18px;"></i> Nouvelle Ouverture Caisse
          </a>
          <a href="<?= RACINE ?>cloture_caisse/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="lock" style="width: 18px; height: 18px;"></i> Clôtures de Caisse
          </a>
        </div>
      </div>

      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <table id="table_ouvertures_caisse" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%;">
          <thead>
            <tr>
              <th>ID</th>
              <th>Code</th>
              <th>Date Ouverture</th>
              <th>Heure</th>
              <th>Fond Initial (FCFA)</th>
              <th>Commercial / Auteur</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

    </div>
  </main>
</div>
<script src="<?= RACINE ?>public/assets/js/modules/ouvertures_caisse.js?v=1.0"></script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
