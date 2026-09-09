<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$item = $item ?? [];
$souscription = $souscription ?? [];
$commercial = $commercial ?? [];
$encryptedId = $encryptedId ?? '';

$codeCotisation = $item['code_cautisation_client'] ?? '-';
$montant = (float)($item['montant_cautisation_client'] ?? ($item['montant_cautisation'] ?? 0));
$nbJours = (int)($item['nombre_jour'] ?? ($item['nombre_jour_paye'] ?? 1));
$statut = strtolower(trim($item['statut_cautisation_client'] ?? 'en_attente'));
$modePaiement = $item['mode_paiement'] ?? 'ESPECES';
$dateCotisation = !empty($item['date_cautisation']) ? date('d/m/Y à H:i', strtotime($item['date_cautisation'])) : '-';
$caisseCode = $item['caisse_code'] ?? 'CAISSE-GEN';
$refPaiement = $item['reference_paiement'] ?? '-';

$nomClient = trim($souscription['nom_client'] ?? '');
if (empty($nomClient)) $nomClient = 'Client Non Renseigné';
$codeClient = $souscription['client_code'] ?? ($item['client_code'] ?? '-');
$telephoneClient = $souscription['telephone_client'] ?? '-';
$libellePack = $souscription['libelle_pack'] ?? 'Pack Produit';
$codeSouscription = $item['souscription_code'] ?? ($souscription['code_souscription'] ?? '-');

$nomCommercial = trim(($commercial['nom_user'] ?? '') . ' ' . ($commercial['prenom_user'] ?? ''));
if (empty($nomCommercial)) {
    $nomCommercial = $item['commercial_code'] ?? ($item['user_code'] ?? 'Agent Commercial');
}

