<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$souscription   = $souscription ?? [];
$caisseOuverte  = $caisseOuverte ?? false;

$nomClient = trim($souscription['nom_client'] ?? '');
$telephone = $souscription['telephone_client'] ?? '-';
$genre = $souscription['sexe_client'] ?? '-';
$residence = $souscription['lieu_residence_client'] ?? '-';
$codeClient = $souscription['code_client'] ?? '-';
$email = $sousscription['email_client'] ?? '-';
$profession = $souscription['profession_client'] ?? '-';

$montantTotal = (float)($souscription['montant_total_a_payer'] ?? $souscription['montant_total'] ?? $souscription['totale_souscription'] ?? 0);
$montantPaye = (float)($souscription['montant_total_paye'] ?? $souscription['total_cotise'] ?? $souscription['montant_total_cotise'] ?? 0);
$montantRestant = isset($souscription['solde_restant']) ? (float)$souscription['solde_restant'] : max(0, $montantTotal - $montantPaye);

$joursTotal = (int)($souscription['nombre_jours_total'] ?? $souscription['duree_totale_jours'] ?? $souscription['nombre_jour_session'] ?? $souscription['nombre_jour_total'] ?? 0);
$joursPayes = (int)($souscription['nombre_jours_payes'] ?? $souscription['nombre_jour_cotise'] ?? 0);
$joursRestants = isset($souscription['jours_restants']) ? (int)$souscription['jours_restants'] : max(0, $joursTotal - $joursPayes);

$prixCotisationJournaliere = (float)($souscription['prix_cotisation_pack'] ?? $souscription['prix_cotisation_journaliere'] ?? $souscription['montant_cotisation_journaliere'] ?? 0);

$codeSouscription = $souscription['code_souscription'] ?? '';
$libelleSession = $souscription['libelle_session'] ?? '-';
$statutSouscription = $souscription['statut_souscription'] ?? '-';

$initials = '';
$words = explode(' ', trim($nomClient));
foreach ($words as $w) {
    if (!empty($w)) $initials .= mb_substr($w, 0, 1);
}
$initials = mb_substr(strtoupper($initials), 0, 2) ?: 'CL';

