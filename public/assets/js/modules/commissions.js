/**
 * Module Mes Commissions - Administration Olive Service / GEICG
 * Calcul dynamique des commissions sur versements commerciaux validés avec filtres avancés
 */

$(function() {
  const racine = window.AppConfig ? window.AppConfig.racine : (window.RACINE || '/');
  const $tableComm = $('#table-commissions');
  let dataTableInstance = null;

  if ($tableComm.length) {
    dataTableInstance = $tableComm.DataTable({
      ajax: {
        url: racine + 'versement/apiCommissions',
        data: function(d) {
          d.date_debut = $('#filter-date-debut').val() || '';
          d.date_fin = $('#filter-date-fin').val() || '';
          d.commercial_code = $('#filter-commercial').val() || '';
        },
        dataSrc: function(json) {
          if (json && json.summary) {
            $('#kpi-total-commissions').text(json.summary.total_commissions_fmt || '0 FCFA');
            $('#kpi-taux-commission').text(json.summary.taux_commission_fmt || '0.00 %');
            $('#kpi-total-versements').text(json.summary.total_versements_fmt || '0 FCFA');
            $('#kpi-count-versements').text(json.summary.count_versements || 0);
          }
          return (json && json.data) ? json.data : [];
        },
        error: function(xhr, error, thrown) {
          console.error("Erreur de chargement des commissions DataTables :", xhr.responseText || thrown);
        }
      },
      processing: true,
      autoWidth: false,
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
      },
      columns: [
        { 
          data: 'code_versement_commercial', 
          width: '130px', 
          render: (d, type) => {
            if (type !== 'display') return d || '';
            return d ? `<code style="font-weight:700; color:#1E3A5F; background:#F1F5F9; padding:4px 8px; border-radius:6px; font-size:12px; border:1px solid #CBD5E1;">${d}</code>` : '-';
          }
        },
        { 
          data: 'nom_commercial_complet', 
          render: (d, type) => {
            if (type !== 'display') return d || '';
            return `<strong style="color:#0F172A; font-size:13px; font-weight:800;">${d || '-'}</strong>`;
          }
        },
        { 
          data: 'libelle_zone', 
          defaultContent: '-', 
          render: (d, type) => {
            if (type !== 'display') return d || '';
            return `<span style="color:#334155; font-weight:600;">${d || '-'}</span>`;
          }
        },
        { 
          data: null, 
          defaultContent: '-', 
          render: function(d, type) {
            const dateVal = d.date_validation || d.created_at_versement || d.periode_versement;
            if (type !== 'display') return dateVal || '';
            return `<span style="color:#64748B; font-size:12px; font-weight:500;">${dateVal || '-'}</span>`;
          }
        },
        { 
          data: null, 
          defaultContent: '-', 
          render: (d, type) => {
            const codeC = d.caisse_code || '-';
            if (type !== 'display') return codeC;
            return `<span style="color:#475569; font-weight:600; font-family:monospace;">${codeC}</span>`;
          }
        },
        { 
          data: 'montant_versement', 
          className: 'text-end',
          render: (d, type) => {
            if (type !== 'display') return d || 0;
            return `<span style="color:#0F172A; font-size:13px; font-weight:800;">${Number(d || 0).toLocaleString('fr-FR')} FCFA</span>`;
          }
        },
        { 
          data: 'taux_commission', 
          className: 'text-center',
          render: (d, type) => {
            if (type !== 'display') return d || 0;
            return `<span style="background:#EFF6FF; color:#2563EB; border:1px solid #BFDBFE; padding:4px 10px; border-radius:12px; font-weight:800; font-size:12px;">${Number(d || 0).toFixed(2)} %</span>`;
          }
        },
        { 
          data: 'montant_commission', 
          className: 'text-end',
          render: (d, type) => {
            if (type !== 'display') return d || 0;
            return `<strong style="color:#047857; font-size:14px; font-weight:900;">+${Number(d || 0).toLocaleString('fr-FR')} FCFA</strong>`;
          }
        },
        { 
          data: 'statut_versement', 
          className: 'text-center', 
          width: '110px', 
          render: function(d, type) {
            if (type !== 'display') return d || '';
            return `<span style="background:#ECFDF5; color:#059669; border:1px solid #A7F3D0; padding:4px 12px; border-radius:20px; font-weight:800; font-size:11px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="check-circle" style="width:12px;height:12px;"></i> Validé</span>`;
          }
        }
      ],
      drawCallback: function() {
        if (typeof lucide !== 'undefined') {
          lucide.createIcons();
        }
      }
    });

    // -------------------------------------------------------------
    // Gestion du bouton "Filtrer"
    // -------------------------------------------------------------
    $('#btn-appliquer-filtre').on('click', function() {
      if (dataTableInstance) {
        dataTableInstance.ajax.reload();
      }
    });

    // -------------------------------------------------------------
    // Gestion du bouton "Effacer"
    // -------------------------------------------------------------
    $('#btn-reset-filtre').on('click', function() {
      $('#filter-date-debut').val('');
      $('#filter-date-fin').val('');
      if ($('#filter-commercial').length) {
        $('#filter-commercial').val('');
      }
      if (dataTableInstance) {
        dataTableInstance.ajax.reload();
      }
    });

    // -------------------------------------------------------------
    // Raccourcis Rapides de Date
    // -------------------------------------------------------------
    $('.btn-shortcut-date').on('click', function() {
      const range = $(this).data('range');
      const now = new Date();
      let debut = '';
      let fin = '';

      const formatDate = function(dateObj) {
        const yyyy = dateObj.getFullYear();
        const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
        const dd = String(dateObj.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
      };

      if (range === 'today') {
        debut = formatDate(now);
        fin = formatDate(now);
      } else if (range === 'month') {
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        debut = formatDate(firstDay);
        fin = formatDate(now);
      } else if (range === 'year') {
        const firstDayYear = new Date(now.getFullYear(), 0, 1);
        debut = formatDate(firstDayYear);
        fin = formatDate(now);
      } else if (range === 'all') {
        debut = '';
        fin = '';
      }

      $('#filter-date-debut').val(debut);
      $('#filter-date-fin').val(fin);

      if (dataTableInstance) {
        dataTableInstance.ajax.reload();
      }
    });
  }
});
