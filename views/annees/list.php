<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">

      <style>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

      // 8. Statut (Uniquement le badge)
      { data: 'statut_annee', width: '110px', className: 'text-center', render: function(d) {
        var statut = (d || 'planifie').toLowerCase();
        if (statut === 'actif') {
          return '<span class="annee-badge badge-statut-actif"><i data-lucide="check-circle" style="width:12px;height:12px;"></i> Actif</span>';
        } else if (statut === 'planifie') {
          return '<span class="annee-badge badge-statut-planifie"><i data-lucide="clock" style="width:12px;height:12px;"></i> Planifié</span>';
        } else {
          return '<span class="annee-badge badge-statut-cloture"><i data-lucide="lock" style="width:12px;height:12px;"></i> Clôturé</span>';
        }
      }},

      // 9. Date Création
      { data: 'created_at_annee', render: function(d) {
        return '<span style="font-size:12px; color:#64748B;">' + formatLocalDateTime(d) + '</span>';
      }},

      // 10. Actions
      { data: null, width: '250px', orderable: false, className: 'text-end', render: function(d) {
        var editId = d.editId || d.id_annee;
        var canManage = (window.AppConfig && typeof window.AppConfig.can === 'function') ? window.AppConfig.can('GESTIONNAIRE_MANAGE_ANNEES') : false;
        var statut = (d.statut_annee || 'planifie').toLowerCase();
        var isCloture = (statut === 'cloture');
        var libelleEsc = $('<div>').text(d.libelle_annee || ('Année #' + d.id_annee)).html();

        var html = '<div style="display:inline-flex; align-items:center; justify-content:flex-end; gap:6px; flex-wrap:nowrap;">';
        
        if (canManage) {
          // Bouton d'action de statut approprié
          if (statut === 'planifie') {
            // Statut planifié -> Bouton "Activer"
            html += '<button type="button" class="btn btn-action-statut" data-id="' + d.id_annee + '" data-action="actif" data-libelle="' + libelleEsc + '" style="background:#ECFDF5; color:#059669; font-weight:700; border-radius:8px; padding:6px 12px; border:1px solid #A7F3D0; display:inline-flex; align-items:center; gap:4px; font-size:12px; cursor:pointer; transition:all 0.2s;" title="Activer cette année">' +
                    '<i data-lucide="play" style="width:13px;height:13px;color:#059669;"></i> Activer' +
                    '</button>';
          } else if (statut === 'actif') {
            // Statut actif -> Bouton "Clôturer"
            html += '<button type="button" class="btn btn-action-statut" data-id="' + d.id_annee + '" data-action="cloture" data-libelle="' + libelleEsc + '" style="background:#FFFBEB; color:#D97706; font-weight:700; border-radius:8px; padding:6px 12px; border:1px solid #FDE68A; display:inline-flex; align-items:center; gap:4px; font-size:12px; cursor:pointer; transition:all 0.2s;" title="Clôturer cette année">' +
                    '<i data-lucide="lock" style="width:13px;height:13px;color:#D97706;"></i> Clôturer' +
                    '</button>';
          } else {
            // Statut clôturé -> Bouton "Clôturer" désactivé
            html += '<button type="button" class="btn" disabled style="background:#F8FAFC; color:#94A3B8; font-weight:700; border-radius:8px; padding:6px 12px; border:1px solid #E2E8F0; display:inline-flex; align-items:center; gap:4px; font-size:12px; cursor:not-allowed; opacity:0.6;" title="Cette année est déjà clôturée">' +
                    '<i data-lucide="lock" style="width:13px;height:13px;"></i> Clôturer' +
                    '</button>';
          }

          // Bouton Éditer (Désactivé si clôturé)
          if (isCloture) {
            html += '<button type="button" class="btn" disabled style="background:#F8FAFC; color:#94A3B8; font-weight:700; border-radius:8px; padding:6px 12px; border:1px solid #E2E8F0; display:inline-flex; align-items:center; gap:4px; font-size:12px; cursor:not-allowed; opacity:0.65;" title="Année clôturée - Modification impossible">' +
                    '<i data-lucide="lock" style="width:14px;height:14px;"></i> Éditer' +
                    '</button>';
          } else {
            html += '<a href="' + window.RACINE + 'annee/edition/' + editId + '" class="btn" style="background:#F1F5F9; color:#1E3A5F; font-weight:700; border-radius:8px; padding:6px 12px; text-decoration:none; border:1px solid #CBD5E1; display:inline-flex; align-items:center; gap:4px; font-size:12px;" title="Modifier">' +
                    '<i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer' +
                    '</a>';
          }
        }

        // Bouton Détails
        html += '<a href="' + window.RACINE + 'annee/details/' + editId + '" class="btn" style="background:#1E3A5F; color:#FFFFFF; font-weight:700; border-radius:8px; padding:6px 12px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-size:12px;" title="Voir fiche & statistiques">' +
                '<i data-lucide="eye" style="width:14px;height:14px;"></i> Détails' +
                '</a>';
        
        html += '</div>';
        return html;
      }}
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { 
      if (window.lucide) lucide.createIcons(); 
    }
  });

  // Action directe sur le statut (Activer ou Clôturer)
  $(document).on('click', '.btn-action-statut', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    var targetStatut = $(this).data('action'); // 'actif' ou 'cloture'
    var libelle = $(this).data('libelle') || ('Exercice #' + id);

    var isActifAction = (targetStatut === 'actif');
    var title = isActifAction ? 'Activer cette année ?' : 'Clôturer cette année ?';
    var text = isActifAction
      ? "L'année d'activité <strong>" + libelle + "</strong> deviendra l'exercice actif. Toute autre année actuellement active de l'établissement sera automatiquement clôturée."
      : "L'année d'activité <strong>" + libelle + "</strong> sera clôturée. Une fois clôturée, elle ne pourra plus être modifiée.";
    var confirmBtnText = isActifAction ? 'Oui, activer' : 'Oui, clôturer';
    var confirmColor = isActifAction ? '#059669' : '#D97706';

    if (window.Swal) {
      Swal.fire({
        title: title,
        html: '<div style="font-size:13px; color:#334155; margin-top:6px; line-height:1.5;">' + text + '</div>',
        icon: isActifAction ? 'question' : 'warning',
        showCancelButton: true,
        confirmButtonText: confirmBtnText,
        cancelButtonText: 'Annuler',
        confirmButtonColor: confirmColor,
        cancelButtonColor: '#64748B'
      }).then(function(result) {
        if (result.isConfirmed) {
          executeStatutChange(id, targetStatut);
        }
      });
    } else {
      if (confirm(title + "\n\n" + text.replace(/<[^>]+>/g, ''))) {
        executeStatutChange(id, targetStatut);
      }
    }
  });

  function executeStatutChange(id, targetStatut) {
    $.ajax({
      url: '<?= RACINE ?>annee/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: id,
        statut: targetStatut,
        csrf_token: '<?= Validator::generateCsrfToken() ?>'
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) {
            toastr.success(res.message || 'Statut mis à jour avec succès');
          } else if (window.Swal) {
            Swal.fire('Succès', res.message || 'Statut mis à jour avec succès', 'success');
          }
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) {
            toastr.error(res.message || 'Erreur lors du changement de statut');
          } else if (window.Swal) {
            Swal.fire('Erreur', res.message || 'Erreur lors du changement de statut', 'error');
          }
        }
      },
      error: function() {
        if (window.toastr) {
          toastr.error('Erreur réseau');
        } else if (window.Swal) {
          Swal.fire('Erreur', 'Erreur de communication avec le serveur', 'error');
        }
      }
    });
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
