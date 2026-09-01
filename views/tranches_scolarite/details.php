<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$montantTranche = (float)($item['montant_tranche'] ?? 0);
$montantScolarite = (float)($item['montant_scolarite'] ?? 0);
$pct = ($montantScolarite > 0) ? round(($montantTranche / $montantScolarite) * 100, 1) : 0;
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Fiche Tranche : <?= htmlspecialchars($item['libelle_tranche'] ?? 'Tranche') ?></h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
            <span>Année : <strong><?= htmlspecialchars($item['libelle_annee'] ?? '-') ?></strong></span> &bull;
            <span>Filière : <strong><?= htmlspecialchars($item['libelle_filiere'] ?? '-') ?></strong></span> &bull; 
            <span>Niveau : <strong><?= htmlspecialchars($item['libelle_niveau'] ?? '-') ?></strong></span> &bull;
            <span>Régime : <span class="badge" style="background: <?= (($item['affectation_etat'] ?? '') === 'affecte') ? '#DCFCE7' : '#F1F5F9' ?>; color: <?= (($item['affectation_etat'] ?? '') === 'affecte') ? '#15803D' : '#475569' ?>; font-weight: 700; padding: 2px 8px; border-radius: 6px;"><?= (($item['affectation_etat'] ?? '') === 'affecte') ? 'Affecté (État)' : 'Non Affecté (Privé)' ?></span></span>
          </p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>scolarite/list?tab=tranches" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux tranches
          </a>
          <a href="<?= RACINE ?>tranche/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier la tranche
          </a>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : DÉTAILS DE LA TRANCHE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="calendar" style="width: 18px; height: 18px;"></i> Spécifications de l'Échéance
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Montant Exigible</span>
            <div style="font-size: 24px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= number_format($montantTranche, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Représente <?= $pct ?>% de la scolarité (<?= number_format($montantScolarite, 0, ',', ' ') ?> FCFA)</div>
          </div>

          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Code Tranche & Scolarité</span>
            <div style="font-size: 16px; font-weight: 800; color: #0F172A; margin-top: 4px;"><code><?= htmlspecialchars($item['code_tranche'] ?? '-') ?></code></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Scolarité : <code><?= htmlspecialchars($item['scolarite_code'] ?? '-') ?></code></div>
          </div>

          <div style="background: #FAF5FF; border: 1px solid #E9D5FF; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #7E22CE; text-transform: uppercase;">Date Limite de Paiement</span>
            <div style="font-size: 18px; font-weight: 800; color: #7E22CE; margin-top: 4px;">
              <?= !empty($item['date_limite']) ? date('d/m/Y', strtotime($item['date_limite'])) : (!empty($item['date_limite_tranche']) ? date('d/m/Y', strtotime($item['date_limite_tranche'])) : 'À l\'inscription') ?>
            </div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Échéance de versement</div>
          </div>

          <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Statut</span>
            <div style="margin-top: 6px;">
              <?php if (($item['statut_tranche'] ?? '') === 'actif'): ?>
                <span class="badge" style="background:#DCFCE7; color:#15803D; padding:4px 12px; border-radius:10px; font-weight:700; font-size:12px;">Actif</span>
              <?php else: ?>
                <span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:4px 12px; border-radius:10px; font-weight:700; font-size:12px;">Inactif</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
