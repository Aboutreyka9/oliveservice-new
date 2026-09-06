<?php
require_once __DIR__ . '/../../public/inc/header.php';
$role = isset($role) ? $role : [];
$permissions = isset($permissions) ? $permissions : [];
$users = isset($users) ? $users : [];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE LA FICHE RÔLE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="shield" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Fiche Rôle : <?= htmlspecialchars($role['libelle_role'] ?? 'Rôle') ?>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Configuration des privilèges et liste des utilisateurs titulaires
            </p>
          </div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>role/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour aux rôles
          </a>
          <a href="<?= RACINE ?>role/edition/<?= $encryptedId ?>" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 10px 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
            <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Configurer ce rôle
          </a>
        </div>
      </div>

      <!-- CARTE 1 : INFORMATIONS GÉNÉRALES -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
          <i data-lucide="shield" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Caractéristiques du Rôle
        </h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; align-items: center;">
          
          <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 52px; height: 52px; min-width: 52px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.2);">
              <i data-lucide="award" style="width: 26px; height: 26px;"></i>
            </div>
            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 2px;">Libellé Rôle</span>
              <h2 style="font-size: 18px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
                <?= htmlspecialchars($role['libelle_role'] ?? '-') ?>
              </h2>
              <span style="font-size: 12px; color: #64748B; font-weight: 600;">
                Groupe : <?= htmlspecialchars($role['groupe'] ?? ($role['module'] ?? 'Direction & IT')) ?>
              </span>
            </div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px;">Code Système</span>
            <code style="font-size: 13px; font-weight: 800; color: #1E3A5F; background: #F1F5F9; padding: 4px 10px; border-radius: 6px; border: 1px solid #CBD5E1; display: inline-block;">
              <?= htmlspecialchars($role['code_role'] ?? '-') ?>
            </code>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px;">Description du Rôle</span>
            <div style="font-size: 13px; color: #334155; line-height: 1.4; font-weight: 500;">
              <?= htmlspecialchars($role['description'] ?? 'Accès complet sur les modules autorisés.') ?>
            </div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Statut d'Activité</span>
            <div>
              <?php if (($role['statut_role'] ?? '') === 'actif'): ?>
                <span style="display: inline-flex; align-items: center; gap: 6px; background:#ECFDF5; color:#059669; padding:4px 14px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #A7F3D0;">
                  <i data-lucide="check-circle" style="width: 14px; height: 14px;"></i> Rôle Actif
                </span>
              <?php else: ?>
                <span style="display: inline-flex; align-items: center; gap: 6px; background:#FEE2E2; color:#B91C1C; padding:4px 14px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #FECACA;">
                  <i data-lucide="x-circle" style="width: 14px; height: 14px;"></i> Rôle Inactif
                </span>
              <?php endif; ?>
            </div>
          </div>

        </div>
      </div>

      <!-- CARTE 2 : PERMISSIONS AUTORISÉES -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #F1F5F9;">
          <div>
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="key" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Permissions & Privilèges Autorisés
            </h3>
            <p style="color: #64748B; font-size: 12px; margin: 4px 0 0 0; font-weight: 500;">Liste des droits accordés aux utilisateurs ayant ce rôle</p>
          </div>
          <span style="font-size: 12px; font-weight: 800; color: #059669; background: #ECFDF5; padding: 4px 14px; border-radius: 20px; border: 1px solid #A7F3D0;">
            <?= count($permissions) ?> permissions actives
          </span>
        </div>

        <?php if (empty($permissions)): ?>
          <div style="text-align: center; padding: 40px 20px; color: #94A3B8;">
            <i data-lucide="shield-off" style="width: 42px; height: 42px; margin-bottom: 8px; opacity: 0.5;"></i>
            <p style="font-size: 13px; margin: 0; font-weight: 500;">Aucune permission spécifique n'a été rattachée à ce rôle.</p>
          </div>
        <?php else: ?>
          <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px;">
            <?php foreach ($permissions as $p): ?>
              <div style="background: #F8FAFC; border: 1px solid #E2E8F0; color: #1E3A5F; font-weight: 600; font-size: 13px; padding: 12px 14px; border-radius: 10px; display: flex; align-items: center; gap: 10px;">
                <div style="width: 24px; height: 24px; min-width: 24px; border-radius: 50%; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; border: 1px solid #A7F3D0;">
                  <i data-lucide="check" style="width: 14px; height: 14px;"></i>
                </div>
                <div>
                  <div style="font-weight: 800; color: #0F172A; line-height: 1.3; font-size: 13px;"><?= htmlspecialchars($p['libelle_permission']) ?></div>
                  <div style="font-size: 11px; color: #64748B; font-family: monospace; font-weight: 600;"><?= htmlspecialchars($p['code_permission']) ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- CARTE 3 : UTILISATEURS ASSIGNÉS À CE RÔLE -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #F1F5F9;">
          <div>
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="users" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Utilisateurs Titulaires de ce Rôle
            </h3>
            <p style="color: #64748B; font-size: 12px; margin: 4px 0 0 0; font-weight: 500;">Membres du personnel disposant actuellement de ce niveau d'accès</p>
          </div>
          <span style="font-size: 12px; font-weight: 800; color: #1E3A5F; background: #E0F2FE; padding: 4px 14px; border-radius: 20px; border: 1px solid #BAE6FD;">
            <?= count($users) ?> agent(s)
          </span>
        </div>

        <?php if (empty($users)): ?>
          <div style="text-align: center; padding: 40px 20px; color: #94A3B8;">
            <i data-lucide="user-x" style="width: 42px; height: 42px; margin-bottom: 8px; opacity: 0.5;"></i>
            <p style="font-size: 13px; margin: 0; font-weight: 500;">Aucun utilisateur n'est actuellement titulaire de ce rôle.</p>
          </div>
        <?php else: ?>
          <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px;">
            <?php foreach ($users as $u): ?>
              <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 16px; display: flex; align-items: center; gap: 12px;">
                <div style="width: 42px; height: 42px; min-width: 42px; border-radius: 12px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; box-shadow: 0 2px 6px rgba(30, 58, 95, 0.2);">
                  <?= strtoupper(substr($u['nom_user'] ?? 'U', 0, 1) . substr($u['prenom_user'] ?? '', 0, 1)) ?>
                </div>
                <div style="flex: 1; overflow: hidden;">
                  <div style="font-size: 14px; font-weight: 800; color: #0F172A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <?= htmlspecialchars(($u['nom_user'] ?? '') . ' ' . ($u['prenom_user'] ?? '')) ?>
                  </div>
                  <div style="font-size: 12px; color: #1E3A5F; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <?= htmlspecialchars($u['email_user'] ?? '') ?>
                  </div>
                  <?php if (!empty($u['telephone_user'])): ?>
                    <div style="font-size: 11px; color: #64748B; font-weight: 500;">
                      <?= htmlspecialchars($u['telephone_user']) ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
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
