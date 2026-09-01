<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php $currentSection = 'cautisation'; ?>
<?php
$souscription = $souscription ?? [];

$nomComplet = trim($souscription['nom_client'] ?? '');
$telephone = $souscription['telephone_client'] ?? '-';
$genre = $souscription['sexe_client'] ?? '-';
$residence = $souscription['lieu_residence_client'] ?? '-';
$codeClient = $souscription['code_client'] ?? '-';
$email = $souscription['email_client'] ?? '-';
$profession = $souscription['profession_client'] ?? '-';

$montantTotal = (float)($souscription['montant_total_a_payer'] ?? 0);
$montantPaye = (float)($souscription['montant_total_paye'] ?? 0);
$montantRestant = max(0, $montantTotal - $montantPaye);

$joursTotal = (int)($souscription['nombre_jours_total'] ?? 0);
$joursPayes = (int)($souscription['nombre_jours_payes'] ?? 0);
$joursRestants = max(0, $joursTotal - $joursPayes);

$prixCotisationJournaliere = (float)($souscription['prix_cotisation_pack'] ?? 0);

$codeSouscription = $souscription['code_souscription'] ?? '';
$libelleSession = $souscription['libelle_session'] ?? '-';
$statutSouscription = $souscription['statut_souscription'] ?? '-';
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <div class="container-fluid py-4">
      <div class="row mb-4">
        <div class="col-md-12">
          <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h2 class="h4 fw-bold mb-1" style="color: #1E3A5F;">Consultation et Paiement des Cautisations</h2>
                  <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Paiement de la caution de la souscription</p>
                </div>
                <a href="<?= RACINE ?>cautisation-payment/search-form" class="btn btn-secondary" style="background: #64748B; border-color: #64748B; color: white; padding: 10px 16px; font-weight: 700; border-radius: 8px; text-decoration: none;">
                  <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Section 1: Informations du client -->
        <div class="col-md-4">
          <div class="card border-0 shadow-sm" style="border-radius: 12px; height: 100%;">
            <div class="card-header" style="background: #1E3A5F; color: white; border-radius: 12px 12px 0 0; font-weight: 700;">
              Informations du Client
            </div>
            <div class="card-body p-4">
              <div class="mb-3">
                <small style="color: #64748B; font-size: 12px; display: block; margin-bottom: 4px;">Nom complet</small>
                <strong style="color: #0F172A; font-size: 15px;"><?= htmlspecialchars($nomComplet) ?></strong>
              </div>
              <div class="mb-3">
                <small style="color: #64748B; font-size: 12px; display: block; margin-bottom: 4px;">Contact</small>
                <strong style="color: #0F172A;">
                  <a href="tel:<?= htmlspecialchars($telephone) ?>"><?= htmlspecialchars($telephone) ?></a>
                </strong>
              </div>
              <div class="mb-3">
                <small style="color: #64748B; font-size: 12px; display: block; margin-bottom: 4px;">Genre</small>
                <span style="color: #0F172A;"><?= htmlspecialchars($genre) ?></span>
              </div>
              <div class="mb-3">
                <small style="color: #64748B; font-size: 12px; display: block; margin-bottom: 4px;">Lieu de résidence</small>
                <span style="color: #0F172A;"><?= htmlspecialchars($residence) ?></span>
              </div>
              <div class="mb-3">
                <small style="color: #64748B; font-size: 12px; display: block; margin-bottom: 4px;">Code client</small>
                <code style="background: #F1F5F9; padding: 4px 8px; border-radius: 4px;"><?= htmlspecialchars($codeClient) ?></code>
              </div>
              <div class="mb-3">
                <small style="color: #64748B; font-size: 12px; display: block; margin-bottom: 4px;">Email</small>
                <span style="color: #0F172A;"><?= htmlspecialchars($email) ?></span>
              </div>
              <div class="mb-3">
                <small style="color: #64748B; font-size: 12px; display: block; margin-bottom: 4px;">Profession</small>
                <span style="color: #0F172A;"><?= htmlspecialchars($profession) ?></span>
              </div>
              <hr style="margin: 16px 0; border-color: #E2E8F0;">
              <div class="mb-3">
                <small style="color: #64748B; font-size: 12px; display: block; margin-bottom: 4px;">Session</small>
                <strong style="color: #0F172A;"><?= htmlspecialchars($libelleSession) ?></strong>
              </div>
              <div class="mb-3">
                <small style="color: #64748B; font-size: 12px; display: block; margin-bottom: 4px;">Montant total de la cautisation</small>
                <strong style="color: #15803D; font-size: 16px;"><?= number_format($montantTotal, 0, ',', ' ') ?> FCFA</strong>
              </div>
              <div class="mb-3">
                <small style="color: #64748B; font-size: 12px; display: block; margin-bottom: 4px;">Montant déjà payé</small>
                <strong style="color: #0F172A;"><?= number_format($montantPaye, 0, ',', ' ') ?> FCFA</strong>
              </div>
              <div class="mb-3">
                <small style="color: #64748B; font-size: 12px; display: block; margin-bottom: 4px;">Montant restant à payer</small>
                <strong style="color: #DC2626; font-size: 16px;"><?= number_format($montantRestant, 0, ',', ' ') ?> FCFA</strong>
              </div>
              <div class="mb-3">
                <small style="color: #64748B; font-size: 12px; display: block; margin-bottom: 4px;">Nombre total de jours</small>
                <strong style="color: #0F172A;"><?= $joursTotal ?></strong>
              </div>
              <div class="mb-3">
                <small style="color: #64748B; font-size: 12px; display: block; margin-bottom: 4px;">Nombre de jours déjà payés</small>
                <strong style="color: #0F172A;"><?= $joursPayes ?></strong>
              </div>
              <div class="mb-0">
                <small style="color: #64748B; font-size: 12px; display: block; margin-bottom: 4px;">Nombre de jours restant à payer</small>
                <strong style="color: #DC2626;"><?= $joursRestants ?></strong>
              </div>
            </div>
          </div>
        </div>

        <!-- Section 2: Historique des cautisations -->
        <div class="col-md-8">
          <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: #1E3A5F; color: white; border-radius: 12px 12px 0 0;">
              <span class="fw-bold">Liste des cautisations</span>
              <?php if ($montantRestant > 0 && $joursRestants > 0): ?>
                <button class="btn btn-sm" id="paymentBtn" style="background: #15803D; color: white; border: none; border-radius: 6px; padding: 6px 14px; font-weight: 700;" data-bs-toggle="modal" data-bs-target="#paymentModal">
                  <i data-lucide="plus" style="width: 14px; height: 14px;"></i> Faire paiement
                </button>
              <?php else: ?>
                <button class="btn btn-sm" style="background: #94A3B8; color: white; border: none; border-radius: 6px; padding: 6px 14px; font-weight: 700;" disabled>
                  <i data-lucide="check" style="width: 14px; height: 14px;"></i> Soldé
                </button>
              <?php endif; ?>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover mb-0" id="historyTable">
                  <thead style="background: #F8FAFC;">
                    <tr>
                      <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B;">Date</th>
                      <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B;">Montant</th>
                      <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B;">Jours</th>
                      <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B;">Mode</th>
                      <th style="padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748B;">Statut</th>
                    </tr>
                  </thead>
                  <tbody id="historyBody">
                    <tr>
                      <td colspan="5" class="text-center py-4 text-muted" style="font-size: 13px;">Chargement de l'historique...</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Modal de paiement -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content" style="border-radius: 12px;">
      <div class="modal-header" style="background: #1E3A5F; color: white;">
        <h5 class="modal-title" style="font-weight: 700;"><i data-lucide="wallet" style="width: 20px; height: 20px; margin-right: 8px;"></i> Paiement de Cautisation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer" style="color: white;"></button>
      </div>
      <div class="modal-body p-4">
        <!-- Partie supérieure: Récapitulatif -->
        <div class="card border-0 mb-4" style="background: #F8FAFC; border-radius: 8px;">
          <div class="card-body p-4">
            <h6 class="fw-bold mb-3" style="color: #1E3A5F;">📋 Récapitulatif</h6>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="mb-2">
                  <small style="color: #64748B; font-size: 12px;">Client</small>
                  <strong style="color: #0F172A; display: block;"><?= htmlspecialchars($nomComplet) ?></strong>
                </div>
                <div class="mb-2">
                  <small style="color: #64748B; font-size: 12px;">Code Souscription</small>
                  <strong style="color: #0F172A; display: block;"><code><?= htmlspecialchars($codeSouscription) ?></code></strong>
                </div>
                <div class="mb-2">
                  <small style="color: #64748B; font-size: 12px;">Session</small>
                  <strong style="color: #0F172A; display: block;"><?= htmlspecialchars($libelleSession) ?></strong>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-2">
                  <small style="color: #64748B; font-size: 12px;">Montant total de la cautisation</small>
                  <strong style="color: #0F172A; display: block;" id="recap_montant_total"><?= number_format($montantTotal, 0, ',', ' ') ?> FCFA</strong>
                </div>
                <div class="mb-2">
                  <small style="color: #64748B; font-size: 12px;">Montant déjà payé</small>
                  <strong style="color: #059669; display: block;" id="recap_montant_paye"><?= number_format($montantPaye, 0, ',', ' ') ?> FCFA</strong>
                </div>
                <div class="mb-2">
                  <small style="color: #64748B; font-size: 12px;">Montant restant à payer</small>
                  <strong style="color: #DC2626; display: block;" id="recap_montant_restant"><?= number_format($montantRestant, 0, ',', ' ') ?> FCFA</strong>
                </div>
              </div>
            </div>
            <hr style="margin: 16px 0; border-color: #E2E8F0;">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="mb-2">
                  <small style="color: #64748B; font-size: 12px;">Nombre total de jours</small>
                  <strong style="color: #0F172A; display: block;" id="recap_jours_total"><?= $joursTotal ?></strong>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-2">
                  <small style="color: #64748B; font-size: 12px;">Nombre de jours restant à payer</small>
                  <strong style="color: #DC2626; display: block;" id="recap_jours_restants"><?= $joursRestants ?></strong>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Partie inférieure: Formulaire de paiement -->
        <div class="card border-0">
          <div class="card-body p-0">
            <div class="row g-4">
              <div class="col-md-6">
                <div class="mb-3">
                  <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #1E3A5F; font-size: 13px;">Montant de la cotisation par jour</label>
                  <input type="number" id="dailyCotisation" class="form-control" readonly style="background: #F8FAFC; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 700; color: #0F172A;" value="<?= number_format($prixCotisationJournaliere, 0, ',', ' ') ?>" step="<?= $prixCotisationJournaliere ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #1E3A5F; font-size: 13px;">Mode de paiement</label>
                  <select class="form-select" id="paymentMode" style="border-radius: 8px; border: 1px solid #CBD5E1;">
                    <option value="especes">Espèces</option>
                    <option value="mobile_money">Mobile Money</option>
                    <option value="cheque">Chèque</option>
                    <option value="virement">Virement bancaire</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="mb-3">
              <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #1E3A5F; font-size: 13px;">Type de paiement</label>
              <div class="d-flex gap-3" style="margin-bottom: 4px;">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="type_paiement" id="typeMontant" value="montant" checked style="border-color: #1E3A5F;">
                  <label class="form-check-label" for="typeMontant" style="font-size: 13px;">Par saisie du montant</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="type_paiement" id="typeJours" value="jours" style="border-color: #1E3A5F;">
                  <label class="form-check-label" for="typeJours" style="font-size: 13px;">Par saisie du nombre de jours</label>
                </div>
              </div>
            </div>

            <div class="row g-4">
              <div class="col-md-6">
                <div class="mb-3">
                  <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #1E3A5F; font-size: 13px;">Montant à versser</label>
                  <input type="number" id="montantInput" class="form-control" style="border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 700;" value="<?= number_format($prixCotisationJournaliere, 0, ',', ' ') ?>" step="<?= $prixCotisationJournaliere ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #1E3A5F; font-size: 13px;">Nombre de jours</label>
                  <input type="number" id="joursInput" class="form-control" style="border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 700;" min="1" value="1">
                </div>
              </div>
            </div>

            <div class="mb-4">
              <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #1E3A5F; font-size: 13px;">Date du prochain rendez-vous</label>
              <input type="text" id="nextAppointment" class="form-control" readonly style="background: #F8FAFC; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 700;">
            </div>

            <div class="d-flex justify-content-end gap-2" style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #E2E8F0;">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 8px; padding: 10px 20px; font-weight: 700;">
                Annuler
              </button>
              <button type="button" class="btn" id="savePaymentBtn" style="background: #1E3A5F; color: white; border: none; border-radius: 8px; padding: 10px 24px; font-weight: 700;">
                Valider le paiement
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const PRIX_COTISATION = <?= $prixCotisationJournaliere ?>;
const MONTANT_RESTANT = <?= $montantRestant ?>;
const JOURS_RESTANTS = <?= $joursRestants ?>;
const CODE_SOUSCRIPTION = '<?= htmlspecialchars($codeSouscription) ?>';

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
    fetch('<?= RACINE ?>cautisation-payment/history', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'code_souscription=' + encodeURIComponent(CODE_SOUSCRIPTION)
    })
    .then(r => r.json())
    .then(data => {
        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted" style="font-size: 13px;">ℹ️ Aucune cautisation enregistrée</td></tr>';
            return;
        }
        tbody.innerHTML = data.data.map(c => {
            const statutClass = c.statut === 'valide' ? 'bg-success' : 'bg-secondary';
            return '<tr>' +
                '<td style="padding: 10px;">' + c.date_paiement + '</td>' +
                '<td style="padding: 10px; font-weight: 600;">' + formatCurrency(c.montant) + '</td>' +
                '<td style="padding: 10px;">' + c.nombre_jours + '</td>' +
                '<td style="padding: 10px;">' + c.mode_paiement + '</td>' +
                '<td style="padding: 10px;"><span class="badge bg-' + statutClass + '">' + c.statut + '</span></td>' +
            '</tr>';
        }).join('');
    })
    .catch(() => {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Erreur de chargement</td></tr>';
    });
}

