<?php
require_once __DIR__ . '/../../public/inc/header.php';
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
          <h1 style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="navigation" style="color: #2563EB;"></i> Gestion des Villes & Quartiers
          </h1>
          <p class="page-subtitle" style="color: #64748B; margin: 4px 0 0 0;">Découpage territorial, zones de couverture et gestion des quartiers rattachés</p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>quartier/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
            <i data-lucide="map-pin" style="width: 16px; height: 16px;"></i> Tous les quartiers
          </a>
          <button type="button" class="btn btn-primary" onclick="openAddVilleModal()" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; background: #1E3A5F; border-color: #1E3A5F;">
            <i data-lucide="plus" style="width: 16px; height: 16px;"></i> Ajouter une ville
          </button>
        </div>
      </div>

      <div class="card" style="border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div class="table-responsive-mobile">
          <table class="table" id="dataTable" style="width: 100%;">
            <thead>
              <tr>
                <th>N°</th>
                <th>Code</th>
                <th>Libellé Ville</th>
                <th>Quartiers Rattachés</th>
                <th>Statut</th>
                <th style="text-align: center;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- MODAL AJOUTER VILLE -->
<div id="modalAddVille" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px); padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden;">
    <div style="padding: 18px 22px; background: #F8FAFC; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #1E293B; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="plus-circle" style="color: #2563EB; width: 18px; height: 18px;"></i> Ajouter une Ville
      </h3>
      <button type="button" onclick="closeAddVilleModal()" style="background: none; border: none; font-size: 18px; color: #94A3B8; cursor: pointer;">
        <i class="fa fa-times"></i>
      </button>
    </div>

    <form id="formAddVille" style="padding: 22px;">
      <?= Validator::csrfField() ?>
      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Nom de la Ville *</label>
        <input type="text" class="form-control" name="libelle_ville" placeholder="Ex: Abidjan, Yamoussoukro, Bouaké..." required>
      </div>

      <div class="form-group" style="margin-bottom: 20px;">
        <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Code Ville (Optionnel)</label>
        <input type="text" class="form-control" name="code_ville" placeholder="Généré automatiquement si vide (ex: VIL-ABJ)">
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" class="btn btn-secondary" onclick="closeAddVilleModal()">Annuler</button>
        <button type="submit" class="btn btn-primary btn_actions" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
          <i data-lucide="save" style="width: 16px; height: 16px;"></i> Enregistrer la ville
        </button>
      </div>
    </form>
  </div>
</div>

<script src="<?= RACINE ?>public/json/mobile-list.js"></script>
<script src="<?= RACINE ?>public/json/entities/villes.js?v=4"></script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
