<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Caisse & Encaissements Scolarité</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion et consultation du registre Caisse & Encaissements Scolarité</p>
        </div>
        <a href="<?= RACINE ?>paiement/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Ajouter Règlement Caisse
        </a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-paiements" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px;">ID</th>
                <th style="padding: 12px;">Réf. Reçu</th>
                <th style="padding: 12px;">N° Dossier Élève</th>
                <th style="padding: 12px;">Montant Versé (FCFA)</th>
                <th style="padding: 12px;">Mode Règlement</th>
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
  var table = $('#table-paiements').DataTable({
    ajax: '<?= RACINE ?>paiement/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_paiement', defaultContent: '-' },
      { data: 'code_paiement', render: function(d) {
        return '<code style="font-weight:700; color:#475569;">' + (d || '-') + '</code>';
      } },
      { data: 'inscription_code', render: function(d, type, row) {
        var eleve = row.etudiant_nom ? '<div style="font-weight:700; color:#0F172A;">' + row.etudiant_nom + '</div>' : '';
        return eleve + '<code style="color:#64748B; font-size:11.5px;">' + (d || '-') + '</code>';
      } },
      { data: 'montant_paiement', render: function(d) {
        return d ? '<strong style="color:#0F172A;">' + Number(d).toLocaleString('fr-FR') + ' FCFA</strong>' : '-';
      } },
      { data: 'mode_paiement', render: function(d) {
        return '<span class="badge" style="background:#EFF6FF; color:#1E3A5F; font-weight:700; padding:4px 8px; border-radius:6px;">' + (d || 'Espèces') + '</span>';
      } },
      { data: 'statut_paiement', width: '135px', className: 'text-center', render: function(d, type, row) {
        var val = d || 'confirme';
        var bgColors = {
          'confirme': '#DCFCE7',
          'en_attente': '#FEF3C7',
          'annule': '#FEE2E2',
          'rembourse': '#F3E8FF',
          'echoue': '#F1F5F9'
        };
        var textColors = {
          'confirme': '#15803D',
          'en_attente': '#B45309',
          'annule': '#B91C1C',
          'rembourse': '#7E22CE',
          'echoue': '#475569'
        };
        var borderColors = {
          'confirme': '#86EFAC',
          'en_attente': '#FCD34D',
          'annule': '#FCA5A5',
          'rembourse': '#D8B4FE',
          'echoue': '#CBD5E1'
        };
        var currentBg = bgColors[val] || '#F1F5F9';
        var currentText = textColors[val] || '#334155';
        var currentBorder = borderColors[val] || '#CBD5E1';

        return '<select class="select-statut-paiement" data-id="' + row.id_paiement + '" style="background:' + currentBg + '; color:' + currentText + '; border:1px solid ' + currentBorder + '; font-weight:700; font-size:12px; border-radius:8px; padding:4px 8px; cursor:pointer; outline:none;">' +
               '<option value="confirme" ' + (val === 'confirme' ? 'selected' : '') + ' style="background:#fff; color:#15803D;">Confirmé</option>' +
               '<option value="en_attente" ' + (val === 'en_attente' ? 'selected' : '') + ' style="background:#fff; color:#B45309;">En attente</option>' +
               '<option value="annule" ' + (val === 'annule' ? 'selected' : '') + ' style="background:#fff; color:#B91C1C;">Annulé</option>' +
               '<option value="rembourse" ' + (val === 'rembourse' ? 'selected' : '') + ' style="background:#fff; color:#7E22CE;">Remboursé</option>' +
               '<option value="echoue" ' + (val === 'echoue' ? 'selected' : '') + ' style="background:#fff; color:#475569;">Échoué</option>' +
               '</select>';
      } },
      { data: null, orderable: false, render: function(d) {
        return '<a href="' + window.RACINE + 'paiement/edition/' + (d.editId || d.id_paiement) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="' + window.RACINE + 'paiement/details/' + (d.editId || d.id_paiement) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  $(document).on('change', '.select-statut-paiement', function() {
    var id = $(this).data('id');
    var newStatut = $(this).val();

    $.ajax({
      url: '<?= RACINE ?>paiement/changer',
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
