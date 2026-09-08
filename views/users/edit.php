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
        <form id="form-user" action="<?= RACINE ?>user/<?= $isEdit ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if ($isEdit): ?>
            <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">
          <?php endif; ?>

          <?php if ($isEdit && !empty($user['token_user'])): 
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl = (strpos(RACINE, 'http://') === 0 || strpos(RACINE, 'https://') === 0) 
                ? rtrim(RACINE, '/') . '/' 
                : ($protocol . '://' . $host . '/' . ltrim(RACINE, '/'));
            $pendingActivationUrl = $baseUrl . 'user/activer?token=' . $user['token_user'];
          ?>
            <div style="background: #FFFBEB; border: 1px solid #FCD34D; border-left: 4px solid #D97706; border-radius: 12px; padding: 18px 20px; margin-bottom: 24px;">
              <div style="display: flex; align-items: center; gap: 8px; font-weight: 800; font-size: 14px; color: #92400E;">
                <i data-lucide="alert-triangle" style="width: 18px; height: 18px; color: #D97706;"></i>
                Compte en attente d'activation par jeton
              </div>
              <p style="font-size: 12.5px; color: #78350F; margin: 4px 0 10px 0; line-height: 1.4;">
                Ce collaborateur n'a pas encore validé son compte via le lien d'activation sécurisé. Vous pouvez copier ce lien pour lui transmettre ou activer directement son compte.
              </p>
              <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <input type="text" class="form-control" readonly value="<?= htmlspecialchars($pendingActivationUrl) ?>" style="flex: 1; min-width: 240px; font-size: 12px; background: #FFFFFF; font-family: monospace; color: #1E293B;" onclick="this.select();">
                <button type="button" class="btn btn-copy-inline" onclick="copyTextToClipboard('<?= htmlspecialchars($pendingActivationUrl) ?>', this)" style="background: #D97706; color: #FFFFFF; font-weight: 700; border-radius: 8px; font-size: 12px; padding: 9px 14px; white-space: nowrap; border: none; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                  <i data-lucide="copy" style="width: 14px; height: 14px;"></i> <span>Copier le lien</span>
                </button>
                <a href="<?= htmlspecialchars($pendingActivationUrl) ?>" target="_blank" class="btn" style="background: #059669; color: #FFFFFF; font-weight: 700; border-radius: 8px; font-size: 12px; padding: 9px 14px; white-space: nowrap; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                  <i data-lucide="external-link" style="width: 14px; height: 14px;"></i> Activer
                </a>
              </div>
            </div>
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

      <!-- MODAL SUCCÈS CRÉATION & LIEN D'ACTIVATION -->
      <div id="modalActivationSuccess" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(5px); padding: 20px;">
        <div style="background: #FFFFFF; border-radius: 20px; width: 100%; max-width: 580px; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35); overflow: hidden; border: 1px solid #E2E8F0; animation: modalSlideDown 0.25s ease-out;">
          
          <div style="padding: 24px 28px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;">
              <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(5, 150, 105, 0.25); border: 1px solid #059669; color: #34D399; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="check-circle" style="width: 24px; height: 24px;"></i>
              </div>
              <div>
                <h3 style="margin: 0; font-size: 17px; font-weight: 800; letter-spacing: -0.3px;">Compte Utilisateur Initialisé !</h3>
                <p style="margin: 2px 0 0 0; font-size: 12px; color: #94A3B8;">Lien d'activation &amp; coordonnées d'accès</p>
              </div>
            </div>
          </div>

          <div style="padding: 28px;">
            <div id="modalActivationAlert" style="background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
              <i data-lucide="mail-check" style="width: 20px; height: 20px; color: #059669; flex-shrink: 0;"></i>
              <span id="modalActivationMessage" style="font-size: 13px; color: #065F46; font-weight: 600;">
                L'utilisateur a été créé avec succès en statut inactif.
              </span>
            </div>

            <!-- Coordonnées sommaires -->
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
              <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <tr>
                  <td style="color: #64748B; font-weight: 600; padding: 4px 0; width: 140px;">Collaborateur :</td>
                  <td id="modalUserNom" style="color: #0F172A; font-weight: 800;">-</td>
                </tr>
                <tr>
                  <td style="color: #64748B; font-weight: 600; padding: 4px 0;">Identifiant / Email :</td>
                  <td id="modalUserEmail" style="color: #0F172A; font-weight: 700;">-</td>
                </tr>
                <tr>
                  <td style="color: #64748B; font-weight: 600; padding: 4px 0;">Mot de passe tempo :</td>
                  <td>
                    <code id="modalUserPassword" style="font-family: monospace; font-size: 14px; font-weight: 800; color: #059669; background: #DCFCE7; border: 1px dashed #86EFAC; padding: 2px 8px; border-radius: 6px; display: inline-block;">-</code>
                  </td>
                </tr>
              </table>
            </div>

            <!-- Bloc Lien d'activation -->
            <div style="margin-bottom: 24px;">
              <label style="display: block; font-size: 12.5px; font-weight: 800; color: #1E3A5F; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
                <i data-lucide="link" style="width: 14px; height: 14px; color: #059669; vertical-align: middle;"></i> Lien d'activation du compte
              </label>
              
              <div style="display: flex; gap: 8px;">
                <input type="text" id="modalActivationLinkInput" class="form-control" readonly style="width: 100%; font-size: 12.5px; font-family: monospace; font-weight: 600; background: #F8FAFC; color: #1E3A5F; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 8px;" onclick="this.select();">
                <button type="button" id="btnCopyActivationLink" class="btn" onclick="copyModalLink()" style="background: #1E3A5F; color: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 10px 18px; white-space: nowrap; border: none; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; font-size: 13px;">
                  <i data-lucide="copy" style="width: 15px; height: 15px;"></i> <span id="copyBtnText">Copier</span>
                </button>
              </div>
              <small style="color: #64748B; font-size: 11.5px; margin-top: 6px; display: block;">
                Ce lien sécurisé permet d'activer le compte et de débloquer l'accès pour ce collaborateur.
              </small>
            </div>

            <!-- Boutons d'action finaux -->
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; border-top: 1px solid #E2E8F0; padding-top: 20px;">
              <a id="modalDirectActivateBtn" href="#" target="_blank" class="btn" style="background: #059669; color: #FFFFFF; font-weight: 800; border-radius: 10px; padding: 11px 22px; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 10px rgba(5, 150, 105, 0.25);">
                <i data-lucide="check" style="width: 16px; height: 16px;"></i> Activer le compte maintenant
              </a>

              <div style="display: flex; gap: 8px;">
                <a href="<?= RACINE ?>user/formulaire" class="btn" style="background: #F1F5F9; color: #475569; font-weight: 700; border-radius: 10px; padding: 11px 16px; text-decoration: none; border: 1px solid #CBD5E1; font-size: 13px;">
                  Nouveau
                </a>
                <a href="<?= RACINE ?>user/list" class="btn" style="background: #1E3A5F; color: #FFFFFF; font-weight: 700; border-radius: 10px; padding: 11px 18px; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                  <i data-lucide="users" style="width: 15px; height: 15px;"></i> Liste des utilisateurs
                </a>
              </div>
            </div>

          </div>
        </div>
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
