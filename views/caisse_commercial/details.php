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

        <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;" class="no-print">
          <a href="<?= RACINE ?>caisse_commercial/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour à la liste
          </a>
          <button onclick="window.print()" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 10px 20px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); cursor: pointer;">
            <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Imprimer le PV
          </button>

          <?php if ((Context::isFinance() || Context::isAdmin()) && !empty($versementLinked)): ?>
            <button type="button" class="btn btn-valider-versement" 
              data-id="<?= $versementLinked['id_versement'] ?>"
              data-code="<?= htmlspecialchars($versementLinked['code_versement_commercial'] ?? '') ?>"
              data-commercial="<?= htmlspecialchars(trim(($item['nom_user'] ?? '') . ' ' . ($item['prenom_user'] ?? ''))) ?>"
              data-zone="<?= htmlspecialchars($item['zone_code'] ?? '') ?>"
              data-montant="<?= (float)($versementLinked['montant_versement'] ?? 0) ?>"
              data-periode="<?= htmlspecialchars($versementLinked['periode_versement'] ?? date('Y-m-d')) ?>"
              data-ref="<?= htmlspecialchars($item['code_caisse'] ?? '') ?>"
              data-statut="<?= htmlspecialchars($versementLinked['statut_versement'] ?? '') ?>"
              style="background: #059669; color: white; font-weight: 800; border-radius: 10px; padding: 10px 20px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25); cursor: pointer;" 
              title="Contrôler et Valider le versement">
              <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i> Valider le Versement
            </button>
          <?php endif; ?>
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

<!-- CSRF TOKEN -->
<input type="hidden" id="csrf_token" value="<?= Validator::generateCsrfToken() ?>">

