<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$item = $item ?? [];
$client = $client ?? [];
$packSouscrit = $packSouscrit ?? [];
$allPacks = $allPacks ?? [];
$cotisations = $cotisations ?? [];
$caisseOuverte = $caisseOuverte ?? false;
$encryptedId = $encryptedId ?? '';
$csrfToken = Validator::generateCsrfToken();

$totalCotise = (float)($item['montant_total_cotise'] ?? 0);
$totalPrevu = (float)($item['montant_total_prevu'] ?? ($item['totale_souscription'] ?? 0));
$tauxProgression = ($totalPrevu > 0) ? min(100, round(($totalCotise / $totalPrevu) * 100, 1)) : 0;
$soldeRestant = max(0, $totalPrevu - $totalCotise);
$nomClient = trim(($client['nom_client'] ?? 'Client Inconnu'));
$codeSouscription = $item['code_souscription'] ?? '-';
$statut = $item['statut_souscription'] ?? 'valide';
$dureeTotal = (int)($item['nombre_jour_total'] ?? ($item['nombre_jour_session'] ?? 0));
$joursCotises = (int)($item['nombre_jour_cotise'] ?? 0);
$joursRestants = max(0, $dureeTotal - $joursCotises);
$prixCotisationJournaliere = (float)($item['montant_cotisation_journaliere'] ?? ($item['sum_prix_cotisation_pack'] ?? 0));
$libelleSession = $item['libelle_session'] ?? '-';
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE LA FICHE SOUSCRIPTION -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <h1 style="font-size: 24px; font-weight: 800; color: #0F172A; margin: 0;">Fiche Contrat Souscription</h1>
            <span style="font-family: monospace; font-size: 15px; font-weight: 800; background: #1E3A5F; color: #FFFFFF; padding: 4px 12px; border-radius: 6px;">
              <?= htmlspecialchars($codeSouscription) ?>
            </span>
          </div>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Détails contractuels, packs souscrits et suivi des encaissements terrain</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <?php if (Context::can('COMMERCIAL_COLLECT_COTISATION') && $statut !== 'solde' && $statut !== 'annule' && ($soldeRestant > 0 || $joursRestants > 0)): ?>
          <button type="button" class="btn" onclick="openPaymentModal()" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #FFF; border: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25); cursor: pointer;">
            <i data-lucide="wallet" style="width: 18px; height: 18px;"></i> Payer une Cotisation
          </button>
          <?php endif; ?>
          <?php if (Context::can('COMMERCIAL_COLLECT_COTISATION')): ?>
          <a href="<?= RACINE ?>cautisation-payment/situation/<?= $codeSouscription ?>" class="btn" style="background: #10B981; border-color: #10B981; color: #FFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; text-decoration: none; box-shadow: 0 2px 6px rgba(16,185,129,0.25);">
            <i data-lucide="credit-card" style="width: 18px; height: 18px;"></i> Situation
          </a>
          <?php endif; ?>
          <?php if ($statut !== 'annule' && $statut !== 'reconduite' && Context::can(['COMMERCIAL_RECONDUIRE_SOUSCRIPTION', 'GESTIONNAIRE_RECONDUIRE_SOUSCRIPTION'])): ?>
          <button type="button" class="btn" onclick="openReconduireModal()" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); color: #FFF; border: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25); cursor: pointer;">
            <i data-lucide="refresh-cw" style="width: 18px; height: 18px;"></i> Reconduire
          </button>
          <?php endif; ?>
          <?php if ($statut !== 'annule' && Context::can('GESTIONNAIRE_ANNULER_SOUSCRIPTION')): ?>
          <button type="button" class="btn" onclick="openAnnulerModal()" style="background: #FEE2E2; border: 1px solid #FECACA; color: #DC2626; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer; transition: all 0.2s ease;">
            <i data-lucide="x-circle" style="width: 18px; height: 18px;"></i> Annuler
          </button>
          <?php endif; ?>
          <?php if (Context::can('GESTIONNAIRE_EDIT_SOUSCRIPTION') && $statut !== 'annule'): ?>
          <a href="<?= RACINE ?>souscription/edition/<?= $encryptedId ?>" class="btn" style="background: #1E3A5F; border-color: #1E3A5F; color: #FFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; text-decoration: none;">
            <i data-lucide="edit-3" style="width: 18px; height: 18px;"></i> Modifier Contrat
          </a>
          <?php endif; ?>
          <a href="<?= RACINE ?>souscription/list" class="btn" style="background: #F1F5F9; border-color: #CBD5E1; color: #475569; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 16px; text-decoration: none;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour
          </a>
        </div>
      </div>

      <!-- HERO BANNER CLIENT PREMIUM (NAVY GLASSMORPHISM) -->
      <div style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); border-radius: 14px; padding: 24px; color: #FFFFFF; margin-bottom: 24px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.12);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
          <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(255, 255, 255, 0.15); display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px); font-size: 22px; font-weight: 800; color: #38BDF8; border: 2px solid rgba(56, 189, 248, 0.3);">
              <?= strtoupper(substr($nomClient, 0, 1)) ?>
            </div>
            <div>
              <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #93C5FD; font-weight: 700;">Client Souscripteur</span>
              <h2 style="font-size: 22px; font-weight: 800; margin: 2px 0 4px 0; color: #FFFFFF;"><?= htmlspecialchars($nomClient) ?></h2>
              <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap; font-size: 13px; color: #CBD5E1;">
                <span><i data-lucide="phone" style="width: 14px; height: 14px; color: #38BDF8; vertical-align: middle;"></i> <?= htmlspecialchars($client['telephone_client'] ?? '-') ?></span>
                <span>&bull;</span>
                <span><i data-lucide="map-pin" style="width: 14px; height: 14px; color: #38BDF8; vertical-align: middle;"></i> <?= htmlspecialchars($client['lieu_residence_client'] ?? '-') ?></span>
                <?php if (!empty($client['email_client'])): ?>
                  <span>&bull;</span>
                  <span><i data-lucide="mail" style="width: 14px; height: 14px; color: #38BDF8; vertical-align: middle;"></i> <?= htmlspecialchars($client['email_client']) ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div style="text-align: right;">
            <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #93C5FD; font-weight: 700; display: block; margin-bottom: 4px;">Statut du Contrat</span>
            <?php if ($statut === 'solde'): ?>
              <span style="display: inline-flex; align-items: center; gap: 6px; background: rgba(16, 185, 129, 0.2); color: #34D399; font-weight: 800; font-size: 13px; padding: 6px 16px; border-radius: 20px; border: 1px solid rgba(52, 211, 153, 0.4);">
                <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i> Contrat Soldé
              </span>
            <?php elseif ($statut === 'reconduite'): ?>
              <span style="display: inline-flex; align-items: center; gap: 6px; background: rgba(245, 158, 11, 0.2); color: #FBBF24; font-weight: 800; font-size: 13px; padding: 6px 16px; border-radius: 20px; border: 1px solid rgba(251, 191, 36, 0.4);">
                <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i> Contrat Reconduit
              </span>
            <?php elseif ($statut === 'annule'): ?>
              <span style="display: inline-flex; align-items: center; gap: 6px; background: rgba(239, 68, 68, 0.2); color: #F87171; font-weight: 800; font-size: 13px; padding: 6px 16px; border-radius: 20px; border: 1px solid rgba(248, 113, 113, 0.4);">
                <i data-lucide="x-circle" style="width: 16px; height: 16px;"></i> Contrat Annulé
              </span>
            <?php else: ?>
              <span style="display: inline-flex; align-items: center; gap: 6px; background: rgba(56, 189, 248, 0.2); color: #38BDF8; font-weight: 800; font-size: 13px; padding: 6px 16px; border-radius: 20px; border: 1px solid rgba(56, 189, 248, 0.4);">
                <i data-lucide="clock" style="width: 16px; height: 16px;"></i> Contrat En Cours
              </span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- GRILLE DES KPIS FINANCIERS DU CONTRAT -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 20px; margin-bottom: 24px;">

        <!-- Card 1: Montant Total Prévu -->
        <div style="background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
            <div>
              <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Montant Total Contractuel</span>
              <h3 style="font-size: 22px; font-weight: 800; color: #1E3A5F; margin: 4px 0 0 0;"><?= number_format($totalPrevu, 0, ',', ' ') ?> <small style="font-size: 12px;">FCFA</small></h3>
            </div>
            <div style="width: 42px; height: 42px; border-radius: 10px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); display: flex; align-items: center; justify-content: center; color: #FFF;">
              <i data-lucide="file-text" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <span style="color: #64748B; font-size: 12px; font-weight: 600;">Cotisation journalière : <?= number_format($prixCotisationJournaliere, 0, ',', ' ') ?> FCFA / j</span>
        </div>

        <!-- Card 2: Total Encaisse / Cotisé -->
        <div style="background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
            <div>
              <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Total Cotisé (Encaissé)</span>
              <h3 style="font-size: 22px; font-weight: 800; color: #15803D; margin: 4px 0 0 0;"><?= number_format($totalCotise, 0, ',', ' ') ?> <small style="font-size: 12px;">FCFA</small></h3>
            </div>
            <div style="width: 42px; height: 42px; border-radius: 10px; background: linear-gradient(135deg, #10B981 0%, #047857 100%); display: flex; align-items: center; justify-content: center; color: #FFF;">
              <i data-lucide="wallet" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div style="display: flex; align-items: center; gap: 8px;">
            <div style="flex: 1; height: 6px; background: #E2E8F0; border-radius: 3px; overflow: hidden;">
              <div style="width: <?= min(100, $tauxProgression) ?>%; background: #10B981; height: 100%;"></div>
            </div>
            <span style="font-size: 12px; font-weight: 800; color: #15803D;"><?= $tauxProgression ?>%</span>
          </div>
        </div>

        <!-- Card 3: Solde Restant -->
        <div style="background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
            <div>
              <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Solde Restant à Cotiser</span>
              <h3 style="font-size: 22px; font-weight: 800; color: <?= $soldeRestant > 0 ? '#DC2626' : '#15803D' ?>; margin: 4px 0 0 0;">
                <?= $soldeRestant > 0 ? number_format($soldeRestant, 0, ',', ' ') . ' <small style="font-size: 12px;">FCFA</small>' : 'Soldé 🎉' ?>
              </h3>
            </div>
            <div style="width: 42px; height: 42px; border-radius: 10px; background: <?= $soldeRestant > 0 ? 'linear-gradient(135deg, #EF4444 0%, #B91C1C 100%)' : 'linear-gradient(135deg, #10B981 0%, #047857 100%)' ?>; display: flex; align-items: center; justify-content: center; color: #FFF;">
              <i data-lucide="<?= $soldeRestant > 0 ? 'alert-circle' : 'check-circle-2' ?>" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <span style="color: <?= $soldeRestant > 0 ? '#DC2626' : '#15803D' ?>; font-size: 12px; font-weight: 700;">
            <?= $soldeRestant > 0 ? 'Créance à recouvrer' : 'Aucun reliquat dû' ?>
          </span>
        </div>

        <!-- Card 4: Progression en Jours -->
        <div style="background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
            <div>
              <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Progression Calendrier</span>
              <h3 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 4px 0 0 0;"><?= $joursCotises ?> / <?= $dureeTotal ?> <small style="font-size: 12px;">jours</small></h3>
            </div>
            <div style="width: 42px; height: 42px; border-radius: 10px; background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%); display: flex; align-items: center; justify-content: center; color: #FFF;">
              <i data-lucide="calendar" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <span style="color: #64748B; font-size: 12px; font-weight: 600;">Jours restants : <strong style="color:#0F172A;"><?= $joursRestants ?> j</strong></span>
        </div>

      </div>

      <!-- CARTE 1 : DETAIL DES PACKS SOUSCRITS -->
      <?php 
      $packsToDisplay = !empty($allPacks) ? $allPacks : (!empty($packSouscrit) ? [$packSouscrit] : []);
      ?>
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
          <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="package" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Packs souscrits dans ce contrat
          </h3>
          <span style="background: #EFF6FF; color: #1D4ED8; font-weight: 800; font-size: 12px; padding: 4px 12px; border-radius: 20px; border: 1px solid #BFDBFE;">
            <?= count($packsToDisplay) ?> Pack<?= count($packsToDisplay) > 1 ? 's' : '' ?>
          </span>
        </div>

        <?php 
        $packsToDisplay = !empty($allPacks) ? $allPacks : (!empty($packSouscrit) ? [$packSouscrit] : []);
        ?>
        <?php if (empty($packsToDisplay)): ?>
          <p style="color: #94A3B8; font-style: italic; text-align: center; padding: 20px 0;">Aucun pack associé à cette souscription.</p>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 14px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                  <th style="padding: 10px 12px;">Pack</th>
                  <th style="padding: 10px 12px;">Catégorie</th>
                  <th style="padding: 10px 12px; text-align: right;">Montant/Jour</th>
                  <th style="padding: 10px 12px; text-align: center;">Durée Session</th>
                  <th style="padding: 10px 12px; text-align: right;">Total Pack</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($packsToDisplay as $p): 
                  $cotisP = (float)($p['prix_cotisation_pack'] ?? 0);
                  $dureeP = (int)($p['nombre_jour_session'] ?? $dureeTotal);
                  $totalP = $cotisP * $dureeP;
                ?>
                  <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 12px; font-weight: 800; color: #0F172A; display: flex; align-items: center; gap: 8px;">
                      <i data-lucide="package-check" style="width: 16px; height: 16px; color: #1E3A5F;"></i>
                      <?= htmlspecialchars($p['libelle_pack'] ?? 'Pack') ?>
                    </td>
                    <td style="padding: 12px;">
                      <span style="font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; background: #1E3A5F; color: #FFF;">
                        <?= htmlspecialchars($p['libelle_categorie_pack'] ?? 'Général') ?>
                      </span>
                    </td>
                    <td style="padding: 12px; text-align: right; font-weight: 700; color: #2563EB;"><?= number_format($cotisP, 0, ',', ' ') ?> FCFA</td>
                    <td style="padding: 12px; text-align: center; font-weight: 600; color: #475569;"><?= $dureeP ?> jours</td>
                    <td style="padding: 12px; text-align: right; font-weight: 800; color: #15803D;"><?= number_format($totalP, 0, ',', ' ') ?> FCFA</td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- CARTE 2 : HISTORIQUE DES COTISATIONS ENCAISSÉES -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9; flex-wrap: wrap; gap: 10px;">
          <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="history" style="width: 18px; height: 18px; color: #10B981;"></i> Historique des cotisations collectées sur le terrain
          </h3>
          <?php if (Context::can('COMMERCIAL_COLLECT_COTISATION') && $statut !== 'solde' && ($soldeRestant > 0 || $joursRestants > 0)): ?>
          <button type="button" onclick="openPaymentModal()" class="btn btn-sm" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); border: none; color: #FFF; font-weight: 700; border-radius: 6px; font-size: 12px; padding: 6px 14px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25);">
            <i data-lucide="plus-circle" style="width: 14px; height: 14px;"></i> Encaisser une Cotisation
          </button>
          <?php endif; ?>
        </div>

        <?php if (empty($cotisations)): ?>
          <div style="text-align: center; padding: 30px 20px; background: #F8FAFC; border-radius: 10px; border: 1px dashed #CBD5E1;">
            <i data-lucide="receipt" style="width: 36px; height: 36px; color: #94A3B8; margin-bottom: 8px;"></i>
            <p style="color: #64748B; font-weight: 600; margin: 0;">Aucune cotisation enregistrée sur ce contrat pour le moment.</p>
          </div>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table display nowrap" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                  <th style="padding: 10px 12px;">Ref. Cotisation</th>
                  <th style="padding: 10px 12px;">Date & Heure</th>
                  <th style="padding: 10px 12px;">Agent Collecteur</th>
                  <th style="padding: 10px 12px;">Mode de Paiement</th>
                  <th style="padding: 10px 12px; text-align: center;">Jours Régularisés</th>
                  <th style="padding: 10px 12px; text-align: right;">Montant Encaissé</th>
                  <th style="padding: 10px 12px; text-align: center;">Statut Validation</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($cotisations as $c): 
                  $statutCotis = $c['statut_cautisation_client'] ?? 'valide';
                  $badgeCotis = 'background:#DCFCE7; color:#15803D; border:1px solid #BBF7D0;';
                  $libCotis = 'Validée';
                  if ($statutCotis === 'en_attente') {
                    $badgeCotis = 'background:#FEF3C7; color:#92400E; border:1px solid #FDE68A;';
                    $libCotis = 'En attente Caisse';
                  } elseif ($statutCotis === 'rejete') {
                    $badgeCotis = 'background:#FEE2E2; color:#B91C1C; border:1px solid #FECACA;';
                    $libCotis = 'Rejetée';
                  }
                ?>
                  <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 10px 12px; font-family: monospace; font-weight: 700; color: #1E3A5F;">
                      <span style="background:#F1F5F9; padding:3px 8px; border-radius:6px; border:1px solid #CBD5E1;">
                        <?= htmlspecialchars($c['code_cautisation_client'] ?? '-') ?>
                      </span>
                    </td>
                    <td style="padding: 10px 12px; color: #334155; font-weight: 600;">
                      <?= !empty($c['date_cautisation']) ? date('d/m/Y H:i', strtotime($c['date_cautisation'])) : '-' ?>
                    </td>
                    <td style="padding: 10px 12px; color: #0F172A; font-weight: 700;">
                      <div style="display:flex; align-items:center; gap:6px;">
                        <i data-lucide="user-check" style="width:14px; height:14px; color:#1E3A5F;"></i>
                        <?= htmlspecialchars(trim(($c['nom_user'] ?? '') . ' ' . ($c['prenom_user'] ?? ''))) ?>
                      </div>
                    </td>
                    <td style="padding: 10px 12px; color: #475569; font-weight: 700;">
                      <span style="background:#EFF6FF; color:#1E3A5F; padding:2px 8px; border-radius:12px; font-size:11px;">
                        <?= htmlspecialchars($c['mode_paiement'] ?? 'ESPECES') ?>
                      </span>
                    </td>
                    <td style="padding: 10px 12px; text-align: center;">
                      <span style="background:#DCFCE7; color:#15803D; padding:3px 10px; border-radius:12px; font-weight:800; font-size:12px;">
                        +<?= (int)($c['nombre_jour'] ?? ($c['nombre_jour_paye'] ?? 1)) ?> j
                      </span>
                    </td>
                    <td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #15803D; font-size: 14px;">
                      <?= number_format((float)($c['montant_cautisation_client'] ?? ($c['montant_cautisation'] ?? 0)), 0, ',', ' ') ?> FCFA
                    </td>
                    <td style="padding: 10px 12px; text-align: center;">
                      <span style="display:inline-block; font-size:11px; font-weight:800; padding:3px 10px; border-radius:20px; <?= $badgeCotis ?>">
                        <?= $libCotis ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<!-- Modal: Formulaire de Paiement Intégré -->
