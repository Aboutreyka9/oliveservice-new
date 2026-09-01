<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$montant = (float)($item['montant_paye'] ?? ($item['montant_paiement'] ?? 0));
$scolarite = $scolarite ?? 0;
$totalPayeCumul = $totalPayeCumul ?? 0;
$soldeRestant = $soldeRestant ?? 0;
?>
<style>
@media print {
  body { background: #fff !important; color: #000 !important; }
  .sidebar, .nav-header, .page-header-actions, .no-print { display: none !important; }
  .app-layout { display: block !important; }
  .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; }
  .content-wrapper { padding: 0 !important; }
  .card-recu { border: 1px dashed #000 !important; box-shadow: none !important; }
}
</style>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <div class="page-header no-print" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Reçu de Caisse / Paiement N° <?= htmlspecialchars($item['recu_numero_paiement'] ?? $item['code_paiement']) ?></h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Étudiant : <strong><?= htmlspecialchars(($item['nom_etudiant'] ?? '') . ' ' . ($item['prenom_etudiant'] ?? '')) ?></strong> &bull; Date : <strong><?= !empty($item['date_paiement']) ? date('d/m/Y H:i', strtotime($item['date_paiement'])) : date('d/m/Y') ?></strong></p>
        </div>
        <div class="page-header-actions" style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>paiement/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux paiements
          </a>
          <button onclick="window.print()" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Imprimer le Reçu
          </button>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : REÇU OFFICIEL DE PAIEMENT -->
      <div class="card card-recu" style="background: #FFFFFF; border-radius: 12px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        
        <!-- En-tête du Reçu -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 20px; border-bottom: 2px solid #1E3A5F; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
          <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 52px; height: 52px; border-radius: 10px; background: #1E3A5F; color: #FFFFFF; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="receipt" style="width: 28px; height: 28px;"></i>
            </div>
            <div>
              <h2 style="font-size: 17px; font-weight: 800; color: #0F172A; margin: 0; text-transform: uppercase;">INSTITUT SUPÉRIEUR GEICG</h2>
              <p style="color: #64748B; font-size: 12px; margin: 3px 0 0 0;">Service de la Comptabilité & Caisse &bull; Reçu Officiel de Versement</p>
            </div>
          </div>
          <div style="text-align: right;">
            <div style="font-size: 16px; font-weight: 800; color: #1E3A5F; font-family: monospace;">
              REÇU N° <?= htmlspecialchars($item['recu_numero_paiement'] ?? $item['code_paiement']) ?>
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px;">
              Date : <?= !empty($item['date_paiement']) ? date('d/m/Y à H:i', strtotime($item['date_paiement'])) : date('d/m/Y') ?>
            </div>
          </div>
        </div>

        <!-- Informations Étudiant & Inscription -->
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 18px 24px; margin-bottom: 24px;">
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Nom & Prénoms de l'Élève</span>
              <div style="font-size: 16px; font-weight: 800; color: #0F172A; margin-top: 3px;"><?= htmlspecialchars(($item['nom_etudiant'] ?? '') . ' ' . ($item['prenom_etudiant'] ?? '')) ?></div>
              <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Matricule : <code><?= htmlspecialchars($item['matricule_etudiant'] ?? '-') ?></code></div>
            </div>

            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Classe & Niveau</span>
              <div style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin-top: 3px;"><?= htmlspecialchars($item['libelle_classe'] ?? '-') ?></div>
              <div style="font-size: 12px; color: #64748B;"><?= htmlspecialchars(($item['libelle_filiere'] ?? '-') . ' / ' . ($item['libelle_niveau'] ?? '-')) ?></div>
            </div>

            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Session Académique</span>
              <div style="font-size: 15px; font-weight: 800; color: #0F172A; margin-top: 3px;"><?= htmlspecialchars($item['libelle_annee'] ?? 'En cours') ?></div>
            </div>
          </div>
        </div>

        <!-- Détails Financiers du Paiement -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
          <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Montant du Versement</span>
            <div style="font-size: 26px; font-weight: 900; color: #15803D; margin-top: 4px;"><?= number_format($montant, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px; text-transform: uppercase;">Mode : <strong><?= htmlspecialchars($item['mode_paiement'] ?? 'Espèces') ?></strong></div>
          </div>

          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Total Cumulé Versé</span>
            <div style="font-size: 20px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= number_format($totalPayeCumul, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Sur scolarité de <?= number_format($scolarite, 0, ',', ' ') ?> F</div>
          </div>

          <div style="background: <?= $soldeRestant > 0 ? '#FEF2F2' : '#F8FAFC' ?>; border: 1px solid <?= $soldeRestant > 0 ? '#FECACA' : '#E2E8F0' ?>; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: <?= $soldeRestant > 0 ? '#DC2626' : '#64748B' ?>; text-transform: uppercase;">Solde Restant à Payer</span>
            <div style="font-size: 20px; font-weight: 800; color: <?= $soldeRestant > 0 ? '#DC2626' : '#15803D' ?>; margin-top: 4px;">
              <?= $soldeRestant > 0 ? number_format($soldeRestant, 0, ',', ' ') . ' FCFA' : 'Scolarité Soldée' ?>
            </div>
          </div>
        </div>

        <!-- Référence, Caisse & Signature -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #E2E8F0;">
          <div>
            <div style="font-size: 12px; color: #64748B;">Opérateur de Caisse :</div>
            <div style="font-size: 14px; font-weight: 700; color: #0F172A;"><?= htmlspecialchars(($item['nom_caissier'] ?? '') . ' ' . ($item['prenom_caissier'] ?? 'Caisse Centrale')) ?></div>
            <div style="font-size: 11px; color: #94A3B8; margin-top: 4px;">Référence transaction : <?= htmlspecialchars($item['reference_paiement'] ?? ($item['code_paiement'] ?? '-')) ?></div>
          </div>
          <div style="text-align: center;">
            <div style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 50px;">Cachet & Signature de la Caisse</div>
            <div style="font-size: 11px; color: #94A3B8; font-style: italic;">Pour acquit et validation</div>
          </div>
        </div>

      </div>

    </div>
  </main>
</div>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
