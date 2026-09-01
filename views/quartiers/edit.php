<?php
require_once __DIR__ . '/../../public/inc/header.php';
$quartier = isset($quartier) ? $quartier : [];
$villes = isset($villes) ? $villes : [];
$encryptedId = isset($encryptedId) ? $encryptedId : '';
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
          <h1 style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="map-pin" style="color: #2563EB;"></i> Modifier le Quartier : <?= htmlspecialchars($quartier['libelle_quartier'] ?? '') ?>
          </h1>
          <p class="page-subtitle" style="color: #64748B; margin: 4px 0 0 0;">Zone de livraison et rattachement à la ville</p>
        </div>
        <a href="<?= RACINE ?>quartier/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
          <i class="fa fa-arrow-left"></i> Retour aux quartiers
        </a>
      </div>

      <div class="card" style="border-radius: 14px; border: 1px solid #E2E8F0; padding: 24px; max-width: 600px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <form class="formEditQuartier" id="formEditQuartier">
          <?= Validator::csrfField() ?>
          <input type="hidden" id="id_quartier" name="id_quartier" value="<?= htmlspecialchars($quartier['id_quartier'] ?? '') ?>">

          <div class="form-group" style="margin-bottom: 16px;">
            <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Code Quartier</label>
            <input type="text" class="form-control" id="code_quartier" name="code_quartier"
                   value="<?= htmlspecialchars($quartier['code_quartier'] ?? '') ?>" readonly style="background: #F8FAFC; font-weight: 700;">
          </div>

          <div class="form-group" style="margin-bottom: 16px;">
            <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Ville de rattachement *</label>
            <select class="form-control" id="ville_code" name="ville_code" required>
              <option value="">Sélectionner une ville</option>
              <?php foreach ($villes as $v): ?>
                <option value="<?= htmlspecialchars($v['code_ville']) ?>" <?= ($quartier['ville_code'] ?? '') === $v['code_ville'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($v['libelle_ville']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group" style="margin-bottom: 16px;">
            <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Nom / Libellé du Quartier *</label>
            <input type="text" class="form-control" id="libelle_quartier" name="libelle_quartier"
                   value="<?= htmlspecialchars($quartier['libelle_quartier'] ?? '') ?>" required placeholder="Ex: Cocody, Plateau, Marcory...">
          </div>

          <div class="form-group" style="margin-bottom: 24px;">
            <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Statut</label>
            <select class="form-control" id="statut_quartier" name="statut_quartier">
              <option value="actif" <?= ($quartier['statut_quartier'] ?? 'actif') === 'actif' ? 'selected' : '' ?>>Actif</option>
              <option value="inactif" <?= ($quartier['statut_quartier'] ?? '') === 'inactif' ? 'selected' : '' ?>>Inactif</option>
            </select>
          </div>

          <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <a href="<?= RACINE ?>quartier/list" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary btn_actions" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
              <i data-lucide="save" style="width: 16px; height: 16px;"></i> Enregistrer les modifications
            </button>
          </div>
        </form>
      </div>

    </div>
  </main>
</div>

<script src="<?= RACINE ?>public/json/entities/quartiers.js?v=3"></script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
