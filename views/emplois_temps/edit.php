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
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_emploi']) ? 'Éditer ' : 'Ajouter ' ?> Créneau Horaire</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Saisie des données du module Emplois du Temps</p>
        </div>
        <a href="<?= RACINE ?>emploi/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>emploi/<?= !empty($item['id_emploi']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_emploi'])): ?>
            <input type="hidden" name="id_emploi" value="<?= $item['id_emploi'] ?>">
          <?php endif; ?>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; width: 100%;">
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Classe concernée <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_cls_et" style="width: 100%;" name="classe_code" required>
                <option value="">-- Rechercher une classe --</option>
                <?php foreach($classes as $cl): ?>
                  <option value="<?= $cl['code_classe'] ?>" <?= (($item['classe_code'] ?? '') == $cl['code_classe']) ? 'selected' : '' ?>><?= htmlspecialchars($cl['libelle_classe']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Matière dispensée <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_mat_et" style="width: 100%;" name="matiere_code" required>
                <option value="">-- Rechercher une matière --</option>
                <?php foreach($matieres as $m): ?>
                  <option value="<?= $m['code_matiere'] ?>" <?= (($item['matiere_code'] ?? '') == $m['code_matiere']) ? 'selected' : '' ?>><?= htmlspecialchars($m['libelle_matiere']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Enseignant responsable <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_ens_et" style="width: 100%;" name="enseignant_code" required>
                <option value="">-- Rechercher un enseignant --</option>
                <?php foreach($enseignants as $ens): ?>
                  <?php 
                    $nomProf = trim(($ens['nom_enseignant'] ?? '') . ' ' . ($ens['prenom_enseignant'] ?? ''));
                    $gradeProf = !empty($ens['grade_enseignant']) ? " ({$ens['grade_enseignant']})" : '';
                    $labelProf = $nomProf ?: ($ens['code_enseignant'] ?? 'Enseignant');
                  ?>
                  <option value="<?= $ens['code_enseignant'] ?>" <?= (($item['enseignant_code'] ?? '') == $ens['code_enseignant']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($labelProf . $gradeProf) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div id="teacher-assigned-badge" style="margin-top: 4px; display: none;"></div>
            </div>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Salle de cours <span style="color: #EF4444;">*</span></label>
              <select class="form-control select2" id="sel_sal_et" style="width: 100%;" name="salle_code" required>
                <option value="">-- Rechercher une salle --</option>
                <?php foreach($salles as $s): ?>
                  <option value="<?= $s['code_salle'] ?>" <?= (($item['salle_code'] ?? '') == $s['code_salle']) ? 'selected' : '' ?>><?= htmlspecialchars($s['libelle_salle']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Jour de la semaine <span style="color: #EF4444;">*</span></label>
              <select class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="jour" required>
                <?php foreach(['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'] as $j): ?>
                  <option value="<?= strtolower($j) ?>" <?= (strtolower($item['jour'] ?? '') === strtolower($j)) ? 'selected' : '' ?>><?= $j ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Heure de début <span style="color: #EF4444;">*</span></label>
              <input type="time" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="heure_debut" value="<?= htmlspecialchars($item['heure_debut'] ?? '') ?>" placeholder="Ex: 08:00" required>
            </div>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Heure de fin <span style="color: #EF4444;">*</span></label>
              <input type="time" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="heure_fin" value="<?= htmlspecialchars($item['heure_fin'] ?? '') ?>" placeholder="Ex: 11:30" required>
            </div>
          </div>

          <!-- BANDEAU DYNAMIQUE DE DÉTECTION DES CONFLITS -->
          <div id="schedule-conflict-banner" style="display: none; margin-top: 24px; border-radius: 10px; padding: 16px 20px; transition: all 0.3s ease;">
            <div style="display: flex; align-items: flex-start; gap: 14px;">
              <div id="conflict-icon-container" style="width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i id="conflict-icon" data-lucide="check-circle" style="width: 22px; height: 22px;"></i>
              </div>
              <div style="flex-grow: 1;">
                <div id="conflict-status-title" style="font-weight: 800; font-size: 14px;">Vérification de la disponibilité</div>
                <div id="conflict-status-subtitle" style="font-size: 13px; margin-top: 3px;">Analyse du créneau horaire...</div>
                <div id="conflict-items-list" style="margin-top: 10px; display: flex; flex-direction: column; gap: 8px;"></div>
              </div>
            </div>
          </div>

          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" id="btn-submit-emploi" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px;">Enregistrer</button>
            <a href="<?= RACINE ?>emploi/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 10px 24px;">Annuler</a>
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
    $('#sel_cls_et').select2({ placeholder: "-- Rechercher une classe --", allowClear: true, width: '100%' });
    $('#sel_mat_et').select2({ placeholder: "-- Rechercher une matière --", allowClear: true, width: '100%' });
    $('#sel_ens_et').select2({ placeholder: "-- Rechercher un enseignant --", allowClear: true, width: '100%' });
    $('#sel_sal_et').select2({ placeholder: "-- Rechercher une salle --", allowClear: true, width: '100%' });
  }

  // 1. Auto-Sélection Enseignant sur choix Classe + Matière
  function checkAndAutoSelectTeacher() {
    var classeCode = $('#sel_cls_et').val();
    var matiereCode = $('#sel_mat_et').val();

    if (!classeCode || !matiereCode) {
      $('#teacher-assigned-badge').slideUp(150);
      return;
    }

    $.ajax({
      url: '<?= RACINE ?>emploi/getAssignedTeacher',
      type: 'GET',
      data: { classe_code: classeCode, matiere_code: matiereCode },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.data && res.data.enseignant_code) {
          var ensCode = res.data.enseignant_code;
          var currentVal = $('#sel_ens_et').val();
          if (currentVal !== ensCode) {
            $('#sel_ens_et').val(ensCode).trigger('change');
          }
          $('#teacher-assigned-badge').html(
            '<div style="background:#EFF6FF; border:1px solid #BFDBFE; color:#1E3A5F; font-size:12px; font-weight:700; padding:6px 12px; border-radius:6px; margin-top:6px; display:flex; align-items:center; gap:6px;">' +
            '<i data-lucide="sparkles" style="width:14px;height:14px;color:#2563EB;"></i> Enseignant titulaire détecté : ' + res.data.nom_complet + ' (' + (res.data.grade || 'Titulaire') + ')' +
            '</div>'
          ).slideDown(200);
          if (window.lucide) lucide.createIcons();
        } else {
          $('#teacher-assigned-badge').slideUp(150);
        }
      }
    });
  }

  // 2. Contrôle de Conflits en Temps Réel
  var conflictTimer = null;
  function checkScheduleConflicts() {
    clearTimeout(conflictTimer);
    conflictTimer = setTimeout(function() {
      var classeCode = $('#sel_cls_et').val();
      var salleCode = $('#sel_sal_et').val();
      var enseignantCode = $('#sel_ens_et').val();
      var jour = $('select[name="jour"]').val();
      var heureDebut = $('input[name="heure_debut"]').val();
      var heureFin = $('input[name="heure_fin"]').val();
      var idEmploi = $('input[name="id_emploi"]').val() || '';

      if (!jour || !heureDebut || !heureFin) {
        $('#schedule-conflict-banner').slideUp(200);
        return;
      }

      $.ajax({
        url: '<?= RACINE ?>emploi/checkScheduleConflicts',
        type: 'GET',
        data: {
          classe_code: classeCode,
          salle_code: salleCode,
          enseignant_code: enseignantCode,
          jour: jour,
          heure_debut: heureDebut,
          heure_fin: heureFin,
          id_emploi: idEmploi
        },
        dataType: 'json',
        success: function(res) {
          if (res.status === 1) {
            var banner = $('#schedule-conflict-banner');
            var iconBox = $('#conflict-icon-container');
            var icon = $('#conflict-icon');
            var title = $('#conflict-status-title');
            var subtitle = $('#conflict-status-subtitle');
            var list = $('#conflict-items-list');

            list.empty();

            if (res.has_conflict && res.conflicts && res.conflicts.length > 0) {
              banner.css({ 'background': '#FEF2F2', 'border': '1.5px solid #FCA5A5' });
              iconBox.css({ 'background': '#FEE2E2', 'color': '#DC2626' });
              icon.attr('data-lucide', 'alert-triangle');
              title.css('color', '#991B1B').text('Attention : Conflit d\'emploi du temps détecté !');
              subtitle.css('color', '#B91C1C').text('Ce créneau horaire chevauche un ou plusieurs cours existants :');

              res.conflicts.forEach(function(c) {
                var itemHtml = '<div style="background:#FFFFFF; border:1px solid #FECACA; border-radius:8px; padding:10px 14px; font-size:12.5px; color:#7F1D1D;">' +
                  '<div style="font-weight:800; display:flex; align-items:center; gap:6px; margin-bottom:3px;">' +
                  '<span class="badge" style="background:#DC2626; color:#FFF; font-size:10.5px; padding:2px 6px; border-radius:4px; text-transform:uppercase;">' + c.type + '</span> ' +
                  c.title +
                  '</div>' +
                  '<div>' + c.message + '</div>' +
                  '</div>';
                list.append(itemHtml);
              });

              $('#btn-submit-emploi').prop('disabled', true).css('opacity', '0.6').attr('title', 'Veuillez résoudre le conflit avant d\'enregistrer');
              banner.stop(true, true).slideDown(250);
            } else {
              banner.css({ 'background': '#F0FDF4', 'border': '1.5px solid #BBF7D0' });
              iconBox.css({ 'background': '#DCFCE7', 'color': '#15803D' });
              icon.attr('data-lucide', 'check-circle-2');
              title.css('color', '#166534').text('Créneau Parfaitement Disponible');
              subtitle.css('color', '#15803D').text('La salle, l\'enseignant et la classe sont 100% libres sur cette plage horaire.');
              $('#btn-submit-emploi').prop('disabled', false).css('opacity', '1').removeAttr('title');
              banner.stop(true, true).slideDown(250);
            }
            if (window.lucide) lucide.createIcons();
          }
        }
      });
    }, 150);
  }

  // Événements
  $('#sel_cls_et, #sel_mat_et').on('change select2:select', function() {
    checkAndAutoSelectTeacher();
    checkScheduleConflicts();
  });

  $('#sel_ens_et, #sel_sal_et, select[name="jour"], input[name="heure_debut"], input[name="heure_fin"]').on('change select2:select input', function() {
    checkScheduleConflicts();
  });

  // Exécution initiale si des champs sont déjà renseignés (ex: mode édition)
  if ($('input[name="heure_debut"]').val() && $('input[name="heure_fin"]').val()) {
    checkScheduleConflicts();
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
