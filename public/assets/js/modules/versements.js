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
        { 
          data: null, 
          defaultContent: '-', 
          render: (d, type) => {
            const codeC = d.caisse_code || (d.reference_versement || '-');
            if (type !== 'display') return codeC;
            return `<span style="color:#475569; font-weight:600; font-family:monospace;">${codeC}</span>`;
          }
        },
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
                  data-ref="${d.caisse_code || (d.reference_versement || '-')}"
                  data-statut="${d.statut_versement || ''}"
                  style="background:#059669; color:#FFFFFF; font-weight:700; border-radius:8px; padding:6px 12px; border:none; display:inline-flex; align-items:center; gap:4px; font-size:12px; cursor:pointer;" 
                  title="Contrôler et Valider le versement">
                  <i data-lucide="check-circle" style="width:14px;height:14px;"></i> Valider
                </button>
              `;
            }
            const caisseTarget = d.caisseIdCrypte || (d.editId || d.id_versement);
            return `
              <div style="display:flex; justify-content:flex-end; gap:6px; align-items:center;">
                ${validerBtn}
                ${!isCommercial ? `<a href="${racine}versement/edition/${editId}" class="btn" style="background:#F1F5F9; color:#1E3A5F; font-weight:700; border-radius:8px; padding:6px 12px; text-decoration:none; border:1px solid #CBD5E1; display:inline-flex; align-items:center; gap:4px; font-size:12px;" title="Modifier"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>` : ''}
                <a href="${racine}caisse_commercial/details/${caisseTarget}" class="btn" style="background:#1E3A5F; color:#FFFFFF; font-weight:700; border-radius:8px; padding:6px 12px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-size:12px;" title="Voir procès-verbal de caisse"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>
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

  // --- GESTION DE LA MODALE DE VALIDATION COMPTABLE ET HISTORIQUE CAISSE ---
  const $modalVal = $('#modalValiderVersement');

  // Gestion des onglets de l'historique
  $(document).on('click', '#tab-btn-caisse-cotis', function() {
    $('#tab-btn-caisse-cotis').addClass('active').css({ 'background': '#FFFFFF', 'color': '#059669', 'border-color': '#CBD5E1' });
    $('#tab-btn-cotis').removeClass('active').css({ 'background': '#F8FAFC', 'color': '#64748B', 'border-color': '#E2E8F0' });
    $('#tab-btn-sessions').removeClass('active').css({ 'background': '#F8FAFC', 'color': '#64748B', 'border-color': '#E2E8F0' });
    $('#tab-content-caisse-cotis').show();
    $('#tab-content-cotis').hide();
    $('#tab-content-sessions').hide();
  });

  $(document).on('click', '#tab-btn-cotis', function() {
    $('#tab-btn-cotis').addClass('active').css({ 'background': '#FFFFFF', 'color': '#1E3A5F', 'border-color': '#CBD5E1' });
    $('#tab-btn-caisse-cotis').removeClass('active').css({ 'background': '#F8FAFC', 'color': '#64748B', 'border-color': '#E2E8F0' });
    $('#tab-btn-sessions').removeClass('active').css({ 'background': '#F8FAFC', 'color': '#64748B', 'border-color': '#E2E8F0' });
    $('#tab-content-cotis').show();
    $('#tab-content-caisse-cotis').hide();
    $('#tab-content-sessions').hide();
  });

  $(document).on('click', '#tab-btn-sessions', function() {
    $('#tab-btn-sessions').addClass('active').css({ 'background': '#FFFFFF', 'color': '#1E3A5F', 'border-color': '#CBD5E1' });
    $('#tab-btn-caisse-cotis').removeClass('active').css({ 'background': '#F8FAFC', 'color': '#64748B', 'border-color': '#E2E8F0' });
    $('#tab-btn-cotis').removeClass('active').css({ 'background': '#F8FAFC', 'color': '#64748B', 'border-color': '#E2E8F0' });
    $('#tab-content-sessions').show();
    $('#tab-content-caisse-cotis').hide();
    $('#tab-content-cotis').hide();
  });

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

    // Re-initialiser l'onglet actif par défaut sur les cotisations de la caisse
    $('#tab-btn-caisse-cotis').trigger('click');

    // Afficher le loader pour l'historique
    $('#caisse_history_loading').show();
    $('#caisse_history_content').hide();

    $modalVal.fadeIn(200).css('display', 'flex');
    if (window.lucide) lucide.createIcons();

    // Charger l'historique de caisse du commercial via API
    $.ajax({
      url: racine + 'versement/apiCommercialCaisseHistory',
      type: 'GET',
      data: { id_versement: id },
      dataType: 'json',
      success: function(res) {
        $('#caisse_history_loading').hide();
        $('#caisse_history_content').show();

        if (res.status === 1 && res.data) {
          const d = res.data;
          $('#hist_total_encaisse').text(d.total_collecte_fmt);
          $('#hist_details_modes').text(`Esp: ${d.total_especes_fmt} | MoMo: ${d.total_momo_fmt}`);
          $('#hist_versements_valides').text(d.total_versements_valides_fmt);
          $('#hist_reste_a_verser').text(d.solde_reste_a_verser_fmt);

          // Caisse liée
          if (d.has_linked_caisse) {
            $('#hist_linked_caisse_code').text(d.linked_caisse_code);
            $('#hist_caisse_attendu').text(d.caisse_attendu_fmt);
            $('#hist_caisse_pot').text(d.versement_actuel_fmt);

            if (d.caisse_ecart === 0) {
              $('#hist_caisse_ecart').text('0 FCFA (Conforme)').css('color', '#059669');
            } else if (d.caisse_ecart > 0) {
              $('#hist_caisse_ecart').text('+' + d.caisse_ecart_fmt + ' (Surplus)').css('color', '#2563EB');
            } else {
              $('#hist_caisse_ecart').text('-' + d.caisse_ecart_fmt + ' (Manquant)').css('color', '#DC2626');
            }
            $('#box-linked-caisse').show();
          } else {
            $('#box-linked-caisse').hide();
          }

          // Cotisations spécifiques de la séance de caisse
          const caisseCotis = d.caisse_cotisations || [];
          $('#hist_nb_caisse_cotis').text(caisseCotis.length);
          let htmlCaisseCotis = '';
          if (caisseCotis.length === 0) {
            htmlCaisseCotis = '<tr><td colspan="6" style="text-align: center; padding: 14px; color: #94A3B8;">Aucune cotisation attachée à cette caisse</td></tr>';
          } else {
            caisseCotis.forEach(c => {
              let stBadge = '<span style="color:#D97706; font-weight:700; background:#FEF3C7; padding:2px 8px; border-radius:12px; font-size:10px;">En attente</span>';
              if (c.statut === 'valide') stBadge = '<span style="color:#059669; font-weight:700; background:#ECFDF5; padding:2px 8px; border-radius:12px; font-size:10px;">Validée</span>';
              if (c.statut === 'annule') stBadge = '<span style="color:#DC2626; font-weight:700; background:#FEE2E2; padding:2px 8px; border-radius:12px; font-size:10px;">Annulée</span>';

              htmlCaisseCotis += `
                <tr style="border-bottom: 1px solid #F1F5F9;">
                  <td style="padding: 6px 10px;">
                    <code style="font-weight:700; color:#1E3A5F; font-size:10px; background:#F1F5F9; padding:2px 5px; border-radius:4px;">${c.souscription}</code>
                  </td>
                  <td style="padding: 6px 10px; font-weight:700; color:#0F172A;">
                    ${c.client}
                    <small style="color:#64748B; display:block; font-weight:normal;">${c.telephone}</small>
                  </td>
                  <td style="padding: 6px 10px; font-size:10px; color:#475569;">
                    <span style="background:#F1F5F9; padding:2px 6px; border-radius:4px; font-weight:700;">${c.mode}</span>
                  </td>
                  <td style="padding: 6px 10px; font-size:10px; color:#64748B;">${c.date}</td>
                  <td style="padding: 6px 10px; text-align:right; font-weight:800; color:#059669;">+${c.montant_fmt}</td>
                  <td style="padding: 6px 10px; text-align:center;">${stBadge}</td>
                </tr>
              `;
            });
          }
          $('#hist_tbody_caisse_cotis').html(htmlCaisseCotis);

          // Écart & Conformité Globale
          const ecart = d.ecart_comparaison;
          if (ecart === 0) {
            $('#hist_ecart_verif').text('0 FCFA').css('color', '#059669');
            $('#hist_ecart_badge').text('Conforme').css({'color': '#059669', 'background': '#ECFDF5'});
          } else if (ecart > 0) {
            $('#hist_ecart_verif').text('+' + d.ecart_comparaison_fmt).css('color', '#2563EB');
            $('#hist_ecart_badge').text('Surplus (+' + d.ecart_comparaison_fmt + ')').css({'color': '#2563EB', 'background': '#EFF6FF'});
          } else {
            $('#hist_ecart_verif').text('-' + d.ecart_comparaison_fmt).css('color', '#DC2626');
            $('#hist_ecart_badge').text('Sous-versement (-' + d.ecart_comparaison_fmt + ')').css({'color': '#DC2626', 'background': '#FEE2E2'});
          }

          // Cotisations récentes
          const cotis = d.recent_cotisations || [];
          $('#hist_nb_cotis').text(cotis.length);
          let htmlCotis = '';
          if (cotis.length === 0) {
            htmlCotis = '<tr><td colspan="5" style="text-align: center; padding: 12px; color: #94A3B8;">Aucune cotisation récente</td></tr>';
          } else {
            cotis.forEach(c => {
              let stBadge = '<span style="color:#D97706; font-weight:700;">Attente</span>';
              if (c.statut === 'valide') stBadge = '<span style="color:#059669; font-weight:700;">Validée</span>';
              if (c.statut === 'annule') stBadge = '<span style="color:#DC2626; font-weight:700;">Annulée</span>';

              htmlCotis += `
                <tr style="border-bottom: 1px solid #F1F5F9;">
                  <td style="padding: 6px 10px;">
                    <code style="font-weight:700; color:#1E3A5F; font-size:10px;">${c.code}</code>
                    <div style="font-size:10px; color:#64748B;">${c.date}</div>
                  </td>
                  <td style="padding: 6px 10px; font-weight:600; color:#0F172A;">${c.client}</td>
                  <td style="padding: 6px 10px; font-size:10px; color:#475569;">${c.mode}</td>
                  <td style="padding: 6px 10px; text-align:right; font-weight:800; color:#059669;">+${c.montant_fmt}</td>
                  <td style="padding: 6px 10px; text-align:center; font-size:10px;">${stBadge}</td>
                </tr>
              `;
            });
          }
          $('#hist_tbody_cotis').html(htmlCotis);

          // Sessions de caisse
          const sessions = d.sessions || [];
          $('#hist_nb_sessions').text(sessions.length);
          let htmlSessions = '';
          if (sessions.length === 0) {
            htmlSessions = '<tr><td colspan="5" style="text-align: center; padding: 12px; color: #94A3B8;">Aucune session de caisse</td></tr>';
          } else {
            sessions.forEach(s => {
              const stClass = s.statut_caisse === 'cloture' ? '<span style="color:#059669; font-weight:700;">Clôturée</span>' : '<span style="color:#D97706; font-weight:700;">Ouverte</span>';
              htmlSessions += `
                <tr style="border-bottom: 1px solid #F1F5F9;">
                  <td style="padding: 6px 10px;">
                    <code style="font-weight:700; color:#1E3A5F; font-size:10px;">${s.code_caisse}</code>
                  </td>
                  <td style="padding: 6px 10px; font-size:10px; color:#475569;">${s.date_ouverture}</td>
                  <td style="padding: 6px 10px; font-size:10px; color:#475569;">${s.date_cloture}</td>
                  <td style="padding: 6px 10px; text-align:right; font-weight:800; color:#0F172A;">${s.montant_depot_fmt}</td>
                  <td style="padding: 6px 10px; text-align:center; font-size:10px;">${stClass}</td>
                </tr>
              `;
            });
          }
          $('#hist_tbody_sessions').html(htmlSessions);
        } else {
          $('#hist_tbody_cotis').html('<tr><td colspan="5" style="text-align: center; padding: 12px; color: #EF4444;">Erreur de chargement de l\'historique</td></tr>');
        }
      },
      error: function() {
        $('#caisse_history_loading').hide();
        $('#caisse_history_content').show();
        $('#hist_tbody_cotis').html('<tr><td colspan="5" style="text-align: center; padding: 12px; color: #EF4444;">Erreur réseau lors du chargement de l\'historique</td></tr>');
      }
    });
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
