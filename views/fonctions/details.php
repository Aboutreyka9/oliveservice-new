<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$users = isset($users) ? $users : [];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE LA FICHE FONCTION -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="briefcase" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Fiche Fonction : <?= htmlspecialchars($item['libelle_fonction'] ?? 'Fonction') ?>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Poste et responsabilité administrative ou académique
            </p>
          </div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>fonction/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour aux fonctions
          </a>
          <a href="<?= RACINE ?>fonction/edition/<?= $encryptedId ?>" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 10px 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
            <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier la fonction
          </a>
        </div>
      </div>

      <!-- CARTE 1 : INFORMATIONS SUR LE POSTE -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
          <i data-lucide="briefcase" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Caractéristiques du Poste
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Intitulé du Poste</span>
            <div style="font-size: 17px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($item['libelle_fonction'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px; font-weight: 500;">
              Code : <code style="font-weight: 700; color: #1E3A5F; background: #E2E8F0; padding: 2px 6px; border-radius: 4px;"><?= htmlspecialchars($item['code_fonction'] ?? '-') ?></code>
            </div>
          </div>

          <div style="background: #F0F9FF; border: 1px solid #BAE6FD; border-radius: 12px; padding: 18px;">
            <span style="font-size: 11px; font-weight: 700; color: #0369A1; text-transform: uppercase; letter-spacing: 0.5px;">Effectif Assigné</span>
            <div style="font-size: 22px; font-weight: 800; color: #0284C7; margin-top: 4px;"><?= count($users) ?> agent(s)</div>
            <div style="font-size: 12px; color: #0369A1; margin-top: 4px; font-weight: 500;">Personnel actif occupant ce poste</div>
          </div>

          <div style="background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 12px; padding: 18px;">
            <span style="font-size: 11px; font-weight: 700; color: #047857; text-transform: uppercase; letter-spacing: 0.5px;">Statut d'Activité</span>
            <div style="margin-top: 8px;">
              <?php if (($item['statut_fonction'] ?? '') === 'actif'): ?>
                <span style="display: inline-flex; align-items: center; gap: 6px; background:#FFFFFF; color:#059669; padding:4px 12px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #A7F3D0;">
                  <i data-lucide="check-circle" style="width: 14px; height: 14px;"></i> Poste Actif
                </span>
              <?php else: ?>
                <span style="display: inline-flex; align-items: center; gap: 6px; background:#FFFFFF; color:#DC2626; padding:4px 12px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #FECACA;">
                  <i data-lucide="x-circle" style="width: 14px; height: 14px;"></i> Poste Inactif
                </span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- CARTE 2 : TITULAIRES DU POSTE -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
          <i data-lucide="users" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Membres du Personnel Assignés (<?= count($users) ?>)
        </h3>

        <?php if (empty($users)): ?>
          <p style="color: #94A3B8; text-align: center; padding: 32px 0; font-style: italic; font-size: 13px;">Aucun collaborateur n'est assigné à cette fonction pour le moment.</p>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 2px solid #E2E8F0;">
                  <th style="padding: 12px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Nom & Prénom(s)</th>
                  <th style="padding: 12px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Email / Login</th>
                  <th style="padding: 12px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Téléphone</th>
                  <th style="padding: 12px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Rôle Système</th>
                  <th style="padding: 12px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: center;">Statut</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($users as $u): ?>
                  <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 12px 16px; font-weight: 800; color: #0F172A;">
                      <a href="<?= RACINE ?>user/details/<?= $this->validator->crypter($u['id_user']) ?>" style="color: #1E3A5F; text-decoration: none; font-weight: 800;">
                        <?= htmlspecialchars(($u['nom_user'] ?? '') . ' ' . ($u['prenom_user'] ?? '')) ?>
                      </a>
                    </td>
                    <td style="padding: 12px 16px; color: #334155; font-weight: 600;"><?= htmlspecialchars($u['email_user'] ?? '-') ?></td>
                    <td style="padding: 12px 16px; color: #64748B; font-weight: 500;"><?= htmlspecialchars($u['telephone_user'] ?? '-') ?></td>
                    <td style="padding: 12px 16px;">
                      <span style="background:rgba(30, 58, 95, 0.08); color:#1E3A5F; border:1px solid rgba(30, 58, 95, 0.2); padding:3px 8px; border-radius:6px; font-size:11px; font-weight:700;">
                        <?= htmlspecialchars($u['libelle_role'] ?? 'Utilisateur') ?>
                      </span>
                    </td>
                    <td style="padding: 12px 16px; text-align: center;">
                      <span style="display:inline-flex; align-items:center; gap:4px; background:#ECFDF5; color:#059669; padding:4px 10px; border-radius:20px; font-weight:800; font-size:11px; border:1px solid #A7F3D0;">
                        <i data-lucide="check-circle" style="width: 12px; height: 12px;"></i> Actif
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
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
