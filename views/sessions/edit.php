<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$isEdit = !empty($item['id_session']);
$title = $isEdit ? 'Éditer la Session' : 'Nouvelle Session d\'Activité';
$annees = $annees ?? [];
$zones = $zones ?? [];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="clock" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              <?= $title ?>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Configuration des paramètres de la campagne et session de souscription
            </p>
          </div>
        </div>

        <a href="<?= RACINE ?>session/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;">
          <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour aux sessions
        </a>
      </div>

      <!-- CARTE FORMULAIRE PRINCIPALE -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; max-width: 760px; box-sizing: border-box;">
        <form id="form-session" action="<?= RACINE ?>session/<?= $isEdit ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if ($isEdit): ?>
            <input type="hidden" name="id_session" value="<?= $item['id_session'] ?>">
          <?php endif; ?>

          <!-- BLOC 1 : IDENTIFICATION DE LA SESSION -->
          <div style="margin-bottom: 28px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="info" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Identification de la Session
            </h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
              <div class="form-group" style="grid-column: 1 / -1;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Libellé de la Session <span style="color: #EF4444;">*</span>
                </label>
                <input type="text" name="libelle_session" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" value="<?= htmlspecialchars($item['libelle_session'] ?? '') ?>" required placeholder="Ex: Session Noël 2026">
              </div>

              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Année d'activité <span style="color: #EF4444;">*</span>
                </label>
                <select name="annee_code" class="form-control select2" style="width: 100%; box-sizing: border-box;" required>
                  <option value="">-- Sélectionner l'année --</option>
                  <?php foreach ($annees as $a): ?>
                    <option value="<?= $a['code_annee'] ?>" <?= ($item['annee_code'] ?? '') === $a['code_annee'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($a['libelle_annee']) ?> (Code: <?= htmlspecialchars($a['code_annee']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Zone Commerciale <span style="color: #64748B; font-weight: 500; font-size: 12px;">(Optionnel)</span>
                </label>
                <select name="zone_code" class="form-control select2" style="width: 100%; box-sizing: border-box;">
                  <option value="">-- Session Globale --</option>
                  <?php foreach ($zones as $z): ?>
                    <option value="<?= htmlspecialchars($z['code_zone']) ?>" <?= ($item['zone_code'] ?? '') === $z['code_zone'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($z['libelle_zone']) ?> (Code: <?= htmlspecialchars($z['code_zone']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <!-- BLOC 2 : DURÉE & PLANIFICATION -->
          <div style="margin-bottom: 28px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="calendar" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Durée & Planification
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Date de Début <span style="color: #EF4444;">*</span>
                </label>
                <input type="date" name="date_debut_session" id="date-debut-session" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A;" value="<?= htmlspecialchars($item['date_debut_session'] ?? '') ?>" required>
              </div>

              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Date de Fin Prévue <span style="color: #EF4444;">*</span>
                </label>
                <input type="date" name="date_fin_session" id="date-fin-session" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A;" value="<?= htmlspecialchars($item['date_fin_session'] ?? '') ?>" required>
              </div>

              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Nombre Total de Jours <span style="color: #EF4444;">*</span>
                </label>
                <input type="number" name="nombre_jour_session" id="nombre-jour-session" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 800; color: #1E3A5F; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" value="<?= htmlspecialchars($item['nombre_jour_session'] ?? '') ?>" required min="1" placeholder="Ex: 170">
              </div>
            </div>
          </div>

          <!-- BOUTONS D'ACTION -->
          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%; flex-wrap: wrap;">
            <button type="submit" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 12px 28px; font-size: 14px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); cursor: pointer;">
              <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> <?= $isEdit ? 'Enregistrer les modifications' : 'Créer la Session' ?>
            </button>
            <a href="<?= RACINE ?>session/list" class="btn" style="background: #F1F5F9; color: #475569; font-weight: 700; border-radius: 10px; padding: 12px 24px; text-decoration: none; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; gap: 6px;">
              Annuler
            </a>
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
    $('.select2').select2({ width: '100%' });
  }

  $('.form-control').on('focus', function() {
    if (!$(this).is('select')) {
      $(this).css({
        'background': '#FFFFFF',
        'border-color': '#1E3A5F',
        'box-shadow': '0 0 0 3px rgba(30, 58, 95, 0.12)'
      });
    }
  }).on('blur', function() {
    if (!$(this).is('select')) {
      $(this).css({
        'background': '#F8FAFC',
        'border-color': '#CBD5E1',
        'box-shadow': 'none'
      });
    }
  });

  function calculerNombreJours() {
    var debut = $('#date-debut-session').val();
    var fin = $('#date-fin-session').val();
    if (debut && fin) {
      var d1 = new Date(debut);
      var d2 = new Date(fin);
      var diff = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
      if (diff > 0) {
        $('#nombre-jour-session').val(diff);
      } else {
        $('#nombre-jour-session').val('');
      }
    }
  }

  $('#date-debut-session, #date-fin-session').on('change', calculerNombreJours);

  $('#form-session').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
      url: $(this).attr('action'),
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Opération réussie');
          setTimeout(function() { window.location.href = '<?= RACINE ?>session/list'; }, 1000);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur réseau');
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