$pourcentagePaye = $montantTotal > 0 ? min(100, round(($montantPaye / $montantTotal) * 100, 1)) : 0;
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">

      <!-- EN-TÊTE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">
            Paiement Cautisation
            <code style="font-weight: 800; color: #1E3A5F; font-size: 20px; background: #EFF6FF; padding: 3px 10px; border-radius: 6px;"><?= htmlspecialchars($codeSouscription) ?></code>
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">
            Client : <strong><?= htmlspecialchars($nomClient) ?></strong>
            &bull; Session : <strong><?= htmlspecialchars($libelleSession) ?></strong>
          </p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>cautisation-payment/search-form" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; text-decoration: none; color: #475569; border: 1px solid #CBD5E1;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour Recherche
          </a>
        </div>
      </div>

      <div class="row" style="display: flex; gap: 24px; flex-wrap: wrap; margin-bottom: 24px;">
        <!-- COLONNE GAUCHE: Informations du client et Situation Financière -->
        <div style="flex: 1.1; min-width: 320px; display: flex; flex-direction: column; gap: 24px;">
          
          <!-- CARTE 1 : INFORMATIONS CLIENT (Design Premium Navy Glass) -->
          <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01);">
            
            <!-- En-tête avec Initiales du Client -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #F1F5F9;">
              <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 18px; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
                  <?= htmlspecialchars($initials) ?>
                </div>
                <div>
                  <h3 style="font-size: 18px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
                    <?= htmlspecialchars($nomClient) ?>
                  </h3>
                  <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                    <span style="background: #F1F5F9; color: #475569; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; font-family: monospace;">
                      <i data-lucide="hash" style="width: 12px; height: 12px; vertical-align: middle; display: inline-block;"></i> <?= htmlspecialchars($codeClient) ?>
                    </span>
                    <span style="background: #EFF6FF; color: #1D4ED8; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px;">
                      Client Cautisation
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Grille des détails client -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 14px;">
              
              <!-- Téléphone -->
              <div class="info-box" style="background: #F8FAFC; border: 1px solid #F1F5F9; border-radius: 12px; padding: 12px 14px;">
                <div style="display: flex; align-items: center; gap: 6px; color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">
                  <i data-lucide="phone" style="width: 14px; height: 14px; color: #2563EB;"></i> Téléphone
                </div>
                <a href="tel:<?= htmlspecialchars($telephone) ?>" style="font-size: 14px; font-weight: 700; color: #0F172A; text-decoration: none; display: block; transition: color 0.2s;">
                  <?= htmlspecialchars($telephone) ?>
                </a>
              </div>

              <!-- Email -->
              <div class="info-box" style="background: #F8FAFC; border: 1px solid #F1F5F9; border-radius: 12px; padding: 12px 14px;">
                <div style="display: flex; align-items: center; gap: 6px; color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">
                  <i data-lucide="mail" style="width: 14px; height: 14px; color: #4F46E5;"></i> Email
                </div>
                <a href="mailto:<?= htmlspecialchars($email) ?>" style="font-size: 13px; font-weight: 600; color: #0F172A; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block;">
                  <?= htmlspecialchars($email) ?>
                </a>
              </div>

              <!-- Genre -->
              <div class="info-box" style="background: #F8FAFC; border: 1px solid #F1F5F9; border-radius: 12px; padding: 12px 14px;">
                <div style="display: flex; align-items: center; gap: 6px; color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">
                  <i data-lucide="user-check" style="width: 14px; height: 14px; color: #059669;"></i> Genre
                </div>
                <div style="font-size: 14px; font-weight: 700; color: #0F172A;">
                  <?= htmlspecialchars($genre) ?>
                </div>
              </div>

              <!-- Profession -->
              <div class="info-box" style="background: #F8FAFC; border: 1px solid #F1F5F9; border-radius: 12px; padding: 12px 14px;">
                <div style="display: flex; align-items: center; gap: 6px; color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">
                  <i data-lucide="briefcase" style="width: 14px; height: 14px; color: #D97706;"></i> Profession
                </div>
                <div style="font-size: 14px; font-weight: 600; color: #0F172A;">
                  <?= htmlspecialchars($profession) ?>
                </div>
              </div>

              <!-- Lieu de résidence -->
              <div class="info-box" style="grid-column: 1 / -1; background: #F8FAFC; border: 1px solid #F1F5F9; border-radius: 12px; padding: 12px 14px;">
                <div style="display: flex; align-items: center; gap: 6px; color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">
                  <i data-lucide="map-pin" style="width: 14px; height: 14px; color: #DC2626;"></i> Lieu de résidence
                </div>
                <div style="font-size: 14px; font-weight: 600; color: #0F172A;">
                  <?= htmlspecialchars($residence) ?>
                </div>
              </div>

            </div>
          </div>


          <!-- CARTE 2 : SITUATION FINANCIÈRE CAUTISATION (Design Dynamic & Vibrant) -->
          <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01);">
            
            <!-- En-tête avec Jauge de progression globale -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid #F1F5F9;">
              <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
                <span style="width: 34px; height: 34px; border-radius: 10px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center;">
                  <i data-lucide="wallet" style="width: 20px; height: 20px;"></i>
                </span>
                Situation Financière Cautisation
              </h3>
              <span style="background: #ECFDF5; color: #047857; font-size: 12px; font-weight: 800; padding: 5px 12px; border-radius: 20px; border: 1px solid #A7F3D0;">
                <?= $pourcentagePaye ?>% Recouvré
              </span>
            </div>

            <!-- Barre de progression visuelle -->
            <div style="margin-bottom: 20px;">
              <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 700; color: #64748B; margin-bottom: 6px;">
                <span>Progression du paiement</span>
                <span style="color: #059669; font-weight: 800;"><?= number_format($montantPaye, 0, ',', ' ') ?> FCFA / <?= number_format($montantTotal, 0, ',', ' ') ?> FCFA</span>
              </div>
              <div style="height: 10px; width: 100%; background: #E2E8F0; border-radius: 999px; overflow: hidden; position: relative;">
                <div style="height: 100%; width: <?= $pourcentagePaye ?>%; background: linear-gradient(90deg, #059669 0%, #10B981 100%); border-radius: 999px; transition: width 1s ease-in-out; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.4);"></div>
              </div>
            </div>

            <!-- Grille des 4 KPI financiers principaux -->
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; margin-bottom: 20px;">
              
              <!-- Montant Total -->
              <div style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); border-radius: 14px; padding: 16px; color: #FFFFFF; position: relative; overflow: hidden; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);">
                <div style="position: absolute; right: -10px; top: -10px; width: 60px; height: 60px; background: rgba(255,255,255,0.06); border-radius: 50%;"></div>
                <span style="font-size: 11px; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                  <i data-lucide="target" style="width: 14px; height: 14px; color: #60A5FA;"></i> Montant Total
                </span>
                <div style="font-size: 20px; font-weight: 800; margin-top: 6px; letter-spacing: -0.5px; color: #FFFFFF;">
                  <?= number_format($montantTotal, 0, ',', ' ') ?> <span style="font-size: 12px; font-weight: 600; color: #94A3B8;">FCFA</span>
                </div>
              </div>

              <!-- Montant Payé -->
              <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%); border-radius: 14px; padding: 16px; color: #FFFFFF; position: relative; overflow: hidden; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);">
                <div style="position: absolute; right: -10px; top: -10px; width: 60px; height: 60px; background: rgba(255,255,255,0.08); border-radius: 50%;"></div>
                <span style="font-size: 11px; font-weight: 700; color: #A7F3D0; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                  <i data-lucide="check-circle" style="width: 14px; height: 14px; color: #34D399;"></i> Total Payé
                </span>
                <div style="font-size: 20px; font-weight: 800; margin-top: 6px; letter-spacing: -0.5px; color: #FFFFFF;">
                  <?= number_format($montantPaye, 0, ',', ' ') ?> <span style="font-size: 12px; font-weight: 600; color: #A7F3D0;">FCFA</span>
                </div>
              </div>

              <!-- Solde Restant -->
              <div style="background: linear-gradient(135deg, #E11D48 0%, #BE123C 100%); border-radius: 14px; padding: 16px; color: #FFFFFF; position: relative; overflow: hidden; box-shadow: 0 4px 12px rgba(225, 29, 72, 0.2);">
                <div style="position: absolute; right: -10px; top: -10px; width: 60px; height: 60px; background: rgba(255,255,255,0.08); border-radius: 50%;"></div>
                <span style="font-size: 11px; font-weight: 700; color: #FECDD3; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                  <i data-lucide="alert-circle" style="width: 14px; height: 14px; color: #FB7185;"></i> Solde Restant
                </span>
                <div style="font-size: 20px; font-weight: 800; margin-top: 6px; letter-spacing: -0.5px; color: #FFFFFF;">
                  <?= number_format($montantRestant, 0, ',', ' ') ?> <span style="font-size: 12px; font-weight: 600; color: #FECDD3;">FCFA</span>
                </div>
              </div>

              <!-- Cotisation / Jour -->
              <div style="background: linear-gradient(135deg, #4F46E5 0%, #3730A3 100%); border-radius: 14px; padding: 16px; color: #FFFFFF; position: relative; overflow: hidden; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);">
                <div style="position: absolute; right: -10px; top: -10px; width: 60px; height: 60px; background: rgba(255,255,255,0.08); border-radius: 50%;"></div>
                <span style="font-size: 11px; font-weight: 700; color: #C7D2FE; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                  <i data-lucide="calendar" style="width: 14px; height: 14px; color: #818CF8;"></i> Taux / Jour
                </span>
                <div style="font-size: 20px; font-weight: 800; margin-top: 6px; letter-spacing: -0.5px; color: #FFFFFF;">
                  <?= number_format($prixCotisationJournaliere, 0, ',', ' ') ?> <span style="font-size: 12px; font-weight: 600; color: #C7D2FE;">FCFA</span>
                </div>
              </div>

            </div>

            <!-- Grille des Jours (3 Badges Modernes) -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
              
              <!-- Jours Total -->
              <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 10px; text-align: center;">
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block; margin-bottom: 4px;">Jours Total</span>
                <div style="font-size: 22px; font-weight: 800; color: #0F172A; display: flex; align-items: center; justify-content: center; gap: 4px;">
                  <i data-lucide="clock" style="width: 18px; height: 18px; color: #64748B;"></i> <?= $joursTotal ?>
                </div>
              </div>

              <!-- Jours Payés -->
              <div style="background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 12px; padding: 14px 10px; text-align: center;">
                <span style="font-size: 11px; font-weight: 700; color: #047857; text-transform: uppercase; display: block; margin-bottom: 4px;">Jours Payés</span>
                <div style="font-size: 22px; font-weight: 800; color: #059669; display: flex; align-items: center; justify-content: center; gap: 4px;">
                  <i data-lucide="check" style="width: 18px; height: 18px; color: #059669;"></i> <?= $joursPayes ?>
                </div>
              </div>

              <!-- Jours Restants -->
              <div style="background: #FFF1F2; border: 1px solid #FECDD3; border-radius: 12px; padding: 14px 10px; text-align: center;">
                <span style="font-size: 11px; font-weight: 700; color: #BE123C; text-transform: uppercase; display: block; margin-bottom: 4px;">Jours Restants</span>
                <div style="font-size: 22px; font-weight: 800; color: #E11D48; display: flex; align-items: center; justify-content: center; gap: 4px;">
                  <i data-lucide="hourglass" style="width: 18px; height: 18px; color: #E11D48;"></i> <?= $joursRestants ?>
                </div>
              </div>

            </div>

          </div>

        </div>

        <!-- COLONNE DROITE: Historique & Liste des Cautisations -->
        <div style="flex: 1; min-width: 320px; display: flex; flex-direction: column;">
          <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); height: 100%; display: flex; flex-direction: column;">
            
            <!-- En-tête de la carte -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #F1F5F9; flex-wrap: wrap; gap: 12px;">
              <div style="display: flex; align-items: center; gap: 10px;">
                <span style="width: 36px; height: 36px; border-radius: 10px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(5, 150, 105, 0.15);">
                  <i data-lucide="receipt" style="width: 20px; height: 20px;"></i>
                </span>
                <div>
                  <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
                    Liste des Cautisations
                  </h3>
                  <span style="font-size: 12px; color: #64748B; font-weight: 600;">Historique complet des versements effectués</span>
                </div>
              </div>

              <?php if ($statutSouscription !== 'solde' && ($montantRestant > 0 || $joursRestants > 0)): ?>
                <button class="btn" id="paymentBtn" onclick="openPaymentModal()" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; border: none; border-radius: 10px; padding: 10px 20px; font-weight: 700; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25); transition: all 0.2s ease;">
                  <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Nouveau Paiement
                </button>
              <?php else: ?>
                <span style="background: #ECFDF5; color: #047857; padding: 8px 16px; border-radius: 20px; font-weight: 800; font-size: 12px; border: 1px solid #A7F3D0; display: inline-flex; align-items: center; gap: 6px;">
                  <i data-lucide="check-circle-2" style="width: 16px; height: 16px;"></i> Souscription Soldée
                </span>
              <?php endif; ?>
            </div>

            <!-- Tableau stylisé de l'historique -->
            <div style="overflow-x: auto; flex: 1; border-radius: 12px; border: 1px solid #F1F5F9;">
              <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px; min-width: 500px;">
                <thead>
                  <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                    <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Date & Heure</th>
                    <th style="padding: 12px 14px; text-align: right; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Montant Versé</th>
                    <th style="padding: 12px 14px; text-align: center; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Couverture</th>
                    <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Mode</th>
                    <th style="padding: 12px 14px; text-align: center; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Statut</th>
                  </tr>
                </thead>
                <tbody id="historyBody">
                  <tr><td colspan="5" class="text-center py-5 text-muted" style="font-size: 13px; font-weight: 600;">Chargement de l'historique...</td></tr>
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Modal: Formulaire de Paiement -->
<div class="modal-overlay" id="paymentModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px); padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 900px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">
    <div class="modal-header" style="background: #1E3A5F; color: white; border: none; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
      <h5 class="modal-title" style="font-size: 18px; font-weight: 700; margin: 0; color: white; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="wallet" style="width: 20px; height: 20px;"></i> Formulaire de Paiement
      </h5>
      <button type="button" onclick="closePaymentModal()" style="background: none; border: none; color: white; opacity: 0.8; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 4px;">
        <i data-lucide="x" style="width: 20px; height: 20px; color: white;"></i>
      </button>
    </div>

    <div class="modal-body p-0" style="overflow-y: auto; flex: 1;">
      <!-- Partie supérieure: Récapitulatif -->
      <div style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0; padding: 20px 28px;">
        <h6 style="font-size: 14px; font-weight: 700; color: #1E3A5F; margin: 0 0 12px 0; display: flex; align-items: center; gap: 6px;">
          <i data-lucide="file-text" style="width: 16px; height: 16px;"></i> Récapitulatif de la situation
        </h6>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Client</span>
            <div style="font-size: 16px; font-weight: 700; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($nomClient) ?></div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Code Souscription</span>
            <div style="font-size: 15px; font-weight: 700; color: #1E3A5F; margin-top: 4px;">
              <code style="background: #EFF6FF; padding: 3px 8px; border-radius: 4px;"><?= htmlspecialchars($codeSouscription) ?></code>
            </div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Session</span>
            <div style="font-size: 15px; font-weight: 600; color: #334155; margin-top: 4px;"><?= htmlspecialchars($libelleSession) ?></div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Montant total</span>
            <div style="font-size: 18px; font-weight: 800; color: #1E3A5F; margin-top: 4px;" id="recap_montant_total"><?= number_format($montantTotal, 0, ',', ' ') ?> FCFA</div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Déjà payé</span>
            <div style="font-size: 18px; font-weight: 800; color: #15803D; margin-top: 4px;" id="recap_montant_paye"><?= number_format($montantPaye, 0, ',', ' ') ?> FCFA</div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Restant à payer</span>
            <div style="font-size: 18px; font-weight: 800; color: #DC2626; margin-top: 4px;" id="recap_montant_restant"><?= number_format($montantRestant, 0, ',', ' ') ?> FCFA</div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Cotisation / jour</span>
            <div style="font-size: 18px; font-weight: 800; color: #059669; margin-top: 4px;" id="recap_prix_jour"><?= number_format($prixCotisationJournaliere, 0, ',', ' ') ?> FCFA</div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Jours totaux</span>
            <div style="font-size: 18px; font-weight: 800; color: #0F172A; margin-top: 4px;" id="recap_jours_total"><?= $joursTotal ?></div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Jours restants</span>
            <div style="font-size: 18px; font-weight: 800; color: #DC2626; margin-top: 4px;" id="recap_jours_restants"><?= $joursRestants ?></div>
          </div>
        </div>
      </div>

      <!-- Partie inférieure: Formulaire -->
      <div style="padding: 24px 28px;">
        <h6 style="font-size: 14px; font-weight: 700; color: #1E3A5F; margin: 0 0 16px 0; display: flex; align-items: center; gap: 6px;">
          <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i> Informations de paiement
        </h6>

        <div class="row g-4" style="margin-bottom: 16px; display: flex; gap: 16px; flex-wrap: wrap;">
          <div style="flex: 1; min-width: 240px;">
            <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #1E3A5F; font-size: 13px;">Montant de la cotisation par jour</label>
            <input type="text" id="dailyCotisation" class="form-control" readonly style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 10px 14px; font-size: 15px; font-weight: 700; color: #059669;" value="<?= number_format($prixCotisationJournaliere, 0, ',', ' ') ?> FCFA">
          </div>
          <div style="flex: 1; min-width: 240px;">
            <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #1E3A5F; font-size: 13px;">Mode de paiement</label>
            <select class="form-select" id="paymentMode" style="border-radius: 8px; border: 1px solid #CBD5E1; padding: 10px 14px; font-size: 14px; width: 100%;">
              <option value="especes">Espèces</option>
              <option value="mobile_money">Mobile Money</option>
              <option value="cheque">Chèque</option>
              <option value="virement">Virement bancaire</option>
            </select>
          </div>
        </div>

        <div style="margin-bottom: 16px;">
          <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #1E3A5F; font-size: 13px;">Type de paiement</label>
          <div style="display: flex; gap: 20px; margin-bottom: 4px;">
            <label style="font-size: 13px; color: #334155; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
              <input type="radio" name="type_paiement" id="typeMontant" value="montant" checked style="accent-color: #1E3A5F;">
              Par saisie du montant
            </label>
            <label style="font-size: 13px; color: #334155; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
              <input type="radio" name="type_paiement" id="typeJours" value="jours" style="accent-color: #1E3A5F;">
              Par saisie du nombre de jours
            </label>
          </div>
        </div>

        <div style="margin-bottom: 20px; display: flex; gap: 16px; flex-wrap: wrap;">
          <div style="flex: 1; min-width: 240px;">
            <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #1E3A5F; font-size: 13px;">Montant à verser</label>
            <div style="display: flex;">
              <input type="number" id="montantInput" class="form-control" style="border-radius: 8px 0 0 8px; border: 1px solid #CBD5E1; padding: 10px 14px; font-size: 15px; font-weight: 700; color: #0F172A; width: 100%;" value="<?= (float)$prixCotisationJournaliere ?>" min="0" step="<?= (float)$prixCotisationJournaliere ?>">
              <span style="background: #F8FAFC; border: 1px solid #CBD5E1; border-left: none; border-radius: 0 8px 8px 0; font-weight: 700; color: #64748B; padding: 0 14px; display: flex; align-items: center;">FCFA</span>
            </div>
            <small style="color: #94A3B8; font-size: 11px; display: block; margin-top: 4px;">Doit être un multiple de la cotisation journalière</small>
          </div>
          <div style="flex: 1; min-width: 240px;">
            <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #1E3A5F; font-size: 13px;">Nombre de jours</label>
            <input type="number" id="joursInput" class="form-control" style="border-radius: 8px; border: 1px solid #CBD5E1; padding: 10px 14px; font-size: 15px; font-weight: 700; color: #0F172A;" min="1" value="1">
          </div>
        </div>

        <div style="margin-bottom: 24px;">
          <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #1E3A5F; font-size: 13px;">Date du prochain rendez-vous</label>
          <input type="text" id="nextAppointment" class="form-control" readonly style="background: #EEF2FF; border: 1px solid #C7D2FE; border-radius: 8px; padding: 10px 14px; font-size: 15px; font-weight: 700; color: #1E3A5F;">
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 16px; border-top: 1px solid #E2E8F0;">
          <button type="button" class="btn" onclick="closePaymentModal()" style="background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; border-radius: 8px; padding: 10px 24px; font-weight: 700; cursor: pointer;">
            Annuler
          </button>
          <button type="button" class="btn" id="savePaymentBtn" style="background: #1E3A5F; color: white; border: none; border-radius: 8px; padding: 10px 28px; font-weight: 700; cursor: pointer;">
            <i data-lucide="save" style="width: 18px; height: 18px;"></i> Valider le paiement
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const PRIX_COTISATION  = <?= $prixCotisationJournaliere ?>;
const MONTANT_RESTANT  = <?= $montantRestant ?>;
const JOURS_RESTANTS   = <?= $joursRestants ?>;
const CODE_SOUSCRIPTION = '<?= htmlspecialchars($codeSouscription) ?>';
const CAISSE_OUVERTE   = <?= $caisseOuverte ? 'true' : 'false' ?>;

