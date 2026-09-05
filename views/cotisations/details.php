<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$item = $item ?? [];
$souscription = $souscription ?? [];
$nomClient = trim(($souscription['nom_client'] ?? '') . ' ' . ($souscription['prenom_client'] ?? ''));
$montant = (float)($item['montant_cautisation'] ?? 0);
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DU REÇU DE COTISATION -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="receipt" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Reçu de Cotisation : <code style="font-weight: 800; color: #1E3A5F; font-size: 18px; background: #F1F5F9; padding: 2px 10px; border-radius: 8px; border: 1px solid #CBD5E1;"><?= htmlspecialchars($item['code_cautisation_client'] ?? '-') ?></code>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Versement encaissé le <strong><?= !empty($item['date_cautisation']) ? date('d/m/Y', strtotime($item['date_cautisation'])) : '-' ?></strong>
            </p>
          </div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>cotisation/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour au journal
          </a>
          <a href="<?= RACINE ?>cotisation/edition/<?= $encryptedId ?>" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 10px 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
            <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier Reçu
          </a>
        </div>
      </div>

      <!-- CARTE SYNTHÈSE DU PAIEMENT -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
          <i data-lucide="coins" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Détails du Règlement Client
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px;">
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Client Souscripteur</span>
            <div style="font-size: 17px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($nomClient ?: 'Client Non Renseigné') ?></div>
            <div style="font-size: 13px; color: #64748B; margin-top: 2px; font-weight: 500;">Tél : <?= htmlspecialchars($souscription['telephone_client'] ?? '-') ?></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Pack Produit</span>
            <div style="font-size: 17px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= htmlspecialchars($souscription['libelle_pack'] ?? 'Pack') ?></div>
            <div style="font-size: 13px; color: #64748B; margin-top: 2px; font-weight: 500;">Réf Contrat : <code style="font-weight: 700; color: #1E3A5F; background: #F1F5F9; padding: 2px 6px; border-radius: 4px;"><?= htmlspecialchars($item['souscription_code'] ?? '-') ?></code></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Montant Versé</span>
            <div style="font-size: 22px; font-weight: 800; color: #059669; margin-top: 4px;"><?= number_format($montant, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 13px; color: #64748B; margin-top: 2px; font-weight: 600;">Jours Régularisés : <strong style="color: #1E3A5F;">+<?= (int)($item['nombre_jour_paye'] ?? 1) ?> j</strong></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Mode & Réf Transaction</span>
            <div style="font-size: 15px; font-weight: 800; color: #0F172A; margin-top: 4px; text-transform: uppercase;"><?= htmlspecialchars($item['mode_paiement'] ?? 'Espèces') ?></div>
            <div style="font-size: 13px; color: #64748B; margin-top: 2px; font-weight: 500;">Réf : <code style="font-weight: 700; color: #1E3A5F; background: #F1F5F9; padding: 2px 6px; border-radius: 4px;"><?= htmlspecialchars($item['reference_paiement'] ?? 'Aucune') ?></code></div>
          </div>
        </div>

        <div style="margin-top: 28px; padding-top: 20px; border-top: 1px solid #F1F5F9; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
          <div style="display: flex; align-items: center; gap: 6px;">
            <i data-lucide="user" style="width: 16px; height: 16px; color: #64748B;"></i>
            <span style="font-size: 13px; color: #64748B; font-weight: 600;">Commercial Encaisseur :</span>
            <strong style="color: #0F172A; font-size: 14px; font-weight: 800;"><?= htmlspecialchars(trim(($item['nom_user'] ?? '') . ' ' . ($item['prenom_user'] ?? ''))) ?></strong>
          </div>
          <div>
            <span style="display: inline-flex; align-items: center; gap: 6px; background: #ECFDF5; color: #059669; padding: 6px 16px; border-radius: 20px; font-weight: 800; font-size: 12px; border: 1px solid #A7F3D0;">
              <i data-lucide="check-circle" style="width: 14px; height: 14px;"></i> Cotisation Validée
            </span>
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
