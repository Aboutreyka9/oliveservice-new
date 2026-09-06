/**
 * Module Client - Administration Olive Service
 * Gestion DataTables, Statistiques KPI temps réel, Filtres rapides & Statuts
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
        // 0. N° Index
        {
          data: null,
          width: '45px',
          className: 'text-muted fw-bold text-center',
          render: (d, type, row, meta) => meta.row + 1
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
        // 6. Souscriptions Packs
        {
          data: 'nb_souscriptions',
          className: 'text-center',
          render: (d) => {
            const count = parseInt(d, 10) || 0;
            if (count > 0) {
              return `
                <span class="badge" style="background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; font-weight: 800; font-size: 12px; padding: 4px 9px;">
                  <i data-lucide="package-check" style="width: 13px; height: 13px; display: inline-block; vertical-align: -1px;"></i>
                  <span>${count} pack${count > 1 ? 's' : ''}</span>
                </span>
              `;
            }
            return `
              <span class="badge" style="background: #F8FAFC; color: #94A3B8; border: 1px solid #E2E8F0; font-weight: 600; font-size: 11px; padding: 3px 8px;">
                0 pack
              </span>
            `;
          }
        },
        // 7. Cumul Cotisé
        {
          data: 'total_cotise',
          className: 'text-end',
          render: (d) => {
            const val = parseFloat(d) || 0;
            if (val > 0) {
              return `<strong style="color: #059669; font-size: 13.5px; font-weight: 800;">${Number(val).toLocaleString('fr-FR')} <small style="font-size: 11px; font-weight: 700;">FCFA</small></strong>`;
            }
            return '<span style="color: #94A3B8; font-weight: 600; font-size: 12px;">0 FCFA</span>';
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
              <a href="${racine}souscription/wizard" class="btn-action-icon success" title="Nouvelle Souscription">
                <i data-lucide="plus-circle" style="width: 15px; height: 15px;"></i>
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
