<?php
require_once __DIR__ . '/../../public/inc/header.php';
$ville = isset($ville) ? $ville : [];
$quartiers = isset($quartiers) ? $quartiers : [];
$encryptedId = isset($encryptedId) ? $encryptedId : '';
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
          <h1 style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="navigation" style="color: #2563EB;"></i> Ville : <?= htmlspecialchars($ville['libelle_ville'] ?? '') ?>
          </h1>
          <p class="page-subtitle" style="color: #64748B; margin: 4px 0 0 0;">Configuration de la ville et découpage des quartiers de livraison</p>
        </div>
        <div style="display: flex; gap: 10px;">
          <a href="<?= RACINE ?>ville/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
            <i class="fa fa-arrow-left"></i> Retour aux villes
          </a>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1.6fr; gap: 24px; align-items: start;">
        
        <!-- CARTE INFORMATIONS VILLE -->
        <div class="card" style="border-radius: 14px; border: 1px solid #E2E8F0; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); background: #FFFFFF;">
          <div style="margin-bottom: 20px; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 16px; font-weight: 700; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="map" style="color: #2563EB; width: 18px; height: 18px;"></i> Paramètres de la Ville
            </h2>
            <span class="badge-status <?= ($ville['statut_ville'] ?? '') === 'actif' ? 'delivered' : 'cancelled' ?>">
              <?= htmlspecialchars($ville['statut_ville'] ?? 'actif') ?>
            </span>
          </div>

          <form class="formEditVille" id="formEditVille">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_ville" name="id_ville" value="<?= htmlspecialchars($ville['id_ville'] ?? '') ?>">

            <div class="form-group" style="margin-bottom: 16px;">
              <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Code Ville</label>
              <input type="text" class="form-control" id="code_ville" name="code_ville"
                     value="<?= htmlspecialchars($ville['code_ville'] ?? '') ?>" readonly style="background: #F8FAFC; font-weight: 700;">
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
              <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Nom de la Ville *</label>
              <input type="text" class="form-control" id="libelle_ville" name="libelle_ville"
                     value="<?= htmlspecialchars($ville['libelle_ville'] ?? '') ?>" required placeholder="Ex: Abidjan, Yamoussoukro, Bouaké...">
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
              <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Statut</label>
              <select class="form-control" id="statut_ville" name="statut_ville">
                <option value="actif" <?= ($ville['statut_ville'] ?? 'actif') === 'actif' ? 'selected' : '' ?>>Actif</option>
                <option value="inactif" <?= ($ville['statut_ville'] ?? '') === 'inactif' ? 'selected' : '' ?>>Inactif</option>
              </select>
            </div>

            <div style="display: flex; justify-content: flex-end;">
              <button type="submit" class="btn btn-primary btn_actions" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 6px; font-weight: 700; width: 100%; justify-content: center;">
                <i data-lucide="save" style="width: 16px; height: 16px;"></i> Enregistrer la ville
              </button>
            </div>
          </form>
        </div>

        <!-- CARTE QUARTIERS RATTACHÉS -->
        <div class="card" style="border-radius: 14px; border: 1px solid #E2E8F0; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); background: #FFFFFF;">
          <div style="margin-bottom: 20px; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
              <h2 style="font-size: 16px; font-weight: 700; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="map-pin" style="color: #2563EB; width: 18px; height: 18px;"></i> Quartiers de <?= htmlspecialchars($ville['libelle_ville'] ?? '') ?>
                <span style="font-size: 12px; background: #EFF6FF; color: #1E40AF; padding: 2px 8px; border-radius: 999px; font-weight: 700; border: 1px solid #BFDBFE;">
                  <?= count($quartiers) ?>
                </span>
              </h2>
            </div>
            <button type="button" class="btn btn-sm btn-primary" onclick="openAddQuartierModalDirect('<?= htmlspecialchars($ville['code_ville'] ?? '') ?>', '<?= htmlspecialchars(addslashes($ville['libelle_ville'] ?? '')) ?>')" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; font-size: 13px;">
              <i data-lucide="plus" style="width: 14px; height: 14px;"></i> Ajouter un quartier
            </button>
          </div>

          <?php if (empty($quartiers)): ?>
            <div style="text-align: center; padding: 36px 20px; background: #F8FAFC; border-radius: 12px; border: 1px dashed #CBD5E1;">
              <i data-lucide="map-pin-off" style="width: 38px; height: 38px; color: #94A3B8; margin-bottom: 8px;"></i>
              <p style="color: #64748B; font-size: 14px; margin: 0 0 12px 0;">Aucun quartier rattaché à cette ville pour le moment.</p>
              <button type="button" class="btn btn-sm btn-primary" onclick="openAddQuartierModalDirect('<?= htmlspecialchars($ville['code_ville'] ?? '') ?>', '<?= htmlspecialchars(addslashes($ville['libelle_ville'] ?? '')) ?>')">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i> Ajouter le 1er quartier
              </button>
            </div>
          <?php else: ?>
            <div class="table-responsive-mobile">
              <table class="table" style="width: 100%;">
                <thead>
                  <tr>
                    <th>N°</th>
                    <th>Code</th>
                    <th>Nom du Quartier</th>
                    <th>Statut</th>
                    <th style="text-align: center;">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($quartiers as $idx => $q): 
                    $isActif = ($q['statut_quartier'] ?? 'actif') === 'actif';
                  ?>
                  <tr>
                    <td><?= $idx + 1 ?></td>
                    <td><span class="code-badge"><?= htmlspecialchars($q['code_quartier'] ?? '') ?></span></td>
                    <td><strong style="color: #1E293B;"><?= htmlspecialchars($q['libelle_quartier'] ?? '') ?></strong></td>
                    <td>
                      <span class="badge-status <?= $isActif ? 'delivered' : 'cancelled' ?>">
                        <?= $isActif ? 'Actif' : 'Inactif' ?>
                      </span>
                    </td>
                    <td style="text-align: center;">
                      <div class="table-actions" style="justify-content: center;">
                        <a href="<?= RACINE ?>quartier/edition/<?= htmlspecialchars($q['editId'] ?? '') ?>" class="btn-action btn-action-primary" title="Modifier">
                          <i class="fa fa-edit"></i>
                        </a>
                        <button type="button" class="btn-action <?= $isActif ? 'btn-action-warning' : 'btn-action-success' ?> btnToggleQuartierQuick"
                                data-id="<?= htmlspecialchars($q['id_quartier'] ?? '') ?>" title="<?= $isActif ? 'Désactiver' : 'Activer' ?>">
                          <i class="fa <?= $isActif ? 'fa-pause' : 'fa-play' ?>"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

      </div>

    </div>
  </main>
