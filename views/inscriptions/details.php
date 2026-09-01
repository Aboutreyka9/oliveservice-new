<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$paiements = isset($paiements) ? $paiements : [];
$scolarite = $scolarite ?? 0;
$totalPaye = $totalPaye ?? 0;
$solde = $solde ?? 0;
$tauxPaiement = ($scolarite > 0) ? min(100, round(($totalPaye / $scolarite) * 100)) : 100;
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Fiche Inscription : <?= htmlspecialchars(($item['nom_etudiant'] ?? '') . ' ' . ($item['prenom_etudiant'] ?? '')) ?></h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Classe : <strong><?= htmlspecialchars($item['libelle_classe'] ?? '-') ?></strong> &bull; Année Académique : <strong><?= htmlspecialchars($item['libelle_annee'] ?? '-') ?></strong></p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>inscription/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux inscriptions
          </a>
          <a href="<?= RACINE ?>inscription/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier l'inscription
          </a>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : SYNTHÈSE ADMINISTRATIVE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="file-text" style="width: 18px; height: 18px;"></i> Détails de l'Inscription
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Étudiant</span>
            <div style="font-size: 17px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars(($item['nom_etudiant'] ?? '') . ' ' . ($item['prenom_etudiant'] ?? '')) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Matricule : <code><?= htmlspecialchars($item['matricule_etudiant'] ?? '-') ?></code></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Classe Affectée</span>
            <div style="font-size: 17px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= htmlspecialchars($item['libelle_classe'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B;"><?= htmlspecialchars(($item['libelle_filiere'] ?? '-') . ' / ' . ($item['libelle_niveau'] ?? '-')) ?></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Session Universitaire</span>
            <div style="font-size: 16px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($item['libelle_annee'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B;">Date : <?= !empty($item['date_inscription']) ? date('d/m/Y', strtotime($item['date_inscription'])) : date('d/m/Y') ?></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Statut Inscription</span>
            <div style="margin-top: 4px;">
              <?php if (($item['statut_inscription'] ?? '') === 'actif'): ?>
                <span class="badge" style="background:#DCFCE7; color:#15803D; padding:5px 12px; border-radius:10px; font-weight:700; font-size:12px;">Validée & Active</span>
              <?php else: ?>
                <span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:5px 12px; border-radius:10px; font-weight:700; font-size:12px;">Suspendue</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- CARD 2 (COL-12) : RÈGLEMENTS DE SCOLARITÉ ASSOCIÉS -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #EFF6FF;">
          <div>
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="credit-card" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Suivi des Paiements de la Scolarité
            </h3>
          </div>
          <a href="<?= RACINE ?>paiement/formulaire" class="btn btn-sm btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 6px; font-size: 12px;">
            + Encaisser un versement
          </a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px;">
          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 14px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Montant Scolarité</span>
            <div style="font-size: 20px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= number_format($scolarite, 0, ',', ' ') ?> FCFA</div>
          </div>

          <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 14px;">
            <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Total Versé (<?= $tauxPaiement ?>%)</span>
            <div style="font-size: 20px; font-weight: 800; color: #15803D; margin-top: 4px;"><?= number_format($totalPaye, 0, ',', ' ') ?> FCFA</div>
          </div>

          <div style="background: <?= $solde > 0 ? '#FEF2F2' : '#F8FAFC' ?>; border: 1px solid <?= $solde > 0 ? '#FECACA' : '#E2E8F0' ?>; border-radius: 10px; padding: 14px;">
            <span style="font-size: 11px; font-weight: 700; color: <?= $solde > 0 ? '#DC2626' : '#64748B' ?>; text-transform: uppercase;">Reste Dû</span>
            <div style="font-size: 20px; font-weight: 800; color: <?= $solde > 0 ? '#DC2626' : '#15803D' ?>; margin-top: 4px;">
              <?= $solde > 0 ? number_format($solde, 0, ',', ' ') . ' FCFA' : 'Soldé' ?>
            </div>
          </div>
        </div>

        <?php if (empty($paiements)): ?>
          <p style="color: #94A3B8; text-align: center; padding: 20px 0; font-style: italic;">Aucun paiement enregistré pour le moment.</p>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 12px;">
                  <th style="padding: 10px;">Reçu N°</th>
                  <th style="padding: 10px;">Date</th>
                  <th style="padding: 10px;">Mode</th>
                  <th style="padding: 10px; text-align: right;">Montant Payé</th>
                  <th style="padding: 10px; text-align: center;">Statut</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($paiements as $p): ?>
                  <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 10px; font-family: monospace; font-weight: 700; color: #1E3A5F;">
                      <a href="<?= RACINE ?>paiement/details/<?= $this->validator->crypter($p['id_paiement']) ?>" style="color: #1E3A5F; text-decoration: underline;">
                        <?= htmlspecialchars($p['reference_paiement'] ?? ($p['code_paiement'] ?? '-')) ?>
                      </a>
                    </td>
                    <td style="padding: 10px; color: #334155;"><?= !empty($p['date_paiement']) ? date('d/m/Y', strtotime($p['date_paiement'])) : '-' ?></td>
                    <td style="padding: 10px; color: #334155; text-transform: uppercase; font-size: 12px;"><?= htmlspecialchars($p['mode_paiement'] ?? 'Espèces') ?></td>
                    <td style="padding: 10px; text-align: right; font-weight: 800; color: #15803D;">
                      <?= number_format((float)($p['montant_paiement'] ?? ($p['montant_paye'] ?? 0)), 0, ',', ' ') ?> FCFA
                    </td>
                    <td style="padding: 10px; text-align: center;">
                      <span class="badge" style="background:#DCFCE7; color:#15803D; padding:2px 8px; border-radius:8px; font-weight:700; font-size:11px;">Validé</span>
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
