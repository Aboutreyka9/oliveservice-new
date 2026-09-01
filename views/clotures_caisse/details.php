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
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Procès-Verbal de Clôture de Caisse</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Date : <strong><?= !empty($item['date_cloture']) ? date('d/m/Y', strtotime($item['date_cloture'])) : date('d/m/Y') ?></strong> &bull; Réf : <strong><?= htmlspecialchars($item['code_cloture'] ?? '-') ?></strong></p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>cloture_caisse/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux clôtures
          </a>
          <button onclick="window.print()" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer le PV
          </button>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : SYNTHÈSE DES FLUX DU JOUR -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="vault" style="width: 18px; height: 18px;"></i> Bilan des Encaissements & Solde de Clôture
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Fond de Caisse Initial</span>
            <div style="font-size: 20px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= number_format($fondInitial, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Ouverture du matin</div>
          </div>

          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Encaissements Espèces</span>
            <div style="font-size: 22px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= number_format($totalEspeces, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Cash guichet</div>
          </div>

          <div style="background: #FAF5FF; border: 1px solid #E9D5FF; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #7E22CE; text-transform: uppercase;">Mobile Money & Banques</span>
            <div style="font-size: 22px; font-weight: 800; color: #7E22CE; margin-top: 4px;"><?= number_format($totalMobile + $totalBanque, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Wave, Orange, Chèques...</div>
          </div>

          <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Total Général Encaissé</span>
            <div style="font-size: 24px; font-weight: 900; color: #15803D; margin-top: 4px;"><?= number_format($totalGeneral, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;"><?= count($paiements) ?> transaction(s)</div>
          </div>
        </div>

        <div style="display: flex; gap: 24px; flex-wrap: wrap; padding-top: 18px; border-top: 1px solid #F1F5F9; font-size: 13px; margin-top: 16px;">
          <div><strong style="color: #64748B;">Espèces Attendues en Coffre :</strong> <span style="font-weight: 800; color: #0F172A;"><?= number_format($soldeAttendu, 0, ',', ' ') ?> FCFA</span></div>
          <div><strong style="color: #64748B;">Espèces Physiques Comptées :</strong> <span style="font-weight: 800; color: #1E3A5F;"><?= number_format($soldePhysique, 0, ',', ' ') ?> FCFA</span></div>
          <div><strong style="color: #64748B;">Écart de Caisse :</strong> 
            <?php if ($ecart == 0): ?>
              <span class="badge" style="background:#DCFCE7; color:#15803D; padding:3px 10px; border-radius:10px; font-weight:700;">Parfait (0 F)</span>
            <?php elseif ($ecart > 0): ?>
              <span class="badge" style="background:#FEF3C7; color:#B45309; padding:3px 10px; border-radius:10px; font-weight:700;">+<?= number_format($ecart, 0, ',', ' ') ?> F (Excédent)</span>
            <?php else: ?>
              <span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:3px 10px; border-radius:10px; font-weight:700;"><?= number_format($ecart, 0, ',', ' ') ?> F (Déficit)</span>
            <?php endif; ?>
          </div>
          <div><strong style="color: #64748B;">Caissier :</strong> <span style="font-weight: 700; color: #0F172A;"><?= htmlspecialchars(($item['nom_user'] ?? '') . ' ' . ($item['prenom_user'] ?? 'Caisse Centrale')) ?></span></div>
        </div>
      </div>

      <!-- CARD 2 (COL-12) : LISTE DES TRANSACTIONS DE LA JOURNÉE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #EFF6FF;">
          <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="list" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Journal des Règlements de la Journée (<?= count($paiements) ?>)
          </h3>
        </div>

        <?php if (empty($paiements)): ?>
          <p style="color: #94A3B8; text-align: center; padding: 30px 0; font-style: italic;">Aucun versement enregistré pour cette date.</p>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 12px;">
                  <th style="padding: 10px;">Reçu N°</th>
                  <th style="padding: 10px;">Heure</th>
                  <th style="padding: 10px;">Étudiant</th>
                  <th style="padding: 10px;">Classe</th>
                  <th style="padding: 10px;">Mode</th>
                  <th style="padding: 10px; text-align: right;">Montant Encaissé</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($paiements as $p): ?>
                  <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 10px; font-family: monospace; font-weight: 700; color: #1E3A5F;">
                      <a href="<?= RACINE ?>paiement/details/<?= $this->validator->crypter($p['id_paiement']) ?>" style="color: #1E3A5F; text-decoration: underline;">
                        <?= htmlspecialchars($p['recu_numero_paiement'] ?? $p['code_paiement']) ?>
                      </a>
                    </td>
                    <td style="padding: 10px; color: #64748B;"><?= !empty($p['date_paiement']) ? date('H:i', strtotime($p['date_paiement'])) : '-' ?></td>
                    <td style="padding: 10px; font-weight: 700; color: #0F172A;"><?= htmlspecialchars(($p['nom_etudiant'] ?? '') . ' ' . ($p['prenom_etudiant'] ?? '')) ?></td>
                    <td style="padding: 10px; color: #334155;"><?= htmlspecialchars($p['libelle_classe'] ?? '-') ?></td>
                    <td style="padding: 10px; color: #334155; text-transform: uppercase; font-size: 11px;"><?= htmlspecialchars($p['mode_paiement'] ?? 'Espèces') ?></td>
                    <td style="padding: 10px; text-align: right; font-weight: 800; color: #15803D;">
                      <?= number_format((float)($p['montant_paye'] ?? 0), 0, ',', ' ') ?> FCFA
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
