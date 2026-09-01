<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Scolarités & Échéanciers de Paiement</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion centralisée des grilles tarifaires et des tranches de versement</p>
        </div>
        <div id="btn-container">
          <a href="<?= RACINE ?>scolarite/formulaire" id="btn-add-action" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> <span id="btn-add-label">Ajouter Tarif de Scolarité</span>
          </a>
        </div>
      </div>

      <!-- Bandeau Résumé / Indicateurs Clés -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- Total Grilles -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="calculator" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Grilles Tarifaires</div>
            <div style="font-size: 22px; font-weight: 800; color: #0F172A; line-height: 1.2;"><?= (int)($totalScolarites ?? 0) ?></div>
          </div>
        </div>

        <!-- Régime Affecté (État) -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="check-circle-2" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 700; color: #15803D; text-transform: uppercase; letter-spacing: 0.5px;">Tarifs Affectés (État)</div>
            <div style="font-size: 22px; font-weight: 800; color: #15803D; line-height: 1.2;"><?= (int)($totalAffectes ?? 0) ?></div>
          </div>
        </div>

        <!-- Régime Non Affecté (Privé) -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #F1F5F9; color: #475569; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="user-check" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Tarifs Privés (Non Affectés)</div>
            <div style="font-size: 22px; font-weight: 800; color: #0F172A; line-height: 1.2;"><?= (int)($totalNonAffectes ?? 0) ?></div>
          </div>
        </div>

        <!-- Échéanciers / Tranches -->
        <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 16px;">
          <div style="width: 46px; height: 46px; border-radius: 10px; background: #FAF5FF; color: #7E22CE; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="calendar" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11.5px; font-weight: 700; color: #7E22CE; text-transform: uppercase; letter-spacing: 0.5px;">Tranches Configurées</div>
            <div style="font-size: 22px; font-weight: 800; color: #7E22CE; line-height: 1.2;"><?= (int)($totalTranches ?? 0) ?></div>
          </div>
        </div>

      </div>

      <!-- Nav Tabs -->
      <ul class="nav nav-tabs" id="scolariteTabs" role="tablist" style="border-bottom: 2px solid #E2E8F0; margin-bottom: 20px; gap: 8px; list-style: none; padding-left: 0; display: flex;">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="tab-scolarites-btn" data-bs-target="#tab-scolarites" type="button" role="tab" style="font-weight: 700; padding: 12px 22px; border-radius: 8px 8px 0 0; color: #1E3A5F; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; border: 1px solid #E2E8F0; border-bottom: none; background: #FFFFFF; cursor: pointer;">
            <i data-lucide="receipt" style="width: 16px; height: 16px;"></i> Grille des Scolarités
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tab-tranches-btn" data-bs-target="#tab-tranches" type="button" role="tab" style="font-weight: 700; padding: 12px 22px; border-radius: 8px 8px 0 0; color: #64748B; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; border: 1px solid #E2E8F0; border-bottom: none; background: #F8FAFC; cursor: pointer;">
            <i data-lucide="list-checks" style="width: 16px; height: 16px;"></i> Échéanciers de Paiement
          </button>
        </li>
      </ul>

      <div class="tab-content" id="scolariteTabsContent">
        <!-- TAB 1 : GRILLE DES SCOLARITÉS -->
        <div class="tab-pane fade show active" id="tab-scolarites" role="tabpanel">
          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
            <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
              <table id="table-scolarites" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
                <thead>
                  <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                    <th style="padding: 12px;">ID</th>
                    <th style="padding: 12px;">Code</th>
                    <th style="padding: 12px;">Année</th>
                    <th style="padding: 12px;">Filière</th>
                    <th style="padding: 12px;">Niveau</th>
                    <th style="padding: 12px;">Régime</th>
                    <th style="padding: 12px;">Montant (FCFA)</th>
                    <th class="text-center" style="padding: 12px;">Statut</th>
                    <th class="text-end" style="padding: 12px;">Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- TAB 2 : ÉCHEANCIERS DE PAIEMENT -->
        <div class="tab-pane fade" id="tab-tranches" role="tabpanel" style="display: none;">
          <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
            <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
              <table id="table-tranches_scolarite" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
                <thead>
                  <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                    <th style="padding: 12px;">ID</th>
                    <th style="padding: 12px;">Code</th>
                    <th style="padding: 12px;">Scolarité Rattachée</th>
                    <th style="padding: 12px;">Libellé Tranche</th>
                    <th style="padding: 12px;">Montant (FCFA)</th>
                    <th style="padding: 12px;">Date Limite</th>
                    <th class="text-center" style="padding: 12px;">Statut</th>
                    <th class="text-end" style="padding: 12px;">Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>
