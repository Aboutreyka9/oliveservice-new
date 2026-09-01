<?php 
require_once __DIR__ . '/../../public/inc/header.php';
$users = isset($users) ? $users : (new ModelUser())->getAll();
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_enseignant']) ? 'Éditer ' : 'Ajouter ' ?> un Enseignant</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Création et gestion directe du corps enseignant et de ses accès</p>
        </div>
        <a href="<?= RACINE ?>enseignant/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
        </a>
      </div>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?>enseignant/<?= !empty($item['id_enseignant']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_enseignant'])): ?>
            <input type="hidden" name="id_enseignant" value="<?= $item['id_enseignant'] ?>">
          <?php endif; ?>

          <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 8px;">
            <i data-lucide="user" style="width: 18px; height: 18px;"></i> Identité & Identifiants de Connexion
          </h3>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; width: 100%; margin-bottom: 24px;">
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Nom de famille <span style="color: #EF4444;">*</span></label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="nom_enseignant" value="<?= htmlspecialchars($item['nom_enseignant'] ?? '') ?>" placeholder="Ex: KOUASSI" required>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Prénom(s) <span style="color: #EF4444;">*</span></label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="prenom_enseignant" value="<?= htmlspecialchars($item['prenom_enseignant'] ?? '') ?>" placeholder="Ex: Jean-Marc" required>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Adresse Email (Login enseignant)</label>
              <input type="email" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="email_enseignant" value="<?= htmlspecialchars($item['email_enseignant'] ?? '') ?>" placeholder="Ex: prof.kouassi@geicg.ci">
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Numéro Téléphone (Contact & Login)</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="telephone_enseignant" value="<?= htmlspecialchars($item['telephone_enseignant'] ?? '') ?>" placeholder="Ex: 0708091011">
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Genre / Sexe</label>
              <select class="form-control" name="sexe_enseignant" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;">
                <option value="M" <?= (($item['sexe_enseignant'] ?? 'M') === 'M') ? 'selected' : '' ?>>Masculin (M)</option>
                <option value="F" <?= (($item['sexe_enseignant'] ?? '') === 'F') ? 'selected' : '' ?>>Féminin (F)</option>
              </select>
            </div>

            <?php if (empty($item['id_enseignant'])): ?>
            <div class="form-group" style="width: 100%; box-sizing: border-box; grid-column: 1 / -1; background: #F0FDF4; border: 1.5px dashed #86EFAC; border-radius: 8px; padding: 12px 16px;">
              <div style="font-size: 13px; font-weight: 700; color: #166534; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="shield-check" style="width: 18px; height: 18px; color: #16A34A;"></i>
                Accès Enseignant : Mot de passe généré automatiquement
              </div>
              <small style="color: #15803D; font-size: 12px; margin-top: 4px; display: block;">
                Un mot de passe sécurisé sera automatiquement généré et affiché dès l'enregistrement de l'enseignant.
              </small>
            </div>
            <?php endif; ?>
          </div>

          <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 24px 0 16px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 8px;">
            <i data-lucide="award" style="width: 18px; height: 18px;"></i> Statut Académique & Autorisations
          </h3>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; width: 100%;">
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Grade / Titre académique</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="grade_enseignant" value="<?= htmlspecialchars($item['grade_enseignant'] ?? '') ?>" placeholder="Ex: Docteur, Ingénieur, Professeur, Maître-Assistant...">
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Type de contrat</label>
              <select class="form-control" name="type_contrat" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;">
                <option value="permanent" <?= (($item['type_contrat'] ?? 'permanent') === 'permanent') ? 'selected' : '' ?>>Permanent / Titulaire</option>
                <option value="vacataire" <?= (($item['type_contrat'] ?? '') === 'vacataire') ? 'selected' : '' ?>>Vacataire (Paiement à l'heure)</option>
                <option value="prestataire" <?= (($item['type_contrat'] ?? '') === 'prestataire') ? 'selected' : '' ?>>Prestataire / Consultant externe</option>
              </select>
            </div>

            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Numéro d'Autorisation d'Enseigner</label>
              <input type="text" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;" name="numero_autorisation" value="<?= htmlspecialchars($item['numero_autorisation'] ?? '') ?>" placeholder="Ex: AUT-2024-00892">
              <small style="color: #64748B; font-size: 11px; margin-top: 4px; display: block;">Numéro d'autorisation ministérielle officielle.</small>
            </div>

            <?php if (!empty($item['id_enseignant'])): ?>
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Statut d'activité</label>
              <select class="form-control" name="statut_enseignant" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #0F172A;">
                <option value="actif" <?= (($item['statut_enseignant'] ?? 'actif') === 'actif') ? 'selected' : '' ?>>Actif (En service)</option>
                <option value="inactif" <?= (($item['statut_enseignant'] ?? '') === 'inactif') ? 'selected' : '' ?>>Inactif / Suspendu</option>
              </select>
            </div>
            <?php endif; ?>
          </div>

          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 10px 24px;">Enregistrer l'Enseignant</button>
            <a href="<?= RACINE ?>enseignant/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 10px 24px;">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
