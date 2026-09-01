<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin: 0;">
            <i data-lucide="search" style="width: 24px; height: 24px; display: inline; margin-right: 8px;"></i>
            Recherche de Souscription pour Paiement
          </h1>
        </div>
      </div>

      <div class="card" style="background: #FFFFFF; border-radius: 12px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
        <div class="card-header" style="background: #1E3A5F; color: #FFFFFF; padding: 16px; border-bottom: 1px solid #E2E8F0;">
          <h5 style="margin: 0; font-size: 16px; font-weight: 700;">Critères de Recherche</h5>
        </div>
        
        <div class="card-body" style="padding: 24px;">
          <form id="searchForm" class="needs-validation">
            <div class="row mb-3">
              <div class="col-md-8">
                <label class="form-label fw-bold">Critère de recherche *</label>
                <input 
                  type="text" 
                  class="form-control form-control-lg" 
                  id="criteria" 
                  name="criteria" 
                  placeholder="Téléphone, nom client, code client ou code souscription"
                  required
                  style="border: 1px solid #CBD5E1; border-radius: 8px; padding: 10px 14px;"
                />
                <small class="form-text text-muted d-block mt-2">
                  Entrez le numéro de téléphone, nom, code client ou code souscription
                </small>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-bold">Type de recherche</label>
                <select class="form-select form-select-lg" id="searchType" name="type" style="border: 1px solid #CBD5E1; border-radius: 8px; padding: 10px 14px;">
                  <option value="all">Tous les critères</option>
                  <option value="phone">Numéro de téléphone</option>
                  <option value="name">Nom du client</option>
                  <option value="code">Code client</option>
                  <option value="subscription">Code souscription</option>
                </select>
              </div>
            </div>

            <div class="row mt-4">
              <div class="col-12">
                <button type="submit" class="btn btn-primary" id="searchBtn" style="background: #1E3A5F; border-color: #1E3A5F; padding: 10px 24px; font-weight: 700; border-radius: 8px;">
                  <i data-lucide="search" style="width: 18px; height: 18px; display: inline; margin-right: 6px;"></i> Rechercher
                </button>
                <button type="reset" class="btn btn-secondary" style="background: #64748B; border-color: #64748B; padding: 10px 24px; font-weight: 700; border-radius: 8px; margin-left: 8px;">
                  <i data-lucide="rotate-cw" style="width: 18px; height: 18px; display: inline; margin-right: 6px;"></i> Réinitialiser
                </button>
              </div>
            </div>
          </form>

          <hr class="my-4" />

          <!-- Tableau des résultats -->
          <div id="resultsContainer" style="display: none;">
            <h6 class="mb-3 fw-bold">
              <i data-lucide="list" style="width: 18px; height: 18px; display: inline; margin-right: 6px;"></i>
              Résultats de recherche
              <span id="resultCount" class="badge bg-info" style="margin-left: 8px;"></span>
            </h6>
            <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
              <table class="table table-hover table-striped" id="resultsTable" style="width: 100%; border-collapse: collapse;">
                <thead style="background: #F8FAFC;">
                  <tr>
                    <th style="padding: 12px;">Nom du client</th>
                    <th style="padding: 12px;">Téléphone</th>
                    <th style="padding: 12px;">Code souscription</th>
                    <th style="padding: 12px;">Session</th>
                    <th style="padding: 12px; text-align: right;">Montant total</th>
                    <th style="padding: 12px; text-align: center;">Actions</th>
                  </tr>
                </thead>
                <tbody id="resultsBody" style="font-size: 13px;">
                  <!-- Résultats chargés en AJAX -->
                </tbody>
              </table>
            </div>
            <div id="noResults" class="alert alert-info" style="display: none; margin-top: 16px;">
              <i data-lucide="info" style="width: 18px; height: 18px; display: inline; margin-right: 8px;"></i> 
              Aucune souscription trouvée
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
document.getElementById('searchForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const criteria = document.getElementById('criteria').value.trim();
    const type = document.getElementById('searchType').value;
    
    if (!criteria) {
        alert('Veuillez entrer un critère de recherche');
        return;
    }

    const searchBtn = document.getElementById('searchBtn');
    searchBtn.disabled = true;
    searchBtn.innerHTML = '<i data-lucide="loader" style="width: 16px; height: 16px; display: inline; margin-right: 6px;"></i> Recherche en cours...';

    try {
        const response = await fetch('<?= RACINE ?>cautisation-payment/search', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                criteria: criteria,
                type: type
            })
        });

        const result = await response.json();

        if (!response.ok || result.error) {
            showResults([], true);
            alert(result.error || 'Erreur lors de la recherche');
            return;
        }

        if (result.message && response.status === 404) {
            showResults([], true);
            return;
        }

        showResults(result.data || []);
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la recherche');
    } finally {
        searchBtn.disabled = false;
        searchBtn.innerHTML = '<i data-lucide="search" style="width: 16px; height: 16px; display: inline; margin-right: 6px;"></i> Rechercher';
    }
});

function showResults(data, isEmpty = false) {
    const container = document.getElementById('resultsContainer');
    const tbody = document.getElementById('resultsBody');
    const noResults = document.getElementById('noResults');
    const resultCount = document.getElementById('resultCount');

    tbody.innerHTML = '';

    if (isEmpty || data.length === 0) {
        noResults.style.display = 'block';
        container.style.display = 'block';
        resultCount.textContent = '';
        return;
    }

    resultCount.textContent = data.length;
    noResults.style.display = 'none';

    data.forEach(souscription => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td style="padding: 12px;">
                <strong>${escapeHtml(souscription.nom_complet || '-')}</strong>
            </td>
            <td style="padding: 12px;">
                <a href="tel:${escapeHtml(souscription.telephone)}">${escapeHtml(souscription.telephone)}</a>
            </td>
            <td style="padding: 12px;">
                <code style="background: #F1F5F9; padding: 2px 6px; border-radius: 4px;">${escapeHtml(souscription.code_souscription)}</code>
            </td>
            <td style="padding: 12px;">${escapeHtml(souscription.libelle_session || '-')}</td>
            <td style="padding: 12px; text-align: right;">
                <strong>${formatCurrency(souscription.montant_total)}</strong>
            </td>
            <td style="padding: 12px; text-align: center;">
                <a 
                    href="<?= RACINE ?>cautisation-payment/situation?code=${escapeHtml(souscription.code_souscription)}"
                    class="btn btn-sm"
                    style="background: #16A34A; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block;"
                >
                    <i data-lucide="arrow-right" style="width: 14px; height: 14px; display: inline; margin-right: 4px;"></i> Détails
                </a>
            </td>
        `;
        tbody.appendChild(row);
    });

    container.style.display = 'block';
}

function formatCurrency(value) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF'
    }).format(value);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<style>
#resultsContainer {
    animation: slideIn 0.3s ease-in;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.btn {
    border: none;
    border-radius: 6px;
    padding: 8px 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.form-control, .form-select {
    border: 1px solid #CBD5E1 !important;
    border-radius: 8px !important;
}

.form-control:focus, .form-select:focus {
    border-color: #1E3A5F !important;
    box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1) !important;
}
</style>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>

