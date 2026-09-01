<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$annees = (new ModelAnnee())->getAll();
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_semestre']) ? 'Éditer ' : 'Ajouter ' ?> Semestre</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Configuration des périodes académiques semestrielles</p>
        </div>
        <a href="<?= RACINE ?>semestre/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>semestre/<?= !empty($item['id_semestre']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_semestre'])): ?>
            <input type="hidden" name="id_semestre" value="<?= $item['id_semestre'] ?>">
          <?php endif; ?>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; width: 100%;">
            
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Libellé du Semestre <span style="color: #EF4444;">*</span></label>
              <?php 
                $curLib = strtoupper(trim($item['libelle_semestre'] ?? '')); 
                $isSem1 = ($curLib === 'SEMESTRE 1' || $curLib === 'SEMESTRE1' || $curLib === 'S1');
                $isSem2 = ($curLib === 'SEMESTRE 2' || $curLib === 'SEMESTRE2' || $curLib === 'S2');
              ?>
              <select class="form-control select2" id="sel_libelle_semestre" name="libelle_semestre" style="width: 100%;" required>
                <option value="">-- Choisir un semestre --</option>
                <option value="Semestre 1" <?= $isSem1 ? 'selected' : '' ?>>Semestre 1 (S1)</option>
                <option value="Semestre 2" <?= $isSem2 ? 'selected' : '' ?>>Semestre 2 (S2)</option>
              </select>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Année Académique <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_annee_semestre" name="annee_code" style="width: 100%;" required>
                <option value="">-- Sélectionner une année académique --</option>
                <?php 
                  $currentAnneeCode = $item['annee_code'] ?? ($_SESSION['annee_active_code'] ?? '');
                  foreach($annees as $an): 
                ?>
                  <option value="<?= htmlspecialchars($an['code_annee']) ?>" <?= ($currentAnneeCode == $an['code_annee']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($an['libelle_annee']) ?> <?= ($an['statut_annee'] === 'actif') ? '(En cours)' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Date de début</label>
              <input type="date" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="date_debut_semestre" value="<?= htmlspecialchars($item['date_debut_semestre'] ?? '') ?>">
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Date de fin</label>
              <input type="date" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="date_fin_semestre" value="<?= htmlspecialchars($item['date_fin_semestre'] ?? '') ?>">
            </div>

          </div>
          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px;">Enregistrer</button>
            <a href="<?= RACINE ?>semestre/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 10px 24px;">Annuler</a>
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
    $('#sel_libelle_semestre').select2({
      placeholder: "-- Choisir un semestre --",
      allowClear: true,
      width: '100%'
    });
    $('#sel_annee_semestre').select2({
      placeholder: "-- Sélectionner une année académique --",
      allowClear: true,
      width: '100%'
    });
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
