<?php
require_once __DIR__ . '/../../public/inc/header.php';
?>

<style>
/* === MOBILE PWA UX OPTIMIZATIONS FOR FORFAITS B2B === */
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
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
          <h1 style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="award" style="color: #2563EB;"></i> Grille des Forfaits B2B
          </h1>
          <p class="page-subtitle" style="color: #64748B; margin: 4px 0 0 0;">Configuration des plans d'adhésion et forfaits proposés aux pressings partenaires</p>
        </div>

        <div class="page-header-actions">
          <button type="button" class="btn btn-primary" onclick="openAddForfaitModal()" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
            <i data-lucide="plus" style="width: 16px; height: 16px;"></i> Nouveau forfait
          </button>
        </div>
      </div>

      <div class="card" style="border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div class="mobile-list-container"></div>
        <div class="table-responsive-mobile">
          <table class="table" id="dataTable" style="width: 100%;">
            <thead>
              <tr>
                <th>N°</th>
                <th>Code</th>
                <th>Libellé Forfait</th>
                <th>Montant</th>
                <th>Durée</th>
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

<!-- MODAL AJOUTER FORFAIT -->
<div id="modalAddForfait" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px); padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden;">
    <div style="padding: 18px 22px; background: #F8FAFC; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #1E293B; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="plus-circle" style="color: #2563EB; width: 18px; height: 18px;"></i> Créer un Forfait B2B
      </h3>
      <button type="button" onclick="closeAddForfaitModal()" style="background: none; border: none; font-size: 18px; color: #94A3B8; cursor: pointer;">
        <i class="fa fa-times"></i>
      </button>
    </div>

    <form id="formAddForfait" style="padding: 22px;">
      <?= Validator::csrfField() ?>
      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Nom / Libellé du Forfait *</label>
        <input type="text" class="form-control" name="libelle_forfait" placeholder="Ex: Forfait Trimestriel Pro" required>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
        <div class="form-group">
          <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Montant (FCFA) *</label>
          <input type="number" class="form-control" name="montant_forfait" placeholder="Ex: 50000" min="0" step="500" required>
        </div>

        <div class="form-group">
          <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Durée (Mois) *</label>
          <input type="number" class="form-control" name="duree_mois_forfait" value="1" min="1" max="36" required>
        </div>
      </div>

      <div class="form-group" style="margin-bottom: 14px;">
        <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Description générale</label>
        <textarea class="form-control" name="description_forfait" rows="2" placeholder="Ex: Idéal pour démarrer votre activité..."></textarea>
      </div>

      <div class="form-group" style="margin-bottom: 20px;">
        <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 4px;">
          Avantages & Fonctionnalités Incluses
        </label>
        <small style="display: block; font-size: 11px; color: #64748B; margin-bottom: 6px;">Indiquez 1 avantage par ligne (s'affichera avec coche ✓ dans l'application).</small>
        <textarea class="form-control" name="avantages_forfait" rows="4" placeholder="3 Mois offerts à l'inscription&#10;Jusqu'à 250 commandes mensuelles&#10;3 Comptes utilisateurs&#10;Badge 'Partenaire Certifié'"></textarea>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" class="btn btn-secondary" onclick="closeAddForfaitModal()">Annuler</button>
        <button type="submit" class="btn btn-primary btn_actions" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
          <i data-lucide="save" style="width: 16px; height: 16px;"></i> Enregistrer le forfait
        </button>
      </div>
    </form>
  </div>
</div>

<script src="<?= RACINE ?>public/json/mobile-list.js"></script>
<script src="<?= RACINE ?>public/json/entities/forfaits.js?v=3"></script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
