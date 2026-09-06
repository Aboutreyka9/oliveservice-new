<?php
require_once __DIR__ . '/../../public/inc/header.php';
$role = isset($role) ? $role : [];
$allPermissions = isset($allPermissions) ? $allPermissions : [];
$assignedCodes = isset($assignedCodes) ? $assignedCodes : [];
$isEdit = !empty($role['id']);
$title = $isEdit ? 'Configuration du Rôle : ' . htmlspecialchars($role['libelle_role']) : 'Ajouter un Rôle & Profil';
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
            <i data-lucide="shield" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              <?= $title ?>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Définition des accès et des permissions granulaires du rôle
            </p>
          </div>
        </div>

        <a href="<?= RACINE ?>role/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;">
          <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour aux rôles
        </a>
      </div>

      <form action="<?= RACINE ?>role/<?= $isEdit ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
        <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
        <?php if ($isEdit): ?>
          <input type="hidden" name="id" value="<?= $role['id'] ?>">
        <?php endif; ?>

        <!-- CARTE 1 : INFORMATIONS GÉNÉRALES -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
          <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
            <i data-lucide="shield" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Informations du Rôle
          </h3>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; width: 100%;">
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Libellé du Rôle <span style="color: #EF4444;">*</span></label>
              <input type="text" class="form-control" name="libelle_role" value="<?= htmlspecialchars($role['libelle_role'] ?? '') ?>" placeholder="Ex: Responsable Scolarité" required style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;">
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Code Système</label>
              <input type="text" class="form-control" name="code_role" value="<?= htmlspecialchars($role['code_role'] ?? '') ?>" placeholder="Ex: ROLE_SCOLARITE" <?= $isEdit ? 'readonly' : '' ?> style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 800; color: #1E3A5F; border-radius: 10px; border: 1px solid #CBD5E1; background: <?= $isEdit ? '#F1F5F9' : '#F8FAFC' ?>; outline: none; font-family: monospace;">
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Groupe / Département</label>
              <input type="text" class="form-control" name="groupe" value="<?= htmlspecialchars($role['groupe'] ?? 'Direction') ?>" placeholder="Ex: Direction Pédagogique, Finance..." style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;">
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Description</label>
              <input type="text" class="form-control" name="description" value="<?= htmlspecialchars($role['description'] ?? '') ?>" placeholder="Ex: Gestion des inscriptions et élèves" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;">
            </div>
          </div>
        </div>

        <!-- CARTE 2 : PERMISSIONS ACCORDÉES -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
          
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 20px; border-bottom: 2px solid #F1F5F9; padding-bottom: 14px;">
            <div>
              <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="key" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Permissions & Privilèges Granulaires
              </h3>
              <p style="color: #64748B; font-size: 12px; margin: 4px 0 0 0; font-weight: 500;">Cochez les droits et privilèges attribués à ce rôle</p>
            </div>
            
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
              <button type="button" class="btn" onclick="checkAllGlobal(true)" style="background: #E0F2FE; color: #0284C7; border: 1px solid #BAE6FD; font-weight: 700; border-radius: 8px; padding: 7px 14px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                <i data-lucide="check-check" style="width: 15px; height: 15px;"></i> Tout cocher
              </button>
              <button type="button" class="btn" onclick="checkAllGlobal(false)" style="background: #FFFFFF; color: #64748B; border: 1px solid #CBD5E1; font-weight: 700; border-radius: 8px; padding: 7px 14px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                <i data-lucide="x" style="width: 15px; height: 15px;"></i> Tout décocher
              </button>
              <button type="button" class="btn" onclick="toggleAllGroups(true)" style="background: #F8FAFC; color: #334155; border: 1px solid #CBD5E1; font-weight: 700; border-radius: 8px; padding: 7px 12px; font-size: 12px; display: inline-flex; align-items: center; gap: 4px; cursor: pointer;" title="Déplier tous les modules">
                <i data-lucide="chevrons-down" style="width: 15px; height: 15px;"></i> Déplier
              </button>
              <button type="button" class="btn" onclick="toggleAllGroups(false)" style="background: #F8FAFC; color: #334155; border: 1px solid #CBD5E1; font-weight: 700; border-radius: 8px; padding: 7px 12px; font-size: 12px; display: inline-flex; align-items: center; gap: 4px; cursor: pointer;" title="Replier tous les modules">
                <i data-lucide="chevrons-up" style="width: 15px; height: 15px;"></i> Replier
              </button>
            </div>
          </div>

          <div style="display: flex; flex-direction: column; gap: 16px;">
            <?php 
            $grpIdx = 0;
            foreach ($allPermissions as $module => $perms): 
              $grpIdx++;
              $grpId = 'grp_' . $grpIdx;
              $totalCount = count($perms);
              $checkedCount = 0;
              foreach ($perms as $p) {
                if (in_array($p['code_permission'], $assignedCodes, true)) {
                  $checkedCount++;
                }
              }
            ?>
              <div class="permission-module-box" id="box-<?= $grpId ?>" style="border: 1px solid #E2E8F0; border-radius: 12px; overflow: hidden; background: #FFFFFF; transition: box-shadow 0.2s ease;">
                
                <div class="module-header" onclick="toggleAccordion('<?= $grpId ?>')" 
                     style="background: #F8FAFC; padding: 14px 20px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; cursor: pointer; user-select: none;">
                  
                  <div style="display: flex; align-items: center; gap: 12px;">
                    <span id="chevron-wrap-<?= $grpId ?>" style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; transition: transform 0.25s ease;">
                      <i data-lucide="chevron-down" id="chevron-<?= $grpId ?>" style="width: 18px; height: 18px; color: #64748B;"></i>
                    </span>
                    <strong style="font-size: 13px; color: #0F172A; letter-spacing: 0.5px; text-transform: uppercase; font-weight: 800;">
                      <?= htmlspecialchars($module) ?>
                    </strong>
                    <span id="badge-<?= $grpId ?>" style="font-size: 11px; font-weight: 800; color: #1E3A5F; background: #E0F2FE; border: 1px solid #BAE6FD; padding: 3px 12px; border-radius: 20px;">
                      <?= $checkedCount ?> / <?= $totalCount ?> cochées
                    </span>
                  </div>

                  <div style="display: flex; gap: 6px;" onclick="event.stopPropagation();">
                    <button type="button" class="btn" onclick="checkModulePerms('<?= $grpId ?>', true, event)" 
                            style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0; font-size: 11px; font-weight: 800; padding: 4px 12px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                      <i data-lucide="check" style="width: 13px; height: 13px;"></i> Tout cocher
                    </button>
                    <button type="button" class="btn" onclick="checkModulePerms('<?= $grpId ?>', false, event)" 
                            style="background: #FFFFFF; color: #64748B; border: 1px solid #CBD5E1; font-size: 11px; font-weight: 700; padding: 4px 12px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                      <i data-lucide="x" style="width: 13px; height: 13px;"></i> Décocher
                    </button>
                  </div>
                </div>

                <div class="module-body" id="body-<?= $grpId ?>" style="padding: 18px; display: block; background: #FFFFFF;">
                  <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px;">
                    <?php foreach ($perms as $p): ?>
                      <?php $isChecked = in_array($p['code_permission'], $assignedCodes, true); ?>
                      <label style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1px solid #F1F5F9; border-radius: 10px; background: #F8FAFC; cursor: pointer; transition: all 0.15s ease;" 
                             onmouseover="this.style.background='#EFF6FF'; this.style.borderColor='#BFDBFE';" 
                             onmouseout="this.style.background='#F8FAFC'; this.style.borderColor='#F1F5F9';">
                        <input type="checkbox" name="permissions[]" class="perm-checkbox perm-group-<?= $grpId ?>" 
                               data-group="<?= $grpId ?>" value="<?= htmlspecialchars($p['code_permission']) ?>" 
                               <?= $isChecked ? 'checked' : '' ?> 
                               onchange="updateGroupBadge('<?= $grpId ?>', <?= $totalCount ?>)" 
                               style="width: 17px; height: 17px; accent-color: #1E3A5F; cursor: pointer;">
                        <span style="font-size: 13px; font-weight: 700; color: #0F172A; line-height: 1.3;">
                          <?= htmlspecialchars($p['libelle_permission']) ?>
                        </span>
                      </label>
                    <?php endforeach; ?>
                  </div>
                </div>

              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- BOUTONS D'ACTION -->
        <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%; flex-wrap: wrap;">
          <button type="submit" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 12px 28px; font-size: 14px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); cursor: pointer;">
            <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Enregistrer le Rôle & Permissions
          </button>
          <a href="<?= RACINE ?>role/list" class="btn" style="background: #F1F5F9; color: #475569; font-weight: 700; border-radius: 10px; padding: 12px 24px; text-decoration: none; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; gap: 6px;">
            Annuler
          </a>
        </div>
      </form>
    </div>
  </main>
