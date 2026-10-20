/**
 * Module Reconductions - Gestion Dédiée des Reports et Renouvellements de Contrats
 * DataTable, Filtres dynamiques, Modal de reconduction & Actions rapides
 */

$(function () {
  const racine = window.AppConfig ? window.AppConfig.racine : (window.RACINE || '/');

  const $tableRecon = $('#table-reconductions');
  if ($tableRecon.length) {
    const dt = $tableRecon.DataTable({
      ajax: racine + 'souscription/apiReconductions',
      processing: true,
      autoWidth: false,
      columns: [
        {
          data: 'code_souscription',
          width: '130px',
          render: (d) => d ? `<span style="font-family: monospace; font-weight: 700; background: #F1F5F9; color: #1E3A5F; padding: 4px 8px; border-radius: 6px; border: 1px solid #CBD5E1;">${d}</span>` : '-'
        },
        {
          data: 'reconduction_status',
          className: 'text-center',
          width: '110px',
          render: function (d, type, row) {
            const st = (d || '').trim();
            if (st === 'Reconduite') {
              return `<span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A;">
                <i data-lucide="refresh-cw" style="width: 12px; height: 12px;"></i> Reconduite
              </span>`;
            } else if (st === 'Éligible') {
              return `<span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; background: #DCFCE7; color: #15803D; border: 1px solid #BBF7D0;">
                <i data-lucide="check-circle-2" style="width: 12px; height: 12px;"></i> Éligible
              </span>`;
            } else if (st === 'Annulée') {
              return `<span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA;">
                <i data-lucide="ban" style="width: 12px; height: 12px;"></i> Annulée
              </span>`;
            }
            return `<span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE;">
              En cours
            </span>`;
          }
        },
        {
          data: 'nom_client_complet',
          render: function (d, type, row) {
            const tel = row.telephone_client ? `<small style="color:#64748B; display:block; font-size:11px;">${row.telephone_client}</small>` : '';
            return `<div style="display:flex; align-items:center; gap:8px;">
              <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color:#FFF; font-weight:800; display:flex; align-items:center; justify-content:center; font-size:12px; flex-shrink:0;">${(d || 'C').charAt(0).toUpperCase()}</div>
              <div>
                <strong style="color:#0F172A; font-size:13px; display:block;">${d || '-'}</strong>
                ${tel}
              </div>
            </div>`;
          }
        },
        { data: 'libelle_session', defaultContent: '-' },
        {
          data: 'libelle_pack',
          render: (d) => `<span style="font-weight:600; color:#334155; font-size:12px;">${d || '-'}</span>`
        },
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
          render: function (d) {
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
          data: null,
          className: 'text-center',
          render: function (d) {
            if (d.is_reconduite && d.date_reconduction && d.date_reconduction !== '-') {
              return `<span style="color:#92400E; font-weight:700; font-size:12px;" title="Date de reconduction">${d.date_reconduction}</span>`;
            }
            return `<span style="color:#64748B; font-size:12px;" title="Date de souscription">${d.date_souscription || '-'}</span>`;
          }
        },
        {
          data: null,
          width: '130px',
          orderable: false,
          className: 'text-end',
          render: function (d) {
            const editId = d.editId || d.id_souscription;
            const isReconduite = d.is_reconduite;
            const isEligible = d.is_eligible;

            // Bouton Reconduire rapide pour contrat éligible
            let reconduireBtn = '';
            if (isEligible) {
              reconduireBtn = `
                <button type="button" class="btn-action-icon warning btn-trigger-reconduire" 
                  data-id="${d.id_souscription}" 
                  data-code="${d.code_souscription}" 
                  data-client="${(d.nom_client_complet || '').replace(/"/g, '&quot;')}"
                  data-phone="${(d.telephone_client || '').replace(/"/g, '&quot;')}"
                  data-session="${(d.libelle_session || '').replace(/"/g, '&quot;')}"
                  data-cotise="${Number(d.montant_total_cotise || 0).toLocaleString('fr-FR')} FCFA"
                  data-total="${Number(d.totale_souscription || 0).toLocaleString('fr-FR')} FCFA"
                  data-statut="${d.reconduction_status || 'Éligible'}"
                  title="Reconduire ce contrat">
                  <i data-lucide="refresh-cw" style="width:15px;height:15px;"></i>
                </button>`;
            }

            // Bouton Ressouscription immédiate
            const ressouscrireBtn = d.client_code ? `
              <a href="${racine}souscription/ressouscription?client_code=${encodeURIComponent(d.client_code)}" class="btn-action-icon success" title="Nouvelle souscription pour ce client">
                <i data-lucide="repeat" style="width:15px;height:15px;"></i>
              </a>` : '';

            // Bouton Situation des versements
            const situationBtn = `
              <a href="${racine}cautisation-payment/situation/${d.code_souscription}" class="btn-action-icon" style="color:#0284C7; border-color:#BAE6FD; background:#F0F9FF;" title="Situation des cotisations">
                <i data-lucide="credit-card" style="width:15px;height:15px;"></i>
              </a>`;

            // Bouton Détails
            const detailsBtn = `
              <a href="${racine}souscription/details/${editId}" class="btn-action-icon primary" title="Consulter la souscription">
                <i data-lucide="eye" style="width:15px;height:15px;"></i>
              </a>`;

            return `
              <div class="recon-actions-wrap">
                ${reconduireBtn}
                ${ressouscrireBtn}
                ${situationBtn}
                ${detailsBtn}
              </div>
            `;
          }
        }
      ],
      language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
      initComplete: function () {
        // Appliquer par défaut le filtre "Reconduits"
        $('.btn-filter-recon[data-status="Reconduite"]').trigger('click');
      },
      drawCallback: () => {
        if (window.lucide) lucide.createIcons();
      }
    });

    // Filtres d'état rapides
    $('.btn-filter-recon').on('click', function () {
      $('.btn-filter-recon').removeClass('active').css({ 'background': '#F8FAFC', 'color': '#475569', 'border-color': '#CBD5E1' });
      $(this).addClass('active').css({ 'background': '#1E3A5F', 'color': '#FFFFFF', 'border-color': '#1E3A5F' });
      const status = $(this).data('status');
      if (status === 'all') {
        dt.column(1).search('').draw();
      } else {
        dt.column(1).search(status).draw();
      }
    });

    // Clic sur cartes KPI
    $(document).on('click', '.recon-kpi-card[data-filter-target]', function () {
      const target = $(this).data('filter-target');
      if (target) {
        $('.btn-filter-recon[data-status="' + target + '"]').trigger('click');
        const off = $tableRecon.offset().top - 120;
        if ($(window).scrollTop() < off - 200 || $(window).scrollTop() > off + 400) {
          $('html, body').animate({ scrollTop: off }, 350);
        }
      }
    });

    // Bouton d'actualisation
    $('#btn-reload-table').on('click', function () {
      const $btn = $(this);
      $btn.prop('disabled', true).addClass('loading');
      dt.ajax.reload(function () {
        $btn.prop('disabled', false).removeClass('loading');
        if (window.lucide) lucide.createIcons();
      }, false);
    });

    // Gestion de la modale de reconduction
    const $modalRecon = $('#modal-reconduire-contrat');

    // Déclenchement de la modale de reconduction
    $(document).on('click', '.btn-trigger-reconduire', function () {
      const btn = $(this);
      const id = btn.data('id');
      const code = btn.data('code') || '-';
      const client = btn.data('client') || '-';
      const phone = btn.data('phone') || '';
      const session = btn.data('session') || '-';
      const cotise = btn.data('cotise') || '0 FCFA';
      const total = btn.data('total') || '0 FCFA';
      const statut = btn.data('statut') || 'Éligible';

      $('#recon-modal-id').val(id);
      $('#recon-modal-code').val(code);
      $('#recon-modal-code-display').text(code);
      $('#recon-modal-client-display').text(client);
      $('#recon-modal-phone-display').text(phone ? 'Tél : ' + phone : '');
      $('#recon-modal-session-display').text(session);
      $('#recon-modal-cotise-display').text(cotise);
      $('#recon-modal-total-display').text(total);
      $('#recon-modal-statut-display').text(statut);
      $('#recon-notes').val('');
      $('#target-session-code').val('');

      $modalRecon.css('display', 'flex').hide().fadeIn(180);
      if (window.lucide) lucide.createIcons();
    });

    // Fermeture de la modale via bouton fermer ou annuler
    $(document).on('click', '.btn-modal-close', function () {
      $modalRecon.fadeOut(150);
    });

    // Fermeture en cliquant sur l'overlay extérieur
    $modalRecon.on('click', function (e) {
      if ($(e.target).is($modalRecon)) {
        $modalRecon.fadeOut(150);
      }
    });

    // Fermeture avec la touche Échap
    $(document).on('keydown', function (e) {
      if (e.key === 'Escape' && $modalRecon.is(':visible')) {
        $modalRecon.fadeOut(150);
      }
    });

    // Soumission du formulaire de reconduction via AJAX
    $('#form-modal-reconduire').on('submit', function (e) {
      e.preventDefault();
      const $form = $(this);
      const $btn = $('#btn-confirm-reconduire');
      const formData = $form.serialize();

      $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Traitement...');

      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function (res) {
          $btn.prop('disabled', false).html('<i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i> Confirmer la Reconduction');
          $modalRecon.fadeOut(150);

          if (res.status === 'success') {
            if (typeof Swal !== 'undefined') {
              Swal.fire({
                icon: 'success',
                title: 'Contrat Reconduit !',
                text: res.message || 'La souscription a été marquée comme reconduite avec succès.',
                showCancelButton: true,
                confirmButtonText: 'Aller à la Ressouscription',
                cancelButtonText: 'Rester sur cette page',
                confirmButtonColor: '#059669',
                cancelButtonColor: '#1E3A5F'
              }).then((result) => {
                if (result.isConfirmed && res.data && res.data.redirect) {
                  window.location.href = res.data.redirect;
                } else {
                  dt.ajax.reload(null, false);
                }
              });
            } else {
              alert(res.message || 'Succès');
              if (res.data && res.data.redirect) {
                window.location.href = res.data.redirect;
              } else {
                dt.ajax.reload(null, false);
              }
            }
          } else {
            if (typeof Swal !== 'undefined') {
              Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: res.message || 'Une erreur est survenue lors de la reconduction.',
                confirmButtonColor: '#1E3A5F'
              });
            } else {
              alert(res.message || 'Erreur');
            }
          }
        },
        error: function (xhr) {
          $btn.prop('disabled', false).html('<i data-lucide="refresh-cw" style="width: 15px; height: 15px; display: inline-block; vertical-align: middle; margin-right: 4px;"></i> Confirmer la Reconduction');
          let errMsg = 'Erreur serveur lors de la reconduction.';
          try {
            const errRes = JSON.parse(xhr.responseText);
            if (errRes.message) errMsg = errRes.message;
          } catch (e) {}

          if (typeof Swal !== 'undefined') {
            Swal.fire({
              icon: 'error',
              title: 'Erreur technique',
              text: errMsg,
              confirmButtonColor: '#1E3A5F'
            });
          } else {
            alert(errMsg);
          }
        }
      });
    });
  }
});
