<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">

<?php
// Fonction utilitaire pour formater les montants
function formatCurrency($value) {
    return number_format((float)$value, 0, ',', ' ') . ' FCFA';
}

$souscription = $souscription ?? [];
$client = [
    'nom_complet' => trim(($souscription['nom_client'] ?? '') . ' ' . ($souscription['prenom_client'] ?? '')),
    'telephone' => $souscription['telephone_client'] ?? '-',
    'genre' => $souscription['sexe_client'] ?? '-',
    'residence' => $souscription['lieu_residence_client'] ?? '-',
    'code' => $souscription['code_client'] ?? '-',
    'email' => $souscription['email_client'] ?? '-',
    'profession' => $souscription['profession_client'] ?? '-'
];

$montantTotal = (float)($souscription['montant_total_a_payer'] ?? 0);
$montantPaye = (float)($souscription['montant_total_paye'] ?? 0);
$montantRestant = max(0, $montantTotal - $montantPaye);

$joursTotal = (int)($souscription['nombre_jours_total'] ?? 0);
$joursPayes = (int)($souscription['nombre_jours_payes'] ?? 0);
$joursRestants = max(0, $joursTotal - $joursPayes);

$prixCotisationJournaliere = (float)($souscription['prix_cotisation_pack'] ?? 0);
$progression = $joursTotal > 0 ? round(($joursPayes / $joursTotal) * 100) : 0;
$codeSouscription = $souscription['code_souscription'] ?? '';
?>

<!-- Page Header -->
<div class="page-header mb-4" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
  <div>
    <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">Situation de la Souscription</h1>
    <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Consultation et paiement des cautisations</p>
  </div>
  <a href="<?= RACINE ?>cautisation-payment/search-form" class="btn btn-secondary" style="background: #64748B; border-color: #64748B; color: white; padding: 10px 16px; font-weight: 700; border-radius: 8px; text-decoration: none;">
    ← Retour
  </a>
</div>