<script>
$(document).ready(function() {
  var tableScolarites = $('#table-scolarites').DataTable({
    ajax: '<?= RACINE ?>scolarite/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_scolarite', defaultContent: '-' },
      { data: 'code_scolarite', render: function(d) { return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>'; } },
      { data: 'libelle_annee', render: function(d, t, r) { 
        return '<span class="badge" style="background:#EFF6FF; color:#1E3A5F; font-weight:700; font-size:12px; padding:3px 8px; border-radius:6px;">' + (d || r.annee_code || 'Toutes') + '</span>'; 
      } },
      { data: 'libelle_filiere', render: function(d, t, r) { return '<span style="font-weight:700; color:#0F172A;">' + (d || r.filiere_code || 'Non définie') + '</span>'; } },
      { data: 'libelle_niveau', render: function(d, t, r) { return '<span style="font-weight:700; color:#1E3A5F;">' + (d || r.niveau_code || 'Non défini') + '</span>'; } },
      { data: 'affectation_etat', render: function(d) {
        return (d === 'affecte')
          ? '<span class="badge" style="background:#DCFCE7; color:#15803D; font-weight:700; font-size:11.5px; padding:3px 8px; border-radius:6px;">Affecté (État)</span>'
          : '<span class="badge" style="background:#F1F5F9; color:#475569; font-weight:700; font-size:11.5px; padding:3px 8px; border-radius:6px;">Non Affecté (Privé)</span>';
      } },
      { data: 'montant_scolarite', render: function(d) { 
        return '<span style="font-weight:800; color:#0F172A;">' + (d ? Number(d).toLocaleString('fr-FR') + ' FCFA' : '0 FCFA') + '</span>'; 
      } },
      { data: 'statut_scolarite', width: '90px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-scolarite" data-id="' + row.id_scolarite + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      } },
      { data: null, orderable: false, render: function(d) {
        return '<a href="' + window.RACINE + 'scolarite/edition/' + (d.editId || d.id_scolarite) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="' + window.RACINE + 'scolarite/details/' + (d.editId || d.id_scolarite) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  var tableTranches = $('#table-tranches_scolarite').DataTable({
    ajax: '<?= RACINE ?>tranche/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_tranche', defaultContent: '-' },
      { data: 'code_tranche', render: function(d) { return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>'; } },
      { data: 'libelle_filiere', render: function(d, t, r) {
        var fil = d || r.filiere_code || 'Filière';
        var niv = r.libelle_niveau || r.niveau_code || '';
        var ann = r.libelle_annee ? ' (' + r.libelle_annee + ')' : '';
        return '<span style="font-weight:700; color:#0F172A;">' + fil + ' ' + niv + '</span>' +
               '<div style="font-size:11.5px; color:#64748B;">' + (r.montant_scolarite ? Number(r.montant_scolarite).toLocaleString('fr-FR') + ' FCFA' : '') + ann + '</div>';
      } },
      { data: 'libelle_tranche', render: function(d) { return '<span style="font-weight:700; color:#1E3A5F;">' + (d || '-') + '</span>'; } },
      { data: 'montant_tranche', render: function(d) { 
        return '<span style="font-weight:800; color:#0F172A;">' + (d ? Number(d).toLocaleString('fr-FR') + ' FCFA' : '0 FCFA') + '</span>'; 
      } },
      { data: 'date_limite', render: function(d) { return '<span style="font-weight:600; color:#475569;">' + (d || '-') + '</span>'; } },
      { data: 'statut_tranche', width: '90px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        return '<div style="display:flex; justify-content:center; align-items:center;">' +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; cursor:pointer;" title="' + (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-tranche" data-id="' + row.id_tranche + '" ' + checkedAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#15803D' : '#CBD5E1') + '; transition:.3s; border-radius:20px;">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '20px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%;"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      } },
      { data: null, orderable: false, render: function(d) {
        return '<a href="' + window.RACINE + 'tranche/edition/' + (d.editId || d.id_tranche) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="' + window.RACINE + 'tranche/details/' + (d.editId || d.id_tranche) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  function bindAjaxToggle(selector, url, tableRef) {
    $(document).on('change', selector, function() {
      var id = $(this).data('id');
      var isChecked = $(this).is(':checked');
      var $input = $(this);
      $.ajax({
        url: url,
        type: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: { id: id, csrf_token: '<?= Validator::generateCsrfToken() ?>' },
        dataType: 'json',
        success: function(res) {
          if (res.status === 1 || res.success) {
            if (window.toastr) toastr.success(res.message || 'Statut mis à jour avec succès');
            tableRef.ajax.reload(null, false);
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
  }

  bindAjaxToggle('.toggle-statut-scolarite', '<?= RACINE ?>scolarite/changer', tableScolarites);
  bindAjaxToggle('.toggle-statut-tranche', '<?= RACINE ?>tranche/changer', tableTranches);

  // Switch Tab Handler (Standalone jQuery) - Default: Grille des Scolarités
  function switchScolariteTab(targetId) {
    $('.nav-tabs .nav-link').removeClass('active').css({ 'color': '#64748B', 'background': '#F8FAFC' });
    $('.tab-pane').removeClass('show active').hide();

    if (targetId === '#tab-tranches') {
      $('#tab-tranches-btn').addClass('active').css({ 'color': '#1E3A5F', 'background': '#FFFFFF' });
      $('#tab-tranches').addClass('show active').show();
      $('#btn-add-action').attr('href', window.RACINE + 'tranche/formulaire');
      $('#btn-add-label').text('Ajouter Tranche / Échéancier');
      tableTranches.columns.adjust().draw();
    } else {
      $('#tab-scolarites-btn').addClass('active').css({ 'color': '#1E3A5F', 'background': '#FFFFFF' });
      $('#tab-scolarites').addClass('show active').show();
      $('#btn-add-action').attr('href', window.RACINE + 'scolarite/formulaire');
      $('#btn-add-label').text('Ajouter Tarif de Scolarité');
      tableScolarites.columns.adjust().draw();
    }
    if (window.lucide) lucide.createIcons();
  }

  $(document).on('click', '.nav-tabs .nav-link', function(e) {
    e.preventDefault();
    var target = $(this).attr('data-bs-target');
    switchScolariteTab(target);
  });

  // Default active tab on load is #tab-scolarites unless ?tab=tranches
  var urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('tab') === 'tranches') {
    switchScolariteTab('#tab-tranches');
  } else {
    switchScolariteTab('#tab-scolarites');
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