function notifyToast(msg, type = 'info', title = null) {
    if (window.toastr && typeof window.toastr[type] === 'function') {
        if (title) {
            window.toastr[type](msg, title, { timeOut: 5000, closeButton: true, progressBar: true });
        } else {
            window.toastr[type](msg, '', { timeOut: 5000, closeButton: true, progressBar: true });
        }
    } else if (typeof showToast === 'function') {
        showToast(msg, type, title);
    } else {
        alert((title ? title + ' : ' : '') + msg);
    }
}

function openPaymentModal() {
    if (!CAISSE_OUVERTE) {
        notifyToast('Veuillez effectuer l\'ouverture de caisse avant de collecter des cotisations.', 'warning', 'Caisse fermée');
        return;
    }
    const modal = document.getElementById('paymentModal');
    if (modal) {
        modal.style.display = 'flex';
        if (window.lucide) window.lucide.createIcons();
    }
}

function closePaymentModal() {
    const modal = document.getElementById('paymentModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

document.getElementById('paymentModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closePaymentModal();
    }
});

function formatCurrency(amount) {
    return Number(amount).toLocaleString('fr-FR') + ' FCFA';
}

function calculateNextDate(jours) {
    const now = new Date();
    const next = new Date(now);
    next.setDate(next.getDate() + jours);
    const d = String(next.getDate()).padStart(2, '0');
    const m = String(next.getMonth() + 1).padStart(2, '0');
    const y = next.getFullYear();
    return d + '/' + m + '/' + y;
}

