<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">

      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3);">
            <i data-lucide="refresh-cw" style="width: 26px; height: 26px; color: #FFFFFF;"></i>
          </div>
          <div>
            <div style="display: flex; align-items: center; gap: 8px;">
              <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
                Ressouscription Client
              </h1>
              <span style="background: #E0F2FE; color: #0369A1; font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;">
                Client Existant
              </span>
            </div>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Souscription à une nouvelle session d'activité pour un client déjà enregistré dans le système
            </p>
          </div>
        </div>

        <a href="<?= RACINE ?>souscription/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;">
          <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Liste des souscriptions
        </a>
      </div>

      <!-- MESSAGES FLASH / NOTIFICATIONS -->
      <div id="form-messages" style="display: none; margin-bottom: 20px; padding: 14px 18px; border-radius: 10px; font-weight: 700; font-size: 14px;"></div>

      <form id="form-ressouscription" action="<?= RACINE ?>souscription/processRessouscription" method="POST" style="width: 100%;">
        <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
        <input type="hidden" name="client_code" id="client_code" value="<?= htmlspecialchars($preselectedClient['code_client'] ?? '') ?>">
        <input type="hidden" name="zone_code" id="zone_code" value="<?= Context::zone() ?>">
        <input type="hidden" name="packs" id="hidden-packs" value="[]">

        <!-- SECTION 1 : RECHERCHE & SÉLECTION DU CLIENT -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03); margin-bottom: 24px;">
          <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
            <i data-lucide="user-search" style="width: 18px; height: 18px; color: #059669;"></i> Étape 1 : Recherche & Sélection du Client
          </h3>

          <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
              Rechercher un Client Existant <span style="color: #EF4444;">*</span>
            </label>
            <select id="select-client-search" class="form-control select2-client-ajax" style="width: 100%;">
              <?php if (!empty($preselectedClient)): ?>
                <option value="<?= htmlspecialchars($preselectedClient['code_client']) ?>" selected>
                  <?= htmlspecialchars($preselectedClient['code_client'] . ' - ' . $preselectedClient['nom_client'] . ' (' . $preselectedClient['telephone_client'] . ')') ?>
                </option>
              <?php endif; ?>
            </select>
            <small style="color: #64748B; font-size: 12px; margin-top: 4px; display: block;">
              Saisissez au moins 2 caractères (Nom, N° Téléphone, Code Client CLI-..., ou N° CNI)
            </small>
          </div>

          <!-- CARTE DES INFORMATIONS CLIENTS (LECTURE SEULE) -->
          <div id="client-info-card" style="display: <?= !empty($preselectedClient) ? 'block' : 'none' ?>; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
              <span style="font-size: 12px; font-weight: 800; color: #059669; background: #ECFDF5; padding: 4px 12px; border-radius: 20px; border: 1px solid #A7F3D0; text-transform: uppercase;">
                <i data-lucide="check-circle" style="width: 12px; height: 12px; display: inline; vertical-align: -1px;"></i> Client Sélectionné
              </span>
              <span id="display-client-code" style="font-family: monospace; font-size: 13px; font-weight: 800; color: #1E3A5F; background: #E2E8F0; padding: 3px 8px; border-radius: 6px;">
                <?= htmlspecialchars($preselectedClient['code_client'] ?? '-') ?>
              </span>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
              <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block; margin-bottom: 2px;">Nom Complet</span>
                <span id="display-client-nom" style="font-size: 14px; font-weight: 800; color: #0F172A;">
                  <?= htmlspecialchars($preselectedClient['nom_client'] ?? '-') ?>
                </span>
              </div>

              <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block; margin-bottom: 2px;">Téléphone Principal</span>
                <span id="display-client-tel" style="font-size: 14px; font-weight: 800; color: #0F172A;">
                  <?= htmlspecialchars($preselectedClient['telephone_client'] ?? '-') ?>
                </span>
              </div>

              <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block; margin-bottom: 2px;">Numéro CNI</span>
                <span id="display-client-cni" style="font-size: 14px; font-weight: 800; color: #0F172A;">
                  <?= htmlspecialchars(!empty($preselectedClient['numero_cni']) ? $preselectedClient['numero_cni'] : 'Non renseigné') ?>
                </span>
              </div>

              <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block; margin-bottom: 2px;">Résidence / Quartier</span>
                <span id="display-client-lieu" style="font-size: 14px; font-weight: 800; color: #0F172A;">
                  <?= htmlspecialchars($preselectedClient['lieu_residence_client'] ?? '-') ?>
                </span>
              </div>

              <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block; margin-bottom: 2px;">Genre</span>
                <span id="display-client-sexe" style="font-size: 14px; font-weight: 800; color: #0F172A;">
                  <?php 
                    $sexe = $preselectedClient['sexe_client'] ?? '';
                    echo $sexe === 'M' ? 'Masculin' : ($sexe === 'F' ? 'Féminin' : '-');
                  ?>
                </span>
              </div>

              <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block; margin-bottom: 2px;">Profession</span>
                <span id="display-client-profession" style="font-size: 14px; font-weight: 800; color: #0F172A;">
                  <?= htmlspecialchars(!empty($preselectedClient['profession_client']) ? $preselectedClient['profession_client'] : 'Non renseignée') ?>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- SECTION 2 : CONFIGURATION DE LA SOUSCRIPTION & CHOIX DES PACKS -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03); margin-bottom: 24px;">
          <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
            <i data-lucide="package" style="width: 18px; height: 18px; color: #059669;"></i> Étape 2 : Session & Choix des Packs
          </h3>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 24px;">
            <div>
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                Session d'activité <span style="color: #EF4444;">*</span>
              </label>
              <select name="session_code" id="select-session" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A;" required>
                <option value="">-- Sélectionner une session --</option>
                <?php if (!empty($sessions)): ?>
                  <?php foreach ($sessions as $s): ?>
                    <option value="<?= htmlspecialchars($s['code_session']) ?>" data-zone="<?= htmlspecialchars($s['zone_code'] ?? Context::zone()) ?>">
                      <?= htmlspecialchars($s['code_session'] . ' - ' . ($s['nom_session'] ?? $s['libelle_session'] ?? $s['code_session'])) ?> (<?= (int)($s['nombre_jour_session'] ?? 0) ?> jours)
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>

            <div>
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                Filtrer par Catégorie de Pack
              </label>
              <select id="select-categorie" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A;">
                <option value="">Toutes les catégories</option>
                <?php if (!empty($categories)): ?>
                  <?php foreach ($categories as $c): ?>
                    <option value="<?= htmlspecialchars($c['code_categorie_pack']) ?>">
                      <?= htmlspecialchars($c['libelle_categorie_pack']) ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
          </div>

          <!-- GRILLE DES PACKS DISPONIBLES -->
          <div style="margin-bottom: 24px;">
            <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 12px;">
              Packs disponibles pour cette session <span style="color: #EF4444;">*</span>
            </label>
            <div id="packs-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;">
              <p style="color: #94A3B8; text-align: center; padding: 30px 0; grid-column: 1 / -1; font-style: italic; background: #F8FAFC; border-radius: 12px; border: 1px dashed #CBD5E1;">
                Veuillez d'abord sélectionner une session d'activité pour afficher les packs disponibles.
              </p>
            </div>
          </div>

          <!-- TABLEAU DE RÉCAPITULATIF ET CALCULS EN TEMPS RÉEL -->
          <div style="background: #F8FAFC; border-radius: 12px; border: 1px solid #E2E8F0; padding: 20px;">
            <h4 style="font-size: 13px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 14px 0; display: flex; align-items: center; gap: 6px;">
              <i data-lucide="calculator" style="width: 16px; height: 16px; color: #059669;"></i> Récapitulatif des Packs & Calculs Automatiques
            </h4>

            <div style="overflow-x: auto; margin-bottom: 16px;">
              <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                  <tr style="border-bottom: 2px solid #CBD5E1; text-align: left; color: #475569; font-weight: 700;">
                    <th style="padding: 10px;">Pack</th>
                    <th style="padding: 10px;">Catégorie</th>
                    <th style="padding: 10px; text-align: right;">Prix Jour</th>
                    <th style="padding: 10px; text-align: center;">Articles</th>
                    <th style="padding: 10px; text-align: center;">Durée</th>
                    <th style="padding: 10px; text-align: right;">Total Prévu</th>
                    <th style="padding: 10px; text-align: center;">Action</th>
                  </tr>
                </thead>
                <tbody id="table-packs-body">
                  <tr>
                    <td colspan="7" style="padding: 20px; text-align: center; color: #94A3B8;">
                      Aucun pack sélectionné pour l'instant.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- CARTE DES TOTAUX (JOURNALIER ET GLOBAL) -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; background: #FFFFFF; padding: 18px; border-radius: 10px; border: 1px solid #CBD5E1;">
              <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 10px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center;">
                  <i data-lucide="calendar" style="width: 22px; height: 22px;"></i>
                </div>
                <div>
                  <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; display: block;">Cotisation Journalière Totale</span>
                  <span id="total-daily-amount" style="font-size: 18px; font-weight: 800; color: #2563EB;">0 FCFA</span>
                </div>
              </div>

              <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 10px; background: #F0FDF4; color: #166534; display: flex; align-items: center; justify-content: center;">
                  <i data-lucide="coins" style="width: 22px; height: 22px;"></i>
                </div>
                <div>
                  <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; display: block;">Montant Total à Payer</span>
                  <span id="total-grand-amount" style="font-size: 20px; font-weight: 900; color: #059669;">0 FCFA</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- BOUTON D'ENREGISTREMENT -->
        <div style="display: flex; justify-content: flex-end; gap: 14px; margin-top: 24px;">
          <a href="<?= RACINE ?>souscription/list" class="btn" style="background: #FFFFFF; border: 1px solid #CBD5E1; color: #475569; font-weight: 700; padding: 12px 24px; border-radius: 10px; text-decoration: none;">
            Annuler
          </a>
          <button type="submit" id="btn-submit-ressouscription" class="btn" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #FFFFFF; font-weight: 800; padding: 12px 28px; border-radius: 10px; border: none; box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3); display: inline-flex; align-items: center; gap: 10px; cursor: pointer; transition: all 0.2s ease;">
            <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Valider & Enregistrer la Ressouscription
          </button>
        </div>
      </form>
    </div>
  </main>