<!-- Section 1: Informations du client et résumé -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
  <!-- Card: Informations Client -->
  <div class="card" style="background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="card-header" style="background: #1E3A5F; color: #FFFFFF; padding: 16px; border-radius: 12px 12px 0 0; border-bottom: 1px solid #E2E8F0;">
      <h5 style="margin: 0; font-size: 14px; font-weight: 700;">👤 Informations du Client</h5>
    </div>
    <div class="card-body" style="padding: 20px;">
      <table style="width: 100%; border-collapse: collapse;">
        <tr style="border-bottom: 1px solid #E2E8F0;">
          <td style="padding: 12px 0; font-weight: 700; width: 40%;">Nom complet:</td>
          <td style="padding: 12px 0;"><?= htmlspecialchars($client['nom_complet']) ?></td>
        </tr>
        <tr style="border-bottom: 1px solid #E2E8F0;">
          <td style="padding: 12px 0; font-weight: 700;">Téléphone:</td>
          <td style="padding: 12px 0;"><a href="tel:<?= htmlspecialchars($client['telephone']) ?>" style="color: #1E3A5F; text-decoration: none;"><?= htmlspecialchars($client['telephone']) ?></a></td>
        </tr>
        <tr style="border-bottom: 1px solid #E2E8F0;">
          <td style="padding: 12px 0; font-weight: 700;">Genre:</td>
          <td style="padding: 12px 0;"><?= htmlspecialchars($client['genre']) ?></td>
        </tr>
        <tr style="border-bottom: 1px solid #E2E8F0;">
          <td style="padding: 12px 0; font-weight: 700;">Résidence:</td>
          <td style="padding: 12px 0;"><?= htmlspecialchars($client['residence']) ?></td>
        </tr>
        <tr style="border-bottom: 1px solid #E2E8F0;">
          <td style="padding: 12px 0; font-weight: 700;">Code client:</td>
          <td style="padding: 12px 0;"><code style="background: #F1F5F9; padding: 2px 6px; border-radius: 4px;"><?= htmlspecialchars($client['code']) ?></code></td>
        </tr>
        <tr style="border-bottom: 1px solid #E2E8F0;">
          <td style="padding: 12px 0; font-weight: 700;">Email:</td>
          <td style="padding: 12px 0;"><a href="mailto:<?= htmlspecialchars($client['email']) ?>" style="color: #1E3A5F; text-decoration: none;"><?= htmlspecialchars($client['email']) ?></a></td>
        </tr>
        <tr>
          <td style="padding: 12px 0; font-weight: 700;">Profession:</td>
          <td style="padding: 12px 0;"><?= htmlspecialchars($client['profession']) ?></td>
        </tr>
      </table>
    </div>
  </div>

  <!-- Card: Résumé de la situation -->
  <div class="card" style="background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="card-header" style="background: #1E3A5F; color: #FFFFFF; padding: 16px; border-radius: 12px 12px 0 0; border-bottom: 1px solid #E2E8F0;">
      <h5 style="margin: 0; font-size: 14px; font-weight: 700;">📊 Résumé de la Situation</h5>
    </div>
    <div class="card-body" style="padding: 20px;">
      <div style="margin-bottom: 16px;">
        <small style="color: #64748B;">Code souscription:</small><br>
        <code style="background: #F1F5F9; padding: 4px 8px; border-radius: 4px; font-weight: 700;"><?= htmlspecialchars($codeSouscription) ?></code>
      </div>
      <div style="margin-bottom: 16px;">
        <small style="color: #64748B;">Session:</small><br>
        <strong><?= htmlspecialchars($souscription['libelle_session'] ?? '-') ?></strong>
      </div>
      <hr style="border: none; border-top: 1px solid #E2E8F0; margin: 16px 0;">
      
      <div style="margin-bottom: 16px;">
        <small style="color: #64748B; display: block; margin-bottom: 8px;">Progression du paiement: <strong><?= $progression ?>%</strong></small>
        <div style="width: 100%; height: 20px; background: #E2E8F0; border-radius: 4px; overflow: hidden;">
          <div style="width: <?= $progression ?>%; height: 100%; background: linear-gradient(90deg, #16A34A, #22C55E); transition: width 0.3s ease;"></div>
        </div>
      </div>
      
      <hr style="border: none; border-top: 1px solid #E2E8F0; margin: 16px 0;">
      
      <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
        <tr>
          <td style="padding: 8px 0; color: #64748B;">Montant total:</td>
          <td style="padding: 8px 0; text-align: right;"><strong><?= formatCurrency($montantTotal) ?></strong></td>
        </tr>
        <tr style="background: #F8FAFC;">
          <td style="padding: 8px 0; color: #16A34A; font-weight: 700;">Montant payé:</td>
          <td style="padding: 8px 0; text-align: right; color: #16A34A; font-weight: 700;"><?= formatCurrency($montantPaye) ?></td>
        </tr>
        <tr>
          <td style="padding: 8px 0; color: #DC2626; font-weight: 700;">Montant restant:</td>
          <td style="padding: 8px 0; text-align: right; color: #DC2626; font-weight: 700;"><?= formatCurrency($montantRestant) ?></td>
        </tr>
        <tr style="background: #F8FAFC;">
          <td style="padding: 8px 0; color: #64748B;">Prix/jour:</td>
          <td style="padding: 8px 0; text-align: right;"><?= formatCurrency($prixCotisationJournaliere) ?></td>
        </tr>
        <tr>
          <td style="padding: 8px 0; color: #64748B;">Jours total:</td>
          <td style="padding: 8px 0; text-align: right;"><strong><?= $joursTotal ?> j.</strong></td>
        </tr>
        <tr style="background: #F8FAFC;">
          <td style="padding: 8px 0; color: #16A34A; font-weight: 700;">Jours payés:</td>
          <td style="padding: 8px 0; text-align: right; color: #16A34A; font-weight: 700;"><?= $joursPayes ?> j.</td>
        </tr>
        <tr>
          <td style="padding: 8px 0; color: #DC2626; font-weight: 700;">Jours restants:</td>
          <td style="padding: 8px 0; text-align: right; color: #DC2626; font-weight: 700;"><?= $joursRestants ?> j.</td>
        </tr>
      </table>
    </div>
  </div>
</div>