function loadHistory() {
    const tbody = document.getElementById('historyBody');
    if (!tbody) return;

    fetch('<?= RACINE ?>cautisation-payment/history', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'code_souscription=' + encodeURIComponent(CODE_SOUSCRIPTION)
    })
    .then(r => r.json())
    .then(data => {
        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5" style="color: #94A3B8; font-style: italic; font-weight: 600;"><i data-lucide="inbox" style="width: 32px; height: 32px; display: block; margin: 0 auto 8px auto; opacity: 0.5;"></i>Aucune cotisation enregistrée pour le moment.</td></tr>';
            if (window.lucide) window.lucide.createIcons();
            return;
        }
        tbody.innerHTML = data.data.map(c => {
            let modeLabel = (c.mode_paiement || 'especes').toLowerCase();
            let modeStyle = 'background: #F1F5F9; color: #475569;';
            let modeIcon = '💶';

            if (modeLabel.includes('mobile')) {
                modeStyle = 'background: #F3E8FF; color: #7E22CE;';
                modeIcon = '📱';
            } else if (modeLabel.includes('cheque') || modeLabel.includes('chèque')) {
                modeStyle = 'background: #FEF3C7; color: #B45309;';
                modeIcon = '📝';
            } else if (modeLabel.includes('virement')) {
                modeStyle = 'background: #EFF6FF; color: #1D4ED8;';
                modeIcon = '🏦';
            } else {
                modeStyle = 'background: #ECFDF5; color: #047857;';
                modeIcon = '💵';
            }

            let modeClean = modeLabel.replace('_', ' ');
            let modeDisplay = modeIcon + ' ' + modeClean.charAt(0).toUpperCase() + modeClean.slice(1);
            
            const rawStatut = String(c.statut || 'valide').trim().toLowerCase();
            let statutLabel = 'Validé';
            let statutStyle = 'background:#ECFDF5; color:#047857; border: 1px solid #A7F3D0;';
            
            if (rawStatut === 'en attente' || rawStatut === 'en_attente') {
                statutLabel = 'En attente';
                statutStyle = 'background:#FEF3C7; color:#B45309; border: 1px solid #FDE68A;';
            } else if (rawStatut === 'annule' || rawStatut === 'ennule') {
                statutLabel = 'Annulé';
                statutStyle = 'background:#FEE2E2; color:#B91C1C; border: 1px solid #FCA5A5;';
            }

            return '<tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.2s;" onmouseover="this.style.background=\'#F8FAFC\'" onmouseout="this.style.background=\'transparent\'">' +
                '<td style="padding: 12px 14px; color: #0F172A; font-weight: 700; font-size: 13px;">' + (c.date_paiement || '-') + '</td>' +
                '<td style="padding: 12px 14px; text-align: right; font-weight: 800; color: #059669; font-size: 14px;">' + formatCurrency(c.montant) + '</td>' +
                '<td style="padding: 12px 14px; text-align: center;"><span style="background:#EEF2FF; color:#4F46E5; padding:4px 10px; border-radius:8px; font-weight:800; font-size:12px; border: 1px solid #C7D2FE;">' + (c.nombre_jours || 0) + ' j</span></td>' +
                '<td style="padding: 12px 14px;"><span style="' + modeStyle + ' padding:4px 10px; border-radius:8px; font-weight:700; font-size:12px; display:inline-block;">' + modeDisplay + '</span></td>' +
                '<td style="padding: 12px 14px; text-align: center;"><span class="badge" style="' + statutStyle + ' padding:5px 12px; border-radius:20px; font-weight:800; font-size:11px; display:inline-block;">' + statutLabel + '</span></td>' +
            '</tr>';
        }).join('');
    })
    .catch(() => {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4" style="color: #94A3B8;">Erreur de chargement</td></tr>';
    });
}

