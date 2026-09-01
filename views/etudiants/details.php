<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$parent = isset($parent) ? $parent : [];
$inscription = isset($inscription) ? $inscription : [];
$paiements = isset($paiements) ? $paiements : [];
$absences = isset($absences) ? $absences : [];
$scolariteTotale = $scolariteTotale ?? 0;
$totalPaye = $totalPaye ?? 0;
$soldeRestant = $soldeRestant ?? 0;
$tauxPaiement = ($scolariteTotale > 0) ? min(100, round(($totalPaye / $scolariteTotale) * 100)) : 100;
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <!-- En-tête de page -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Dossier Étudiant : <?= htmlspecialchars(($item['nom_etudiant'] ?? '') . ' ' . ($item['prenom_etudiant'] ?? '')) ?></h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Matricule : <strong><?= htmlspecialchars($item['matricule_etudiant'] ?? '-') ?></strong> &bull; Classe : <strong><?= htmlspecialchars($inscription['libelle_classe'] ?? 'Non inscrit') ?></strong></p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>etudiant/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux étudiants
          </a>
          <a href="<?= RACINE ?>etudiant/edition/<?= $encryptedId ?>" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="edit" style="width: 18px; height: 18px;"></i> Modifier le dossier
          </a>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : ÉTAT CIVIL & INSCRIPTION ACTIVE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="user" style="width: 18px; height: 18px;"></i> Identité & Parcours Pédagogique
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          
          <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 52px; height: 52px; min-width: 52px; border-radius: 50%; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 800; border: 1px solid #BFDBFE;">
              <?= strtoupper(substr($item['nom_etudiant'] ?? 'E', 0, 1) . substr($item['prenom_etudiant'] ?? 'T', 0, 1)) ?>
            </div>
            <div>
              <h2 style="font-size: 17px; font-weight: 800; color: #0F172A; margin: 0;">
                <?= htmlspecialchars(($item['nom_etudiant'] ?? '') . ' ' . ($item['prenom_etudiant'] ?? '')) ?>
              </h2>
              <span style="font-size: 12px; color: #64748B;">Sexe : <strong><?= htmlspecialchars($item['sexe_etudiant'] ?? 'M') ?></strong> &bull; Nat. : <strong><?= htmlspecialchars($item['nationalite_etudiant'] ?? 'Ivoirienne') ?></strong></span>
            </div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Coordonnées de Contact</span>
            <div style="font-size: 13px; font-weight: 700; color: #0F172A;"><?= htmlspecialchars($item['telephone_etudiant'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #1E3A5F;"><?= htmlspecialchars($item['email_etudiant'] ?? 'Aucun email') ?></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Classe Actuelle</span>
            <div style="font-size: 16px; font-weight: 800; color: #1E3A5F;"><?= htmlspecialchars($inscription['libelle_classe'] ?? 'Non inscrit') ?></div>
            <div style="font-size: 12px; color: #64748B;"><?= htmlspecialchars(($inscription['libelle_filiere'] ?? '-') . ' / ' . ($inscription['libelle_niveau'] ?? '-')) ?></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Statut Dossier</span>
            <div style="margin-top: 4px;">
              <?php if (($item['statut_etudiant'] ?? '') === 'actif'): ?>
                <span class="badge" style="background:#DCFCE7; color:#15803D; padding:5px 12px; border-radius:10px; font-weight:700; font-size:12px;">Actif / Régulier</span>
              <?php else: ?>
                <span class="badge" style="background:#FEE2E2; color:#B91C1C; padding:5px 12px; border-radius:10px; font-weight:700; font-size:12px;">Inactif</span>
              <?php endif; ?>
            </div>
          </div>

        </div>

        <div style="display: flex; gap: 20px; flex-wrap: wrap; padding-top: 14px; border-top: 1px solid #F1F5F9; font-size: 13px; margin-top: 16px;">
          <div><strong style="color: #64748B;">Date de Naissance :</strong> <span style="font-weight: 700; color: #0F172A;"><?= !empty($item['date_naissance_etudiant']) ? date('d/m/Y', strtotime($item['date_naissance_etudiant'])) : '-' ?></span> <?= !empty($item['lieu_naissance_etudiant']) ? 'à ' . htmlspecialchars($item['lieu_naissance_etudiant']) : '' ?></div>
          <div><strong style="color: #64748B;">Lieu de Résidence :</strong> <span style="color: #0F172A;"><?= htmlspecialchars($item['lieu_residence_etudiant'] ?? 'Non renseigné') ?></span></div>
        </div>
      </div>

      <!-- CARD 2 (COL-12) : PARENTS ET TUTEURS -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="shield-check" style="width: 18px; height: 18px;"></i> Responsables Légaux & Filiation
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Père</span>
            <div style="font-size: 15px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($parent['nom_pere'] ?? 'Non renseigné') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Tél : <?= htmlspecialchars($parent['telephone_pere'] ?? '-') ?></div>
            <div style="font-size: 11px; color: #64748B; margin-top: 2px;">Profession : <?= htmlspecialchars($parent['profession_pere'] ?? '-') ?></div>
          </div>

          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Mère</span>
            <div style="font-size: 15px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($parent['nom_mere'] ?? 'Non renseignée') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Tél : <?= htmlspecialchars($parent['telephone_mere'] ?? '-') ?></div>
          </div>

          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Tuteur Légal / Correspondant</span>
            <div style="font-size: 15px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($parent['nom_tuteur'] ?? 'Non renseigné') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Tél : <?= htmlspecialchars($parent['telephone_tuteur'] ?? '-') ?></div>
          </div>
        </div>
      </div>

      <!-- CARD 3 (COL-12) : SITUATION FINANCIÈRE & PAIEMENTS -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #EFF6FF;">
          <div>
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="credit-card" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Situation Financière & Reçus de Scolarité
            </h3>
          </div>
          <a href="<?= RACINE ?>paiement/formulaire" class="btn btn-sm btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; font-weight: 700; border-radius: 6px; font-size: 12px;">
            + Nouveau règlement
          </a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px;">
          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 14px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Scolarité Totale</span>
            <div style="font-size: 20px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= number_format($scolariteTotale, 0, ',', ' ') ?> F</div>
          </div>

          <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 14px;">
            <span style="font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">Total Versé (<?= $tauxPaiement ?>%)</span>
            <div style="font-size: 20px; font-weight: 800; color: #15803D; margin-top: 4px;"><?= number_format($totalPaye, 0, ',', ' ') ?> F</div>
          </div>

          <div style="background: <?= $soldeRestant > 0 ? '#FEF2F2' : '#F8FAFC' ?>; border: 1px solid <?= $soldeRestant > 0 ? '#FECACA' : '#E2E8F0' ?>; border-radius: 10px; padding: 14px;">
            <span style="font-size: 11px; font-weight: 700; color: <?= $soldeRestant > 0 ? '#DC2626' : '#64748B' ?>; text-transform: uppercase;">Reste à Payer</span>
            <div style="font-size: 20px; font-weight: 800; color: <?= $soldeRestant > 0 ? '#DC2626' : '#15803D' ?>; margin-top: 4px;">
              <?= $soldeRestant > 0 ? number_format($soldeRestant, 0, ',', ' ') . ' F' : 'Soldé (0 F)' ?>
            </div>
          </div>
        </div>

        <?php if (empty($paiements)): ?>
          <p style="color: #94A3B8; text-align: center; padding: 20px 0; font-style: italic;">Aucun paiement enregistré pour cet étudiant.</p>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; font-size: 12px;">
                  <th style="padding: 10px;">Reçu N°</th>
                  <th style="padding: 10px;">Date</th>
                  <th style="padding: 10px;">Mode Règlement</th>
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
