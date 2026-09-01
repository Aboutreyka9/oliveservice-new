<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">

      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="file-text" style="color: #1E3A5F; width: 26px; height: 26px;"></i>
            <span>Souscriptions Clients</span>
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Contrats d'abonnement packs et suivi des cotisations terrain</p>
        </div>
        <div style="display: flex; gap: 10px;">
          <a href="<?= RACINE ?>souscription/wizard" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle Souscription
          </a>
         
        </div>
      </div>

      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-souscriptions" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px;">Code Souscription</th>
                <th style="padding: 12px; text-align: center;">Statut</th>
                <th style="padding: 12px;">Client</th>
                <th style="padding: 12px;">Session</th>
                <th style="padding: 12px;">Cotis. / Jour</th>
                <th style="padding: 12px; text-align: right;">Total Souscription</th>
                <th style="padding: 12px;">Progression</th>
                <th style="padding: 12px; text-align: right;">Total Cotisé</th>
                <th style="padding: 12px; text-align: right;">Reste à Payer</th>
                <th style="padding: 12px;">Date</th>
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
  var table = $('#table-souscriptions').DataTable({
    ajax: '<?= RACINE ?>souscription/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'code_souscription', width: '130px', render: function(d) {
        if (!d) return '-';
        return '<code style="font-weight:700; color:#334155; background:#F1F5F9; padding:2px 6px; border-radius:4px;">' + d + '</code>';
      }},
      { data: 'statut_souscription', className: 'text-center', width: '100px', render: function(d) {
        var badge = 'bg-primary';
        var libelle = 'Validée';
        if (d === 'solde') { badge = 'bg-success'; libelle = 'Soldée'; }
        else if (d === 'annule') { badge = 'bg-danger'; libelle = 'Annulée'; }
        else if (d === 'reconduite') { badge = 'bg-warning text-dark'; libelle = 'Reconduite'; }
        return '<span class="badge ' + badge + '">' + libelle + '</span>';
      }},
      { data: 'nom_client_complet', render: function(d) {
        return '<strong style="color:#0F172A;">' + (d || '-') + '</strong>';
      }},
      { data: 'libelle_session', defaultContent: '-' },
      { data: 'sum_prix_cotisation_pack', render: function(d) {
        return '<span style="font-weight:700; color:#15803D;">' + Number(d || 0).toLocaleString('fr-FR') + ' FCFA</span>';
      }},
      { data: 'totale_souscription', render: function(d) {
        return '<strong style="color:#1E3A5F;">' + Number(d || 0).toLocaleString('fr-FR') + ' FCFA</strong>';
      }, className: 'text-end' },
      { data: null, render: function(d) {
        var cotise = d.nombre_jour_cotise || 0;
        var total = d.nombre_jour_session || 0;
        var pct = total > 0 ? Math.round((cotise / total) * 100) : 0;
        var color = pct >= 100 ? '#15803D' : (pct >= 50 ? '#D97706' : '#DC2626');
        return '<div style="min-width:120px;"><strong>' + cotise + ' / ' + total + ' j</strong> <small style="color:#64748B;">(' + pct + '%)</small>' +
               '<div class="progress" style="height:6px; margin-top:4px; background:#E2E8F0; border-radius:3px; overflow:hidden;"><div class="progress-bar" style="width:' + pct + '%; background:' + color + '; height:100%;"></div></div></div>';
      }},
      { data: 'montant_total_cotise', render: function(d) {
        return '<strong style="color:#0F172A;">' + Number(d || 0).toLocaleString('fr-FR') + ' FCFA</strong>';
      }, className: 'text-end' },
      { data: 'solde_restant', render: function(d) {
        if ((d || 0) <= 0) return '<span style="color:#15803D; font-weight:800;">Soldé</span>';
        return '<strong style="color:#DC2626;">' + Number(d).toLocaleString('fr-FR') + ' FCFA</strong>';
      }, className: 'text-end' },
      { data: 'date_souscription', defaultContent: '-', className: 'text-center' },
      { data: null, width: '180px', orderable: false, render: function(d) {
        var btns = '<a href="' + window.RACINE + 'souscription/edition/' + (d.editId || d.id_souscription) + '" class="btn btn-sm btn-secondary" style="margin-right:6px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>' +
                   '<a href="' + window.RACINE + 'souscription/details/' + (d.editId || d.id_souscription) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>';
        return btns;
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
