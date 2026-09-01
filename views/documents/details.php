<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Fiche Document Administratif / Pédagogique</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Intitulé : <strong><?= htmlspecialchars($item['titre_document'] ?? ($item['nom_document'] ?? 'Document')) ?></strong></p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>document/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux documents
          </a>
          <a href="<?= RACINE ?>document/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier le document
          </a>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : DÉTAILS DU DOCUMENT -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="file" style="width: 18px; height: 18px;"></i> Métadonnées & Pièce Jointe
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Désignation</span>
            <div style="font-size: 18px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($item['titre_document'] ?? ($item['nom_document'] ?? '-')) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Code : <code><?= htmlspecialchars($item['code_document'] ?? '-') ?></code></div>
          </div>

          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Catégorie & Format</span>
            <div style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= htmlspecialchars(ucfirst($item['type_document'] ?? 'PDF')) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Date : <?= !empty($item['created_at_document']) ? date('d/m/Y', strtotime($item['created_at_document'])) : date('d/m/Y') ?></div>
          </div>

          <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Statut</span>
            <div style="margin-top: 6px;">
              <?php if (($item['statut_document'] ?? '') === 'actif'): ?>
                <span class="badge" style="background:#DCFCE7; color:#15803D; padding:4px 12px; border-radius:10px; font-weight:700; font-size:12px;">Archivé / Actif</span>
              <?php else: ?>
                <span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:4px 12px; border-radius:10px; font-weight:700; font-size:12px;">Inactif</span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <?php if (!empty($item['description_document'])): ?>
          <div style="padding-top: 16px; border-top: 1px solid #F1F5F9; margin-top: 16px; font-size: 13px; color: #334155;">
            <strong style="color: #64748B;">Description / Objet :</strong> <?= htmlspecialchars($item['description_document']) ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($item['lien_document']) || !empty($item['chemin_fichier'])): 
          $fileUrl = !empty($item['lien_document']) ? $item['lien_document'] : $item['chemin_fichier'];
        ?>
          <div style="padding-top: 16px; border-top: 1px solid #F1F5F9; margin-top: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; background: #F8FAFC; padding: 14px 18px; border-radius: 8px;">
            <div style="display: flex; align-items: center; gap: 10px;">
              <i data-lucide="file-check" style="width: 20px; height: 20px; color: #1E3A5F;"></i>
              <div>
                <strong style="color: #0F172A; font-size: 13.5px;"><?= htmlspecialchars(basename($fileUrl)) ?></strong>
                <div style="font-size: 12px; color: #64748B;"><?= htmlspecialchars($fileUrl) ?></div>
              </div>
            </div>
            <a href="<?= htmlspecialchars($fileUrl) ?>" target="_blank" download class="btn btn-sm btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 6px; padding: 8px 18px; display: inline-flex; align-items: center; gap: 8px;">
              <i data-lucide="download" style="width: 15px; height: 15px;"></i> Télécharger / Ouvrir le fichier
            </a>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