<!-- Section 2: Historique des paiements -->
<div class="card" style="background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
  <div class="card-header" style="background: #1E3A5F; color: #FFFFFF; padding: 16px; border-radius: 12px 12px 0 0; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
    <h5 style="margin: 0; font-size: 14px; font-weight: 700;">📋 Historique des Cautisations</h5>
    <button 
      class="btn btn-success"
      id="paymentBtn"
      data-code="<?= htmlspecialchars($codeSouscription) ?>"
      data-montant-total="<?= $montantTotal ?>"
      data-montant-paye="<?= $montantPaye ?>"
      data-jours-total="<?= $joursTotal ?>"
      data-jours-payes="<?= $joursPayes ?>"
      data-prix-jour="<?= $prixCotisationJournaliere ?>"
      style="background: #16A34A; border: none; color: white; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer;"
      <?= $montantRestant <= 0 ? 'disabled style="opacity: 0.5;"' : '' ?>
    >
      + Paiement
    </button>
  </div>
  <div class="card-body" style="padding: 20px;">
    <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
      <table class="table table-hover" style="width: 100%; border-collapse: collapse; font-size: 13px;">
        <thead style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
          <tr>
            <th style="padding: 12px; text-align: left;">Date de paiement</th>
            <th style="padding: 12px; text-align: right;">Montant</th>
            <th style="padding: 12px; text-align: center;">Jours</th>
            <th style="padding: 12px; text-align: left;">Mode</th>
            <th style="padding: 12px; text-align: center;">Statut</th>
          </tr>
        </thead>
        <tbody id="historyBody">
          <tr><td colspan="5" style="padding: 20px; text-align: center; color: #64748B;">Chargement...</td></tr>
        </tbody>
      </table>
    </div>
    <div id="noHistory" class="alert" style="display: none; background: #EFF6FF; border: 1px solid #BFDBFE; color: #1E40AF; padding: 16px; border-radius: 8px; margin-top: 16px;">
      <strong>ℹ️</strong> Aucune cautisation enregistrée
    </div>
  </div>
</div>

    </div>
  </main>
</div>

