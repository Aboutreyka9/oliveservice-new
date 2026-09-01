<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$annees = (new ModelAnnee())->getAll();
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
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_scolarite']) ? 'Éditer ' : 'Ajouter ' ?> Tarif de Scolarité</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Configuration des grilles tarifaires par année, filière et niveau d'études</p>
        </div>
        <a href="<?= RACINE ?>scolarite/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>scolarite/<?= !empty($item['id_scolarite']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_scolarite'])): ?>
            <input type="hidden" name="id_scolarite" value="<?= $item['id_scolarite'] ?>">
          <?php endif; ?>

          <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 8px;">
            <i data-lucide="calculator" style="width: 18px; height: 18px;"></i> Paramètres du Tarif de Scolarité
          </h3>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; width: 100%;">
            
            <!-- Année Académique (Select2) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Année Académique <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_annee_scolarite" name="annee_code" style="width: 100%;" required>
                <option value="">-- Choisir une année --</option>
                <?php foreach($annees as $a): ?>
                  <option value="<?= htmlspecialchars($a['code_annee']) ?>" <?= (($item['annee_code'] ?? ($_SESSION['annee_active_code'] ?? '')) == $a['code_annee']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['libelle_annee']) ?> <?= (($a['statut_annee'] ?? '') === 'actif') ? '(En cours)' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Filière (Select2) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Filière rattachée <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_filiere_scolarite" name="filiere_code" style="width: 100%;" required>
                <option value="">-- Choisir une filière --</option>
                <?php foreach($filieres as $f): ?>
                  <option value="<?= htmlspecialchars($f['code_filiere']) ?>" <?= (($item['filiere_code'] ?? '') == $f['code_filiere']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['libelle_filiere']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Niveau d'études (Select2) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Niveau d'études <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_niveau_scolarite" name="niveau_code" style="width: 100%;" required>
                <option value="">-- Choisir un niveau --</option>
                <?php foreach($niveaux as $n): ?>
                  <option value="<?= htmlspecialchars($n['code_niveau']) ?>" <?= (($item['niveau_code'] ?? '') == $n['code_niveau']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($n['libelle_niveau']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Régime / Statut d'affectation (Select statique) -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut d'Affectation <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_affectation_scolarite" name="affectation_etat" style="width: 100%;" required>
                <option value="non_affecte" <?= (($item['affectation_etat'] ?? 'non_affecte') === 'non_affecte') ? 'selected' : '' ?>>Non Affecté (Privé)</option>
                <option value="affecte" <?= (($item['affectation_etat'] ?? '') === 'affecte') ? 'selected' : '' ?>>Affecté (de l'État)</option>
              </select>
            </div>

            <!-- Montant annuel -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Montant annuel (FCFA) <span style="color: #EF4444;">*</span></label>
              <input type="number" step="1000" min="0" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="montant_scolarite" value="<?= htmlspecialchars($item['montant_scolarite'] ?? '') ?>" placeholder="Ex: 650000" required>
            </div>

          </div>

          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px;">Enregistrer</button>
            <a href="<?= RACINE ?>scolarite/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 10px 24px;">Annuler</a>
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
    $('#sel_annee_scolarite').select2({ placeholder: "-- Choisir une année --", allowClear: true, width: '100%' });
    $('#sel_filiere_scolarite').select2({ placeholder: "-- Choisir une filière --", allowClear: true, width: '100%' });
    $('#sel_niveau_scolarite').select2({ placeholder: "-- Choisir un niveau --", allowClear: true, width: '100%' });
    $('#sel_affectation_scolarite').select2({ minimumResultsForSearch: Infinity, width: '100%' });
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
