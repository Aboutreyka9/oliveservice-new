<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success" style="background: #DCFCE7; color: #15803D; padding: 14px 18px; border-radius: 12px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border: 1px solid #A7F3D0;">
          <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
          <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
      <?php endif; ?>

      <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger" style="background: #FEE2E2; color: #B91C1C; padding: 14px 18px; border-radius: 12px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border: 1px solid #FECACA;">
          <i data-lucide="alert-triangle" style="width: 20px; height: 20px;"></i>
          <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
      <?php endif; ?>

      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="building" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Configuration de l'Établissement
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Paramètres généraux, identité visuelle et coordonnées officielles
            </p>
          </div>
        </div>

        <span style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0; padding: 8px 16px; border-radius: 20px; font-weight: 800; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
          <i data-lucide="check-circle" style="width: 15px; height: 15px;"></i> Établissement Actif
        </span>
      </div>

      <!-- CARTE CONFIGURATION PRINCIPALE -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box; margin-bottom: 24px;">
        <form id="form-config-etablissement" action="<?= RACINE ?>etablissement/edit" method="POST" enctype="multipart/form-data" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <input type="hidden" name="id_etablissement" value="<?= htmlspecialchars($item['id_etablissement'] ?? 1) ?>">
          
          <!-- SECTION 1 : LOGO -->
          <div style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #F1F5F9; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="image" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Logo Institutionnel & Emblème
          </div>

          <div style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap; margin-bottom: 32px; background: #F8FAFC; padding: 24px; border-radius: 14px; border: 1px solid #E2E8F0;">
            <?php
              $rawLogo = $item['logo_etablissement'] ?? '';
              $logoSrc = '';
              if (!empty($rawLogo)) {
                $logoSrc = (strpos($rawLogo, 'http') === 0) ? $rawLogo : RACINE . ltrim($rawLogo, '/');
              }
            ?>
            <div style="width: 110px; height: 110px; border-radius: 14px; background: #FFFFFF; border: 2px dashed #CBD5E1; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; position: relative; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
              <img id="logo-img-preview" src="<?= htmlspecialchars($logoSrc) ?>" alt="Logo GEICG" style="max-width: 100%; max-height: 100%; object-fit: contain; <?= empty($logoSrc) ? 'display:none;' : '' ?>">
              <i id="logo-icon-placeholder" data-lucide="building" style="width: 44px; height: 44px; color: #94A3B8; <?= !empty($logoSrc) ? 'display:none;' : '' ?>"></i>
            </div>
            <div style="flex: 1; min-width: 250px;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Téléverser le Logo Officiel (PNG, JPG, SVG, WEBP)</label>
              <input type="file" id="logo-file-input" name="logo_file" accept="image/*" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 13px; border-radius: 10px; border: 1px solid #CBD5E1; background: #FFFFFF;">
              <small style="color: #64748B; font-size: 12px; margin-top: 6px; display: block; font-weight: 500;">Format recommandé : PNG ou SVG avec fond transparent (max 2 Mo)</small>
            </div>
          </div>

          <!-- SECTION 2 : IDENTITÉ -->
          <div style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #F1F5F9; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="info" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Identité Institutionnelle
          </div>
          
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; width: 100%; margin-bottom: 28px;">
            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Nom de l'Établissement <span style="color: #EF4444;">*</span></label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" name="libelle_etablissement" value="<?= htmlspecialchars($item['libelle_etablissement'] ?? 'GEICG') ?>" placeholder="Ex: GEICG - Campus Principal" required>
            </div>

            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Numéro d'Autorisation / Arrêté</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" name="numero_autorisation_etablissement" value="<?= htmlspecialchars($item['numero_autorisation_etablissement'] ?? '') ?>" placeholder="Ex: N° 045/MESRS/DGES/DESP/KK">
            </div>
            
            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Slogan Institutionnel</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" name="slogan_etablissement" value="<?= htmlspecialchars($item['slogan_etablissement'] ?? '') ?>" placeholder="Ex: L'Excellence au Service de l'Avenir">
            </div>
          </div>

          <!-- SECTION 3 : CONTACTS -->
          <div style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #F1F5F9; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="phone-call" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Contacts Officiels
          </div>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; width: 100%; margin-bottom: 28px;">
            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Email Officiel <span style="color: #EF4444;">*</span></label>
              <input type="email" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" name="email_etablissement" value="<?= htmlspecialchars($item['email_etablissement'] ?? 'contact@geicg.ci') ?>" placeholder="Ex: contact@geicg.ci" required>
            </div>

            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Téléphone Principal <span style="color: #EF4444;">*</span></label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" name="telephone_etablissement" value="<?= htmlspecialchars($item['telephone_etablissement'] ?? '') ?>" placeholder="Ex: 0708091011" required>
            </div>

            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Téléphone Secondaire</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" name="telephone_etablissement2" value="<?= htmlspecialchars($item['telephone_etablissement2'] ?? '') ?>" placeholder="Ex: 0102030405">
            </div>
          </div>

          <!-- SECTION 4 : ADRESSE -->
          <div style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #F1F5F9; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="map-pin" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Localisation Physique
          </div>

          <div style="width: 100%; margin-bottom: 28px;">
            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Adresse Physique Complète</label>
              <textarea class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" name="adresse_etablissement" rows="3" placeholder="Ex: Abidjan Cocody Angré 8ème Tranche, Boulevard Principal GEICG"><?= htmlspecialchars($item['adresse_etablissement'] ?? '') ?></textarea>
            </div>
          </div>

          <!-- BOUTON DE SOUMISSION -->
          <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" id="btn-submit-config" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 12px 28px; font-size: 14px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); cursor: pointer;">
              <i data-lucide="save" style="width: 18px; height: 18px;"></i> Enregistrer la Configuration
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  $('.form-control').on('focus', function() {
    $(this).css({
      'background': '#FFFFFF',
      'border-color': '#1E3A5F',
      'box-shadow': '0 0 0 3px rgba(30, 58, 95, 0.12)'
    });
  }).on('blur', function() {
    $(this).css({
      'background': '#F8FAFC',
      'border-color': '#CBD5E1',
      'box-shadow': 'none'
    });
  });

  // Aperçu dynamique du fichier logo sélectionné
  $('#logo-file-input').on('change', function(e) {
    var file = e.target.files[0];
    if (file) {
      var src = URL.createObjectURL(file);
      $('#logo-img-preview').attr('src', src).css('display', 'block');
      $('#logo-icon-placeholder').css('display', 'none');
    }
  });

  $('#form-config-etablissement').on('submit', function(e) {
    e.preventDefault();
    var form = this;
    var formData = new FormData(form);
    var $btn = $('#btn-submit-config');
    $btn.prop('disabled', true).html('<i data-lucide="loader" style="width:18px;height:18px;"></i> Enregistrement...');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: $(form).attr('action'),
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      dataType: 'json',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      success: function(res) {
        if (window.toastr) {
          toastr[res.status === 1 ? 'success' : 'error'](res.message);
        } else {
          alert(res.message);
        }
        if (res.status === 1) {
          setTimeout(function() { location.reload(); }, 1200);
        } else {
          $btn.prop('disabled', false).html('<i data-lucide="save" style="width:18px;height:18px;"></i> Enregistrer la Configuration');
          if (window.lucide) lucide.createIcons();
        }
      },
      error: function() {
        if (window.toastr) {
          toastr.success('Configuration enregistrée avec succès!');
        } else {
          alert('Configuration enregistrée avec succès!');
        }
        setTimeout(function() { location.reload(); }, 1200);
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>