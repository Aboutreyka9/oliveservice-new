/**
 * Module Client - Administration Olive Service
 * Gestion DataTables, Statistiques KPI temps réel, Filtres rapides, Statuts & Réaffectation Commerciale
 */

$(function () {
  const racine = window.AppConfig ? window.AppConfig.racine : (window.RACINE || '/');
  const csrfToken = $('#csrf_token').val() || (window.AppConfig && window.AppConfig.csrfToken ? window.AppConfig.csrfToken : '');

  function notifyToast(msg, type) {
    if (typeof showToast === 'function') {
      showToast(msg, type);
    } else if (window.toastr && typeof window.toastr[type] === 'function') {
      window.toastr[type](msg);
    } else {
      alert(msg);
    }
  }

  // État du filtre rapide actif
  let currentFilter = 'all';

  // Ensemble des clients sélectionnés pour action groupée (Map: code_client -> nom_complet)
  const selectedClients = new Map();

  function updateBulkToolbar() {
    const count = selectedClients.size;
    const $toolbar = $('#bulk-actions-toolbar');
    if (count > 0) {
      $('#bulk-badge-count').text(count);
      $('#bulk-selected-count').text(count + ' client' + (count > 1 ? 's' : '') + ' sélectionné' + (count > 1 ? 's' : ''));
      $('#bulk-modal-count-text').text(count + ' client' + (count > 1 ? 's' : ''));
      $toolbar.css('display', 'flex');

      // Mettre à jour l'aperçu visuel des clients dans le modal
      const $preview = $('#bulk-selected-preview');
      $preview.empty();
      let shown = 0;
      selectedClients.forEach((nom, code) => {
        if (shown < 25) {
          $preview.append(`
            <span style="background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
              <i data-lucide="user" style="width: 10px; height: 10px;"></i> ${nom} <span style="font-size: 10px; color: #64748B; font-family: monospace;">(${code})</span>
            </span>
          `);
        }
        shown++;
      });
      if (shown > 25) {
        $preview.append(`
          <span style="background: #F1F5F9; color: #475569; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 6px;">
            + ${shown - 25} autre(s)...
          </span>
        `);
      }
      if (window.lucide) lucide.createIcons();
    } else {
      $toolbar.hide();
      $('#check-all-clients').prop('checked', false);
      $('#bulk-selected-preview').empty();
    }
  }

  // Filtre personnalisé pour DataTables
  $.fn.dataTable.ext.search.push(function (settings, data, dataIndex, rowData) {
    if (!settings || !settings.nTable || settings.nTable.id !== 'table-clients') return true;
    if (!rowData) return true;

    if (currentFilter === 'all') return true;
    if (currentFilter === 'actif') {
      return (rowData.statut_client === 'actif');
    }
    if (currentFilter === 'inactif') {
      return (rowData.statut_client === 'inactif');
    }
    if (currentFilter === 'souscripteur') {
      return (parseInt(rowData.nb_souscriptions, 10) > 0);
    }
    return true;
  });

  const $tableClients = $('#table-clients');
  if ($tableClients.length) {
    const table = $tableClients.DataTable({
      ajax: racine + 'client/apiList',
      processing: true,
      autoWidth: false,
      columns: [
        // 0. Checkbox Sélection
        {
          data: null,
          width: '45px',
          orderable: false,
          className: 'text-center',
          render: (d, type, row) => {
            const code = row.code_client || '';
            const isChecked = selectedClients.has(code) ? 'checked' : '';
            return `
              <div style="display: flex; align-items: center; justify-content: center;">
                <input type="checkbox" class="client-bulk-cb" value="${code}" data-nom="${row.nom_complet || code}" ${isChecked} style="cursor: pointer; width: 16px; height: 16px; accent-color: #1E3A5F;">
              </div>
            `;
          }
        },
        // 1. Code Client
        {
          data: 'code_client',
          width: '110px',
          render: (d) => d ? `<code class="client-code-badge">${d}</code>` : '-'
        },
        // 2. Abonné / Nom & Prénom + Initiales Avatar
        {
          data: 'nom_complet',
          render: (d, type, row) => {
            const inits = row.initiales || 'CL';
            const nom = row.nom_complet || '-';
            const dateCreation = row.date_creation && row.date_creation !== '-' 
              ? `<span style="font-size: 11px; color: #94A3B8; display: block;">Inscrit le ${row.date_creation}</span>` 
              : '';
            return `
              <div style="display: flex; align-items: center; gap: 10px;">
                <div class="client-avatar-mini" title="${nom}">${inits}</div>
                <div>
                  <strong style="color: #0F172A; font-size: 13.5px; display: block; line-height: 1.3;">${nom}</strong>
                  ${dateCreation}
                </div>
              </div>
            `;
          }
        },
        // 3. Contact Téléphonique
        {
          data: 'telephone_client',
          render: (d) => {
            if (!d) return '<span style="color: #94A3B8;">-</span>';
            return `
              <a href="tel:${d}" class="cli-tel-link" title="Appeler ce contact">
                <i data-lucide="phone" style="width: 13px; height: 13px; color: #2563EB;"></i>
                <span>${d}</span>
              </a>
            `;
          }
        },
        // 4. Numéro CNI
        {
          data: 'cni_client',
          render: (d, type, row) => {
            const cni = row.numero_cni || row.cni_client;
            if (!cni) return '<span style="color: #94A3B8;">-</span>';
            return `
              <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 600; background: #F8FAFC; border: 1px solid #E2E8F0; padding: 3px 8px; border-radius: 6px; color: #334155; font-family: monospace;">
                <i data-lucide="credit-card" style="width: 12px; height: 12px; color: #64748B;"></i>
                <span>${cni}</span>
              </span>
            `;
          }
        },
        // 5. Zone & Résidence
        {
          data: null,
          render: (d, type, row) => {
            const zone = row.libelle_zone 
              ? `<span class="badge" style="background: #EFF6FF; color: #1D4ED8; font-weight: 700; border: 1px solid #BFDBFE; font-size: 11px; padding: 3px 7px;">
                   <i data-lucide="map-pin" style="width: 11px; height: 11px; display: inline-block; vertical-align: -1px;"></i> ${row.libelle_zone}
                 </span>` 
              : '';
            const lieu = row.lieu_residence_client 
              ? `<div style="font-size: 12px; color: #64748B; margin-top: 3px; font-weight: 500;">${row.lieu_residence_client}</div>` 
              : '';
            return (zone || lieu) ? `${zone}${lieu}` : '<span style="color: #94A3B8;">-</span>';
          }
        },
        // 6. Commercial Référent Actif
        {
          data: 'nom_commercial',
          render: (d, type, row) => {
            const nom = d || 'Non assigné';
            const isAssigned = (nom !== 'Non assigné' && nom !== '-');
            const bg = isAssigned ? '#F0FDF4' : '#F8FAFC';
            const color = isAssigned ? '#166534' : '#94A3B8';
            const border = isAssigned ? '#BBF7D0' : '#E2E8F0';
            const tel = row.telephone_commercial ? `<span style="font-size: 11px; color: #64748B; display: block; margin-top: 2px;"><i data-lucide="phone" style="width: 10px; height: 10px; display: inline-block;"></i> ${row.telephone_commercial}</span>` : '';
            return `
              <div>
                <span class="badge" style="background: ${bg}; color: ${color}; border: 1px solid ${border}; font-weight: 700; font-size: 11.5px; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                  <i data-lucide="user-check" style="width: 12px; height: 12px;"></i>
                  <span>${nom}</span>
                </span>
                ${tel}
              </div>
            `;
          }
        },
        // 7. Souscription (nombre de souscriptions du client)
        {
          data: 'nb_souscriptions',
          className: 'text-center',
          render: (d) => {
            const count = parseInt(d, 10) || 0;
            if (count > 0) {
              return `
                <span class="badge" style="background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; font-weight: 800; font-size: 12px; padding: 4px 9px; display: inline-flex; align-items: center; gap: 4px;">
                  <i data-lucide="file-text" style="width: 13px; height: 13px; display: inline-block; vertical-align: -1px;"></i>
                  <span>${count} souscription${count > 1 ? 's' : ''}</span>
                </span>
              `;
            }
            return `
              <span class="badge" style="background: #F8FAFC; color: #94A3B8; border: 1px solid #E2E8F0; font-weight: 600; font-size: 11px; padding: 3px 8px; display: inline-flex; align-items: center; gap: 4px;">
                0 souscription
              </span>
            `;
          }
        },
        // 8. Statut (Toggle interactif si gestionnaire/admin)
        {
          data: 'statut_client',
          width: '110px',
          className: 'text-center',
          render: function (d, type, row) {
            const isActif = (d === 'actif');
            const canToggle = window.AppConfig && window.AppConfig.can ? window.AppConfig.can('GESTIONNAIRE_EDIT_CLIENT') : false;
            const isCommercial = window.AppConfig ? window.AppConfig.isCommercial : false;
            const checkedAttr = isActif ? 'checked' : '';

            if (canToggle && !isCommercial) {
              return `
                <div style="display: flex; justify-content: center; align-items: center; gap: 8px;">
                  <label style="position: relative; display: inline-block; width: 38px; height: 20px; margin: 0; cursor: pointer;" title="${isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer'}">
                    <input type="checkbox" class="toggle-statut-client" data-id="${row.id_client}" ${checkedAttr} style="opacity: 0; width: 0; height: 0;">
                    <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: ${isActif ? '#10B981' : '#CBD5E1'}; transition: .3s; border-radius: 20px;">
                      <span style="position: absolute; content: ''; height: 14px; width: 14px; left: ${isActif ? '20px' : '3px'}; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%;"></span>
                    </span>
                  </label>
                  <span class="badge ${isActif ? 'bg-success' : 'bg-secondary'}" style="font-size: 11px; padding: 3px 6px;">${isActif ? 'Actif' : 'Inactif'}</span>
                </div>
              `;
            }

            return `<span class="badge ${isActif ? 'bg-success' : 'bg-secondary'}" style="font-size: 11.5px; padding: 4px 8px;">${isActif ? 'Actif' : 'Inactif'}</span>`;
          }
        },
        // 9. Actions Directes
        {
          data: null,
          width: '130px',
          orderable: false,
          className: 'text-end',
          render: function (d) {
            const editId = d.editId || d.id_client;
            const canEdit = window.AppConfig && window.AppConfig.can ? window.AppConfig.can('GESTIONNAIRE_EDIT_CLIENT') : false;
            const canSous = window.AppConfig && window.AppConfig.can ? window.AppConfig.can('COMMERCIAL_ADD_SOUSCRIPTION') : false;

            const sousBtn = canSous ? `
              <a href="${racine}souscription/ressouscription?client_code=${encodeURIComponent(d.code_client || '')}" class="btn-action-icon success" title="Ressouscription">
                <i data-lucide="refresh-cw" style="width: 15px; height: 15px;"></i>
              </a>` : '';

            const editBtn = canEdit ? `
              <a href="${racine}client/edition/${editId}" class="btn-action-icon secondary" title="Modifier les informations du client">
                <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
              </a>` : '';

            return `
              <div class="cli-actions-wrap">
                ${sousBtn}
                ${editBtn}
                <a href="${racine}client/details/${editId}" class="btn-action-icon primary" title="Consulter la fiche complète">
                  <i data-lucide="eye" style="width: 15px; height: 15px;"></i>
                </a>
              </div>
            `;
          }
        }
      ],
      language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
      pageLength: 25,
      order: [],
      drawCallback: () => {
        // Restaurer l'état des cases à cocher après pagination / redraw
        $('.client-bulk-cb').each(function () {
          const code = $(this).val();
          $(this).prop('checked', selectedClients.has(code));
        });
        if (window.lucide) lucide.createIcons();
      }
    });

    // Mise à jour temps réel des cartes KPI (stats) et pilules de filtres
    function updateKpiCards(stats) {
      if (!stats) return;
      const formatNum = (v) => Number(v || 0).toLocaleString('fr-FR');
      const formatFCFA = (v) => Number(v || 0).toLocaleString('fr-FR') + ' <small style="font-size: 13px; font-weight: 700;">FCFA</small>';

      $('#kpi-total-clients').text(formatNum(stats.total_clients));
      $('#kpi-nb-actifs-mini').text(formatNum(stats.clients_actifs));
      $('#kpi-clients-actifs').text(formatNum(stats.clients_actifs));
      $('#kpi-taux-actifs').text((stats.taux_actifs || 0) + '%');
      $('#kpi-clients-souscripteurs').text(formatNum(stats.clients_souscripteurs));
      $('#kpi-taux-engagement').text((stats.taux_engagement || 0) + '%');
      $('#kpi-total-cotise').html(formatFCFA(stats.total_cotise));

      // Mise à jour des compteurs sur les boutons filtres
      $('#filter-count-all').text(formatNum(stats.total_clients));
      $('#filter-count-actif').text(formatNum(stats.clients_actifs));
      $('#filter-count-inactif').text(formatNum(stats.clients_inactifs));
      $('#filter-count-sous').text(formatNum(stats.clients_souscripteurs));

      if (window.lucide) lucide.createIcons();
    }

    // Écoute de l'événement Ajax XHR pour synchroniser les KPIs
    table.on('xhr', function () {
      const json = table.ajax.json();
      if (json && json.stats) {
        updateKpiCards(json.stats);
      }
    });

    // Filtres rapides au clic sur les pilules
    $('.btn-cli-filter').on('click', function () {
      $('.btn-cli-filter').removeClass('active');
      $(this).addClass('active');
      currentFilter = $(this).data('filter') || 'all';
      table.draw();
    });

    // Bouton d'actualisation manuelle
    $('#btn-reload-clients').on('click', function () {
      const $btn = $(this);
      const $icon = $btn.find('i, svg');

      $icon.css({ 'transition': 'transform 0.6s ease', 'transform': 'rotate(360deg)' });
      table.ajax.reload(function () {
        setTimeout(() => $icon.css({ 'transform': 'none' }), 600);
        notifyToast('Répertoire des clients actualisé avec succès', 'info');
      }, false);
    });

    // GESTION DE LA SÉLECTION MULTIPLE (BULK)
    $(document).on('change', '.client-bulk-cb', function () {
      const code = $(this).val();
      const nom = $(this).data('nom') || code;
      if ($(this).is(':checked')) {
        selectedClients.set(code, nom);
      } else {
        selectedClients.delete(code);
      }
      updateBulkToolbar();
    });

    $('#check-all-clients').on('change', function () {
      const isChecked = $(this).is(':checked');
      $('.client-bulk-cb').each(function () {
        const code = $(this).val();
        const nom = $(this).data('nom') || code;
        $(this).prop('checked', isChecked);
        if (isChecked) {
          selectedClients.set(code, nom);
        } else {
          selectedClients.delete(code);
        }
      });
      updateBulkToolbar();
    });

    $('#btn-uncheck-all').on('click', function () {
      selectedClients.clear();
      $('.client-bulk-cb').prop('checked', false);
      $('#check-all-clients').prop('checked', false);
      updateBulkToolbar();
    });

    // OUVERTURE & FERMETURE DE LA MODALE DE RÉAFFECTATION EN MASSE
    $('#btn-open-bulk-transfer').on('click', function (e) {
      e.preventDefault();
      if (selectedClients.size === 0) {
        if (window.Swal) {
          Swal.fire('Aucun client sélectionné', 'Veuillez cocher au moins un client à réaffecter.', 'warning');
        } else {
          alert('Veuillez cocher au moins un client.');
        }
        return;
      }
      const $modal = $('#modal-bulk-transfer');
      $modal.fadeIn(200).css('display', 'flex');
      if (window.lucide) lucide.createIcons();
    });

    $('#modal-bulk-close, #btn-cancel-bulk-transfer').on('click', function () {
      $('#modal-bulk-transfer').fadeOut(150);
    });

    $('#modal-bulk-transfer').on('click', function (e) {
      if ($(e.target).is('#modal-bulk-transfer')) {
        $(this).fadeOut(150);
      }
    });

    $(document).on('keydown', function (e) {
      if (e.key === 'Escape' && $('#modal-bulk-transfer').is(':visible')) {
        $('#modal-bulk-transfer').fadeOut(150);
      }
    });

    // Soumission du transfert en masse
    $('#form-bulk-transfer').on('submit', function (e) {
      e.preventDefault();
      const $form = $(this);
      const $btn = $('#btn-submit-bulk-transfer');
      const commercialCode = $('#select-bulk-commercial').val();
      const motif = $('#input-bulk-motif').val();

      if (selectedClients.size === 0) {
        if (window.Swal) {
          Swal.fire('Aucun client sélectionné', 'Veuillez cocher au moins un client à réaffecter.', 'warning');
        } else {
          alert('Veuillez cocher au moins un client.');
        }
        return;
      }

      if (!commercialCode) {
        if (window.Swal) {
          Swal.fire('Commercial requis', 'Veuillez sélectionner le commercial récepteur.', 'warning');
        } else {
          alert('Veuillez sélectionner un commercial.');
        }
        return;
      }

      $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Transfert groupé...');

      const formData = {
        csrf_token: $form.find('input[name="csrf_token"]').val(),
        commercial_code: commercialCode,
        motif: motif,
        client_codes: Array.from(selectedClients.keys())
      };

      $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: formData,
        dataType: 'json',
        success: function (res) {
          $btn.prop('disabled', false).html('<i data-lucide="check-check" style="width: 16px; height: 16px;"></i> Confirmer le Transfert Groupé');
          if (window.lucide) lucide.createIcons();

          if (res.status === 1 || res.status === 'success' || res.success) {
            $('#modal-bulk-transfer').fadeOut(150);
            selectedClients.clear();
            $('#check-all-clients').prop('checked', false);
            updateBulkToolbar();

            if (window.Swal) {
              Swal.fire({
                icon: 'success',
                title: 'Transfert groupé effectué !',
                text: res.message || 'Les clients ont été réaffectés avec succès.',
                confirmButtonColor: '#059669'
              });
            } else {
              notifyToast(res.message || 'Transfert groupé effectué', 'success');
            }
            table.ajax.reload(null, false);
          } else {
            if (window.Swal) {
              Swal.fire('Erreur', res.message || 'Erreur lors du transfert.', 'error');
            } else {
              notifyToast(res.message || 'Erreur lors du transfert', 'error');
            }
          }
        },
        error: function (xhr) {
          $btn.prop('disabled', false).html('<i data-lucide="check-check" style="width: 16px; height: 16px;"></i> Confirmer le Transfert Groupé');
          if (window.lucide) lucide.createIcons();
          const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Une erreur de communication est survenue.';
          if (window.Swal) {
            Swal.fire('Erreur serveur', msg, 'error');
          } else {
            notifyToast(msg, 'error');
          }
        }
      });
    });

    // Basculement de statut via AJAX
    $(document).on('change', '.toggle-statut-client', function () {
      const id = $(this).data('id');
      const isChecked = $(this).is(':checked');
      const $input = $(this);

      $.ajax({
        url: racine + 'client/changer',
        type: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: { id: id, csrf_token: csrfToken },
        dataType: 'json',
        success: function (res) {
          if (res.status === 1 || res.success) {
            notifyToast(res.message || 'Statut client mis à jour avec succès', 'success');
            table.ajax.reload(null, false);
          } else {
            notifyToast(res.message || 'Erreur lors de la mise à jour du statut', 'error');
            $input.prop('checked', !isChecked);
          }
        },
        error: function () {
          notifyToast('Erreur réseau lors de la communication serveur', 'error');
          $input.prop('checked', !isChecked);
        }
      });
    });
  }
});
