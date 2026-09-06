<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$isEdit = !empty($item['id_client']);
$title = $isEdit ? 'Éditer le Profil Client' : 'Nouveau Client';
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
            <i data-lucide="<?= $isEdit ? 'user-check' : 'user-plus' ?>" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              <?= $title ?>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Saisie et mise à jour des informations personnelles, contacts et localisation du client
            </p>
          </div>
        </div>

        <a href="<?= RACINE ?>client/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;">
          <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour à la liste
        </a>
      </div>

      <!-- CARTE FORMULAIRE PRINCIPALE -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box;">
        <form id="form-client" action="<?= RACINE ?>client/<?= $isEdit ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if ($isEdit): ?>
            <input type="hidden" name="id_client" value="<?= $item['id_client'] ?>">
          <?php endif; ?>

          <!-- BLOC 1 : IDENTITÉ DU CLIENT -->
          <div style="margin-bottom: 28px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="user" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Identité du Client
            </h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Nom de famille <span style="color: #EF4444;">*</span>
                </label>
                <input type="text" name="nom_client" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A; transition: all 0.2s ease;" value="<?= htmlspecialchars($item['nom_client'] ?? '') ?>" required placeholder="Ex: KOUASSI Jean">
              </div>

              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  N° CNI / Pièce d'identité
                </label>
                <input type="text" name="cni_client" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A; transition: all 0.2s ease;" value="<?= htmlspecialchars($item['cni_client'] ?? $item['numero_cni'] ?? '') ?>" placeholder="Ex: C0123456789">
              </div>
            </div>
          </div>

          <!-- BLOC 2 : COORDONNÉES & LOCALISATION -->
          <div style="margin-bottom: 28px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="map-pin" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Coordonnées & Localisation
            </h3>
            
            <div class="row g-3">
              <div class="col-md-3 form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Téléphone Principal <span style="color: #EF4444;">*</span>
                </label>
                <input type="text" name="telephone_client" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A; transition: all 0.2s ease;" value="<?= htmlspecialchars($item['telephone_client'] ?? '') ?>" required placeholder="Ex: 0701020304">
              </div>

              <div class="col-md-3 form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Adresse Email
                </label>
                <input type="email" name="email_client" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A; transition: all 0.2s ease;" value="<?= htmlspecialchars($item['email_client'] ?? '') ?>" placeholder="Ex: client@gmail.com">
              </div>

              <div class="col-md-3 form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Quartier / Repère de Résidence
                </label>
                <input type="text" name="lieu_residence_client" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A; transition: all 0.2s ease;" value="<?= htmlspecialchars($item['lieu_residence_client'] ?? '') ?>" placeholder="Ex: Yopougon Ananeraie, Carrefour de la Pharmacie">
              </div>
            </div>

          </div>

          <!-- BOUTONS D'ACTION -->
          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%; flex-wrap: wrap;">
            <button type="submit" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 12px 28px; font-size: 14px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); cursor: pointer;">
              <i data-lucide="save" style="width: 18px; height: 18px;"></i> <?= $isEdit ? 'Enregistrer les modifications' : 'Créer le Client' ?>
            </button>
            <a href="<?= RACINE ?>client/list" class="btn" style="background: #F1F5F9; color: #475569; font-weight: 700; border-radius: 10px; padding: 12px 24px; text-decoration: none; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; gap: 6px;">
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

  $('#form-client').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
      url: $(this).attr('action'),
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Opération réussie');
          setTimeout(function() { window.location.href = '<?= RACINE ?>client/list'; }, 1000);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur réseau lors de l\'enregistrement');
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
