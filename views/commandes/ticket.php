<?php
$commande = $commande ?? [];
$details = $commande['details'] ?? [];
$paiement = $paiement ?? [];
$modePaiement = $paiement['mode_paiement'] ?? 'especes';
$statutPaiement = $paiement['statut_paiement'] ?? 'en_attente';
$isPaye = ($statutPaiement === 'valide');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Ticket Commande #<?= htmlspecialchars($commande['code_commande'] ?? '') ?> - LAVEX</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Courier New', Courier, monospace, sans-serif; }
    body { background: #F1F5F9; padding: 20px; display: flex; justify-content: center; }
    .ticket-container {
      width: 100%;
      max-width: 380px;
      background: #FFFFFF;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
      border: 1px dashed #CBD5E1;
    }
    .header { text-align: center; border-bottom: 2px dashed #000; padding-bottom: 12px; margin-bottom: 12px; }
    .logo { font-size: 22px; font-weight: 900; letter-spacing: 2px; }
    .pressing-name { font-size: 14px; font-weight: 700; margin-top: 4px; }
    .pressing-info { font-size: 11px; color: #475569; }
    
    .meta-section { font-size: 12px; margin-bottom: 12px; border-bottom: 1px dashed #E2E8F0; padding-bottom: 10px; }
    .meta-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
    
    .table-items { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 12px; }
    .table-items th { text-align: left; border-bottom: 1px solid #000; padding-bottom: 4px; font-size: 11px; }
    .table-items td { padding: 5px 0; border-bottom: 1px dotted #E2E8F0; }
    
    .totals-section { border-top: 1px dashed #000; padding-top: 8px; font-size: 13px; margin-bottom: 14px; }
    .total-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
    .total-main { font-size: 16px; font-weight: 900; border-top: 2px solid #000; padding-top: 6px; margin-top: 6px; }
    
    .payment-badge {
      text-align: center;
      padding: 8px;
      border-radius: 6px;
      font-weight: 800;
      font-size: 12px;
      margin-bottom: 14px;
      text-transform: uppercase;
      <?= $isPaye ? 'background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC;' : 'background: #FEF3C7; color: #B45309; border: 1px solid #FCD34D;' ?>
    }

    .footer-note { text-align: center; font-size: 10px; color: #64748B; border-top: 1px dashed #CBD5E1; padding-top: 10px; }
    
    .actions-bar {
      position: fixed;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 10px;
      background: #FFFFFF;
      padding: 10px 20px;
      border-radius: 30px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
    .btn-print {
      background: #2563EB;
      color: #FFFFFF;
      border: none;
      padding: 10px 20px;
      border-radius: 20px;
      font-weight: 700;
      cursor: pointer;
      font-size: 14px;
    }
    .btn-close {
      background: #F1F5F9;
      color: #1E293B;
      border: none;
      padding: 10px 16px;
      border-radius: 20px;
      font-weight: 600;
      cursor: pointer;
    }

    @media print {
      body { background: #FFFFFF; padding: 0; }
      .ticket-container { box-shadow: none; border: none; max-width: 100%; width: 100%; }
      .actions-bar { display: none !important; }
    }
  </style>
</head>
<body>

  <div class="ticket-container">
    <div class="header">
      <div class="logo">LAVEX</div>
      <div class="pressing-name"><?= htmlspecialchars($commande['libelle_pressing'] ?? 'Pressing Partenaire') ?></div>
      <div class="pressing-info"><?= htmlspecialchars($commande['adresse_pressing'] ?? 'Abidjan, Côte d\'Ivoire') ?></div>
      <div class="pressing-info">Tél : <?= htmlspecialchars($commande['telephone_pressing'] ?? '') ?></div>
    </div>

    <div class="meta-section">
      <div class="meta-row">
        <span>COMMANDE :</span>
        <strong>#<?= htmlspecialchars($commande['code_commande'] ?? '') ?></strong>
      </div>
      <div class="meta-row">
        <span>DATE :</span>
        <span><?= date('d/m/Y H:i', strtotime($commande['created_at_commande'] ?? 'now')) ?></span>
      </div>
      <div class="meta-row">
        <span>CLIENT :</span>
        <strong><?= htmlspecialchars($commande['client_nom'] ?? 'Client') ?></strong>
      </div>
      <div class="meta-row">
        <span>TÉLÉPHONE :</span>
        <span><?= htmlspecialchars($commande['client_telephone'] ?? '') ?></span>
      </div>
      <div class="meta-row">
        <span>LIVRAISON :</span>
        <span><?= htmlspecialchars($commande['adresse_livraison_commande'] ?? $commande['quartier_nom'] ?? 'Abidjan') ?></span>
      </div>
    </div>

    <table class="table-items">
      <thead>
        <tr>
          <th>ARTICLE / SERVICE</th>
          <th style="text-align: center;">QTÉ</th>
          <th style="text-align: right;">TOTAL</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($details)): ?>
          <?php foreach ($details as $d): ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($d['libelle_article'] ?? $d['article_code']) ?></strong><br>
                <small style="color: #64748B;"><?= htmlspecialchars($d['libelle_service'] ?? '') ?></small>
              </td>
              <td style="text-align: center; vertical-align: middle;"><?= (int)$d['quantite_commande_detail'] ?></td>
              <td style="text-align: right; vertical-align: middle; font-weight: 700;">
                <?= number_format($d['sous_total_commande_detail'], 0, ',', ' ') ?> F
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="3" style="text-align: center; padding: 10px;">Commande globale / Colis sans détail</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <?php
      $remiseTicket = (float)($commande['remise_commande'] ?? 0);
      $fraisCollTicket = (float)($commande['frais_collecte_commande'] ?? 0);
      $fraisLivTicket = (float)($commande['frais_livraison_commande'] ?? 0);
      $totalTicket = (float)($commande['montant_total_commande'] ?? 0);
      $subtotalArticlesTicket = 0;
      if (!empty($details)) {
        foreach ($details as $d) {
          $subtotalArticlesTicket += (float)($d['sous_total_commande_detail'] ?? 0);
        }
      }
      if ($subtotalArticlesTicket <= 0) {
        $subtotalArticlesTicket = max(0, $totalTicket + $remiseTicket - $fraisCollTicket - $fraisLivTicket);
      }
    ?>
    <div class="totals-section">
      <div class="total-row">
        <span>Sous-total articles :</span>
        <span><?= number_format($subtotalArticlesTicket, 0, ',', ' ') ?> FCFA</span>
      </div>
      <?php if ($fraisCollTicket > 0 || $fraisLivTicket > 0): ?>
        <div class="total-row">
          <span>Frais collecte & livraison :</span>
          <span><?= number_format($fraisCollTicket + $fraisLivTicket, 0, ',', ' ') ?> FCFA</span>
        </div>
      <?php endif; ?>
      <?php if ($remiseTicket > 0): ?>
        <div class="total-row" style="color: #059669; font-weight: bold;">
          <span>Remise / Code promo :</span>
          <span>-<?= number_format($remiseTicket, 0, ',', ' ') ?> FCFA</span>
        </div>
      <?php endif; ?>
      <div class="total-row total-main">
        <span>TOTAL À PAYER :</span>
        <span><?= number_format($totalTicket, 0, ',', ' ') ?> FCFA</span>
      </div>
    </div>

    <div class="payment-badge">
      <?php if ($isPaye): ?>
        PAYÉ EN LIGNE (<?= strtoupper($modePaiement) ?>)
      <?php else: ?>
        À RÉGLER EN ESPÈCES AU LIVREUR
      <?php endif; ?>
    </div>

    <div class="footer-note">
      <p>Merci pour votre confiance !</p>
      <p>Plateforme LAVEX • www.lavex.ci</p>
      <p>Conservez ce reçu jusqu'à réception de vos vêtements.</p>
    </div>
  </div>

  <div class="actions-bar">
    <button class="btn-print" onclick="window.print()">Imprimer Ticket</button>
    <button class="btn-close" onclick="window.close(); history.back();">Fermer</button>
  </div>

</body>
</html>
