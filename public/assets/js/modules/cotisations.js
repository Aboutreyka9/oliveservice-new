/**
 * Module Cotisations Terrain - Administration Olive Service
 * Gestion DataTables des cotisations quotidiennes (Navy Premium)
 */

$(function() {
  const racine = window.AppConfig ? window.AppConfig.racine : (window.RACINE || '/');

  const $tableCotis = $('#table-cotisations');
  if ($tableCotis.length) {
    $tableCotis.DataTable({
      ajax: racine + 'cotisation/apiList',
      processing: true,
      autoWidth: false,
      columns: [
        { 
          data: 'code_cautisation_client', 
          width: '130px', 
          render: function(d) {
            if (!d) return '-';
            return '<code style="font-weight:700; color:#1E3A5F; background:#F1F5F9; padding:4px 8px; border-radius:6px; font-size:12px; border:1px solid #CBD5E1;">' + d + '</code>';
          }
        },
        { 
          data: 'date_cautisation', 
          defaultContent: '-', 
          width: '140px',
          render: function(d, type, row) {
            if (!d) return '-';
            var formatted = (row && row.date_formatted) ? row.date_formatted : d;
            var time = (row && row.time_formatted) ? row.time_formatted : '';
            var timeBadge = time ? '<span style="color:#64748B; font-size:11px; font-weight:600; background:#F1F5F9; padding:2px 6px; border-radius:4px; margin-left:4px;">' + time + '</span>' : '';
            return '<div style="display:inline-flex; align-items:center; gap:4px; font-weight:700; color:#1E3A5F; font-size:13px;"><i data-lucide="calendar" style="width:14px;height:14px;color:#059669;"></i> <span>' + formatted + '</span>' + timeBadge + '</div>';
          }
        },
        { 
          data: 'nom_client_complet', 
          render: function(d) {
            return '<span style="font-weight:800; color:#0F172A; font-size:14px;">' + (d || '-') + '</span>';
          }
        },
        { 
          data: 'souscription_code', 
          defaultContent: '-',
          render: function(d, type, row) {
            var val = d || (row ? (row.souscription_code || row.code_souscription) : '');
            if (!val) return '-';
            return '<code style="font-weight:700; color:#059669; background:#ECFDF5; padding:4px 8px; border-radius:6px; font-size:12px; border:1px solid #A7F3D0;">' + val + '</code>';
          }
        },
        { 
          data: 'nom_commercial_complet', 
          render: function(d) {
            return '<span style="font-weight:500; color:#64748B;">' + (d || '-') + '</span>';
          }
        },
        { 
          data: 'mode_paiement', 
          defaultContent: '-', 
          width: '110px', 
          className: 'text-center', 
          render: function(d) {
            var badgeStyle = 'background:#F1F5F9; color:#475569; border:1px solid #E2E8F0;';
            var modeText = d || 'Espèce';
            if (d === 'espece') {
              badgeStyle = 'background:#ECFDF5; color:#059669; border:1px solid #A7F3D0;';
              modeText = 'Espèce';
            } else if (d === 'mobile_money') {
              badgeStyle = 'background:#E0F2FE; color:#0284C7; border:1px solid #BAE6FD;';
              modeText = 'Mobile Money';
            } else if (d === 'virement') {
              badgeStyle = 'background:#EEF2FF; color:#4F46E5; border:1px solid #C7D2FE;';
              modeText = 'Virement';
            }
            return '<span style="display:inline-block; padding:4px 10px; border-radius:20px; font-weight:800; font-size:11px; text-transform:uppercase; ' + badgeStyle + '">' + modeText + '</span>';
          }
        },
        { 
          data: 'nombre_jour', 
          className: 'text-center', 
          width: '75px', 
          render: function(d) {
            return '<span style="display:inline-block; padding:3px 8px; border-radius:6px; background:#F8FAFC; color:#1E3A5F; font-weight:800; font-size:12px; border:1px solid #E2E8F0;">+' + (d || 1) + ' j</span>';
          }
        },
        { 
          data: 'montant_cautisation_client', 
          className: 'text-end',
          render: function(d) {
            return '<span style="font-weight:800; color:#059669; font-size:14px;">' + Number(d || 0).toLocaleString('fr-FR') + ' FCFA</span>';
          }
        },
        { 
          data: null, 
          width: '170px', 
          orderable: false, 
          className: 'text-end',
          render: function(d) {
            const editId = d.editId || d.id_cautisation_client;
            const isCloturee = d.caisse_cloturee || (d.statut_caisse_commercial === 'cloture');
            
            let editBtn = '';
            if (isCloturee) {
              editBtn = '<button type="button" class="btn" style="background:#F1F5F9; color:#94A3B8; font-weight:700; border-radius:8px; padding:6px 12px; border:1px solid #E2E8F0; display:inline-flex; align-items:center; gap:4px; font-size:12px; cursor:not-allowed; opacity:0.65;" disabled title="Caisse clôturée - Édition impossible"><i data-lucide="lock" style="width:14px;height:14px;"></i> Éditer</button>';
            } else {
              editBtn = '<a href="' + racine + 'cotisation/edition/' + editId + '" class="btn" style="background:#F1F5F9; color:#1E3A5F; font-weight:700; border-radius:8px; padding:6px 12px; text-decoration:none; border:1px solid #CBD5E1; display:inline-flex; align-items:center; gap:4px; font-size:12px;" title="Modifier"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>';
            }

            return '<div style="display:flex; justify-content:flex-end; gap:6px;">' +
                   editBtn +
                   '<a href="' + racine + 'cotisation/details/' + editId + '" class="btn" style="background:#1E3A5F; color:#FFFFFF; font-weight:700; border-radius:8px; padding:6px 12px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-size:12px;" title="Voir reçu"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>' +
                   '</div>';
          } 
        }
      ],
      language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
      drawCallback: function() {
        if (window.lucide) lucide.createIcons();
      }
    });
  }
});
