<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Bulletins & Relevés de Notes</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Génération, consultation et impression des relevés de notes académiques par étudiant</p>
        </div>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-bulletin" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px;">ID</th>
                <th style="padding: 12px;">Matricule</th>
                <th style="padding: 12px;">Nom & Prénom Étudiant</th>
                <th style="padding: 12px;">Classe</th>
                <th style="padding: 12px;">Année Académique</th>
                <th style="padding: 12px;">Notes Saisies</th>
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
  $('#table-bulletin').DataTable({
    ajax: '<?= RACINE ?>bulletin/apiList',
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id_inscription',   defaultContent: '-', width: '50px' },
      { data: 'matricule_etudiant', defaultContent: '<span style="color:#94A3B8; font-style:italic;">-</span>', width: '130px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<strong style="color:#1E3A5F;">' + (d || '-') + '</strong>';
      }},
      { data: 'etudiant_nom',    defaultContent: '-', width: '200px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="font-weight:600; color:#0F172A;">' + (d || '-') + '</span>';
      }},
      { data: 'classe_nom',      defaultContent: '-', width: '130px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="color:#334155; font-weight:500;">' + (d || '<span style=\"color:#94A3B8; font-style:italic;\">Non assigné</span>') + '</span>';
      }},
      { data: 'annee_nom',       defaultContent: '-', width: '120px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="color:#64748B;">' + (d || '-') + '</span>';
      }},
      { data: 'nb_notes',        defaultContent: '0', width: '120px', render: function(d, type) {
        if (type !== 'display') return d || 0;
        var count = parseInt(d) || 0;
        if (count > 0) {
          return '<span style="display:inline-block; padding: 3px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600; background: #DCFCE7; color: #15803D;">' + count + ' note(s)</span>';
        } else {
          return '<span style="display:inline-block; padding: 3px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600; background: #F1F5F9; color: #64748B;">0 note</span>';
        }
      }},
      { data: null, width: '170px', orderable: false, render: function(d) {
        return '<a href="' + window.RACINE + 'bulletin/details/' + (d.editId || d.id_inscription) + '" class="btn btn-sm btn-primary" style="background:#1E3A5F;border-color:#1E3A5F;color:#fff;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:6px;padding:6px 14px;"><i data-lucide="file-text" style="width:14px;height:14px;"></i> Voir Bulletin</a>';
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
