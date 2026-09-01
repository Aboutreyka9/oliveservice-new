<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- En-tête Général du Hub -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="layers" style="color: #1E3A5F; width: 24px; height: 24px;"></i> Filières & Cycles d'Études
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion centralisée du Catalogue des Filières, des Cycles et de leurs Assignations</p>
        </div>
      </div>

      <!-- Barre d'Onglets Structurée -->
      <div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 2px solid #E2E8F0; padding-bottom: 2px; flex-wrap: wrap;">
        <button type="button" class="tab-btn active" data-tab="tab-assignations" style="padding: 10px 20px; font-weight: 700; font-size: 14px; border: none; background: transparent; color: #1E3A5F; border-bottom: 3px solid #1E3A5F; cursor: pointer; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="git-merge" style="width: 16px; height: 16px;"></i> 1. Assignations Filières - Cycles
        </button>
        <button type="button" class="tab-btn" data-tab="tab-filieres" style="padding: 10px 20px; font-weight: 700; font-size: 14px; border: none; background: transparent; color: #64748B; border-bottom: 3px solid transparent; cursor: pointer; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="book-open" style="width: 16px; height: 16px;"></i> 2. Catalogue des Filières
        </button>
        <button type="button" class="tab-btn" data-tab="tab-cycles" style="padding: 10px 20px; font-weight: 700; font-size: 14px; border: none; background: transparent; color: #64748B; border-bottom: 3px solid transparent; cursor: pointer; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="layers" style="width: 16px; height: 16px;"></i> 3. Cycles d'Études
        </button>
      </div>

      <!-- CONTENU DU TAB 1 : ASSIGNATIONS -->
      <div id="tab-assignations" class="tab-content" style="display: block;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
          <h2 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin: 0;">Table d'Assignation Filières ↔ Cycles</h2>
          <a href="<?= RACINE ?>filiere_cycle/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px;">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Nouvelle Assignation
          </a>
        </div>
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
          <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table id="table-assignations" class="table display nowrap" style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                  <th style="padding: 12px;">ID</th>
                  <th style="padding: 12px;">Code</th>
                  <th style="padding: 12px;">Cycle D'Études</th>
                  <th style="padding: 12px;">Filière Associée</th>
                  <th style="padding: 12px;" class="text-center">Statut</th>
                  <th style="padding: 12px; text-align: right;">Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- CONTENU DU TAB 2 : CATALOGUE FILIÈRES -->
      <div id="tab-filieres" class="tab-content" style="display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
          <h2 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin: 0;">Catalogue Général des Filières</h2>
          <a href="<?= RACINE ?>filiere/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px;">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Ajouter une Filière
          </a>
        </div>
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
          <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table id="table-filieres-catalogue" class="table display nowrap" style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                  <th style="padding: 12px;">ID</th>
                  <th style="padding: 12px;">Code</th>
                  <th style="padding: 12px;">Nom de la Filière</th>
                  <th style="padding: 12px;">Description</th>
                  <th style="padding: 12px;" class="text-center">Statut</th>
                  <th style="padding: 12px; text-align: right;">Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- CONTENU DU TAB 3 : CYCLES D'ÉTUDES -->
      <div id="tab-cycles" class="tab-content" style="display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
          <h2 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin: 0;">Référentiel des Cycles d'Études</h2>
          <a href="<?= RACINE ?>cycle/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px;">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Ajouter un Cycle
          </a>
        </div>
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
          <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table id="table-cycles-list" class="table display nowrap" style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                  <th style="padding: 12px;">ID</th>
                  <th style="padding: 12px;">Code Cycle</th>
                  <th style="padding: 12px;">Libellé du Cycle</th>
                  <th style="padding: 12px;">Description</th>
                  <th style="padding: 12px;" class="text-center">Statut</th>
                  <th style="padding: 12px; text-align: right;">Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  // Gestion des Onglets
  $('.tab-btn').on('click', function() {
    $('.tab-btn').css({ 'color': '#64748B', 'border-bottom-color': 'transparent' }).removeClass('active');
    $(this).css({ 'color': '#1E3A5F', 'border-bottom-color': '#1E3A5F' }).addClass('active');
    $('.tab-content').hide();
    var targetTab = $(this).data('tab');
    $('#' + targetTab).show();
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
  });

  // Table 1: Assignations
  var tableAssign = $('#table-assignations').DataTable({
    ajax: '<?= RACINE ?>filiere_cycle/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_filiere_cycle', defaultContent: '-', width: '50px' },
      { data: 'code_filiere_cycle', width: '120px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
      }},
      { data: 'libelle_cycle', width: '180px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="font-weight:700; color:#1E3A5F;">' + (d || 'Non défini') + '</span>';
      }},
      { data: 'libelle_filiere', width: '200px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="font-weight:700; color:#0F172A;">' + (d || 'Non défini') + '</span>';
      }},
      { data: 'statut_filiere_cycle', width: '100px', className: 'text-center', render: function(d, type) {
        if (type !== 'display') return d || '';
        return d === 'actif' ? '<span style="display:inline-block; background:#DCFCE7; color:#15803D; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Actif</span>' : '<span style="display:inline-block; background:#FEE2E2; color:#B91C1C; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Inactif</span>';
      }},
      { data: null, width: '160px', orderable: false, className: 'text-end', render: function(d) {
        return '<a href="<?= RACINE ?>filiere_cycle/edition/' + (d.editId || d.id_filiere_cycle) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="<?= RACINE ?>filiere_cycle/details/' + (d.editId || d.id_filiere_cycle) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }}
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Table 2: Catalogue Filières
  var tableFilieres = $('#table-filieres-catalogue').DataTable({
    ajax: '<?= RACINE ?>filiere/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_filiere', defaultContent: '-', width: '50px' },
      { data: 'code_filiere', width: '120px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
      }},
      { data: 'libelle_filiere', width: '200px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="font-weight:700; color:#0F172A;">' + (d || '-') + '</span>';
      }},
      { data: 'description_filiere', defaultContent: '-', render: function(d, type) {
        if (type !== 'display') return d || '';
        return d || '-';
      }},
      { data: 'statut_filiere', width: '100px', className: 'text-center', render: function(d, type) {
        if (type !== 'display') return d || '';
        return d === 'actif' ? '<span style="display:inline-block; background:#DCFCE7; color:#15803D; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Actif</span>' : '<span style="display:inline-block; background:#FEE2E2; color:#B91C1C; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Inactif</span>';
      }},
      { data: null, width: '160px', orderable: false, className: 'text-end', render: function(d) {
        return '<a href="<?= RACINE ?>filiere/edition/' + (d.editId || d.id_filiere) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="<?= RACINE ?>filiere/details/' + (d.editId || d.id_filiere) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }}
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Table 3: Cycles d'Études
  var tableCycles = $('#table-cycles-list').DataTable({
    ajax: '<?= RACINE ?>cycle/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_cycle', defaultContent: '-', width: '50px' },
      { data: 'code_cycle', width: '120px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
      }},
      { data: 'libelle_cycle', width: '200px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="font-weight:700; color:#1E3A5F;">' + (d || '-') + '</span>';
      }},
      { data: 'description_cycle', defaultContent: '-', render: function(d, type) {
        if (type !== 'display') return d || '';
        return d || '-';
      }},
      { data: 'statut_cycle', width: '100px', className: 'text-center', render: function(d, type) {
        if (type !== 'display') return d || '';
        return d === 'actif' ? '<span style="display:inline-block; background:#DCFCE7; color:#15803D; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Actif</span>' : '<span style="display:inline-block; background:#FEE2E2; color:#B91C1C; padding:4px 10px; border-radius:12px; font-weight:700; font-size:12px;">Inactif</span>';
      }},
      { data: null, width: '160px', orderable: false, className: 'text-end', render: function(d) {
        return '<a href="<?= RACINE ?>cycle/edition/' + (d.editId || d.id_cycle) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="<?= RACINE ?>cycle/details/' + (d.editId || d.id_cycle) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }}
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>