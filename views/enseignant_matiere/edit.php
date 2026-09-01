<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$classes = (new ModelClasse())->getAll();
$matieres = (new ModelMatiere())->getAll();
// Récupérer les enseignants avec le nom direct ou de l'utilisateur associé
$sqlEns = "SELECT e.*, CONCAT(COALESCE(e.nom_enseignant, u.nom_user, ''), ' ', COALESCE(e.prenom_enseignant, u.prenom_user, '')) AS nom_complet 
           FROM enseignants e 
           LEFT JOIN users u ON u.code_user = e.user_code 
           ORDER BY nom_complet ASC";
$enseignants = (new ModelEnseignant())->getCon()->query($sqlEns)->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_enseignant_matiere']) ? 'Éditer ' : 'Ajouter ' ?> Affectation de Cours</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Affectation des matières, des enseignants et des coefficients par classe</p>
        </div>
        <a href="<?= RACINE ?>enseignant_matiere/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>enseignant_matiere/<?= !empty($item['id_enseignant_matiere']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_enseignant_matiere'])): ?>
            <input type="hidden" name="id_enseignant_matiere" value="<?= $item['id_enseignant_matiere'] ?>">
          <?php endif; ?>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; width: 100%;">
            
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Enseignant <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_ens_em" style="width: 100%;" name="enseignant_code" required>
                <option value="">-- Rechercher un enseignant --</option>
                <?php foreach($enseignants as $ens): ?>
                  <?php 
                    $nomAffiche = !empty(trim($ens['nom_complet'])) ? $ens['nom_complet'] : $ens['code_enseignant'];
                  ?>
                  <option value="<?= $ens['code_enseignant'] ?>" <?= (($item['enseignant_code'] ?? '') == $ens['code_enseignant']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($nomAffiche . ' (' . $ens['code_enseignant'] . ')') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Matière affectée <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_mat_em" style="width: 100%;" name="matiere_code" required>
                <option value="">-- Rechercher une matière --</option>
                <?php foreach($matieres as $m): ?>
                  <option value="<?= $m['code_matiere'] ?>" <?= (($item['matiere_code'] ?? '') == $m['code_matiere']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['libelle_matiere']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Classe attribuée <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_cls_em" style="width: 100%;" name="classe_code" required>
                <option value="">-- Rechercher une classe --</option>
                <?php foreach($classes as $cl): ?>
                  <option value="<?= $cl['code_classe'] ?>" <?= (($item['classe_code'] ?? '') == $cl['code_classe']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cl['libelle_classe']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Coefficient (Pondération) <span style="color: #EF4444;">*</span></label>
              <input type="number" step="0.1" min="0.1" max="50" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="coefficient" value="<?= htmlspecialchars($item['coefficient'] ?? '1.0') ?>" placeholder="Ex: 3.0" required>
              <small style="color: #64748B; font-size: 12px; margin-top: 4px; display: block;">Coefficient spécifique appliqué à cette classe.</small>
            </div>

          </div>
          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px;">Enregistrer</button>
            <a href="<?= RACINE ?>enseignant_matiere/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 10px 24px;">Annuler</a>
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
    $('#sel_ens_em').select2({ placeholder: "-- Rechercher un enseignant --", allowClear: true, width: '100%' });
    $('#sel_mat_em').select2({ placeholder: "-- Rechercher une matière --", allowClear: true, width: '100%' });
    $('#sel_cls_em').select2({ placeholder: "-- Rechercher une classe --", allowClear: true, width: '100%' });
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
