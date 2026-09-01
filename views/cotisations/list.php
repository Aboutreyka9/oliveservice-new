<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Journal des Cotisations Clients</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Encaissements terrain quotidiens des commerciaux</p>
        </div>
        <a href="<?= RACINE ?>cotisation/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Saisir Cotisation
        </a>
      </div>

      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-cotisations" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px;">Code / Reçu</th>
                <th style="padding: 12px;">Date</th>
                <th style="padding: 12px;">Client</th>
                <th style="padding: 12px;">Pack</th>
                <th style="padding: 12px;">Commercial</th>
                <th style="padding: 12px;">Mode</th>
                <th style="padding: 12px; text-align: center;">Jours</th>
                <th style="padding: 12px; text-align: right;">Montant</th>
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
  var table = $('#table-cotisations').DataTable({
    ajax: '<?= RACINE ?>cotisation/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'code_cautisation_client', width: '130px', render: function(d, type, row) {
        if (!d) return '-';
        return '<code style="font-weight:700; color:#334155; background:#F1F5F9; padding:2px 6px; border-radius:4px;">' + d + '</code>';
      }},
      { data: 'date_cautisation', defaultContent: '-', width: '100px' },
      { data: 'nom_client_complet', render: function(d) {
        return '<strong style="color:#0F172A;">' + (d || '-') + '</strong>';
      }},
      { data: 'libelle_pack', defaultContent: '-' },
      { data: 'nom_commercial_complet', defaultContent: '-' },
      { data: 'mode_paiement', defaultContent: '-', width: '90px', className: 'text-center', render: function(d) {
        var badge = 'bg-secondary';
        if (d === 'espece') badge = 'bg-success';
        else if (d === 'mobile_money') badge = 'bg-info';
        else if (d === 'virement') badge = 'bg-primary';
        return '<span class="badge ' + badge + '">' + (d || '-') + '</span>';
      }},
      { data: 'nombre_jour', className: 'text-center', width: '70px', render: function(d) {
        return '<span class="badge bg-secondary">+' + (d || 1) + ' j</span>';
      }},
      { data: 'montant_cautisation_client', render: function(d) {
        return '<strong style="color:#15803D; font-size:14px;">' + Number(d || 0).toLocaleString('fr-FR') + ' FCFA</strong>';
      }},
      { data: null, width: '160px', orderable: false, render: function(d) {
        return '<a href="' + window.RACINE + 'cotisation/edition/' + (d.editId || d.id_cautisation_client) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
               '<a href="' + window.RACINE + 'cotisation/details/' + (d.editId || d.id_cautisation_client) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