<div class="modal-overlay" id="paymentModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px); padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 900px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">
    <div class="modal-header" style="background: #1E3A5F; color: white; border: none; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
      <h5 class="modal-title" style="font-size: 18px; font-weight: 700; margin: 0; color: white; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="wallet" style="width: 20px; height: 20px;"></i> Formulaire de Paiement Cautisation
      </h5>
      <button type="button" onclick="closePaymentModal()" style="background: none; border: none; color: white; opacity: 0.8; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 4px;">
        <i data-lucide="x" style="width: 20px; height: 20px; color: white;"></i>
      </button>
    </div>

    <div class="modal-body p-0" style="overflow-y: auto; flex: 1;">
      <!-- Partie supérieure: Récapitulatif -->
      <div style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0; padding: 20px 28px;">
        <h6 style="font-size: 14px; font-weight: 700; color: #1E3A5F; margin: 0 0 12px 0; display: flex; align-items: center; gap: 6px;">
          <i data-lucide="file-text" style="width: 16px; height: 16px;"></i> Récapitulatif de la situation
        </h6>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Client</span>
            <div style="font-size: 16px; font-weight: 700; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($nomClient) ?></div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Code Souscription</span>
            <div style="font-size: 15px; font-weight: 700; color: #1E3A5F; margin-top: 4px;">
              <code style="background: #EFF6FF; padding: 3px 8px; border-radius: 4px;"><?= htmlspecialchars($codeSouscription) ?></code>
            </div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Session</span>
            <div style="font-size: 15px; font-weight: 600; color: #334155; margin-top: 4px;"><?= htmlspecialchars($libelleSession) ?></div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Montant total</span>
            <div style="font-size: 18px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= number_format($totalPrevu, 0, ',', ' ') ?> FCFA</div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Déjà payé</span>
            <div style="font-size: 18px; font-weight: 800; color: #15803D; margin-top: 4px;"><?= number_format($totalCotise, 0, ',', ' ') ?> FCFA</div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Restant à payer</span>
            <div style="font-size: 18px; font-weight: 800; color: #DC2626; margin-top: 4px;"><?= number_format($soldeRestant, 0, ',', ' ') ?> FCFA</div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Cotisation / jour</span>
            <div style="font-size: 18px; font-weight: 800; color: #059669; margin-top: 4px;"><?= number_format($prixCotisationJournaliere, 0, ',', ' ') ?> FCFA</div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Jours totaux</span>
            <div style="font-size: 18px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= $dureeTotal ?></div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Jours restants</span>
            <div style="font-size: 18px; font-weight: 800; color: #DC2626; margin-top: 4px;"><?= $joursRestants ?></div>
          </div>
        </div>
      </div>

      <!-- Partie inférieure: Formulaire -->
      <div style="padding: 24px 28px;">
        <h6 style="font-size: 14px; font-weight: 700; color: #1E3A5F; margin: 0 0 16px 0; display: flex; align-items: center; gap: 6px;">
          <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i> Informations de paiement
        </h6>

        <div class="row g-4" style="margin-bottom: 16px; display: flex; gap: 16px; flex-wrap: wrap;">
          <div style="flex: 1; min-width: 240px;">
            <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #1E3A5F; font-size: 13px;">Montant de la cotisation par jour</label>
            <input type="text" id="dailyCotisation" class="form-control" readonly style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 10px 14px; font-size: 15px; font-weight: 700; color: #059669;" value="<?= number_format($prixCotisationJournaliere, 0, ',', ' ') ?> FCFA">
          </div>
          <div style="flex: 1; min-width: 240px;">
            <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #1E3A5F; font-size: 13px;">Mode de paiement</label>
            <select class="form-select" id="paymentMode" style="border-radius: 8px; border: 1px solid #CBD5E1; padding: 10px 14px; font-size: 14px; width: 100%;">
              <option value="especes">Espèces</option>
              <option value="mobile_money">Mobile Money</option>
              <option value="cheque">Chèque</option>
              <option value="virement">Virement bancaire</option>
            </select>
          </div>
        </div>

        <div style="margin-bottom: 16px;">
          <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #1E3A5F; font-size: 13px;">Type de paiement</label>
          <div style="display: flex; gap: 20px; margin-bottom: 4px;">
            <label style="font-size: 13px; color: #334155; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
              <input type="radio" name="type_paiement" id="typeMontant" value="montant" checked style="accent-color: #1E3A5F;">
              Par saisie du montant
            </label>
            <label style="font-size: 13px; color: #334155; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
              <input type="radio" name="type_paiement" id="typeJours" value="jours" style="accent-color: #1E3A5F;">
              Par saisie du nombre de jours
            </label>
          </div>
        </div>

        <div style="margin-bottom: 20px; display: flex; gap: 16px; flex-wrap: wrap;">
          <div style="flex: 1; min-width: 240px;">
            <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #1E3A5F; font-size: 13px;">Montant à verser</label>
            <div style="display: flex;">
              <input type="number" id="montantInput" class="form-control" style="border-radius: 8px 0 0 8px; border: 1px solid #CBD5E1; padding: 10px 14px; font-size: 15px; font-weight: 700; color: #0F172A; width: 100%;" value="<?= (float)$prixCotisationJournaliere ?>" min="0" step="<?= (float)$prixCotisationJournaliere ?>">
              <span style="background: #F8FAFC; border: 1px solid #CBD5E1; border-left: none; border-radius: 0 8px 8px 0; font-weight: 700; color: #64748B; padding: 0 14px; display: flex; align-items: center;">FCFA</span>
            </div>
            <small style="color: #94A3B8; font-size: 11px; display: block; margin-top: 4px;">Doit être un multiple de la cotisation journalière</small>
          </div>
          <div style="flex: 1; min-width: 240px;">
            <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #1E3A5F; font-size: 13px;">Nombre de jours</label>
            <input type="number" id="joursInput" class="form-control" style="border-radius: 8px; border: 1px solid #CBD5E1; padding: 10px 14px; font-size: 15px; font-weight: 700; color: #0F172A;" min="1" value="1">
          </div>
        </div>

        <div style="margin-bottom: 24px;">
          <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #1E3A5F; font-size: 13px;">Date du prochain rendez-vous</label>
          <input type="text" id="nextAppointment" class="form-control" readonly style="background: #EEF2FF; border: 1px solid #C7D2FE; border-radius: 8px; padding: 10px 14px; font-size: 15px; font-weight: 700; color: #1E3A5F;">
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 16px; border-top: 1px solid #E2E8F0;">
          <button type="button" class="btn" onclick="closePaymentModal()" style="background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; border-radius: 8px; padding: 10px 24px; font-weight: 700; cursor: pointer;">
            Annuler
          </button>
          <button type="button" class="btn" id="savePaymentBtn" style="background: #1E3A5F; color: white; border: none; border-radius: 8px; padding: 10px 28px; font-weight: 700; cursor: pointer;">
            <i data-lucide="save" style="width: 18px; height: 18px;"></i> Valider le paiement
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Annuler le Contrat -->
<div class="modal-overlay" id="annulerModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px); padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 540px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; display: flex; flex-direction: column;">
    <div class="modal-header" style="background: #1E3A5F; color: white; border: none; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #EF4444;">
      <h5 class="modal-title" style="font-size: 18px; font-weight: 700; margin: 0; color: white; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="alert-triangle" style="width: 20px; height: 20px; color: #F87171;"></i> Annulation du Contrat
      </h5>
      <button type="button" onclick="closeAnnulerModal()" style="background: none; border: none; color: white; opacity: 0.8; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 4px;">
        <i data-lucide="x" style="width: 20px; height: 20px; color: white;"></i>
      </button>
    </div>

    <div class="modal-body" style="padding: 24px;">
      <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 10px; padding: 14px 16px; margin-bottom: 20px; display: flex; gap: 12px; align-items: flex-start;">
        <i data-lucide="alert-octagon" style="width: 22px; height: 22px; color: #DC2626; flex-shrink: 0; margin-top: 2px;"></i>
        <div style="font-size: 13px; color: #991B1B; line-height: 1.5;">
          <strong>Attention : Cette action est irréversible !</strong><br>
          Le statut du contrat passera à <strong style="color: #7F1D1D;">Annulé</strong>. Les futures cotisations seront définitivement bloquées pour cette souscription.
        </div>
      </div>

      <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px; margin-bottom: 24px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 13px;">
          <div>
            <span style="color: #64748B; font-size: 11px; text-transform: uppercase; font-weight: 700; display: block;">Client</span>
            <strong style="color: #0F172A;"><?= htmlspecialchars($nomClient) ?></strong>
          </div>
          <div>
            <span style="color: #64748B; font-size: 11px; text-transform: uppercase; font-weight: 700; display: block;">N° Contrat</span>
            <code style="background: #EFF6FF; padding: 2px 6px; border-radius: 4px; color: #1E3A5F; font-weight: 700;"><?= htmlspecialchars($codeSouscription) ?></code>
          </div>
          <div>
            <span style="color: #64748B; font-size: 11px; text-transform: uppercase; font-weight: 700; display: block;">Total Cotisé</span>
            <strong style="color: #059669;"><?= number_format($totalCotise, 0, ',', ' ') ?> FCFA</strong>
          </div>
          <div>
            <span style="color: #64748B; font-size: 11px; text-transform: uppercase; font-weight: 700; display: block;">Solde Restant</span>
            <strong style="color: <?= $soldeRestant > 0 ? '#DC2626' : '#059669' ?>;"><?= number_format($soldeRestant, 0, ',', ' ') ?> FCFA</strong>
          </div>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 16px; border-top: 1px solid #E2E8F0;">
        <button type="button" class="btn" onclick="closeAnnulerModal()" style="background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; border-radius: 8px; padding: 10px 20px; font-weight: 700; cursor: pointer;">
          Non, Conserver
        </button>
        <button type="button" class="btn" id="btnConfirmAnnuler" style="background: #DC2626; color: white; border: none; border-radius: 8px; padding: 10px 24px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);">
          <i data-lucide="x-circle" style="width: 18px; height: 18px;"></i> Confirmer l'annulation
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Reconduire le Contrat -->
<div class="modal-overlay" id="reconduireModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px); padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 560px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; display: flex; flex-direction: column;">
    <div class="modal-header" style="background: #1E3A5F; color: white; border: none; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #F59E0B;">
      <h5 class="modal-title" style="font-size: 18px; font-weight: 700; margin: 0; color: white; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="refresh-cw" style="width: 20px; height: 20px; color: #FBBF24;"></i> Reconduction du Contrat
      </h5>
      <button type="button" onclick="closeReconduireModal()" style="background: none; border: none; color: white; opacity: 0.8; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 4px;">
        <i data-lucide="x" style="width: 20px; height: 20px; color: white;"></i>
      </button>
    </div>

    <div class="modal-body" style="padding: 24px;">
      <div style="background: #FEF3C7; border: 1px solid #FDE68A; border-radius: 10px; padding: 14px 16px; margin-bottom: 20px; display: flex; gap: 12px; align-items: flex-start;">
        <i data-lucide="info" style="width: 22px; height: 22px; color: #D97706; flex-shrink: 0; margin-top: 2px;"></i>
        <div style="font-size: 13px; color: #92400E; line-height: 1.5;">
          La reconduction marque le contrat actuel avec le statut <strong>Reconduit</strong> et permet d'initier un nouveau contrat pour ce même souscripteur.
        </div>
      </div>

      <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px; margin-bottom: 24px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 13px;">
          <div>
            <span style="color: #64748B; font-size: 11px; text-transform: uppercase; font-weight: 700; display: block;">Client</span>
            <strong style="color: #0F172A;"><?= htmlspecialchars($nomClient) ?></strong>
          </div>
          <div>
            <span style="color: #64748B; font-size: 11px; text-transform: uppercase; font-weight: 700; display: block;">N° Contrat</span>
            <code style="background: #EFF6FF; padding: 2px 6px; border-radius: 4px; color: #1E3A5F; font-weight: 700;"><?= htmlspecialchars($codeSouscription) ?></code>
          </div>
          <div>
            <span style="color: #64748B; font-size: 11px; text-transform: uppercase; font-weight: 700; display: block;">Total Cotisé</span>
            <strong style="color: #059669;"><?= number_format($totalCotise, 0, ',', ' ') ?> FCFA</strong>
          </div>
          <div>
            <span style="color: #64748B; font-size: 11px; text-transform: uppercase; font-weight: 700; display: block;">Montant Prévu</span>
            <strong style="color: #1E3A5F;"><?= number_format($totalPrevu, 0, ',', ' ') ?> FCFA</strong>
          </div>
        </div>
      </div>

      <div style="display: flex; flex-direction: column; gap: 10px;">
        <button type="button" id="btnConfirmReconduireAndNew" class="btn" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); color: #FFF; border: none; border-radius: 8px; padding: 12px 18px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);">
          <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Marquer Reconduit & Ouvrir Nouveau Contrat
        </button>
        <button type="button" id="btnConfirmReconduireOnly" class="btn" style="background: #F8FAFC; border: 1px solid #CBD5E1; color: #334155; border-radius: 8px; padding: 10px 18px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer;">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Marquer comme Reconduit uniquement
        </button>
        <button type="button" onclick="closeReconduireModal()" class="btn" style="background: transparent; border: none; color: #64748B; font-size: 13px; font-weight: 600; padding: 6px; cursor: pointer;">
          Annuler l'opération
        </button>
      </div>
    </div>
  </div>
