<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$currentSection = 'cautisation'; 
$caisseOuverte  = $caisseOuverte ?? false;
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">

      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="users" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Recherche & Encaissement Cautisations
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Sélectionnez un client dans la liste pour consulter ses souscriptions et effectuer un paiement
            </p>
          </div>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <button type="button" id="btnRefresh" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;">
            <i data-lucide="refresh-cw" style="width: 16px; height: 16px; color: #64748B;"></i> Réinitialiser
          </button>
          <a href="<?= RACINE ?>souscription/wizard" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 20px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); text-decoration: none; transition: all 0.2s ease;">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Nouvelle souscription
          </a>
        </div>
      </div>

      <!-- FORMULAIRE DE SELECTION CLIENT (SELECT2) -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box; margin-bottom: 24px;">
        <label style="display: block; font-weight: 800; font-size: 14px; color: #0F172A; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="user-check" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Sélectionner un client
        </label>
        
        <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
          <div style="flex: 1; min-width: 280px;">
            <select id="selectClient" class="form-control select2" style="width: 100%;">
              <option value="">-- Rechercher ou choisir un client dans la liste --</option>
              <?php if (!empty($clients) && is_array($clients)): ?>
                <?php foreach ($clients as $client): ?>
                  <option value="<?= htmlspecialchars($client['code_client']) ?>">
                    <?= htmlspecialchars($client['nom_client']) ?><?= !empty($client['telephone_client']) ? ' (' . htmlspecialchars($client['telephone_client']) . ')' : '' ?> - [<?= htmlspecialchars($client['code_client']) ?>]
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <button type="button" id="searchBtn" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 12px; padding: 14px 28px; font-size: 15px; border: none; display: inline-flex; align-items: center; gap: 10px; cursor: pointer; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.25); min-width: 160px; justify-content: center;">
            <i data-lucide="search" style="width: 18px; height: 18px;"></i> Rechercher
          </button>
        </div>

        <!-- Informations d'aide -->
        <div style="display: flex; align-items: center; gap: 8px; margin-top: 14px; flex-wrap: wrap;">
          <span style="font-size: 12px; font-weight: 700; color: #64748B;">Astuce :</span>
          <span style="font-size: 12px; color: #64748B;">Vous pouvez saisir le nom, le numéro de téléphone ou le code du client directement dans la liste déroulante Select2.</span>
        </div>
      </div>

      <!-- RÉSULTATS DE RECHERCHE -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box;">
        
        <!-- Placeholder initial -->
        <div id="searchPlaceholder" style="text-align: center; padding: 60px 20px; color: #94A3B8;">
          <div style="width: 64px; height: 64px; border-radius: 20px; background: #F8FAFC; color: #94A3B8; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto; border: 1px solid #E2E8F0;">
            <i data-lucide="search" style="width: 32px; height: 32px; opacity: 0.7;"></i>
          </div>
          <h4 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0 0 6px 0;">Effectuez une recherche</h4>
          <p style="font-size: 13px; margin: 0; color: #64748B;">Sélectionnez un client ci-dessus et cliquez sur <strong>Rechercher</strong> pour afficher immédiatement ses souscriptions et sa situation financière.</p>
        </div>

        <!-- Conteneur des résultats -->
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; display: none;" id="searchResultsContainer">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="layers" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Souscriptions disponibles pour ce client
            </h3>
          </div>

          <table id="table-search-results" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse; font-size: 13px;">
            <thead>
              <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Client</th>
                <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Téléphone</th>
                <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Code Souscription</th>
                <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Session</th>
                <th style="padding: 12px 14px; text-align: right; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Montant Total</th>
                <th style="padding: 12px 14px; text-align: center; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Statut</th>
                <th style="padding: 12px 14px; text-align: right; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Action</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

      </div>

    </div>
  </main>
</div>