</div>

<!-- SCRIPTS ET LOGIQUE JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script>
var selectedPacks = [];
var loadedPacksData = [];

function showMessage(type, text) {
  var $msg = $('#form-messages');
  var bg = type === 'danger' ? '#FEE2E2' : '#DCFCE7';
  var fg = type === 'danger' ? '#991B1B' : '#166534';
  var border = type === 'danger' ? '#FCA5A5' : '#86EFAC';
  $msg.css({ background: bg, color: fg, border: '1px solid ' + border }).html(text).show();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateClientCard(data) {
  if (data && data.code_client) {
    $('#client_code').val(data.code_client);
    $('#display-client-code').text(data.code_client);
    $('#display-client-nom').text(data.nom_client || '-');
    $('#display-client-tel').text(data.telephone_client || '-');
    $('#display-client-cni').text(data.numero_cni || 'Non renseigné');
    $('#display-client-lieu').text(data.lieu_residence_client || '-');
    var sexeTxt = data.sexe_client === 'M' ? 'Masculin' : (data.sexe_client === 'F' ? 'Féminin' : '-');
    $('#display-client-sexe').text(sexeTxt);
    $('#display-client-profession').text(data.profession_client || 'Non renseignée');
    $('#client-info-card').slideDown(200);
  } else {
    $('#client_code').val('');
    $('#client-info-card').slideUp(200);
  }
}

function loadAvailablePacks() {
  var sessionCode = $('#select-session').val();
  var categorieCode = $('#select-categorie').val();
  var container = $('#packs-container');

  if (!sessionCode) {
    container.html('<p style="color: #94A3B8; text-align: center; padding: 30px 0; grid-column: 1 / -1; font-style: italic; background: #F8FAFC; border-radius: 12px; border: 1px dashed #CBD5E1;">Veuillez d\'abord sélectionner une session d\'activité pour afficher les packs disponibles.</p>');
    loadedPacksData = [];
    renderSelectedPacksTable();
    return;
  }

  container.html('<p style="color: #059669; text-align: center; padding: 30px 0; grid-column: 1 / -1; font-weight: 700;">Chargement des packs en cours...</p>');

  $.ajax({
    url: '<?= RACINE ?>souscription/wizardData',
    type: 'GET',
    data: { session_code: sessionCode, categorie_code: categorieCode },
    dataType: 'json',
    success: function(res) {
      if (res.status === 1 && res.data && res.data.length > 0) {
        loadedPacksData = res.data;
        var html = '';
        res.data.forEach(function(pack) {
          var isSelected = selectedPacks.indexOf(pack.code_pack) !== -1;
          var cardBg = isSelected ? '#F0FDF4' : '#FFFFFF';
          var borderCol = isSelected ? '#059669' : '#E2E8F0';
          var checkIcon = isSelected ? '<span style="background: #059669; color: #FFF; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 900;">✓</span>' : '<span style="border: 2px solid #CBD5E1; width: 20px; height: 20px; border-radius: 50%; display: inline-block;"></span>';

          var prixCotis = parseFloat(pack.prix_cotisation_pack || 0);
          var duree = parseInt(pack.nombre_jour_session || 0);
          var totalPack = prixCotis * duree;

          html += '<div class="pack-card" data-code="' + pack.code_pack + '" style="background: ' + cardBg + '; border: 2px solid ' + borderCol + '; border-radius: 14px; padding: 18px; cursor: pointer; transition: all 0.2s ease; position: relative; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">' +
            '<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">' +
              '<span style="font-size: 11px; font-weight: 800; color: #059669; background: #ECFDF5; padding: 3px 8px; border-radius: 12px; text-transform: uppercase;">' + (pack.libelle_categorie_pack || 'Général') + '</span>' +
              checkIcon +
            '</div>' +
            '<h4 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0 0 8px 0;">' + (pack.libelle_pack || 'Pack') + '</h4>' +
            '<div style="margin-bottom: 12px;">' +
              '<span style="font-size: 18px; font-weight: 900; color: #2563EB;">' + prixCotis.toLocaleString('fr-FR') + ' FCFA</span>' +
              '<span style="font-size: 12px; color: #64748B; font-weight: 600;"> / jour</span>' +
            '</div>' +
            '<div style="font-size: 12px; color: #475569; display: flex; justify-content: space-between; border-top: 1px solid #F1F5F9; padding-top: 8px; margin-top: 8px;">' +
              '<span>Articles: <strong>' + (pack.nombre_articles || 0) + '</strong></span>' +
              '<span>Durée: <strong>' + duree + ' j</strong></span>' +
            '</div>' +
            '<div style="font-size: 12px; color: #059669; font-weight: 800; text-align: right; margin-top: 6px;">' +
              'Total: ' + totalPack.toLocaleString('fr-FR') + ' FCFA' +
            '</div>' +
          '</div>';
        });
        container.html(html);
      } else {
        loadedPacksData = [];
        container.html('<p style="color: #94A3B8; text-align: center; padding: 30px 0; grid-column: 1 / -1; font-style: italic;">Aucun pack disponible pour cette session.</p>');
      }
      renderSelectedPacksTable();
    },
    error: function() {
      container.html('<p style="color: #EF4444; text-align: center; padding: 30px 0; grid-column: 1 / -1;">Erreur lors du chargement des packs.</p>');
    }
  });
}

function renderSelectedPacksTable() {
  var tbody = $('#table-packs-body');
  tbody.empty();

  var totalDaily = 0;
  var totalGrand = 0;

  if (selectedPacks.length === 0) {
    tbody.html('<tr><td colspan="7" style="padding: 20px; text-align: center; color: #94A3B8;">Aucun pack sélectionné.</td></tr>');
    $('#total-daily-amount').text('0 FCFA');
    $('#total-grand-amount').text('0 FCFA');
    return;
  }

  selectedPacks.forEach(function(code) {
    var pack = loadedPacksData.find(function(p) { return p.code_pack === code; });
    if (pack) {
      var prixCotis = parseFloat(pack.prix_cotisation_pack || 0);
      var duree = parseInt(pack.nombre_jour_session || 0);
      var totalPack = prixCotis * duree;

      totalDaily += prixCotis;
      totalGrand += totalPack;

      var row = '<tr data-code="' + pack.code_pack + '" style="border-bottom: 1px solid #E2E8F0;">' +
        '<td style="padding: 10px; font-weight: 700; color: #0F172A;">' + (pack.libelle_pack || 'Pack') + '</td>' +
        '<td style="padding: 10px;"><span style="font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 12px; background: #E2E8F0; color: #334155;">' + (pack.libelle_categorie_pack || '-') + '</span></td>' +
        '<td style="padding: 10px; text-align: right; font-weight: 700; color: #2563EB;">' + prixCotis.toLocaleString('fr-FR') + ' FCFA</td>' +
        '<td style="padding: 10px; text-align: center;">' + (pack.nombre_articles || 0) + '</td>' +
        '<td style="padding: 10px; text-align: center;">' + duree + ' j</td>' +
        '<td style="padding: 10px; text-align: right; font-weight: 800; color: #059669;">' + totalPack.toLocaleString('fr-FR') + ' FCFA</td>' +
        '<td style="padding: 10px; text-align: center;">' +
          '<button type="button" class="btn-remove-pack" data-code="' + pack.code_pack + '" style="border-radius: 6px; font-weight: 700; background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; padding: 4px 8px; cursor: pointer; font-size: 12px;">' +
            'Supprimer' +
          '</button>' +
        '</td>' +
      '</tr>';
      tbody.append(row);
    }
  });

  $('#total-daily-amount').text(totalDaily.toLocaleString('fr-FR') + ' FCFA');
  $('#total-grand-amount').text(totalGrand.toLocaleString('fr-FR') + ' FCFA');
}

$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  // Initialisation de Select2 AJAX pour la recherche de client
  $('#select-client-search').select2({
    placeholder: 'Saisissez le Nom, Téléphone, Code Client ou CNI...',
    allowClear: true,
    minimumInputLength: 2,
    language: {
      inputTooShort: function() { return 'Veuillez saisir au moins 2 caractères...'; },
      searching: function() { return 'Recherche du client en cours...'; },
      noResults: function() { return 'Aucun client trouvé'; }
    },
    ajax: {
      url: '<?= RACINE ?>souscription/apiClientsSearch',
      dataType: 'json',
      delay: 300,
      data: function(params) {
        return { q: params.term };
      },
      processResults: function(data) {
        return {
          results: $.map(data, function(item) {
            return {
              id: item.code_client,
              text: item.text || (item.code_client + ' - ' + item.nom_client + ' (' + item.telephone_client + ')'),
              clientData: item
            };
          })
        };
      },
      cache: true
    }
  }).on('select2:select', function(e) {
    var data = e.params.data.clientData;
    updateClientCard(data);
  }).on('select2:clear', function() {
    updateClientCard(null);
  });

  // Événements sur la sélection de Session et Catégorie
  $('#select-session').on('change', function() {
    selectedPacks = [];
    var selectedZone = $(this).find('option:selected').data('zone') || '<?= Context::zone() ?>';
    $('#zone_code').val(selectedZone);
    loadAvailablePacks();
  });

  $('#select-categorie').on('change', function() {
    loadAvailablePacks();
  });

  // Sélection/Désélection d'un pack via carte
  $(document).on('click', '.pack-card', function() {
    var code = $(this).data('code');
    var idx = selectedPacks.indexOf(code);
    if (idx === -1) {
      selectedPacks.push(code);
    } else {
      selectedPacks.splice(idx, 1);
    }
    loadAvailablePacks();
  });

  // Suppression d'un pack depuis la table récapitulative
  $(document).on('click', '.btn-remove-pack', function(e) {
    e.stopPropagation();
    var code = $(this).data('code');
    var idx = selectedPacks.indexOf(code);
    if (idx !== -1) {
      selectedPacks.splice(idx, 1);
    }
    loadAvailablePacks();
  });

  // Soumission AJAX du formulaire de Ressouscription
  $('#form-ressouscription').on('submit', function(e) {
    e.preventDefault();

    var clientCode = $('#client_code').val();
    if (!clientCode) {
      showMessage('danger', 'Veuillez rechercher et sélectionner un client existant.');
      return;
    }

    var sessionCode = $('#select-session').val();
    if (!sessionCode) {
      showMessage('danger', 'Veuillez sélectionner une session d\'activité.');
      return;
    }

    if (selectedPacks.length === 0) {
      showMessage('danger', 'Veuillez sélectionner au moins un pack.');
      return;
    }

    $('#hidden-packs').val(JSON.stringify(selectedPacks));

    var btnSubmit = $('#btn-submit-ressouscription');
    btnSubmit.prop('disabled', true).html('<i data-lucide="loader" class="spin"></i> Enregistrement en cours...');

    $.ajax({
      url: $(this).attr('action'),
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        btnSubmit.prop('disabled', false).html('<i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Valider & Enregistrer la Ressouscription');
        if (window.lucide) lucide.createIcons();

        if (res.status === 1 || res.success) {
          if (window.toastr) {
            toastr.success(res.message || 'Ressouscription enregistrée avec succès !');
          } else {
            showMessage('success', res.message || 'Ressouscription enregistrée avec succès !');
          }

          var targetUrl = '<?= RACINE ?>cautisation-payment/situation/' + (res.code_souscription || '');
          setTimeout(function() {
            window.location.href = targetUrl;
          }, 1200);
        } else {
          showMessage('danger', res.message || 'Erreur lors de l\'enregistrement de la ressouscription.');
          if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement.');
        }
      },
      error: function() {
        btnSubmit.prop('disabled', false).html('<i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Valider & Enregistrer la Ressouscription');
        if (window.lucide) lucide.createIcons();
        showMessage('danger', 'Erreur réseau ou serveur indisponible lors de la ressouscription.');
        if (window.toastr) toastr.error('Erreur réseau');
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