</div>

<script>
const PRIX_COTISATION  = <?= $prixCotisationJournaliere ?>;
const MONTANT_RESTANT  = <?= $soldeRestant ?>;
const JOURS_RESTANTS   = <?= $joursRestants ?>;
const CODE_SOUSCRIPTION = '<?= htmlspecialchars($codeSouscription) ?>';
const ENCRYPTED_ID     = '<?= htmlspecialchars($encryptedId) ?>';
const CSRF_TOKEN       = '<?= htmlspecialchars($csrfToken) ?>';
const CAISSE_OUVERTE   = <?= $caisseOuverte ? 'true' : 'false' ?>;

function openPaymentModal() {
    if (!CAISSE_OUVERTE) {
        if (window.toastr) {
            toastr.warning('Veuillez effectuer l\'ouverture de caisse avant de collecter des cotisations.', 'Caisse fermée');
        } else {
            alert('Veuillez effectuer l\'ouverture de caisse avant de collecter des cotisations.');
        }
        return;
    }
    const modal = document.getElementById('paymentModal');
    if (modal) {
        modal.style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }
}

function closePaymentModal() {
    const modal = document.getElementById('paymentModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

document.getElementById('paymentModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closePaymentModal();
    }
});

function openAnnulerModal() {
    const modal = document.getElementById('annulerModal');
    if (modal) {
        modal.style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }
}

