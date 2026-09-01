<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$cours = isset($cours) ? $cours : [];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <!-- En-tête de page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Fiche Enseignant : <?= htmlspecialchars(($item['nom_enseignant'] ?? '') . ' ' . ($item['prenom_enseignant'] ?? '')) ?></h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Profil académique, compte d'accès et répartition des cours</p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>enseignant/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
          </a>
          <a href="<?= RACINE ?>enseignant/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier le profil
          </a>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : PROFIL ENSEIGNANT HORIZONTAL -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="user" style="width: 18px; height: 18px;"></i> Informations Personnelles & Contrat RH
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; align-items: center;">
          
          <!-- Nom & Initiales -->
          <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 52px; height: 52px; min-width: 52px; border-radius: 50%; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 800; border: 1px solid #BFDBFE;">
              <?= strtoupper(substr($item['nom_enseignant'] ?? 'E', 0, 1) . substr($item['prenom_enseignant'] ?? 'P', 0, 1)) ?>
            </div>
            <div>
              <h2 style="font-size: 17px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
                <?= htmlspecialchars(($item['nom_enseignant'] ?? '') . ' ' . ($item['prenom_enseignant'] ?? '')) ?>
              </h2>
              <span style="font-size: 13px; color: #1E3A5F; font-weight: 600;">
                <?= htmlspecialchars($item['grade_enseignant'] ?? 'Enseignant') ?>
              </span>
            </div>
          </div>

          <!-- Coordonnées & Connexion -->
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px;">Identifiant de Connexion</span>
            <div style="font-size: 13px; font-weight: 700; color: #0F172A;"><?= htmlspecialchars($item['email_enseignant'] ?? 'Non renseigné') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Tél : <?= htmlspecialchars($item['telephone_enseignant'] ?? '-') ?></div>
          </div>

          <!-- Contrat & Autorisation -->
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px;">Contrat & Autorisation</span>
            <div style="font-size: 14px; font-weight: 800; color: #1E3A5F;"><?= htmlspecialchars($item['numero_autorisation'] ?? 'Non renseigné') ?></div>
            <div style="font-size: 12px; color: #64748B; text-transform: capitalize; margin-top: 2px;">Contrat <?= htmlspecialchars($item['type_contrat'] ?? 'Permanent') ?></div>
          </div>

          <!-- Statut -->
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px;">Statut</span>
            <div>
              <?php if (($item['statut_enseignant'] ?? '') === 'actif'): ?>
                <span class="badge" style="background:#DCFCE7; color:#15803D; padding:5px 14px; border-radius:10px; font-weight:700; font-size:12px;">Actif</span>
              <?php else: ?>
                <span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:5px 14px; border-radius:10px; font-weight:700; font-size:12px;">Inactif</span>
              <?php endif; ?>
            </div>
          </div>

        </div>
      </div>

      <!-- CARD 2 (COL-12) : COURS ET MATIÈRES ASSIGNÉS -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #EFF6FF;">
          <div>
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="book-open" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Cours & Matières Assignés (<?= count($cours) ?>)
            </h3>
          </div>
          <a href="<?= RACINE ?>enseignant_matiere/formulaire" class="btn btn-sm btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 6px; font-size: 12px;">
            + Affecter une matière
          </a>
        </div>

        <?php if (empty($cours)): ?>
          <p style="color: #94A3B8; text-align: center; padding: 30px 0; font-style: italic;">Aucun cours n'est actuellement affecté à cet enseignant.</p>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 12px;">
                  <th style="padding: 10px;">Matière</th>
                  <th style="padding: 10px;">Classe</th>
                  <th style="padding: 10px; text-align: center;">Coefficient</th>
                  <th style="padding: 10px; text-align: center;">Volume Horaire</th>
                  <th style="padding: 10px; text-align: center;">Statut</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($cours as $c): ?>
                  <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 10px; font-weight: 700; color: #0F172A;"><?= htmlspecialchars($c['libelle_matiere'] ?? '-') ?></td>
                    <td style="padding: 10px; color: #334155; font-weight: 600;"><?= htmlspecialchars($c['libelle_classe'] ?? '-') ?></td>
                    <td style="padding: 10px; text-align: center;">
                      <span style="background: #EFF6FF; color: #1E3A5F; font-weight: 800; padding: 3px 10px; border-radius: 6px; font-size: 12px;">
                        <?= htmlspecialchars($c['coefficient'] ?? ($c['coefficient_enseignant_matiere'] ?? '1.0')) ?>
                      </span>
                    </td>
                    <td style="padding: 10px; text-align: center; color: #64748B;">
                      <?= (int)($c['volume_horaire'] ?? 0) ?> h
                    </td>
                    <td style="padding: 10px; text-align: center;">
                      <span class="badge" style="background:#DCFCE7; color:#15803D; padding:3px 10px; border-radius:8px; font-weight:700; font-size:11px;">Actif</span>
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
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