</div>

<!-- MODAL AJOUT RAPIDE QUARTIER DANS CETTE VILLE -->
<div id="modalAddQuartierDirect" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px); padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden;">
    <div style="padding: 18px 22px; background: #F8FAFC; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #1E293B; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="map-pin" style="color: #2563EB; width: 18px; height: 18px;"></i> Ajouter un Quartier à <span id="directVilleNom"></span>
      </h3>
      <button type="button" onclick="closeAddQuartierDirectModal()" style="background: none; border: none; font-size: 18px; color: #94A3B8; cursor: pointer;">
        <i class="fa fa-times"></i>
      </button>
    </div>

    <form id="formAddQuartierDirect" style="padding: 22px;">
      <?= Validator::csrfField() ?>
      <input type="hidden" name="ville_code" id="directVilleCode" value="<?= htmlspecialchars($ville['code_ville'] ?? '') ?>">

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Nom du Quartier *</label>
        <input type="text" class="form-control" name="libelle_quartier" placeholder="Ex: Cocody Angré, Riviera Palmeraie..." required>
      </div>

      <div class="form-group" style="margin-bottom: 20px;">
        <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Code Quartier (Optionnel)</label>
        <input type="text" class="form-control" name="code_quartier" placeholder="Généré automatiquement si vide (ex: QTR-001)">
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" class="btn btn-secondary" onclick="closeAddQuartierDirectModal()">Annuler</button>
        <button type="submit" class="btn btn-primary btn_actions" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
          <i data-lucide="save" style="width: 16px; height: 16px;"></i> Enregistrer le quartier
        </button>
      </div>
    </form>
  </div>
</div>

<script src="<?= RACINE ?>public/json/entities/villes.js?v=4"></script>
<script>
function openAddQuartierModalDirect(code, nom) {
    $('#directVilleCode').val(code);
    $('#directVilleNom').text(nom);
    $('#modalAddQuartierDirect').css('display', 'flex');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeAddQuartierDirectModal() {
    $('#modalAddQuartierDirect').hide();
}

$(document).ready(function() {
    const baseApi = (typeof LINK !== 'undefined') ? LINK : ((typeof RACINE !== 'undefined') ? RACINE : '/admin-lavex/');

    // Ajout direct de quartier
    $('#formAddQuartierDirect').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('.btn_actions');

        if (typeof loading === 'function') loading(btn, true, 'Enregistrement...');

        $.ajax({
            url: baseApi + 'quartier/add',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(rep) {
                if (typeof loading === 'function') loading(btn, false);
                if (rep.status) {
                    if (typeof showToast === 'function') showToast(rep.message || 'Quartier ajouté !', 'success');
                    closeAddQuartierDirectModal();
                    setTimeout(() => window.location.reload(), 700);
                } else {
                    if (typeof showToast === 'function') showToast(rep.message || 'Erreur lors de l\'ajout', 'error');
                }
            },
            error: function(xhr) {
                if (typeof loading === 'function') loading(btn, false);
                let msg = 'Erreur lors de l\'ajout';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                if (typeof showToast === 'function') showToast(msg, 'error');
            }
        });
    });

    // Toggle statut rapide de quartier
    $(document).on('click', '.btnToggleQuartierQuick', function() {
        const id = $(this).data('id');
        if (!id) return;

        showConfirm('Modifier le statut de ce quartier ?', function() {
            $.post(baseApi + 'quartier/changer', { id: id }, function(rep) {
                if (rep.status) {
                    if (typeof showToast === 'function') showToast(rep.message || 'Statut mis à jour', 'success');
                    setTimeout(() => window.location.reload(), 700);
                } else {
                    if (typeof showToast === 'function') showToast(rep.message || 'Erreur', 'error');
                }
            }, 'json').fail(function() {
                if (typeof showToast === 'function') showToast('Erreur serveur', 'error');
            });
        }, 'Statut Quartier', 'Modifier', false);
    });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
