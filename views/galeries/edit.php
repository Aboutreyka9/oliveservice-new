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
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_galerie']) ? 'Éditer ' : 'Ajouter ' ?> Galerie Médias</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Saisie des données du module Galeries Photos & Vidéos</p>
        </div>
        <a href="<?= RACINE ?>galerie/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>galerie/<?= !empty($item['id_galerie']) ? 'edit' : 'add' ?>" method="POST" enctype="multipart/form-data" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_galerie'])): ?>
            <input type="hidden" name="id_galerie" value="<?= $item['id_galerie'] ?>">
          <?php endif; ?>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; width: 100%;">
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Titre de l'album / Galerie <span style="color: #EF4444;">*</span></label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="titre_galerie" value="<?= htmlspecialchars($item['titre_galerie'] ?? '') ?>" placeholder="Ex: Cérémonie de Remise des Diplômes 2025" required>
            </div>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Type de média (Photo / Vidéo) <span style="color: #EF4444;">*</span></label>
              <select class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="type_galerie" id="type_galerie" required>
                <option value="photo" <?= (($item['type_galerie'] ?? '') === 'photo') ? 'selected' : '' ?>>Album Photos</option>
                <option value="video" <?= (($item['type_galerie'] ?? '') === 'video') ? 'selected' : '' ?>>Album Vidéos</option>
              </select>
            </div>
            <div class="form-group" style="width: 100%; box-sizing: border-box; grid-column: 1 / -1;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Lien / URL du Fichier ou Vidéo <span style="color: #EF4444;">*</span></label>
              <div style="display: flex; gap: 0; margin-bottom: 12px; border-radius: 8px; overflow: hidden; border: 1px solid #CBD5E1; width: fit-content;">
                <button type="button" id="tab_upload" onclick="switchTab('upload')" style="padding: 8px 20px; font-size: 13px; font-weight: 700; background: #1E3A5F; color: #fff; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                  <i data-lucide="upload-cloud" style="width:15px;height:15px;"></i> Uploader un fichier
                </button>
                <button type="button" id="tab_url" onclick="switchTab('url')" style="padding: 8px 20px; font-size: 13px; font-weight: 700; background: #F1F5F9; color: #475569; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                  <i data-lucide="link" style="width:15px;height:15px;"></i> Saisir une URL
                </button>
              </div>
              <div id="panel_upload">
                <label id="drop_zone" for="fichier_upload" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; border: 2px dashed #CBD5E1; border-radius: 10px; padding: 30px 20px; cursor: pointer; background: #F8FAFC; transition: border-color 0.2s; min-height: 140px;">
                  <i data-lucide="image-plus" style="width:40px;height:40px;color:#94A3B8;"></i>
                  <span style="font-size: 14px; font-weight: 600; color: #475569;">Cliquez ou glissez-déposez votre fichier ici</span>
                  <span id="drop_hint" style="font-size: 12px; color: #94A3B8;">Images : JPG, PNG, WEBP, GIF — Vidéos : MP4, MOV, AVI (max 50 Mo)</span>
                  <input type="file" id="fichier_upload" name="fichier_upload" accept="image/*,video/*" style="display:none;">
                </label>
                <div id="preview_zone" style="display:none; margin-top: 14px; border-radius: 10px; overflow: hidden; border: 1px solid #E2E8F0; background: #0F172A; position: relative; max-width: 100%;">
                  <img id="preview_img" src="" alt="Aperçu" style="display:none; width:100%; max-height:320px; object-fit:contain; border-radius:10px;">
                  <video id="preview_vid" src="" controls style="display:none; width:100%; max-height:320px; border-radius:10px;"></video>
                  <button type="button" onclick="clearFile()" title="Supprimer le fichier" style="position:absolute;top:8px;right:8px;background:#EF4444;color:#fff;border:none;border-radius:50%;width:30px;height:30px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="x" style="width:16px;height:16px;"></i>
                  </button>
                </div>
                <div id="file_info" style="display:none; margin-top: 8px; font-size: 12px; color: #64748B;"></div>
              </div>
              <div id="panel_url" style="display:none;">
                <input type="text" class="form-control" id="url_fichier_input" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" placeholder="Ex: https://geicg.ci/medias/album-2025.jpg ou lien YouTube">
                <div id="preview_url_zone" style="display:none; margin-top:12px; border-radius:10px; overflow:hidden; border:1px solid #E2E8F0; background:#0F172A; max-width:100%;">
                  <img id="preview_url_img" src="" alt="Aperçu" style="display:none; width:100%; max-height:280px; object-fit:contain; border-radius:10px;">
                  <div id="preview_url_video" style="display:none;"></div>
                </div>
              </div>
              <input type="hidden" name="url_fichier" id="url_fichier" value="<?= htmlspecialchars($item['url_fichier'] ?? '') ?>">
            </div>
            <div class="form-group" style="width: 100%; box-sizing: border-box; grid-column: 1 / -1;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Description</label>
              <textarea class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A; outline: none; transition: border-color 0.2s;" name="description_galerie" placeholder="Ex: Album photos souvenir de la remise des diplômes..."  rows="3"><?= htmlspecialchars($item['description_galerie'] ?? '') ?></textarea>
            </div>
          </div>
          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px;">Enregistrer</button>
            <a href="<?= RACINE ?>galerie/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 10px 24px;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<script>
var currentTab = 'upload';
var existingUrl = <?= json_encode($item['url_fichier'] ?? '') ?>;

