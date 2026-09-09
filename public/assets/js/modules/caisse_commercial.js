/**
 * Module Caisse Commercial - Administration Olive Service / GEICG
 * Registre des caisses journalières pour commerciaux et validation comptable
 */

$(function() {
  const racine = window.AppConfig ? window.AppConfig.racine : (window.RACINE || '/');
  const csrfToken = $('#csrf_token').val() || '';
  const isCommercial = window.AppConfig ? window.AppConfig.isCommercial : false;

  // --- PARTIE 1 : HISTORIQUE DES CAISSES (LISTE) ---
  const $tableClo = $('#table-clotures_caisse');
  if ($tableClo.length) {
    const table = $tableClo.DataTable({
      ajax: racine + 'caisse_commercial/apiList',
      processing: true,
      autoWidth: false,
      columns: [
        { data: 'id_cloture', defaultContent: '-', width: '50px' },
        { 
          data: 'code_cloture', 
          width: '140px',
          render: (d, type) => {
            if (type !== 'display') return d || '';
            return `<code style="font-weight:700; color:#1E3A5F; background:#F1F5F9; padding:4px 8px; border-radius:6px; font-size:12px; border:1px solid #CBD5E1;">${d || '-'}</code>`;
          } 
        },
        { data: 'date_cloture', defaultContent: '-', render: (d, type) => {
          if (type !== 'display') return d || '';
          return `<span style="color:#334155; font-weight:600;">${d || '-'}</span>`;
        }},
        { data: 'nom_auteur_complet', defaultContent: '-', render: (d, type) => {
          if (type !== 'display') return d || '';
          return `<strong style="color:#0F172A; font-weight:800;">${d || '-'}</strong>`;
        }},
        { 
          data: 'total_especes', 
          render: (d, type) => {
            if (type !== 'display') return d || 0;
            return `<span style="color:#1E3A5F; font-weight:700;">${d ? Number(d).toLocaleString('fr-FR') + ' F' : '0 F'}</span>`;
          } 
        },
        { 
          data: 'total_mobile_money', 
          render: (d, type) => {
            if (type !== 'display') return d || 0;
            return `<span style="color:#7E22CE; font-weight:700;">${d ? Number(d).toLocaleString('fr-FR') + ' F' : '0 F'}</span>`;
          } 
        },
        { 
          data: 'total_cheque_virement', 
          render: (d, type) => {
            if (type !== 'display') return d || 0;
            return `<span style="color:#0284C7; font-weight:700;">${d ? Number(d).toLocaleString('fr-FR') + ' F' : '0 F'}</span>`;
          } 
        },
        { 
          data: 'total_general', 
          render: (d, type) => {
            if (type !== 'display') return d || 0;
            return `<strong style="color:#059669; font-size:14px; font-weight:800;">${d ? Number(d).toLocaleString('fr-FR') + ' FCFA' : '0 FCFA'}</strong>`;
          } 
        },
        { 
          data: 'statut_cloture', 
          width: '140px', 
          className: 'text-center', 
          render: function(d, type, row) {
            if (type !== 'display') return d || 'attente';
            const val = d || 'attente';
            const bgColors = { 'valide': '#ECFDF5', 'attente': '#FEF3C7', 'rejete': '#FEE2E2' };
            const textColors = { 'valide': '#059669', 'attente': '#D97706', 'rejete': '#DC2626' };
            const borderColors = { 'valide': '#A7F3D0', 'attente': '#FDE68A', 'rejete': '#FECACA' };
            const currentBg = bgColors[val] || '#F1F5F9';
            const currentText = textColors[val] || '#334155';
            const currentBorder = borderColors[val] || '#CBD5E1';

            if (isCommercial) {
              const labels = { 'valide': 'Validée', 'attente': 'En attente', 'rejete': 'Rejetée' };
              return `<span class="badge" style="background:${currentBg}; color:${currentText}; border:1px solid ${currentBorder}; padding:6px 12px; font-weight:800; border-radius:20px; font-size:11px;">${labels[val] || val}</span>`;
            }

            return `
              <select class="select-statut-cloture" data-id="${row.id_cloture}" style="background:${currentBg}; color:${currentText}; border:1px solid ${currentBorder}; font-weight:800; font-size:12px; border-radius:10px; padding:6px 10px; cursor:pointer; outline:none;">
                <option value="attente" ${val === 'attente' ? 'selected' : ''} style="background:#fff; color:#D97706;">En attente</option>
                <option value="valide" ${val === 'valide' ? 'selected' : ''} style="background:#fff; color:#059669;">Validée</option>
                <option value="rejete" ${val === 'rejete' ? 'selected' : ''} style="background:#fff; color:#DC2626;">Rejetée</option>
              </select>`;
          } 
        },
        { 
          data: null, 
          width: '100px', 
          orderable: false, 
          className: 'text-end',
          render: function(d) {
            const editId = d.editId || d.id_cloture;
            return `
              <a href="${racine}caisse_commercial/details/${editId}" class="btn" style="background:#1E3A5F; color:#FFFFFF; font-weight:700; border-radius:8px; padding:6px 12px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-size:12px;" title="Voir détails"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>
            `;
          } 
        }
      ],
      language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
      drawCallback: () => {
        if (window.lucide) lucide.createIcons();
      }
    });

    $(document).on('change', '.select-statut-cloture', function() {
      const id = $(this).data('id');
      const newStatut = $(this).val();

      $.ajax({
        url: racine + 'caisse_commercial/changer',
        type: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: {
          id: id,
          statut: newStatut,
          csrf_token: csrfToken
        },
        dataType: 'json',
        success: function(res) {
          if (res.status === 1 || res.success) {
            if (window.toastr) toastr.success(res.message || 'Statut mis à jour avec succès');
            table.ajax.reload(null, false);
          } else {
            if (window.toastr) toastr.error(res.message || 'Erreur lors du changement de statut');
            table.ajax.reload(null, false);
          }
        },
        error: function() {
          if (window.toastr) toastr.error('Erreur réseau');
          table.ajax.reload(null, false);
        }
      });
    });
  }

  // --- PARTIE 2 : TABLEAU DE BORD COMMERCIAL & CLÔTURE (EDIT) ---
  const $caisseLoader = $('#caisse-loader');
  if ($caisseLoader.length) {
    let currentCotisations = [];
    let activeTotalAttendu = 0;
    const $modalCotisations = $('#modalCotisationsDetails');

    function loadCommercialSession() {
      $caisseLoader.show();
      $('#section-caisse-ouverte').hide();
      $('#section-caisse-fermee').hide();

      $.ajax({
        url: racine + 'caisse_commercial/apiGetCommercialSession',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
          $caisseLoader.hide();

          if (res.status === 1) {
            if (res.has_active_session) {
              // CAISSE OUVERTE
              const s = res.session;
              currentCotisations = s.cotisations || [];
              activeTotalAttendu = Number(s.total_general || 0);

              $('#open_session_code').text(s.code_caisse);
              $('#open_session_date').text(s.date_caisse);
              $('#open_session_heure').text(s.heure_ouverture || '-');

              $('#open_total_general').text(s.total_general_fmt);
              $('#open_nb_cotisations').text(s.nb_cotisations);
              $('#open_fond_initial').text(s.fond_initial_fmt);

              $('#open_especes').text(Number(s.total_especes).toLocaleString('fr-FR') + ' FCFA');
              $('#open_mobile').text(Number(s.total_mobile_money).toLocaleString('fr-FR') + ' FCFA');
              $('#open_cheques').text(Number(s.total_cheque_virement).toLocaleString('fr-FR') + ' FCFA');

              // Init pot controls
              $('#cloture_montant_attendu_hidden').val(activeTotalAttendu);
              $('#pot_montant_attendu_txt').text(Number(activeTotalAttendu).toLocaleString('fr-FR') + ' FCFA');
              $('#cloture_montant_pot').val('');
              verifierConformitePot();

              renderCotisationsTable(currentCotisations);
              $('#section-caisse-ouverte').fadeIn(200);
            } else {
              // CAISSE FERMÉE
              if (res.last_cloture) {
                const l = res.last_cloture;
                currentCotisations = l.cotisations || [];
                renderCotisationsTable(currentCotisations);

                $('#last_code_lbl').text(l.code_cloture);
                $('#last_date_lbl').text(l.date_cloture);
                $('#last_total_lbl').text(l.total_general_fmt);

                let badgeStatut = '';
                if (l.statut_cloture === 'valide') {
                  badgeStatut = '<span style="background: #ECFDF5; color: #059669; font-weight: 800; font-size: 12px; padding: 6px 14px; border-radius: 20px; border: 1px solid #A7F3D0;">Validée par Finance</span>';
                } else if (l.statut_cloture === 'rejete') {
                  badgeStatut = '<span style="background: #FEE2E2; color: #DC2626; font-weight: 800; font-size: 12px; padding: 6px 14px; border-radius: 20px; border: 1px solid #FECACA;">Rejetée</span>';
                } else {
                  badgeStatut = '<span style="background: #FEF3C7; color: #D97706; font-weight: 800; font-size: 12px; padding: 6px 14px; border-radius: 20px; border: 1px solid #FDE68A;">En attente de validation</span>';
                }
                $('#last_statut_lbl').html(badgeStatut);

                $('#card-dernier-bilan').show();
              }

              $('#section-caisse-fermee').fadeIn(200);
            }

            if (window.lucide) lucide.createIcons();
          }
        },
        error: function() {
          $caisseLoader.hide();
          $('#section-caisse-fermee').show();
          if (window.toastr) toastr.error('Erreur réseau lors de la récupération de votre session de caisse.');
        }
      });
    }

    // Fonction de vérification dynamique du Montant du Pot avec blocage strict
    function verifierConformitePot() {
      const valRaw = $('#cloture_montant_pot').val();
      if (valRaw === '' || valRaw === null) {
        $('#pot_montant_declare_txt').text('0 FCFA');
        $('#pot_ecart_txt').text('0 FCFA').css('color', '#64748B');
        $('#pot_ecart_badge_sub').text('En attente').css('color', '#64748B');
        $('#pot_status_alert').css({
          'background': '#F1F5F9',
          'border': '1px solid #CBD5E1',
          'color': '#475569'
        }).html('<i data-lucide="info" style="width: 18px; height: 18px; color: #0284C7; flex-shrink: 0;"></i><span>Veuillez renseigner le montant de votre pot pour vérifier la conformité avec la recette de caisse.</span>');
        
        $('#btn-submit-cloture').prop('disabled', true).css({
          'background': '#94A3B8',
          'cursor': 'not-allowed',
          'box-shadow': 'none'
        });
        $('#txt-bloque-avertissement').hide();
        if (window.lucide) lucide.createIcons();
        return;
      }

      const montantPot = parseFloat(valRaw) || 0;
      const ecart = Math.round(montantPot - activeTotalAttendu);
      $('#pot_montant_declare_txt').text(montantPot.toLocaleString('fr-FR') + ' FCFA');

      if (ecart === 0) {
        // Conforme : Débloquer le bouton
        $('#pot_ecart_txt').text('0 FCFA').css('color', '#059669');
        $('#pot_ecart_badge_sub').text('Conforme (0 F)').css('color', '#059669');
        $('#pot_status_alert').css({
          'background': '#ECFDF5',
          'border': '1.5px solid #A7F3D0',
          'color': '#065F46'
        }).html('<i data-lucide="check-circle" style="width: 18px; height: 18px; color: #059669; flex-shrink: 0;"></i><span><strong>Montant conforme !</strong> Le montant du pot correspond exactement aux encaissements enregistrés (' + activeTotalAttendu.toLocaleString('fr-FR') + ' FCFA). Vous pouvez valider la clôture.</span>');

        $('#btn-submit-cloture').prop('disabled', false).css({
          'background': 'linear-gradient(135deg, #059669 0%, #047857 100%)',
          'cursor': 'pointer',
          'box-shadow': '0 4px 12px rgba(5, 150, 105, 0.25)'
        });
        $('#txt-bloque-avertissement').hide();
      } else if (ecart < 0) {
        // Manquant : Bloquer strictement
        const manquant = Math.abs(ecart);
        $('#pot_ecart_txt').text('-' + manquant.toLocaleString('fr-FR') + ' FCFA').css('color', '#DC2626');
        $('#pot_ecart_badge_sub').text('Manquant').css('color', '#DC2626');
        $('#pot_status_alert').css({
          'background': '#FEF2F2',
          'border': '1.5px solid #FECACA',
          'color': '#991B1B'
        }).html('<i data-lucide="alert-triangle" style="width: 18px; height: 18px; color: #DC2626; flex-shrink: 0;"></i><span><strong>Écart détecté (-' + manquant.toLocaleString('fr-FR') + ' FCFA) :</strong> Le pot est inférieur à la recette attendue. La clôture est <u>strictement bloquée</u> jusqu\'à régularisation.</span>');

        $('#btn-submit-cloture').prop('disabled', true).css({
          'background': '#94A3B8',
          'cursor': 'not-allowed',
          'box-shadow': 'none'
        });
        $('#txt-bloque-avertissement').show().text('⚠️ Bouton verrouillé : Un manquant de ' + manquant.toLocaleString('fr-FR') + ' FCFA est détecté. Clôture interdite.');
      } else {
        // Surplus : Bloquer strictement
        const surplus = ecart;
        $('#pot_ecart_txt').text('+' + surplus.toLocaleString('fr-FR') + ' FCFA').css('color', '#2563EB');
        $('#pot_ecart_badge_sub').text('Surplus').css('color', '#2563EB');
        $('#pot_status_alert').css({
          'background': '#EFF6FF',
          'border': '1.5px solid #BFDBFE',
          'color': '#1E40AF'
        }).html('<i data-lucide="alert-circle" style="width: 18px; height: 18px; color: #2563EB; flex-shrink: 0;"></i><span><strong>Écart détecté (+' + surplus.toLocaleString('fr-FR') + ' FCFA) :</strong> Le pot est supérieur à la recette enregistrée. La clôture est <u>strictement bloquée</u>.</span>');

        $('#btn-submit-cloture').prop('disabled', true).css({
          'background': '#94A3B8',
          'cursor': 'not-allowed',
          'box-shadow': 'none'
        });
        $('#txt-bloque-avertissement').show().text('⚠️ Bouton verrouillé : Un surplus de ' + surplus.toLocaleString('fr-FR') + ' FCFA est détecté. Clôture interdite.');
      }

      if (window.lucide) lucide.createIcons();
    }

    $(document).on('input keyup change', '#cloture_montant_pot', verifierConformitePot);

    function renderCotisationsTable(list) {
      const $tbody = $('#tbl_cotisations_body');
      $tbody.empty();

      if (!list || list.length === 0) {
        $tbody.html('<tr><td colspan="7" style="text-align: center; color: #94A3B8; padding: 26px; font-weight: 600;">Aucune cotisation enregistrée dans cette caisse.</td></tr>');
        return;
      }

      list.forEach(function(item) {
        const isValide = (item.statut === 'valide');
        const badge = isValide 
          ? '<span style="background: #ECFDF5; color: #059669; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; border: 1px solid #A7F3D0;">Validée</span>'
          : '<span style="background: #FEF3C7; color: #D97706; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; border: 1px solid #FDE68A;">En attente</span>';

        const modeBadge = `<span style="background: #F1F5F9; color: #1E3A5F; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 6px; border: 1px solid #CBD5E1;">${item.mode_paiement || 'ESPECES'}</span>`;

        $tbody.append(`
          <tr style="border-bottom: 1px solid #F1F5F9;">
            <td style="padding: 12px 14px;"><code style="font-weight:700; color:#1E3A5F; font-size: 12px; background:#F1F5F9; padding:2px 6px; border-radius:4px;">${item.code_souscription || '-'}</code></td>
            <td style="padding: 12px 14px;"><strong style="color: #0F172A; font-size: 13px; font-weight:800;">${item.nom_client || 'Client'}</strong><br><small style="color:#64748B;">${item.telephone_client || '-'}</small></td>
            <td style="padding: 12px 14px;"><strong style="color:#059669; font-size: 13px; font-weight:800;">${item.montant_fmt || '0 FCFA'}</strong></td>
            <td style="padding: 12px 14px;">${modeBadge}</td>
            <td style="padding: 12px 14px; color: #475569; font-weight:600;">${item.date_cautisation || '-'}</td>
            <td style="padding: 12px 14px;"><strong style="color:#0F172A;">${item.date_prochain_rdv || '-'}</strong></td>
            <td style="padding: 12px 14px;">${badge}</td>
          </tr>
        `);
      });
    }

    // Action Ouverture de Caisse
    $('#form-ouvrir-caisse').on('submit', function(e) {
      e.preventDefault();
      const formData = $(this).serialize();

      $.ajax({
        url: racine + 'caisse_commercial/ouvrirMaCaisse',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(res) {
          if (res.status === 1 || res.success) {
            if (window.toastr) toastr.success(res.message || 'Caisse ouverte avec succès !');
            loadCommercialSession();
          } else {
            if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'ouverture');
          }
        },
        error: function() {
          if (window.toastr) toastr.error('Erreur réseau');
        }
      });
    });

    // Action Clôture de Caisse avec vérification stricte
    $('#form-cloturer-caisse').on('submit', function(e) {
      e.preventDefault();

      const valRaw = $('#cloture_montant_pot').val();
      if (valRaw === '' || valRaw === null) {
        if (window.toastr) toastr.warning('Veuillez saisir le montant de votre pot.');
        return;
      }

      const montantPot = parseFloat(valRaw) || 0;
      if (Math.round(montantPot - activeTotalAttendu) !== 0) {
        if (window.toastr) toastr.error('Clôture strictement bloquée : Le montant du pot ne correspond pas au montant attendu.');
        return;
      }

      const formData = $(this).serialize();

      $.ajax({
        url: racine + 'caisse_commercial/add',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(res) {
          if (res.status === 1 || res.success) {
            if (window.toastr) toastr.success(res.message || 'Clôture et versement transmis avec succès !');
            loadCommercialSession();
          } else {
            if (window.toastr) toastr.error(res.message || 'Erreur lors de la clôture');
          }
        },
        error: function() {
          if (window.toastr) toastr.error('Erreur réseau');
        }
      });
    });

    // GESTION DU DÉCLENCHEMENT DE LA MODALE
    $(document).on('click', '.btn-open-modal-details', function(e) {
      e.preventDefault();
      $modalCotisations.addClass('active');
      if (window.lucide) lucide.createIcons();
    });

    $('#modalCotisationsClose, #btn-close-modal-cotisations').on('click', function(e) {
      e.preventDefault();
      $modalCotisations.removeClass('active');
    });

    $modalCotisations.on('click', function(e) {
      if ($(e.target).is('#modalCotisationsDetails')) {
        $modalCotisations.removeClass('active');
      }
    });

    // Charge la session initiale
    loadCommercialSession();
  }
});
