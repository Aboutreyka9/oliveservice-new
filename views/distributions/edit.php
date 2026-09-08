<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$isEdit = !empty($item['id_distribution']);
$title = $isEdit ? 'Éditer la Distribution' : 'Validation de Distribution des Packs';
$souscriptions = $souscriptions ?? [];
$agents = $agents ?? [];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(30, 58, 95, 0.25);">
            <i data-lucide="package-check" style="width: 26px; height: 26px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              <?= $title ?>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Formulaire de remise physique des articles du pack pour les souscriptions intégralement soldées
            </p>
          </div>
        </div>

        <a href="<?= RACINE ?>distribution/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;">
          <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Liste des distributions
        </a>
      </div>

      <!-- MESSAGES FLASH -->
      <div id="form-messages" style="display: none; margin-bottom: 20px; padding: 14px 18px; border-radius: 10px; font-weight: 700; font-size: 14px;"></div>

      <!-- CARTE FORMULAIRE PRINCIPALE -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); width: 100%; box-sizing: border-box;">
        <form id="form-distribution" action="<?= RACINE ?>distribution/<?= $isEdit ? 'edit' : 'add' ?>" method="POST">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <input type="hidden" name="client_code" id="client_code" value="<?= htmlspecialchars($item['client_code'] ?? '') ?>">
          <input type="hidden" name="packs_data" id="packs_data_json" value="[]">
          <?php if ($isEdit): ?>
            <input type="hidden" name="id_distribution" value="<?= $item['id_distribution'] ?>">
          <?php endif; ?>
          
          <!-- SELECTION DE LA SOUSCRIPTION SOLDÉE -->
          <div style="margin-bottom: 28px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="file-check" style="width: 18px; height: 18px; color: #059669;"></i> Étape 1 : Sélection de la Souscription Soldée
            </h3>
            
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                Souscription Client Soldée <span style="color: #EF4444;">*</span>
              </label>
              <select name="souscription_code" id="select-souscription" class="form-control select2" style="width: 100%; box-sizing: border-box;" required <?= $isEdit ? 'disabled' : '' ?>>
                <option value="">-- Sélectionner une souscription soldée --</option>
                <?php foreach ($souscriptions as $s): ?>
                  <option value="<?= htmlspecialchars($s['code_souscription']) ?>" 
                          <?= ($item['souscription_code'] ?? '') === $s['code_souscription'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['code_souscription']) ?> - <?= htmlspecialchars($s['nom_client'] ?? 'Client') ?> (<?= htmlspecialchars($s['telephone_client'] ?? '') ?>) - <?= number_format((float)($s['montant_total_prevu'] ?? 0), 0, ',', ' ') ?> FCFA [SOLDE]
                  </option>
                <?php endforeach; ?>
              </select>
              <small style="color: #64748B; font-size: 12px; margin-top: 4px; display: block;">
                Seules les souscriptions ayant le statut "Solde" (100% cotisées) et non distribuées apparaissent dans cette liste.
              </small>
            </div>

            <!-- CARTE RECAPITULATIVE DU CLIENT -->
            <div id="client-summary-card" style="display: none; margin-top: 20px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span style="font-size: 11px; font-weight: 800; color: #059669; background: #ECFDF5; padding: 3px 10px; border-radius: 20px; border: 1px solid #A7F3D0; text-transform: uppercase;">
                  Souscription Élible à la Distribution
                </span>
                <span id="display-sub-code" style="font-family: monospace; font-size: 13px; font-weight: 800; color: #1E3A5F; background: #E2E8F0; padding: 2px 8px; border-radius: 6px;"></span>
              </div>
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
                <div>
                  <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Nom Client</span>
                  <div id="display-client-nom" style="font-size: 14px; font-weight: 800; color: #0F172A;">-</div>
                </div>
                <div>
                  <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Téléphone</span>
                  <div id="display-client-tel" style="font-size: 14px; font-weight: 800; color: #0F172A;">-</div>
                </div>
                <div>
                  <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Résidence</span>
                  <div id="display-client-lieu" style="font-size: 14px; font-weight: 800; color: #0F172A;">-</div>
                </div>
                <div>
                  <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Total Cotisé</span>
                  <div id="display-sub-montant" style="font-size: 14px; font-weight: 900; color: #059669;">-</div>
                </div>
              </div>
            </div>
          </div>

          <!-- TABLEAU DES PACKS & ARTICLES À LIVRER -->
          <div id="packs-section" style="display: none; margin-bottom: 28px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="boxes" style="width: 18px; height: 18px; color: #059669;"></i> Étape 2 : Packs & Articles de la Souscription
            </h3>

            <div style="overflow-x: auto; background: #F8FAFC; border-radius: 12px; border: 1px solid #E2E8F0; padding: 16px;">
              <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                  <tr style="border-bottom: 2px solid #CBD5E1; text-align: left; color: #475569; font-weight: 700;">
                    <th style="padding: 10px;">Pack Souscrit</th>
                    <th style="padding: 10px;">Catégorie</th>
                    <th style="padding: 10px;">Articles Inclus dans le Pack</th>
                    <th style="padding: 10px; text-align: center;">Qté Attendue</th>
                    <th style="padding: 10px; text-align: center; width: 140px;">Qté Livrée</th>
                  </tr>
                </thead>
                <tbody id="table-distribution-packs-body">
                  <tr>
                    <td colspan="5" style="padding: 20px; text-align: center; color: #94A3B8;">
                      Veuillez sélectionner une souscription pour afficher ses packs.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- INFORMATIONS AGENT & OBSERVATIONS -->
          <div style="margin-bottom: 28px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="user-check" style="width: 18px; height: 18px; color: #059669;"></i> Étape 3 : Agent Remettant & Observations
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
              <div class="form-group">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Agent de Distribution <small style="color: #64748B;">(Remettant / Validation)</small>
                </label>
                <select name="user_code" class="form-control select2" style="width: 100%; box-sizing: border-box;">
                  <?php foreach ($agents as $a): ?>
                    <option value="<?= htmlspecialchars($a['code_user']) ?>" <?= ($a['code_user'] === Context::user()) ? 'selected' : '' ?>>
                      <?= htmlspecialchars(trim(($a['nom_user'] ?? '') . ' ' . ($a['prenom_user'] ?? ''))) ?> (<?= htmlspecialchars($a['role_user'] ?? 'Agent') ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group" style="grid-column: 1 / -1;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Observations / Remarques de Livraison
                </label>
                <textarea name="observation_distribution" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A; resize: vertical;" rows="3" placeholder="État des marchandises, remarques du client..."><?= htmlspecialchars($item['observation_distribution'] ?? '') ?></textarea>
              </div>
            </div>
          </div>

          <!-- BOUTONS D'ACTION -->
          <div style="display: flex; justify-content: flex-end; gap: 14px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0;">
            <a href="<?= RACINE ?>distribution/list" class="btn" style="background: #FFFFFF; border: 1px solid #CBD5E1; color: #475569; font-weight: 700; padding: 12px 24px; border-radius: 10px; text-decoration: none;">
              Annuler
            </a>
            <button type="submit" id="btn-submit-distribution" class="btn" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #FFFFFF; font-weight: 800; padding: 12px 28px; border-radius: 10px; border: none; box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3); display: inline-flex; align-items: center; gap: 10px; cursor: pointer; transition: all 0.2s ease;">
              <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Confirmer & Valider la Distribution
            </button>
          </div>
        </form>
      </div>

    </div>
  </main>
</div>

<script>
var loadedPacksDetails = [];

function loadSouscriptionPacksInfo(souscriptionCode) {
  if (!souscriptionCode) {
    $('#client-summary-card').slideUp(200);
    $('#packs-section').slideUp(200);
    loadedPacksDetails = [];
    return;
  }

  $.ajax({
    url: '<?= RACINE ?>distribution/getPacksInfo',
    type: 'GET',
    data: { souscription_code: souscriptionCode },
    dataType: 'json',
    success: function(res) {
      if (res.status === 1 && res.souscription) {
        var s = res.souscription;
        $('#client_code').val(s.client_code);
        $('#display-sub-code').text(s.code_souscription);
        $('#display-client-nom').text(s.nom_client || '-');
        $('#display-client-tel').text(s.telephone_client || '-');
        $('#display-client-lieu').text(s.lieu_residence_client || '-');
        $('#display-sub-montant').text(parseFloat(s.montant_total_prevu || 0).toLocaleString('fr-FR') + ' FCFA');
        $('#client-summary-card').slideDown(200);

        if (res.packs && res.packs.length > 0) {
          loadedPacksDetails = res.packs;
          var tbody = $('#table-distribution-packs-body');
          tbody.empty();

          res.packs.forEach(function(pack, idx) {
            var articlesHtml = '';
            if (pack.articles && pack.articles.length > 0) {
              articlesHtml = pack.articles.map(function(art) {
                return '<span style="display:inline-block; background:#E2E8F0; color:#334155; padding:2px 8px; border-radius:6px; font-size:11px; font-weight:700; margin:2px;">' +
                       art.libelle_article + ' (x' + art.quantite_article + ')</span>';
              }).join(' ');
            } else {
              articlesHtml = '<em style="color:#94A3B8;">Aucun article configuré</em>';
            }

            var qteAttendue = parseInt(pack.quantite_article_attendue || 0);

            var row = '<tr style="border-bottom: 1px solid #E2E8F0;">' +
              '<td style="padding: 12px; font-weight: 800; color: #0F172A;">' + (pack.libelle_pack || 'Pack') + '</td>' +
              '<td style="padding: 12px;"><span style="font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 12px; background: #ECFDF5; color: #059669;">' + (pack.libelle_categorie_pack || 'Général') + '</span></td>' +
              '<td style="padding: 12px;">' + articlesHtml + '</td>' +
              '<td style="padding: 12px; text-align: center; font-weight: 800; color: #2563EB; font-size: 15px;">' + qteAttendue + '</td>' +
              '<td style="padding: 12px; text-align: center;">' +
                '<input type="number" class="form-control input-qte-livree" data-pack="' + pack.pack_code + '" data-attendue="' + qteAttendue + '" value="' + qteAttendue + '" min="0" max="' + qteAttendue + '" style="width: 90px; text-align: center; font-weight: 800; color: #059669; padding: 6px 10px; border-radius: 8px; border: 1px solid #CBD5E1;">' +
              '</td>' +
            '</tr>';
            tbody.append(row);
          });

          $('#packs-section').slideDown(200);
        } else {
          $('#table-distribution-packs-body').html('<tr><td colspan="5" style="padding: 20px; text-align: center; color: #94A3B8;">Aucun pack associé à cette souscription.</td></tr>');
          $('#packs-section').slideDown(200);
        }
        if (window.lucide) lucide.createIcons();
      } else {
        if (window.toastr) toastr.error(res.message || 'Erreur lors de la récupération de la souscription');
      }
    },
    error: function() {
      if (window.toastr) toastr.error('Erreur réseau lors de la récupération des détails de souscription.');
    }
  });
}

$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }

  $('#select-souscription').on('change', function() {
    loadSouscriptionPacksInfo($(this).val());
  });

  // Si pré-sélectionné
  var initialCode = $('#select-souscription').val();
  if (initialCode) {
    loadSouscriptionPacksInfo(initialCode);
  }

  $('#form-distribution').on('submit', function(e) {
    e.preventDefault();

    var souscriptionCode = $('#select-souscription').val();
    if (!souscriptionCode) {
      if (window.toastr) toastr.error('Veuillez sélectionner une souscription soldée.');
      return;
    }

    var packsData = [];
    $('.input-qte-livree').each(function() {
      var packCode = $(this).data('pack');
      var qteAttendue = parseInt($(this).data('attendue') || 0);
      var qteLivree = parseInt($(this).val() || 0);
      packsData.push({
        pack_code: packCode,
        quantite_article_attendue: qteAttendue,
        quantite_article_livree: qteLivree
      });
    });

    $('#packs_data_json').val(JSON.stringify(packsData));

    var btnSubmit = $('#btn-submit-distribution');
    btnSubmit.prop('disabled', true).html('<i data-lucide="loader" class="spin"></i> Enregistrement en cours...');

    $.ajax({
      url: $(this).attr('action'),
      type: 'POST',
      data: $(this).serialize() + '&packs=' + encodeURIComponent(JSON.stringify(packsData)),
      dataType: 'json',
      success: function(res) {
        btnSubmit.prop('disabled', false).html('<i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Confirmer & Valider la Distribution');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Distribution enregistrée avec succès !');
          setTimeout(function() {
            window.location.href = res.redirect || ('<?= RACINE ?>distribution/list');
          }, 1200);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
        }
      },
      error: function() {
        btnSubmit.prop('disabled', false).html('<i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Confirmer & Valider la Distribution');
        if (window.lucide) lucide.createIcons();
        if (window.toastr) toastr.error('Erreur réseau ou serveur indisponible');
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
