<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="calendar" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Années Académiques & Exercices
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Gestion et consultation du registre des années et exercices d'activité
            </p>
          </div>
        </div>

        <?php if (Context::can('GESTIONNAIRE_MANAGE_ANNEES', ['ROLE_GESTIONNAIRE', 'ROLE_ADMIN'])): ?>
        <a href="<?= RACINE ?>annee/formulaire" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 12px 22px; font-size: 14px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); text-decoration: none; cursor: pointer;">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle Année
        </a>
        <?php endif; ?>
      </div>

      <!-- CARTE TABLEAU PRINCIPALE (NAVY PREMIUM) -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-annees" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse; font-size: 13px;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 2px solid #E2E8F0;">
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">ID</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Code</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Année Académique</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Date Début</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Date Fin</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: center;">Statut</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: right;">Actions</th>
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
  var table = $('#table-annees').DataTable({
    ajax: '<?= RACINE ?>annee/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_annee', defaultContent: '-', width: '50px' },
      { data: 'code_annee', width: '130px', render: function(d) {
        if (!d) return '-';
        return '<code style="font-weight:700; color:#1E3A5F; background:#F1F5F9; padding:4px 8px; border-radius:6px; font-size:12px; border:1px solid #CBD5E1;">' + d + '</code>';
      }},
      { data: 'libelle_annee', render: function(d) {
        return '<span style="font-weight:800; color:#0F172A; font-size:14px;">' + (d || '-') + '</span>';
      }},
      { data: 'date_debut_annee', render: function(d) {
        if (!d) return '-';
        return '<span style="font-size:12px; color:#475569; font-weight:600;">' + d + '</span>';
      }},
      { data: 'date_fin_annee', render: function(d) {
        if (!d) return '-';
        return '<span style="font-size:12px; color:#475569; font-weight:600;">' + d + '</span>';
      }},
      { data: 'statut_annee', width: '90px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        var canManage = (window.AppConfig && typeof window.AppConfig.can === 'function') ? window.AppConfig.can('GESTIONNAIRE_MANAGE_ANNEES') : false;
        var disabledAttr = canManage ? '' : 'disabled';
        var cursorStyle = canManage ? 'cursor:pointer;' : 'cursor:not-allowed; opacity:0.6;';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:42px; height:22px; margin:0; ' + cursorStyle + '" title="' + (isActif ? 'Actif' : 'Inactif') + '">' +
               '<input type="checkbox" class="toggle-statut-annee" data-id="' + row.id_annee + '" ' + checkedAttr + ' ' + disabledAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; ' + cursorStyle + ' top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#059669' : '#CBD5E1') + '; transition:.3s; border-radius:20px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">' +
               '<span style="position:absolute; content:\'\'; height:16px; width:16px; left:' + (isActif ? '23px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%; box-shadow: 0 1px 3px rgba(0,0,0,0.2);"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},
      { data: null, width: '170px', orderable: false, render: function(d) {
        var editId = d.editId || d.id_annee;
        var canManage = (window.AppConfig && typeof window.AppConfig.can === 'function') ? window.AppConfig.can('GESTIONNAIRE_MANAGE_ANNEES') : false;
        var html = '<div style="display:flex; justify-content:flex-end; gap:6px;">';
        if (canManage) {
          html += '<a href="' + window.RACINE + 'annee/edition/' + editId + '" class="btn" style="background:#F1F5F9; color:#1E3A5F; font-weight:700; border-radius:8px; padding:6px 12px; text-decoration:none; border:1px solid #CBD5E1; display:inline-flex; align-items:center; gap:4px; font-size:12px;" title="Modifier"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>';
        }
        html += '<a href="' + window.RACINE + 'annee/details/' + editId + '" class="btn" style="background:#1E3A5F; color:#FFFFFF; font-weight:700; border-radius:8px; padding:6px 12px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-size:12px;" title="Voir détails"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>' +
                '</div>';
        return html;
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  $(document).on('change', '.toggle-statut-annee', function() {
    var id = $(this).data('id');
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    $.ajax({
      url: '<?= RACINE ?>annee/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: id,
        csrf_token: '<?= Validator::generateCsrfToken() ?>'
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Statut mis à jour avec succès');
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors du changement de statut');
          $input.prop('checked', !isChecked);
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur réseau');
        $input.prop('checked', !isChecked);
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
