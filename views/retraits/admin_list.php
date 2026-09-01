<?php
require_once __DIR__ . '/../../public/inc/header.php';
$retraits = $retraits ?? [];
$mode = $mode ?? 'sandbox';
?>

<style>
@keyframes spinSlow {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.icon-spin {
  animation: spinSlow 2s linear infinite;
}
.btn-action-approuver {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #059669;
  color: #FFFFFF;
  border: 1px solid #047857;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s ease;
}
.btn-action-approuver:hover {
  background: #047857;
  box-shadow: 0 2px 4px rgba(5, 150, 105, 0.25);
}
.btn-action-rejeter {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #FFFFFF;
  color: #DC2626;
  border: 1px solid #FECACA;
  padding: 6px 10px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s ease;
}
.btn-action-rejeter:hover {
  background: #FEF2F2;
  border-color: #F87171;
}
.btn-action-webhook {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #EFF6FF;
  color: #2563EB;
  border: 1px solid #BFDBFE;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s ease;
}
.btn-action-webhook:hover {
  background: #DBEAFE;
}
.op-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 9px;
  border-radius: 8px;
  font-weight: 800;
  font-size: 11px;
  letter-spacing: 0.3px;
  text-transform: uppercase;
}
.op-pill-wave {
  background: #E0F7FE;
  color: #0284C7;
  border: 1px solid #BAE6FD;
}
.op-pill-orange {
  background: #FFF7ED;
  color: #EA580C;
  border: 1px solid #FFEDD5;
}
.op-pill-mtn {
  background: #FEFCE8;
  color: #CA8A04;
  border: 1px solid #FEF08A;
}
.op-pill-moov {
  background: #EFF6FF;
  color: #2563EB;
  border: 1px solid #BFDBFE;
}
.badge-retrait {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 700;
  white-space: nowrap;
}
@keyframes modalPop {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
        <div>
          <h1 style="margin: 0; font-size: 24px; font-weight: 800; color: #1E293B; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="banknote" style="color: #2563EB;"></i> Retraits des Pressings
          </h1>
          <p class="page-subtitle" style="margin: 4px 0 0; color: #64748B; font-size: 13px;">Supervision, approbation et déclenchement des reversements Mobile Money aux ateliers partenaires</p>
        </div>
        <div>
          <span style="background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; font-weight: 700; font-size: 12px; padding: 6px 12px; border-radius: 20px; display: inline-flex; align-items: center; gap: 6px;">
            <i data-lucide="zap" style="width: 14px; height: 14px;"></i> GeniusPay <?= ucfirst($mode) ?>
          </span>
        </div>
      </div>

      <div class="card" style="border-radius: 16px; border: 1px solid #E2E8F0; padding: 24px; background: #FFFFFF;">
        <div class="table-responsive">
          <table class="table" style="width: 100%;">
            <thead>
              <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B;">Code</th>
                <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B;">Pressing</th>
                <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B;">Date</th>
                <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B;">Montant</th>
                <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B;">Opérateur & Compte</th>
                <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B; text-align: center;">Statut</th>
                <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B; text-align: center;">Actions Admin</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($retraits)): ?>
                <tr>
                  <td colspan="7" style="text-align: center; padding: 36px; color: #94A3B8;">
                    <i data-lucide="inbox" style="width: 36px; height: 36px; margin-bottom: 8px; display: inline-block;"></i>
                    <p style="margin: 0; font-size: 14px;">Aucune demande de retrait enregistrée pour le moment.</p>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($retraits as $r): ?>
                  <?php
                    // Opérateur Badge & Icône
                    $op = strtolower($r['operateur_retrait'] ?? 'wave');
                    $opClass = 'op-pill-wave';
                    $opName = 'WAVE';
                    $opDot = '#0284C7';
                    if ($op === 'orange_money') {
                        $opClass = 'op-pill-orange';
                        $opName = 'ORANGE';
                        $opDot = '#EA580C';
                    } elseif ($op === 'mtn_money') {
                        $opClass = 'op-pill-mtn';
                        $opName = 'MTN';
                        $opDot = '#CA8A04';
                    } elseif ($op === 'moov_money') {
                        $opClass = 'op-pill-moov';
                        $opName = 'MOOV';
                        $opDot = '#2563EB';
                    }

                    // Statut court et propre
                    $st = $r['statut_retrait'] ?? 'en_attente';
                    $stHtml = '';
                    if ($st === 'complete') {
                        $stHtml = '<span class="badge-retrait" style="background:#ECFDF5; color:#059669; border:1px solid #A7F3D0;"><i data-lucide="check-circle-2" style="width:13px; height:13px;"></i> Effectué</span>';
                    } elseif ($st === 'en_attente') {
                        $stHtml = '<span class="badge-retrait" style="background:#FFFBEB; color:#D97706; border:1px solid #FDE68A;"><i data-lucide="clock" style="width:13px; height:13px;"></i> En attente</span>';
                    } elseif ($st === 'approuve') {
                        $stHtml = '<span class="badge-retrait" style="background:#EFF6FF; color:#2563EB; border:1px solid #BFDBFE;"><i data-lucide="refresh-cw" class="icon-spin" style="width:13px; height:13px;"></i> En cours</span>';
                    } elseif ($st === 'rejete') {
                        $stHtml = '<span class="badge-retrait" style="background:#FEF2F2; color:#DC2626; border:1px solid #FECACA;"><i data-lucide="x-circle" style="width:13px; height:13px;"></i> Rejeté</span>';
                    } else {
                        $stHtml = '<span class="badge-retrait" style="background:#FEF2F2; color:#DC2626; border:1px solid #FECACA;"><i data-lucide="alert-triangle" style="width:13px; height:13px;"></i> Échoué</span>';
                    }
                  ?>
                  <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 14px 12px;">
                      <span style="background: #F1F5F9; color: #1E293B; padding: 4px 8px; border-radius: 6px; font-family: monospace; font-weight: 700; font-size: 12px;">
                        <?= htmlspecialchars($r['code_retrait']) ?>
                      </span>
                    </td>
                    <td style="padding: 14px 12px;">
                      <strong style="color: #1E293B; font-size: 14px;"><?= htmlspecialchars($r['libelle_pressing'] ?? $r['pressing_code']) ?></strong>
                      <div style="font-size: 12px; color: #64748B;"><?= htmlspecialchars($r['pressing_code']) ?></div>
                    </td>
                    <td style="padding: 14px 12px; font-size: 13px; color: #64748B;">
                      <?= date('d/m/Y H:i', strtotime($r['created_at_retrait'])) ?>
                    </td>
                    <td style="padding: 14px 12px;">
                      <strong style="font-size: 15px; color: #059669;">
                        <?= number_format($r['montant_demande'], 0, ',', ' ') ?> FCFA
                      </strong>
                    </td>
                    <td style="padding: 14px 12px;">
                      <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="op-pill <?= $opClass ?>">
                          <span style="width: 8px; height: 8px; border-radius: 50%; background: <?= $opDot ?>; display: inline-block;"></span>
                          <?= $opName ?>
                        </span>
                        <span style="font-weight: 700; font-size: 13px; color: #1E293B;"><?= htmlspecialchars($r['telephone_beneficiaire']) ?></span>
                      </div>
                      <?php if (!empty($r['nom_beneficiaire'])): ?>
                        <div style="font-size: 12px; color: #64748B; margin-top: 3px; display: flex; align-items: center; gap: 4px;">
                          <i data-lucide="user" style="width: 12px; height: 12px; color: #94A3B8;"></i> <?= htmlspecialchars($r['nom_beneficiaire']) ?>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 14px 12px; text-align: center;">
                      <?= $stHtml ?>
                    </td>
                    <td style="padding: 14px 12px; text-align: center;">
                      <?php if ($st === 'en_attente'): ?>
                        <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                          <button type="button" class="btn-action-approuver" onclick="ouvrirModalApprobation(<?= $r['id_retrait'] ?>, '<?= $r['code_retrait'] ?>', '<?= htmlspecialchars(addslashes($r['libelle_pressing'] ?? 'Pressing')) ?>', <?= (float)$r['montant_demande'] ?>, '<?= htmlspecialchars($r['operateur_retrait']) ?>', '<?= htmlspecialchars($r['telephone_beneficiaire']) ?>', '<?= htmlspecialchars(addslashes($r['nom_beneficiaire'] ?? '')) ?>')" title="Approuver et déclencher le virement">
                            <i data-lucide="send" style="width: 13px; height: 13px;"></i> Approuver
                          </button>
                          <button type="button" class="btn-action-rejeter" onclick="ouvrirModalRejet(<?= $r['id_retrait'] ?>, '<?= $r['code_retrait'] ?>', '<?= htmlspecialchars(addslashes($r['libelle_pressing'] ?? 'Pressing')) ?>', <?= (float)$r['montant_demande'] ?>)" title="Rejeter la demande">
                            <i data-lucide="x" style="width: 13px; height: 13px;"></i> Rejeter
                          </button>
                        </div>
                      <?php elseif ($st === 'approuve'): ?>
                        <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                          <button type="button" class="btn-action-webhook" onclick="simulerWebhookCashout('<?= $r['code_retrait'] ?>', 'complete')" title="Confirmer le succès via Webhook">
                            <i data-lucide="zap" style="width: 13px; height: 13px;"></i> Webhook
                          </button>
                        </div>
                      <?php else: ?>
                        <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: #94A3B8; font-weight: 600;">
                          <i data-lucide="check" style="width: 13px; height: 13px; color: #10B981;"></i> Clôturé
                        </span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- MODAL D'APPROBATION & VIREMENT MOBILE MONEY -->
<div id="modalApprouver" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 16px;">
  <div style="background: #FFFFFF; width: 100%; max-width: 480px; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; animation: modalPop 0.2s ease-out;">
    <div style="padding: 20px 24px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; background: #F8FAFC;">
      <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #1E293B; display: flex; align-items: center; gap: 10px;">
        <span style="width: 32px; height: 32px; border-radius: 8px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center;">
          <i data-lucide="send" style="width: 18px; height: 18px;"></i>
        </span>
        Approuver le Virement
      </h3>
      <button type="button" onclick="fermerModalApprobation()" style="background: none; border: none; cursor: pointer; color: #94A3B8; font-size: 20px; display: flex; align-items: center;">
        <i data-lucide="x" style="width: 20px; height: 20px;"></i>
      </button>
    </div>

    <div style="padding: 24px;">
      <!-- Récapitulatif du transfert -->
      <div style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 14px; padding: 18px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
          <span style="font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase;">Demande de retrait</span>
          <span id="appr-code" style="font-family: monospace; font-weight: 800; font-size: 12px; background: #FFFFFF; padding: 2px 8px; border-radius: 6px; border: 1px solid #CBD5E1;">#RET-000000</span>
        </div>

        <div style="margin-bottom: 12px;">
          <small style="color: #64748B; font-size: 11px; text-transform: uppercase; font-weight: 700;">Pressing Bénéficiaire</small>
          <div id="appr-pressing" style="font-size: 15px; font-weight: 800; color: #1E293B;">Nom du pressing</div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px dashed #CBD5E1;">
          <div>
            <small style="color: #64748B; font-size: 11px; text-transform: uppercase; font-weight: 700;">Montant à Virer</small>
            <div id="appr-montant" style="font-size: 24px; font-weight: 900; color: #059669;">0 FCFA</div>
          </div>
          <div style="text-align: right;">
            <small style="color: #64748B; font-size: 11px; text-transform: uppercase; font-weight: 700;">Destination</small>
            <div id="appr-dest" style="font-size: 13px; font-weight: 800; color: #1E293B; margin-top: 2px;">WAVE • 0566015516</div>
          </div>
        </div>
      </div>

      <!-- Zone de message d'erreur GeniusPay directement dans la modale -->
      <div id="appr-error-box" style="display: none; background: #FEF2F2; border: 1.5px solid #FCA5A5; color: #991B1B; border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; font-size: 13px; line-height: 1.4;">
        <div style="display: flex; gap: 10px; align-items: flex-start;">
          <i data-lucide="alert-triangle" style="width: 20px; height: 20px; color: #DC2626; flex-shrink: 0; margin-top: 1px;"></i>
          <div>
            <strong style="display: block; font-weight: 800; margin-bottom: 2px; color: #DC2626;">Alerte GeniusPay Payout :</strong>
            <span id="appr-error-msg" style="font-weight: 600;">Message d'erreur...</span>
          </div>
        </div>
      </div>

      <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 12px 14px; margin-bottom: 24px; display: flex; gap: 10px; align-items: flex-start;">
        <i data-lucide="info" style="width: 18px; height: 18px; color: #2563EB; flex-shrink: 0; margin-top: 2px;"></i>
        <p style="margin: 0; font-size: 12px; color: #1E40AF; line-height: 1.4;">
          L'API <strong>GeniusPay Payout</strong> sera immédiatement appelée pour exécuter le virement automatique vers le compte Mobile Money du pressing.
        </p>
      </div>

      <input type="hidden" id="appr-id">

      <div style="display: flex; gap: 12px; justify-content: flex-end;">
        <button type="button" onclick="fermerModalApprobation()" class="btn btn-secondary" style="padding: 10px 18px; font-weight: 700; border-radius: 10px;">
          Annuler
        </button>
        <button type="button" id="btnConfirmApprobation" onclick="executerApprobation()" class="btn btn-primary" style="background: #059669; border-color: #059669; padding: 10px 22px; font-weight: 800; border-radius: 10px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="send" style="width: 16px; height: 16px;"></i>
          <span>Confirmer & Virer</span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL DE REJET DE RETRAIT -->
<div id="modalRejeter" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 16px;">
  <div style="background: #FFFFFF; width: 100%; max-width: 460px; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; animation: modalPop 0.2s ease-out;">
    <div style="padding: 20px 24px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; background: #FEF2F2;">
      <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #DC2626; display: flex; align-items: center; gap: 10px;">
        <span style="width: 32px; height: 32px; border-radius: 8px; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center;">
          <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
        </span>
        Rejeter la Demande
      </h3>
      <button type="button" onclick="fermerModalRejet()" style="background: none; border: none; cursor: pointer; color: #94A3B8; font-size: 20px; display: flex; align-items: center;">
        <i data-lucide="x" style="width: 20px; height: 20px;"></i>
      </button>
    </div>

    <div style="padding: 24px;">
      <p style="margin: 0 0 16px 0; font-size: 13px; color: #64748B;">
        Vous vous apprêtez à rejeter la demande <strong id="rejet-code" style="color: #1E293B;">#RET-000000</strong> du pressing <strong id="rejet-pressing" style="color: #1E293B;">...</strong> (<span id="rejet-montant" style="color: #DC2626; font-weight: 700;">0 FCFA</span>).
      </p>

      <div class="form-group" style="margin-bottom: 18px;">
        <label style="display: block; font-size: 12px; font-weight: 700; color: #1E293B; margin-bottom: 6px;">Motif du rejet (notifié au pressing)</label>
        <select id="rejet-motif-select" class="form-control" onchange="changerMotifRejet(this.value)" style="width: 100%; border-radius: 10px; padding: 10px 12px; border: 1.5px solid #E2E8F0; font-size: 13px; margin-bottom: 8px;">
          <option value="Numéro de téléphone incorrect ou non attribué">Numéro de téléphone incorrect ou non attribué</option>
          <option value="Compte Mobile Money non identifié ou inactif">Compte Mobile Money non identifié ou inactif</option>
          <option value="Informations du titulaire non concordantes">Informations du titulaire non concordantes</option>
          <option value="Autre">Autre motif personnalisé...</option>
        </select>
        <textarea id="rejet-motif-text" rows="2" class="form-control" placeholder="Précisez le motif du rejet..." style="width: 100%; border-radius: 10px; padding: 10px 12px; border: 1.5px solid #E2E8F0; font-size: 13px; display: none;"></textarea>
      </div>

      <small style="display: block; color: #64748B; font-size: 11px; margin-bottom: 20px;">
        ℹ️ Les fonds resteront crédités sur le solde disponible du pressing.
      </small>

      <input type="hidden" id="rejet-id">

      <div style="display: flex; gap: 12px; justify-content: flex-end;">
        <button type="button" onclick="fermerModalRejet()" class="btn btn-secondary" style="padding: 10px 18px; font-weight: 700; border-radius: 10px;">
          Annuler
        </button>
        <button type="button" id="btnConfirmRejet" onclick="executerRejet()" class="btn btn-danger" style="background: #DC2626; border-color: #DC2626; padding: 10px 22px; font-weight: 800; border-radius: 10px; display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="x" style="width: 16px; height: 16px;"></i>
          <span>Rejeter la demande</span>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
let currentApprId = null;
let currentRejetId = null;

function ouvrirModalApprobation(id, code, pressing, montant, operateur, tel, beneficiaire) {
  currentApprId = id;
  $('#appr-id').val(id);
  $('#appr-code').text('#' + code);
  $('#appr-pressing').text(pressing);
  $('#appr-montant').text(Number(montant).toLocaleString('fr-FR') + ' FCFA');
  $('#appr-dest').text(operateur.toUpperCase() + ' • ' + tel + (beneficiaire ? ' (' + beneficiaire + ')' : ''));
  $('#appr-error-box').hide();
  
  $('#modalApprouver').css('display', 'flex');
  if (typeof lucide !== 'undefined') lucide.createIcons();
}

function fermerModalApprobation() {
  $('#modalApprouver').css('display', 'none');
  $('#appr-error-box').hide();
}

function executerApprobation() {
  if (!currentApprId) return;
  const btn = $('#btnConfirmApprobation');
  $('#appr-error-box').hide();
  btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Traitement GeniusPay...');

  $.post(LINK + 'retrait/changerStatut', {
    id_retrait: currentApprId,
    statut: 'approuve'
  }, function(rep) {
    if (rep.status) {
      showToast(rep.message, 'success');
      fermerModalApprobation();
      setTimeout(() => location.reload(), 900);
    } else {
      btn.prop('disabled', false).html('<i data-lucide="send" style="width: 16px; height: 16px;"></i> <span>Confirmer & Virer</span>');
      if (typeof lucide !== 'undefined') lucide.createIcons();
      const errorMsg = rep.message || 'Erreur lors du virement';
      $('#appr-error-msg').text(errorMsg);
      $('#appr-error-box').fadeIn(200);
      showToast(errorMsg, 'error');
    }
  }, 'json').fail(function(xhr) {
    btn.prop('disabled', false).html('<i data-lucide="send" style="width: 16px; height: 16px;"></i> <span>Confirmer & Virer</span>');
    if (typeof lucide !== 'undefined') lucide.createIcons();
    const errorMsg = xhr.responseJSON?.message || 'Erreur de communication avec GeniusPay';
    $('#appr-error-msg').text(errorMsg);
    $('#appr-error-box').fadeIn(200);
    showToast(errorMsg, 'error');
  });
}

function ouvrirModalRejet(id, code, pressing, montant) {
  currentRejetId = id;
  $('#rejet-id').val(id);
  $('#rejet-code').text('#' + code);
  $('#rejet-pressing').text(pressing);
  $('#rejet-montant').text(Number(montant).toLocaleString('fr-FR') + ' FCFA');
  $('#rejet-motif-select').val('Numéro de téléphone incorrect ou non attribué');
  $('#rejet-motif-text').hide().val('');
  
  $('#modalRejeter').css('display', 'flex');
  if (typeof lucide !== 'undefined') lucide.createIcons();
}

function fermerModalRejet() {
  $('#modalRejeter').css('display', 'none');
}

function changerMotifRejet(val) {
  if (val === 'Autre') {
    $('#rejet-motif-text').show().focus();
  } else {
    $('#rejet-motif-text').hide();
  }
}

function executerRejet() {
  if (!currentRejetId) return;
  const selectVal = $('#rejet-motif-select').val();
  const motif = (selectVal === 'Autre') ? $('#rejet-motif-text').val().trim() : selectVal;

  if (!motif) {
    showToast('Veuillez préciser le motif du rejet.', 'warning');
    return;
  }

  const btn = $('#btnConfirmRejet');
  btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Rejet...');

  $.post(LINK + 'retrait/changerStatut', {
    id_retrait: currentRejetId,
    statut: 'rejete',
    motif: motif
  }, function(rep) {
    if (rep.status) {
      showToast(rep.message, 'success');
      fermerModalRejet();
      setTimeout(() => location.reload(), 900);
    } else {
      btn.prop('disabled', false).html('<i data-lucide="x" style="width: 16px; height: 16px;"></i> <span>Rejeter la demande</span>');
      if (typeof lucide !== 'undefined') lucide.createIcons();
      showToast(rep.message || 'Erreur', 'error');
    }
  }, 'json').fail(function() {
    btn.prop('disabled', false).html('<i data-lucide="x" style="width: 16px; height: 16px;"></i> <span>Rejeter la demande</span>');
    if (typeof lucide !== 'undefined') lucide.createIcons();
    showToast('Erreur serveur', 'error');
  });
}

function simulerWebhookCashout(codeRetrait, statut) {
  $.post(LINK + 'retrait/simulerWebhookCashout', {
    code_retrait: codeRetrait,
    statut: statut
  }, function(rep) {
    if (rep.status) {
      showToast(rep.message, 'success');
      setTimeout(() => location.reload(), 800);
    } else {
      showToast(rep.message || 'Erreur', 'error');
    }
  }, 'json').fail(function() {
    showToast('Erreur serveur', 'error');
  });
}

$(document).ready(function() {
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }
});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
