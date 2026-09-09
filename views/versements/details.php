<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$item = $item ?? [];
$commercial = $commercial ?? [];
$zone = $zone ?? [];
$validatorUser = $validatorUser ?? null;
$canValidate = $canValidate ?? (Context::isFinance() || Context::isAdmin());
$nomCommercial = trim(($commercial['nom_user'] ?? '') . ' ' . ($commercial['prenom_user'] ?? ''));
$nomValidateur = !empty($validatorUser) ? trim(($validatorUser['nom_user'] ?? '') . ' ' . ($validatorUser['prenom_user'] ?? '')) : ($item['user_validate'] ?? '');
$dateValidation = (!empty($item['date_validation']) && $item['date_validation'] !== '1000-01-01 00:00:00') ? date('d/m/Y à H:i', strtotime($item['date_validation'])) : null;
$montant = (float)($item['montant_versement'] ?? 0);
$statut = $item['statut_versement'] ?? 'En attente';
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE LA FICHE VERSEMENT -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="arrow-down-left" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Bordereau Versement : <code style="font-weight: 800; color: #1E3A5F; font-size: 20px; background: #F1F5F9; padding: 3px 10px; border-radius: 6px; border: 1px solid #CBD5E1;"><?= htmlspecialchars($item['code_versement_commercial'] ?? '-') ?></code>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Dépôt de fonds effectué du <strong><?= htmlspecialchars($item['periode_versement_debut'] ?? '-') ?></strong> au <strong><?= htmlspecialchars($item['periode_versement_fin'] ?? '-') ?></strong>
            </p>
          </div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
          <a href="<?= RACINE ?>versement/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour aux versements
          </a>

          <?php if (!Context::isCommercial()): ?>
            <a href="<?= RACINE ?>versement/edition/<?= $encryptedId ?>" class="btn" style="background: #F1F5F9; border: 1px solid #CBD5E1; color: #1E3A5F; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
              <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier
            </a>
          <?php endif; ?>

          <?php if ($canValidate): ?>
            <button type="button" id="btn-open-valider-modal" class="btn" style="background: #059669; color: white; font-weight: 800; border-radius: 10px; padding: 10px 20px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25); cursor: pointer;">
              <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i> Contrôler / Valider
            </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- CARTE DÉTAIL VERSEMENT (NAVY PREMIUM) -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
          <i data-lucide="arrow-down-left" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Informations sur le Dépôt de Fonds
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div style="background: #F8FAFC; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Agent Commercial</span>
            <div style="font-size: 17px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($nomCommercial ?: 'Non renseigné') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px; font-weight: 500;">
              Code : <code style="font-weight: 700; color: #1E3A5F; background: #E2E8F0; padding: 2px 6px; border-radius: 4px;"><?= htmlspecialchars($item['commercial_code'] ?? '-') ?></code>
            </div>
          </div>

          <div style="background: #F8FAFC; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Zone d'Activité</span>
            <div style="font-size: 17px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($zone['libelle_zone'] ?? ($item['zone_code'] ?? '-')) ?></div>
          </div>

          <div style="background: #ECFDF5; border-radius: 12px; padding: 18px; border: 1px solid #A7F3D0;">
            <span style="font-size: 11px; font-weight: 700; color: #047857; text-transform: uppercase; letter-spacing: 0.5px;">Montant Versé</span>
            <div style="font-size: 22px; font-weight: 800; color: #059669; margin-top: 4px;">+<?= number_format($montant, 0, ',', ' ') ?> FCFA</div>
          </div>

          <div style="background: #F8FAFC; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Code Caisse / Référence</span>
            <div style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin-top: 4px; font-family: monospace;"><?= htmlspecialchars($item['caisse_code'] ?? ($item['reference_versement'] ?? 'Aucun')) ?></div>
          </div>

          <div style="background: #F8FAFC; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Statut Validation</span>
            <div>
              <?php if ($statut === 'valide'): ?>
                <span style="display: inline-flex; align-items: center; gap: 6px; background:#ECFDF5; color:#059669; padding:6px 14px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #A7F3D0;">
                  <i data-lucide="check-circle" style="width: 14px; height: 14px;"></i> Validé en Caisse
                </span>
              <?php elseif ($statut === 'ennule' || $statut === 'annule'): ?>
                <span style="display: inline-flex; align-items: center; gap: 6px; background:#FEE2E2; color:#DC2626; padding:6px 14px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #FECACA;">
                  <i data-lucide="x-circle" style="width: 14px; height: 14px;"></i> Annulé
                </span>
              <?php else: ?>
                <span style="display: inline-flex; align-items: center; gap: 6px; background:#FEF3C7; color:#D97706; padding:6px 14px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #FDE68A;">
                  <i data-lucide="clock" style="width: 14px; height: 14px;"></i> En attente
                </span>
              <?php endif; ?>
            </div>
          </div>

          <?php if (!empty($nomValidateur)): ?>
          <div style="background: #F8FAFC; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Validateur Finance</span>
            <div style="font-size: 16px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($nomValidateur) ?></div>
            <?php if ($dateValidation): ?>
              <div style="font-size: 12px; color: #64748B; margin-top: 4px; font-weight: 500;">
                Validé le <?= $dateValidation ?>
              </div>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>

        <?php if (!empty($item['commentaire_validation'])): ?>
        <div style="margin-top: 24px; padding: 16px 20px; background: #F8FAFC; border-left: 4px solid #1E3A5F; border-radius: 8px; font-size: 13px; color: #334155; border: 1px solid #E2E8F0; border-left-width: 4px;">
          <strong style="color: #0F172A; font-weight: 800;">Commentaire de validation comptable :</strong>
          <div style="margin-top: 4px; line-height: 1.5; font-weight: 500;"><?= nl2br(htmlspecialchars($item['commentaire_validation'])) ?></div>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<!-- MODALE DE VALIDATION COMPTABLE (VUE DÉTAIL) -->
