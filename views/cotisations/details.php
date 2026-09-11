<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$item = $item ?? [];
$souscription = $souscription ?? [];
$commercial = $commercial ?? [];
$etablissement = $etablissement ?? [];
$encryptedId = $encryptedId ?? '';

$codeCotisation = $item['code_cautisation_client'] ?? '-';
$montant = (float)($item['montant_cautisation_client'] ?? ($item['montant_cautisation'] ?? 0));
$nbJours = (int)($item['nombre_jour'] ?? ($item['nombre_jour_paye'] ?? 1));
$statut = strtolower(trim($item['statut_cautisation_client'] ?? 'en_attente'));
$modePaiement = !empty($item['mode_paiement']) ? strtoupper($item['mode_paiement']) : 'ESPECES';
$dateCotisationRaw = !empty($item['date_cautisation']) ? $item['date_cautisation'] : ($item['created_at_cautisation_client'] ?? '');
$dateCotisation = !empty($dateCotisationRaw) ? date('d/m/Y à H:i', strtotime($dateCotisationRaw)) : date('d/m/Y');
$caisseCode = !empty($item['caisse_code']) ? $item['caisse_code'] : 'CAISSE-GEN';
$refPaiement = !empty($item['reference_paiement']) ? $item['reference_paiement'] : '-';

$nomClient = trim($souscription['nom_client'] ?? '');
if (empty($nomClient)) $nomClient = 'Client Non Renseigné';
$codeClient = $souscription['client_code'] ?? ($item['client_code'] ?? '-');
$telephoneClient = $souscription['telephone_client'] ?? '-';
$adresseClient = $souscription['adresse_client'] ?? ($souscription['lieu_residence_client'] ?? '-');
$libellePack = $souscription['libelle_pack'] ?? 'Pack Produit';
$codeSouscription = $item['souscription_code'] ?? ($souscription['code_souscription'] ?? '-');

$prixJourPack = (float)($souscription['sum_prix_cotisation_pack'] ?? 0);
$montantTotalPrevu = (float)($souscription['totale_souscription'] ?? 0);
$cumulCotise = (float)($souscription['montant_total_cotise'] ?? $montant);
$soldeRestant = max(0, $montantTotalPrevu - $cumulCotise);

$nomCommercial = trim(($commercial['nom_user'] ?? '') . ' ' . ($commercial['prenom_user'] ?? ''));
if (empty($nomCommercial)) {
    $nomCommercial = $item['commercial_code'] ?? ($item['user_code'] ?? 'Agent Commercial');
}

$etabNom = $etablissement['libelle_etablissement'] ?? ($globalEtablissementNom ?? 'OLIVE SERVICE');
$etabTel = $etablissement['telephone_etablissement'] ?? '';
$etabAdresse = $etablissement['adresse_etablissement'] ?? '';
$etabEmail = $etablissement['email_etablissement'] ?? '';
$rawLogo = !empty($etablissement['logo_etablissement']) ? $etablissement['logo_etablissement'] : ($globalEtablissementLogo ?? '');
$etabLogoSrc = '';
if (!empty($rawLogo)) {
    $etabLogoSrc = (strpos($rawLogo, 'http') === 0) ? $rawLogo : RACINE . ltrim($rawLogo, '/');
}

// Extraction des initiales du client pour le badge
$initials = '';
$words = explode(' ', trim($nomClient));
foreach ($words as $w) {
    if (!empty($w)) $initials .= mb_substr($w, 0, 1);
}
$initials = mb_substr(strtoupper($initials), 0, 2) ?: 'CL';

$photoRecu = $item['photo_recu'] ?? null;
?>

<style>
/* ================= STYLES SPÉCIFIQUES D'IMPRESSION DU REÇU ================= */
.print-receipt-container {
  display: none;
}

