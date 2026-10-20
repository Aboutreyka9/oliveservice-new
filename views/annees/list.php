<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$totalAnnees = $totalAnnees ?? 0;
$activeAnnee = $activeAnnee ?? null;
$totalPlanifie = $totalPlanifie ?? 0;
$totalCloture = $totalCloture ?? 0;
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">

      <style>
      .annee-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 18px;
        margin-bottom: 24px;
      }
      .annee-kpi-card {
        background: #FFFFFF;
        border-radius: 14px;
        padding: 20px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.02);
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
      }
      .annee-kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--kpi-accent, #1E3A5F);
        border-radius: 14px 14px 0 0;
      }
      .annee-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 18px -4px rgba(15, 23, 42, 0.08);
      }
      .annee-kpi-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
      }
      .annee-kpi-title {
        font-size: 11px;
        font-weight: 800;
        color: #64748B;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }
      .annee-kpi-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--kpi-bg-icon, #EFF6FF);
        color: var(--kpi-color-icon, #1E3A5F);
      }
      .annee-kpi-val {
        font-size: 24px;
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -0.5px;
        color: #0F172A;
        margin-bottom: 8px;
      }
      .annee-kpi-footer {
        font-size: 12px;
        color: #64748B;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 6px;
      }
      .annee-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
      }
      .badge-statut-actif {
        background: #ECFDF5;
        color: #059669;
        border: 1px solid #A7F3D0;
      }
      .badge-statut-planifie {
        background: #EFF6FF;
        color: #1D4ED8;
        border: 1px solid #BFDBFE;
      }
      .badge-statut-cloture {
        background: #F1F5F9;
        color: #475569;
        border: 1px solid #CBD5E1;
      }
      </style>
      
      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="calendar" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Années d'Activité & Exercices
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Gestion et suivi du registre des années, périodes d'exercice et barèmes de pénalités
            </p>
          </div>
        </div>

        <?php if (Context::can('GESTIONNAIRE_MANAGE_ANNEES')): ?>
        <a href="<?= RACINE ?>annee/formulaire" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 12px 22px; font-size: 14px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); text-decoration: none; cursor: pointer;">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle Année
        </a>
        <?php endif; ?>
      </div>

      <!-- GRILLE KPIS STATISTIQUES EN-TÊTE -->
      <div class="annee-kpi-grid">
        
        <!-- Card 1 : Exercice Actif -->
        <div class="annee-kpi-card" style="--kpi-accent: linear-gradient(90deg, #059669, #047857); --kpi-bg-icon: #ECFDF5; --kpi-color-icon: #059669;">
          <div class="annee-kpi-header">
            <span class="annee-kpi-title">Exercice Actif en cours</span>
            <div class="annee-kpi-icon"><i data-lucide="calendar-check" style="width: 20px; height: 20px;"></i></div>
          </div>
          <div class="annee-kpi-val" style="color: #059669;">
            <?= htmlspecialchars($activeAnnee['libelle_annee'] ?? 'Aucun actif') ?>
          </div>
          <div class="annee-kpi-footer">
            <span>Code : <code style="font-weight:700; color:#059669;"><?= htmlspecialchars($activeAnnee['code_annee'] ?? '-') ?></code></span>
            <span class="annee-badge badge-statut-actif"><i data-lucide="check-circle" style="width: 12px; height: 12px;"></i> Actif</span>
          </div>
        </div>

        <!-- Card 2 : Total Années / Exercices -->
        <div class="annee-kpi-card" style="--kpi-accent: linear-gradient(90deg, #1E3A5F, #0F172A); --kpi-bg-icon: #EFF6FF; --kpi-color-icon: #1E3A5F;">
          <div class="annee-kpi-header">
            <span class="annee-kpi-title">Total Exercices</span>
            <div class="annee-kpi-icon"><i data-lucide="calendar" style="width: 20px; height: 20px;"></i></div>
          </div>
          <div class="annee-kpi-val" style="color: #0F172A;">
            <?= (int)$totalAnnees ?> <small style="font-size: 13px; font-weight: 600; color: #64748B;">enregistré(s)</small>
          </div>
          <div class="annee-kpi-footer">
            <span><?= (int)$totalPlanifie ?> planifié(s)</span>
            <span class="annee-badge badge-statut-cloture"><?= (int)$totalCloture ?> clôturé(s)</span>
          </div>
        </div>

        <!-- Card 3 : Pénalité Reconduction Active -->
        <div class="annee-kpi-card" style="--kpi-accent: linear-gradient(90deg, #F59E0B, #D97706); --kpi-bg-icon: #FFFBEB; --kpi-color-icon: #D97706;">
          <div class="annee-kpi-header">
            <span class="annee-kpi-title">Pénalité Reconduction</span>
            <div class="annee-kpi-icon"><i data-lucide="refresh-cw" style="width: 20px; height: 20px;"></i></div>
          </div>
          <div class="annee-kpi-val" style="color: #D97706;">
            <?= number_format((float)($activeAnnee['penalite_annee'] ?? 0), 2, ',', ' ') ?> <small style="font-size: 13px; font-weight: 700;">%</small>
          </div>
          <div class="annee-kpi-footer">
            <span>Taux appliqué sur reconduction</span>
            <span class="annee-badge" style="background:#FFFBEB; color:#B45309; border:1px solid #FDE68A;">Exercice actif</span>
          </div>
        </div>

        <!-- Card 4 : Pénalité Restauration Active -->
        <div class="annee-kpi-card" style="--kpi-accent: linear-gradient(90deg, #7C3AED, #6D28D9); --kpi-bg-icon: #FAF5FF; --kpi-color-icon: #7C3AED;">
          <div class="annee-kpi-header">
            <span class="annee-kpi-title">Pénalité Restauration</span>
            <div class="annee-kpi-icon"><i data-lucide="shield-alert" style="width: 20px; height: 20px;"></i></div>
          </div>
          <div class="annee-kpi-val" style="color: #7C3AED;">
            <?= number_format((float)($activeAnnee['penalite_restore'] ?? 0), 2, ',', ' ') ?> <small style="font-size: 13px; font-weight: 700;">%</small>
          </div>
          <div class="annee-kpi-footer">
            <span>Taux de restauration souscription</span>
            <span class="annee-badge" style="background:#FAF5FF; color:#6D28D9; border:1px solid #E9D5FF;">Exercice actif</span>
          </div>
        </div>

      </div>

      <!-- CARTE TABLEAU PRINCIPALE (NAVY PREMIUM) -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-annees" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse; font-size: 13px;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 2px solid #E2E8F0;">
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;"># ID</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Code</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Année / Exercice</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Période (Début - Fin)</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: center;">Pén. Reconduction</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: center;">Pén. Restauration</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Zone Commerciale</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: center;">Statut</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Date Création</th>
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
  function formatLocalDate(isoStr) {
    if (!isoStr) return '-';
    var parts = isoStr.split('-');
    if (parts.length === 3) {
      return parts[2] + '/' + parts[1] + '/' + parts[0];
    }
    return isoStr;
  }

  function formatLocalDateTime(dtStr) {
    if (!dtStr) return '-';
    var d = new Date(dtStr.replace(' ', 'T'));
    if (isNaN(d.getTime())) return dtStr;
    var day = ('0' + d.getDate()).slice(-2);
    var month = ('0' + (d.getMonth() + 1)).slice(-2);
    var year = d.getFullYear();
    var hours = ('0' + d.getHours()).slice(-2);
    var mins = ('0' + d.getMinutes()).slice(-2);
    return day + '/' + month + '/' + year + ' ' + hours + ':' + mins;
  }

  var table = $('#table-annees').DataTable({
    ajax: '<?= RACINE ?>annee/apiList',
    processing: true,
    autoWidth: false,
    order: [[0, 'desc']],
    columns: [
      // 1. ID
      { data: 'id_annee', defaultContent: '-', width: '50px', render: function(d) {
        return '<span style="font-weight:700; color:#64748B;">#' + d + '</span>';
      }},

      // 2. Code Année
      { data: 'code_annee', width: '130px', render: function(d) {
        if (!d) return '-';
        return '<code style="font-weight:700; color:#1E3A5F; background:#F1F5F9; padding:4px 8px; border-radius:6px; font-size:12px; border:1px solid #CBD5E1;">' + d + '</code>';
      }},

      // 3. Libellé Année
      { data: 'libelle_annee', render: function(d, type, row) {
        var lib = d || '-';
        var souscr = row.nb_souscriptions ? (row.nb_souscriptions + ' souscription(s)') : '0 souscription';
        var sess = row.nb_sessions ? (row.nb_sessions + ' session(s)') : '0 session';
        return '<div>' +
               '<div style="font-weight:800; color:#0F172A; font-size:14px;">' + lib + '</div>' +
               '<div style="font-size:11px; color:#64748B; margin-top:2px;">' + souscr + ' &bull; ' + sess + '</div>' +
               '</div>';
      }},

      // 4. Période Début - Fin
      { data: null, render: function(d, type, row) {
        var deb = formatLocalDate(row.date_debut_annee);
        var fin = formatLocalDate(row.date_fin_annee);
        return '<div style="font-size:12px; color:#334155; font-weight:600; display:inline-flex; align-items:center; gap:6px;">' +
               '<i data-lucide="calendar" style="width:14px; height:14px; color:#64748B;"></i>' +
               '<span>' + deb + ' &rarr; ' + fin + '</span>' +
               '</div>';
      }},

      // 5. Pénalité Année / Reconduction
      { data: 'penalite_annee', className: 'text-center', render: function(d) {
        var val = parseFloat(d || 0);
        return '<span style="font-size:12px; font-weight:700; color:#D97706; background:#FFFBEB; padding:3px 10px; border-radius:6px; border:1px solid #FDE68A;">' + val.toFixed(2) + ' %</span>';
      }},

      // 6. Pénalité Restauration
      { data: 'penalite_restore', className: 'text-center', render: function(d) {
        var val = parseFloat(d || 0);
        return '<span style="font-size:12px; font-weight:700; color:#7C3AED; background:#FAF5FF; padding:3px 10px; border-radius:6px; border:1px solid #E9D5FF;">' + val.toFixed(2) + ' %</span>';
      }},

      // 7. Zone Commerciale
      { data: null, render: function(d, type, row) {
        var zone = row.libelle_zone || row.zone_code || 'Toutes zones';
        return '<div style="font-size:12px; color:#334155; font-weight:600;"><i data-lucide="map-pin" style="width:13px; height:13px; color:#059669; vertical-align:middle; margin-right:4px;"></i>' + zone + '</div>';
      }},

      // 8. Statut (Badge + Toggle)
      { data: 'statut_annee', width: '130px', className: 'text-center', render: function(d, type, row) {
        var statut = (d || 'planifie').toLowerCase();
        var isActif = (statut === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        var canManage = (window.AppConfig && typeof window.AppConfig.can === 'function') ? window.AppConfig.can('GESTIONNAIRE_MANAGE_ANNEES') : false;
        var disabledAttr = canManage ? '' : 'disabled';
        var cursorStyle = canManage ? 'cursor:pointer;' : 'cursor:not-allowed; opacity:0.6;';

        var badgeHtml = '';
        if (statut === 'actif') {
          badgeHtml = '<span class="annee-badge badge-statut-actif" style="margin-bottom:4px;"><i data-lucide="check-circle" style="width:11px;height:11px;"></i> Actif</span>';
        } else if (statut === 'planifie') {
          badgeHtml = '<span class="annee-badge badge-statut-planifie" style="margin-bottom:4px;"><i data-lucide="clock" style="width:11px;height:11px;"></i> Planifié</span>';
        } else {
          badgeHtml = '<span class="annee-badge badge-statut-cloture" style="margin-bottom:4px;"><i data-lucide="lock" style="width:11px;height:11px;"></i> Clôturé</span>';
        }

        return '<div style="display:flex; flex-direction:column; align-items:center; gap:2px;">' +
               badgeHtml +
               '<label style="position:relative; display:inline-block; width:38px; height:20px; margin:0; ' + cursorStyle + '" title="' + (isActif ? 'Cliquer pour clôturer' : 'Cliquer pour activer') + '">' +
               '<input type="checkbox" class="toggle-statut-annee" data-id="' + row.id_annee + '" ' + checkedAttr + ' ' + disabledAttr + ' style="opacity:0; width:0; height:0;">' +
               '<span style="position:absolute; ' + cursorStyle + ' top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#059669' : '#CBD5E1') + '; transition:.3s; border-radius:20px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">' +
               '<span style="position:absolute; content:\'\'; height:14px; width:14px; left:' + (isActif ? '21px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%; box-shadow: 0 1px 3px rgba(0,0,0,0.2);"></span>' +
               '</span>' +
               '</label>' +
               '</div>';
      }},

      // 9. Date Création
      { data: 'created_at_annee', render: function(d) {
        return '<span style="font-size:12px; color:#64748B;">' + formatLocalDateTime(d) + '</span>';
      }},

      // 10. Actions
      { data: null, width: '160px', orderable: false, className: 'text-end', render: function(d) {
        var editId = d.editId || d.id_annee;
        var canManage = (window.AppConfig && typeof window.AppConfig.can === 'function') ? window.AppConfig.can('GESTIONNAIRE_MANAGE_ANNEES') : false;
        var html = '<div style="display:flex; justify-content:flex-end; gap:6px;">';
        if (canManage) {
          html += '<a href="' + window.RACINE + 'annee/edition/' + editId + '" class="btn" style="background:#F1F5F9; color:#1E3A5F; font-weight:700; border-radius:8px; padding:6px 12px; text-decoration:none; border:1px solid #CBD5E1; display:inline-flex; align-items:center; gap:4px; font-size:12px;" title="Modifier"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>';
        }
        html += '<a href="' + window.RACINE + 'annee/details/' + editId + '" class="btn" style="background:#1E3A5F; color:#FFFFFF; font-weight:700; border-radius:8px; padding:6px 12px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-size:12px;" title="Voir fiche & statistiques"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>' +
                '</div>';
        return html;
      }}
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { 
      if (window.lucide) lucide.createIcons(); 
    }
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
          if (window.toastr) {
            toastr.success(res.message || 'Statut de l\'année mis à jour avec succès');
          }
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) {
            toastr.error(res.message || 'Erreur lors du changement de statut');
          }
          $input.prop('checked', !isChecked);
        }
      },
      error: function() {
        if (window.toastr) {
          toastr.error('Erreur réseau');
        }
        $input.prop('checked', !isChecked);
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
