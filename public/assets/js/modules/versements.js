/**
 * Module Versements Commerciaux - Administration Olive Service / GEICG
 * Suivi & Validation Caisse des versements commerciaux
 */

$(function() {
  const racine = window.AppConfig ? window.AppConfig.racine : (window.RACINE || '/');
  const isFinanceOrAdmin = window.AppConfig ? (window.AppConfig.isFinance || window.AppConfig.isAdmin) : false;
  const isCommercial = window.AppConfig ? window.AppConfig.isCommercial : false;

  const $tableVers = $('#table-versements');
  let dataTableInstance = null;

  if ($tableVers.length) {
    dataTableInstance = $tableVers.DataTable({
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
          width: isFinanceOrAdmin ? '230px' : '160px', 
          orderable: false, 
          className: 'text-end',
          render: function(d) {
            const editId = d.editId || d.id_versement;
            let validerBtn = '';
            if (isFinanceOrAdmin) {
              validerBtn = `
                <button type="button" class="btn btn-valider-versement" 
                  data-id="${d.id_versement}"
                  data-code="${d.code_versement_commercial || ''}"
                  data-commercial="${d.nom_commercial_complet || ''}"
                  data-zone="${d.libelle_zone || ''}"
                  data-montant="${d.montant_versement || 0}"
                  data-periode="${(d.periode_versement_debut || '-') + ' → ' + (d.periode_versement_fin || '-')}"
                  data-ref="${d.reference_versement || '-'}"
                  data-statut="${d.statut_versement || ''}"
                  style="background:#059669; color:#FFFFFF; font-weight:700; border-radius:8px; padding:6px 12px; border:none; display:inline-flex; align-items:center; gap:4px; font-size:12px; cursor:pointer;" 
                  title="Contrôler et Valider le versement">
                  <i data-lucide="check-circle" style="width:14px;height:14px;"></i> Valider
                </button>
              `;
            }
            return `
              <div style="display:flex; justify-content:flex-end; gap:6px; align-items:center;">
                ${validerBtn}
                ${!isCommercial ? `<a href="${racine}versement/edition/${editId}" class="btn" style="background:#F1F5F9; color:#1E3A5F; font-weight:700; border-radius:8px; padding:6px 12px; text-decoration:none; border:1px solid #CBD5E1; display:inline-flex; align-items:center; gap:4px; font-size:12px;" title="Modifier"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>` : ''}
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

  // --- GESTION DE LA MODALE DE VALIDATION COMPTABLE ---
  const $modalVal = $('#modalValiderVersement');

  $(document).on('click', '.btn-valider-versement', function() {
    const btn = $(this);
    const id = btn.data('id');
    const code = btn.data('code') || '-';
    const commercial = btn.data('commercial') || '-';
    const zone = btn.data('zone') || '-';
    const montant = Number(btn.data('montant') || 0);
    const periode = btn.data('periode') || '-';
    const ref = btn.data('ref') || '-';

    $('#val_id_versement').val(id);
    $('#val_code_versement').text(code);
    $('#val_commercial_nom').text(commercial);
    $('#val_zone_nom').text(zone);
    $('#val_montant_fmt').text(montant.toLocaleString('fr-FR') + ' FCFA');
    $('#val_periode_txt').text(periode);
    $('#val_reference_txt').text(ref);
    $('#val_commentaire').val('');

    $('input[name="statut_versement"][value="valide"]').prop('checked', true);

    $modalVal.fadeIn(200).css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  });

  $(document).on('click', '#modalValiderClose, .modal-close-btn', function() {
    $modalVal.fadeOut(150);
  });

  $modalVal.on('click', function(e) {
    if ($(e.target).is($modalVal)) {
      $modalVal.fadeOut(150);
    }
  });

  $('#form-valider-versement').on('submit', function(e) {
    e.preventDefault();
    const idVersement = $('#val_id_versement').val();
    const statut = $('input[name="statut_versement"]:checked').val() || 'valide';
    const commentaire = $('#val_commentaire').val();
    const csrfToken = $('#csrf_token').val() || '';

    const $btnSubmit = $('#btn-submit-valider-versement');
    const originalHtml = $btnSubmit.html();
    $btnSubmit.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Traitement...');

    $.ajax({
      url: racine + 'versement/valider',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id_versement: idVersement,
        statut_versement: statut,
        commentaire_validation: commentaire,
        csrf_token: csrfToken
      },
      dataType: 'json',
      success: function(res) {
        $btnSubmit.prop('disabled', false).html(originalHtml);
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Opération réussie');
          $modalVal.fadeOut(150);
          if (dataTableInstance) {
            dataTableInstance.ajax.reload(null, false);
          } else {
            setTimeout(() => { window.location.reload(); }, 800);
          }
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de la validation');
        }
      },
      error: function(xhr) {
        $btnSubmit.prop('disabled', false).html(originalHtml);
        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Erreur réseau ou action non autorisée';
        if (window.toastr) toastr.error(msg);
      }
    });
  });
});