function closeAnnulerModal() {
    const modal = document.getElementById('annulerModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

document.getElementById('annulerModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeAnnulerModal();
    }
});

function openReconduireModal() {
    const modal = document.getElementById('reconduireModal');
    if (modal) {
        modal.style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }
}

function closeReconduireModal() {
    const modal = document.getElementById('reconduireModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

document.getElementById('reconduireModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeReconduireModal();
    }
});

function formatCurrency(amount) {
    return Number(amount).toLocaleString('fr-FR') + ' FCFA';
}

function calculateNextDate(jours) {
    const now = new Date();
    const next = new Date(now);
    next.setDate(next.getDate() + (parseInt(jours) || 1));
    const d = String(next.getDate()).padStart(2, '0');
    const m = String(next.getMonth() + 1).padStart(2, '0');
    const y = next.getFullYear();
    return d + '/' + m + '/' + y;
}

$(document).ready(function() {
    if (window.lucide) lucide.createIcons();

    const montantInput = document.getElementById('montantInput');
    const joursInput = document.getElementById('joursInput');
    const nextAppointment = document.getElementById('nextAppointment');

    function updateCalculations() {
        if (!montantInput || !joursInput) return;
        const typeRadio = document.querySelector('input[name="type_paiement"]:checked');
        const type = typeRadio ? typeRadio.value : 'montant';
        let montant, jours;

        if (type === 'montant') {
            montant = parseFloat(montantInput.value) || 0;
            jours = PRIX_COTISATION > 0 ? Math.floor(montant / PRIX_COTISATION) : 0;
            joursInput.value = jours;
        } else {
            jours = parseInt(joursInput.value) || 1;
            montant = jours * PRIX_COTISATION;
            montantInput.value = montant;
        }
        if (nextAppointment) {
            nextAppointment.value = calculateNextDate(jours);
        }
    }

    if (montantInput && joursInput) {
        montantInput.addEventListener('input', updateCalculations);
        joursInput.addEventListener('input', updateCalculations);
        document.getElementById('typeMontant')?.addEventListener('change', updateCalculations);
        document.getElementById('typeJours')?.addEventListener('change', updateCalculations);
        updateCalculations();
    }

    $('#savePaymentBtn').on('click', function() {
        const montant = parseFloat(montantInput.value) || 0;
        const jours = parseInt(joursInput.value) || 0;
        const mode = $('#paymentMode').val();
        const typeRadio = document.querySelector('input[name="type_paiement"]:checked');
        const type = typeRadio ? typeRadio.value : 'montant';

        if (montant <= 0) {
            if (window.toastr) toastr.warning('Le montant doit être supérieur à 0 FCFA.', 'Montant invalide');
            else alert('Le montant doit être supérieur à 0 FCFA.');
            return;
        }
        if (jours <= 0) {
            if (window.toastr) toastr.warning('Le nombre de jours doit être au moins de 1 jour.', 'Nombre de jours invalide');
            else alert('Le nombre de jours doit être au moins de 1 jour.');
            return;
        }
        if (PRIX_COTISATION > 0 && (montant % PRIX_COTISATION) > 0.01) {
            if (window.toastr) toastr.warning('Le montant doit être un multiple de ' + formatCurrency(PRIX_COTISATION) + '.', 'Montant incorrect');
            else alert('Le montant doit être un multiple de ' + formatCurrency(PRIX_COTISATION) + '.');
            return;
        }
        if (montant > MONTANT_RESTANT) {
            if (window.toastr) toastr.warning('Le montant dépasse le solde restant à payer (' + formatCurrency(MONTANT_RESTANT) + ').', 'Plafond dépassé');
            else alert('Le montant dépasse le solde restant à payer (' + formatCurrency(MONTANT_RESTANT) + ').');
            return;
        }
        if (jours > JOURS_RESTANTS) {
            if (window.toastr) toastr.warning('Le nombre de jours dépasse le nombre de jours restants (' + JOURS_RESTANTS + ' jours).', 'Plafond dépassé');
            else alert('Le nombre de jours dépasse le nombre de jours restants (' + JOURS_RESTANTS + ' jours).');
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<i data-lucide="loader-2" class="spinner-border spinner-border-sm"></i> Enregistrement...');

        const formData = new URLSearchParams();
        formData.append('code_souscription', CODE_SOUSCRIPTION);
        formData.append('montant', montant);
        formData.append('nombre_jours', jours);
        formData.append('mode_paiement', mode);
        formData.append('type_paiement', type);

        $.ajax({
            url: '<?= RACINE ?>cautisation-payment/savepayment',
            type: 'POST',
            data: formData.toString(),
            contentType: 'application/x-www-form-urlencoded',
            dataType: 'json',
            success: function(result) {
                btn.prop('disabled', false).html('<i data-lucide="save" style="width: 18px; height: 18px;"></i> Valider le paiement');
                if (result.status === 1) {
                    closePaymentModal();
                    const msgSuccess = (result.message || 'Paiement enregistré avec succès !') +
                      (result.code_cautisation ? ' [Code : ' + result.code_cautisation + ']' : '');
                    if (window.toastr) toastr.success(msgSuccess, 'Paiement effectué');
                    else alert(msgSuccess);
                    setTimeout(function() { location.reload(); }, 1200);
                } else {
                    if (window.toastr) toastr.error(result.message || 'Erreur lors de l\'enregistrement du paiement', 'Échec du paiement');
                    else alert(result.message || 'Erreur lors de l\'enregistrement du paiement');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i data-lucide="save" style="width: 18px; height: 18px;"></i> Valider le paiement');
                let errStr = 'Erreur réseau lors du paiement';
                try {
                    const res = JSON.parse(xhr.responseText);
                    if (res.error) errStr = res.error;
                    if (res.message) errStr = res.message;
                } catch(e){}
                if (window.toastr) toastr.error(errStr, 'Erreur système');
                else alert(errStr);
            }
        });
    });

    // Confirmation Annulation
    $('#btnConfirmAnnuler').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i data-lucide="loader-2" class="spinner-border spinner-border-sm"></i> Annulation...');

        $.ajax({
            url: '<?= RACINE ?>souscription/annuler',
            type: 'POST',
            data: {
                csrf_token: CSRF_TOKEN,
                id: ENCRYPTED_ID,
                code_souscription: CODE_SOUSCRIPTION
            },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            dataType: 'json',
            success: function(result) {
                if (result.status === 1) {
                    closeAnnulerModal();
                    if (window.toastr) toastr.success(result.message || 'Souscription annulée avec succès.', 'Succès');
                    else alert(result.message || 'Souscription annulée avec succès.');
                    setTimeout(function() { location.reload(); }, 1000);
                } else {
                    btn.prop('disabled', false).html('<i data-lucide="x-circle" style="width: 18px; height: 18px;"></i> Confirmer l\'annulation');
                    if (window.lucide) lucide.createIcons();
                    if (window.toastr) toastr.error(result.message || 'Erreur lors de l\'annulation.', 'Erreur');
                    else alert(result.message || 'Erreur lors de l\'annulation.');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i data-lucide="x-circle" style="width: 18px; height: 18px;"></i> Confirmer l\'annulation');
                if (window.lucide) lucide.createIcons();
                let errMsg = 'Erreur serveur lors de l\'annulation.';
                try {
                    const parsed = JSON.parse(xhr.responseText);
                    if (parsed.message) errMsg = parsed.message;
                } catch(e) {}
                if (window.toastr) toastr.error(errMsg, 'Erreur');
                else alert(errMsg);
            }
        });
    });

    // Traitement Reconduction
    function executeReconduction(btn, redirectAfter) {
        const origHtml = btn.html();
        btn.prop('disabled', true).html('<i data-lucide="loader-2" class="spinner-border spinner-border-sm"></i> Traitement...');

        $.ajax({
            url: '<?= RACINE ?>souscription/reconduire',
            type: 'POST',
            data: {
                csrf_token: CSRF_TOKEN,
                id: ENCRYPTED_ID,
                code_souscription: CODE_SOUSCRIPTION
            },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            dataType: 'json',
            success: function(result) {
                if (result.status === 1) {
                    closeReconduireModal();
                    if (window.toastr) toastr.success(result.message || 'Souscription reconduite avec succès.', 'Succès');
                    else alert(result.message || 'Souscription reconduite avec succès.');
                    
                    setTimeout(function() {
                        if (redirectAfter && result.redirect) {
                            window.location.href = result.redirect;
                        } else {
                            location.reload();
                        }
                    }, 1000);
                } else {
                    btn.prop('disabled', false).html(origHtml);
                    if (window.lucide) lucide.createIcons();
                    if (window.toastr) toastr.error(result.message || 'Erreur lors de la reconduction.', 'Erreur');
                    else alert(result.message || 'Erreur lors de la reconduction.');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(origHtml);
                if (window.lucide) lucide.createIcons();
                let errMsg = 'Erreur serveur lors de la reconduction.';
                try {
                    const parsed = JSON.parse(xhr.responseText);
                    if (parsed.message) errMsg = parsed.message;
                } catch(e) {}
                if (window.toastr) toastr.error(errMsg, 'Erreur');
                else alert(errMsg);
            }
        });
    }

    $('#btnConfirmReconduireAndNew').on('click', function() {
        executeReconduction($(this), true);
    });

    $('#btnConfirmReconduireOnly').on('click', function() {
        executeReconduction($(this), false);
    });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