</div>

<script>
$(document).ready(function() { 
  if (window.lucide) lucide.createIcons();

  $('.form-control').on('focus', function() {
    $(this).css({
      'background': '#FFFFFF',
      'border-color': '#1E3A5F',
      'box-shadow': '0 0 0 3px rgba(30, 58, 95, 0.12)'
    });
  }).on('blur', function() {
    $(this).css({
      'background': '#F8FAFC',
      'border-color': '#CBD5E1',
      'box-shadow': 'none'
    });
  });
});

function toggleAccordion(grpId) {
  var $body = $('#body-' + grpId);
  var $wrap = $('#chevron-wrap-' + grpId);
  if ($body.is(':visible')) {
    $body.slideUp(180);
    $wrap.css('transform', 'rotate(-90deg)');
  } else {
    $body.slideDown(180);
    $wrap.css('transform', 'rotate(0deg)');
  }
}

function toggleAllGroups(open) {
  if (open) {
    $('.module-body').slideDown(180);
    $('[id^="chevron-wrap-"]').css('transform', 'rotate(0deg)');
  } else {
    $('.module-body').slideUp(180);
    $('[id^="chevron-wrap-"]').css('transform', 'rotate(-90deg)');
  }
}

function checkModulePerms(grpId, checked, evt) {
  if (evt) evt.stopPropagation();
  $('.perm-group-' + grpId).prop('checked', checked);
  var total = $('.perm-group-' + grpId).length;
  updateGroupBadge(grpId, total);
}

function checkAllGlobal(checked) {
  $('.perm-checkbox').prop('checked', checked);
  $('[id^="box-"]').each(function() {
    var grpId = $(this).attr('id').replace('box-', '');
    var total = $('.perm-group-' + grpId).length;
    updateGroupBadge(grpId, total);
  });
}

function updateGroupBadge(grpId, total) {
  var checkedCount = $('.perm-group-' + grpId + ':checked').length;
  $('#badge-' + grpId).text(checkedCount + ' / ' + total + ' cochées');
  if (checkedCount === total) {
    $('#badge-' + grpId).css({'background': '#ECFDF5', 'color': '#059669', 'borderColor': '#A7F3D0'});
  } else if (checkedCount > 0) {
    $('#badge-' + grpId).css({'background': '#E0F2FE', 'color': '#0284C7', 'borderColor': '#BAE6FD'});
  } else {
    $('#badge-' + grpId).css({'background': '#F1F5F9', 'color': '#64748B', 'borderColor': '#CBD5E1'});
  }
}
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