// Extraction des initiales du client pour le badge
$initials = '';
$words = explode(' ', trim($nomClient));
foreach ($words as $w) {
    if (!empty($w)) $initials .= mb_substr($w, 0, 1);
}
$initials = mb_substr(strtoupper($initials), 0, 2) ?: 'CL';
?>
<style>
@media print {
  .app-layout > sidebar,
  .main-content > header,
  .page-header-actions,
  .no-print {
    display: none !important;
  }
  .main-content {
    margin: 0 !important;
    padding: 0 !important;
  }
  .content-wrapper {
    padding: 0 !important;
  }
  .card-premium {
    box-shadow: none !important;
    border: 1px solid #CBD5E1 !important;
  }
}
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DU REÇU DE COTISATION -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3);">
            <i data-lucide="receipt" style="width: 26px; height: 26px; color: #FFFFFF;"></i>
          </div>
          <div>
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
              <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
                Reçu de Cotisation
              </h1>
              <code style="font-weight: 800; color: #1E3A5F; font-size: 16px; background: #EFF6FF; padding: 3px 10px; border-radius: 8px; border: 1px solid #BFDBFE; font-family: monospace;">
                <?= htmlspecialchars($codeCotisation) ?>
              </code>
            </div>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Encaissé le <strong><?= $dateCotisation ?></strong> &bull; Caisse : <strong><?= htmlspecialchars($caisseCode) ?></strong>
            </p>
          </div>
        </div>

        <div class="page-header-actions" style="display: flex; gap: 10px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>cautisation-payment/situation/<?= htmlspecialchars($codeSouscription) ?>" class="btn" style="background: #FFFFFF; border: 1px solid #CBD5E1; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Voir Situation
          </a>
          <button type="button" onclick="window.print()" class="btn" style="background: #F8FAFC; border: 1px solid #CBD5E1; color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i data-lucide="printer" style="width: 16px; height: 16px; color: #1E3A5F;"></i> Imprimer Reçu
          </button>
          <?php if (Context::can('FINANCE_EDIT_COTISATION') && !empty($encryptedId)): ?>
          <a href="<?= RACINE ?>cotisation/edition/<?= $encryptedId ?>" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; font-weight: 800; border-radius: 10px; padding: 10px 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
            <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i> Modifier Reçu
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- CARTE 1 : STATUT DU VERSEMENT & RÉSUMÉ CLÉ (KPI) -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- KPI 1 : MONTANT VERSÉ -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Montant Versé</span>
            <div style="width: 36px; height: 36px; border-radius: 10px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="banknote" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div style="font-size: 24px; font-weight: 900; color: #059669; line-height: 1.2;">
            <?= number_format($montant, 0, ',', ' ') ?> <small style="font-size: 14px; font-weight: 700;">FCFA</small>
          </div>
          <span style="font-size: 12px; color: #64748B; font-weight: 600; margin-top: 4px; display: block;">Règlement Client</span>
        </div>

        <!-- KPI 2 : JOURS DE COUVERTURE -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Jours Régularisés</span>
            <div style="width: 36px; height: 36px; border-radius: 10px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="calendar-check" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div style="font-size: 24px; font-weight: 900; color: #2563EB; line-height: 1.2;">
            +<?= $nbJours ?> <small style="font-size: 14px; font-weight: 700;">jour<?= $nbJours > 1 ? 's' : '' ?></small>
          </div>
          <span style="font-size: 12px; color: #64748B; font-weight: 600; margin-top: 4px; display: block;">Couverture Session</span>
        </div>

        <!-- KPI 3 : MODE DE PAIEMENT -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Mode de Règlement</span>
            <div style="width: 36px; height: 36px; border-radius: 10px; background: #F8FAFC; color: #334155; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="credit-card" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div style="font-size: 18px; font-weight: 800; color: #0F172A; text-transform: uppercase; line-height: 1.2;">
            <?= htmlspecialchars($modePaiement) ?>
          </div>
          <span style="font-size: 12px; color: #64748B; font-weight: 600; margin-top: 4px; display: block;">Ref : <?= htmlspecialchars($refPaiement) ?></span>
        </div>

        <!-- KPI 4 : STATUT VALIDATION -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Statut Encaissement</span>
            <div style="width: 36px; height: 36px; border-radius: 10px; background: #F1F5F9; color: #475569; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="shield-check" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div style="margin-top: 4px;">
            <?php if ($statut === 'valide'): ?>
              <span style="display: inline-flex; align-items: center; gap: 6px; background: #ECFDF5; color: #047857; padding: 6px 14px; border-radius: 20px; font-weight: 800; font-size: 12px; border: 1px solid #A7F3D0;">
                <i data-lucide="check-circle-2" style="width: 15px; height: 15px;"></i> Validée (Caisse)
              </span>
            <?php elseif ($statut === 'annule'): ?>
              <span style="display: inline-flex; align-items: center; gap: 6px; background: #FEE2E2; color: #B91C1C; padding: 6px 14px; border-radius: 20px; font-weight: 800; font-size: 12px; border: 1px solid #FCA5A5;">
                <i data-lucide="x-circle" style="width: 15px; height: 15px;"></i> Annulée
              </span>
            <?php else: ?>
              <span style="display: inline-flex; align-items: center; gap: 6px; background: #FEF3C7; color: #B45309; padding: 6px 14px; border-radius: 20px; font-weight: 800; font-size: 12px; border: 1px solid #FDE68A;">
                <i data-lucide="clock" style="width: 15px; height: 15px;"></i> En attente de caisse
              </span>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <!-- CARTE DÉTAILLÉE : FICHE SOUSCRIPTEUR & TRAÇABILITÉ -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);">
        
        <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 12px;">
          <i data-lucide="user-check" style="width: 18px; height: 18px; color: #059669;"></i> Fiche du Souscripteur & Traçabilité
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; margin-bottom: 24px;">
          
          <!-- BLOC CLIENT -->
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
              <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px;">
                <?= htmlspecialchars($initials) ?>
              </div>
              <div>
                <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase;">Client Souscripteur</span>
                <h4 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 2px 0 0 0;"><?= htmlspecialchars($nomClient) ?></h4>
              </div>
            </div>
            <div style="font-size: 13px; color: #475569; display: flex; flex-direction: column; gap: 6px;">
              <div><strong>Code Client :</strong> <code style="font-weight: 700; color: #1E3A5F; background: #E2E8F0; padding: 2px 6px; border-radius: 4px;"><?= htmlspecialchars($codeClient) ?></code></div>
              <div><strong>Contact :</strong> <?= htmlspecialchars($telephoneClient) ?></div>
            </div>
          </div>

          <!-- BLOC CONTRAT SOUSCRIPTION -->
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
              <div style="width: 44px; height: 44px; border-radius: 12px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="package" style="width: 22px; height: 22px;"></i>
              </div>
              <div>
                <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase;">Contrat & Pack Souscrit</span>
                <h4 style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin: 2px 0 0 0;"><?= htmlspecialchars($libellePack) ?></h4>
              </div>
            </div>
            <div style="font-size: 13px; color: #475569; display: flex; flex-direction: column; gap: 6px;">
              <div><strong>Code Contrat :</strong> <code style="font-weight: 700; color: #059669; background: #ECFDF5; padding: 2px 6px; border-radius: 4px; border: 1px solid #A7F3D0;"><?= htmlspecialchars($codeSouscription) ?></code></div>
              <div><a href="<?= RACINE ?>cautisation-payment/situation/<?= htmlspecialchars($codeSouscription) ?>" style="color: #2563EB; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-top: 4px;"><i data-lucide="external-link" style="width: 14px; height: 14px;"></i> Voir fiche situation complète</a></div>
            </div>
          </div>

          <!-- BLOC COMMERCIAL & TRAÇABILITÉ -->
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
              <div style="width: 44px; height: 44px; border-radius: 12px; background: #F0FDF4; color: #166534; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="user-check" style="width: 22px; height: 22px;"></i>
              </div>
              <div>
                <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase;">Commercial Encaisseur</span>
                <h4 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 2px 0 0 0;"><?= htmlspecialchars($nomCommercial) ?></h4>
              </div>
            </div>
            <div style="font-size: 13px; color: #475569; display: flex; flex-direction: column; gap: 6px;">
              <div><strong>Caisse :</strong> <span style="font-weight: 700; color: #334155;"><?= htmlspecialchars($caisseCode) ?></span></div>
              <div><strong>Saisie le :</strong> <?= $dateCotisation ?></div>
            </div>
          </div>

        </div>

      </div>

    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
