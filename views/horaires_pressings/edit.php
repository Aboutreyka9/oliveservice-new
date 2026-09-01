<?php
require_once __DIR__ . '/../../public/inc/header.php';
$horaire   = isset($horaire) ? $horaire : [];
$pressings = isset($pressings) ? $pressings : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1><?= isset($horaire['id_horaire']) ? 'Modifier l\'horaire' : 'Ajouter un horaire' ?></h1>
          <p class="page-subtitle">Gestion des plages horaires d'ouverture</p>
        </div>
        <a href="<?= RACINE ?>horaire/list" class="btn btn-sm btn-outline-secondary">
          <i data-lucide="arrow-left"></i>
          Retour à la liste
        </a>
      </div>

      <div class="form-card">
        <div class="card-header">
          <div>
            <h2>Plage horaire</h2>
          </div>
        </div>

        <div class="card-body">
          <form class="formEditHoraire">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_horaire" name="id_horaire" value="<?= htmlspecialchars($horaire['id_horaire'] ?? '') ?>">

             <div class="form-grid">
                <?php
                  $resolvedHorairePressingCode = $currentPressingCode ?: ($horaire['pressing_code'] ?? ($pressings[0]['code_pressing'] ?? 'PRS-001'));
                ?>
                <input type="hidden" id="pressing_code" name="pressing_code" value="<?= htmlspecialchars($resolvedHorairePressingCode) ?>">

               <div class="form-field">
                 <label for="jour">Jour de la semaine</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('calendar'); ?></span>
                   <select class="form-control" id="jour" name="jour" required>
                     <option value="">-- Choisir un jour --</option>
                     <option value="lundi" <?= strtolower($horaire['jour'] ?? '') === 'lundi' ? 'selected' : '' ?>>Lundi</option>
                     <option value="mardi" <?= strtolower($horaire['jour'] ?? '') === 'mardi' ? 'selected' : '' ?>>Mardi</option>
                     <option value="mercredi" <?= strtolower($horaire['jour'] ?? '') === 'mercredi' ? 'selected' : '' ?>>Mercredi</option>
                     <option value="jeudi" <?= strtolower($horaire['jour'] ?? '') === 'jeudi' ? 'selected' : '' ?>>Jeudi</option>
                     <option value="vendredi" <?= strtolower($horaire['jour'] ?? '') === 'vendredi' ? 'selected' : '' ?>>Vendredi</option>
                     <option value="samedi" <?= strtolower($horaire['jour'] ?? '') === 'samedi' ? 'selected' : '' ?>>Samedi</option>
                     <option value="dimanche" <?= strtolower($horaire['jour'] ?? '') === 'dimanche' ? 'selected' : '' ?>>Dimanche</option>
                   </select>
                 </div>
                 <div class="error-message" id="jourError"></div>
               </div>

               <div class="form-field">
                 <label for="est_ferme">Jour de fermeture ?</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('toggle-left'); ?></span>
                   <select class="form-control" id="est_ferme" name="est_ferme" onchange="toggleHoraireInputs(this.value)">
                     <option value="0" <?= ($horaire['est_ferme'] ?? 0) == 0 ? 'selected' : '' ?>>Non (Ouvert)</option>
                     <option value="1" <?= ($horaire['est_ferme'] ?? 0) == 1 ? 'selected' : '' ?>>Oui (Fermé toute la journée)</option>
                   </select>
                 </div>
                 <div class="error-message" id="fermeError"></div>
               </div>

               <div class="form-field horaire-time-field" style="<?= ($horaire['est_ferme'] ?? 0) == 1 ? 'opacity: 0.5; pointer-events: none;' : '' ?>">
                 <label for="heure_ouverture">Heure d'ouverture</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('clock'); ?></span>
                   <input type="time" class="form-control" id="heure_ouverture" name="heure_ouverture"
                          value="<?= htmlspecialchars(substr($horaire['heure_ouverture'] ?? '08:00', 0, 5)) ?>">
                 </div>
                 <div class="error-message" id="ouvertureError"></div>
               </div>

               <div class="form-field horaire-time-field" style="<?= ($horaire['est_ferme'] ?? 0) == 1 ? 'opacity: 0.5; pointer-events: none;' : '' ?>">
                 <label for="heure_fermeture">Heure de fermeture</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('clock'); ?></span>
                   <input type="time" class="form-control" id="heure_fermeture" name="heure_fermeture"
                          value="<?= htmlspecialchars(substr($horaire['heure_fermeture'] ?? '18:00', 0, 5)) ?>">
                 </div>
                 <div class="error-message" id="fermetureError"></div>
               </div>
             </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary btn_actions btnEditHoraire" style="background: #1E3A5F; border-color: #1E3A5F;">
                <span class="btn-text">
                  <i data-lucide="save"></i>
                  Enregistrer l'horaire
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
function toggleHoraireInputs(isFerme) {
  const fields = document.querySelectorAll('.horaire-time-field');
  fields.forEach(f => {
    if (isFerme == 1) {
      f.style.opacity = '0.5';
      f.style.pointerEvents = 'none';
    } else {
      f.style.opacity = '1';
      f.style.pointerEvents = 'auto';
    }
  });
}

$(document).ready(function() {
  $('.formEditHoraire').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const btn = form.find('.btnEditHoraire');
    const isEdit = $('#id_horaire').val() !== '';
    const baseApi = (typeof LINK !== 'undefined') ? LINK : ((typeof RACINE !== 'undefined') ? RACINE : '/admin-lavex/');
    const url = isEdit ? (baseApi + 'horaire/edit') : (baseApi + 'horaire/add');

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
          loading(btn, false, '<i data-lucide="save"></i> Enregistrer l\'horaire');
        }
        if (rep.status) {
          if (typeof showToast === 'function') showToast(rep.message || 'Horaire enregistré avec succès !', 'success');
          setTimeout(function() {
            window.location.href = baseApi + 'horaire/list';
          }, 700);
        } else {
          if (typeof showToast === 'function') showToast(rep.message || 'Erreur lors de l\'enregistrement', 'error');
        }
      },
      error: function(xhr) {
        if (typeof loading === 'function') {
          loading(btn, false, '<i data-lucide="save"></i> Enregistrer l\'horaire');
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
