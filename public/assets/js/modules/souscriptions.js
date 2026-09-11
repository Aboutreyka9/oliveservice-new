/**
 * Module Souscription - Administration GEICG
 * DataTables, Calculs de progression & Gestion des Contrats
 */

$(function() {
  const racine = window.AppConfig ? window.AppConfig.racine : (window.RACINE || '/');

  const $tableSouscr = $('#table-souscriptions');
  if ($tableSouscr.length) {
    var dt = $tableSouscr.DataTable({
      ajax: racine + 'souscription/apiList',
      processing: true,
      autoWidth: false,
      columns: [
        { 
          data: 'code_souscription', 
          width: '130px', 
          render: (d) => d ? `<span style="font-family: monospace; font-weight: 700; background: #F1F5F9; color: #1E3A5F; padding: 4px 8px; border-radius: 6px; border: 1px solid #CBD5E1;">${d}</span>` : '-' 
        },
        { 
          data: 'statut_souscription', 
          className: 'text-center', 
          width: '100px', 
          render: function(d) {
            let badgeStyle = 'background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE;';
            let libelle = 'Validée';
            if (d === 'solde') { badgeStyle = 'background: #DCFCE7; color: #15803D; border: 1px solid #BBF7D0;'; libelle = 'Soldée'; }
            else if (d === 'annule') { badgeStyle = 'background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA;'; libelle = 'Annulée'; }
            else if (d === 'reconduite') { badgeStyle = 'background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A;'; libelle = 'Reconduite'; }
            return `<span style="display: inline-block; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; ${badgeStyle}">${libelle}</span>`;
          }
        },
        { 
          data: 'nom_client_complet', 
          render: (d) => `<div style="display:flex; align-items:center; gap:8px;">
            <div style="width:30px; height:30px; border-radius:50%; background:linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color:#FFF; font-weight:800; display:flex; align-items:center; justify-content:center; font-size:12px; flex-shrink:0;">${(d || 'C').charAt(0).toUpperCase()}</div>
            <strong style="color:#0F172A; font-size:13px;">${d || '-'}</strong>
          </div>` 
        },
        { data: 'libelle_session', defaultContent: '-' },
        { 
          data: 'sum_prix_cotisation_pack', 
          render: (d) => `<span style="font-weight:700; color:#2563EB;">${Number(d || 0).toLocaleString('fr-FR')} FCFA</span>` 
        },
        { 
          data: 'totale_souscription', 
          className: 'text-end',
          render: (d) => `<strong style="color:#1E3A5F;">${Number(d || 0).toLocaleString('fr-FR')} FCFA</strong>` 
        },
        { 
          data: null, 
          render: function(d) {
            const cotise = d.nombre_jour_cotise || 0;
            const total = d.nombre_jour_session || 0;
            const pct = total > 0 ? Math.round((cotise / total) * 100) : 0;
            const color = pct >= 100 ? '#10B981' : (pct >= 50 ? '#F59E0B' : '#EF4444');
            return `
              <div style="min-width:130px;">
                <div style="display:flex; justify-content:space-between; font-size:11px; margin-bottom:3px;">
                  <strong style="color:#334155;">${cotise} / ${total} j</strong>
                  <strong style="color:${color};">${pct}%</strong>
                </div>
                <div class="progress" style="height:6px; background:#E2E8F0; border-radius:3px; overflow:hidden;">
                  <div class="progress-bar" style="width:${pct}%; background:${color}; height:100%; border-radius:3px;"></div>
                </div>
              </div>`;
          }
        },
        { 
          data: 'montant_total_cotise', 
          className: 'text-end',
          render: (d) => `<strong style="color:#15803D;">${Number(d || 0).toLocaleString('fr-FR')} FCFA</strong>` 
        },
        { 
          data: 'solde_restant', 
          className: 'text-end',
          render: function(d) {
            if ((d || 0) <= 0) return '<span style="color:#15803D; font-weight:800; background:#DCFCE7; padding:2px 8px; border-radius:12px; font-size:11px; border:1px solid #BBF7D0;">Soldé</span>';
            return `<strong style="color:#DC2626;">${Number(d).toLocaleString('fr-FR')} FCFA</strong>`;
          }
        },
        { data: 'date_souscription', defaultContent: '-', className: 'text-center' },
        { 
          data: null, 
          width: '240px', 
          orderable: false, 
          className: 'text-end',
          render: function(d) {
            const editId = d.editId || d.id_souscription;
            const canCollect = window.AppConfig && window.AppConfig.can ? window.AppConfig.can('COMMERCIAL_COLLECT_COTISATION') : false;
            const canEdit = window.AppConfig && window.AppConfig.can ? window.AppConfig.can('GESTIONNAIRE_EDIT_SOUSCRIPTION') : false;

            const situationBtn = canCollect ? `
              <a href="${racine}cautisation-payment/situation/${d.code_souscription}" class="btn btn-sm" style="background:#10B981; border-color:#10B981; color:#FFF; font-weight:700; border-radius:6px; padding:5px 10px; margin-right:4px;" title="Situation des versements">
                <i data-lucide="credit-card" style="width:13px;height:13px; display:inline-block; vertical-align:middle;"></i> Situation
              </a>` : '';

            const editBtn = canEdit ? `
              <a href="${racine}souscription/edition/${editId}" class="btn btn-sm" style="background:#F1F5F9; border-color:#CBD5E1; color:#334155; font-weight:700; border-radius:6px; padding:5px 8px; margin-right:4px;" title="Éditer">
                <i data-lucide="edit-3" style="width:13px;height:13px; display:inline-block; vertical-align:middle;"></i>
              </a>` : '';

            return `
              ${situationBtn}
              ${editBtn}
              <a href="${racine}souscription/details/${editId}" class="btn btn-sm" style="background:#1E3A5F; border-color:#1E3A5F; color:#FFF; font-weight:700; border-radius:6px; padding:5px 8px;" title="Détails complets">
                <i data-lucide="eye" style="width:13px;height:13px; display:inline-block; vertical-align:middle;"></i> Détails
              </a>
            `;
          } 
        }
      ],
      language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
      drawCallback: () => {
        if (window.lucide) lucide.createIcons();
      }
    });

    // Filtres rapides par statut
    $('.btn-filter-status').on('click', function() {
      $('.btn-filter-status').removeClass('active').css({ 'background': '#F1F5F9', 'color': '#64748B' });
      $(this).addClass('active').css({ 'background': '#1E3A5F', 'color': '#FFFFFF' });
      var status = $(this).data('status');
      if (status === 'all') {
        dt.column(1).search('').draw();
      } else {
        dt.column(1).search(status).draw();
      }
    });
  }
});
