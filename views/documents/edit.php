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
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_document']) ? 'Éditer ' : 'Ajouter ' ?> Document / Support de Cours</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Saisie des données du module Centre de Documents & Supports Pédagogiques</p>
        </div>
        <a href="<?= RACINE ?>document/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>

      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>document/<?= !empty($item['id_document']) ? 'edit' : 'add' ?>" method="POST" enctype="multipart/form-data" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_document'])): ?>
            <input type="hidden" name="id_document" value="<?= $item['id_document'] ?>">
          <?php endif; ?>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; width: 100%;">
            
            <!-- Nom / Titre du document -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nom / Titre du document <span style="color: #EF4444;">*</span></label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="libelle_document" value="<?= htmlspecialchars($item['libelle_document'] ?? '') ?>" placeholder="Ex: Support de cours - Algorithmique & Programmation PHP" required>
            </div>

            <!-- Type / Format du document -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Format / Catégorie du document</label>
              <select class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="type_document" id="type_document">
                <option value="pdf" <?= (($item['type_document'] ?? '') === 'pdf') ? 'selected' : '' ?>>Document PDF (.pdf)</option>
                <option value="word" <?= (($item['type_document'] ?? '') === 'word') ? 'selected' : '' ?>>Document Word (.doc, .docx)</option>
                <option value="powerpoint" <?= (($item['type_document'] ?? '') === 'powerpoint') ? 'selected' : '' ?>>Présentation PowerPoint (.ppt, .pptx)</option>
                <option value="excel" <?= (($item['type_document'] ?? '') === 'excel') ? 'selected' : '' ?>>Feuille de calcul Excel (.xls, .xlsx)</option>
                <option value="archive" <?= (($item['type_document'] ?? '') === 'archive') ? 'selected' : '' ?>>Archive compressée (.zip, .rar)</option>
                <option value="image" <?= (($item['type_document'] ?? '') === 'image') ? 'selected' : '' ?>>Image / Schéma (.jpg, .png)</option>
                <option value="autre" <?= (($item['type_document'] ?? '') === 'autre') ? 'selected' : '' ?>>Autre format / Support externe</option>
              </select>
            </div>

            <!-- Filière ciblée -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Filière ciblée</label>
              <select class="form-control select2" style="width: 100%; box-sizing: border-box;" name="filiere_code">
                <option value="">-- Toutes les filières (Général) --</option>
                <?php foreach($filieres as $f): ?>
                  <option value="<?= $f['code_filiere'] ?>" <?= (($item['filiere_code'] ?? '') == $f['code_filiere']) ? 'selected' : '' ?>><?= htmlspecialchars($f['libelle_filiere']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Niveau d'études -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Niveau d'études</label>
              <select class="form-control select2" style="width: 100%; box-sizing: border-box;" name="niveaux_code">
                <option value="">-- Tous les niveaux --</option>
                <?php foreach($niveaux as $n): ?>
                  <option value="<?= $n['code_niveau'] ?>" <?= (($item['niveaux_code'] ?? '') == $n['code_niveau']) ? 'selected' : '' ?>><?= htmlspecialchars($n['libelle_niveau']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- ========================================================================= -->
            <!-- ZONE DE TÉLÉVERSEMENT (UPLOAD) ET LIEN URL -->
            <!-- ========================================================================= -->
            <div class="form-group" style="width: 100%; box-sizing: border-box; grid-column: 1 / -1;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                Fichier / Support de cours <span style="color: #EF4444;">*</span>
              </label>

              <!-- Onglets Uploader / URL -->
              <div style="display: flex; gap: 0; margin-bottom: 14px; border-radius: 8px; overflow: hidden; border: 1px solid #CBD5E1; width: fit-content;">
                <button type="button" id="tab_upload" onclick="switchDocTab('upload')" style="padding: 9px 22px; font-size: 13px; font-weight: 700; background: #1E3A5F; color: #FFFFFF; border: none; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;">
                  <i data-lucide="upload-cloud" style="width: 16px; height: 16px;"></i> Uploader un fichier
                </button>
                <button type="button" id="tab_url" onclick="switchDocTab('url')" style="padding: 9px 22px; font-size: 13px; font-weight: 700; background: #F1F5F9; color: #475569; border: none; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s;">
                  <i data-lucide="link" style="width: 16px; height: 16px;"></i> Saisir une URL / Lien
                </button>
              </div>

              <!-- PANNEAU 1 : DRAG & DROP UPLOAD -->
              <div id="panel_upload">
                <label id="doc_drop_zone" for="fichier_upload" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; border: 2px dashed #CBD5E1; border-radius: 12px; padding: 32px 20px; cursor: pointer; background: #F8FAFC; transition: all 0.2s; min-height: 150px; text-align: center;">
                  <div style="width: 52px; height: 52px; border-radius: 12px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="file-up" style="width: 28px; height: 28px;"></i>
                  </div>
                  <div>
                    <span style="font-size: 14.5px; font-weight: 700; color: #1E3A5F; display: block;">Cliquez ou glissez-déposez votre document ici</span>
                    <span style="font-size: 12px; color: #64748B; margin-top: 4px; display: block;">Formats acceptés : PDF, Word (.docx), Excel (.xlsx), PowerPoint (.pptx), Zip, Images (max 50 Mo)</span>
                  </div>
                  <input type="file" id="fichier_upload" name="fichier_upload" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.rtf,.odt,.ods,.odp,.zip,.rar,.7z,image/*" style="display:none;">
                </label>

                <!-- Zone de prévisualisation du document uploadé -->
                <div id="doc_preview_zone" style="display: none; margin-top: 14px; border-radius: 10px; padding: 16px; border: 1.5px solid #BFDBFE; background: #EFF6FF; position: relative;">
                  <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                      <div id="doc_icon_box" style="width: 44px; height: 44px; border-radius: 10px; background: #FFFFFF; color: #1E3A5F; display: flex; align-items: center; justify-content: center; border: 1px solid #BFDBFE;">
                        <i data-lucide="file-text" id="doc_type_icon" style="width: 24px; height: 24px;"></i>
                      </div>
                      <div>
                        <div id="doc_file_name" style="font-size: 14px; font-weight: 800; color: #0F172A;">document.pdf</div>
                        <div id="doc_file_size" style="font-size: 12px; color: #64748B; margin-top: 2px;">0.00 Mo</div>
                      </div>
                    </div>
                    <button type="button" onclick="clearDocFile()" class="btn btn-sm" style="background: #FFFFFF; color: #DC2626; border: 1px solid #FECACA; border-radius: 6px; font-weight: 700; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                      <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i> Retirer le fichier
                    </button>
                  </div>
                </div>
              </div>

              <!-- PANNEAU 2 : SAISIE D'UNE URL EXTERNE -->
              <div id="panel_url" style="display: none;">
                <input type="text" class="form-control" id="url_fichier_input" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" placeholder="Ex: https://monsite.com/supports/cours_php8.pdf ou lien Google Drive / OneDrive">
                <small style="color: #64748B; font-size: 12px; margin-top: 4px; display: block;">Collez ici le lien direct ou l'adresse de partage de votre document</small>
              </div>

              <!-- Champ caché stockant le chemin du fichier / lien -->
              <input type="hidden" name="lien_document" id="lien_document" value="<?= htmlspecialchars($item['lien_document'] ?? ($item['chemin_fichier'] ?? '')) ?>">
            </div>

          </div>

          <!-- Boutons de validation -->
          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px; display: inline-flex; align-items: center; gap: 8px;">
              <i data-lucide="check" style="width: 16px; height: 16px;"></i> Enregistrer le document
            </button>
            <a href="<?= RACINE ?>document/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 10px 24px;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>

<script>
var currentDocTab = 'upload';
var existingDocUrl = <?= json_encode($item['lien_document'] ?? ($item['chemin_fichier'] ?? '')) ?>;

function switchDocTab(tab) {
  currentDocTab = tab;
  var isUpload = (tab === 'upload');
  document.getElementById('panel_upload').style.display = isUpload ? '' : 'none';
  document.getElementById('panel_url').style.display = isUpload ? 'none' : '';
  
  document.getElementById('tab_upload').style.background = isUpload ? '#1E3A5F' : '#F1F5F9';
  document.getElementById('tab_upload').style.color = isUpload ? '#FFFFFF' : '#475569';
  document.getElementById('tab_url').style.background = isUpload ? '#F1F5F9' : '#1E3A5F';
  document.getElementById('tab_url').style.color = isUpload ? '#475569' : '#FFFFFF';

  if (!isUpload) {
    var inp = document.getElementById('url_fichier_input');
    if (!inp.value && existingDocUrl) { 
      inp.value = existingDocUrl; 
    }
    document.getElementById('lien_document').value = inp.value;
  }
}

// Drag & Drop
var dropZone = document.getElementById('doc_drop_zone');
dropZone.addEventListener('dragover', function(e) {
  e.preventDefault();
  dropZone.style.borderColor = '#1E3A5F';
  dropZone.style.background = '#EFF6FF';
});

dropZone.addEventListener('dragleave', function() {
  dropZone.style.borderColor = '#CBD5E1';
  dropZone.style.background = '#F8FAFC';
});

dropZone.addEventListener('drop', function(e) {
  e.preventDefault();
  dropZone.style.borderColor = '#CBD5E1';
  dropZone.style.background = '#F8FAFC';
  var files = e.dataTransfer.files;
  if (files.length) {
    document.getElementById('fichier_upload').files = files;
    handleDocFile(files[0]);
  }
});

document.getElementById('fichier_upload').addEventListener('change', function() {
  if (this.files.length) {
    handleDocFile(this.files[0]);
  }
});

function handleDocFile(file) {
  var maxMo = 50;
  if (file.size > maxMo * 1024 * 1024) {
    alert('Le fichier sélectionné dépasse la taille maximale autorisée de ' + maxMo + ' Mo.');
    return;
  }

  var ext = file.name.split('.').pop().toLowerCase();
  var iconName = 'file-text';
  var typeSelectVal = 'autre';

  if (ext === 'pdf') {
    iconName = 'file-text';
    typeSelectVal = 'pdf';
  } else if (['doc', 'docx', 'odt', 'rtf', 'txt'].indexOf(ext) !== -1) {
    iconName = 'file-type-2';
    typeSelectVal = 'word';
  } else if (['xls', 'xlsx', 'ods'].indexOf(ext) !== -1) {
    iconName = 'file-spreadsheet';
    typeSelectVal = 'excel';
  } else if (['ppt', 'pptx', 'odp'].indexOf(ext) !== -1) {
    iconName = 'presentation';
    typeSelectVal = 'powerpoint';
  } else if (['zip', 'rar', '7z'].indexOf(ext) !== -1) {
    iconName = 'archive';
    typeSelectVal = 'archive';
  } else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].indexOf(ext) !== -1) {
    iconName = 'image';
    typeSelectVal = 'image';
  }

  document.getElementById('doc_preview_zone').style.display = '';
  document.getElementById('doc_file_name').textContent = file.name;
  document.getElementById('doc_file_size').textContent = (file.size / (1024 * 1024)).toFixed(2) + ' Mo — Extension .' + ext.toUpperCase();
  document.getElementById('type_document').value = typeSelectVal;
  document.getElementById('lien_document').value = '';

  var iconEl = document.getElementById('doc_type_icon');
  iconEl.setAttribute('data-lucide', iconName);
  if (window.lucide) lucide.createIcons();
}

function clearDocFile() {
  document.getElementById('fichier_upload').value = '';
  document.getElementById('doc_preview_zone').style.display = 'none';
  document.getElementById('lien_document').value = existingDocUrl;
}

document.getElementById('url_fichier_input').addEventListener('input', function() {
  var val = this.value.trim();
  document.getElementById('lien_document').value = val;
});

$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  if (existingDocUrl) {
    if (existingDocUrl.indexOf('http') === 0) {
      switchDocTab('url');
      document.getElementById('url_fichier_input').value = existingDocUrl;
    } else {
      // Document existant uploadé
      document.getElementById('doc_preview_zone').style.display = '';
      var fileName = existingDocUrl.split('/').pop();
      document.getElementById('doc_file_name').textContent = fileName;
      document.getElementById('doc_file_size').textContent = 'Fichier enregistré existant';
      if (window.lucide) lucide.createIcons();
    }
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>