const montantInput = document.getElementById('montantInput');
const joursInput = document.getElementById('joursInput');
const nextAppointment = document.getElementById('nextAppointment');

function updateCalculations() {
    const typeRadio = document.querySelector('input[name="type_paiement"]:checked');
    const type = typeRadio ? typeRadio.value : 'montant';
    let montant, jours;

    if (type === 'montant') {
        montant = parseFloat(montantInput.value) || 0;
        jours = PRIX_COTISATION > 0 ? Math.floor(montant / PRIX_COTISATION) : 0;
        joursInput.value = jours;
    } else {
        jours = parseInt(joursInput.value) || 1;
        montant = jours * PRIX_COTISATION;
        montantInput.value = montant;
    }
    nextAppointment.value = calculateNextDate(jours);
}

montantInput.addEventListener('input', updateCalculations);
joursInput.addEventListener('input', updateCalculations);
document.getElementById('typeMontant').addEventListener('change', updateCalculations);
document.getElementById('typeJours').addEventListener('change', updateCalculations);

document.getElementById('savePaymentBtn').addEventListener('click', function() {
    const montant = parseFloat(montantInput.value) || 0;
    const jours = parseInt(joursInput.value) || 0;
    const mode = document.getElementById('paymentMode').value;
    const typeRadio = document.querySelector('input[name="type_paiement"]:checked');
    const type = typeRadio ? typeRadio.value : 'montant';

    if (montant <= 0) {
        notifyToast('Le montant doit être supérieur à 0 FCFA.', 'warning', 'Montant invalide');
        return;
    }
    if (jours <= 0) {
        notifyToast('Le nombre de jours doit être au moins de 1 jour.', 'warning', 'Nombre de jours invalide');
        return;
    }
    if (PRIX_COTISATION > 0 && montant % PRIX_COTISATION > 0.01) {
        notifyToast('Le montant doit être un multiple de ' + formatCurrency(PRIX_COTISATION) + '.', 'warning', 'Montant incorrect');
        return;
    }
    if (montant > MONTANT_RESTANT) {
        notifyToast('Le montant dépasse le solde restant à payer (' + formatCurrency(MONTANT_RESTANT) + ').', 'warning', 'Plafond dépassé');
        return;
    }
    if (jours > JOURS_RESTANTS) {
        notifyToast('Le nombre de jours dépasse le nombre de jours restants (' + JOURS_RESTANTS + ' jours).', 'warning', 'Plafond dépassé');
        return;
    }

    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader-2" class="spinner-border spinner-border-sm"></i> Enregistrement...';

    const formData = new URLSearchParams();
    formData.append('code_souscription', CODE_SOUSCRIPTION);
    formData.append('montant', montant);
    formData.append('nombre_jours', jours);
    formData.append('mode_paiement', mode);
    formData.append('type_paiement', type);

    fetch('<?= RACINE ?>cautisation-payment/savepayment', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(r => r.json())
    .then(result => {
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="save" style="width: 18px; height: 18px;"></i> Valider le paiement';

        if (result.status === 1) {
            closePaymentModal();
            loadHistory();
            updateCalculations();
            
            const msgSuccess = (result.message || 'Paiement enregistré avec succès !') + 
              (result.code_cautisation ? ' [Code : ' + result.code_cautisation + ']' : '') +
              (result.prochain_rdv ? ' | Prochain RDV : ' + result.prochain_rdv : '');

            notifyToast(msgSuccess, 'success', 'Paiement effectué');
            setTimeout(function() { location.reload(); }, 1500);
        } else {
            notifyToast(result.message || 'Erreur lors de l\'enregistrement du paiement', 'error', 'Échec du paiement');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="save" style="width: 18px; height: 18px;"></i> Valider le paiement';
        notifyToast(err.message || 'Erreur réseau lors du paiement', 'error', 'Erreur système');
    });
});

loadHistory();
updateCalculations();
</script>

<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
