<?php
require_once __DIR__ . '/../../public/inc/header.php';
$forfait = isset($forfait) ? $forfait : [];
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
            <i data-lucide="box" style="color: #2563EB;"></i> Modifier le Forfait : <?= htmlspecialchars($forfait['libelle_forfait'] ?? '') ?>
          </h1>
          <p class="page-subtitle" style="color: #64748B; margin: 4px 0 0 0;">Configuration de l'offre d'abonnement B2B pour les pressings</p>
        </div>
        <a href="<?= RACINE ?>forfait/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
          <i class="fa fa-arrow-left"></i> Retour aux forfaits
        </a>
      </div>

      <div class="card" style="border-radius: 14px; border: 1px solid #E2E8F0; padding: 24px; max-width: 600px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <form class="formEditForfait" id="formEditForfait">
          <?= Validator::csrfField() ?>
          <input type="hidden" id="id_forfait" name="id_forfait" value="<?= htmlspecialchars($forfait['id_forfait'] ?? '') ?>">

          <div class="form-group" style="margin-bottom: 16px;">
            <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Code Forfait</label>
            <input type="text" class="form-control" id="code_forfait" name="code_forfait"
                   value="<?= htmlspecialchars($forfait['code_forfait'] ?? '') ?>" readonly style="background: #F8FAFC; font-weight: 700;">
          </div>

          <div class="form-group" style="margin-bottom: 16px;">
            <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Nom du Forfait *</label>
            <input type="text" class="form-control" id="libelle_forfait" name="libelle_forfait"
                   value="<?= htmlspecialchars($forfait['libelle_forfait'] ?? '') ?>" required placeholder="Ex: Forfait Mensuel Standard">
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div class="form-group">
              <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Montant (FCFA) *</label>
              <input type="number" class="form-control" id="montant_forfait" name="montant_forfait"
                     value="<?= htmlspecialchars($forfait['montant_forfait'] ?? '0') ?>" required min="0" step="500" placeholder="ex: 15000">
            </div>

            <div class="form-group">
              <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Durée (Mois) *</label>
              <input type="number" class="form-control" id="duree_mois_forfait" name="duree_mois_forfait"
                     value="<?= htmlspecialchars($forfait['duree_mois_forfait'] ?? '1') ?>" required min="1" max="36" placeholder="ex: 1">
            </div>
          </div>

          <div class="form-group" style="margin-bottom: 16px;">
            <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Description générale</label>
            <textarea class="form-control" id="description_forfait" name="description_forfait" rows="2" placeholder="Détails du forfait..."><?= htmlspecialchars($forfait['description_forfait'] ?? '') ?></textarea>
          </div>

          <?php
          $advVal = '';
          if (!empty($forfait['avantages_forfait'])) {
              $decoded = json_decode($forfait['avantages_forfait'], true);
              if (is_array($decoded)) {
                  $advVal = implode("\n", $decoded);
              } else {
                  $advVal = $forfait['avantages_forfait'];
              }
          }
          ?>
          <div class="form-group" style="margin-bottom: 20px;">
            <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 4px;">
              Avantages & Fonctionnalités Incluses
            </label>
            <small style="display: block; font-size: 11px; color: #64748B; margin-bottom: 6px;">Entrez 1 avantage par ligne. Ils s'afficheront sous forme de liste avec coches ✓ dans l'application.</small>
            <textarea class="form-control" id="avantages_forfait" name="avantages_forfait" rows="5" placeholder="3 Mois offerts à l'inscription&#10;Jusqu'à 250 commandes mensuelles&#10;3 Comptes utilisateurs&#10;Badge 'Partenaire Certifié'"><?= htmlspecialchars($advVal) ?></textarea>
          </div>

          <div class="form-group" style="margin-bottom: 24px;">
            <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Statut</label>
            <select class="form-control" id="statut_forfait" name="statut_forfait">
              <option value="actif" <?= ($forfait['statut_forfait'] ?? 'actif') === 'actif' ? 'selected' : '' ?>>Actif</option>
              <option value="inactif" <?= ($forfait['statut_forfait'] ?? '') === 'inactif' ? 'selected' : '' ?>>Inactif</option>
            </select>
          </div>

          <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <a href="<?= RACINE ?>forfait/list" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary btn_actions" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
              <i data-lucide="save" style="width: 16px; height: 16px;"></i> Enregistrer les modifications
            </button>
          </div>
        </form>
      </div>

    </div>
  </main>
</div>

<script src="<?= RACINE ?>public/json/entities/forfaits.js?v=3"></script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
