<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$item = $item ?? [];
$client = $client ?? [];
$packSouscrit = $packSouscrit ?? [];
$allPacks = $allPacks ?? [];
$cotisations = $cotisations ?? [];

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
          <?php if (Context::can('COMMERCIAL_COLLECT_COTISATION')): ?>
          <a href="<?= RACINE ?>cautisation-payment/situation/<?= $codeSouscription ?>" class="btn" style="background: #10B981; border-color: #10B981; color: #FFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; text-decoration: none; box-shadow: 0 2px 6px rgba(16,185,129,0.25);">
            <i data-lucide="credit-card" style="width: 18px; height: 18px;"></i> Situation & Encaissement
          </a>
          <?php endif; ?>
          <?php if (Context::can('GESTIONNAIRE_EDIT_SOUSCRIPTION')): ?>
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
          <span style="color: #64748B; font-size: 12px; font-weight: 600;">Cotisation journalière : <?= number_format((float)($item['montant_cotisation_journaliere'] ?? ($item['sum_prix_cotisation_pack'] ?? 0)), 0, ',', ' ') ?> FCFA / j</span>
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
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #F1F5F9; padding-bottom: 12px;">
          <i data-lucide="package" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Packs souscrits dans ce contrat
        </h3>

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
          <?php if (Context::can('COMMERCIAL_COLLECT_COTISATION')): ?>
          <a href="<?= RACINE ?>cautisation-payment/situation/<?= $codeSouscription ?>" class="btn btn-sm" style="background: #10B981; border-color: #10B981; color: #FFF; font-weight: 700; border-radius: 6px; font-size: 12px; text-decoration: none; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px;">
            <i data-lucide="plus-circle" style="width: 14px; height: 14px;"></i> Encaisser une Cotisation
          </a>
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
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
