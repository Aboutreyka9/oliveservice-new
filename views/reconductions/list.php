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
            <i data-lucide="rotate-cw" style="color: #1E3A5F; width: 26px; height: 26px;"></i>
            <span>Reconduction des Souscriptions</span>
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Gestion de la reconduction des souscriptions pour la nouvelle session d'activité</p>
        </div>
      </div>

      <!-- CARTE TABLEAU PRINCIPALE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-reconductions" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px;">N° Souscription</th>
                <th style="padding: 12px;">Client</th>
                <th style="padding: 12px;">Pack Souscrit</th>
                <th style="padding: 12px;">Session Actuelle</th>
                <th style="padding: 12px; text-align: right;">Montant Total</th>
                <th style="padding: 12px; text-align: center;">Statut</th>
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
  var table = $('#table-reconductions').DataTable({
    ajax: '<?= RACINE ?>reconduction/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'numero_souscription', width: '130px', render: function(d) {
        if (!d) return '-';
        return '<code style="font-weight:800; color:#1E3A5F; background:#EFF6FF; padding:3px 8px; border-radius:6px;">' + d + '</code>';
      }},
      { data: 'nom_client_complet', render: function(d) {
        return '<strong style="color:#0F172A; font-size:14px;">' + (d || '-') + '</strong>';
      }},
      { data: 'libelle_pack', defaultContent: '-' },
      { data: 'libelle_session', defaultContent: '-' },
      { data: 'montant_total_prevu', className: 'text-end', render: function(d) {
        return '<strong style="color:#047857;">' + (parseInt(d || 0).toLocaleString('fr-FR')) + ' FCFA</strong>';
      }},
      { data: 'statut_souscription', width: '100px', className: 'text-center', render: function(d) {
        var badgeClass = 'bg-info';
        if (d === 'solde') badgeClass = 'bg-success';
        if (d === 'distribue') badgeClass = 'bg-primary';
        return '<span class="badge ' + badgeClass + '" style="padding:6px 12px; border-radius:8px; font-weight:700;">' + (d ? d.toUpperCase() : 'ACTIF') + '</span>';
      }},
      { data: null, width: '150px', orderable: false, render: function(d) {
        return '<a href="' + window.RACINE + 'souscription/details/' + (d.editId || d.id_souscription) + '" class="btn btn-sm btn-primary" style="background:#1E3A5F; border-color:#1E3A5F; font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="rotate-cw" style="width:14px;height:14px;"></i> Reconduire</a>';
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
