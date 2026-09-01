<?php
require_once __DIR__ . '/../../public/inc/header.php';

$order        = $order ?? [];
$clients      = $clients ?? [];
$pressings    = $pressings ?? [];
$encryptedId  = $encryptedId ?? '';

$codeCommande = $order['code_commande'] ?? '';
$clientCode   = $order['client_code'] ?? '';
$pressingCode = $order['pressing_code'] ?? '';
$statutSuivi  = $order['statut_suivi_commande'] ?? 'creee';
$statut       = $order['statut_commande'] ?? 'actif';
$typeCmd      = $order['type_commande'] ?? 'detaillee';
$montantTotal = (float)($order['montant_total_commande'] ?? 0);
$fraisCollecte= (float)($order['frais_collecte_commande'] ?? 0);
$fraisLivraison=(float)($order['frais_livraison_commande'] ?? 0);
$remiseCmd    = (float)($order['remise_commande'] ?? 0);
$adresseLiv   = $order['adresse_livraison_commande'] ?? '';
$observation  = $order['observation_commande'] ?? '';
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header" style="margin-bottom: 24px;">
        <div>
          <div style="display: flex; align-items: center; gap: 10px;">
            <h1 style="margin: 0; font-size: 22px; font-weight: 800; color: #1E293B;">Modifier la commande</h1>
            <span style="background: #E2E8F0; color: #334155; padding: 4px 10px; border-radius: 20px; font-weight: 700; font-size: 13px;">
              #<?= htmlspecialchars($codeCommande) ?>
            </span>
          </div>
          <p class="page-subtitle" style="margin: 4px 0 0; color: #64748B; font-size: 13px;">
            Mettez à jour les paramètres, le montant ou le statut de la commande.
          </p>
        </div>
        <div style="display: flex; gap: 8px;">
          <a href="<?= RACINE ?>commande/details/<?= htmlspecialchars($codeCommande) ?>" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
            <i data-lucide="eye" style="width: 16px; height: 16px;"></i> Voir les détails
          </a>
          <a href="<?= RACINE ?>commande/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour à la liste
          </a>
        </div>
      </div>

      <div class="form-card" style="background: #FFF; border-radius: 14px; border: 1px solid #E2E8F0; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); max-width: 900px; margin: 0 auto;">
        <form class="formEditOrder" id="formEditOrder">
          <?= Validator::csrfField() ?>
          <input type="hidden" name="id_commande" value="<?= (int)($order['id_commande'] ?? 0) ?>">

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 20px;">
            <!-- Client -->
            <div>
              <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Client *</label>
              <select name="client_code" required class="form-control" style="width: 100%; padding: 10px 12px; font-size: 14px;">
                <option value="">-- Choisir un client --</option>
                <?php foreach ($clients as $c): ?>
                  <option value="<?= htmlspecialchars($c['code_client'] ?? '') ?>" <?= ($c['code_client'] ?? '') === $clientCode ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nom_client'] ?? 'Client') ?> (<?= htmlspecialchars($c['telephone_client'] ?? '-') ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Étape de suivi -->
            <div>
              <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Étape de suivi *</label>
              <select name="statut_suivi_commande" required class="form-control" style="width: 100%; padding: 10px 12px; font-size: 14px; font-weight: 600;">
                <?php foreach (STATUTS::SUIVI_COMMANDES as $step): ?>
                  <option value="<?= $step ?>" <?= $step === $statutSuivi ? 'selected' : '' ?>>
                    <?= ucfirst(str_replace('_', ' ', $step)) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Adresse de livraison -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Adresse de collecte / livraison *</label>
            <textarea name="adresse_livraison_commande" rows="2" required class="form-control" style="width: 100%; padding: 10px 12px; font-size: 14px;" placeholder="Quartier, rue, repère..."><?= htmlspecialchars($adresseLiv) ?></textarea>
          </div>

          <!-- Ligne des montants -->
          <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; background: #F8FAFC; padding: 16px; border-radius: 10px; border: 1px solid #E2E8F0;">
            <div>
              <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px;">Frais Collecte (FCFA)</label>
              <input type="number" name="frais_collecte_commande" id="edit_frais_col" value="<?= $fraisCollecte ?>" min="0" step="100" class="form-control" style="width: 100%; padding: 8px 10px;">
            </div>
            <div>
              <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px;">Frais Livraison (FCFA)</label>
              <input type="number" name="frais_livraison_commande" id="edit_frais_liv" value="<?= $fraisLivraison ?>" min="0" step="100" class="form-control" style="width: 100%; padding: 8px 10px;">
            </div>
            <div>
              <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px;">Remise (FCFA)</label>
              <input type="number" name="remise_commande" id="edit_remise" value="<?= $remiseCmd ?>" min="0" step="100" class="form-control" style="width: 100%; padding: 8px 10px;">
            </div>
            <div>
              <label style="display: block; font-size: 12px; font-weight: 700; color: #1E293B; margin-bottom: 4px;">Montant Total (FCFA)</label>
              <input type="number" name="montant_total_commande" id="edit_total" value="<?= $montantTotal ?>" min="0" step="100" required class="form-control" style="width: 100%; padding: 8px 10px; font-weight: 700; color: #059669;">
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 18px; margin-bottom: 24px;">
            <!-- Observation -->
            <div>
              <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Instructions / Observations</label>
              <input type="text" name="observation_commande" value="<?= htmlspecialchars($observation) ?>" class="form-control" style="width: 100%; padding: 10px 12px; font-size: 14px;" placeholder="Notes pour le livreur ou l'atelier...">
            </div>

            <!-- Statut d'activation -->
            <div>
              <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Statut</label>
              <select name="statut_commande" class="form-control" style="width: 100%; padding: 10px 12px; font-size: 14px;">
                <option value="actif" <?= $statut === 'actif' ? 'selected' : '' ?>>Actif</option>
                <option value="inactif" <?= $statut === 'inactif' ? 'selected' : '' ?>>Inactif</option>
              </select>
            </div>
          </div>

          <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #E2E8F0; padding-top: 18px;">
            <a href="<?= RACINE ?>commande/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
              Annuler
            </a>
            <button type="submit" class="btn btn-primary btnSubmitEditOrder" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 6px;">
              <i data-lucide="save" style="width: 16px; height: 16px;"></i> Enregistrer les modifications
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  const baseApiUrl = (typeof LINK !== 'undefined') ? LINK : ((typeof RACINE !== 'undefined') ? RACINE : '/admin-lavex/');

  $('#formEditOrder').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const btn = form.find('.btnSubmitEditOrder');

    if (typeof loading === 'function') {
      loading(btn, true, '<i class="fa fa-spinner fa-spin"></i> Enregistrement...');
    }

    $.post(baseApiUrl + 'commande/edit', form.serialize(), function(rep) {
      if (typeof loading === 'function') {
        loading(btn, false, '<i data-lucide="save"></i> Enregistrer les modifications');
      }
      if (typeof showToast === 'function') {
        showToast(rep.message || 'Commande enregistrée', rep.status ? 'success' : 'error');
      }
      if (rep.status) {
        setTimeout(function() {
          window.location.href = baseApiUrl + 'commande/list';
        }, 800);
      }
    }, 'json').fail(function() {
      if (typeof loading === 'function') {
        loading(btn, false, '<i data-lucide="save"></i> Enregistrer les modifications');
      }
      if (typeof showToast === 'function') {
        showToast('Erreur serveur lors de la mise à jour', 'error');
      }
    });
  });
});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
