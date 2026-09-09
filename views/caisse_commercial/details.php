<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$paiements = isset($paiements) ? $paiements : [];
$totalEspeces = (float)($item['total_especes'] ?? 0);
$totalMobile = (float)($item['total_mobile_money'] ?? 0);
$totalBanque = (float)($item['total_cheque_virement'] ?? 0);
$totalGeneral = (float)($item['total_general'] ?? ($totalEspeces + $totalMobile + $totalBanque));
$fondInitial = (float)($item['fond_initial'] ?? 0);
$soldeAttendu = (float)($item['solde_attendu_caisse'] ?? ($fondInitial + $totalEspeces));
$soldePhysique = (float)($item['solde_physique_caisse'] ?? $soldeAttendu);
$ecart = (float)($item['ecart_caisse'] ?? ($soldePhysique - $soldeAttendu));
?>
<style>
@media print {
  @page {
    size: A4 portrait;
    margin: 10mm;
  }
  body, html {
    background: #FFFFFF !important;
    color: #000000 !important;
    margin: 0 !important;
    padding: 0 !important;
  }
  .sidebar,
  .topbar,
  .btn,
  .no-print {
    display: none !important;
  }
  .main-content,
  .content-wrapper {
    margin: 0 !important;
    padding: 0 !important;
  }
  .card-premium {
    box-shadow: none !important;
    border: 1px solid #CBD5E1 !important;
    padding: 16px !important;
    margin-bottom: 16px !important;
  }
  table {
    width: 100% !important;
    border-collapse: collapse !important;
  }
  th, td {
    border: 1px solid #CBD5E1 !important;
    padding: 6px 8px !important;
  }
}
</style>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE LA FICHE DÉTAILS CAISSE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="wallet" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Procès-Verbal de Caisse Commercial
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Date : <strong><?= !empty($item['date_cloture']) ? date('d/m/Y', strtotime($item['date_cloture'])) : (!empty($item['date_ouverture']) ? date('d/m/Y', strtotime($item['date_ouverture'])) : date('d/m/Y')) ?></strong> &bull; Réf : <code style="font-weight: 800; color: #1E3A5F; background: #F1F5F9; padding: 2px 6px; border-radius: 4px; border: 1px solid #CBD5E1;"><?= htmlspecialchars($item['code_caisse'] ?? ($item['code_cloture'] ?? '-')) ?></code>
            </p>
          </div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;" class="no-print">
          <a href="<?= RACINE ?>caisse_commercial/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour à la liste
          </a>
          <button onclick="window.print()" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 10px 20px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); cursor: pointer;">
            <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Imprimer le PV
          </button>
        </div>
      </div>

      <!-- CARTE SYNTHÈSE DES FLUX DU JOUR (NAVY PREMIUM) -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
          <i data-lucide="vault" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Bilan des Encaissements & Solde de Clôture
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div style="background: #F8FAFC; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Fond de Caisse Initial</span>
            <div style="font-size: 20px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= number_format($fondInitial, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px; font-weight: 500;">Ouverture du matin</div>
          </div>

          <div style="background: #F0F9FF; border-radius: 12px; padding: 18px; border: 1px solid #BAE6FD;">
            <span style="font-size: 11px; font-weight: 700; color: #0369A1; text-transform: uppercase; letter-spacing: 0.5px;">Encaissements Espèces</span>
            <div style="font-size: 22px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= number_format($totalEspeces, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px; font-weight: 500;">Cash guichet</div>
          </div>

          <div style="background: #FAF5FF; border-radius: 12px; padding: 18px; border: 1px solid #E9D5FF;">
            <span style="font-size: 11px; font-weight: 700; color: #7E22CE; text-transform: uppercase; letter-spacing: 0.5px;">Mobile Money & Banques</span>
            <div style="font-size: 22px; font-weight: 800; color: #7E22CE; margin-top: 4px;"><?= number_format($totalMobile + $totalBanque, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px; font-weight: 500;">Wave, Orange, Chèques...</div>
          </div>

          <div style="background: #ECFDF5; border-radius: 12px; padding: 18px; border: 1px solid #A7F3D0;">
            <span style="font-size: 11px; font-weight: 700; color: #047857; text-transform: uppercase; letter-spacing: 0.5px;">Total Général Encaissé</span>
            <div style="font-size: 24px; font-weight: 900; color: #059669; margin-top: 4px;"><?= number_format($totalGeneral, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px; font-weight: 600;"><?= count($paiements) ?> transaction(s)</div>
          </div>
        </div>

        <div style="display: flex; gap: 24px; flex-wrap: wrap; padding-top: 18px; border-top: 1px solid #F1F5F9; font-size: 13px; margin-top: 20px;">
          <div><strong style="color: #64748B;">Espèces Attendues :</strong> <span style="font-weight: 800; color: #0F172A;"><?= number_format($soldeAttendu, 0, ',', ' ') ?> FCFA</span></div>
          <div><strong style="color: #64748B;">Commercial / Agent :</strong> <span style="font-weight: 800; color: #0F172A;"><?= htmlspecialchars(($item['nom_user'] ?? '') . ' ' . ($item['prenom_user'] ?? '')) ?></span></div>
        </div>
      </div>

      <!-- CARTE DÉTAILS DES OPÉRATIONS DE LA SEANCE DE CAISSE -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
        <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 12px;">
          <i data-lucide="receipt" style="width: 18px; height: 18px; color: #059669;"></i> Liste des Cotisations Encaissées dans cette Caisse (<?= count($paiements) ?>)
        </h3>

        <?php if (!empty($paiements)): ?>
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 2px solid #E2E8F0;">
                <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px;">N° Cotisation</th>
                <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px;">Date & Heure</th>
                <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px;">Client Souscripteur</th>
                <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px; text-align: center;">Mode</th>
                <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px; text-align: center;">Jours</th>
                <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px; text-align: right;">Montant</th>
                <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px; text-align: center;">Statut</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($paiements as $p): ?>
                <?php 
                  $clientNom = trim(($p['nom_client'] ?? '') . ' ' . ($p['prenom_client'] ?? ''));
                  if (empty($clientNom)) $clientNom = $p['client_code'] ?? 'Client';
                  $m = (float)($p['montant_cautisation_client'] ?? 0);
                  $mode = strtolower(trim($p['mode_paiement'] ?? 'espece'));
                  $st = strtolower(trim($p['statut_cautisation_client'] ?? 'en_attente'));
                ?>
                <tr style="border-bottom: 1px solid #F1F5F9;">
                  <td style="padding: 12px 14px;">
                    <code style="font-weight:700; color:#1E3A5F; background:#F1F5F9; padding:3px 8px; border-radius:6px; font-size:12px; border:1px solid #CBD5E1;"><?= htmlspecialchars($p['code_cautisation_client'] ?? '-') ?></code>
                  </td>
                  <td style="padding: 12px 14px; font-weight: 600; color: #334155;">
                    <?= !empty($p['date_cautisation']) ? date('d/m/Y H:i', strtotime($p['date_cautisation'])) : '-' ?>
                  </td>
                  <td style="padding: 12px 14px; font-weight: 800; color: #0F172A;">
                    <?= htmlspecialchars($clientNom) ?>
                  </td>
                  <td style="padding: 12px 14px; text-align: center;">
                    <span style="padding: 3px 10px; border-radius: 12px; font-weight: 800; font-size: 11px; text-transform: uppercase; background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0;">
                      <?= htmlspecialchars($mode) ?>
                    </span>
                  </td>
                  <td style="padding: 12px 14px; text-align: center; font-weight: 800; color: #2563EB;">
                    +<?= (int)($p['nombre_jour'] ?? 1) ?> j
                  </td>
                  <td style="padding: 12px 14px; text-align: right; font-weight: 900; color: #059669;">
                    <?= number_format($m, 0, ',', ' ') ?> FCFA
                  </td>
                  <td style="padding: 12px 14px; text-align: center;">
                    <?php if ($st === 'valide'): ?>
                      <span style="background: #ECFDF5; color: #047857; padding: 3px 10px; border-radius: 12px; font-weight: 800; font-size: 11px; border: 1px solid #A7F3D0;">Validée</span>
                    <?php else: ?>
                      <span style="background: #FEF3C7; color: #B45309; padding: 3px 10px; border-radius: 12px; font-weight: 800; font-size: 11px; border: 1px solid #FDE68A;">En attente</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
          <p style="color: #64748B; font-weight: 500; margin: 0; font-size: 13px;">Aucune transaction enregistrée directement sur cette session de caisse.</p>
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
