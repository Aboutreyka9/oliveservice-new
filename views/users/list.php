<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
  $userRoles = $_SESSION[USERS_AUTH]['roles'] ?? [];
  if (empty($userRoles)) {
      $singleRole = $_SESSION[USERS_AUTH]['role_code'] ?? ($_SESSION['role_code'] ?? '');
      $userRoles = !empty($singleRole) ? [$singleRole] : [];
  }
  $isSuperAdminUser = in_array('ROLE_SUPERADMIN', $userRoles, true);
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="users" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Liste des Commerciaux & Agents Terrain
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Registre central et gestion exclusive de l'équipe commerciale
            </p>
          </div>
        </div>

        <?php if (Context::can('ADMIN_MANAGE_USERS') || Context::can('GESTIONNAIRE_MANAGE_PACKS')): ?>
        <a href="<?= RACINE ?>user/formulaire" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 12px 22px; font-size: 14px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); text-decoration: none; cursor: pointer;">
          <i data-lucide="user-plus" style="width: 18px; height: 18px;"></i> Recruter un Commercial
        </a>
        <?php endif; ?>
      </div>

      <!-- BARRE DE FILTRAGE PAR ZONE -->
      <div style="background: #FFFFFF; border-radius: 14px; padding: 16px 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.03); margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
          <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: #1E293B; font-size: 13px;">
            <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(5, 150, 105, 0.1); color: #059669; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="map-pin" style="width: 17px; height: 17px;"></i>
            </div>
            <span>Zone Commerciale :</span>
          </div>

          <div style="min-width: 260px;">
            <?php if (!empty($hasJoker)): ?>
              <select id="filter-zone" class="form-control" style="border: 1.5px solid #CBD5E1; border-radius: 8px; padding: 8px 12px; font-size: 13px; font-weight: 600; color: #1E293B; background: #FFF; width: 100%; cursor: pointer;">
                <option value="">Toutes les zones (Affichage Global)</option>
                <?php if (!empty($zones)): ?>
                  <?php foreach ($zones as $z): ?>
                    <option value="<?= htmlspecialchars($z['code_zone']) ?>">
                      <?= htmlspecialchars($z['libelle_zone']) ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            <?php else: ?>
              <div style="display: flex; align-items: center; gap: 8px;">
                <select id="filter-zone" class="form-control" disabled readonly aria-readonly="true" style="border: 1.5px solid #E2E8F0; border-radius: 8px; padding: 8px 12px; font-size: 13px; font-weight: 700; color: #1E3A5F; background: #F8FAFC; cursor: not-allowed; min-width: 220px; pointer-events: none;">
                  <option value="<?= htmlspecialchars($userZoneCode ?? '') ?>" selected>
                    <?= htmlspecialchars($userZoneLibelle ?? 'Zone assignée') ?>
                  </option>
                </select>
                <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; color: #B45309; background: #FEF3C7; border: 1px solid #FDE68A; padding: 4px 8px; border-radius: 6px; white-space: nowrap;">
                  <i data-lucide="lock" style="width: 12px; height: 12px;"></i> Zone assignée (Lecture seule)
                </span>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <?php if (!empty($hasJoker)): ?>
          <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; color: #059669; background: #ECFDF5; border: 1px solid #A7F3D0; padding: 5px 12px; border-radius: 20px;">
            <i data-lucide="shield-check" style="width: 14px; height: 14px;"></i> Accès Joker SuperAdmin (Toutes zones autorisées)
          </span>
        <?php endif; ?>
      </div>

      <!-- CARTE TABLEAU PRINCIPALE (NAVY PREMIUM) -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-users" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse; font-size: 13px;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 2px solid #E2E8F0;">
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">ID</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Code</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Nom complet</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Contact (Email / Tél)</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Fonction</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Zone</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Rôle Attribué</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: center;">Statut</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<script>
var IS_SUPER_ADMIN_USER = <?= !empty($isSuperAdminUser) ? 'true' : 'false' ?>;