<!-- Modal de paiement -->
<div class="modal fade" id="paymentModal" tabindex="-1" style="display: none;">
  <div class="modal-dialog modal-lg" style="margin: 48px auto;">
    <div class="modal-content" style="background: white; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 20px 25px rgba(0,0,0,0.1);">
      <div class="modal-header" style="background: #1E3A5F; color: white; padding: 20px; border-bottom: 1px solid #E2E8F0; border-radius: 12px 12px 0 0;">
        <h5 class="modal-title" style="margin: 0; font-size: 16px; font-weight: 700;">💳 Paiement de Cautisation</h5>
        <button type="button" class="close-modal" style="background: none; border: none; color: white; font-size: 24px; cursor: pointer; padding: 0;">×</button>
      </div>
      <form id="paymentForm" style="padding: 24px;">
        <div class="form-section" style="background: #F8FAFC; padding: 16px; border-radius: 8px; margin-bottom: 24px; border: 1px solid #E2E8F0;">
          <h6 style="margin: 0 0 16px 0; font-weight: 700;">📋 Récapitulatif</h6>
          <table style="width: 100%; font-size: 13px;">
            <tr style="border-bottom: 1px solid #E2E8F0;">
              <td style="padding: 8px 0; font-weight: 700; width: 40%;">Client:</td>
              <td style="padding: 8px 0;"><?= htmlspecialchars($client['nom_complet']) ?></td>
            </tr>
            <tr style="border-bottom: 1px solid #E2E8F0;">
              <td style="padding: 8px 0; font-weight: 700;">Code souscription:</td>
              <td style="padding: 8px 0;"><code style="background: white; padding: 2px 6px; border-radius: 4px;"><?= htmlspecialchars($codeSouscription) ?></code></td>
            </tr>
            <tr style="border-bottom: 1px solid #E2E8F0;">
              <td style="padding: 8px 0; font-weight: 700;">Session:</td>
              <td style="padding: 8px 0;"><?= htmlspecialchars($souscription['libelle_session'] ?? '-') ?></td>
            </tr>
            <tr style="border-bottom: 1px solid #E2E8F0;">
              <td style="padding: 8px 0; font-weight: 700;">Montant total:</td>
              <td style="padding: 8px 0; text-align: right;"><strong><?= formatCurrency($montantTotal) ?></strong></td>
            </tr>
            <tr style="border-bottom: 1px solid #E2E8F0;">
              <td style="padding: 8px 0; color: #16A34A; font-weight: 700;">Montant déjà payé:</td>
              <td style="padding: 8px 0; text-align: right; color: #16A34A; font-weight: 700;"><?= formatCurrency($montantPaye) ?></td>
            </tr>
            <tr>
              <td style="padding: 8px 0; color: #DC2626; font-weight: 700;">Montant restant:</td>
              <td style="padding: 8px 0; text-align: right; color: #DC2626; font-weight: 700;"><?= formatCurrency($montantRestant) ?></td>
            </tr>
          </table>
        </div>

        <h6 style="margin: 0 0 16px 0; font-weight: 700;">💳 Formulaire de Paiement</h6>

        <div style="margin-bottom: 16px;">
          <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 13px;">Montant de cotisation journalière</label>
          <input type="text" id="dailyCotisation" value="<?= formatCurrency($prixCotisationJournaliere) ?>" readonly style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 6px; background: #F8FAFC; font-weight: 700;"/>
        </div>

        <div style="margin-bottom: 16px;">
          <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 13px;">Mode de paiement *</label>
          <select id="paymentMode" name="mode_paiement" required style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 6px;">
            <option value="">-- Sélectionner --</option>
            <option value="especes">Espèces</option>
            <option value="mobile_money">Mobile Money</option>
            <option value="cheque">Chèque</option>
            <option value="virement">Virement bancaire</option>
          </select>
        </div>

        <div style="margin-bottom: 16px;">
          <label style="display: block; margin-bottom: 8px; font-weight: 700; font-size: 13px;">Type de paiement *</label>
          <div style="display: flex; gap: 12px;">
            <label style="flex: 1; cursor: pointer;">
              <input type="radio" name="paymentType" id="paymentTypeAmount" value="montant" checked style="margin-right: 6px;">
              <span>Saisir le montant</span>
            </label>
            <label style="flex: 1; cursor: pointer;">
              <input type="radio" name="paymentType" id="paymentTypeDays" value="jours" style="margin-right: 6px;">
              <span>Saisir les jours</span>
            </label>
          </div>
        </div>

        <div id="amountField" style="margin-bottom: 16px;">
          <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 13px;">Montant à verser (FCFA) *</label>
          <input type="number" id="amount" name="montant" placeholder="0" step="<?= $prixCotisationJournaliere ?>" min="0" max="<?= $montantRestant ?>" required style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 6px;"/>
          <small id="amountHint" style="display: block; color: #64748B; margin-top: 4px;"></small>
        </div>

        <div id="daysField" style="display: none; margin-bottom: 16px;">
          <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 13px;">Nombre de jours *</label>
          <input type="number" id="numberOfDays" name="nombre_jours" placeholder="0" min="0" max="<?= $joursRestants ?>" style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 6px;"/>
          <small style="display: block; color: #64748B; margin-top: 4px;">Maximum: <?= $joursRestants ?> jours</small>
        </div>

        <div style="margin-bottom: 16px;">
          <label style="display: block; margin-bottom: 6px; font-weight: 700; font-size: 13px;">Date du prochain rendez-vous</label>
          <input type="text" id="nextDate" readonly style="width: 100%; padding: 10px; border: 1px solid #CBD5E1; border-radius: 6px; background: #F8FAFC;"/>
        </div>

        <div id="errorMessage" style="display: none; background: #FEE2E2; border: 1px solid #FECACA; color: #991B1B; padding: 12px; border-radius: 6px; margin-bottom: 16px;"></div>

        <div style="display: flex; gap: 12px; justify-content: flex-end;">
          <button type="button" id="closeModalBtn" style="padding: 10px 20px; border: 1px solid #E2E8F0; background: white; border-radius: 6px; cursor: pointer; font-weight: 600;">Annuler</button>
          <button type="submit" id="submitPaymentBtn" style="padding: 10px 20px; background: #16A34A; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Valider le paiement</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const PRIX_COTISATION = <?= $prixCotisationJournaliere ?>;
const MONTANT_RESTANT = <?= $montantRestant ?>;
const JOURS_RESTANTS = <?= $joursRestants ?>;
const CODE_SOUSCRIPTION = '<?= htmlspecialchars($codeSouscription) ?>';

document.addEventListener('DOMContentLoaded', async () => {
    await loadHistory();
    setupPaymentForm();
});

