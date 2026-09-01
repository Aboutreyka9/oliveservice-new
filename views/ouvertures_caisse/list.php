<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Registre des Ouvertures de Caisse</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Historique des ouvertures de caisses et fonds de caisse initiaux</p>
        </div>
        <div style="display: flex; gap: 10px;">
          <a href="<?= RACINE ?>ouverture_caisse/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; color: #FFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="unlock" style="width: 18px; height: 18px;"></i> Nouvelle Ouverture Caisse
          </a>
          <a href="<?= RACINE ?>cloture_caisse/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="lock" style="width: 18px; height: 18px;"></i> Clôtures de Caisse
          </a>
        </div>
      </div>

      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <table id="table_ouvertures_caisse" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%;">
          <thead>
            <tr>
              <th>ID</th>
              <th>Code</th>
              <th>Date Ouverture</th>
              <th>Heure</th>
              <th>Fond Initial (FCFA)</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  $('#table_ouvertures_caisse').DataTable({
    ajax: {
      url: window.RACINE + 'ouverture_caisse/apiList',
      type: 'GET'
    },
    columns: [
      { data: 'id_ouverture' },
      { 
        data: 'code_ouverture',
        render: function(data) {
          return '<code style="font-weight:700; color:#1E3A5F;">' + (data || '-') + '</code>';
        }
      },
      { 
        data: 'date_ouverture',
        render: function(data) {
          if (!data) return '-';
          return new Date(data).toLocaleDateString('fr-FR');
        }
      },
      { data: 'heure_ouverture' },
      { 
        data: 'fond_initial',
        render: function(data) {
          return '<strong style="color:#0F172A;">' + Number(data || 0).toLocaleString('fr-FR') + ' FCFA</strong>';
        }
      },
      { 
        data: 'statut_ouverture',
        width: '130px',
        className: 'text-center',
        render: function(d, type, row) {
          var val = d || 'ouverte';
          var isOuverte = (val === 'ouverte');
          var currentBg = isOuverte ? '#DCFCE7' : '#F1F5F9';
          var currentText = isOuverte ? '#166534' : '#475569';
          var currentBorder = isOuverte ? '#86EFAC' : '#CBD5E1';

          return '<select class="select-statut-ouverture" data-id="' + row.id_ouverture + '" style="background:' + currentBg + '; color:' + currentText + '; border:1px solid ' + currentBorder + '; font-weight:700; font-size:12px; border-radius:8px; padding:4px 8px; cursor:pointer; outline:none;">' +
                 '<option value="ouverte" ' + (isOuverte ? 'selected' : '') + ' style="background:#fff; color:#166534;">Ouverte</option>' +
                 '<option value="cloturee" ' + (!isOuverte ? 'selected' : '') + ' style="background:#fff; color:#475569;">Clôturée</option>' +
                 '</select>';
        }
      },
      { 
        data: null,
        orderable: false,
        render: function(data, type, row) {
          return '<a href="' + window.RACINE + 'ouverture_caisse/edition/' + (row.editId || row.id_ouverture) + '" class="btn btn-sm btn-info" style="border-radius:6px; font-weight:600; padding:4px 10px;"><i data-lucide="edit-3" style="width:14px; height:14px;"></i> Éditer</a>';
        }
      }
    ],
    language: {
      url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json'
    },
    drawCallback: function() {
      if (window.lucide) lucide.createIcons();
    }
  });

  $(document).on('change', '.select-statut-ouverture', function() {
    var id = $(this).data('id');
    var newStatut = $(this).val();

    $.ajax({
      url: window.RACINE + 'ouverture_caisse/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: id,
        statut: newStatut,
        csrf_token: '<?= Validator::generateCsrfToken() ?>'
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Statut mis à jour avec succès');
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors du changement de statut');
          table.ajax.reload(null, false);
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur réseau');
        table.ajax.reload(null, false);
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
