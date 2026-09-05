/**
 * Module Versements Commerciaux - Administration Olive Service / GEICG
 * Suivi & Validation Caisse des versements commerciaux
 */

$(function() {
  const racine = window.AppConfig ? window.AppConfig.racine : (window.RACINE || '/');

  const $tableVers = $('#table-versements');
  if ($tableVers.length) {
    $tableVers.DataTable({
      ajax: racine + 'versement/apiList',
      processing: true,
      autoWidth: false,
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
            return `<strong style="color:#0F172A; font-size:14px; font-weight:800;">${d || '-'}</strong>`;
          }
        },
        { data: 'libelle_zone', defaultContent: '-', render: (d, type) => {
          if (type !== 'display') return d || '';
          return `<span style="color:#334155; font-weight:600;">${d || '-'}</span>`;
        }},
        { 
          data: null, 
          defaultContent: '-', 
          render: function(d, type) {
            if (type !== 'display') return (d.periode_versement_debut || '') + ' ' + (d.periode_versement_fin || '');
            const debut = d.periode_versement_debut || '-';
            const fin = d.periode_versement_fin || '-';
            return `<span style="color:#64748B; font-size:12px; font-weight:500;">${debut} &rarr; ${fin}</span>`;
          }
        },
        { data: 'reference_versement', defaultContent: '-', render: (d, type) => {
          if (type !== 'display') return d || '';
          return `<span style="color:#475569; font-weight:600; font-family:monospace;">${d || '-'}</span>`;
        }},
        { 
          data: 'montant_versement', 
          className: 'text-end',
          render: (d, type) => {
            if (type !== 'display') return d || 0;
            return `<strong style="color:#059669; font-size:14px; font-weight:800;">+${Number(d || 0).toLocaleString('fr-FR')} FCFA</strong>`;
          }
        },
        { 
          data: 'statut_versement', 
          className: 'text-center', 
          width: '120px', 
          render: function(d, type) {
            if (type !== 'display') return d || '';
            if (d === 'valide') {
              return `<span style="background:#ECFDF5; color:#059669; border:1px solid #A7F3D0; padding:4px 12px; border-radius:20px; font-weight:800; font-size:11px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="check-circle" style="width:12px;height:12px;"></i> Validé</span>`;
            } else if (d === 'ennule' || d === 'annule') {
              return `<span style="background:#FEE2E2; color:#DC2626; border:1px solid #FECACA; padding:4px 12px; border-radius:20px; font-weight:800; font-size:11px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="x-circle" style="width:12px;height:12px;"></i> Annulé</span>`;
            }
            return `<span style="background:#FEF3C7; color:#D97706; border:1px solid #FDE68A; padding:4px 12px; border-radius:20px; font-weight:800; font-size:11px; display:inline-flex; align-items:center; gap:4px;"><i data-lucide="clock" style="width:12px;height:12px;"></i> En attente</span>`;
          }
        },
        { 
          data: null, 
          width: '170px', 
          orderable: false, 
          className: 'text-end',
          render: function(d) {
            const editId = d.editId || d.id_versement;
            return `
              <div style="display:flex; justify-content:flex-end; gap:6px;">
                <a href="${racine}versement/edition/${editId}" class="btn" style="background:#F1F5F9; color:#1E3A5F; font-weight:700; border-radius:8px; padding:6px 12px; text-decoration:none; border:1px solid #CBD5E1; display:inline-flex; align-items:center; gap:4px; font-size:12px;" title="Modifier"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>
                <a href="${racine}versement/details/${editId}" class="btn" style="background:#1E3A5F; color:#FFFFFF; font-weight:700; border-radius:8px; padding:6px 12px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-size:12px;" title="Voir détails"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>
              </div>
            `;
          } 
        }
      ],
      language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
      drawCallback: () => {
        if (window.lucide) lucide.createIcons();
      }
    });
  }
});