<!-- MODALE DE VALIDATION COMPTABLE DU VERSEMENT -->
<div class="modal-overlay" id="modalValiderVersement" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(5px); padding: 16px;">
  <div class="modal" style="max-width: 750px; width: 100%; background: #FFFFFF; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.3); overflow: hidden; border: 1px solid #E2E8F0; margin: auto;">
    <div class="modal-header" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; padding: 20px 24px; display: flex; justify-content: space-between; align-items: center;">
      <h3 class="modal-title" style="font-weight: 800; font-size: 16px; margin: 0; color: #FFFFFF; display: flex; align-items: center; gap: 10px;">
        <i data-lucide="check-circle" style="width: 20px; height: 20px; color: #10B981;"></i> Contrôle & Validation Comptable
      </h3>
      <button type="button" class="modal-close-btn" id="modalValiderClose" style="background: rgba(255,255,255,0.12); border: none; color: #FFFFFF; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; transition: background 0.2s;" title="Fermer">
        <i data-lucide="x" style="width: 18px; height: 18px;"></i>
      </button>
    </div>

    <form id="form-valider-versement" style="margin: 0;">
      <input type="hidden" name="id_versement" id="val_id_versement" value="">
      <div class="modal-body" style="padding: 24px; max-height: 78vh; overflow-y: auto;">
        
        <!-- CARTE RÉCAPITULATIVE DU VERSEMENT -->
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px; margin-bottom: 20px;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; border-bottom: 1px dashed #CBD5E1; padding-bottom: 10px; flex-wrap: wrap; gap: 8px;">
            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Code Versement</span>
              <div style="font-weight: 800; font-size: 15px; color: #1E3A5F; font-family: monospace;" id="val_code_versement">-</div>
            </div>
            <div style="text-align: right;">
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Montant Versé</span>
              <div style="font-weight: 900; font-size: 18px; color: #059669;" id="val_montant_fmt">0 FCFA</div>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; font-size: 13px;">
            <div>
              <span style="color: #64748B; font-weight: 600;">Commercial :</span> 
              <strong id="val_commercial_nom" style="color: #0F172A; display: block; margin-top: 2px;">-</strong>
            </div>
            <div>
              <span style="color: #64748B; font-weight: 600;">Zone :</span> 
              <strong id="val_zone_nom" style="color: #0F172A; display: block; margin-top: 2px;">-</strong>
            </div>
            <div>
              <span style="color: #64748B; font-weight: 600;">Période :</span> 
              <strong id="val_periode_txt" style="color: #0F172A; display: block; margin-top: 2px;">-</strong>
            </div>
            <div>
              <span style="color: #64748B; font-weight: 600;">Bordereau / Réf :</span> 
              <strong id="val_reference_txt" style="color: #0F172A; font-family: monospace; display: block; margin-top: 2px;">-</strong>
            </div>
          </div>
        </div>

        <!-- BLOC HISTORIQUE ACTIVITÉ CAISSE COMMERCIAL -->
        <div style="background: #F1F5F9; border: 1px solid #CBD5E1; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
            <h4 style="font-size: 13px; font-weight: 800; color: #1E3A5F; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="wallet" style="width: 16px; height: 16px; color: #059669;"></i> Contrôle de la Caisse & Activité Commercial
            </h4>
            <span style="font-size: 11px; font-weight: 700; color: #059669; background: #ECFDF5; border: 1px solid #A7F3D0; padding: 2px 10px; border-radius: 12px;">
              Dépouillement des transactions
            </span>
          </div>

          <!-- LOADER / SPINNER -->
          <div id="caisse_history_loading" style="display: none; text-align: center; padding: 20px; color: #64748B;">
            <i class="fa fa-spinner fa-spin" style="font-size: 20px; color: #1E3A5F;"></i>
            <p style="font-size: 12px; margin-top: 8px; font-weight: 600;">Chargement des transactions de la caisse...</p>
          </div>

          <!-- CONTENU HISTORIQUE CAISSE -->
          <div id="caisse_history_content">
            
            <!-- BANDEAU SYNTHÈSE CAISSE LIÉE -->
            <div id="box-linked-caisse" style="background: #FFFFFF; border: 1.5px solid #CBD5E1; border-radius: 10px; padding: 14px; margin-bottom: 14px; display: none;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; border-bottom: 1px dashed #E2E8F0; padding-bottom: 6px; flex-wrap: wrap; gap: 6px;">
                <span style="font-size: 11px; font-weight: 800; color: #1E3A5F; text-transform: uppercase;">Séance de Caisse Associée</span>
                <code id="hist_linked_caisse_code" style="font-weight: 800; color: #1E3A5F; background: #F1F5F9; padding: 2px 8px; border-radius: 4px; font-size: 12px; border: 1px solid #CBD5E1;">-</code>
              </div>
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px;">
                <div>
                  <span style="font-size: 10px; font-weight: 700; color: #64748B; text-transform: uppercase;">Attendu Caisse</span>
                  <strong id="hist_caisse_attendu" style="font-size: 14px; font-weight: 900; color: #1E3A5F; display: block; margin-top: 2px;">0 FCFA</strong>
                </div>
                <div>
                  <span style="font-size: 10px; font-weight: 700; color: #64748B; text-transform: uppercase;">Pot Déclaré</span>
                  <strong id="hist_caisse_pot" style="font-size: 14px; font-weight: 900; color: #059669; display: block; margin-top: 2px;">0 FCFA</strong>
                </div>
                <div>
                  <span style="font-size: 10px; font-weight: 700; color: #64748B; text-transform: uppercase;">Écart Séance</span>
                  <strong id="hist_caisse_ecart" style="font-size: 14px; font-weight: 900; color: #059669; display: block; margin-top: 2px;">0 FCFA</strong>
                </div>
              </div>
            </div>

            <!-- GRILLE KPI GLOBAUX DU COMMERCIAL -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px; margin-bottom: 14px;">
              <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px;">
                <span style="font-size: 10px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block;">Cumul Encaissé</span>
                <strong id="hist_total_encaisse" style="font-size: 13px; font-weight: 800; color: #0F172A; display: block; margin-top: 2px;">0 FCFA</strong>
                <span id="hist_details_modes" style="font-size: 10px; color: #64748B; font-weight: 500;">Esp: 0 | MoMo: 0</span>
              </div>
              <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px;">
                <span style="font-size: 10px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block;">Versements Validés</span>
                <strong id="hist_versements_valides" style="font-size: 13px; font-weight: 800; color: #059669; display: block; margin-top: 2px;">0 FCFA</strong>
                <span style="font-size: 10px; color: #64748B; font-weight: 500;">Déjà régularisés</span>
              </div>
              <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px;">
                <span style="font-size: 10px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block;">Reste à Verser</span>
                <strong id="hist_reste_a_verser" style="font-size: 13px; font-weight: 800; color: #D97706; display: block; margin-top: 2px;">0 FCFA</strong>
                <span style="font-size: 10px; color: #64748B; font-weight: 500;">Attendu global</span>
              </div>
              <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px;">
                <span style="font-size: 10px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block;">Conformité</span>
                <strong id="hist_ecart_verif" style="font-size: 13px; font-weight: 800; color: #2563EB; display: block; margin-top: 2px;">0 FCFA</strong>
                <span id="hist_ecart_badge" style="font-size: 10px; font-weight: 700; color: #059669;">Conforme</span>
              </div>
            </div>

            <!-- ONGLETS / SECTIONS SOUS-TABLEAUX -->
            <div style="margin-top: 10px;">
              <div style="display: flex; gap: 8px; border-bottom: 1px solid #CBD5E1; margin-bottom: 8px; flex-wrap: wrap;">
                <button type="button" id="tab-btn-caisse-cotis" class="tab-hist-btn active" style="background: #FFFFFF; border: 1px solid #CBD5E1; border-bottom: none; border-radius: 6px 6px 0 0; padding: 6px 14px; font-size: 11px; font-weight: 800; color: #059669; cursor: pointer;">
                  Cotisations de cette Caisse (<span id="hist_nb_caisse_cotis">0</span>)
                </button>
                <button type="button" id="tab-btn-cotis" class="tab-hist-btn" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-bottom: none; border-radius: 6px 6px 0 0; padding: 6px 12px; font-size: 11px; font-weight: 700; color: #64748B; cursor: pointer;">
                  Toutes Récentes (<span id="hist_nb_cotis">0</span>)
                </button>
                <button type="button" id="tab-btn-sessions" class="tab-hist-btn" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-bottom: none; border-radius: 6px 6px 0 0; padding: 6px 12px; font-size: 11px; font-weight: 700; color: #64748B; cursor: pointer;">
                  Sessions Caisse (<span id="hist_nb_sessions">0</span>)
                </button>
              </div>

              <!-- TABLEAU COTISATIONS DE CETTE CAISSE (DÉPOUILLEMENT) -->
              <div id="tab-content-caisse-cotis" style="max-height: 200px; overflow-y: auto; background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                  <thead>
                    <tr style="background: #F8FAFC; color: #64748B; border-bottom: 1px solid #E2E8F0; text-align: left;">
                      <th style="padding: 6px 10px;">Souscription</th>
                      <th style="padding: 6px 10px;">Client</th>
                      <th style="padding: 6px 10px;">Mode</th>
                      <th style="padding: 6px 10px;">Date & Heure</th>
                      <th style="padding: 6px 10px; text-align: right;">Montant</th>
                      <th style="padding: 6px 10px; text-align: center;">Statut</th>
                    </tr>
                  </thead>
                  <tbody id="hist_tbody_caisse_cotis">
                    <tr><td colspan="6" style="text-align: center; padding: 12px; color: #94A3B8;">Aucune cotisation attachée à cette caisse</td></tr>
                  </tbody>
                </table>
              </div>

              <!-- TABLEAU TOUTES COTISATIONS RÉCENTES -->
              <div id="tab-content-cotis" style="display: none; max-height: 200px; overflow-y: auto; background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                  <thead>
                    <tr style="background: #F8FAFC; color: #64748B; border-bottom: 1px solid #E2E8F0; text-align: left;">
                      <th style="padding: 6px 10px;">Code / Date</th>
                      <th style="padding: 6px 10px;">Client</th>
                      <th style="padding: 6px 10px;">Mode</th>
                      <th style="padding: 6px 10px; text-align: right;">Montant</th>
                      <th style="padding: 6px 10px; text-align: center;">Statut</th>
                    </tr>
                  </thead>
                  <tbody id="hist_tbody_cotis">
                    <tr><td colspan="5" style="text-align: center; padding: 12px; color: #94A3B8;">Aucune cotisation récente</td></tr>
                  </tbody>
                </table>
              </div>

              <!-- TABLEAU SESSIONS DE CAISSE -->
              <div id="tab-content-sessions" style="display: none; max-height: 200px; overflow-y: auto; background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                  <thead>
                    <tr style="background: #F8FAFC; color: #64748B; border-bottom: 1px solid #E2E8F0; text-align: left;">
                      <th style="padding: 6px 10px;">Code Caisse</th>
                      <th style="padding: 6px 10px;">Ouverture</th>
                      <th style="padding: 6px 10px;">Clôture</th>
                      <th style="padding: 6px 10px; text-align: right;">Dépôt</th>
                      <th style="padding: 6px 10px; text-align: center;">Statut</th>
                    </tr>
                  </thead>
                  <tbody id="hist_tbody_sessions">
                    <tr><td colspan="5" style="text-align: center; padding: 12px; color: #94A3B8;">Aucune session de caisse</td></tr>
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>

        <!-- DÉCISION DU CONTRÔLE -->
        <div class="form-group" style="margin-bottom: 18px;">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
            Décision de validation <span style="color: #EF4444;">*</span>
          </label>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
            <label style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1.5px solid #A7F3D0; background: #ECFDF5; border-radius: 10px; cursor: pointer; font-weight: 700; font-size: 13px; color: #047857;">
              <input type="radio" name="statut_versement" value="valide" checked style="accent-color: #059669; width: 16px; height: 16px;">
              <span>Valider le versement & la caisse</span>
            </label>
            <label style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1.5px solid #FECACA; background: #FEF2F2; border-radius: 10px; cursor: pointer; font-weight: 700; font-size: 13px; color: #DC2626;">
              <input type="radio" name="statut_versement" value="annule" style="accent-color: #DC2626; width: 16px; height: 16px;">
              <span>Rejeter / Annuler</span>
            </label>
          </div>
        </div>

        <!-- COMMENTAIRE / OBSERVATIONS -->
        <div class="form-group" style="margin-bottom: 0;">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Observations / Note de validation
          </label>
          <textarea name="commentaire_validation" id="val_commentaire" rows="3" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px; font-size: 13px; font-weight: 500; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" placeholder="Ex: Montant vérifié et perçu sans écart par la caisse centrale."></textarea>
          <p style="font-size: 11px; color: #64748B; margin: 6px 0 0 0; display: flex; align-items: center; gap: 4px;">
            <i data-lucide="info" style="width: 13px; height: 13px; color: #0284C7;"></i> 
            La validation basculera automatiquement la séance de caisse et toutes ses cotisations en statut « validé ».
          </p>
        </div>

      </div>

      <div class="modal-footer" style="background: #F8FAFC; padding: 16px 24px; border-top: 1px solid #E2E8F0; display: flex; justify-content: flex-end; gap: 12px;">
        <button type="button" class="btn modal-close-btn" style="background: #FFFFFF; color: #475569; font-weight: 700; border-radius: 8px; padding: 10px 18px; border: 1px solid #CBD5E1; font-size: 13px; cursor: pointer;">
          Annuler
        </button>
        <button type="submit" id="btn-submit-valider-versement" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; font-weight: 800; border-radius: 8px; padding: 10px 22px; border: none; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
          <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i> Confirmer la Décision
        </button>
      </div>
    </form>
  </div>
</div>

<script src="<?= RACINE ?>public/assets/js/modules/versements.js?v=1.4"></script>
<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
