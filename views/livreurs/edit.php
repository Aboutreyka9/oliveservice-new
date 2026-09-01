<?php
require_once __DIR__ . '/../../public/inc/header.php';
$livreur = isset($livreur) ? $livreur : [];
$pressings = isset($pressings) ? $pressings : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1><?= isset($livreur['id_livreur']) ? 'Modifier le livreur' : 'Ajouter un livreur' ?></h1>
          <p class="page-subtitle">Gestion des livreurs</p>
        </div>
        <a href="<?= RACINE ?>livreur/list" class="btn btn-sm btn-outline-secondary">
          <i data-lucide="arrow-left"></i>
          Retour à la liste
        </a>
      </div>

      <div class="form-card">
        <div class="card-header">
          <div>
            <h2>Informations du livreur</h2>
          </div>
          <?php if (isset($livreur['statut_livreur'])): ?>
            <span class="badge-status <?= $livreur['statut_livreur'] == 'actif' ? 'delivered' : 'cancelled' ?>">
              <?= $livreur['statut_livreur'] == 'actif' ? 'Actif' : 'Inactif' ?>
            </span>
          <?php endif; ?>
        </div>

        <div class="card-body">
          <form class="formEditLivreur">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_livreur" name="id_livreur" value="<?= htmlspecialchars($livreur['id_livreur'] ?? '') ?>">

             <div class="form-grid">
               <div class="form-field">
                 <label for="nom_livreur">Nom</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('user'); ?></span>
                   <input type="text" class="form-control" id="nom_livreur" name="nom_livreur"
                          value="<?= htmlspecialchars($livreur['nom_livreur'] ?? '') ?>" required placeholder="ex: Kouassi">
                 </div>
                 <div class="error-message" id="nomError"></div>
               </div>

               <div class="form-field">
                 <label for="prenom_livreur">Prénom</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('user'); ?></span>
                   <input type="text" class="form-control" id="prenom_livreur" name="prenom_livreur"
                          value="<?= htmlspecialchars($livreur['prenom_livreur'] ?? '') ?>" placeholder="ex: Jean">
                 </div>
                 <div class="error-message" id="prenomError"></div>
               </div>

               <div class="form-field">
                 <label for="telephone_livreur">Téléphone</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('phone'); ?></span>
                   <input type="text" class="form-control" id="telephone_livreur" name="telephone_livreur"
                          value="<?= htmlspecialchars($livreur['telephone_livreur'] ?? '') ?>" required placeholder="ex: +225 07 00 00 00 00">
                 </div>
                 <div class="error-message" id="telephoneError"></div>
               </div>

                <?php
                  $resolvedLivreurPressingCode = $currentPressingCode ?: ($livreur['pressing_code'] ?? '');
                ?>
                <input type="hidden" id="pressing_code" name="pressing_code" value="<?= htmlspecialchars($resolvedLivreurPressingCode) ?>">

               <?php if (isset($livreur['statut_livreur'])): ?>
               <div class="form-field">
                 <label for="actif">Statut</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('toggle-left'); ?></span>
                   <select class="form-control" id="actif" name="actif">
                     <option value="1" <?= ($livreur['statut_livreur'] ?? '') == 'actif' ? 'selected' : '' ?>>Actif</option>
                     <option value="0" <?= ($livreur['statut_livreur'] ?? '') == 'inactif' ? 'selected' : '' ?>>Inactif</option>
                   </select>
                 </div>
                 <div class="error-message" id="actifError"></div>
               </div>
               <?php endif; ?>
             </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary btn_actions btnEditLivreur" style="background: #1E3A5F; border-color: #1E3A5F;">
                <span class="btn-text">
                  <i data-lucide="save"></i>
                  Sauvegarder
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
  $('.formEditLivreur').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const btn = form.find('.btnEditLivreur');
    const isEdit = $('#id_livreur').val() !== '';
    const baseApi = (typeof LINK !== 'undefined') ? LINK : ((typeof RACINE !== 'undefined') ? RACINE : '/admin-lavex/');
    const url = isEdit ? (baseApi + 'livreur/edit') : (baseApi + 'livreur/add');

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
          loading(btn, false, '<i data-lucide="save"></i> Sauvegarder');
        }
        if (rep.status) {
          if (typeof showToast === 'function') showToast(rep.message || 'Livreur enregistré avec succès !', 'success');
          setTimeout(function() {
            window.location.href = baseApi + 'livreur/list';
          }, 700);
        } else {
          if (typeof showToast === 'function') showToast(rep.message || 'Erreur lors de l\'enregistrement', 'error');
        }
      },
      error: function(xhr) {
        if (typeof loading === 'function') {
          loading(btn, false, '<i data-lucide="save"></i> Sauvegarder');
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
