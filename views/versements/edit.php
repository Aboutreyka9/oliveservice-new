<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$isEdit = !empty($item['id_versement']);
$title = $isEdit ? 'Éditer le Versement' : 'Nouveau Versement de Fonds Commercial';
$commerciaux = $commerciaux ?? [];
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
            <i data-lucide="arrow-down-left" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              <?= $title ?>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Dépôt en caisse centrale des fonds collectés par l'agent commercial sur le terrain
            </p>
          </div>
        </div>

        <a href="<?= RACINE ?>versement/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;">
          <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour aux versements
        </a>
      </div>

      <!-- CARTE FORMULAIRE PRINCIPALE (NAVY PREMIUM) -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; max-width: 900px; box-sizing: border-box;">
        <form id="form-versement" action="<?= RACINE ?>versement/<?= $isEdit ? 'edit' : 'add' ?>" method="POST" enctype="multipart/form-data" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if ($isEdit): ?>
            <input type="hidden" name="id_versement" value="<?= $item['id_versement'] ?>">
          <?php endif; ?>

          <!-- SECTION 1 : COMMERCIAL & MONTIANT -->
          <div style="margin-bottom: 28px;">
            <div style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="user" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Agent Commercial & Fonds à Verser
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Agent Commercial <span style="color: #EF4444;">*</span></label>
                <select name="commercial_code" class="form-control select2" style="width: 100%;" required>
                  <option value="">-- Sélectionner l'agent commercial --</option>
                  <?php foreach ($commerciaux as $c): ?>
                    <option value="<?= $c['code_user'] ?>" <?= ($item['commercial_code'] ?? '') === $c['code_user'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars(trim(($c['nom_user'] ?? '') . ' ' . ($c['prenom_user'] ?? ''))) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Montant Versé (FCFA) <span style="color: #EF4444;">*</span></label>
                <input type="number" name="montant_versement" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 16px; border-radius: 10px; border: 1px solid #CBD5E1; font-weight: 800; color: #059669; outline: none; background: #F8FAFC;" value="<?= htmlspecialchars($item['montant_versement'] ?? '') ?>" required placeholder="Ex: 50000">
              </div>

              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Zone d'Activité</label>
                <select name="zone_code" class="form-control select2" style="width: 100%;">
                  <option value="">-- Sélectionner la zone --</option>
                  <?php foreach ($zones as $z): ?>
                    <option value="<?= $z['code_zone'] ?>" <?= ($item['zone_code'] ?? '') === $z['code_zone'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($z['libelle_zone']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <!-- SECTION 2 : PÉRIODE & RÉFÉRENCE -->
          <div style="margin-bottom: 28px;">
            <div style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="clock" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Période de Collecte & Référence
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Date Début Période</label>
                <input type="date" name="periode_versement_debut" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" value="<?= htmlspecialchars($item['periode_versement_debut'] ?? date('Y-m-d')) ?>">
              </div>

              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Date Fin Période</label>
                <input type="date" name="periode_versement_fin" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" value="<?= htmlspecialchars($item['periode_versement_fin'] ?? date('Y-m-d')) ?>">
              </div>

              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Référence Versement / Bordereau</label>
                <input type="text" name="reference_versement" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 700; color: #1E3A5F; font-family: monospace; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" value="<?= htmlspecialchars($item['reference_versement'] ?? '') ?>" placeholder="Ex: VRS-2026-001">
              </div>
            </div>
          </div>

          <!-- BOUTONS D'ACTION -->
          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%; flex-wrap: wrap;">
            <button type="submit" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 12px 28px; font-size: 14px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); cursor: pointer;">
              <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> <?= $isEdit ? 'Enregistrer les modifications' : 'Valider le Versement' ?>
            </button>
            <a href="<?= RACINE ?>versement/list" class="btn" style="background: #F1F5F9; color: #475569; font-weight: 700; border-radius: 10px; padding: 12px 24px; text-decoration: none; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; gap: 6px;">
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

  $('#form-versement').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
      url: $(this).attr('action'),
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Opération réussie');
          setTimeout(function() { window.location.href = '<?= RACINE ?>versement/list'; }, 1000);
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
