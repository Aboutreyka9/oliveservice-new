<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$isEdit = !empty($item['id_distribution']);
$title = $isEdit ? 'Éditer la Distribution' : 'Validation de Remise de Pack';
$souscriptions = $souscriptions ?? [];
$agents = $agents ?? [];
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
            <i data-lucide="truck" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              <?= $title ?>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Enregistrement et suivi de la remise effective du pack au client
            </p>
          </div>
        </div>

        <a href="<?= RACINE ?>distribution/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;">
          <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour aux distributions
        </a>
      </div>

      <!-- CARTE FORMULAIRE PRINCIPALE -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; max-width: 760px; box-sizing: border-box;">
        <form id="form-distribution" action="<?= RACINE ?>distribution/<?= $isEdit ? 'edit' : 'add' ?>" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if ($isEdit): ?>
            <input type="hidden" name="id_distribution" value="<?= $item['id_distribution'] ?>">
          <?php endif; ?>
          
          <!-- BLOC 1 : SOUSCRIPTION À LIVRER -->
          <div style="margin-bottom: 28px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="file-check-2" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Souscription à Livrer
            </h3>
            
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                Souscription Client Soldée <span style="color: #EF4444;">*</span>
              </label>
              <select name="souscription_code" id="select-souscription" class="form-control select2" style="width: 100%; box-sizing: border-box;" required <?= $isEdit ? 'disabled' : '' ?>>
                <option value="">-- Sélectionner une souscription soldée --</option>
                <?php foreach ($souscriptions as $s): ?>
                  <option value="<?= $s['code_souscription'] ?>" 
                          data-zone="<?= htmlspecialchars($s['libelle_zone'] ?? '') ?>"
                          <?= ($item['souscription_code'] ?? '') === $s['code_souscription'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars(trim($s['nom_client'] . ' ' . $s['prenom_client'])) ?> - <?= htmlspecialchars($s['libelle_pack'] ?? 'Pack') ?> (<?= $s['code_souscription'] ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- BLOC 2 : LOGISTIQUE & LIVRAISON -->
          <div style="margin-bottom: 28px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="truck" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Logistique & Livraison
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Zone de Livraison</label>
                <select name="zone_code" class="form-control select2" style="width: 100%; box-sizing: border-box;">
                  <option value="">-- Zone par défaut --</option>
                  <?php foreach ($zones as $z): ?>
                    <option value="<?= $z['code_zone'] ?>" <?= ($item['zone_code'] ?? '') === $z['code_zone'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($z['libelle_zone']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Agent Livreur</label>
                <select name="agent_livreur_code" class="form-control select2" style="width: 100%; box-sizing: border-box;">
                  <option value="">-- Sélectionner l'agent --</option>
                  <?php foreach ($agents as $a): ?>
                    <option value="<?= $a['code_user'] ?>" <?= ($item['agent_livreur_code'] ?? '') === $a['code_user'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars(trim($a['nom_user'] . ' ' . $a['prenom_user'])) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Date et Heure de Remise</label>
                <input type="datetime-local" name="date_distribution_effectuee" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A;" value="<?= htmlspecialchars(!empty($item['date_distribution_effectuee']) ? date('Y-m-d\TH:i', strtotime($item['date_distribution_effectuee'])) : date('Y-m-d\TH:i')) ?>" required>
              </div>

              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Statut Livraison <span style="color: #EF4444;">*</span></label>
                <select name="statut_distribution" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 700; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A;">
                  <option value="valide" <?= ($item['statut_distribution'] ?? 'valide') === 'valide' ? 'selected' : '' ?>>Validée / Livrée</option>
                  <option value="En attente" <?= ($item['statut_distribution'] ?? '') === 'En attente' ? 'selected' : '' ?>>En attente</option>
                  <option value="ennule" <?= ($item['statut_distribution'] ?? '') === 'ennule' ? 'selected' : '' ?>>Annulée</option>
                </select>
              </div>
            </div>
          </div>

          <!-- BLOC 3 : PREUVES & OBSERVATIONS -->
          <div style="margin-bottom: 28px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="clipboard-list" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Preuves & Observations
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
              <div class="form-group" style="grid-column: 1 / -1;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Observation / Remarques</label>
                <textarea name="observation_distribution" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A; resize: vertical;" rows="3" placeholder="État du colis, remarques du client..."><?= htmlspecialchars($item['observation_distribution'] ?? '') ?></textarea>
              </div>

              <div class="form-group" style="grid-column: 1 / -1;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Photo PV de Réception</label>
                <input type="file" name="pv_reception_photo" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" accept="image/*">
                <?php if (!empty($item['pv_reception_photo'])): ?>
                  <div style="margin-top: 12px;">
                    <img src="<?= RACINE . htmlspecialchars($item['pv_reception_photo']) ?>" style="max-height: 140px; border-radius: 10px; border: 1px solid #E2E8F0; box-shadow: 0 2px 6px rgba(0,0,0,0.08);">
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%; flex-wrap: wrap;">
            <button type="submit" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 12px 28px; font-size: 14px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); cursor: pointer;">
              <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> <?= $isEdit ? 'Enregistrer les modifications' : 'Confirmer la remise du pack' ?>
            </button>
            <a href="<?= RACINE ?>distribution/list" class="btn" style="background: #F1F5F9; color: #475569; font-weight: 700; border-radius: 10px; padding: 12px 24px; text-decoration: none; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; gap: 6px;">
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

  $('#form-distribution').on('submit', function(e) {
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
          setTimeout(function() { window.location.href = '<?= RACINE ?>distribution/list'; }, 1000);
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
