<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$item = $item ?? [];
$commerciaux = $commerciaux ?? [];
$zones = $zones ?? [];
$isEdit = !empty($item['id_zone_commercial']);
$title = $isEdit ? 'Éditer l\'Affectation Commercial / Zone' : 'Nouvelle Affectation Commercial / Zone';
$selectedCommercial = $item['commercial_code'] ?? '';
$selectedZone = !empty($item['zone_code']) ? $item['zone_code'] : Context::zone();
$statutAffectation = $item['statut_zone_commercial'] ?? 'actif';
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
            <i data-lucide="map-pin" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              <?= $title ?>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Attribution d'un secteur géographique à un commercial pour la prospection et les cotisations
            </p>
          </div>
        </div>

        <a href="<?= RACINE ?>zone_commercial/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;">
          <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour aux affectations
        </a>
      </div>

      <!-- CARTE FORMULAIRE PRINCIPALE -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box;">
        
        <form id="form-zc" action="<?= RACINE ?>zone_commercial/<?= $isEdit ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <input type="hidden" name="user_code" value="<?= htmlspecialchars(Context::user() ?? '') ?>">
          <?php if ($isEdit): ?>
            <input type="hidden" name="id_zone_commercial" value="<?= $item['id_zone_commercial'] ?>">
          <?php endif; ?>

          <!-- BLOC 1 : SÉLECTION DU COMMERCIAL -->
          <div style="margin-bottom: 28px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="user-check" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Sélection de l'Agent Commercial
            </h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Agent Commercial Terrain <span style="color: #EF4444;">*</span>
                </label>
                <select name="commercial_code" id="commercial-code-select" class="form-control select2" style="width: 100%;" required>
                  <option value="">-- Choisir un commercial dans la liste --</option>
                  <?php foreach ($commerciaux as $c): ?>
                    <?php 
                      $nomComplet = trim(($c['nom_user'] ?? '') . ' ' . ($c['prenom_user'] ?? ''));
                      $tel = !empty($c['telephone_user']) ? ' (' . $c['telephone_user'] . ')' : '';
                      $code = $c['code_user'] ?? '';
                    ?>
                    <option value="<?= htmlspecialchars($code) ?>" <?= ($selectedCommercial === $code) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($nomComplet . $tel . ' - [' . $code . ']') ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <small style="color: #64748B; font-size: 12px; margin-top: 6px; display: block;">
                  Sélectionnez l'agent de terrain qui prospectera et collectera des souscriptions sur ce secteur.
                </small>
              </div>
            </div>
          </div>

          <!-- BLOC 2 : ZONE GÉOGRAPHIQUE & STATUT -->
          <div style="margin-bottom: 28px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="map" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Secteur & Statut d'Activité
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Zone Commerciale <span style="color: #EF4444;">*</span>
                </label>
                <select name="zone_code" id="zone-code-select" class="form-control select2" style="width: 100%;" required>
                  <option value="">-- Sélectionner une zone --</option>
                  <?php foreach ($zones as $z): ?>
                    <option value="<?= htmlspecialchars($z['code_zone']) ?>" <?= ($selectedZone === $z['code_zone']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($z['libelle_zone']) ?> [<?= htmlspecialchars($z['code_zone']) ?>]
                    </option>
                  <?php endforeach; ?>
                </select>
                <small style="color: #64748B; font-size: 12px; margin-top: 6px; display: block;">
                  Périmètre territorial auquel le commercial est rattaché.
                </small>
              </div>

              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Statut de l'Affectation <span style="color: #EF4444;">*</span>
                </label>
                <select name="statut_zone_commercial" class="form-control" style="width: 100%; padding: 12px 16px; font-size: 14px; font-weight: 600; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A;">
                  <option value="actif" <?= ($statutAffectation === 'actif') ? 'selected' : '' ?>>Actif (Opérationnel sur le terrain)</option>
                  <option value="inactif" <?= ($statutAffectation === 'inactif') ? 'selected' : '' ?>>Inactif (Affectation suspendue)</option>
                </select>
                <small style="color: #64748B; font-size: 12px; margin-top: 6px; display: block;">
                  Les affectations inactives ne sont plus visibles dans le terminal de l'agent.
                </small>
              </div>
            </div>
          </div>

          <!-- BLOC 3 : ENCADRÉ D'INFORMATION & CONTEXTE -->
          <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 14px;">
            <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(5, 150, 105, 0.15); color: #059669; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px;">
              <i data-lucide="shield-check" style="width: 18px; height: 18px;"></i>
            </div>
            <div style="font-size: 13px; line-height: 1.5; color: #166534;">
              <strong>Rattachement automatique & Traçabilité :</strong> L'affectation sera enregistrée pour l'établissement actif <strong><?= htmlspecialchars(Context::etablissement() ?: 'Principal') ?></strong> et authentifiée par l'administrateur en session (<code><?= htmlspecialchars(Context::user() ?? 'SYSTÈME') ?></code>). Le commercial pourra être affecté à plusieurs zones si nécessaire.
            </div>
          </div>

          <!-- BOUTONS D'ACTION -->
          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%; flex-wrap: wrap;">
            <button type="submit" id="btn-submit-zc" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 12px 28px; font-size: 14px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); cursor: pointer; transition: all 0.2s ease;">
              <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> <?= $isEdit ? 'Enregistrer les modifications' : 'Valider l\'affectation' ?>
            </button>
            <a href="<?= RACINE ?>zone_commercial/list" class="btn" style="background: #F1F5F9; color: #475569; font-weight: 700; border-radius: 10px; padding: 12px 24px; text-decoration: none; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; gap: 6px;">
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

  function notifyToast(msg, type) {
    if (typeof showToast === 'function') {
      showToast(msg, type);
    } else if (window.toastr && typeof window.toastr[type] === 'function') {
      window.toastr[type](msg);
    } else {
      alert(msg);
    }
  }

  $('#form-zc').on('submit', function(e) {
    e.preventDefault();
    var $btn = $('#btn-submit-zc');
    var originalHtml = $btn.html();
    $btn.prop('disabled', true).css({ 'opacity': '0.7', 'pointer-events': 'none' })
        .html('<i data-lucide="loader-2" class="spin" style="width: 18px; height: 18px;"></i> Traitement...');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: $(this).attr('action'),
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          notifyToast(res.message || 'Affectation enregistrée avec succès !', 'success');
          setTimeout(function() { window.location.href = '<?= RACINE ?>zone_commercial/list'; }, 1000);
        } else {
          $btn.prop('disabled', false).css({ 'opacity': '1', 'pointer-events': 'auto' }).html(originalHtml);
          if (window.lucide) lucide.createIcons();
          notifyToast(res.message || 'Erreur lors de l\'enregistrement', 'error');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).css({ 'opacity': '1', 'pointer-events': 'auto' }).html(originalHtml);
        if (window.lucide) lucide.createIcons();
        var errMsg = 'Erreur réseau lors de l\'enregistrement';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errMsg = xhr.responseJSON.message;
        }
        notifyToast(errMsg, 'error');
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
