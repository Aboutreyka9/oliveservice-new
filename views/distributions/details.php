<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$client = $client ?? [];
$souscription = $souscription ?? [];
$agent = $agent ?? [];
$distributionPacks = $distributionPacks ?? [];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(30, 58, 95, 0.25);">
            <i data-lucide="package-check" style="width: 26px; height: 26px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              PV Distribution : <code style="font-weight: 800; color: #1E3A5F; font-size: 18px; background: #F1F5F9; padding: 2px 10px; border-radius: 8px; border: 1px solid #CBD5E1;"><?= htmlspecialchars($item['code_distribution'] ?? '-') ?></code>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Remise effective enregistrée le <?= !empty($item['created_at_distribution']) ? date('d/m/Y à H:i', strtotime($item['created_at_distribution'])) : '-' ?>
            </p>
          </div>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <button onclick="window.print()" class="btn" style="background: #FFFFFF; border: 1px solid #CBD5E1; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); cursor: pointer;">
            <i data-lucide="printer" style="width: 16px; height: 16px; color: #1E3A5F;"></i> Imprimer le Bordereau
          </button>
          <a href="<?= RACINE ?>distribution/list" class="btn" style="background: #1E3A5F; color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 2px 6px rgba(30,58,95,0.25);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour aux distributions
          </a>
        </div>
      </div>

      <!-- CARTE SYNTHÈSE BÉNÉFICIAIRE & SOUSCRIPTION -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
          <i data-lucide="user-check" style="width: 18px; height: 18px; color: #059669;"></i> Informations Client Bénéficiaire
        </h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Client Bénéficiaire</span>
            <div style="font-size: 17px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($client['nom_client'] ?? ($souscription['nom_client'] ?? '-')) ?></div>
            <div style="font-size: 13px; color: #64748B; margin-top: 2px; font-weight: 600;">Code Client : <?= htmlspecialchars($client['code_client'] ?? ($item['client_code'] ?? '-')) ?></div>
            <div style="font-size: 13px; color: #64748B; margin-top: 2px; font-weight: 500;">Tél : <?= htmlspecialchars($client['telephone_client'] ?? '-') ?></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Souscription & Financier</span>
            <div style="font-size: 17px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= htmlspecialchars($item['souscription_code'] ?? '-') ?></div>
            <div style="font-size: 13px; color: #059669; margin-top: 2px; font-weight: 800;">Statut : SOLDE (100% Payé)</div>
            <div style="font-size: 13px; color: #64748B; margin-top: 2px; font-weight: 600;">Total : <?= number_format((float)($souscription['montant_total_prevu'] ?? 0), 0, ',', ' ') ?> FCFA</div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Agent Livreur / Validation</span>
            <div style="font-size: 16px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars(trim(($agent['nom_user'] ?? '') . ' ' . ($agent['prenom_user'] ?? ''))) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px; font-weight: 600;">Code : <?= htmlspecialchars($item['user_code'] ?? '-') ?> (<?= htmlspecialchars($agent['role_user'] ?? 'Agent') ?>)</div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Statut Distribution</span>
            <div style="margin-top: 6px;">
              <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-weight: 800; font-size: 12px; background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0;">
                <i data-lucide="check-circle" style="width: 14px; height: 14px;"></i> <?= htmlspecialchars($item['statut_distribution'] ?? 'valide') ?>
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- CARTE DÉTAIL DES PACKS & ARTICLES LIVRÉS -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
          <i data-lucide="boxes" style="width: 18px; height: 18px; color: #059669;"></i> Récapitulatif des Packs & Quantités Remises
        </h3>

        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #475569; border-bottom: 2px solid #CBD5E1;">
                <th style="padding: 12px; font-weight: 800;">Pack</th>
                <th style="padding: 12px; font-weight: 800;">Catégorie</th>
                <th style="padding: 12px; font-weight: 800;">Articles Inclus</th>
                <th style="padding: 12px; font-weight: 800; text-align: center;">Qté Attendue</th>
                <th style="padding: 12px; font-weight: 800; text-align: center;">Qté Livrée</th>
                <th style="padding: 12px; font-weight: 800; text-align: center;">Statut Remise</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($distributionPacks)): ?>
                <?php foreach ($distributionPacks as $dp): ?>
                  <tr style="border-bottom: 1px solid #E2E8F0;">
                    <td style="padding: 12px; font-weight: 800; color: #0F172A;"><?= htmlspecialchars($dp['libelle_pack'] ?? 'Pack') ?></td>
                    <td style="padding: 12px;">
                      <span style="font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 12px; background: #ECFDF5; color: #059669;">
                        <?= htmlspecialchars($dp['libelle_categorie_pack'] ?? 'Général') ?>
                      </span>
                    </td>
                    <td style="padding: 12px;">
                      <?php if (!empty($dp['articles'])): ?>
                        <?php foreach ($dp['articles'] as $art): ?>
                          <span style="display: inline-block; background: #E2E8F0; color: #334155; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; margin: 2px;">
                            <?= htmlspecialchars($art['libelle_article']) ?> (x<?= (int)$art['quantite_article'] ?>)
                          </span>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <em style="color: #94A3B8;">Articles inclus</em>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 12px; text-align: center; font-weight: 800; color: #2563EB; font-size: 15px;">
                      <?= (int)($dp['quantite_article_attendue'] ?? 0) ?>
                    </td>
                    <td style="padding: 12px; text-align: center; font-weight: 900; color: #059669; font-size: 15px;">
                      <?= (int)($dp['quantite_article_livree'] ?? 0) ?>
                    </td>
                    <td style="padding: 12px; text-align: center;">
                      <?php 
                        $attendue = (int)($dp['quantite_article_attendue'] ?? 0);
                        $livree = (int)($dp['quantite_article_livree'] ?? 0);
                        if ($livree >= $attendue && $attendue > 0) {
                          echo '<span style="color:#059669; font-weight:800; background:#D1FAE5; padding:3px 10px; border-radius:12px;">● Complète</span>';
                        } else {
                          echo '<span style="color:#D97706; font-weight:800; background:#FEF3C7; padding:3px 10px; border-radius:12px;">● Partielle (' . $livree . '/' . $attendue . ')</span>';
                        }
                      ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" style="padding: 20px; text-align: center; color: #94A3B8;">Aucun pack enregistré sur cette distribution.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <?php if (!empty($item['observation_distribution'])): ?>
        <div style="margin-top: 24px; padding: 16px; background: #F8FAFC; border-radius: 12px; border: 1px solid #E2E8F0;">
          <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Observation / Remarques</span>
          <p style="margin: 6px 0 0 0; color: #334155; font-size: 14px; font-weight: 500; line-height: 1.5;"><?= nl2br(htmlspecialchars($item['observation_distribution'])) ?></p>
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
