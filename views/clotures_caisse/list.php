<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Clôtures de Caisse Journalières</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion et consultation du registre Clôtures de Caisse Journalières</p>
        </div>
        <a href="<?= RACINE ?>cloture_caisse/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Clôture de Caisse
        </a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-clotures_caisse" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px;">ID</th>
                <th style="padding: 12px;">Code Clôture</th>
                <th style="padding: 12px;">Date Caisse</th>
                <th style="padding: 12px;">Espèces (FCFA)</th>
                <th style="padding: 12px;">Mobile Money</th>
                <th style="padding: 12px;">Chèques / Vir.</th>
                <th style="padding: 12px;">Total Général</th>
                <th style="padding: 12px;">Statut</th>
                <th style="padding: 12px; text-align: right;">Actions</th>
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
  var table = $('#table-clotures_caisse').DataTable({
    ajax: '<?= RACINE ?>cloture_caisse/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_cloture', defaultContent: '-' },
      { data: 'code_cloture', render: function(d) {
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
      } },
      { data: 'date_cloture', defaultContent: '-' },
      { data: 'total_especes', render: function(d) { return d ? Number(d).toLocaleString('fr-FR') + ' F' : '0 F'; } },
      { data: 'total_mobile_money', render: function(d) { return d ? Number(d).toLocaleString('fr-FR') + ' F' : '0 F'; } },
      { data: 'total_cheque_virement', render: function(d) { return d ? Number(d).toLocaleString('fr-FR') + ' F' : '0 F'; } },
      { data: 'total_general', render: function(d) {
        return '<strong style="color:#0F172A;">' + (d ? Number(d).toLocaleString('fr-FR') + ' FCFA' : '0 FCFA') + '</strong>';
      } },
      { data: 'statut_cloture', width: '130px', className: 'text-center', render: function(d, type, row) {
        var val = d || 'attente';
        var bgColors = { 'valide': '#DCFCE7', 'attente': '#FEF3C7', 'rejete': '#FEE2E2' };
        var textColors = { 'valide': '#15803D', 'attente': '#B45309', 'rejete': '#B91C1C' };
        var borderColors = { 'valide': '#86EFAC', 'attente': '#FCD34D', 'rejete': '#FCA5A5' };
        var currentBg = bgColors[val] || '#F1F5F9';
        var currentText = textColors[val] || '#334155';
        var currentBorder = borderColors[val] || '#CBD5E1';

        return '<select class="select-statut-cloture" data-id="' + row.id_cloture + '" style="background:' + currentBg + '; color:' + currentText + '; border:1px solid ' + currentBorder + '; font-weight:700; font-size:12px; border-radius:8px; padding:4px 8px; cursor:pointer; outline:none;">' +
               '<option value="attente" ' + (val === 'attente' ? 'selected' : '') + ' style="background:#fff; color:#B45309;">En attente</option>' +
               '<option value="valide" ' + (val === 'valide' ? 'selected' : '') + ' style="background:#fff; color:#15803D;">Validée</option>' +
               '<option value="rejete" ' + (val === 'rejete' ? 'selected' : '') + ' style="background:#fff; color:#B91C1C;">Rejetée</option>' +
               '</select>';
      } },
      { data: null, orderable: false, render: function(d) {
        return '<a href="' + window.RACINE + 'cloture_caisse/edition/' + (d.editId || d.id_cloture) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="' + window.RACINE + 'cloture_caisse/details/' + (d.editId || d.id_cloture) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  $(document).on('change', '.select-statut-cloture', function() {
    var id = $(this).data('id');
    var newStatut = $(this).val();

    $.ajax({
      url: '<?= RACINE ?>cloture_caisse/changer',
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
