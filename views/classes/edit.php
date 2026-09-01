<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$filieres = (new ModelFiliere())->getAll();
$niveaux = (new ModelNiveau())->getAll();
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_classe']) ? 'Éditer ' : 'Ajouter ' ?> Classe / Promotion</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Création intelligente et gestion des classes et promotions</p>
        </div>
        <a href="<?= RACINE ?>classe/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>classe/<?= !empty($item['id_classe']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_classe'])): ?>
            <input type="hidden" name="id_classe" value="<?= $item['id_classe'] ?>">
          <?php endif; ?>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; width: 100%;">
            
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Filière rattachée <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_filiere_classe" name="filiere_code" style="width: 100%;" required>
                <option value="">-- Choisir une filière --</option>
                <?php foreach($filieres as $f): ?>
                  <option value="<?= htmlspecialchars($f['code_filiere']) ?>" data-nom="<?= htmlspecialchars($f['libelle_filiere']) ?>" <?= (($item['filiere_code'] ?? '') == $f['code_filiere']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['libelle_filiere']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Niveau d'études <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_niveau_classe" name="niveau_code" style="width: 100%;" required>
                <option value="">-- Choisir un niveau --</option>
                <?php foreach($niveaux as $n): ?>
                  <option value="<?= htmlspecialchars($n['code_niveau']) ?>" data-nom="<?= htmlspecialchars($n['libelle_niveau']) ?>" <?= (($item['niveau_code'] ?? '') == $n['code_niveau']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($n['libelle_niveau']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
                Libellé de la classe <span style="color: #EF4444;">*</span>
                <span style="font-size: 11px; font-weight: 500; color: #64748B; margin-left: 6px;">(Automatique)</span>
              </label>
              <input type="text" id="libelle_classe_input" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; font-weight: 600; outline: none; transition: border-color 0.2s;" name="libelle_classe" value="<?= htmlspecialchars($item['libelle_classe'] ?? '') ?>" placeholder="Ex: IDA - Première année" required>
              <small style="color: #64748B; font-size: 12px; margin-top: 4px; display: block;">Rempli automatiquement selon la filière et le niveau sélectionnés.</small>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Capacité maximale d'accueil</label>
              <input type="number" min="1" max="500" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="capacite_max_classe" value="<?= htmlspecialchars($item['capacite_max_classe'] ?? '35') ?>" placeholder="Ex: 35">
            </div>

          </div>
          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px;">Enregistrer</button>
            <a href="<?= RACINE ?>classe/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 10px 24px;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  if ($.fn.select2) {
    $('#sel_filiere_classe').select2({ placeholder: "-- Choisir une filière --", allowClear: true, width: '100%' });
    $('#sel_niveau_classe').select2({ placeholder: "-- Choisir un niveau --", allowClear: true, width: '100%' });
  }

  // Génération automatique intelligente du libellé de la classe
  function updateLibelleClasse() {
    var filiereText = $('#sel_filiere_classe option:selected').data('nom') || $('#sel_filiere_classe option:selected').text().trim();
    var niveauText  = $('#sel_niveau_classe option:selected').data('nom') || $('#sel_niveau_classe option:selected').text().trim();
    
    // Si placeholder sélectionné
    if (!$('#sel_filiere_classe').val()) filiereText = '';
    if (!$('#sel_niveau_classe').val()) niveauText = '';

    if (filiereText && niveauText) {
      $('#libelle_classe_input').val(filiereText + ' - ' + niveauText);
    } else if (filiereText) {
      $('#libelle_classe_input').val(filiereText);
    } else if (niveauText) {
      $('#libelle_classe_input').val(niveauText);
    }
  }

  $('#sel_filiere_classe, #sel_niveau_classe').on('change', function() {
    updateLibelleClasse();
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
