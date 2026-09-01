<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_cloture']) ? 'Éditer ' : 'Nouvelle ' ?> Clôture de Caisse Journalière</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Arrêté des comptes de caisse et réconciliation des recettes</p>
        </div>
        <a href="<?= RACINE ?>cloture_caisse/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>

      <!-- Banner Résumé des Encaissements Réels -->
      <div class="card" style="background: #1E3A5F; color: #FFFFFF; border-radius: 12px; padding: 22px; margin-bottom: 24px; box-shadow: 0 4px 14px rgba(30,58,95,0.2);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
          <div>
            <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.85;">Recette Totale à Clôturer</div>
            <div style="font-size: 26px; font-weight: 800; margin-top: 4px;" id="disp_total_general">0 FCFA</div>
            <div style="font-size: 12.5px; opacity: 0.85; margin-top: 2px;">
              Nombre d'encaissements enregistrés : <strong id="disp_nb_encaissements" style="color: #38BDF8;">0</strong>
            </div>
          </div>

          <div style="display: flex; gap: 16px; flex-wrap: wrap;">
            <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(4px); padding: 10px 16px; border-radius: 10px; min-width: 120px;">
              <div style="font-size: 11px; font-weight: 700; opacity: 0.85; text-transform: uppercase;">Espèces (Guichet)</div>
              <div style="font-size: 15px; font-weight: 800; margin-top: 2px;" id="disp_especes">0 FCFA</div>
            </div>

            <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(4px); padding: 10px 16px; border-radius: 10px; min-width: 120px;">
              <div style="font-size: 11px; font-weight: 700; opacity: 0.85; text-transform: uppercase;">Mobile Money</div>
              <div style="font-size: 15px; font-weight: 800; margin-top: 2px;" id="disp_mobile">0 FCFA</div>
            </div>

            <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(4px); padding: 10px 16px; border-radius: 10px; min-width: 120px;">
              <div style="font-size: 11px; font-weight: 700; opacity: 0.85; text-transform: uppercase;">Chèques / Virements</div>
              <div style="font-size: 15px; font-weight: 800; margin-top: 2px;" id="disp_cheques">0 FCFA</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Form Card -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        
        <!-- Warning Banner if already closed -->
        <div id="already-closed-alert" style="display: none; background: #FEF2F2; border: 1.5px solid #FCA5A5; border-radius: 10px; padding: 14px 18px; margin-bottom: 24px; color: #991B1B; font-weight: 700; font-size: 14px;">
          <i data-lucide="alert-triangle" style="width: 18px; height: 18px; vertical-align: -3px; margin-right: 6px; color: #DC2626;"></i>
          <span>La caisse de la journée du <span id="closed_date_lbl"></span> est DÉJÀ clôturée (Réf: <code id="closed_code_lbl" style="font-weight:800; color:#991B1B;">-</code>). Une seule clôture par jour est autorisée.</span>
        </div>

        <form action="<?= RACINE ?>cloture_caisse/<?= !empty($item['id_cloture']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_cloture'])): ?>
            <input type="hidden" name="id_cloture" value="<?= $item['id_cloture'] ?>">
          <?php endif; ?>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; width: 100%;">
            
            <!-- Date de clôture -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Date de clôture de caisse <span style="color: #EF4444;">*</span></label>
              <input type="date" id="inp_date_cloture" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 700;" name="date_cloture" value="<?= htmlspecialchars($item['date_cloture'] ?? date('Y-m-d')) ?>" required>
            </div>

            <!-- Total Espèces -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Total Espèces en Caisse (FCFA) <span style="color: #EF4444;">*</span></label>
              <input type="number" id="inp_total_especes" class="form-control calc-total" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1;" name="total_especes" value="<?= htmlspecialchars($item['total_especes'] ?? 0) ?>" required>
            </div>

            <!-- Total Mobile Money -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Total Mobile Money (FCFA) <span style="color: #EF4444;">*</span></label>
              <input type="number" id="inp_total_mobile_money" class="form-control calc-total" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1;" name="total_mobile_money" value="<?= htmlspecialchars($item['total_mobile_money'] ?? 0) ?>" required>
            </div>

            <!-- Total Chèques & Virements -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Total Chèques & Virements (FCFA) <span style="color: #EF4444;">*</span></label>
              <input type="number" id="inp_total_cheque_virement" class="form-control calc-total" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1;" name="total_cheque_virement" value="<?= htmlspecialchars($item['total_cheque_virement'] ?? 0) ?>" required>
            </div>

            <!-- Total Général -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Total Général Arrêté (FCFA) <span style="color: #EF4444;">*</span></label>
              <input type="number" id="inp_total_general" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 15px; font-weight: 800; border-radius: 8px; border: 1px solid #CBD5E1; background: #F8FAFC;" name="total_general" value="<?= htmlspecialchars($item['total_general'] ?? 0) ?>" required readonly>
            </div>

            <!-- Observations -->
            <div class="form-group" style="width: 100%; box-sizing: border-box; grid-column: 1 / -1;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Observations & Procès-verbal du Caissier</label>
              <textarea class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1;" name="observations" placeholder="Ex: Arrêté de caisse journalier vérifié et validé conforme." rows="3"><?= htmlspecialchars($item['observations'] ?? '') ?></textarea>
            </div>

          </div>

          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" id="btn_submit_cloture" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 800; border-radius: 8px; padding: 11px 28px; display: inline-flex; align-items: center; gap: 8px;">
              <i data-lucide="lock" style="width: 18px; height: 18px;"></i> Enregistrer la Clôture de Caisse
            </button>
            <a href="<?= RACINE ?>cloture_caisse/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 11px 24px;">Annuler</a>
          </div>

        </form>
      </div>

    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  function fetchDailyTotals(targetDate) {
    if (!targetDate) return;

    $.ajax({
      url: window.RACINE + 'cloture_caisse/getDailyTotals',
      type: 'GET',
      data: { date: targetDate },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 && res.data) {
          var d = res.data;
          $('#inp_total_especes').val(d.total_especes);
          $('#inp_total_mobile_money').val(d.total_mobile_money);
          $('#inp_total_cheque_virement').val(d.total_cheque_virement);
          $('#inp_total_general').val(d.total_general);

          $('#disp_especes').text(d.total_especes_fmt);
          $('#disp_mobile').text(d.total_mobile_money_fmt);
          $('#disp_cheques').text(d.total_cheque_virement_fmt);
          $('#disp_total_general').text(d.total_general_fmt);
          $('#disp_nb_encaissements').text(d.nb_encaissements);

          if (d.is_already_closed) {
            $('#closed_date_lbl').text(new Date(d.date).toLocaleDateString('fr-FR'));
            $('#closed_code_lbl').text(d.existing_code || 'CLO-EXIST');
            $('#already-closed-alert').slideDown(200);
            $('#btn_submit_cloture').prop('disabled', true).css({ opacity: 0.5, cursor: 'not-allowed' });
          } else {
            $('#already-closed-alert').slideUp(200);
            $('#btn_submit_cloture').prop('disabled', false).css({ opacity: 1, cursor: 'pointer' });
          }
        }
      }
    });
  }

  // Recalculate manual changes to general sum
  $('.calc-total').on('input change', function() {
    var esp = parseFloat($('#inp_total_especes').val()) || 0;
    var mob = parseFloat($('#inp_total_mobile_money').val()) || 0;
    var chq = parseFloat($('#inp_total_cheque_virement').val()) || 0;
    var tot = esp + mob + chq;
    $('#inp_total_general').val(tot);
    $('#disp_total_general').text(Number(tot).toLocaleString('fr-FR') + ' FCFA');
  });

  $('#inp_date_cloture').on('change', function() {
    fetchDailyTotals($(this).val());
  });

  // Fetch initial date on load
  var initialDate = $('#inp_date_cloture').val();
  if (initialDate) {
    fetchDailyTotals(initialDate);
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
