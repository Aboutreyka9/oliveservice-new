<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$cycles = (new ModelCycle())->getAll();
$filieres = (new ModelFiliere())->getAll();
$niveaux = (new ModelNiveau())->getAll();
$classes = (new ModelClasse())->getAll();
$salles = (new ModelSalle())->getAll();
$scolarites = (new ModelScolarite())->getAll();
$ues = [];
$matieres = (new ModelMatiere())->getAll();
$semestres = (new ModelSemestre())->getAll();
$etudiants = (new ModelEtudiant())->getAll();
$inscriptions = (new ModelInscription())->getAll();
$typeDepenses = (new ModelTypeDepense())->getAll();
$users = (new ModelUser())->getAll();
$enseignants = (new ModelEnseignant())->getAll();
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_note']) ? 'Éditer ' : 'Ajouter ' ?> Note / Évaluation</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Saisie des données du module Saisie des Notes & Évaluations</p>
        </div>
        <a href="<?= RACINE ?>note/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>note/<?= !empty($item['id_note']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_note'])): ?>
            <input type="hidden" name="id_note" value="<?= $item['id_note'] ?>">
          <?php endif; ?>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; width: 100%;">
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Étudiant inscrit <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_ins_no" style="width: 100%;" name="inscription_code" required>
                <option value="">-- Rechercher une inscription élève --</option>
                <?php foreach($inscriptions as $ins): ?>
                  <option value="<?= $ins['code_inscription'] ?>" <?= (($item['inscription_code'] ?? '') == $ins['code_inscription']) ? 'selected' : '' ?>><?= htmlspecialchars($ins['code_inscription']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Matière évaluée <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_mat_no" style="width: 100%;" name="matiere_code" required>
                <option value="">-- Rechercher une matière --</option>
                <?php foreach($matieres as $m): ?>
                  <option value="<?= $m['code_matiere'] ?>" <?= (($item['matiere_code'] ?? '') == $m['code_matiere']) ? 'selected' : '' ?>><?= htmlspecialchars($m['libelle_matiere']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Semestre académique <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_sem_no" style="width: 100%;" name="semestre_code" required>
                <option value="">-- Rechercher un semestre --</option>
                <?php foreach($semestres as $sm): ?>
                  <option value="<?= $sm['code_semestre'] ?>" <?= (($item['semestre_code'] ?? '') == $sm['code_semestre']) ? 'selected' : '' ?>><?= htmlspecialchars($sm['libelle_semestre']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Type d\'évaluation <span style="color: #EF4444;">*</span></label>
              <select class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="type_evaluation_code" required>
                <option value="Devoir" <?= (($item['type_evaluation_code'] ?? '') === 'Devoir') ? 'selected' : '' ?>>Devoir Surveillé</option>
                <option value="Examen" <?= (($item['type_evaluation_code'] ?? '') === 'Examen') ? 'selected' : '' ?>>Examen Semestriel</option>
                <option value="TP" <?= (($item['type_evaluation_code'] ?? '') === 'TP') ? 'selected' : '' ?>>Travaux Pratiques (TP)</option>
              </select>
            </div>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Note sur 20 <span style="color: #EF4444;">*</span></label>
              <input type="number"  class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="valeur_note" value="<?= htmlspecialchars($item['valeur_note'] ?? '') ?>" placeholder="Ex: 15.5" required>
            </div>
            <div class="form-group" style="width: 100%; box-sizing: border-box; grid-column: 1 / -1;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Appréciation du professeur</label>
              <textarea class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="appreciation" placeholder="Ex: Excellent travail, bonne maîtrise du sujet"  rows="3"><?= htmlspecialchars($item['appreciation'] ?? '') ?></textarea>
            </div>
          </div>
          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px;">Enregistrer</button>
            <a href="<?= RACINE ?>note/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 10px 24px;">Annuler</a>
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
    $('#sel_ins_no').select2({ placeholder: "-- Rechercher une inscription élève --", allowClear: true, width: '100%' });
    $('#sel_mat_no').select2({ placeholder: "-- Rechercher une matière --", allowClear: true, width: '100%' });
    $('#sel_sem_no').select2({ placeholder: "-- Rechercher un semestre --", allowClear: true, width: '100%' });
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
