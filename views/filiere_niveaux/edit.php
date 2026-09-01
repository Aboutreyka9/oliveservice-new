<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
  $db = (new Database())->getCon();
  $filieresList = $db->query("SELECT code_filiere, libelle_filiere FROM filieres WHERE statut_filiere = 'actif' ORDER BY libelle_filiere ASC")->fetchAll(PDO::FETCH_ASSOC);
  $niveauxList = $db->query("SELECT code_niveau, libelle_niveau FROM niveaux WHERE statut_niveau = 'actif' ORDER BY libelle_niveau ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="git-merge" style="color: #1E3A5F; width: 24px; height: 24px;"></i>
            <?= !empty($item['id_filiere_niveau']) ? 'Modifier l\'Assignation Filière - Niveau' : 'Nouvelle Assignation Filière - Niveau' ?>
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Rattachement d'un Niveau d'études du catalogue général à une Filière d'études</p>
        </div>
        <a href="<?= RACINE ?>niveau/list" class="btn btn-secondary" style="background: #F1F5F9; color: #334155; border: 1px solid #CBD5E1; font-weight: 700; border-radius: 8px; padding: 10px 18px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la Liste
        </a>
      </div>

      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form action="<?= RACINE ?><?= !empty($item['id_filiere_niveau']) ? 'filiere_niveau/edit' : 'filiere_niveau/add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_filiere_niveau'])): ?>
            <input type="hidden" name="id_filiere_niveau" value="<?= htmlspecialchars($item['id_filiere_niveau']) ?>">
          <?php endif; ?>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; width: 100%; margin-bottom: 24px;">
            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Filière d'Études <span style="color: #EF4444;">*</span></label>
              <select name="filiere_code" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" required>
                <option value="">-- Sélectionner la filière --</option>
                <?php foreach ($filieresList as $f): ?>
                  <option value="<?= htmlspecialchars($f['code_filiere']) ?>" <?= (($item['filiere_code'] ?? '') === $f['code_filiere']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['libelle_filiere']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group" style="width: 100%;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Niveau d'Études Rattaché <span style="color: #EF4444;">*</span></label>
              <select name="niveau_code" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; background: #FFFFFF;" required>
                <option value="">-- Sélectionner le niveau --</option>
                <?php foreach ($niveauxList as $n): ?>
                  <option value="<?= htmlspecialchars($n['code_niveau']) ?>" <?= (($item['niveau_code'] ?? '') === $n['code_niveau']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($n['libelle_niveau']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%;">
            <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 11px 28px; display: inline-flex; align-items: center; gap: 8px;">
              <i data-lucide="save" style="width: 18px; height: 18px;"></i> Enregistrer l'Assignation
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>