@media print {
  @page {
    size: A4 portrait;
    margin: 10mm 12mm 10mm 12mm;
  }

  html, body {
    background: #FFFFFF !important;
    color: #0F172A !important;
    font-size: 10pt !important;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif !important;
    line-height: 1.3 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  /* Masquage des barres de navigation et éléments Web non imprimables */
  aside, .sidebar, #sidebar,
  header, .topbar, header.topbar,
  footer, .footer, #footer,
  .page-header-actions,
  .no-print,
  .web-only-view {
    display: none !important;
  }

  /* Déblocage complet des conteneurs pour une impression pleine page */
  .app-layout, .main-content, .content-wrapper {
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    box-shadow: none !important;
    border: none !important;
    background: #FFFFFF !important;
  }

  .print-receipt-container {
    display: block !important;
    width: 100% !important;
    box-sizing: border-box !important;
  }
}
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- ================= VUE ÉCRAN WEB ================= -->
      <div class="web-only-view">
        
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
            <a href="<?= RACINE ?>cautisation-payment/situation/<?= htmlspecialchars($codeSouscription) ?>" class="btn" style="background: #FFFFFF; border: 1px solid #CBD5E1; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
              <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Voir Situation
            </a>
            <button type="button" onclick="window.print()" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; border: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 800; border-radius: 10px; padding: 10px 20px; cursor: pointer; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.25);">
              <i data-lucide="printer" style="width: 16px; height: 16px; color: #FFFFFF;"></i> Imprimer le Reçu
            </button>
          </div>
        </div>

        <!-- CARTE 1 : STATUT DU VERSEMENT & RÉSUMÉ CLÉ (KPI) -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
          
          <!-- KPI 1 : MONTANT VERSÉ -->
          <div class="card-premium" style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
              <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Montant Encaissé</span>
              <div style="width: 36px; height: 36px; border-radius: 10px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="banknote" style="width: 20px; height: 20px;"></i>
              </div>
            </div>
            <div style="font-size: 24px; font-weight: 900; color: #059669; line-height: 1.2;">
              <?= number_format($montant, 0, ',', ' ') ?> <small style="font-size: 14px; font-weight: 700;">FCFA</small>
            </div>
            <span style="font-size: 12px; color: #64748B; font-weight: 600; margin-top: 4px; display: block;">Versement effectué par le client</span>
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
            <span style="font-size: 12px; color: #64748B; font-weight: 600; margin-top: 4px; display: block;">Couverture du contrat</span>
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
            <span style="font-size: 12px; color: #64748B; font-weight: 600; margin-top: 4px; display: block;">Réf : <?= htmlspecialchars($refPaiement) ?></span>
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
        <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); margin-bottom: 24px;">
          
          <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 12px;">
            <i data-lucide="user-check" style="width: 18px; height: 18px; color: #059669;"></i> Fiche du Souscripteur & Traçabilité
          </h3>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px;">
            
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
                <div><strong>Adresse :</strong> <?= htmlspecialchars($adresseClient) ?></div>
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
                <div><strong>Total Prévu :</strong> <?= number_format($montantTotalPrevu, 0, ',', ' ') ?> FCFA</div>
                <div><strong>Solde Restant :</strong> <strong style="color: #D97706;"><?= number_format($soldeRestant, 0, ',', ' ') ?> FCFA</strong></div>
                <div><a href="<?= RACINE ?>cautisation-payment/situation/<?= htmlspecialchars($codeSouscription) ?>" style="color: #2563EB; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-top: 4px;"><i data-lucide="external-link" style="width: 14px; height: 14px;"></i> Voir situation complète</a></div>
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
                <div><strong>Code Caisse :</strong> <span style="font-weight: 700; color: #334155;"><?= htmlspecialchars($caisseCode) ?></span></div>
                <div><strong>Saisie le :</strong> <?= $dateCotisation ?></div>
                <div><strong>Mode de Règlement :</strong> <?= htmlspecialchars($modePaiement) ?></div>
              </div>
            </div>

          </div>

        </div>

        <?php if (!empty($photoRecu)): ?>
          <!-- PIÈCE JUSTIFICATIVE PHOTO DU REÇU -->
          <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);">
            <h4 style="font-size: 13px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 16px 0;">Photo du Reçu Papier Joine</h4>
            <div style="max-width: 320px; border-radius: 12px; overflow: hidden; border: 1px solid #E2E8F0; background: #F8FAFC;">
              <img src="<?= RACINE ?>public/assets/images/recus/<?= htmlspecialchars($photoRecu) ?>" alt="Reçu scan" style="width: 100%; display: block; object-fit: contain;">
            </div>
          </div>
        <?php endif; ?>

      </div>

      <!-- ================= VUE D'IMPRESSION DÉDIÉE (PRINT ONLY) ================= -->
      <div class="print-receipt-container">
        
        <!-- BON D'ENCAISSEMENT FORMEL A4 / MARGE OPTIMISÉE -->
        <div style="border: 2px solid #0F172A; border-radius: 10px; padding: 20px; background: #FFFFFF; position: relative;">
          
          <!-- FILIGRANE DE SÉCURITÉ -->
          <div style="position: absolute; top: 40%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 60pt; font-weight: 900; color: rgba(15, 23, 42, 0.04); text-transform: uppercase; pointer-events: none; white-space: nowrap;">
            <?= $statut === 'valide' ? 'ENCAISSÉ - VALIDÉ' : 'PAYÉ CLIENT' ?>
          </div>

          <!-- EN-TÊTE OFFICIEL DE L'ÉTABLISSEMENT -->
          <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0F172A; padding-bottom: 14px; margin-bottom: 16px; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 14px;">
              <?php if (!empty($etabLogoSrc)): ?>
                <img src="<?= htmlspecialchars($etabLogoSrc) ?>" alt="Logo Etablissement" style="max-height: 60px; max-width: 110px; object-fit: contain;">
              <?php else: ?>
                <div style="width: 50px; height: 50px; border-radius: 8px; background: #1E3A5F; color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 20px;">
                  OS
                </div>
              <?php endif; ?>
              <div>
                <h2 style="font-size: 16px; font-weight: 900; color: #0F172A; margin: 0 0 2px 0; text-transform: uppercase; letter-spacing: 0.5px;">
                  <?= htmlspecialchars($etabNom) ?>
                </h2>
                <?php if (!empty($etabAdresse)): ?>
                  <div style="font-size: 9.5pt; color: #475569; margin-bottom: 1px;"><?= htmlspecialchars($etabAdresse) ?></div>
                <?php endif; ?>
                <?php if (!empty($etabTel)): ?>
                  <div style="font-size: 9.5pt; color: #475569;">Tél : <strong><?= htmlspecialchars($etabTel) ?></strong> <?= !empty($etabEmail) ? '&bull; Email : ' . htmlspecialchars($etabEmail) : '' ?></div>
                <?php endif; ?>
              </div>
            </div>

            <div style="text-align: right;">
              <div style="display: inline-block; background: #0F172A; color: #FFFFFF; padding: 6px 14px; border-radius: 6px; font-weight: 900; font-size: 11pt; letter-spacing: 1px; text-transform: uppercase;">
                REÇU DE COTISATION
              </div>
              <div style="font-size: 11pt; font-weight: 900; color: #1E3A5F; margin-top: 6px; font-family: monospace;">
                N° : <?= htmlspecialchars($codeCotisation) ?>
              </div>
              <div style="font-size: 9pt; color: #64748B; margin-top: 2px;">
                Date : <strong><?= $dateCotisation ?></strong>
              </div>
            </div>
          </div>

          <!-- GRILLE CLIENT & CONTRAT -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            
            <div style="border: 1px solid #CBD5E1; border-radius: 8px; padding: 12px; background: #F8FAFC;">
              <div style="font-size: 9pt; font-weight: 800; color: #64748B; text-transform: uppercase; border-bottom: 1px solid #E2E8F0; padding-bottom: 4px; margin-bottom: 8px;">
                Informations du Client / Souscripteur
              </div>
              <div style="font-size: 12pt; font-weight: 900; color: #0F172A; margin-bottom: 4px;">
                <?= htmlspecialchars($nomClient) ?>
              </div>
              <div style="font-size: 9.5pt; color: #334155; display: flex; flex-direction: column; gap: 3px;">
                <div>Code Client : <strong><?= htmlspecialchars($codeClient) ?></strong></div>
                <div>Téléphone : <strong><?= htmlspecialchars($telephoneClient) ?></strong></div>
                <div>Adresse / Résidence : <?= htmlspecialchars($adresseClient) ?></div>
              </div>
            </div>

            <div style="border: 1px solid #CBD5E1; border-radius: 8px; padding: 12px; background: #F8FAFC;">
              <div style="font-size: 9pt; font-weight: 800; color: #64748B; text-transform: uppercase; border-bottom: 1px solid #E2E8F0; padding-bottom: 4px; margin-bottom: 8px;">
                Détails du Contrat & Pack
              </div>
              <div style="font-size: 12pt; font-weight: 900; color: #1E3A5F; margin-bottom: 4px;">
                <?= htmlspecialchars($libellePack) ?>
              </div>
              <div style="font-size: 9.5pt; color: #334155; display: flex; flex-direction: column; gap: 3px;">
                <div>Code Souscription : <strong><?= htmlspecialchars($codeSouscription) ?></strong></div>
                <div>Cotisation Journalière : <strong><?= number_format($prixJourPack, 0, ',', ' ') ?> FCFA / jour</strong></div>
                <div>Zone Commerciale : <?= htmlspecialchars($souscription['libelle_zone'] ?? '-') ?></div>
              </div>
            </div>

          </div>

          <!-- TABLEAU DÉTAIL ENCAISSEMENT -->
          <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 10pt;">
            <thead>
              <tr style="background: #0F172A; color: #FFFFFF; text-align: left;">
                <th style="padding: 8px 10px; font-weight: 800; border: 1px solid #0F172A;">Désignation / Opération</th>
                <th style="padding: 8px 10px; text-align: center; font-weight: 800; border: 1px solid #0F172A;">Jours Régularisés</th>
                <th style="padding: 8px 10px; text-align: center; font-weight: 800; border: 1px solid #0F172A;">Mode Règlement</th>
                <th style="padding: 8px 10px; text-align: right; font-weight: 800; border: 1px solid #0F172A;">Montant Versé</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td style="padding: 10px; border: 1px solid #CBD5E1; font-weight: 700; color: #0F172A;">
                  Versement Cotisation Client (Pack <?= htmlspecialchars($libellePack) ?>)
                  <?php if (!empty($refPaiement) && $refPaiement !== '-'): ?>
                    <div style="font-size: 8.5pt; color: #64748B; font-weight: normal; margin-top: 2px;">Réf : <?= htmlspecialchars($refPaiement) ?></div>
                  <?php endif; ?>
                </td>
                <td style="padding: 10px; border: 1px solid #CBD5E1; text-align: center; font-weight: 800; color: #1E3A5F;">
                  <?= $nbJours ?> jour<?= $nbJours > 1 ? 's' : '' ?>
                </td>
                <td style="padding: 10px; border: 1px solid #CBD5E1; text-align: center; font-weight: 700; text-transform: uppercase;">
                  <?= htmlspecialchars($modePaiement) ?>
                </td>
                <td style="padding: 10px; border: 1px solid #CBD5E1; text-align: right; font-weight: 900; font-size: 12pt; color: #059669;">
                  <?= number_format($montant, 0, ',', ' ') ?> FCFA
                </td>
              </tr>
            </tbody>
          </table>

          <!-- RÉSUMÉ FINANCIER DU CONTRAT EN DUPLEX -->
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; gap: 16px;">
            
            <div style="flex: 1; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px; background: #F8FAFC;">
              <div style="font-size: 9pt; font-weight: 800; color: #1E3A5F; text-transform: uppercase; margin-bottom: 6px;">
                Situation Financière du Contrat
              </div>
              <table style="width: 100%; font-size: 9pt; border-collapse: collapse;">
                <tr>
                  <td style="padding: 2px 0; color: #64748B;">Montant Total Prévu :</td>
                  <td style="padding: 2px 0; text-align: right; font-weight: 800; color: #0F172A;"><?= number_format($montantTotalPrevu, 0, ',', ' ') ?> FCFA</td>
                </tr>
                <tr>
                  <td style="padding: 2px 0; color: #64748B;">Total Cotisé à ce jour :</td>
                  <td style="padding: 2px 0; text-align: right; font-weight: 800; color: #059669;"><?= number_format($cumulCotise, 0, ',', ' ') ?> FCFA</td>
                </tr>
                <tr style="border-top: 1px solid #CBD5E1;">
                  <td style="padding: 4px 0 0 0; font-weight: 800; color: #0F172A;">Solde Restant à Cotiser :</td>
                  <td style="padding: 4px 0 0 0; text-align: right; font-weight: 900; color: #D97706; font-size: 10.5pt;"><?= number_format($soldeRestant, 0, ',', ' ') ?> FCFA</td>
                </tr>
              </table>
            </div>

            <div style="width: 220px; border: 2px solid #059669; border-radius: 8px; padding: 10px; background: #ECFDF5; text-align: center;">
              <span style="font-size: 8.5pt; font-weight: 800; color: #047857; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 2px;">
                Net Encaissé Ce Jour
              </span>
              <div style="font-size: 16pt; font-weight: 900; color: #059669;">
                <?= number_format($montant, 0, ',', ' ') ?> FCFA
              </div>
              <span style="font-size: 8pt; color: #059669; font-weight: 700;">
                (<?= $statut === 'valide' ? 'Validé par la Caisse' : 'Saisie Commercial' ?>)
              </span>
            </div>

          </div>

          <!-- SIGNATURES ET CACHET -->
          <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; text-align: center; margin-top: 24px; padding-top: 12px; border-top: 1px solid #CBD5E1;">
            
            <div>
              <div style="font-size: 8.5pt; font-weight: 800; color: #64748B; text-transform: uppercase; margin-bottom: 40px;">
                Le Commercial Encaisseur
              </div>
              <div style="font-size: 9.5pt; font-weight: 800; color: #0F172A;"><?= htmlspecialchars($nomCommercial) ?></div>
            </div>

            <div>
              <div style="font-size: 8.5pt; font-weight: 800; color: #64748B; text-transform: uppercase; margin-bottom: 40px;">
                Le Client Souscripteur
              </div>
              <div style="font-size: 9.5pt; font-weight: 800; color: #0F172A;"><?= htmlspecialchars($nomClient) ?></div>
            </div>

            <div>
              <div style="font-size: 8.5pt; font-weight: 800; color: #64748B; text-transform: uppercase; margin-bottom: 40px;">
                Cachet & Signature Caisse
              </div>
              <div style="font-size: 8.5pt; font-style: italic; color: #94A3B8;"><?= htmlspecialchars($etabNom) ?></div>
            </div>

          </div>

          <!-- PIED DE PAGE IMPRESSION -->
          <div style="text-align: center; font-size: 8pt; color: #94A3B8; margin-top: 20px; border-top: 1px dashed #E2E8F0; padding-top: 8px;">
            Ce reçu est généré automatiquement par <?= htmlspecialchars($etabNom) ?> le <?= date('d/m/Y à H:i:s') ?> &bull; Document officiel d'encaissement client.
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
