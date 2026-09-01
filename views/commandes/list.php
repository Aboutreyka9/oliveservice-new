<?php
require_once __DIR__ . '/../../public/inc/header.php';
$clients   = isset($clients) ? $clients : [];
$pressings = isset($pressings) ? $pressings : [];
$articles  = isset($articles) ? $articles : [];
$services  = isset($services) ? $services : [];
$tarifs    = isset($tarifs) ? $tarifs : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header" style="margin-bottom: 24px;">
        <div>
          <h1 style="margin: 0; font-size: 22px; font-weight: 800; color: #1E293B;">Gestion des Commandes</h1>
          <p class="page-subtitle" style="margin: 4px 0 0; color: #64748B; font-size: 13px;">
            Suivi des commandes détaillées et des collectes de linge au sac
          </p>
        </div>
        <?php if (empty($isLivreur)): ?>
        <button class="btn-primary" onclick="openNewOrderModal()" style="display: inline-flex; align-items: center; gap: 8px;">
          <i data-lucide="plus" style="width: 18px; height: 18px;"></i> Nouvelle commande
        </button>
        <?php endif; ?>
      </div>

      <div class="card" style="padding: 20px; border-radius: 14px;">
         <div class="mobile-list-container"></div>
         <div class="table-responsive-mobile">
            <table class="table" id="dataTable" style="width: 100%;">
               <thead>
                <tr>
                  <th>N°</th>
                  <th>Code</th>
                  <th>Type</th>
                  <th>Client</th>
                  <th>Pressing</th>
                  <th>Étape de suivi</th>
                  <th style="text-align: right;">Montant</th>
                  <th>Date</th>
                  <th style="text-align: center;">Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
           </table>
         </div>
      </div>

      <!-- ==========================================
           MODAL CRÉATION DE COMMANDE (COLIS OU DÉTAILLÉE)
           ========================================== -->
      <?php if (empty($isLivreur)): ?>
      <div id="modal-nouvelle-commande" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: #FFF; border-radius: 16px; width: 92%; max-width: 680px; max-height: 90vh; overflow-y: auto; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
          
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
              <div style="width: 38px; height: 38px; border-radius: 10px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="shopping-bag" style="width: 20px; height: 20px;"></i>
              </div>
              <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #1E293B;">Nouvelle Commande</h3>
            </div>
            <button type="button" onclick="closeNewOrderModal()" style="background: none; border: none; font-size: 24px; color: #94A3B8; cursor: pointer;">&times;</button>
          </div>

          <form id="form-nouvelle-commande" onsubmit="submitNewOrder(event)">
            <?= Validator::csrfField() ?>
            <input type="hidden" name="items_json" id="items_json" value="[]">

            <!-- 1. SÉLECTEUR DE TYPE DE COMMANDE -->
            <div style="margin-bottom: 20px;">
              <label style="display: block; font-size: 13px; font-weight: 600; color: #1E293B; margin-bottom: 8px;">Type de commande</label>
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <!-- Option 1 : Collecte au Sac / Colis -->
                <label id="label-type-colis" style="border: 2px solid #2563EB; background: #F8FAFC; border-radius: 10px; padding: 14px; cursor: pointer; display: flex; align-items: flex-start; gap: 10px; transition: all 0.15s ease;">
                  <input type="radio" name="type_commande" value="colis" checked onchange="handleTypeCommandeChange('colis')" style="margin-top: 3px;">
                  <div>
                    <strong style="display: block; font-size: 14px; font-weight: 700; color: #1E293B;">Collecte au Sac</strong>
                    <span style="color: #64748B; font-size: 12px; line-height: 1.4; display: block; margin-top: 3px;">
                      Sac sans détail. Le devis est fixé par le pressing après pesée/inventaire.
                    </span>
                  </div>
                </label>

                <!-- Option 2 : Commande Détaillée -->
                <label id="label-type-detaillee" style="border: 1px solid #E2E8F0; background: #FFFFFF; border-radius: 10px; padding: 14px; cursor: pointer; display: flex; align-items: flex-start; gap: 10px; transition: all 0.15s ease;">
                  <input type="radio" name="type_commande" value="detaillee" onchange="handleTypeCommandeChange('detaillee')" style="margin-top: 3px;">
                  <div>
                    <strong style="display: block; font-size: 14px; font-weight: 700; color: #1E293B;">Commande Détaillée</strong>
                    <span style="color: #64748B; font-size: 12px; line-height: 1.4; display: block; margin-top: 3px;">
                      Articles et services choisis avec calcul automatique du total.
                    </span>
                  </div>
                </label>
              </div>
            </div>

            <!-- BLOC 1 : COLLECTE AU SAC -->
            <div id="bloc-colis-options" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px; margin-bottom: 20px;">
              <div class="form-group" style="margin: 0;">
                <label style="font-size: 13px; font-weight: 600; color: #1E293B; margin-bottom: 6px; display: block;">
                  Nombre de sacs de linge confiés
                </label>
                <div style="display: flex; align-items: center; gap: 10px;">
                  <input type="number" name="nb_sacs_colis" id="nb_sacs_colis" value="1" min="1" max="20" class="form-control" style="width: 90px; font-size: 15px; font-weight: 700; text-align: center;">
                  <span style="font-size: 13px; color: #475569;">sac(s) de linge</span>
                </div>
                <small style="color: #64748B; font-size: 12px; display: block; margin-top: 10px; line-height: 1.4;">
                  Le montant initial sera à 0 FCFA. Dès réception du linge, le pressing saisira le montant réel via le bouton d'inventaire.
                </small>
              </div>
            </div>

            <!-- BLOC 2 : COMMANDE DÉTAILLÉE (CHOIX D'ARTICLES ET SERVICES) -->
            <div id="bloc-detaillee-options" style="display: none; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
              <h4 style="margin: 0 0 12px; font-size: 14px; font-weight: 700; color: #1E293B; display: flex; align-items: center; gap: 6px;">
                <i data-lucide="plus-circle" style="width: 16px; height: 16px; color: #2563EB;"></i> Ajouter des vêtements au panier
              </h4>

              <div style="display: grid; grid-template-columns: 2fr 1.5fr 1fr 1.2fr auto; gap: 8px; align-items: flex-end; margin-bottom: 12px;">
                <div>
                  <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Vêtement / Article</label>
                  <select id="line_article_select" class="form-control" onchange="autoFillLinePrice()" style="width: 100%; padding: 8px 10px; font-size: 13px;">
                    <option value="">-- Choisir un vêtement / article --</option>
                    <?php foreach ($articles as $art): ?>
                      <option value="<?= htmlspecialchars($art['code_article']) ?>" data-label="<?= htmlspecialchars($art['libelle_article']) ?>">
                        <?= htmlspecialchars($art['libelle_article']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div>
                  <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Service</label>
                  <select id="line_service_select" class="form-control" onchange="autoFillLinePrice()" style="width: 100%; padding: 8px 10px; font-size: 13px;">
                    <option value="">-- Service --</option>
                    <?php foreach ($services as $srv): ?>
                      <option value="<?= htmlspecialchars($srv['code_service']) ?>" data-label="<?= htmlspecialchars($srv['libelle_service']) ?>">
                        <?= htmlspecialchars($srv['libelle_service']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div>
                  <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Qté</label>
                  <input type="number" id="line_qty" value="1" min="1" class="form-control" style="width: 100%; padding: 8px 6px; font-size: 13px; text-align: center;">
                </div>

                <div>
                  <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Prix Unit. (FCFA)</label>
                  <input type="number" id="line_price" value="" min="0" step="50" class="form-control" style="width: 100%; padding: 8px 6px; font-size: 13px;" placeholder="Prix FCFA">
                </div>

                <div>
                  <button type="button" class="btn btn-primary" onclick="addLineItem()" style="padding: 8px 12px; height: 38px; display: inline-flex; align-items: center; gap: 4px; background: #1E3A5F; border-color: #1E3A5F;">
                    <i data-lucide="plus" style="width: 16px; height: 16px;"></i> Ajouter
                  </button>
                </div>
              </div>

              <!-- TABLEAU DES ARTICLES AJOUTÉS -->
              <div style="background: #FFF; border: 1px solid #E2E8F0; border-radius: 8px; overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse; font-size: 12px;" id="table-selected-items">
                  <thead>
                    <tr style="background: #F1F5F9; text-align: left; color: #475569;">
                      <th style="padding: 8px 12px;">Vêtement / Article</th>
                      <th style="padding: 8px 12px;">Service</th>
                      <th style="padding: 8px 12px; text-align: center;">Qté</th>
                      <th style="padding: 8px 12px; text-align: right;">Prix Unit.</th>
                      <th style="padding: 8px 12px; text-align: right;">Total</th>
                      <th style="padding: 8px 12px; text-align: center; width: 40px;"></th>
                    </tr>
                  </thead>
                  <tbody id="tbody-selected-items">
                    <tr id="row-empty-items">
                      <td colspan="6" style="padding: 16px; text-align: center; color: #94A3B8;">
                        Aucun vêtement sélectionné. Choisissez un vêtement ci-dessus pour composer la commande.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- 2. CLIENT -->
            <div style="margin-bottom: 16px;">
              <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Client</label>
              <select name="client_code" id="order_client_code" required class="form-control" onchange="fillClientAddress(this)" style="width: 100%; padding: 10px 12px; font-size: 14px;">
                <option value="">-- Choisir un client --</option>
                <?php foreach ($clients as $cl): ?>
                  <option value="<?= htmlspecialchars($cl['code_client']) ?>" data-adresse="<?= htmlspecialchars($cl['adresse_client'] ?? ($cl['quartier_client'] ?? '')) ?>" data-tel="<?= htmlspecialchars($cl['telephone_client'] ?? '') ?>">
                    <?= htmlspecialchars($cl['nom_client']) ?> (<?= htmlspecialchars($cl['telephone_client'] ?? '-') ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- 3. PRESSING (Session automatique) -->
            <?php
              $resolvedOrderPressingCode = $currentPressingCode ?: ($pressings[0]['code_pressing'] ?? 'PRS-001');
            ?>
            <input type="hidden" name="pressing_code" id="order_pressing_code" value="<?= htmlspecialchars($resolvedOrderPressingCode) ?>">

            <!-- 4. ADRESSE DE COLLECTE / LIVRAISON -->
            <div style="margin-bottom: 16px;">
              <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Adresse de collecte / livraison</label>
              <input type="text" name="adresse_livraison_commande" id="order_adresse" placeholder="ex: Cocody Angré 8ème Tranche, Villa 12" class="form-control" style="width: 100%; padding: 10px 12px; font-size: 14px;">
            </div>

            <!-- 5. FRAIS & MONTANT TOTAL CALCULÉ -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1.2fr; gap: 10px; margin-bottom: 16px; background: #F8FAFC; padding: 12px; border-radius: 10px; border: 1px solid #E2E8F0;">
              <div>
                <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Frais Collecte</label>
                <input type="number" name="frais_collecte_commande" id="frais_collecte" value="0" min="0" step="100" oninput="recalcGrandTotal()" class="form-control" style="width: 100%; padding: 6px 8px; font-size: 13px;">
              </div>
              <div>
                <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Frais Livraison</label>
                <input type="number" name="frais_livraison_commande" id="frais_livraison" value="0" min="0" step="100" oninput="recalcGrandTotal()" class="form-control" style="width: 100%; padding: 6px 8px; font-size: 13px;">
              </div>
              <div>
                <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Remise</label>
                <input type="number" name="remise_commande" id="remise_commande" value="0" min="0" step="100" oninput="recalcGrandTotal()" class="form-control" style="width: 100%; padding: 6px 8px; font-size: 13px;">
              </div>
              <div>
                <label style="display: block; font-size: 11px; font-weight: 700; color: #0F766E; margin-bottom: 4px;">Total Commande</label>
                <input type="number" name="montant_total_commande" id="montant_total_commande" value="0" min="0" step="100" class="form-control" style="width: 100%; padding: 6px 8px; font-size: 15px; font-weight: 800; color: #059669; background: #ECFDF5;">
              </div>
            </div>

            <!-- 6. OBSERVATIONS -->
            <div style="margin-bottom: 20px;">
              <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Instructions / Observations</label>
              <textarea name="observation_commande" rows="2" placeholder="ex: Attention au linge délicat, appeler à l'arrivée..." class="form-control" style="width: 100%; padding: 8px 10px; font-size: 13px;"></textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid #E2E8F0; padding-top: 16px;">
              <button type="button" class="btn btn-secondary" onclick="closeNewOrderModal()">Annuler</button>
              <button type="submit" class="btn btn-primary btnSubmitNewOrder" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i> Créer la commande
              </button>
            </div>
          </form>
        </div>
      </div>
      <?php endif; ?>

      <script src="<?= RACINE ?>public/json/mobile-list.js"></script>
      <script src="<?= RACINE ?>public/json/entities/commandes.js?v=6"></script>

      <script>
      const tarifsCatalogue = <?= json_encode($tarifs, JSON_UNESCAPED_UNICODE) ?>;
      let selectedItemsList = [];

      function openNewOrderModal() {
        const modal = document.getElementById('modal-nouvelle-commande');
        if (modal) {
          modal.style.display = 'flex';
          if (typeof lucide !== 'undefined') lucide.createIcons();
        }
      }

      function closeNewOrderModal() {
        const modal = document.getElementById('modal-nouvelle-commande');
        if (modal) modal.style.display = 'none';
        if (window.history && window.history.replaceState) {
          window.history.replaceState({}, document.title, window.location.pathname);
        }
      }

      function handleTypeCommandeChange(type) {
        const blocColis = document.getElementById('bloc-colis-options');
        const blocDetaillee = document.getElementById('bloc-detaillee-options');
        const labelColis = document.getElementById('label-type-colis');
        const labelDetaillee = document.getElementById('label-type-detaillee');

        if (type === 'colis') {
          blocColis.style.display = 'block';
          blocDetaillee.style.display = 'none';
          labelColis.style.border = '2px solid #2563EB';
          labelColis.style.background = '#F8FAFC';
          labelDetaillee.style.border = '1px solid #E2E8F0';
          labelDetaillee.style.background = '#FFFFFF';
          document.getElementById('montant_total_commande').value = 0;
        } else {
          blocColis.style.display = 'none';
          blocDetaillee.style.display = 'block';
          labelDetaillee.style.border = '2px solid #2563EB';
          labelDetaillee.style.background = '#F8FAFC';
          labelColis.style.border = '1px solid #E2E8F0';
          labelColis.style.background = '#FFFFFF';
          recalcGrandTotal();
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
      }

      function autoFillLinePrice() {
        const artCode = $('#line_article_select').val() || document.getElementById('line_article_select')?.value;
        const srvCode = $('#line_service_select').val() || document.getElementById('line_service_select')?.value;
        const pressingCode = $('#order_pressing_code').val() || document.getElementById('order_pressing_code')?.value || '';

        if (!artCode || !srvCode) return;

        let found = null;
        if (Array.isArray(tarifsCatalogue) && tarifsCatalogue.length > 0) {
          if (pressingCode) {
            found = tarifsCatalogue.find(t => t.article_code === artCode && t.service_code === srvCode && t.pressing_code === pressingCode);
          }
          if (!found) {
            found = tarifsCatalogue.find(t => t.article_code === artCode && t.service_code === srvCode);
          }
        }

        if (found && found.prix_tarif !== undefined && found.prix_tarif !== null) {
          const val = Math.round(parseFloat(found.prix_tarif));
          $('#line_price').val(val);
          const priceInput = document.getElementById('line_price');
          if (priceInput) priceInput.value = val;
        }
      }

      function addLineItem() {
        const selArt = document.getElementById('line_article_select');
        const selSrv = document.getElementById('line_service_select');
        const artCode = selArt.value;
        const srvCode = selSrv.value;
        const artLabel = selArt.options[selArt.selectedIndex]?.getAttribute('data-label') || artCode;
        const srvLabel = selSrv.options[selSrv.selectedIndex]?.getAttribute('data-label') || srvCode;
        const qty = parseInt(document.getElementById('line_qty').value) || 1;
        const price = parseFloat(document.getElementById('line_price').value) || 0;

        if (!artCode) {
          if (typeof showToast === 'function') showToast('Veuillez choisir un article', 'warning');
          return;
        }
        if (!srvCode) {
          if (typeof showToast === 'function') showToast('Veuillez choisir un service', 'warning');
          return;
        }

        const existingIdx = selectedItemsList.findIndex(i => i.article_code === artCode && i.service_code === srvCode);
        if (existingIdx >= 0) {
          selectedItemsList[existingIdx].quantite += qty;
          selectedItemsList[existingIdx].prix_unitaire = price;
          selectedItemsList[existingIdx].sous_total = selectedItemsList[existingIdx].quantite * price;
        } else {
          selectedItemsList.push({
            article_code: artCode,
            article_label: artLabel,
            service_code: srvCode,
            service_label: srvLabel,
            quantite: qty,
            prix_unitaire: price,
            sous_total: qty * price
          });
        }

        renderSelectedItems();
      }

      function removeLineItem(idx) {
        selectedItemsList.splice(idx, 1);
        renderSelectedItems();
      }

      function renderSelectedItems() {
        const tbody = document.getElementById('tbody-selected-items');
        tbody.innerHTML = '';

        if (selectedItemsList.length === 0) {
          tbody.innerHTML = `
            <tr id="row-empty-items">
              <td colspan="6" style="padding: 16px; text-align: center; color: #94A3B8;">
                Aucun article sélectionné. Choisissez un article ci-dessus pour composer la commande.
              </td>
            </tr>
          `;
        } else {
          selectedItemsList.forEach((item, idx) => {
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #F1F5F9';
            tr.innerHTML = `
              <td style="padding: 8px 12px;"><strong>${item.article_label}</strong></td>
              <td style="padding: 8px 12px;"><span style="background: #F1F5F9; color: #1E293B; padding: 2px 6px; border-radius: 4px; font-size: 11px;">${item.service_label}</span></td>
              <td style="padding: 8px 12px; text-align: center; font-weight: 700;">${item.quantite}</td>
              <td style="padding: 8px 12px; text-align: right;">${new Intl.NumberFormat('fr-FR').format(item.prix_unitaire)} FCFA</td>
              <td style="padding: 8px 12px; text-align: right; font-weight: 700; color: #059669;">${new Intl.NumberFormat('fr-FR').format(item.sous_total)} FCFA</td>
              <td style="padding: 8px 12px; text-align: center;">
                <button type="button" onclick="removeLineItem(${idx})" style="background: none; border: none; color: #DC2626; cursor: pointer; font-size: 14px;" title="Supprimer">
                  &times;
                </button>
              </td>
            `;
            tbody.appendChild(tr);
          });
        }

        document.getElementById('items_json').value = JSON.stringify(selectedItemsList);
        recalcGrandTotal();
      }

      function recalcGrandTotal() {
        const type = document.querySelector('input[name="type_commande"]:checked')?.value || 'colis';
        const fraisCol = parseFloat(document.getElementById('frais_collecte').value) || 0;
        const fraisLiv = parseFloat(document.getElementById('frais_livraison').value) || 0;
        const remise   = parseFloat(document.getElementById('remise_commande').value) || 0;

        if (type === 'detaillee') {
          const itemsSum = selectedItemsList.reduce((acc, curr) => acc + curr.sous_total, 0);
          const grandTotal = Math.max(0, itemsSum + fraisCol + fraisLiv - remise);
          document.getElementById('montant_total_commande').value = grandTotal;
        } else {
          // Colis
          const colSum = Math.max(0, fraisCol + fraisLiv - remise);
          document.getElementById('montant_total_commande').value = colSum;
        }
      }

      function fillClientAddress(selectEl) {
        if (!selectEl) return;
        const opt = selectEl.options ? selectEl.options[selectEl.selectedIndex] : null;
        if (opt) {
          const adr = opt.getAttribute('data-adresse') || '';
          const inputAdr = document.getElementById('order_adresse');
          if (inputAdr && adr) {
            inputAdr.value = adr;
          }
        }
      }

      function onOrderClientChange(el) {
        fillClientAddress(el);
      }

      function submitNewOrder(e) {
        e.preventDefault();
        const form = $(e.target);
        const btn = form.find('.btnSubmitNewOrder');
        const baseApi = (typeof LINK !== 'undefined') ? LINK : ((typeof RACINE !== 'undefined') ? RACINE : '/admin-lavex/');

        const type = document.querySelector('input[name="type_commande"]:checked')?.value || 'colis';
        if (type === 'detaillee' && selectedItemsList.length === 0) {
          if (typeof showToast === 'function') showToast('Veuillez ajouter au moins un article dans la commande détaillée', 'warning');
          return;
        }

        if (typeof loading === 'function') {
          loading(btn, true, '<i class="fa fa-spinner fa-spin"></i> Création...');
        }

        $.ajax({
          url: baseApi + 'commande/add',
          type: 'POST',
          data: form.serialize(),
          dataType: 'json',
          success: function(rep) {
            if (typeof loading === 'function') {
              loading(btn, false, '<i data-lucide="check-circle"></i> Créer la commande');
            }
            if (rep.status) {
              if (typeof showToast === 'function') showToast(rep.message || 'Commande créée avec succès !', 'success');
              closeNewOrderModal();
              selectedItemsList = [];
              renderSelectedItems();
              if ($.fn.DataTable && $.fn.DataTable.isDataTable('#dataTable')) {
                $('#dataTable').DataTable().ajax.reload();
              } else {
                setTimeout(() => window.location.reload(), 700);
              }
            } else {
              if (typeof showToast === 'function') showToast(rep.message || 'Erreur lors de la création', 'error');
            }
          },
          error: function(xhr) {
            if (typeof loading === 'function') {
              loading(btn, false, '<i data-lucide="check-circle"></i> Créer la commande');
            }
            let msg = 'Erreur lors de la création de la commande';
            if (xhr.responseJSON && xhr.responseJSON.message) {
              msg = xhr.responseJSON.message;
            }
            if (typeof showToast === 'function') showToast(msg, 'error');
          }
        });
      }

      $(document).ready(function() {
        if ($.fn.select2) {
          $('#order_client_code').select2({
            dropdownParent: $('#modal-nouvelle-commande'),
            placeholder: '-- Choisir un client --',
            width: '100%'
          }).on('change', function() {
            onOrderClientChange(this);
          });

          $('#line_article_select').select2({
            dropdownParent: $('#modal-nouvelle-commande'),
            placeholder: '-- Choisir un article --',
            width: '100%'
          }).on('change', function() {
            autoFillLinePrice();
          });

          $('#line_service_select').select2({
            dropdownParent: $('#modal-nouvelle-commande'),
            placeholder: '-- Choisir un service --',
            width: '100%'
          }).on('change', function() {
            autoFillLinePrice();
          });
        }

        // Détection de l'ouverture automatique depuis la liste client
        const urlParams = new URLSearchParams(window.location.search);
        const preselectedClient = urlParams.get('client');
        if (preselectedClient) {
          const sel = $('#order_client_code');
          if (sel.length) {
            sel.val(preselectedClient).trigger('change');
          }
          openNewOrderModal();
          if (window.history && window.history.replaceState) {
            window.history.replaceState({}, document.title, window.location.pathname);
          }
        }
      });
      </script>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
