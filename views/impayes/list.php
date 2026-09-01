<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Suivi des Relances & Impayés Scolaires</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Historique des relances ébauchées, expédiées et suivi du recouvrement</p>
        </div>
        <a href="<?= RACINE ?>impayes/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="send" style="width: 18px; height: 18px;"></i> Émettre une Relance Impayé
        </a>
      </div>

      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-impayes" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px;">ID</th>
                <th style="padding: 12px;">Code Relance</th>
                <th style="padding: 12px;">Élève / Étudiant</th>
                <th style="padding: 12px;">Niveau de Relance</th>
                <th style="padding: 12px;">Canal</th>
                <th style="padding: 12px;">Montant Impayé</th>
                <th style="padding: 12px;">Date d'Émission</th>
                <th class="text-center" style="padding: 12px;">Statut</th>
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
  if (window.lucide) lucide.createIcons();

  var table = $('#table-impayes').DataTable({
    ajax: {
      url: '<?= RACINE ?>impayes/apiList',
      type: 'GET'
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_relance', defaultContent: '-' },
      { 
        data: 'code_relance', 
        render: function(d) {
          return '<code style="font-weight:700; color:#D97706;">' + (d || '-') + '</code>';
        }
      },
      { 
        data: null,
        render: function(d) {
          if (d.nom_etudiant) {
            return '<strong>' + (d.nom_etudiant + ' ' + (d.prenom_etudiant || '')).trim() + '</strong><br><small style="color:#64748B;">' + (d.matricule_etudiant || d.etudiant_code) + '</small>';
          }
          return d.etudiant_code || '-';
        }
      },
      { 
        data: 'niveau_relance', 
        render: function(d) {
          if (d === 'rappel_amiable') return '<span class="badge" style="background:#EFF6FF; color:#1E40AF; padding:5px 10px; border-radius:6px; font-weight:700;">Rappel Amiable</span>';
          if (d === 'relance_ferme') return '<span class="badge" style="background:#FEF3C7; color:#92400E; padding:5px 10px; border-radius:6px; font-weight:700;">Relance Ferme (48h)</span>';
          return '<span class="badge" style="background:#FEE2E2; color:#991B1B; padding:5px 10px; border-radius:6px; font-weight:700;">Mise en Demeure</span>';
        }
      },
      { 
        data: 'canal_relance',
        render: function(d) {
          return '<span style="font-weight:600; text-transform:uppercase; font-size:12px;">' + (d || 'SMS') + '</span>';
        }
      },
      { 
        data: 'montant_impaye', 
        render: function(d) {
          return '<strong style="color:#DC2626;">' + Number(d || 0).toLocaleString('fr-FR') + ' FCFA</strong>';
        }
      },
      { 
        data: 'created_at_relance', 
        render: function(d) {
          if (!d) return '-';
          return new Date(d).toLocaleDateString('fr-FR') + ' ' + new Date(d).toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'});
        }
      },
      { 
        data: 'statut_relance', 
        width: '130px', 
        className: 'text-center', 
        render: function(d, type, row) {
          var val = d || 'envoye';
          var bgColors = { 'regle': '#DCFCE7', 'envoye': '#DBEAFE', 'en_attente': '#FEF3C7' };
          var textColors = { 'regle': '#15803D', 'envoye': '#1E40AF', 'en_attente': '#B45309' };
          var borderColors = { 'regle': '#86EFAC', 'envoye': '#93C5FD', 'en_attente': '#FCD34D' };
          var currentBg = bgColors[val] || '#F1F5F9';
          var currentText = textColors[val] || '#334155';
          var currentBorder = borderColors[val] || '#CBD5E1';

          return '<select class="select-statut-relance" data-id="' + row.id_relance + '" style="background:' + currentBg + '; color:' + currentText + '; border:1px solid ' + currentBorder + '; font-weight:700; font-size:12px; border-radius:8px; padding:4px 8px; cursor:pointer; outline:none;">' +
                 '<option value="envoye" ' + (val === 'envoye' ? 'selected' : '') + ' style="background:#fff; color:#1E40AF;">Envoyée</option>' +
                 '<option value="en_attente" ' + (val === 'en_attente' ? 'selected' : '') + ' style="background:#fff; color:#B45309;">En attente</option>' +
                 '<option value="regle" ' + (val === 'regle' ? 'selected' : '') + ' style="background:#fff; color:#15803D;">Réglée</option>' +
                 '</select>';
        } 
      },
      { 
        data: null, 
        orderable: false,
        render: function(d) {
          return '<a href="' + window.RACINE + 'impayes/details/' + (d.editId || d.id_relance) + '" class="btn btn-sm btn-info" style="font-weight:600; border-radius:6px; padding:4px 10px;"><i data-lucide="eye" style="width:14px;height:14px;"></i> Voir</a>';
        }
      }
    ],
    language: {
      url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json'
    },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  $(document).on('change', '.select-statut-relance', function() {
    var id = $(this).data('id');
    var newStatut = $(this).val();

    $.ajax({
      url: '<?= RACINE ?>impayes/changer',
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
