<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$statsJour = isset($statsJour) ? $statsJour : ['total_jour' => 0, 'nb_paiements' => 0];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <!-- En-tête de page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="unlock" style="color: #15803D; width: 24px; height: 24px;"></i>
            Ouverture de Caisse &bull; <?= !empty($item['date_ouverture']) ? date('d/m/Y', strtotime($item['date_ouverture'])) : '-' ?>
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Code Session : <strong><?= htmlspecialchars($item['code_ouverture'] ?? '-') ?></strong></p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>ouverture_caisse/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour au registre
          </a>
          <?php if (($item['statut_ouverture'] ?? '') === 'ouverte'): ?>
            <a href="<?= RACINE ?>cloture_caisse/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
              <i data-lucide="lock" style="width: 18px; height: 18px;"></i> Clôturer cette session
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : DÉTAILS DE L'OUVERTURE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="info" style="width: 18px; height: 18px;"></i> Informations sur l'Ouverture de Caisse
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Fond Initial Déclaré</span>
            <div style="font-size: 22px; font-weight: 800; color: #15803D; margin-top: 4px;"><?= number_format((float)($item['fond_initial'] ?? 0), 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Espèces en caisse à l'ouverture</div>
          </div>

          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Heure d'Ouverture</span>
            <div style="font-size: 20px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= htmlspecialchars($item['heure_ouverture'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Date : <?= !empty($item['date_ouverture']) ? date('d/m/Y', strtotime($item['date_ouverture'])) : '-' ?></div>
          </div>

          <div style="background: #FAF5FF; border: 1px solid #E9D5FF; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #7E22CE; text-transform: uppercase;">Caissier / Opérateur</span>
            <div style="font-size: 16px; font-weight: 800; color: #7E22CE; margin-top: 4px;"><?= htmlspecialchars(($item['nom_user'] ?? '') . ' ' . ($item['prenom_user'] ?? '')) ?: 'Caissier' ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Code : <code><?= htmlspecialchars($item['user_code'] ?? '-') ?></code></div>
          </div>

          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Statut de la Caisse</span>
            <div style="margin-top: 6px;">
              <?php if (($item['statut_ouverture'] ?? '') === 'ouverte'): ?>
                <span class="badge" style="background:#DCFCE7; color:#15803D; padding:4px 12px; border-radius:10px; font-weight:700; font-size:12px;">En cours (Ouverte)</span>
              <?php else: ?>
                <span class="badge" style="background:#F1F5F9; color:#475569; padding:4px 12px; border-radius:10px; font-weight:700; font-size:12px;">Clôturée</span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <?php if (!empty($item['observations'])): ?>
          <div style="margin-top: 18px; padding: 14px; background: #F8FAFC; border-radius: 8px; border: 1px solid #E2E8F0; font-size: 13px; color: #475569;">
            <strong>Observations à l'ouverture :</strong> <?= nl2br(htmlspecialchars($item['observations'])) ?>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
