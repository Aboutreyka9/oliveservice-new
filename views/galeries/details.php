<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$typeMedia = strtolower($item['type_media'] ?? ($item['type_fichier'] ?? 'image'));
$urlMedia = $item['url_fichier'] ?? ($item['fichier'] ?? '');
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Fiche Média / Galerie : <?= htmlspecialchars($item['titre_galerie'] ?? 'Média') ?></h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Photothèque et vidéothèque institutionnelle GEICG</p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>galerie/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la galerie
          </a>
          <a href="<?= RACINE ?>galerie/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier le média
          </a>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : DÉTAILS DU MÉDIA -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="image" style="width: 18px; height: 18px;"></i> Caractéristiques du Fichier
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Titre de la publication</span>
            <div style="font-size: 18px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($item['titre_galerie'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Code : <code><?= htmlspecialchars($item['code_galerie'] ?? '-') ?></code></div>
          </div>

          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Nature du média</span>
            <div style="font-size: 17px; font-weight: 800; color: #1E3A5F; margin-top: 4px; text-transform: uppercase;"><?= htmlspecialchars($typeMedia) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Date : <?= !empty($item['created_at_galerie']) ? date('d/m/Y', strtotime($item['created_at_galerie'])) : date('d/m/Y') ?></div>
          </div>

          <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Statut de visibilité</span>
            <div style="margin-top: 6px;">
              <?php if (($item['statut_galerie'] ?? '') === 'actif'): ?>
                <span class="badge" style="background:#DCFCE7; color:#15803D; padding:4px 12px; border-radius:10px; font-weight:700; font-size:12px;">En ligne / Publié</span>
              <?php else: ?>
                <span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:4px 12px; border-radius:10px; font-weight:700; font-size:12px;">Masqué / Archivé</span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <?php if (!empty($item['description_galerie'])): ?>
          <div style="padding-top: 16px; border-top: 1px solid #F1F5F9; margin-top: 16px; font-size: 13px; color: #334155;">
            <strong style="color: #64748B;">Légende / Description :</strong> <?= htmlspecialchars($item['description_galerie']) ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- CARD 2 (COL-12) : APERÇU DU CONTENU VISUEL -->
      <?php if (!empty($urlMedia)): ?>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="eye" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Visualisation du Média
        </h3>

        <div style="text-align: center; background: #0F172A; border-radius: 8px; padding: 20px; max-height: 500px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
          <?php if ($typeMedia === 'video'): ?>
            <video src="<?= htmlspecialchars($urlMedia) ?>" controls style="max-width: 100%; max-height: 460px; border-radius: 6px;"></video>
          <?php else: ?>
            <img src="<?= htmlspecialchars($urlMedia) ?>" alt="<?= htmlspecialchars($item['titre_galerie'] ?? 'Média') ?>" style="max-width: 100%; max-height: 460px; object-fit: contain; border-radius: 6px;">
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </main>
</div>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
