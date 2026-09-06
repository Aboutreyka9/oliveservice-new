<?php
require_once __DIR__ . '/../../public/inc/header.php';
$user = isset($user) ? $user : [];
$role = isset($role) ? $role : [];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE LA FICHE UTILISATEUR -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="user" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Fiche Utilisateur : <?= htmlspecialchars(($user['nom_user'] ?? '') . ' ' . ($user['prenom_user'] ?? '')) ?>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Consultation du compte d'accès, des permissions et des rôles attribués
            </p>
          </div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>user/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour à la liste
          </a>
          <a href="<?= RACINE ?>user/edition/<?= $encryptedId ?>" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 10px 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
            <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier l'utilisateur
          </a>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; width: 100%; box-sizing: border-box;">
        
        <!-- CARTE 1 : IDENTITÉ -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); height: fit-content;">
          <div style="text-align: center; padding-bottom: 20px; border-bottom: 1px solid #F1F5F9;">
            <div style="width: 80px; height: 80px; border-radius: 20px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px auto; font-size: 26px; font-weight: 800; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.2);">
              <?= strtoupper(substr($user['nom_user'] ?? 'U', 0, 1) . substr($user['prenom_user'] ?? 'S', 0, 1)) ?>
            </div>
            <h2 style="font-size: 18px; font-weight: 800; color: #0F172A; margin: 0;"><?= htmlspecialchars(($user['nom_user'] ?? '') . ' ' . ($user['prenom_user'] ?? '')) ?></h2>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;"><?= htmlspecialchars($role['libelle_role'] ?? 'Rôle non attribué') ?></p>
            <div style="margin-top: 12px;">
              <?php if (($user['statut_user'] ?? '') === 'actif'): ?>
                <span style="display: inline-flex; align-items: center; gap: 6px; background:#ECFDF5; color:#059669; padding:4px 12px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #A7F3D0;">
                  <i data-lucide="check-circle" style="width: 14px; height: 14px;"></i> Compte Actif
                </span>
              <?php else: ?>
                <span style="display: inline-flex; align-items: center; gap: 6px; background:#FEE2E2; color:#B91C1C; padding:4px 12px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #FECACA;">
                  <i data-lucide="x-circle" style="width: 14px; height: 14px;"></i> Compte Inactif
                </span>
              <?php endif; ?>
            </div>
          </div>

          <div style="padding-top: 20px; display: flex; flex-direction: column; gap: 14px;">
            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Code Utilisateur</span>
              <div style="margin-top: 4px;"><code style="font-weight:700; color:#1E3A5F; background:#F1F5F9; padding:4px 8px; border-radius:6px; font-size:12px; border:1px solid #CBD5E1;"><?= htmlspecialchars($user['code_user'] ?? '-') ?></code></div>
            </div>

            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Email de connexion</span>
              <div style="font-size: 14px; font-weight: 700; color: #0F172A; margin-top: 2px;"><?= htmlspecialchars($user['email_user'] ?? 'Non renseigné') ?></div>
            </div>

            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Téléphone</span>
              <div style="font-size: 14px; font-weight: 600; color: #334155; margin-top: 2px;"><?= htmlspecialchars($user['telephone_user'] ?? 'Non renseigné') ?></div>
            </div>

            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Zone d'Affectation</span>
              <div style="font-size: 14px; font-weight: 700; color: #1E3A5F; margin-top: 2px;"><?= htmlspecialchars($user['libelle_zone'] ?? 'Globale') ?></div>
            </div>

            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Dernière Connexion</span>
              <div style="font-size: 13px; color: #64748B; margin-top: 2px; font-weight: 500;"><?= !empty($user['last_connexion']) ? date('d/m/Y H:i', strtotime($user['last_connexion'])) : 'Jamais connecté' ?></div>
            </div>
          </div>
        </div>

        <!-- CARTE 2 : RÔLES ET PRIVILÈGES -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01);">
          <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
            <i data-lucide="shield-check" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Rôles Attribués & Privilèges RBAC
          </h3>

          <div style="margin-bottom: 24px;">
            <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">Rôles Système Détenus</div>
            
            <?php 
              $displayRoles = !empty($userRoles) ? $userRoles : (!empty($role) ? [$role] : []);
            ?>

            <?php if (!empty($displayRoles)): ?>
              <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach($displayRoles as $ur): ?>
                  <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-left: 4px solid #1E3A5F; border-radius: 10px; padding: 14px 18px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                      <div style="font-size: 15px; font-weight: 800; color: #1E3A5F; display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="award" style="width: 16px; height: 16px; color: #1E3A5F;"></i>
                        <?= htmlspecialchars($ur['libelle_role'] ?? 'Rôle non défini') ?>
                      </div>
                      <span style="background: #F1F5F9; color: #475569; font-weight: 700; font-size: 11px; padding: 3px 8px; border-radius: 6px; border: 1px solid #CBD5E1;">
                        <?= htmlspecialchars($ur['groupe'] ?? ($ur['module'] ?? 'Standard')) ?>
                      </span>
                    </div>
                    <?php if (!empty($ur['description'])): ?>
                      <p style="color: #64748B; font-size: 12.5px; margin: 6px 0 0 24px; font-weight: 500;"><?= htmlspecialchars($ur['description']) ?></p>
                    <?php endif; ?>

                    <div style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed #E2E8F0; display: flex; flex-wrap: wrap; gap: 16px;">
                      <span style="font-size: 12px; font-weight: 700; color: <?= !empty($ur['create_permission']) ? '#059669' : '#94A3B8' ?>; display: inline-flex; align-items: center; gap: 4px;">
                        <i data-lucide="<?= !empty($ur['create_permission']) ? 'check' : 'x' ?>" style="width: 14px; height: 14px;"></i> Créer
                      </span>
                      <span style="font-size: 12px; font-weight: 700; color: <?= !empty($ur['edit_permission']) ? '#0284C7' : '#94A3B8' ?>; display: inline-flex; align-items: center; gap: 4px;">
                        <i data-lucide="<?= !empty($ur['edit_permission']) ? 'check' : 'x' ?>" style="width: 14px; height: 14px;"></i> Modifier
                      </span>
                      <span style="font-size: 12px; font-weight: 700; color: <?= (!isset($ur['show_permission']) || $ur['show_permission'] == 1) ? '#475569' : '#94A3B8' ?>; display: inline-flex; align-items: center; gap: 4px;">
                        <i data-lucide="<?= (!isset($ur['show_permission']) || $ur['show_permission'] == 1) ? 'check' : 'x' ?>" style="width: 14px; height: 14px;"></i> Consulter
                      </span>
                      <span style="font-size: 12px; font-weight: 700; color: <?= !empty($ur['delete_permission']) ? '#DC2626' : '#94A3B8' ?>; display: inline-flex; align-items: center; gap: 4px;">
                        <i data-lucide="<?= !empty($ur['delete_permission']) ? 'check' : 'x' ?>" style="width: 14px; height: 14px;"></i> Supprimer
                      </span>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div style="color: #94A3B8; font-style: italic; font-size: 13px;">Aucun rôle attribué pour le moment.</div>
            <?php endif; ?>
          </div>

          <div style="background: #F8FAFC; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0;">
            <h4 style="font-size: 13px; font-weight: 800; color: #0F172A; margin: 0 0 16px 0;">Matrice des Actions Autorisées :</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
              
              <div style="display: flex; align-items: center; gap: 10px;">
                <i data-lucide="<?= (isset($role['create_permission']) && $role['create_permission'] == 1) ? 'check-circle-2' : 'x-circle' ?>" style="width: 20px; height: 20px; color: <?= (isset($role['create_permission']) && $role['create_permission'] == 1) ? '#059669' : '#DC2626' ?>;"></i>
                <div>
                  <div style="font-weight: 800; font-size: 13px; color: #0F172A;">Création</div>
                  <div style="font-size: 11px; color: #64748B; font-weight: 500;"><?= (isset($role['create_permission']) && $role['create_permission'] == 1) ? 'Autorisé' : 'Refusé' ?></div>
                </div>
              </div>

              <div style="display: flex; align-items: center; gap: 10px;">
                <i data-lucide="<?= (isset($role['edit_permission']) && $role['edit_permission'] == 1) ? 'check-circle-2' : 'x-circle' ?>" style="width: 20px; height: 20px; color: <?= (isset($role['edit_permission']) && $role['edit_permission'] == 1) ? '#0284C7' : '#DC2626' ?>;"></i>
                <div>
                  <div style="font-weight: 800; font-size: 13px; color: #0F172A;">Modification</div>
                  <div style="font-size: 11px; color: #64748B; font-weight: 500;"><?= (isset($role['edit_permission']) && $role['edit_permission'] == 1) ? 'Autorisé' : 'Refusé' ?></div>
                </div>
              </div>

              <div style="display: flex; align-items: center; gap: 10px;">
                <i data-lucide="<?= (!isset($role['show_permission']) || $role['show_permission'] == 1) ? 'check-circle-2' : 'x-circle' ?>" style="width: 20px; height: 20px; color: <?= (!isset($role['show_permission']) || $role['show_permission'] == 1) ? '#059669' : '#DC2626' ?>;"></i>
                <div>
                  <div style="font-weight: 800; font-size: 13px; color: #0F172A;">Consultation</div>
                  <div style="font-size: 11px; color: #64748B; font-weight: 500;"><?= (!isset($role['show_permission']) || $role['show_permission'] == 1) ? 'Autorisé' : 'Refusé' ?></div>
                </div>
              </div>

              <div style="display: flex; align-items: center; gap: 10px;">
                <i data-lucide="<?= (isset($role['delete_permission']) && $role['delete_permission'] == 1) ? 'check-circle-2' : 'x-circle' ?>" style="width: 20px; height: 20px; color: <?= (isset($role['delete_permission']) && $role['delete_permission'] == 1) ? '#059669' : '#DC2626' ?>;"></i>
                <div>
                  <div style="font-weight: 800; font-size: 13px; color: #0F172A;">Suppression</div>
                  <div style="font-size: 11px; color: #64748B; font-weight: 500;"><?= (isset($role['delete_permission']) && $role['delete_permission'] == 1) ? 'Autorisé' : 'Refusé' ?></div>
                </div>
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
  if (window.lucide) lucide.createIcons();
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