function switchTab(tab) {
  currentTab = tab;
  var isUpload = (tab === 'upload');
  document.getElementById('panel_upload').style.display = isUpload ? '' : 'none';
  document.getElementById('panel_url').style.display   = isUpload ? 'none' : '';
  document.getElementById('tab_upload').style.background = isUpload ? '#1E3A5F' : '#F1F5F9';
  document.getElementById('tab_upload').style.color     = isUpload ? '#fff'     : '#475569';
  document.getElementById('tab_url').style.background   = isUpload ? '#F1F5F9' : '#1E3A5F';
  document.getElementById('tab_url').style.color        = isUpload ? '#475569' : '#fff';
  if (!isUpload) {
    var inp = document.getElementById('url_fichier_input');
    if (!inp.value && existingUrl) { inp.value = existingUrl; }
    previewUrl(inp.value);
    document.getElementById('url_fichier').value = inp.value;
  }
}

var dz = document.getElementById('drop_zone');
dz.addEventListener('dragover', function(e){ e.preventDefault(); dz.style.borderColor='#1E3A5F'; });
dz.addEventListener('dragleave', function(){ dz.style.borderColor='#CBD5E1'; });
dz.addEventListener('drop', function(e){
  e.preventDefault(); dz.style.borderColor='#CBD5E1';
  var files = e.dataTransfer.files;
  if (files.length) { document.getElementById('fichier_upload').files = files; handleFile(files[0]); }
});

document.getElementById('fichier_upload').addEventListener('change', function(){
  if (this.files.length) handleFile(this.files[0]);
});

function handleFile(file) {
  var maxMo = 50;
  if (file.size > maxMo * 1024 * 1024) {
    alert('Le fichier dépasse ' + maxMo + ' Mo. Veuillez choisir un fichier plus petit.');
    return;
  }
  var isImage = file.type.startsWith('image/');
  var isVideo = file.type.startsWith('video/');
  if (!isImage && !isVideo) { alert('Format non supporté. Choisissez une image ou une vidéo.'); return; }

  var reader = new FileReader();
  reader.onload = function(e) {
    document.getElementById('preview_zone').style.display = '';
    if (isImage) {
      document.getElementById('preview_img').src = e.target.result;
      document.getElementById('preview_img').style.display = '';
      document.getElementById('preview_vid').style.display = 'none';
    } else {
      document.getElementById('preview_vid').src = e.target.result;
      document.getElementById('preview_vid').style.display = '';
      document.getElementById('preview_img').style.display = 'none';
    }
    document.getElementById('file_info').style.display = '';
    document.getElementById('file_info').innerHTML = '<i data-lucide="file" style="width:14px;height:14px;vertical-align:middle;display:inline-block;margin-right:4px;"></i> <strong>' + file.name + '</strong> &nbsp;(' + (file.size/1024/1024).toFixed(2) + ' Mo)';
    if (window.lucide) lucide.createIcons();
    document.getElementById('url_fichier').value = '';
  };
  reader.readAsDataURL(file);
  if (isImage) document.getElementById('type_galerie').value = 'photo';
  if (isVideo) document.getElementById('type_galerie').value = 'video';
}

function clearFile() {
  document.getElementById('fichier_upload').value = '';
  document.getElementById('preview_zone').style.display = 'none';
  document.getElementById('preview_img').src = '';
  document.getElementById('preview_vid').src = '';
  document.getElementById('file_info').style.display = 'none';
  document.getElementById('url_fichier').value = existingUrl;
}

document.getElementById('url_fichier_input').addEventListener('input', function(){
  var val = this.value.trim();
  previewUrl(val);
  document.getElementById('url_fichier').value = val;
});

function previewUrl(url) {
  var imgEl = document.getElementById('preview_url_img');
  var vidEl = document.getElementById('preview_url_video');
  var zone  = document.getElementById('preview_url_zone');
  if (!url) { zone.style.display='none'; imgEl.style.display='none'; vidEl.innerHTML=''; vidEl.style.display='none'; return; }
  var ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/);
  var vmMatch = url.match(/vimeo\.com\/(\d+)/);
  if (ytMatch) {
    zone.style.display=''; imgEl.style.display='none';
    vidEl.style.display=''; vidEl.innerHTML='<iframe src="https://www.youtube.com/embed/'+ytMatch[1]+'" style="width:100%;height:280px;border:none;" allowfullscreen></iframe>';
  } else if (vmMatch) {
    zone.style.display=''; imgEl.style.display='none';
    vidEl.style.display=''; vidEl.innerHTML='<iframe src="https://player.vimeo.com/video/'+vmMatch[1]+'" style="width:100%;height:280px;border:none;" allowfullscreen></iframe>';
  } else if (/\.(mp4|mov|avi|webm)(\?.*)?$/i.test(url)) {
    zone.style.display=''; imgEl.style.display='none';
    vidEl.style.display=''; vidEl.innerHTML='<video src="'+url+'" controls style="width:100%;max-height:280px;border-radius:10px;"></video>';
  } else if (/\.(jpg|jpeg|png|gif|webp|svg)(\?.*)?$/i.test(url)) {
    zone.style.display=''; vidEl.innerHTML=''; vidEl.style.display='none';
    imgEl.style.display=''; imgEl.src=url;
  } else {
    zone.style.display='none';
  }
}

$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  if (existingUrl) {
    switchTab('url');
    document.getElementById('url_fichier_input').value = existingUrl;
    previewUrl(existingUrl);
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
