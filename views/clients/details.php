<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$item = $item ?? [];
$souscriptions = $souscriptions ?? [];
$cotisations = $cotisations ?? [];

$nomClient = trim($item['nom_client'] ?? '');
$prenomClient = trim($item['prenom_client'] ?? '');
$nomComplet = trim($nomClient . ' ' . $prenomClient);
if (empty($nomComplet)) $nomComplet = 'Client Sans Nom';

// Calcul des initiales pour le badge avatar
$words = explode(' ', $nomComplet);
$initials = '';
foreach ($words as $w) {
    if (!empty($w)) $initials .= mb_substr($w, 0, 1, 'UTF-8');
}
$initials = mb_strtoupper(mb_substr($initials, 0, 2, 'UTF-8'), 'UTF-8');
if (empty($initials)) $initials = 'CL';

// KPIs & Statistiques
$totalCotise = 0;
foreach ($cotisations as $c) {
    $st = strtolower(trim($c['statut_cautisation_client'] ?? 'valide'));
    if ($st !== 'ennule' && $st !== 'annule') {
        $totalCotise += (float)($c['montant_cautisation_client'] ?? 0);
    }
}
$nbSouscriptions = count($souscriptions);
$nbCotisations = count($cotisations);
$dernierVersement = !empty($cotisations) ? $cotisations[0] : null;
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE LA FICHE CLIENT -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 16px;">
          <!-- Avatar initiales -->
          <div style="width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 800; box-shadow: 0 4px 14px rgba(30, 58, 95, 0.3); border: 2px solid #FFFFFF;">
            <?= htmlspecialchars($initials) ?>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              <?= htmlspecialchars($nomComplet) ?>
            </h1>
            <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px; flex-wrap: wrap;">
              <span style="font-size: 12px; font-weight: 700; color: #1E3A5F; background: #EEF2FF; padding: 3px 10px; border-radius: 6px; border: 1px solid #C7D2FE;">
                Code : <?= htmlspecialchars($item['code_client'] ?? '-') ?>
              </span>
              <span style="font-size: 12px; font-weight: 700; color: #047857; background: #ECFDF5; padding: 3px 10px; border-radius: 6px; border: 1px solid #A7F3D0;">
                Zone : <?= htmlspecialchars($item['libelle_zone'] ?? 'Non assignée') ?>
              </span>
              <?php if (($item['statut_client'] ?? 'actif') === 'actif'): ?>
                <span style="font-size: 12px; font-weight: 800; color: #059669; background: #D1FAE5; padding: 3px 10px; border-radius: 20px;">
                  ● Client Actif
                </span>
              <?php else: ?>
                <span style="font-size: 12px; font-weight: 800; color: #DC2626; background: #FEE2E2; padding: 3px 10px; border-radius: 20px;">
                  ● Inactif
                </span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>client/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour à la liste
          </a>
          <a href="<?= RACINE ?>client/edition/<?= $encryptedId ?>" class="btn" style="background: #F1F5F9; border: 1px solid #CBD5E1; color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none;">
            <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier le Profil
          </a>
          <a href="<?= RACINE ?>souscription/ressouscription?client_code=<?= urlencode($item['code_client'] ?? '') ?>" class="btn" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);">
            <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i> Ressouscription
          </a>
          <a href="<?= RACINE ?>souscription/wizard" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 20px; text-decoration: none; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Nouvelle Souscription
          </a>
        </div>
      </div>

      <!-- CARTE KPIS & SYNTHÈSE FINANCIÈRE -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- KPI 1 : Total cotisé -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 14px;">
          <div style="width: 46px; height: 46px; border-radius: 12px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="wallet" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Total Cotisé</span>
            <div style="font-size: 18px; font-weight: 800; color: #059669; margin-top: 2px;">
              <?= number_format($totalCotise, 0, ',', ' ') ?> <span style="font-size: 12px;">FCFA</span>
            </div>
          </div>
        </div>

        <!-- KPI 2 : Souscriptions -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 14px;">
          <div style="width: 46px; height: 46px; border-radius: 12px; background: #EEF2FF; color: #4F46E5; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="package" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Souscriptions</span>
            <div style="font-size: 18px; font-weight: 800; color: #1E3A5F; margin-top: 2px;">
              <?= $nbSouscriptions ?> <span style="font-size: 12px; font-weight: 600;">pack(s)</span>
            </div>
          </div>
        </div>

        <!-- KPI 3 : Versements effectués -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 14px;">
          <div style="width: 46px; height: 46px; border-radius: 12px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="receipt" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Versements Effectués</span>
            <div style="font-size: 18px; font-weight: 800; color: #2563EB; margin-top: 2px;">
              <?= $nbCotisations ?> <span style="font-size: 12px; font-weight: 600;">cotisation(s)</span>
            </div>
          </div>
        </div>

        <!-- KPI 4 : Dernier versement -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 14px;">
          <div style="width: 46px; height: 46px; border-radius: 12px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="calendar" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Dernier Versement</span>
            <div style="font-size: 14px; font-weight: 800; color: #0F172A; margin-top: 2px;">
              <?php if ($dernierVersement): ?>
                <?= date('d/m/Y', strtotime($dernierVersement['date_cautisation'] ?? $dernierVersement['created_at_cautisation_client'])) ?>
                <span style="color: #059669; font-size: 12px;">(<?= number_format((float)$dernierVersement['montant_cautisation_client'], 0, ',', ' ') ?> F)</span>
              <?php else: ?>
                <span style="color: #94A3B8; font-style: italic;">Aucun versement</span>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>

      <!-- CARTE 1 : SIGNALÉTIQUE & INFORMATIONS DU CLIENT -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 26px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 12px;">
          <i data-lucide="user-check" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Informations & Coordonnées Client
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px;">
          <div>
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Identité Complète</span>
            <div style="font-size: 16px; font-weight: 800; color: #0F172A; margin-top: 6px;"><?= htmlspecialchars($nomComplet) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
              <i data-lucide="credit-card" style="width: 13px; height: 13px; color: #94A3B8;"></i> CNI : <strong><?= htmlspecialchars($item['cni_client'] ?? $item['numero_cni'] ?? '-') ?></strong>
            </div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Contacts & Joignabilité</span>
            <div style="font-size: 16px; font-weight: 800; color: #059669; margin-top: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="phone" style="width: 15px; height: 15px;"></i> <?= htmlspecialchars($item['telephone_client'] ?? '-') ?>
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
              <i data-lucide="mail" style="width: 13px; height: 13px; color: #94A3B8;"></i> Email : <strong><?= htmlspecialchars($item['email_client'] ?? 'Non renseigné') ?></strong>
            </div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Localisation & Zone</span>
            <div style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin-top: 6px; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="map-pin" style="width: 15px; height: 15px; color: #2563EB;"></i> <?= htmlspecialchars($item['libelle_zone'] ?? '-') ?>
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">
              Résidence : <strong><?= htmlspecialchars($item['lieu_residence_client'] ?? $item['quartier_client'] ?? 'Non renseignée') ?></strong>
            </div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Enregistrement</span>
            <div style="font-size: 13px; font-weight: 700; color: #334155; margin-top: 6px;">
              Créé le : <?= !empty($item['created_at_client']) ? date('d/m/Y à H:i', strtotime($item['created_at_client'])) : '-' ?>
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">
              Commercial : <strong><?= htmlspecialchars($item['user_code'] ?? 'N/A') ?></strong>
            </div>
          </div>
        </div>
      </div>

      <!-- CARTE 2 : LISTE DES SOUSCRIPTIONS DE CE CLIENT -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 26px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); width: 100%; box-sizing: border-box; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #F1F5F9; flex-wrap: wrap; gap: 12px;">
          <div>
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="layers" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Souscriptions de Packs (<?= $nbSouscriptions ?>)
            </h3>
          </div>
         
        </div>

        <?php if (empty($souscriptions)): ?>
          <div style="text-align: center; padding: 40px 20px; color: #94A3B8;">
            <i data-lucide="package-open" style="width: 40px; height: 40px; margin-bottom: 8px; opacity: 0.5;"></i>
            <p style="font-size: 14px; margin: 0; font-style: italic;">Aucune souscription enregistrée pour ce client.</p>
          </div>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                  <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Code Souscription</th>
                  <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Pack Souscrit</th>
                  <th style="padding: 12px 14px; text-align: right; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Cotisation / Jour</th>
                  <th style="padding: 12px 14px; text-align: center; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Jours Cotisés</th>
                  <th style="padding: 12px 14px; text-align: center; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Statut</th>
                  <th style="padding: 12px 14px; text-align: right; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($souscriptions as $s): ?>
                  <?php 
                    $statut = strtolower(trim($s['statut_souscription'] ?? 'valide'));
                    $statutBadge = '<span style="background:#ECFDF5; color:#047857; padding:4px 10px; border-radius:20px; font-weight:800; font-size:11px; border:1px solid #A7F3D0;">En cours</span>';
                    if ($statut === 'solde' || $statut === 'soldé' || $statut === 'soldee') {
                        $statutBadge = '<span style="background:#EFF6FF; color:#1D4ED8; padding:4px 10px; border-radius:20px; font-weight:800; font-size:11px; border:1px solid #BFDBFE;">Soldée</span>';
                    } else if ($statut === 'annule' || $statut === 'annulée') {
                        $statutBadge = '<span style="background:#FEE2E2; color:#B91C1C; padding:4px 10px; border-radius:20px; font-weight:800; font-size:11px; border:1px solid #FCA5A5;">Annulée</span>';
                    }
                  ?>
                  <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.2s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 12px 14px;">
                      <code style="font-weight: 800; color: #1E3A5F; background: #F1F5F9; padding: 4px 8px; border-radius: 6px; font-family: monospace;">
                        <?= htmlspecialchars($s['code_souscription']) ?>
                      </code>
                    </td>
                    <td style="padding: 12px 14px; font-weight: 800; color: #0F172A;">
                      <?= htmlspecialchars($s['libelle_pack'] ?? 'Pack Produit') ?>
                    </td>
                    <td style="padding: 12px 14px; text-align: right; font-weight: 800; color: #059669;">
                      <?= number_format((float)($s['montant_cotisation_journaliere'] ?? $s['prix_cotisation_journaliere'] ?? 0), 0, ',', ' ') ?> FCFA
                    </td>
                    <td style="padding: 12px 14px; text-align: center;">
                      <span style="background: #EEF2FF; color: #4F46E5; padding: 4px 10px; border-radius: 8px; font-weight: 800; font-size: 12px; border: 1px solid #C7D2FE;">
                        <?= (int)($s['nombre_jour_cotise'] ?? 0) ?> / <?= (int)($s['nombre_jour_total'] ?? 0) ?> j
                      </span>
                    </td>
                    <td style="padding: 12px 14px; text-align: center;">
                      <?= $statutBadge ?>
                    </td>
                    <td style="padding: 12px 14px; text-align: right;">
                      <a href="<?= RACINE ?>cautisation-payment/situation?code=<?= htmlspecialchars($s['code_souscription']) ?>" class="btn btn-sm" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; border-radius: 8px; padding: 6px 12px; font-weight: 700; font-size: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                        <i data-lucide="eye" style="width: 14px; height: 14px;"></i> Situation
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- CARTE 3 (NOUVEAU) : TABLEAU DES COTISATIONS DU CLIENT -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 26px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #F1F5F9; flex-wrap: wrap; gap: 12px;">
          <div>
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="receipt" style="width: 18px; height: 18px; color: #059669;"></i> Historique des Cotisations & Versements (<?= $nbCotisations ?>)
            </h3>
          </div>
        </div>

        <?php if (empty($cotisations)): ?>
          <div style="text-align: center; padding: 40px 20px; color: #94A3B8;">
            <i data-lucide="inbox" style="width: 40px; height: 40px; margin-bottom: 8px; opacity: 0.5;"></i>
            <p style="font-size: 14px; margin: 0; font-style: italic;">Aucune cotisation enregistrée pour ce client jusqu'à présent.</p>
          </div>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                  <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Date & Heure</th>
                  <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Code Cotisation</th>
                  <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Code Souscription</th>
                  <th style="padding: 12px 14px; text-align: right; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Montant Versé</th>
                  <th style="padding: 12px 14px; text-align: center; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Jours Payés</th>
                  <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Mode</th>
                  <th style="padding: 12px 14px; text-align: center; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Statut</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($cotisations as $c): ?>
                  <?php 
                    $modeLabel = strtolower(trim($c['mode_paiement'] ?? 'especes'));
                    $modeStyle = 'background: #ECFDF5; color: #047857;';
                    $modeIcon = '💵';
                    if (strpos($modeLabel, 'mobile') !== false) {
                        $modeStyle = 'background: #F3E8FF; color: #7E22CE;';
                        $modeIcon = '📱';
                    } else if (strpos($modeLabel, 'cheque') !== false || strpos($modeLabel, 'chèque') !== false) {
                        $modeStyle = 'background: #FEF3C7; color: #B45309;';
                        $modeIcon = '📝';
                    } else if (strpos($modeLabel, 'virement') !== false) {
                        $modeStyle = 'background: #EFF6FF; color: #1D4ED8;';
                        $modeIcon = '🏦';
                    }

                    $rawStatut = strtolower(trim($c['statut_cautisation_client'] ?? 'valide'));
                    $statutLabel = 'Validé';
                    $statutStyle = 'background:#ECFDF5; color:#047857; border: 1px solid #A7F3D0;';
                    
                    if ($rawStatut === 'en attente' || $rawStatut === 'en_attente') {
                        $statutLabel = 'En attente';
                        $statutStyle = 'background:#FEF3C7; color:#B45309; border: 1px solid #FDE68A;';
                    } else if ($rawStatut === 'annule' || $rawStatut === 'ennule') {
                        $statutLabel = 'Annulé';
                        $statutStyle = 'background:#FEE2E2; color:#B91C1C; border: 1px solid #FCA5A5;';
                    }

                    $dateDisplay = !empty($c['date_cautisation']) ? date('d/m/Y H:i', strtotime($c['date_cautisation'])) : (!empty($c['created_at_cautisation_client']) ? date('d/m/Y H:i', strtotime($c['created_at_cautisation_client'])) : '-');
                  ?>
                  <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.2s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 12px 14px; color: #0F172A; font-weight: 700;">
                      <?= htmlspecialchars($dateDisplay) ?>
                    </td>
                    <td style="padding: 12px 14px;">
                      <code style="font-weight: 800; color: #059669; background: #ECFDF5; padding: 4px 8px; border-radius: 6px; font-family: monospace;">
                        <?= htmlspecialchars($c['code_cautisation_client'] ?? '-') ?>
                      </code>
                    </td>
                    <td style="padding: 12px 14px;">
                      <?php if (!empty($c['souscription_code'] ?? $c['code_souscription'])): ?>
                        <a href="<?= RACINE ?>cautisation-payment/situation?code=<?= htmlspecialchars($c['souscription_code'] ?? $c['code_souscription']) ?>" style="color: #1E3A5F; font-weight: 700; text-decoration: none;">
                          <code style="background: #F1F5F9; padding: 4px 8px; border-radius: 6px; font-family: monospace;">
                            <?= htmlspecialchars($c['souscription_code'] ?? $c['code_souscription']) ?>
                          </code>
                        </a>
                      <?php else: ?>
                        -
                      <?php endif; ?>
                    </td>
                    <td style="padding: 12px 14px; text-align: right; font-weight: 800; color: #059669; font-size: 14px;">
                      <?= number_format((float)($c['montant_cautisation_client'] ?? 0), 0, ',', ' ') ?> FCFA
                    </td>
                    <td style="padding: 12px 14px; text-align: center;">
                      <span style="background:#EEF2FF; color:#4F46E5; padding:4px 10px; border-radius:8px; font-weight:800; font-size:12px; border: 1px solid #C7D2FE;">
                        <?= (int)($c['nombre_jour'] ?? $c['nombre_jour_paye'] ?? 0) ?> j
                      </span>
                    </td>
                    <td style="padding: 12px 14px;">
                      <span style="<?= $modeStyle ?> padding:4px 10px; border-radius:8px; font-weight:700; font-size:12px; display:inline-block;">
                        <?= $modeIcon ?> <?= ucfirst(htmlspecialchars(str_replace('_', ' ', $modeLabel))) ?>
                      </span>
                    </td>
                    <td style="padding: 12px 14px; text-align: center;">
                      <span class="badge" style="<?= $statutStyle ?> padding:5px 12px; border-radius:20px; font-weight:800; font-size:11px; display:inline-block;">
                        <?= $statutLabel ?>
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
<script>
$(document).ready(function() { 
  if (window.lucide) lucide.createIcons(); 
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