<?php if ($canValidate): ?>
<div class="modal-overlay" id="modalValiderVersementDetail" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(5px); padding: 16px;">
  <div class="modal" style="max-width: 540px; width: 100%; background: #FFFFFF; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.3); overflow: hidden; border: 1px solid #E2E8F0; margin: auto;">
    <div class="modal-header" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; padding: 20px 24px; display: flex; justify-content: space-between; align-items: center;">
      <h3 class="modal-title" style="font-weight: 800; font-size: 16px; margin: 0; color: #FFFFFF; display: flex; align-items: center; gap: 10px;">
        <i data-lucide="check-circle" style="width: 20px; height: 20px; color: #10B981;"></i> Contrôle & Validation du Bordereau
      </h3>
      <button type="button" class="modal-close-btn" id="modalValiderDetailClose" style="background: rgba(255,255,255,0.12); border: none; color: #FFFFFF; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px;" title="Fermer">
        <i data-lucide="x" style="width: 18px; height: 18px;"></i>
      </button>
    </div>

    <form id="form-valider-detail" style="margin: 0;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id_versement" value="<?= (int)($item['id_versement'] ?? 0) ?>">
      
      <div class="modal-body" style="padding: 24px;">
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px; margin-bottom: 20px; font-size: 13px;">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="color: #64748B; font-weight: 600;">Montant à valider :</span>
            <strong style="color: #059669; font-size: 18px; font-weight: 900;">+<?= number_format($montant, 0, ',', ' ') ?> FCFA</strong>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
            <span style="color: #64748B; font-weight: 600;">Agent Commercial :</span>
            <strong style="color: #0F172A;"><?= htmlspecialchars($nomCommercial ?: '-') ?></strong>
          </div>
        </div>

        <div class="form-group" style="margin-bottom: 18px;">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
            Décision de validation <span style="color: #EF4444;">*</span>
          </label>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <label style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1.5px solid #A7F3D0; background: #ECFDF5; border-radius: 10px; cursor: pointer; font-weight: 700; font-size: 13px; color: #047857;">
              <input type="radio" name="statut_versement" value="valide" checked style="accent-color: #059669; width: 16px; height: 16px;">
              <span>Valider le versement</span>
            </label>
            <label style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1.5px solid #FECACA; background: #FEF2F2; border-radius: 10px; cursor: pointer; font-weight: 700; font-size: 13px; color: #DC2626;">
              <input type="radio" name="statut_versement" value="annule" style="accent-color: #DC2626; width: 16px; height: 16px;">
              <span>Rejeter / Annuler</span>
            </label>
          </div>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Observations de validation
          </label>
          <textarea name="commentaire_validation" rows="3" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px; font-size: 13px; font-weight: 500; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" placeholder="Ex: Dépôt d'espèces conforme aux cotisations enregistrées."></textarea>
        </div>
      </div>

      <div class="modal-footer" style="background: #F8FAFC; padding: 16px 24px; border-top: 1px solid #E2E8F0; display: flex; justify-content: flex-end; gap: 12px;">
        <button type="button" class="btn modal-close-btn" id="btn-cancel-valider-detail" style="background: #FFFFFF; color: #475569; font-weight: 700; border-radius: 8px; padding: 10px 18px; border: 1px solid #CBD5E1; font-size: 13px; cursor: pointer;">
          Annuler
        </button>
        <button type="submit" id="btn-submit-valider-detail" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; font-weight: 800; border-radius: 8px; padding: 10px 22px; border: none; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
          <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i> Confirmer la Décision
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  const $modal = $('#modalValiderVersementDetail');
  $('#btn-open-valider-modal').on('click', function() {
    $modal.fadeIn(200).css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  });

  $('#modalValiderDetailClose, #btn-cancel-valider-detail').on('click', function() {
    $modal.fadeOut(150);
  });

  $modal.on('click', function(e) {
    if ($(e.target).is($modal)) {
      $modal.fadeOut(150);
    }
  });

  $('#form-valider-detail').on('submit', function(e) {
    e.preventDefault();
    const $btn = $('#btn-submit-valider-detail');
    const origHtml = $btn.html();
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Traitement...');

    $.ajax({
      url: '<?= RACINE ?>versement/valider',
      type: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        $btn.prop('disabled', false).html(origHtml);
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Opération réussie');
          $modal.fadeOut(150);
          setTimeout(function() {
            window.location.reload();
          }, 800);
        } else {
          if (window.toastr) toastr.error(res.message || 'Erreur lors de la validation');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html(origHtml);
        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Erreur réseau ou action non autorisée';
        if (window.toastr) toastr.error(msg);
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
