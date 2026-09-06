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
            <i data-lucide="truck" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Distributions & Remises de Packs
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Suivi logistique et remises effectives des packs aux clients ayant soldé leurs souscriptions
            </p>
          </div>
        </div>

        <?php if (Context::can('GESTIONNAIRE_MANAGE_DISTRIBUTIONS', ['ROLE_GESTIONNAIRE', 'ROLE_ADMIN'])): ?>
        <a href="<?= RACINE ?>distribution/formulaire" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 12px 22px; font-size: 14px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); text-decoration: none; cursor: pointer;">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Valider une Distribution
        </a>
        <?php endif; ?>
      </div>

      <!-- CARTE TABLEAU PRINCIPALE (NAVY PREMIUM) -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-distributions" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse; font-size: 13px;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 2px solid #E2E8F0;">
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Code Dist.</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Client</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Pack</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Zone</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Agent / Livreur</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Date</th>
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
  var table = $('#table-distributions').DataTable({
    ajax: '<?= RACINE ?>distribution/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'code_distribution', width: '130px', render: function(d) {
        if (!d) return '-';
        return '<code style="font-weight:700; color:#1E3A5F; background:#F1F5F9; padding:4px 8px; border-radius:6px; font-size:12px; border: 1px solid #CBD5E1;">' + d + '</code>';
      }},
      { data: 'nom_client_complet', render: function(d) {
        return '<span style="font-weight:800; color:#0F172A; font-size:14px;">' + (d || '-') + '</span>';
      }},
      { data: 'libelle_pack', render: function(d) {
        return '<span style="font-weight:600; color:#334155;">' + (d || '-') + '</span>';
      }},
      { data: 'libelle_zone', render: function(d) {
        return '<span style="font-weight:600; color:#475569;">' + (d || '-') + '</span>';
      }},
      { data: 'nom_livreur_complet', render: function(d) {
        return '<span style="font-weight:500; color:#64748B;">' + (d || '-') + '</span>';
      }},
      { data: 'date_distribution_effectuee', render: function(d) {
        if (!d) return '-';
        return '<span style="font-size:12px; color:#475569; font-weight:600;">' + d + '</span>';
      }},
      { data: 'statut_distribution', className: 'text-center', width: '110px', render: function(d) {
        var style = 'background:#FEF3C7; color:#B45309; border:1px solid #FDE68A;';
        var icon = 'clock';
        var libelle = 'En attente';
        if (d === 'valide') { 
          style = 'background:#ECFDF5; color:#059669; border:1px solid #A7F3D0;'; 
          icon = 'check-circle';
          libelle = 'Validée'; 
        } else if (d === 'ennule') { 
          style = 'background:#FEE2E2; color:#B91C1C; border:1px solid #FECACA;'; 
          icon = 'x-circle';
          libelle = 'Annulée'; 
        }
        return '<span style="display:inline-flex; align-items:center; gap:4px; padding:4px 12px; border-radius:20px; font-weight:800; font-size:12px; ' + style + '">' +
               '<i data-lucide="' + icon + '" style="width:13px; height:13px;"></i> ' + libelle + '</span>';
      }},
      { data: null, width: '170px', orderable: false, render: function(d) {
        var editId = d.editId || d.id_distribution;
        var canManage = (window.AppConfig && typeof window.AppConfig.can === 'function') ? window.AppConfig.can('GESTIONNAIRE_MANAGE_DISTRIBUTIONS') : false;
        var html = '<div style="display:flex; justify-content:flex-end; gap:6px;">';
        if (canManage) {
          html += '<a href="' + window.RACINE + 'distribution/edition/' + editId + '" class="btn" style="background:#F1F5F9; color:#1E3A5F; font-weight:700; border-radius:8px; padding:6px 12px; text-decoration:none; border:1px solid #CBD5E1; display:inline-flex; align-items:center; gap:4px; font-size:12px;" title="Modifier"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>';
        }
        html += '<a href="' + window.RACINE + 'distribution/details/' + editId + '" class="btn" style="background:#1E3A5F; color:#FFFFFF; font-weight:700; border-radius:8px; padding:6px 12px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-size:12px;" title="Voir PV"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>' +
                '</div>';
        return html;
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