$(document).ready(function() {
  var table = $('#table-users').DataTable({
    ajax: {
      url: '<?= RACINE ?>user/apiList',
      type: 'POST',
      data: function(d) {
        d.zone_code = $('#filter-zone').val() || '';
      }
    },
    processing: true,
    autoWidth: false,
    columns: [
      { data: 'id', defaultContent: '-', width: '50px' },
      { data: 'code', width: '110px', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<code style="font-weight:700; color:#1E3A5F; background:#F1F5F9; padding:4px 8px; border-radius:6px; font-size:12px; border:1px solid #CBD5E1;">' + (d || '-') + '</code>';
      }},
      { data: 'nom', render: function(d, type, row) {
        if (type !== 'display') return (d || '') + ' ' + (row.prenom || '');
        var nomComplet = (d || '') + ' ' + (row.prenom || '');
        return '<span style="font-weight:800; color:#0F172A; font-size:14px;">' + (nomComplet.trim() || '-') + '</span>';
      }},
      { data: 'email', render: function(d, type, row) {
        if (type !== 'display') return d || row.telephone || '';
        var res = '';
        if (d) res += '<div style="font-weight:700; color:#1E3A5F; font-size:13px;">' + d + '</div>';
        if (row.telephone) res += '<div style="font-size:12px; color:#64748B; font-weight:500;">' + row.telephone + '</div>';
        return res || '-';
      }},
      { data: 'fonction', defaultContent: '-', render: function(d, type) {
        if (type !== 'display') return d || '';
        return '<span style="color:#334155; font-weight:600;">' + (d || '-') + '</span>';
      }},
      { data: 'zone', defaultContent: 'Globale', render: function(d, type, row) {
        var zoneVal = d || (row && row.zone ? row.zone : 'Globale');
        if (type !== 'display') return zoneVal;
        if (!zoneVal || zoneVal === 'Globale') {
          return '<span style="display:inline-block; background:#F1F5F9; color:#64748B; padding:4px 10px; border-radius:20px; font-weight:700; font-size:11px; border:1px solid #CBD5E1;">Globale</span>';
        }
        return '<span style="display:inline-block; background:#E0F2FE; color:#0284C7; padding:4px 10px; border-radius:20px; font-weight:700; font-size:11px; border:1px solid #BAE6FD;">' + zoneVal + '</span>';
      }},
      { data: 'roles_list', render: function(d, type, row) {
        if (type !== 'display') return (row.roles_list && row.roles_list.length) ? row.roles_list.join(', ') : (row.role || '');
        var roles = (row.roles_list && row.roles_list.length) ? row.roles_list : (row.role ? [row.role] : []);
        if (!roles.length || roles[0] === 'Non attribué') {
          return '<span style="color:#94A3B8; font-style:italic; font-size:12px;">Non attribué</span>';
        }
        var badges = roles.map(function(r) {
          return '<span style="background:rgba(30, 58, 95, 0.08); color:#1E3A5F; border: 1px solid rgba(30, 58, 95, 0.2); font-weight:700; padding:3px 8px; border-radius:6px; font-size:11.5px; display:inline-block;">' + r + '</span>';
        });
        return '<div style="display:flex; flex-wrap:wrap; gap:4px; max-width:260px;">' + badges.join('') + '</div>';
      }},
      { data: 'statut', width: '110px', className: 'text-center', render: function(d, type, row) {
        var isActif = (d === 'actif');
        var checkedAttr = isActif ? 'checked' : '';
        var isPending = !!row.token_pending;
        
        var tooltipMsg = isPending
          ? (IS_SUPER_ADMIN_USER ? 'Compte non activé par jeton (Dérogation Super Admin active)' : 'Jeton d\'activation non validé - Modification réservée au Super Admin')
          : (isActif ? 'Actif - Cliquez pour désactiver' : 'Inactif - Cliquez pour activer');

        var canManage = (window.AppConfig && typeof window.AppConfig.can === 'function') ? window.AppConfig.can('ADMIN_MANAGE_USERS') : false;
        var containerStyle = (isPending && !IS_SUPER_ADMIN_USER) || !canManage ? 'opacity: 0.6; cursor: not-allowed;' : 'cursor: pointer;';
        var disabledAttr = (!canManage || (isPending && !IS_SUPER_ADMIN_USER)) ? 'disabled' : '';

        var html = '<div style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:4px;">';
        html += '<label style="position:relative; display:inline-block; width:42px; height:22px; margin:0; ' + containerStyle + '" title="' + tooltipMsg + '">';
        html += '<input type="checkbox" class="toggle-statut-user" data-id="' + (row.id || row.id_user) + '" data-pending="' + (isPending ? '1' : '0') + '" ' + checkedAttr + ' ' + disabledAttr + ' style="opacity:0; width:0; height:0;">';
        html += '<span style="position:absolute; cursor:' + (canManage ? 'pointer' : 'not-allowed') + '; top:0; left:0; right:0; bottom:0; background-color:' + (isActif ? '#059669' : '#CBD5E1') + '; transition:.3s; border-radius:20px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">';
        html += '<span style="position:absolute; content:\'\'; height:16px; width:16px; left:' + (isActif ? '23px' : '3px') + '; bottom:3px; background-color:white; transition:.3s; border-radius:50%; box-shadow: 0 1px 3px rgba(0,0,0,0.2);"></span>';
        html += '</span>';
        html += '</label>';

        if (isPending) {
          var copyBtn = '';
          if (row.activation_url) {
            copyBtn = '<button type="button" onclick="copyActivationUrl(\'' + row.activation_url + '\', this)" style="background:#FEF3C7; border:1px solid #FDE68A; color:#B45309; font-size:10.5px; font-weight:700; border-radius:4px; padding:2px 6px; cursor:pointer; display:inline-flex; align-items:center; gap:3px; margin-top:2px;" title="Copier le lien d\'activation du compte"><i data-lucide="copy" style="width:11px;height:11px;"></i> <span>Copier lien</span></button>';
          }
          html += '<span style="background:#FEF3C7; color:#B45309; border:1px solid #FDE68A; font-size:10px; padding:2px 6px; border-radius:6px; font-weight:800; white-space:nowrap; display:inline-block;" title="En attente de validation du lien mail">Jeton non activé</span>';
          if (copyBtn) html += copyBtn;
        }

        html += '</div>';
        return html;
      }},
      { data: null, width: '170px', orderable: false, render: function(d) {
        var editId = d.editId || d.id;
        var canManage = (window.AppConfig && typeof window.AppConfig.can === 'function') ? window.AppConfig.can('ADMIN_MANAGE_USERS') : false;
        var html = '<div style="display:flex; justify-content:flex-end; gap:6px;">';
        if (canManage) {
          html += '<a href="' + window.RACINE + 'user/edition/' + editId + '" class="btn" style="background:#F1F5F9; color:#1E3A5F; font-weight:700; border-radius:8px; padding:6px 12px; text-decoration:none; border:1px solid #CBD5E1; display:inline-flex; align-items:center; gap:4px; font-size:12px;" title="Modifier"><i data-lucide="edit" style="width:14px;height:14px;"></i> Éditer</a>';
        }
        html += '<a href="' + window.RACINE + 'user/details/' + editId + '" class="btn" style="background:#1E3A5F; color:#FFFFFF; font-weight:700; border-radius:8px; padding:6px 12px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-size:12px;" title="Voir profil"><i data-lucide="eye" style="width:14px;height:14px;"></i> Profil</a>' +
               '</div>';
        return html;
      }, className: 'text-end' }
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  // Rechargement automatique de la liste lors du changement de filtre de zone
  $('#filter-zone').on('change', function() {
    table.ajax.reload();
  });

  // Bascule de statut instantanée via Ajax
  $(document).on('change', '.toggle-statut-user', function(e) {
    var id = $(this).data('id');
    var isPending = $(this).data('pending') == '1';
    var isChecked = $(this).is(':checked');
    var $input = $(this);

    if (isPending && !IS_SUPER_ADMIN_USER) {
      if (window.toastr) {
        toastr.warning("Action bloquée : Ce compte est en attente d'activation par l'utilisateur via le jeton reçu par email. Seul un Super Admin est autorisé à modifier son statut.");
      } else {
        alert("Action bloquée : Seul un Super Admin peut modifier le statut d'un compte avec jeton non activé.");
      }
      $input.prop('checked', !isChecked);
      return false;
    }

    $.ajax({
      url: '<?= RACINE ?>user/changer',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        id: id,
        csrf_token: '<?= Validator::generateCsrfToken() ?>'
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Statut mis à jour avec succès');
          table.ajax.reload(null, false);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors du changement de statut');
          $input.prop('checked', !isChecked);
        }
      },
      error: function() {
        if (window.toastr) toastr.error('Erreur réseau');
        $input.prop('checked', !isChecked);
      }
    });
  });

  window.copyActivationUrl = function(url, btnEl) {
    if (!url) return;
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(url).then(function() {
        showCopyListFeedback(btnEl);
      }).catch(function() {
        fallbackCopyList(url, btnEl);
      });
    } else {
      fallbackCopyList(url, btnEl);
    }
  };

  function fallbackCopyList(text, btnEl) {
    var temp = $('<input>');
    $('body').append(temp);
    temp.val(text).select();
    try {
      document.execCommand('copy');
      showCopyListFeedback(btnEl);
    } catch (err) {
      if (window.toastr) toastr.warning("Veuillez copier manuellement le lien.");
    }
    temp.remove();
  }

  function showCopyListFeedback(btnEl) {
    var $btn = $(btnEl);
    var $span = $btn.find('span');
    var oldText = $span.length ? $span.text() : $btn.text();
    if ($span.length) $span.text('Copié !');
    else $btn.text('Copié !');
    $btn.css({'background': '#059669', 'color': '#FFF', 'border-color': '#059669'});
    if (window.toastr) toastr.success("Lien d'activation copié dans le presse-papier !");
    setTimeout(function() {
      if ($span.length) $span.text(oldText);
      else $btn.text(oldText);
      $btn.css({'background': '#FEF3C7', 'color': '#B45309', 'border-color': '#FDE68A'});
    }, 2000);
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