<!-- Modal: Formulaire de Paiement Intégré -->
<div class="modal-overlay" id="paymentModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px); padding: 16px;">
  <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 900px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">
    <div class="modal-header" style="background: #1E3A5F; color: white; border: none; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
      <h5 class="modal-title" style="font-size: 18px; font-weight: 700; margin: 0; color: white; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="wallet" style="width: 20px; height: 20px;"></i> Formulaire de Paiement Cautisation
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
            <div style="font-size: 16px; font-weight: 700; color: #0F172A; margin-top: 4px;" id="recap_nom_client">-</div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Code Souscription</span>
            <div style="font-size: 15px; font-weight: 700; color: #1E3A5F; margin-top: 4px;">
              <code style="background: #EFF6FF; padding: 3px 8px; border-radius: 4px;" id="recap_code_souscription">-</code>
            </div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Session</span>
            <div style="font-size: 15px; font-weight: 600; color: #334155; margin-top: 4px;" id="recap_session">-</div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Montant total</span>
            <div style="font-size: 18px; font-weight: 800; color: #1E3A5F; margin-top: 4px;" id="recap_montant_total">0 FCFA</div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Déjà payé</span>
            <div style="font-size: 18px; font-weight: 800; color: #15803D; margin-top: 4px;" id="recap_montant_paye">0 FCFA</div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Restant à payer</span>
            <div style="font-size: 18px; font-weight: 800; color: #DC2626; margin-top: 4px;" id="recap_montant_restant">0 FCFA</div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Cotisation / jour</span>
            <div style="font-size: 18px; font-weight: 800; color: #059669; margin-top: 4px;" id="recap_prix_jour">0 FCFA</div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Jours totaux</span>
            <div style="font-size: 18px; font-weight: 800; color: #0F172A; margin-top: 4px;" id="recap_jours_total">0</div>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">Jours restants</span>
            <div style="font-size: 18px; font-weight: 800; color: #DC2626; margin-top: 4px;" id="recap_jours_restants">0</div>
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
            <input type="text" id="dailyCotisation" class="form-control" readonly style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 10px 14px; font-size: 15px; font-weight: 700; color: #059669;" value="0 FCFA">
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
              <input type="number" id="montantInput" class="form-control" style="border-radius: 8px 0 0 8px; border: 1px solid #CBD5E1; padding: 10px 14px; font-size: 15px; font-weight: 700; color: #0F172A; width: 100%;" value="0" min="0">
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
const CAISSE_OUVERTE = <?= $caisseOuverte ? 'true' : 'false' ?>;
let CURRENT_SOUSCRIPTION = null;
let PRIX_COTISATION = 0;
let MONTANT_RESTANT = 0;
let JOURS_RESTANTS = 0;
let CODE_SOUSCRIPTION = '';