const montantInput = document.getElementById('montantInput');
const joursInput = document.getElementById('joursInput');
const nextAppointment = document.getElementById('nextAppointment');
const typeMontantRadio = document.getElementById('typeMontant');
const typeJoursRadio = document.getElementById('typeJours');

function updateCalculations() {
    const type = document.querySelector('input[name="type_paiement"]:checked').value;
    
    if (type === 'montant') {
        const montant = parseFloat(montantInput.value) || 0;
        const jours = PRIX_COTISATION > 0 ? Math.floor(montant / PRIX_COTISATION) : 0;
        joursInput.value = jours;
    } else {
        const jours = parseInt(joursInput.value) || 1;
        const montant = jours * PRIX_COTISATION;
        montantInput.value = montant;
    }

    const jours = parseInt(joursInput.value) || 0;
    nextAppointment.value = calculateNextDate(jours);
}

montantInput.addEventListener('input', updateCalculations);
joursInput.addEventListener('input', updateCalculations);
typeMontantRadio.addEventListener('change', updateCalculations);
typeJoursRadio.addEventListener('change', updateCalculations);

document.getElementById('savePaymentBtn').addEventListener('click', function() {
    const montant = parseFloat(montantInput.value) || 0;
    const jours = parseInt(joursInput.value) || 0;
    const mode = document.getElementById('paymentMode').value;
    const type = document.querySelector('input[name="type_paiement"]:checked').value;

    if (montant <= 0) {
        alert('Le montant doit être supérieur à 0');
        return;
    }
    if (jours <= 0) {
        alert('Le nombre de jours doit être supérieur à 0');
        return;
    }

    const remainder = montant % PRIX_COTISATION;
    if (Math.abs(remainder) > 0.01) {
        alert('Le montant doit être un multiple de ' + formatCurrency(PRIX_COTISATION));
        return;
    }
    if (montant > MONTANT_RESTANT) {
        alert('Le montant dépasse le montant restant à payer (' + formatCurrency(MONTANT_RESTANT) + ')');
        return;
    }
    if (jours > JOURS_RESTANTS) {
        alert('Le nombre de jours dépasse le nombre de jours restants (' + JOURS_RESTANTS + ' jours)');
        return;
    }

    this.disabled = true;
    this.innerHTML = '<i data-lucide="loader-2" class="spinner-border spinner-border-sm"></i> Enregistrement...';

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
        this.disabled = false;
        this.innerHTML = '<i data-lucide="save" style="width: 16px; height: 16px;"></i> Valider le paiement';

        if (result.status === 1) {
            alert('✅ Paiement enregistré!\n\nCode: ' + result.code_cautisation + '\nProchain RDV: ' + result.prochain_rdv);
            location.reload();
        } else {
            alert('❌ ' + result.message);
        }
    })
    .catch(err => {
        this.disabled = false;
        this.innerHTML = '<i data-lucide="save" style="width: 16px; height: 16px;"></i> Valider le paiement';
        alert('Erreur: ' + err.message);
    });
});

// Initial load
loadHistory();
updateCalculations();
</script>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
