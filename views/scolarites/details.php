<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$tranches = isset($tranches) ? $tranches : [];
$montantScolarite = (float)($item['montant_scolarite'] ?? ($item['montant'] ?? 0));
$fraisInscription = (float)($item['frais_inscription'] ?? 0);
$fraisAnnexes = (float)($item['frais_annexes'] ?? 0);
$totalGeneral = $montantScolarite + $fraisInscription + $fraisAnnexes;
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Grille Tarifaire Scolarité</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
            <span>Année : <strong><?= htmlspecialchars($item['libelle_annee'] ?? '-') ?></strong></span> &bull; 
            <span>Filière : <strong><?= htmlspecialchars($item['libelle_filiere'] ?? '-') ?></strong></span> &bull; 
            <span>Niveau : <strong><?= htmlspecialchars($item['libelle_niveau'] ?? '-') ?></strong></span> &bull; 
            <span>Régime : <span class="badge" style="background: <?= (($item['affectation_etat'] ?? '') === 'affecte') ? '#DCFCE7' : '#F1F5F9' ?>; color: <?= (($item['affectation_etat'] ?? '') === 'affecte') ? '#15803D' : '#475569' ?>; font-weight: 700; padding: 2px 8px; border-radius: 6px;"><?= (($item['affectation_etat'] ?? '') === 'affecte') ? 'Affecté (État)' : 'Non Affecté (Privé)' ?></span></span>
          </p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>scolarite/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux scolarités
          </a>
          <a href="<?= RACINE ?>scolarite/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier le tarif
          </a>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : SYNTHÈSE TARIFAIRE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="coins" style="width: 18px; height: 18px;"></i> Tarifs Applicables pour l'Année Académique
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Montant Scolarité</span>
            <div style="font-size: 24px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= number_format($montantScolarite, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Frais d'études annuels</div>
          </div>

          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Frais d'Inscription</span>
            <div style="font-size: 20px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= number_format($fraisInscription, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Frais de dossier</div>
          </div>

          <div style="background: #FAF5FF; border: 1px solid #E9D5FF; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #7E22CE; text-transform: uppercase;">Frais Annexes / Dotation</span>
            <div style="font-size: 20px; font-weight: 800; color: #7E22CE; margin-top: 4px;"><?= number_format($fraisAnnexes, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Assurance & Tenue</div>
          </div>

          <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Coût Total Annuel</span>
            <div style="font-size: 24px; font-weight: 800; color: #15803D; margin-top: 4px;"><?= number_format($totalGeneral, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Package complet</div>
          </div>
        </div>
      </div>

      <!-- CARD 2 (COL-12) : ÉCHÉANCIER / TRANCHES DE PAIEMENT -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #EFF6FF;">
          <div>
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="calendar" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Échéancier Officiel des Tranches (<?= count($tranches) ?>)
            </h3>
          </div>
          <a href="<?= RACINE ?>tranche/formulaire" class="btn btn-sm btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 6px; font-size: 12px;">
            + Ajouter une tranche
          </a>
        </div>

        <?php if (empty($tranches)): ?>
          <p style="color: #94A3B8; text-align: center; padding: 30px 0; font-style: italic;">Aucun échéancier de tranches n'a encore été configuré pour ce tarif.</p>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 12px;">
                  <th style="padding: 10px;">Ordre</th>
                  <th style="padding: 10px;">Libellé de la Tranche</th>
                  <th style="padding: 10px; text-align: right;">Montant Échu</th>
                  <th style="padding: 10px; text-align: center;">Date Limite / Échéance</th>
                  <th style="padding: 10px; text-align: center;">Statut</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($tranches as $t): ?>
                  <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 10px; font-weight: 800; color: #1E3A5F;">#<?= (int)($t['ordre_tranche'] ?? 1) ?></td>
                    <td style="padding: 10px; font-weight: 700; color: #0F172A;"><?= htmlspecialchars($t['libelle_tranche'] ?? 'Tranche') ?></td>
                    <td style="padding: 10px; text-align: right; font-weight: 800; color: #15803D;">
                      <?= number_format((float)($t['montant_tranche'] ?? 0), 0, ',', ' ') ?> FCFA
                    </td>
                    <td style="padding: 10px; text-align: center; color: #334155;">
                      <?= !empty($t['date_limite_tranche']) ? date('d/m/Y', strtotime($t['date_limite_tranche'])) : 'À l\'inscription' ?>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                      <span class="badge" style="background:#DCFCE7; color:#15803D; padding:2px 8px; border-radius:8px; font-weight:700; font-size:11px;">Actif</span>
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
