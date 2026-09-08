<?php
require_once __DIR__ . '/../../public/inc/header.php';
$user = isset($user) ? $user : [];
$role = isset($role) ? $role : [];
$roles = isset($roles) ? $roles : (new ModelRole())->getAll();
$fonctions = isset($fonctions) ? $fonctions : (new ModelFonction())->getAll();
$hasJoker = isset($hasJoker) ? $hasJoker : Context::hasJoker();
$userZoneCode = isset($userZoneCode) ? $userZoneCode : Context::zone();
$isEdit = !empty($user['id_user']);
$title = $isEdit ? 'Modifier l\'Utilisateur' : 'Créer un Compte Utilisateur';
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
            <i data-lucide="user-cog" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              <?= $title ?>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Gestion des accès, des attributions de rôles et des droits d'action RBAC
            </p>
          </div>
        </div>

        <a href="<?= RACINE ?>user/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;">
          <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour à la liste
        </a>
      </div>

      <!-- CARTE FORMULAIRE PRINCIPALE -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>user/<?= $isEdit ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if ($isEdit): ?>
            <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">
          <?php endif; ?>

          <!-- BLOC 1 : IDENTITÉ -->
          <div style="margin-bottom: 28px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="user" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Identité & Coordonnées
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; width: 100%;">
              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Nom de famille <span style="color: #EF4444;">*</span></label>
                <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" name="nom" value="<?= htmlspecialchars($user['nom_user'] ?? '') ?>" placeholder="Ex: KOUASSI" required>
              </div>

              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Prénom(s)</label>
                <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" name="prenom" value="<?= htmlspecialchars($user['prenom_user'] ?? '') ?>" placeholder="Ex: Jean-Marc">
              </div>

              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Adresse Email (Identifiant) <span style="color: #EF4444;">*</span></label>
                <input type="email" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" name="email" value="<?= htmlspecialchars($user['email_user'] ?? '') ?>" placeholder="Ex: utilisateur@geicg.ci" required>
              </div>

              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Numéro Téléphone</label>
                <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" name="telephone" value="<?= htmlspecialchars($user['telephone_user'] ?? '') ?>" placeholder="Ex: 0708091011">
              </div>

              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Fonction / Poste</label>
                <select class="form-control select2" id="sel_fonction_user" name="fonction_code" style="width: 100%;">
                  <option value="">-- Sélectionner un poste --</option>
                  <?php foreach($fonctions as $f): ?>
                    <option value="<?= htmlspecialchars($f['code_fonction']) ?>" <?= (($user['fonction_code'] ?? '') == $f['code_fonction']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($f['libelle_fonction']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: flex; justify-content: space-between; align-items: center; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  <span>Zone d'Affectation <?= empty($hasJoker) ? '<span style="color: #EF4444;">*</span>' : '<span style="color: #64748B; font-weight: 500; font-size: 12px;">(Optionnel)</span>' ?></span>
                  <?php if (empty($hasJoker)): ?>
                    <span style="font-size: 11px; color: #B45309; background: #FEF3C7; border: 1px solid #FDE68A; padding: 2px 8px; border-radius: 6px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                      <i data-lucide="lock" style="width: 12px; height: 12px;"></i> Zone assignée (Lecture seule)
                    </span>
                  <?php endif; ?>
                </label>

                <?php if (empty($hasJoker)): ?>
                  <?php 
                    $lockedZoneCode = !empty($user['zone_code']) ? $user['zone_code'] : ($userZoneCode ?: Context::zone());
                    $lockedZoneLibelle = 'Zone assignée';
                    if (!empty($zones)) {
                      foreach ($zones as $z) {
                        if ($z['code_zone'] === $lockedZoneCode) {
                          $lockedZoneLibelle = $z['libelle_zone'];
                          break;
                        }
                      }
                    }
                  ?>
                  <select class="form-control" id="sel_zone_code" disabled readonly aria-readonly="true" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 700; color: #1E3A5F; background: #F8FAFC; border-radius: 10px; border: 1px solid #CBD5E1; cursor: not-allowed; pointer-events: none;">
                    <option value="<?= htmlspecialchars($lockedZoneCode) ?>" selected>
                      <?= htmlspecialchars($lockedZoneLibelle) ?>
                    </option>
                  </select>
                  <input type="hidden" name="zone_code" value="<?= htmlspecialchars($lockedZoneCode) ?>">
                <?php else: ?>
                  <select class="form-control select2" id="sel_zone_code" name="zone_code" style="width: 100%;">
                    <option value="">-- Aucune zone (Global) --</option>
                    <?php if (!empty($zones)): foreach($zones as $z): ?>
                      <option value="<?= htmlspecialchars($z['code_zone']) ?>" <?= ((($user['zone_code'] ?? $user['zone_user'] ?? '') == $z['code_zone']) ? 'selected' : '') ?>>
                        <?= htmlspecialchars($z['libelle_zone']) ?>
                      </option>
                    <?php endforeach; endif; ?>
                  </select>
                <?php endif; ?>
              </div>

              <?php if (!$isEdit): ?>
              <div class="form-group" style="width: 100%; box-sizing: border-box; grid-column: 1 / -1; background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 12px; padding: 16px;">
                <div style="font-size: 13px; font-weight: 800; color: #059669; display: flex; align-items: center; gap: 8px;">
                  <i data-lucide="shield-check" style="width: 18px; height: 18px;"></i> Mot de passe généré automatiquement
                </div>
                <small style="color: #047857; font-size: 12px; margin-top: 4px; display: block; font-weight: 500;">
                  Un mot de passe sécurisé sera généré et communiqué à l'utilisateur lors de la création de son compte.
                </small>
              </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- BLOC 2 : RÔLES & DROITS -->
          <div style="margin-bottom: 28px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="shield" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Attribution des Rôles & Permissions
            </h3>

            <?php 
              $selectedRoleCodes = isset($userRoleCodes) ? $userRoleCodes : (isset($role['role_code']) ? [$role['role_code']] : []);
            ?>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; width: 100%; margin-bottom: 24px;">
              <div class="form-group" style="width: 100%; box-sizing: border-box; grid-column: 1 / -1;">
                <label style="display: flex; justify-content: space-between; align-items: center; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  <span>Rôle(s) attribué(s) à l'utilisateur <span style="color: #EF4444;">*</span></span>
                  <small style="color: #64748B; font-weight: 500; font-size: 12px;">Sélection multiple autorisée</small>
                </label>
                <select class="form-control select2" id="sel_roles_user" name="roles[]" multiple="multiple" style="width: 100%;" required>
                  <?php foreach($roles as $r): ?>
                    <option value="<?= htmlspecialchars($r['code_role']) ?>" <?= in_array($r['code_role'], $selectedRoleCodes, true) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($r['libelle_role'] . ' (' . ($r['groupe'] ?? $r['module']) . ')') ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <?php if ($isEdit): ?>
              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Statut du compte</label>
                <select class="form-control" name="actif" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 700; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A;">
                  <option value="actif" <?= (($user['statut_user'] ?? 'actif') === 'actif') ? 'selected' : '' ?>>Compte Actif (Autorisé)</option>
                  <option value="inactif" <?= (($user['statut_user'] ?? '') === 'inactif') ? 'selected' : '' ?>>Compte Inactif (Bloqué)</option>
                </select>
              </div>
              <?php endif; ?>
            </div>

            <!-- MATRICE CRUD -->
            <div style="margin-top: 12px; margin-bottom: 24px;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 8px;">
                <h4 style="font-size: 14px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                  <i data-lucide="sliders" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Permissions d'Action (CRUD) Spécifiques
                </h4>
                <span style="font-size: 12px; color: #64748B; font-weight: 500;">Configuration granulaire par rôle</span>
              </div>

              <div id="rolesPermissionsContainer" style="display: flex; flex-direction: column; gap: 14px;"></div>
            </div>
          </div>

          <!-- BOUTONS D'ACTION -->
          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%; flex-wrap: wrap;">
            <button type="submit" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 12px 28px; font-size: 14px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); cursor: pointer;">
              <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Enregistrer l'Utilisateur & Permissions
            </button>
            <a href="<?= RACINE ?>user/list" class="btn" style="background: #F1F5F9; color: #475569; font-weight: 700; border-radius: 10px; padding: 12px 24px; text-decoration: none; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; gap: 6px;">
              Annuler
            </a>
          </div>
        </form>
      </div>

    </div>
  </main>
</div>

<script>
$(document).ready(function() { 
  var ALL_ROLES = <?= json_encode($roles) ?>;
  var SAVED_ROLES = <?= json_encode(!empty($userRoles) ? $userRoles : (!empty($role) ? [$role] : [])) ?>;
  
  var savedPermsMap = {};
  if (Array.isArray(SAVED_ROLES)) {
    SAVED_ROLES.forEach(function(r) {
      if (r && (r.role_code || r.code_role)) {
        var code = r.role_code || r.code_role;
        savedPermsMap[code] = {
          create: r.create_permission !== undefined ? parseInt(r.create_permission) : 1,
          edit: r.edit_permission !== undefined ? parseInt(r.edit_permission) : 1,
          show: r.show_permission !== undefined ? parseInt(r.show_permission) : 1,
          delete: r.delete_permission !== undefined ? parseInt(r.delete_permission) : 0
        };
      }
    });
  }

  var rolesDict = {};
  if (Array.isArray(ALL_ROLES)) {
    ALL_ROLES.forEach(function(r) {
      rolesDict[r.code_role] = r;
    });
  }

  function renderRolePermissions() {
    var selected = $('#sel_roles_user').val() || [];
    if (typeof selected === 'string') selected = [selected];
    var container = $('#rolesPermissionsContainer');
    
    if (!selected.length) {
      container.html('<div style="background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:12px; padding:20px; text-align:center; color:#94A3B8; font-size:13px; font-weight:500;">Veuillez sélectionner au moins un rôle ci-dessus pour définir ses permissions.</div>');
      return;
    }

    var html = '';
    selected.forEach(function(roleCode) {
      var rMeta = rolesDict[roleCode] || { libelle_role: roleCode, module: 'Standard', description: '' };
      
      var curCreate = $('#perm_create_' + roleCode).length ? ($('#perm_create_' + roleCode).is(':checked') ? 1 : 0) : (savedPermsMap[roleCode] ? savedPermsMap[roleCode].create : 1);
      var curEdit   = $('#perm_edit_' + roleCode).length ? ($('#perm_edit_' + roleCode).is(':checked') ? 1 : 0) : (savedPermsMap[roleCode] ? savedPermsMap[roleCode].edit : 1);
      var curShow   = $('#perm_show_' + roleCode).length ? ($('#perm_show_' + roleCode).is(':checked') ? 1 : 0) : (savedPermsMap[roleCode] ? savedPermsMap[roleCode].show : 1);
      var curDelete = $('#perm_delete_' + roleCode).length ? ($('#perm_delete_' + roleCode).is(':checked') ? 1 : 0) : (savedPermsMap[roleCode] ? savedPermsMap[roleCode].delete : 0);

      html += '<div class="role-perm-card" id="card_role_' + roleCode + '" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-left: 4px solid #1E3A5F; border-radius: 12px; padding: 16px 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">' +
                '<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid #F1F5F9;">' +
                  '<div>' +
                    '<strong style="font-size: 14px; color: #0F172A; display: inline-flex; align-items: center; gap: 6px;">' +
                      '<i data-lucide="award" style="width: 16px; height: 16px; color: #1E3A5F;"></i> ' +
                      (rMeta.libelle_role || roleCode) +
                    '</strong>' +
                    '<span style="font-size: 11px; font-weight: 700; color: #64748B; background: #F1F5F9; padding: 3px 8px; border-radius: 6px; margin-left: 8px;">' +
                      (rMeta.groupe || rMeta.module || 'Système') +
                    '</span>' +
                  '</div>' +
                  '<div style="display: flex; gap: 6px;">' +
                    '<button type="button" class="btn" onclick="setRoleAllPerms(\'' + roleCode + '\', true)" style="font-size: 11px; padding: 4px 10px; font-weight: 700; border: 1px solid #CBD5E1; border-radius: 6px; background: #F8FAFC; color: #1E3A5F;">Tout autoriser</button>' +
                    '<button type="button" class="btn" onclick="setRoleReadOnly(\'' + roleCode + '\')" style="font-size: 11px; padding: 4px 10px; font-weight: 700; border: 1px solid #CBD5E1; border-radius: 6px; background: #F8FAFC; color: #475569;">Lecture seule</button>' +
                  '</div>' +
                '</div>' +

                '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px;">' +
                  '<label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13px; color: #334155; cursor: pointer;">' +
                    '<input type="checkbox" id="perm_create_' + roleCode + '" name="role_perms[' + roleCode + '][create]" value="1" ' + (curCreate ? 'checked' : '') + ' style="width: 17px; height: 17px; accent-color: #059669;">' +
                    '<span>Créer (<strong style="color:#059669;">Ajouter</strong>)</span>' +
                  '</label>' +
                  '<label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13px; color: #334155; cursor: pointer;">' +
                    '<input type="checkbox" id="perm_edit_' + roleCode + '" name="role_perms[' + roleCode + '][edit]" value="1" ' + (curEdit ? 'checked' : '') + ' style="width: 17px; height: 17px; accent-color: #0284C7;">' +
                    '<span>Modifier (<strong style="color:#0284C7;">Éditer</strong>)</span>' +
                  '</label>' +
                  '<label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13px; color: #334155; cursor: pointer;">' +
                    '<input type="checkbox" id="perm_show_' + roleCode + '" name="role_perms[' + roleCode + '][show]" value="1" ' + (curShow ? 'checked' : '') + ' style="width: 17px; height: 17px; accent-color: #475569;">' +
                    '<span>Consulter (<strong style="color:#475569;">Afficher</strong>)</span>' +
                  '</label>' +
                  '<label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13px; color: #334155; cursor: pointer;">' +
                    '<input type="checkbox" id="perm_delete_' + roleCode + '" name="role_perms[' + roleCode + '][delete]" value="1" ' + (curDelete ? 'checked' : '') + ' style="width: 17px; height: 17px; accent-color: #DC2626;">' +
                    '<span>Supprimer (<strong style="color:#DC2626;">Effacer</strong>)</span>' +
                  '</label>' +
                '</div>' +
              '</div>';
    });

    container.html(html);
    if (window.lucide) lucide.createIcons();
  }

  window.setRoleAllPerms = function(roleCode, allow) {
    $('#perm_create_' + roleCode).prop('checked', allow);
    $('#perm_edit_' + roleCode).prop('checked', allow);
    $('#perm_show_' + roleCode).prop('checked', allow);
    $('#perm_delete_' + roleCode).prop('checked', allow);
  };

  window.setRoleReadOnly = function(roleCode) {
    $('#perm_create_' + roleCode).prop('checked', false);
    $('#perm_edit_' + roleCode).prop('checked', false);
    $('#perm_show_' + roleCode).prop('checked', true);
    $('#perm_delete_' + roleCode).prop('checked', false);
  };

  $('.form-control').on('focus', function() {
    if (!$(this).is('select')) {
      $(this).css({
        'background': '#FFFFFF',
        'border-color': '#1E3A5F',
        'box-shadow': '0 0 0 3px rgba(30, 58, 95, 0.12)'
      });
    }
  }).on('blur', function() {
    if (!$(this).is('select')) {
      $(this).css({
        'background': '#F8FAFC',
        'border-color': '#CBD5E1',
        'box-shadow': 'none'
      });
    }
  });

  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('#sel_fonction_user').select2({ placeholder: "-- Sélectionner un poste --", allowClear: true, width: '100%' });
    $('#sel_zone_code:not([disabled]), #sel_zone_user:not([disabled])').select2({ placeholder: "-- Aucune zone (Global) --", allowClear: true, width: '100%' });
    $('#sel_roles_user').select2({ placeholder: "Sélectionnez un ou plusieurs rôles", closeOnSelect: false, width: '100%' });
    $('#sel_roles_user').on('change', renderRolePermissions);
  }

  renderRolePermissions();
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