async function loadHistory() {
    try {
        const response = await fetch('<?= RACINE ?>cautisation-payment/history', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({ code_souscription: CODE_SOUSCRIPTION })
        });
        const result = await response.json();
        const tbody = document.getElementById('historyBody');
        tbody.innerHTML = '';
        
        if (!result.data || result.data.length === 0) {
            document.getElementById('noHistory').style.display = 'block';
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #64748B;">-</td></tr>';
            return;
        }

        result.data.forEach(item => {
            const row = tbody.insertRow();
            row.style.borderBottom = '1px solid #E2E8F0';
            row.innerHTML = `
                <td style="padding: 12px;">${item.date_paiement}</td>
                <td style="padding: 12px; text-align: right; font-weight: 600;">${formatCurrency(parseFloat(item.montant))}</td>
                <td style="padding: 12px; text-align: center;">${item.nombre_jours}</td>
                <td style="padding: 12px;">${item.mode_paiement}</td>
                <td style="padding: 12px; text-align: center;"><span style="background: ${item.statut === 'valide' ? '#DCFCE7' : '#FEF08A'}; color: ${item.statut === 'valide' ? '#166534' : '#854D0E'}; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">${item.statut}</span></td>
            `;
        });
    } catch (error) {
        console.error('Erreur:', error);
    }
}

function setupPaymentForm() {
    const modal = document.getElementById('paymentModal');
    const paymentBtn = document.getElementById('paymentBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    
    paymentBtn.addEventListener('click', () => {
        modal.style.display = 'block';
        updateAmountHint();
    });
    
    closeModalBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.style.display = 'none';
    });

    document.getElementById('paymentTypeAmount').addEventListener('change', () => {
        document.getElementById('amountField').style.display = 'block';
        document.getElementById('daysField').style.display = 'none';
        document.getElementById('amount').required = true;
        document.getElementById('numberOfDays').required = false;
        updateAmountHint();
    });

    document.getElementById('paymentTypeDays').addEventListener('change', () => {
        document.getElementById('amountField').style.display = 'none';
        document.getElementById('daysField').style.display = 'block';
        document.getElementById('amount').required = false;
        document.getElementById('numberOfDays').required = true;
    });

    document.getElementById('amount').addEventListener('input', () => {
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        if (amount > 0) {
            const days = Math.floor(amount / PRIX_COTISATION);
            document.getElementById('numberOfDays').value = days;
            updateNextDate(days);
        }
    });

    document.getElementById('numberOfDays').addEventListener('input', () => {
        const days = parseInt(document.getElementById('numberOfDays').value) || 0;
        if (days > 0) {
            document.getElementById('amount').value = days * PRIX_COTISATION;
            updateNextDate(days);
        }
    });

    document.getElementById('paymentForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const errorDiv = document.getElementById('errorMessage');
        errorDiv.style.display = 'none';

        const mode = document.getElementById('paymentMode').value;
        const type = document.querySelector('input[name="paymentType"]:checked').value;
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const days = parseInt(document.getElementById('numberOfDays').value) || 0;

        if (!mode) {
            showError('Sélectionner un mode de paiement');
            return;
        }
        if (type === 'montant' && amount <= 0) {
            showError('Montant invalide');
            return;
        }
        if (type === 'jours' && days <= 0) {
            showError('Nombre de jours invalide');
            return;
        }

        const submitBtn = document.getElementById('submitPaymentBtn');
        submitBtn.disabled = true;

        try {
            const response = await fetch('<?= RACINE ?>cautisation-payment/savepayment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    code_souscription: CODE_SOUSCRIPTION,
                    montant: amount || (days * PRIX_COTISATION),
                    nombre_jours: days || Math.floor(amount / PRIX_COTISATION),
                    mode_paiement: mode,
                    type_paiement: type
                })
            });

            const result = await response.json();
            if (!response.ok || result.error) {
                showError(result.error || 'Erreur');
                return;
            }

            alert('✅ Paiement enregistré!\n\nCode: ' + result.data.code_cautisation + '\nProchain RDV: ' + result.data.prochain_rdv);
            modal.style.display = 'none';
            setTimeout(() => location.reload(), 500);
        } catch (error) {
            showError('Erreur lors de l\'enregistrement');
        } finally {
            submitBtn.disabled = false;
        }
    });

    function updateAmountHint() {
        document.getElementById('amountHint').textContent = `Multiples de ${formatCurrency(PRIX_COTISATION)}`;
    }

    function updateNextDate(days) {
        const d = new Date();
        d.setDate(d.getDate() + days);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        document.getElementById('nextDate').value = `${day}/${month}/${year}`;
    }

    function showError(msg) {
        const errorDiv = document.getElementById('errorMessage');
        errorDiv.textContent = msg;
        errorDiv.style.display = 'block';
    }
}

function formatCurrency(value) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(value);
}
</script>

    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
