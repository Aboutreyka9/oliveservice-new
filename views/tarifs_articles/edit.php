<?php
require_once __DIR__ . '/../../public/inc/header.php';
$tarif     = isset($tarif) ? $tarif : [];
$articles  = isset($articles) ? $articles : [];
$services  = isset($services) ? $services : [];
$pressings = isset($pressings) ? $pressings : [];
?>

<style>
/* === MOBILE PWA UX OPTIMIZATIONS FOR EDIT TARIF FORM === */
@media (max-width: 768px) {
  .content-wrapper {
    padding: 12px 10px 80px 10px !important;
  }
  .page-header {
    flex-direction: column !important;
    align-items: stretch !important;
    margin-bottom: 16px !important;
    gap: 10px !important;
  }
  .page-header .btn {
    width: 100% !important;
    justify-content: center !important;
    height: 44px !important;
  }
  .form-card {
    border-radius: 14px !important;
    padding: 16px !important;
  }
  .form-grid {
    grid-template-columns: 1fr !important;
    gap: 14px !important;
  }
  .form-actions {
    margin-top: 20px !important;
  }
  .form-actions .btn {
    width: 100% !important;
    height: 48px !important;
    font-size: 15px !important;
    justify-content: center !important;
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
          <h1><?= isset($tarif['id_tarif']) ? 'Modifier le tarif vêtement' : 'Ajouter un tarif vêtement' ?></h1>
          <p class="page-subtitle">Gestion de la grille tarifaire des vêtements et linge du pressing</p>
        </div>
        <a href="<?= RACINE ?>tarif/list" class="btn btn-sm btn-outline-secondary">
          <i data-lucide="arrow-left"></i>
          Retour à la liste
        </a>
      </div>

      <div class="form-card">
        <div class="card-header">
          <div>
            <h2>Configuration du tarif de prestation</h2>
          </div>
          <?php if (isset($tarif['statut_tarif'])): ?>
            <span class="badge-status <?= $tarif['statut_tarif'] == 'actif' ? 'delivered' : 'cancelled' ?>">
              <?= $tarif['statut_tarif'] == 'actif' ? 'Actif' : 'Inactif' ?>
            </span>
          <?php endif; ?>
        </div>

        <div class="card-body">
          <form class="formEditTarif">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_tarif" name="id_tarif" value="<?= htmlspecialchars($tarif['id_tarif'] ?? '') ?>">

              <div class="form-grid">
                <?php
                  $resolvedPressingCode = $currentPressingCode ?: ($tarif['pressing_code'] ?? ($pressings[0]['code_pressing'] ?? 'PRS-001'));
                ?>
                <input type="hidden" id="pressing_code" name="pressing_code" value="<?= htmlspecialchars($resolvedPressingCode) ?>">

               <div class="form-field">
                 <label for="article_code">Vêtement / Article du catalogue</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('shirt'); ?></span>
                   <select class="form-control" id="article_code" name="article_code" required>
                     <option value="">-- Sélectionner un vêtement / article --</option>
                     <?php foreach ($articles as $art): ?>
                       <?php 
                         $catLabel = !empty($art['libelle_categorie']) ? $art['libelle_categorie'] : ($art['categorie_article_code'] ?? '');
                       ?>
                       <option value="<?= htmlspecialchars($art['code_article']) ?>" <?= ($tarif['article_code'] ?? '') === $art['code_article'] ? 'selected' : '' ?>>
                         <?= htmlspecialchars($art['libelle_article']) ?><?= !empty($catLabel) ? ' (' . htmlspecialchars($catLabel) . ')' : '' ?>
                       </option>
                     <?php endforeach; ?>
                   </select>
                 </div>
                 <div class="error-message" id="articleError"></div>
               </div>

               <div class="form-field">
                 <label for="service_code">Service associé</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('sparkles'); ?></span>
                   <select class="form-control" id="service_code" name="service_code" required>
                     <option value="">-- Sélectionner un service --</option>
                     <?php foreach ($services as $srv): ?>
                       <option value="<?= htmlspecialchars($srv['code_service']) ?>" <?= ($tarif['service_code'] ?? '') === $srv['code_service'] ? 'selected' : '' ?>>
                         <?= htmlspecialchars($srv['libelle_service']) ?>
                       </option>
                     <?php endforeach; ?>
                   </select>
                 </div>
                 <div class="error-message" id="serviceError"></div>
               </div>

               <div class="form-field">
                 <label for="prix_tarif">Prix appliqué (FCFA)</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('dollar-sign'); ?></span>
                   <input type="number" class="form-control" id="prix_tarif" name="prix_tarif" min="100" step="50"
                          value="<?= htmlspecialchars($tarif['prix_tarif'] ?? 0) ?>" required placeholder="ex: 1500">
                 </div>
                 <div class="error-message" id="prixError"></div>
               </div>

               <?php if (isset($tarif['statut_tarif'])): ?>
               <div class="form-field">
                 <label for="actif">Statut</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('toggle-left'); ?></span>
                   <select class="form-control" id="actif" name="actif">
                     <option value="1" <?= ($tarif['statut_tarif'] ?? '') == 'actif' ? 'selected' : '' ?>>Actif</option>
                     <option value="0" <?= ($tarif['statut_tarif'] ?? '') == 'inactif' ? 'selected' : '' ?>>Inactif</option>
                   </select>
                 </div>
                 <div class="error-message" id="actifError"></div>
               </div>
               <?php endif; ?>
             </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary btn_actions btnEditTarif" style="background: #1E3A5F; border-color: #1E3A5F;">
                <span class="btn-text">
                  <i data-lucide="save"></i>
                  Enregistrer le tarif
                </span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  if ($.fn.select2) {
    $('#article_code').select2({
      placeholder: '-- Sélectionner un article --',
      allowClear: true,
      width: '100%'
    });

    $('#service_code').select2({
      placeholder: '-- Sélectionner un service --',
      allowClear: true,
      width: '100%'
    });
  }

  $('.formEditTarif').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const btn = form.find('.btnEditTarif');
    const isEdit = $('#id_tarif').val() !== '';
    const baseApi = (typeof LINK !== 'undefined') ? LINK : ((typeof RACINE !== 'undefined') ? RACINE : '/admin-lavex/');
    const url = isEdit ? (baseApi + 'tarif/edit') : (baseApi + 'tarif/add');

    if (typeof loading === 'function') {
      loading(btn, true, '<i class="fa fa-spinner fa-spin"></i> Enregistrement...');
    }

    $.ajax({
      url: url,
      type: 'POST',
      data: form.serialize(),
      dataType: 'json',
      success: function(rep) {
        if (typeof loading === 'function') {
          loading(btn, false, '<i data-lucide="save"></i> Enregistrer le tarif');
        }
        if (rep.status) {
          if (typeof showToast === 'function') showToast(rep.message || 'Tarif enregistré avec succès !', 'success');
          setTimeout(function() {
            window.location.href = baseApi + 'tarif/list';
          }, 700);
        } else {
          if (typeof showToast === 'function') showToast(rep.message || 'Erreur lors de l\'enregistrement', 'error');
        }
      },
      error: function(xhr) {
        if (typeof loading === 'function') {
          loading(btn, false, '<i data-lucide="save"></i> Enregistrer le tarif');
        }
        let msg = 'Erreur serveur';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
        if (typeof showToast === 'function') showToast(msg, 'error');
      }
    });
  });
});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
