<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php $currentSection = 'cautisation'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">

      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="users" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Recherche & Encaissement Cautisations
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Sélectionnez un client dans la liste pour consulter ses souscriptions et effectuer un paiement
            </p>
          </div>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <button type="button" id="btnRefresh" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;">
            <i data-lucide="refresh-cw" style="width: 16px; height: 16px; color: #64748B;"></i> Réinitialiser
          </button>
          <a href="<?= RACINE ?>souscription/wizard" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 20px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); text-decoration: none; transition: all 0.2s ease;">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Nouvelle souscription
          </a>
        </div>
      </div>

      <!-- FORMULAIRE DE SELECTION CLIENT (SELECT2) -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box; margin-bottom: 24px;">
        <label style="display: block; font-weight: 800; font-size: 14px; color: #0F172A; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="user-check" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Sélectionner un client
        </label>
        
        <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
          <div style="flex: 1; min-width: 280px;">
            <select id="selectClient" class="form-control select2" style="width: 100%;">
              <option value="">-- Rechercher ou choisir un client dans la liste --</option>
              <?php if (!empty($clients) && is_array($clients)): ?>
                <?php foreach ($clients as $client): ?>
                  <option value="<?= htmlspecialchars($client['code_client']) ?>">
                    <?= htmlspecialchars($client['nom_client']) ?><?= !empty($client['telephone_client']) ? ' (' . htmlspecialchars($client['telephone_client']) . ')' : '' ?> - [<?= htmlspecialchars($client['code_client']) ?>]
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <button type="button" id="searchBtn" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 12px; padding: 14px 28px; font-size: 15px; border: none; display: inline-flex; align-items: center; gap: 10px; cursor: pointer; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.25); min-width: 160px; justify-content: center;">
            <i data-lucide="search" style="width: 18px; height: 18px;"></i> Rechercher
          </button>
        </div>

        <!-- Informations d'aide -->
        <div style="display: flex; align-items: center; gap: 8px; margin-top: 14px; flex-wrap: wrap;">
          <span style="font-size: 12px; font-weight: 700; color: #64748B;">Astuce :</span>
          <span style="font-size: 12px; color: #64748B;">Vous pouvez saisir le nom, le numéro de téléphone ou le code du client directement dans la liste déroulante Select2.</span>
        </div>
      </div>

      <!-- RÉSULTATS DE RECHERCHE -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box;">
        
        <!-- Placeholder initial -->
        <div id="searchPlaceholder" style="text-align: center; padding: 60px 20px; color: #94A3B8;">
          <div style="width: 64px; height: 64px; border-radius: 20px; background: #F8FAFC; color: #94A3B8; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto; border: 1px solid #E2E8F0;">
            <i data-lucide="search" style="width: 32px; height: 32px; opacity: 0.7;"></i>
          </div>
          <h4 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0 0 6px 0;">Effectuez une recherche</h4>
          <p style="font-size: 13px; margin: 0; color: #64748B;">Sélectionnez un client ci-dessus et cliquez sur <strong>Rechercher</strong> pour afficher immédiatement ses souscriptions et sa situation financière.</p>
        </div>

        <!-- Conteneur des résultats -->
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; display: none;" id="searchResultsContainer">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="layers" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Souscriptions disponibles pour ce client
            </h3>
          </div>

          <table id="table-search-results" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse; font-size: 13px;">
            <thead>
              <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Client</th>
                <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Téléphone</th>
                <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Code Souscription</th>
                <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Session</th>
                <th style="padding: 12px 14px; text-align: right; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Montant Total</th>
                <th style="padding: 12px 14px; text-align: center; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Statut</th>
                <th style="padding: 12px 14px; text-align: right; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Action</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

      </div>

    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  // Initialisation de Select2
  $('#selectClient').select2({
    placeholder: "-- Rechercher ou choisir un client dans la liste --",
    allowClear: true,
    width: '100%'
  });

  const searchBtn = document.getElementById('searchBtn');
  const searchResultsContainer = document.getElementById('searchResultsContainer');
  const searchPlaceholder = document.getElementById('searchPlaceholder');
  let dataTable = null;

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  function formatCurrency(amount) {
    return Number(amount || 0).toLocaleString('fr-FR') + ' FCFA';
  }

  function renderStatut(statut) {
    const s = String(statut || '').toLowerCase();
    if (s === 'valide' || s === 'validé') {
      return '<span style="background: #ECFDF5; color: #047857; padding: 5px 12px; border-radius: 20px; font-weight: 800; font-size: 11px; border: 1px solid #A7F3D0; display: inline-flex; align-items: center; gap: 4px;"><i data-lucide="check-circle" style="width:12px; height:12px;"></i> Validée</span>';
    } else if (s === 'solde' || s === 'soldé') {
      return '<span style="background: #EFF6FF; color: #1D4ED8; padding: 5px 12px; border-radius: 20px; font-weight: 800; font-size: 11px; border: 1px solid #BFDBFE; display: inline-flex; align-items: center; gap: 4px;"><i data-lucide="shield-check" style="width:12px; height:12px;"></i> Soldée</span>';
    } else if (s === 'annule' || s === 'annulé') {
      return '<span style="background: #FEE2E2; color: #B91C1C; padding: 5px 12px; border-radius: 20px; font-weight: 800; font-size: 11px; border: 1px solid #FCA5A5; display: inline-flex; align-items: center; gap: 4px;"><i data-lucide="x-circle" style="width:12px; height:12px;"></i> Annulée</span>';
    } else if (s === 'reconduite') {
      return '<span style="background: #FEF3C7; color: #B45309; padding: 5px 12px; border-radius: 20px; font-weight: 800; font-size: 11px; border: 1px solid #FDE68A; display: inline-flex; align-items: center; gap: 4px;"><i data-lucide="rotate-cw" style="width:12px; height:12px;"></i> Reconduite</span>';
    }
    return '<span style="background: #F1F5F9; color: #475569; padding: 5px 12px; border-radius: 20px; font-weight: 700; font-size: 11px;">' + escapeHtml(statut || '-') + '</span>';
  }

  function performSearch() {
    const selectedClientCode = $('#selectClient').val();

    if (!selectedClientCode) {
      toastr.error('Veuillez sélectionner un client dans la liste.');
      return;
    }

    if (dataTable) {
      dataTable.destroy();
      dataTable = null;
    }

    searchPlaceholder.style.display = 'none';
    searchResultsContainer.style.display = 'block';

    const table = $('#table-search-results').DataTable({
      ajax: {
        url: '<?= RACINE ?>cautisation-payment/search',
        type: 'POST',
        data: function(d) {
          d.criteria = selectedClientCode;
          d.type = 'all';
        },
        dataSrc: function(json) {
          if (json.error) {
            toastr.error(json.error);
            return [];
          }
          if (json.message) {
            toastr.info(json.message);
            return [];
          }
          return json.data || [];
        },
        error: function() {
          toastr.error('Erreur réseau lors de la recherche.');
          return [];
        }
      },
      processing: true,
      autoWidth: false,
      columns: [
        { data: 'nom_complet', render: function(d) {
            return '<div style="font-weight:800; color:#0F172A; font-size:14px;">' + escapeHtml(d || '-') + '</div>';
        }},
        { data: 'telephone', render: function(d) {
            return d ? '<a href="tel:' + escapeHtml(d) + '" style="font-weight:700; color:#1E3A5F; text-decoration:none;"><i data-lucide="phone" style="width:12px; height:12px; vertical-align:middle; display:inline-block;"></i> ' + escapeHtml(d) + '</a>' : '-';
        }},
        { data: 'code_souscription', render: function(d) {
            return d ? '<code style="font-weight:800; color:#1E3A5F; background:#F1F5F9; padding:4px 8px; border-radius:6px; font-family:monospace;"><i data-lucide="hash" style="width:12px; height:12px; vertical-align:middle; display:inline-block;"></i> ' + escapeHtml(d) + '</code>' : '-';
        }},
        { data: 'libelle_session', defaultContent: '-' },
        { data: 'montant_total', render: function(d) {
            return '<strong style="color:#059669; font-size:14px; font-weight:800;">' + formatCurrency(d) + '</strong>';
        }},
        { data: 'statut', className: 'text-center', render: function(d) {
            return renderStatut(d);
        }},
        { data: null, width: '130px', orderable: false, className: 'text-end', render: function(d) {
            const code = escapeHtml(d.code_sousscription || d.code_souscription || '');
            return '<a href="<?= RACINE ?>cautisation-payment/situation?code=' + code + '" class="btn btn-sm" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; border: none; border-radius: 8px; padding: 7px 14px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(15, 23, 42, 0.2); text-decoration: none;"><i data-lucide="eye" style="width:14px; height:14px;"></i> Situation</a>';
        }}
      ],
      language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
      drawCallback: function() { if (window.lucide) lucide.createIcons(); }
    });
    dataTable = $('#table-search-results').DataTable();
  }

  searchBtn.addEventListener('click', performSearch);

  $('#selectClient').on('change', function() {
    if ($(this).val()) {
      performSearch();
    }
  });

  document.getElementById('btnRefresh').addEventListener('click', function() {
    $('#selectClient').val('').trigger('change.select2');
    if (dataTable) {
      dataTable.destroy();
      dataTable = null;
    }
    searchPlaceholder.style.display = 'block';
    searchResultsContainer.style.display = 'none';
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
