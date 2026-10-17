<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$user = $user ?? [];
$zones = $zones ?? [];
$assignedZoneCodes = $assignedZoneCodes ?? [];
$userZoneCode = $userZoneCode ?? Context::zone();
$hasJoker = $hasJoker ?? Context::hasJoker();
$nomComplet = trim(($user['nom_user'] ?? '') . ' ' . ($user['prenom_user'] ?? ''));
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
            <i data-lucide="edit" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Modifier l'Agent : <?= htmlspecialchars($nomComplet) ?>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Mise à jour des coordonnées, secteurs géographiques de prospection et taux de commission
            </p>
          </div>
        </div>

        <a href="<?= RACINE ?>user/gestionnaireList" class="btn" style="background: #FFFFFF; border: 1px solid #CBD5E1; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;">
          <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour au Répertoire
        </a>
      </div>

      <!-- CARTE DU FORMULAIRE DÉDIÉ -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box;">
        
        <form id="form-modifier-commercial" action="<?= RACINE ?>user/editCommercial" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <input type="hidden" name="id_user" value="<?= (int)($user['id_user'] ?? 0) ?>">

          <!-- BLOC 1 : IDENTITÉ & CONTACT -->
          <div style="margin-bottom: 28px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="user-check" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Identité & Coordonnées de l'Agent
            </h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Nom de famille <span style="color: #EF4444;">*</span>
                </label>
                <input type="text" name="nom" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" value="<?= htmlspecialchars($user['nom_user'] ?? '') ?>" required>
              </div>

              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Prénom(s) <span style="color: #EF4444;">*</span>
                </label>
                <input type="text" name="prenom" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" value="<?= htmlspecialchars($user['prenom_user'] ?? '') ?>" required>
              </div>

              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Genre / Sexe <span style="color: #EF4444;">*</span>
                </label>
                <?php $currentSexe = $user['sexe_user'] ?? 'M'; ?>
                <select name="sexe" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;">
                  <option value="M" <?= $currentSexe === 'M' ? 'selected' : '' ?>>Masculin (M)</option>
                  <option value="F" <?= $currentSexe === 'F' ? 'selected' : '' ?>>Féminin (F)</option>
                </select>
              </div>

              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Numéro de Téléphone
                </label>
                <input type="text" name="telephone" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" value="<?= htmlspecialchars($user['telephone_user'] ?? '') ?>">
              </div>

              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Adresse Email <span style="color: #EF4444;">*</span>
                </label>
                <input type="email" name="email" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" value="<?= htmlspecialchars($user['email_user'] ?? '') ?>" required>
              </div>
            </div>
          </div>

          <!-- BLOC 2 : AFFECTATION DE ZONE & RÉMUNÉRATION -->
          <div style="margin-bottom: 28px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="map-pin" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Affectation Territoriale & Conditions de Commission
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Zone(s) d'Affectation <span style="color: #EF4444;">*</span>
                </label>
                <select name="zone_codes[]" id="select-zones-commercial" class="form-control select2" multiple="multiple" style="width: 100%;" required>
                  <?php foreach ($zones as $z): ?>
                    <?php $isAssigned = in_array($z['code_zone'], $assignedZoneCodes); ?>
                    <option value="<?= htmlspecialchars($z['code_zone']) ?>" <?= $isAssigned ? 'selected' : '' ?>>
                      <?= htmlspecialchars($z['libelle_zone']) ?> [<?= htmlspecialchars($z['code_zone']) ?>]
                    </option>
                  <?php endforeach; ?>
                </select>
                <small style="color: #64748B; font-size: 12px; margin-top: 6px; display: block;">
                  Les zones sélectionnées sont synchronisées automatiquement dans la table des affectations.
                </small>
              </div>

              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Taux de Commission (%)
                </label>
                <div style="position: relative;">
                  <input type="number" step="0.01" min="0" max="100" name="commission" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 38px 12px 16px; font-size: 14px; font-weight: 700; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" value="<?= htmlspecialchars((string)($user['commission'] ?? '0')) ?>">
                  <span style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); font-weight: 800; color: #64748B; font-size: 14px;">%</span>
                </div>
                <small style="color: #64748B; font-size: 12px; margin-top: 6px; display: block;">
                  Pourcentage perçu sur les encaissements et souscriptions conclues.
                </small>
              </div>

              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Statut du Compte Commercial <span style="color: #EF4444;">*</span>
                </label>
                <?php $currentStatut = $user['statut_user'] ?? 'actif'; ?>
                <select name="statut" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;">
                  <option value="actif" <?= $currentStatut === 'actif' ? 'selected' : '' ?>>Actif (Opérationnel)</option>
                  <option value="inactif" <?= $currentStatut === 'inactif' ? 'selected' : '' ?>>Inactif (Suspendu / Désactivé)</option>
                </select>
                <small style="color: #64748B; font-size: 12px; margin-top: 6px; display: block;">
                  Un compte inactif ne peut pas se connecter ni collecter de cotisations.
                </small>
              </div>
            </div>
          </div>

          <!-- BLOC 3 : SÉCURITÉ & MODIFICATION DU MOT DE PASSE -->
          <div style="margin-bottom: 28px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="shield-check" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Sécurité & Modification du Mot de Passe
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                  Nouveau mot de passe <span style="font-weight: 500; color: #64748B; font-size: 12px;">(Optionnel)</span>
                </label>
                <div style="display: flex; gap: 8px;">
                  <input type="text" id="input-password" name="password" class="form-control" style="flex: 1; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; letter-spacing: 1px;" placeholder="Laisser vide pour conserver le mot de passe actuel">
                  <button type="button" id="btn-generate-password" class="btn" style="background: #F1F5F9; border: 1px solid #CBD5E1; color: #1E3A5F; font-weight: 700; border-radius: 10px; padding: 10px 14px; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; cursor: pointer;">
                    <i data-lucide="refresh-cw" style="width: 15px; height: 15px;"></i> Générer
                  </button>
                </div>
                <small style="color: #64748B; font-size: 12px; margin-top: 6px; display: block;">
                  Renseignez ce champ uniquement si vous souhaitez réinitialiser l'accès de l'agent.
                </small>
              </div>
            </div>
          </div>

          <!-- BLOC 4 : CARTE DE TRAÇABILITÉ -->
          <div style="background: #F8FAFC; border-radius: 12px; padding: 18px 24px; border: 1px solid #E2E8F0; margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 12px;">
              <div style="width: 36px; height: 36px; border-radius: 10px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center; border: 1px solid #BFDBFE;">
                <i data-lucide="info" style="width: 20px; height: 20px;"></i>
              </div>
              <div>
                <span style="display: block; font-size: 13px; font-weight: 800; color: #0F172A;">Fiche Commerciale</span>
                <span style="display: block; font-size: 12px; color: #64748B; margin-top: 2px;">
                  Code Agent : <strong><?= htmlspecialchars($user['code_user'] ?? '') ?></strong> &bull; Matricule : <strong><?= htmlspecialchars($user['matricule_user'] ?? 'Non défini') ?></strong>
                </span>
              </div>
            </div>

            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
              <span style="font-size: 12px; font-weight: 700; color: #059669; background: #ECFDF5; border: 1px solid #A7F3D0; padding: 5px 12px; border-radius: 20px;">
                <i data-lucide="user-check" style="width: 12px; height: 12px; display: inline-block; vertical-align: middle; margin-right: 4px;"></i> ROLE_COMMERCIAL
              </span>
              <span style="font-size: 12px; font-weight: 700; color: #1E3A5F; background: #EFF6FF; border: 1px solid #BFDBFE; padding: 5px 12px; border-radius: 20px;">
                <i data-lucide="building" style="width: 12px; height: 12px; display: inline-block; vertical-align: middle; margin-right: 4px;"></i> Établissement actif
              </span>
            </div>
          </div>

          <!-- BOUTONS D'ACTION -->
          <div style="display: flex; justify-content: flex-end; align-items: center; gap: 14px; border-top: 1px solid #F1F5F9; padding-top: 24px;">
            <a href="<?= RACINE ?>user/gestionnaireList" class="btn" style="background: #F1F5F9; color: #475569; font-weight: 700; border-radius: 10px; padding: 12px 22px; text-decoration: none; border: 1px solid #CBD5E1; font-size: 14px; transition: all 0.2s ease;">
              Annuler
            </a>
            <button type="submit" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; font-weight: 800; border-radius: 10px; padding: 12px 28px; font-size: 14px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.25); transition: all 0.2s ease;">
              <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Enregistrer les Modifications
            </button>
          </div>
        </form>
      </div>

    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) {
    lucide.createIcons();
  }

  // Initialisation du Select2 multiple pour les zones
  if ($.fn.select2) {
    $('#select-zones-commercial').select2({
      placeholder: "-- Choisir une ou plusieurs zones d'affectation --",
      allowClear: true,
      width: '100%'
    });
  }

  // Générateur de mot de passe aléatoire
  $('#btn-generate-password').on('click', function() {
    var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%';
    var gen = 'COM-';
    for (var i = 0; i < 6; i++) {
      gen += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    $('#input-password').val(gen);
    notifyToast('Nouveau mot de passe généré : ' + gen, 'info');
  });

  function notifyToast(msg, type) {
    if (typeof showToast === 'function') {
      showToast(msg, type);
    } else if (window.toastr && typeof window.toastr[type] === 'function') {
      window.toastr[type](msg);
    } else {
      alert(msg);
    }
  }

  // Soumission AJAX du formulaire
  $('#form-modifier-commercial').on('submit', function(e) {
    e.preventDefault();
    var $btn = $(this).find('button[type="submit"]');
    var originalText = $btn.html();
    $btn.prop('disabled', true).html('<i data-lucide="loader-2" class="spin" style="width: 18px; height: 18px;"></i> Enregistrement...');
    if (window.lucide) lucide.createIcons();

    $.ajax({
      url: $(this).attr('action'),
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          notifyToast(res.message || 'Commercial mis à jour avec succès !', 'success');
          setTimeout(function() {
            window.location.href = res.data && res.data.redirect ? res.data.redirect : '<?= RACINE ?>user/gestionnaireList';
          }, 1200);
        } else {
          $btn.prop('disabled', false).html(originalText);
          if (window.lucide) lucide.createIcons();
          notifyToast(res.message || 'Erreur lors de la modification.', 'error');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html(originalText);
        if (window.lucide) lucide.createIcons();
        var errMsg = 'Erreur réseau lors de la mise à jour';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errMsg = xhr.responseJSON.message;
        }
        notifyToast(errMsg, 'error');
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
