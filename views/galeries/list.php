<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Galeries Photos & Vidéos</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion et consultation du registre Galeries Photos & Vidéos</p>
        </div>
        <a href="<?= RACINE ?>galerie/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Galerie Médias
        </a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-galeries" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px;">ID</th>
                <th style="padding: 12px;">Code Album</th>
                <th style="padding: 12px;">Titre Album</th>
                <th style="padding: 12px;">Type Média</th>
                <th style="padding: 12px; text-align: center;" class="text-center">Statut</th>
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
  var table = $('#table-galeries').DataTable({
    ajax: '<?= RACINE ?>galerie/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_galerie',    defaultContent: '-', width: '50px' },
      { data: 'code_galerie',  defaultContent: '-', width: '130px' },
      { data: 'titre_galerie', defaultContent: '-' },
      { data: 'type_galerie',  defaultContent: '-', width: '110px', render: function(d) {
        if (!d) return '-';
        return d === 'video'
          ? '<span style="background:#F1F5F9;color:#475569;padding:3px 10px;border-radius:10px;font-weight:700;font-size:12px;display:inline-block;">Vidéo</span>'
          : '<span style="background:#EFF6FF;color:#1E3A5F;padding:3px 10px;border-radius:10px;font-weight:700;font-size:12px;display:inline-block;">Photo</span>';
      }},
      { data: 'statut_galerie', defaultContent: '-', width: '130px', className: 'text-center', render: function(d, type, row) {
        var val = d || 'actif';
        var bgColors = { 'actif': '#DCFCE7', 'brouillon': '#FEF3C7', 'archive': '#F1F5F9' };
        var textColors = { 'actif': '#15803D', 'brouillon': '#B45309', 'archive': '#475569' };
        var borderColors = { 'actif': '#86EFAC', 'brouillon': '#FCD34D', 'archive': '#CBD5E1' };
        var currentBg = bgColors[val] || '#F1F5F9';
        var currentText = textColors[val] || '#334155';
        var currentBorder = borderColors[val] || '#CBD5E1';

        return '<select class="select-statut-galerie" data-id="' + row.id_galerie + '" style="background:' + currentBg + '; color:' + currentText + '; border:1px solid ' + currentBorder + '; font-weight:700; font-size:12px; border-radius:8px; padding:4px 8px; cursor:pointer; outline:none;">' +
               '<option value="actif" ' + (val === 'actif' ? 'selected' : '') + ' style="background:#fff; color:#15803D;">Actif</option>' +
               '<option value="brouillon" ' + (val === 'brouillon' ? 'selected' : '') + ' style="background:#fff; color:#B45309;">Brouillon</option>' +
               '<option value="archive" ' + (val === 'archive' ? 'selected' : '') + ' style="background:#fff; color:#475569;">Archivé</option>' +
               '</select>';
      }},
      { data: null, width: '160px', orderable: false, render: function(d) {
        return '<a href="' + window.RACINE + 'galerie/edition/' + (d.editId || d.id_galerie) + '" class="btn btn-sm btn-secondary" style="margin-right:5px;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>'
             + '<a href="' + window.RACINE + 'galerie/details/' + (d.editId || d.id_galerie) + '" class="btn btn-sm btn-info" style="font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Changement de statut via Ajax
  $(document).on('change', '.select-statut-galerie', function() {
    var id = $(this).data('id');
    var newStatut = $(this).val();

    $.ajax({
      url: '<?= RACINE ?>galerie/changer',
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
