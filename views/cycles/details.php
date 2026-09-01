<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$stats = isset($stats) ? $stats : [];
$filieres = isset($filieres) ? $filieres : [];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Fiche Cycle de Formation : <?= htmlspecialchars($item['libelle_cycle'] ?? 'Cycle') ?></h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Spécifications du diplôme, filières d'études et effectifs rattachés</p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>cycle/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la liste
          </a>
          <a href="<?= RACINE ?>cycle/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier le cycle
          </a>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : SYNTHÈSE DU CYCLE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="graduation-cap" style="width: 18px; height: 18px;"></i> Informations & Chiffres Clés
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Libellé du Diplôme</span>
            <div style="font-size: 18px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($item['libelle_cycle'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Code : <code><?= htmlspecialchars($item['code_cycle'] ?? '-') ?></code></div>
          </div>

          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Filières Associées</span>
            <div style="font-size: 24px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= count($filieres) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Parcours universitaires</div>
          </div>

          <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Classes Actives</span>
            <div style="font-size: 24px; font-weight: 800; color: #15803D; margin-top: 4px;"><?= (int)($stats['total_classes'] ?? 0) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Promotions ouvertes</div>
          </div>

          <div style="background: #FAF5FF; border: 1px solid #E9D5FF; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #7E22CE; text-transform: uppercase;">Étudiants Inscrits</span>
            <div style="font-size: 24px; font-weight: 800; color: #7E22CE; margin-top: 4px;"><?= number_format((int)($stats['total_etudiants'] ?? 0), 0, ',', ' ') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Effectif global</div>
          </div>
        </div>
      </div>

      <!-- CARD 2 (COL-12) : FILIÈRES DU CYCLE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #EFF6FF;">
          <div>
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="book-marked" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Filières rattachées à ce cycle (<?= count($filieres) ?>)
            </h3>
          </div>
          <a href="<?= RACINE ?>filiere/formulaire" class="btn btn-sm btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 6px; font-size: 12px;">
            + Créer une filière
          </a>
        </div>

        <?php if (empty($filieres)): ?>
          <p style="color: #94A3B8; text-align: center; padding: 30px 0; font-style: italic;">Aucune filière n'est rattachée à ce cycle pour le moment.</p>
        <?php else: ?>
          <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px;">
            <?php foreach ($filieres as $f): ?>
              <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                  <div style="font-size: 14px; font-weight: 800; color: #0F172A;"><?= htmlspecialchars($f['libelle_filiere']) ?></div>
                  <div style="font-size: 12px; color: #64748B;">Code : <code><?= htmlspecialchars($f['code_filiere']) ?></code></div>
                </div>
                <div style="text-align: right;">
                  <span style="background: #EFF6FF; color: #1E3A5F; font-weight: 700; font-size: 11px; padding: 3px 8px; border-radius: 6px;">
                    <?= (int)($f['nb_classes'] ?? 0) ?> classe(s)
                  </span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
