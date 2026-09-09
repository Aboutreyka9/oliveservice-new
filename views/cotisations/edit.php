<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$isEdit = !empty($item['id_cautisation_client']);
$title = $isEdit ? 'Éditer la Cotisation' : 'Saisie d\'une Cotisation Client';
$souscriptions = $souscriptions ?? [];
$commerciaux = $commerciaux ?? [];
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
            <i data-lucide="wallet" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              <?= $title ?>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Enregistrement et encaissement d'une cotisation quotidienne client sur le terrain
            </p>
          </div>
        </div>

        <a href="<?= RACINE ?>cotisation/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;">
          <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour aux cotisations
        </a>
      </div>

      <!-- CARTE FORMULAIRE PRINCIPALE -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; max-width: 100%; box-sizing: border-box;">
        <form id="form-cotisation" action="<?= RACINE ?>cotisation/<?= $isEdit ? 'edit' : 'add' ?>" method="POST" enctype="multipart/form-data" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if ($isEdit): ?>
            <input type="hidden" name="id_cautisation_client" value="<?= $item['id_cautisation_client'] ?>">
          <?php endif; ?>

          <!-- BLOC 1 : CONTRAT DE SOUSCRIPTION -->
          <div style="margin-bottom: 28px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="file-text" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Contrat de Souscription Client
            </h3>
            
            <div style="display: grid; grid-template-columns: repeat(1, 1fr); gap: 20px;">
              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Souscription Client Concernée <span style="color: #EF4444;">*</span>
                </label>
                <select name="souscription_code" id="select-souscription" class="form-control select2" style="width: 100%; box-sizing: border-box;" required <?= $isEdit ? 'disabled' : '' ?>>
                  <option value="">-- Rechercher une souscription par client ou pack --</option>
                  <?php foreach ($souscriptions as $s): ?>
                    <option value="<?= $s['code_souscription'] ?>" 
                            data-cotis="<?= $s['montant_cotisation_journaliere'] ?? 1000 ?>"
                            <?= ($item['souscription_code'] ?? '') === $s['code_souscription'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars(trim($s['nom_client'] ?? '')) ?> - Pack <?= htmlspecialchars($s['libelle_pack'] ?? 'Pack') ?> (Réf: <?= $s['code_souscription'] ?> - <?= number_format((float)($s['montant_cotisation_journaliere'] ?? 1000), 0, ',', ' ') ?> F/j)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <!-- BLOC 2 : MONTANTS ET ÉCHÉANCE -->
          <div style="margin-bottom: 28px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="coins" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Montant & Nombre de Jours
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Montant Reçu (FCFA) <span style="color: #EF4444;">*</span>
                </label>
                <input type="number" name="montant_cautisation" id="input-montant" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 800; color: #059669; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" value="<?= htmlspecialchars($item['montant_cautisation'] ?? '') ?>" required placeholder="Ex: 1000">
              </div>

              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Nombre de Jours Payés</label>
                <input type="number" name="nombre_jour_paye" id="input-nb-jours" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 800; color: #1E3A5F; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F1F5F9; cursor: not-allowed;" value="<?= htmlspecialchars($item['nombre_jour_paye'] ?? '1') ?>" min="1" placeholder="Calculé auto" readonly>
              </div>

              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Date de la Cotisation <span style="color: #EF4444;">*</span>
                </label>
                <input type="date" name="date_cautisation" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A;" value="<?= htmlspecialchars($item['date_cautisation'] ?? date('Y-m-d')) ?>" required>
              </div>

              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Commercial Encaisseur</label>
                <?php
                  $commCodeVal = $item['commercial_code'] ?? Context::user();
                  $commLabelVal = '';
                  foreach ($commerciaux as $c) {
                    if (($c['code_user'] ?? '') === $commCodeVal) {
                      $commLabelVal = trim(($c['nom_user'] ?? '') . ' ' . ($c['prenom_user'] ?? ''));
                      break;
                    }
                  }
                  if (empty($commLabelVal)) {
                    $commLabelVal = trim(($_SESSION['nom'] ?? $_SESSION['user_nom'] ?? '') . ' ' . ($_SESSION['prenom'] ?? $_SESSION['user_prenom'] ?? ''));
                    if (empty($commLabelVal)) {
                      $commLabelVal = $commCodeVal;
                    }
                  }
                ?>
                <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; color: #334155; border-radius: 10px; border: 1px solid #CBD5E1; background: #F1F5F9; cursor: not-allowed;" value="<?= htmlspecialchars($commLabelVal) ?>" readonly>
                <input type="hidden" name="commercial_code" value="<?= htmlspecialchars($commCodeVal) ?>">
              </div>
            </div>
          </div>

          <!-- BLOC 3 : MODE & PREUVE DE PAIEMENT -->
          <div style="margin-bottom: 28px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="receipt" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Mode & Preuve de Paiement
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Mode de Règlement <span style="color: #EF4444;">*</span>
                </label>
                <select name="mode_paiement" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 700; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A;">
                  <option value="espece" <?= ($item['mode_paiement'] ?? 'espece') === 'espece' ? 'selected' : '' ?>>Espèce (Comptant)</option>
                  <option value="mobile_money" <?= ($item['mode_paiement'] ?? '') === 'mobile_money' ? 'selected' : '' ?>>Mobile Money (Wave, Orange, MTN)</option>
                  <option value="virement" <?= ($item['mode_paiement'] ?? '') === 'virement' ? 'selected' : '' ?>>Virement Bancaire</option>
                </select>
              </div>

              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">N° Référence Transaction</label>
                <input type="text" name="reference_paiement" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A;" value="<?= htmlspecialchars($item['reference_paiement'] ?? '') ?>" placeholder="Ex: TXN12345678">
              </div>

              <div class="form-group" style="grid-column: 1 / -1;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Photo du Reçu / Preuve (Image)</label>
                <input type="file" name="photo_recu" class="form-control" style="width: 100%; box-sizing: border-box; padding: 10px 14px; font-size: 14px; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" accept="image/*">
              </div>
            </div>
          </div>

          <!-- BOUTONS D'ACTION -->
          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%; flex-wrap: wrap;">
            <button type="submit" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 12px 28px; font-size: 14px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); cursor: pointer;">
              <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> <?= $isEdit ? 'Enregistrer les modifications' : 'Valider la Cotisation' ?>
            </button>
            <a href="<?= RACINE ?>cotisation/list" class="btn" style="background: #F1F5F9; color: #475569; font-weight: 700; border-radius: 10px; padding: 12px 24px; text-decoration: none; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; gap: 6px;">
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

  $('#select-souscription').on('change select2:select', function() {
    var selectedOpt = $(this).find('option:selected');
    var cotisUnit = parseFloat(selectedOpt.data('cotis')) || 1000;
    var montant = parseFloat($('#input-montant').val()) || 0;
    if (montant > 0 && cotisUnit > 0) {
      $('#input-nb-jours').val(Math.max(1, Math.round(montant / cotisUnit)));
    }
  });

  $('#input-montant').on('input', function() {
    var selectedOpt = $('#select-souscription').find('option:selected');
    var cotisUnit = parseFloat(selectedOpt.data('cotis')) || 1000;
    var montant = parseFloat($(this).val()) || 0;
    if (cotisUnit > 0) {
      $('#input-nb-jours').val(Math.max(1, Math.round(montant / cotisUnit)));
    }
  });

  $('#form-cotisation').on('submit', function(e) {
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
          setTimeout(function() { window.location.href = '<?= RACINE ?>cotisation/list'; }, 1000);
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
