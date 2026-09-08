$(document).ready(function() {
    const baseApi = (typeof LINK !== 'undefined') ? LINK : ((typeof RACINE !== 'undefined') ? RACINE : '/');

    if ($('#dataTable').length) {
        const columns = [
            { 
                title: 'N°', 
                data: null, 
                render: function(data, type, row, meta) { return meta.row + 1; } 
            },
            { 
                data: 'type', 
                title: 'Type',
                render: function(data) {
                    let icon = 'bell';
                    let bg = '#EFF6FF';
                    let color = '#2563EB';
                    let label = 'Notification';

                    if (data === 'cotisation') {
                        icon = 'coins';
                        bg = '#ECFDF5';
                        color = '#059669';
                        label = 'Cotisation';
                    } else if (data === 'versement') {
                        icon = 'arrow-up-right';
                        bg = '#EFF6FF';
                        color = '#2563EB';
                        label = 'Versement';
                    } else if (data === 'souscription') {
                        icon = 'sparkles';
                        bg = '#FEF3C7';
                        color = '#D97706';
                        label = 'Souscription';
                    } else if (data === 'systeme') {
                        icon = 'info';
                        bg = '#F1F5F9';
                        color = '#475569';
                        label = 'Système';
                    }

                    return `
                        <span style="background:${bg}; color:${color}; border:1px solid ${color}30; font-size:11px; font-weight:700; padding:4px 9px; border-radius:6px; display:inline-flex; align-items:center; gap:5px;">
                            <i data-lucide="${icon}" style="width:14px; height:14px;"></i> ${label}
                        </span>
                    `;
                }
            },
            { 
                data: 'destinataire', 
                title: 'Cible',
                render: function(data) {
                    return `
                        <div>
                            <strong style="color:#1E293B; font-size:12px;">${data || 'Tous'}</strong>
                        </div>
                    `;
                }
            },
            { 
                data: 'titre', 
                title: 'Titre & Message',
                render: function(data, type, row) {
                    const isUnread = (parseInt(row.lu) === 0);
                    const dot = isUnread ? '<span style="width:8px; height:8px; border-radius:50%; background:#059669; display:inline-block; margin-right:6px; flex-shrink:0;" title="Non lue"></span>' : '';
                    const linkOpen = row.url ? `<a href="${row.url}" style="text-decoration:none; color:inherit;">` : '<div>';
                    const linkClose = row.url ? `</a>` : '</div>';

                    return `
                        ${linkOpen}
                        <div style="max-width:380px;">
                            <strong style="color:${isUnread ? '#1E293B' : '#475569'}; font-size:13px; display:flex; align-items:center;">
                                ${dot}${data || ''}
                            </strong>
                            <p style="color:#64748B; font-size:12px; margin:2px 0 0 0; line-height:1.35;">
                                ${row.message || ''}
                            </p>
                        </div>
                        ${linkClose}
                    `;
                }
            },
            { 
                data: 'reference', 
                title: 'Réf. Associée',
                render: function(data, type, row) {
                    if (!data) return '<span style="color:#94A3B8;">-</span>';
                    if (row.url) {
                        return `<a href="${row.url}" class="badge" style="background:#F1F5F9; color:#1E3A5F; font-size:11px; font-weight:700; border:1px solid #CBD5E1; text-decoration:none; padding:3px 7px; border-radius:5px;">${data} <i data-lucide="external-link" style="width:11px; height:11px; vertical-align:middle;"></i></a>`;
                    }
                    return `<span class="badge" style="background:#F1F5F9; color:#1E3A5F; font-size:11px; font-weight:700; border:1px solid #CBD5E1; padding:3px 7px; border-radius:5px;">${data}</span>`;
                }
            },
            { 
                data: 'created_at', 
                title: 'Date & Heure',
                render: function(data) {
                    return `<span style="color:#64748B; font-size:12px;"><i data-lucide="clock" style="width:13px; height:13px; vertical-align:middle; margin-right:3px;"></i>${data || '-'}</span>`;
                }
            },
            { 
                data: 'lu', 
                title: 'État',
                render: function(data) {
                    const isLu = (parseInt(data) === 1);
                    if (isLu) {
                        return '<span class="badge" style="background:#ECFDF5; color:#059669; border:1px solid #A7F3D0; font-size:11px; font-weight:700; padding:3px 8px; border-radius:6px;"><i data-lucide="check" style="width:12px; height:12px; vertical-align:middle;"></i> Lue</span>';
                    } else {
                        return '<span class="badge" style="background:#FEF3C7; color:#D97706; border:1px solid #FDE68A; font-size:11px; font-weight:700; padding:3px 8px; border-radius:6px;"><i data-lucide="bell" style="width:12px; height:12px; vertical-align:middle;"></i> Non lue</span>';
                    }
                }
            },
            {
                data: null,
                title: 'Actions',
                className: 'text-center',
                render: function(data, type, row) {
                    const isLu = (parseInt(row.lu) === 1);
                    return `
                        <div style="display:inline-flex; gap:6px; align-items:center;">
                            <button type="button" title="${isLu ? 'Marquer comme non lue' : 'Marquer comme lue'}"
                                data-id="${row.id}"
                                class="btn-action ${isLu ? 'btn-action-secondary' : 'btn-action-primary'} toggleReadStatus"
                                style="width:30px; height:30px; border-radius:6px; border:1px solid #E2E8F0; background:#FFF; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;">
                                <i data-lucide="${isLu ? 'mail' : 'check'}" style="width:14px; height:14px; color:${isLu ? '#64748B' : '#059669'};"></i>
                            </button>
                            <button type="button" title="Supprimer"
                                data-id="${row.id}"
                                class="btn-action deleteNotifBtn"
                                style="width:30px; height:30px; border-radius:6px; border:1px solid #FEE2E2; background:#FEF2F2; color:#DC2626; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;">
                                <i data-lucide="trash-2" style="width:14px; height:14px;"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ];

        const table = initDataTable('dataTable', 'notification/apiList', columns);

        // Re-render Lucide icons after DataTables draw
        $('#dataTable').on('draw.dt', function () {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        // Toggle Read Status
        $(document).on('click', '.toggleReadStatus', function() {
            const id = $(this).attr('data-id');
            if (!id) return;

            $.post(baseApi + 'notification/changer', { id: id }, function(rep) {
                if (rep.status) {
                    if (typeof showToast === 'function') showToast(rep.message || 'Statut mis à jour', 'success');
                    table.ajax.reload(null, false);
                    reloadStats();
                } else {
                    if (typeof showToast === 'function') showToast(rep.message || 'Erreur', 'error');
                }
            }, 'json').fail(function() {
                if (typeof showToast === 'function') showToast('Erreur serveur', 'error');
            });
        });

        // Delete Notification
        $(document).on('click', '.deleteNotifBtn', function() {
            const id = $(this).attr('data-id');
            if (!id) return;

            showConfirm('Voulez-vous vraiment supprimer cette notification ?', function() {
                $.post(baseApi + 'notification/delete', { id: id }, function(rep) {
                    if (rep.status) {
                        if (typeof showToast === 'function') showToast(rep.message || 'Notification supprimée', 'success');
                        table.ajax.reload(null, false);
                        reloadStats();
                    } else {
                        if (typeof showToast === 'function') showToast(rep.message || 'Erreur', 'error');
                    }
                }, 'json').fail(function() {
                    if (typeof showToast === 'function') showToast('Erreur serveur', 'error');
                });
            }, 'Suppression', 'Supprimer', true);
        });

        // Mobile list config
        const notifsMobileConfig = {
            entity: 'notification',
            primary: [{ key: 'titre', label: 'Titre' }, { key: 'destinataire', label: 'Cible' }],
            secondary: [{ key: 'type', label: 'Type' }, { key: 'created_at', label: 'Date' }],
            actions: [
                {
                    id: 'toggle',
                    label: 'Changer état',
                    icon: 'check',
                    onClick: function(rowData) {
                        $.post(baseApi + 'notification/changer', { id: rowData.id }, function(rep) {
                            if (typeof showToast === 'function') showToast(rep.message, 'success');
                            table.ajax.reload(null, false);
                            reloadStats();
                        }, 'json');
                    }
                }
            ]
        };
        renderMobileCards('dataTable', notifsMobileConfig);
    }

    // Formulaire d'envoi de notification (Admin)
    $('#formSendNotification').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('button[type="submit"]');

        if (typeof loading === 'function') loading(btn, true, 'Envoi en cours...');

        $.ajax({
            url: baseApi + 'notification/add',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(rep) {
                if (typeof loading === 'function') loading(btn, false);
                if (rep.status) {
                    if (typeof showToast === 'function') showToast(rep.message || 'Notification diffusée avec succès !', 'success');
                    closeSendModal();
                    form[0].reset();
                    if ($('#dataTable').length) {
                        $('#dataTable').DataTable().ajax.reload(null, false);
                    }
                    reloadStats();
                } else {
                    if (typeof showToast === 'function') showToast(rep.message || 'Erreur lors de l\'envoi', 'error');
                }
            },
            error: function(xhr) {
                if (typeof loading === 'function') loading(btn, false);
                let msg = 'Erreur lors de l\'envoi';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                if (typeof showToast === 'function') showToast(msg, 'error');
            }
        });
    });

    // Fonction recharger les statistiques KPI
    function reloadStats() {
        $.get(baseApi + 'notification/stats', function(data) {
            if (data) {
                $('#kpi-total').text(data.total || 0);
                $('#kpi-nonlues').text(data.non_lues || 0);
                $('#kpi-lues').text(data.lues || 0);
                $('#kpi-cotisations').text(data.cotisations || 0);
                $('#kpi-versements').text(data.versements || 0);
                $('#kpi-souscriptions').text(data.souscriptions || 0);
            }
        }, 'json');
    }
});

// Fonctions globales
function openSendModal() {
    $('#modalSendNotification').css('display', 'flex');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeSendModal() {
    $('#modalSendNotification').hide();
}

function markAllNotificationsRead() {
    const baseApi = (typeof LINK !== 'undefined') ? LINK : ((typeof RACINE !== 'undefined') ? RACINE : '/');
    showConfirm('Voulez-vous marquer toutes vos notifications comme lues ?', function() {
        $.post(baseApi + 'notification/marquerToutLu', {}, function(rep) {
            if (rep.status) {
                if (typeof showToast === 'function') showToast(rep.message || 'Toutes les notifications sont marquées comme lues', 'success');
                if ($('#dataTable').length) {
                    $('#dataTable').DataTable().ajax.reload(null, false);
                }
                const badge = document.getElementById('navNotifBadge');
                if (badge) badge.remove();
                const tag = document.getElementById('navUnreadTag');
                if (tag) tag.remove();
                const markAllBtn = document.getElementById('navMarkAllBtn');
                if (markAllBtn) markAllBtn.remove();

                $.get(baseApi + 'notification/stats', function(data) {
                    if (data) {
                        $('#kpi-nonlues').text(data.non_lues || 0);
                        $('#kpi-lues').text(data.lues || 0);
                    }
                }, 'json');
            } else {
                if (typeof showToast === 'function') showToast(rep.message || 'Erreur', 'error');
            }
        }, 'json').fail(function() {
            if (typeof showToast === 'function') showToast('Erreur serveur', 'error');
        });
    }, 'Notifications', 'Tout marquer comme lu', false);
}