$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  // Initialisation de Select2
  $('#selectClient').select2({
    placeholder: "-- Rechercher ou choisir un client dans la liste --",
    allowClear: true,
    width: '100%'
  });

  const searchBtn = document.getElementById('searchBtn');
  const searchResultsContainer = document.getElementById('searchResultsContainer');
  const searchPlaceholder = document.getElementById('searchPlaceholder');
  let dataTable = null;

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  function formatCurrency(amount) {
    return Number(amount || 0).toLocaleString('fr-FR') + ' FCFA';
  }

  function calculateNextDate(jours) {
    const now = new Date();
    const next = new Date(now);
    next.setDate(next.getDate() + (parseInt(jours) || 1));
    const d = String(next.getDate()).padStart(2, '0');
    const m = String(next.getMonth() + 1).padStart(2, '0');
    const y = next.getFullYear();
    return d + '/' + m + '/' + y;
  }

  function updateCalculations() {
    const typeRadio = document.querySelector('input[name="type_paiement"]:checked');
    const type = typeRadio ? typeRadio.value : 'montant';
    let montant, jours;

    const montantInput = document.getElementById('montantInput');
    const joursInput = document.getElementById('joursInput');
    const nextAppointment = document.getElementById('nextAppointment');

    if (!montantInput || !joursInput) return;

    if (type === 'montant') {
      montant = parseFloat(montantInput.value) || 0;
      jours = PRIX_COTISATION > 0 ? Math.floor(montant / PRIX_COTISATION) : 0;
      joursInput.value = jours;
    } else {
      jours = parseInt(joursInput.value) || 1;
      montant = jours * PRIX_COTISATION;
      montantInput.value = montant;
    }
    if (nextAppointment) {
      nextAppointment.value = calculateNextDate(jours);
    }
  }

  $('#montantInput, #joursInput').on('input', updateCalculations);
  $('input[name="type_paiement"]').on('change', updateCalculations);

  function renderStatut(statut) {
    const s = String(statut || '').toLowerCase();
    if (s === 'valide' || s === 'validé') {
      return '<span style="background: #ECFDF5; color: #047857; padding: 5px 12px; border-radius: 20px; font-weight: 800; font-size: 11px; border: 1px solid #A7F3D0; display: inline-flex; align-items: center; gap: 4px;"><i data-lucide="check-circle" style="width:12px; height:12px;"></i> Validée</span>';
    } else if (s === 'solde' || s === 'soldé') {
      return '<span style="background: #EFF6FF; color: #1D4ED8; padding: 5px 12px; border-radius: 20px; font-weight: 800; font-size: 11px; border: 1px solid #BFDBFE; display: inline-flex; align-items: center; gap: 4px;"><i data-lucide="shield-check" style="width:12px; height:12px;"></i> Soldée</span>';
    } else if (s === 'annule' || s === 'annulé') {
      return '<span style="background: #FEE2E2; color: #B91C1C; padding: 5px 12px; border-radius: 20px; font-weight: 800; font-size: 11px; border: 1px solid #FCA5A5; display: inline-flex; align-items: center; gap: 4px;"><i data-lucide="x-circle" style="width:12px; height:12px;"></i> Annulée</span>';
    } else if (s === 'reconduite') {
      return '<span style="background: #FEF3C7; color: #B45309; padding: 5px 12px; border-radius: 20px; font-weight: 800; font-size: 11px; border: 1px solid #FDE68A; display: inline-flex; align-items: center; gap: 4px;"><i data-lucide="rotate-cw" style="width:12px; height:12px;"></i> Reconduite</span>';
    }
    return '<span style="background: #F1F5F9; color: #475569; padding: 5px 12px; border-radius: 20px; font-weight: 700; font-size: 11px;">' + escapeHtml(statut || '-') + '</span>';
  }

  window.openPaymentModalFor = function(codeSouscription) {
    if (!CAISSE_OUVERTE) {
      toastr.warning('Veuillez effectuer l\'ouverture de caisse avant de collecter des cotisations.', 'Caisse fermée');
      return;
    }

    $.ajax({
      url: '<?= RACINE ?>cautisation-payment/situation-details',
      type: 'POST',
      data: { code_souscription: codeSouscription },
      dataType: 'json',
      success: function(res) {
        if (!res.data) {
          toastr.error(res.error || 'Impossible de charger les détails de la souscription.');
          return;
        }

        const s = res.data;
        CURRENT_SOUSCRIPTION = s;
        CODE_SOUSCRIPTION = s.code_souscription || '';

        const montantTotal = parseFloat(s.montant_total_a_payer || s.montant_total || s.totale_souscription || 0);
        const montantPaye = parseFloat(s.montant_total_paye || s.total_cotise || s.montant_total_cotise || 0);
        MONTANT_RESTANT = s.solde_restant !== undefined ? parseFloat(s.solde_restant) : Math.max(0, montantTotal - montantPaye);

        const joursTotal = parseInt(s.nombre_jours_total || s.duree_totale_jours || s.nombre_jour_session || 0);
        const joursPayes = parseInt(s.nombre_jours_payes || s.nombre_jour_cotise || 0);
        JOURS_RESTANTS = s.jours_restants !== undefined ? parseInt(s.jours_restants) : Math.max(0, joursTotal - joursPayes);

        PRIX_COTISATION = parseFloat(s.prix_cotisation_pack || s.prix_cotisation_journaliere || s.montant_cotisation_journaliere || 0);

        if (MONTANT_RESTANT <= 0 || JOURS_RESTANTS <= 0) {
          toastr.info('Cette souscription est déjà entièrement soldée.', 'Souscription soldée');
          return;
        }

        // Remplissage du récapitulatif
        $('#recap_nom_client').text(s.nom_client || '-');
        $('#recap_code_souscription').text(CODE_SOUSCRIPTION);
        $('#recap_session').text(s.libelle_session || '-');
        $('#recap_montant_total').text(formatCurrency(montantTotal));
        $('#recap_montant_paye').text(formatCurrency(montantPaye));
        $('#recap_montant_restant').text(formatCurrency(MONTANT_RESTANT));
        $('#recap_prix_jour').text(formatCurrency(PRIX_COTISATION));
        $('#recap_jours_total').text(joursTotal);
        $('#recap_jours_restants').text(JOURS_RESTANTS);

        $('#dailyCotisation').val(formatCurrency(PRIX_COTISATION));
        $('#montantInput').val(PRIX_COTISATION).attr('step', PRIX_COTISATION);
        $('#joursInput').val(1);
        $('#paymentMode').val('especes');
        $('#typeMontant').prop('checked', true);

        updateCalculations();

        $('#paymentModal').css('display', 'flex');
        if (window.lucide) window.lucide.createIcons();
      },
      error: function() {
        toastr.error('Erreur réseau lors de la récupération des détails.');
      }
    });
  };

  window.closePaymentModal = function() {
    $('#paymentModal').css('display', 'none');
  };

  $('#paymentModal').on('click', function(e) {
    if (e.target === this) {
      closePaymentModal();
    }
  });

  $('#savePaymentBtn').on('click', function() {
    const montantInput = document.getElementById('montantInput');
    const joursInput = document.getElementById('joursInput');
    const montant = parseFloat(montantInput.value) || 0;
    const jours = parseInt(joursInput.value) || 0;
    const mode = $('#paymentMode').val();
    const typeRadio = document.querySelector('input[name="type_paiement"]:checked');
    const type = typeRadio ? typeRadio.value : 'montant';

    if (montant <= 0) {
      toastr.warning('Le montant doit être supérieur à 0 FCFA.', 'Montant invalide');
      return;
    }
    if (jours <= 0) {
      toastr.warning('Le nombre de jours doit être au moins de 1 jour.', 'Nombre de jours invalide');
      return;
    }
    if (PRIX_COTISATION > 0 && (montant % PRIX_COTISATION) > 0.01) {
      toastr.warning('Le montant doit être un multiple de ' + formatCurrency(PRIX_COTISATION) + '.', 'Montant incorrect');
      return;
    }
    if (montant > MONTANT_RESTANT) {
      toastr.warning('Le montant dépasse le solde restant à payer (' + formatCurrency(MONTANT_RESTANT) + ').', 'Plafond dépassé');
      return;
    }
    if (jours > JOURS_RESTANTS) {
      toastr.warning('Le nombre de jours dépasse le nombre de jours restants (' + JOURS_RESTANTS + ' jours).', 'Plafond dépassé');
      return;
    }

    const btn = $(this);
    btn.prop('disabled', true).html('<i data-lucide="loader-2" class="spinner-border spinner-border-sm"></i> Enregistrement...');

    const formData = new URLSearchParams();
    formData.append('code_souscription', CODE_SOUSCRIPTION);
    formData.append('montant', montant);
    formData.append('nombre_jours', jours);
    formData.append('mode_paiement', mode);
    formData.append('type_paiement', type);

    $.ajax({
      url: '<?= RACINE ?>cautisation-payment/savepayment',
      type: 'POST',
      data: formData.toString(),
      contentType: 'application/x-www-form-urlencoded',
      dataType: 'json',
      success: function(result) {
        btn.prop('disabled', false).html('<i data-lucide="save" style="width: 18px; height: 18px;"></i> Valider le paiement');
        if (result.status === 1) {
          closePaymentModal();
          const msgSuccess = (result.message || 'Paiement enregistré avec succès !') +
            (result.code_cautisation ? ' [Code : ' + result.code_cautisation + ']' : '');
          toastr.success(msgSuccess, 'Paiement effectué');
          if (dataTable) {
            dataTable.ajax.reload(null, false);
          }
        } else {
          toastr.error(result.message || 'Erreur lors de l\'enregistrement du paiement', 'Échec du paiement');
        }
      },
      error: function(xhr) {
        btn.prop('disabled', false).html('<i data-lucide="save" style="width: 18px; height: 18px;"></i> Valider le paiement');
        let errStr = 'Erreur réseau lors du paiement';
        try {
          const res = JSON.parse(xhr.responseText);
          if (res.error) errStr = res.error;
          if (res.message) errStr = res.message;
        } catch(e){}
        toastr.error(errStr, 'Erreur système');
      }
    });
  });

  function performSearch() {
    const selectedClientCode = $('#selectClient').val();

    if (!selectedClientCode) {
      toastr.error('Veuillez sélectionner un client dans la liste.');
      return;
    }

    if (dataTable) {
      dataTable.destroy();
      dataTable = null;
    }

    searchPlaceholder.style.display = 'none';
    searchResultsContainer.style.display = 'block';

    const table = $('#table-search-results').DataTable({
      ajax: {
        url: '<?= RACINE ?>cautisation-payment/search',
        type: 'POST',
        data: function(d) {
          d.criteria = selectedClientCode;
          d.type = 'all';
        },
        dataSrc: function(json) {
          if (json.error) {
            toastr.error(json.error);
            return [];
          }
          if (json.message) {
            toastr.info(json.message);
            return [];
          }
          return json.data || [];
        },
        error: function() {
          toastr.error('Erreur réseau lors de la recherche.');
          return [];
        }
      },
      processing: true,
      autoWidth: false,
      createdRow: function(row) {
        $(row).css('cursor', 'pointer');
      },
      columns: [
        { data: 'nom_complet', render: function(d) {
            return '<div style="font-weight:800; color:#0F172A; font-size:14px;">' + escapeHtml(d || '-') + '</div>';
        }},
        { data: 'telephone', render: function(d) {
            return d ? '<a href="tel:' + escapeHtml(d) + '" style="font-weight:700; color:#1E3A5F; text-decoration:none;"><i data-lucide="phone" style="width:12px; height:12px; vertical-align:middle; display:inline-block;"></i> ' + escapeHtml(d) + '</a>' : '-';
        }},
        { data: 'code_souscription', render: function(d) {
            return d ? '<code style="font-weight:800; color:#1E3A5F; background:#F1F5F9; padding:4px 8px; border-radius:6px; font-family:monospace;"><i data-lucide="hash" style="width:12px; height:12px; vertical-align:middle; display:inline-block;"></i> ' + escapeHtml(d) + '</code>' : '-';
        }},
        { data: 'libelle_session', defaultContent: '-' },
        { data: 'montant_total', render: function(d) {
            return '<strong style="color:#059669; font-size:14px; font-weight:800;">' + formatCurrency(d) + '</strong>';
        }},
        { data: 'statut', className: 'text-center', render: function(d) {
            return renderStatut(d);
        }},
        { data: null, width: '180px', orderable: false, className: 'text-end', render: function(d) {
            const code = escapeHtml(d.code_sousscription || d.code_souscription || '');
            return '<div style="display:flex; justify-content:flex-end; gap:6px;">' +
              '<button type="button" onclick="event.stopPropagation(); openPaymentModalFor(\'' + code + '\')" class="btn btn-sm" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; border: none; border-radius: 8px; padding: 6px 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25); cursor: pointer;"><i data-lucide="wallet" style="width:13px; height:13px;"></i> Payer</button>' +
              '<a href="<?= RACINE ?>cautisation-payment/situation?code=' + code + '" onclick="event.stopPropagation();" class="btn btn-sm" style="background: #FFFFFF; border: 1px solid #CBD5E1; color: #334155; border-radius: 8px; padding: 6px 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; text-decoration: none;"><i data-lucide="eye" style="width:13px; height:13px;"></i> Situation</a>' +
              '</div>';
        }}
      ],
      language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
      drawCallback: function() { if (window.lucide) lucide.createIcons(); }
    });
    dataTable = $('#table-search-results').DataTable();

    // Clic sur la ligne pour ouvrir le modal de paiement directement sur la page
    $('#table-search-results tbody').off('click', 'tr').on('click', 'tr', function(e) {
      if ($(e.target).closest('a, button').length) return;
      const rowData = dataTable.row(this).data();
      if (rowData) {
        const code = rowData.code_sousscription || rowData.code_souscription || '';
        if (code) {
          openPaymentModalFor(code);
        }
      }
    });
  }

  searchBtn.addEventListener('click', performSearch);

  $('#selectClient').on('change', function() {
    if ($(this).val()) {
      performSearch();
    }
  });

  document.getElementById('btnRefresh').addEventListener('click', function() {
    $('#selectClient').val('').trigger('change.select2');
    if (dataTable) {
      dataTable.destroy();
      dataTable = null;
    }
    searchPlaceholder.style.display = 'block';
    searchResultsContainer.style.display = 'none';
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
