<?php
require_once __DIR__ . '/../../public/inc/header.php';

$mission = isset($mission) ? $mission : [];

$codeMission = $mission['code_mission'] ?? '';
$codeCommande= $mission['commande_code'] ?? '';
$typeMission = $mission['type_mission'] ?? 'livraison';
$statutMission=$mission['statut_mission'] ?? 'en_attente';
$nomClient   = $mission['nom_client'] ?? 'Client';
$telClient   = $mission['telephone_client'] ?? '-';
$adrClient   = $mission['adresse_client'] ?? ($mission['adresse_mission'] ?? 'Adresse non renseignée');
$nomPressing = $mission['libelle_pressing'] ?? 'Pressing';
$telPressing = $mission['telephone_pressing'] ?? '';
$adrPressing = $mission['adresse_pressing'] ?? '';
$montantCmd  = (float)($mission['montant_total_commande'] ?? 0);
$isColis     = ($mission['type_commande'] ?? '') === 'colis';
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <!-- === EN-TÊTE MISSION === -->
      <div class="page-header" style="margin-bottom: 20px;">
        <div>
          <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <h1 style="margin: 0; font-size: 22px; font-weight: 800; color: #1E293B;">Mission #<?= htmlspecialchars($codeMission) ?></h1>
            <span style="background: <?= $typeMission === 'collecte' ? '#FEF3C7' : '#EFF6FF' ?>; color: <?= $typeMission === 'collecte' ? '#92400E' : '#1E40AF' ?>; padding: 4px 10px; border-radius: 20px; font-weight: 700; font-size: 12px; text-transform: uppercase;">
              <?= htmlspecialchars($typeMission) ?>
            </span>
            <span class="badge-status <?= $statutMission === 'terminee' ? 'delivered' : ($statutMission === 'annulee' ? 'cancelled' : 'badge-status-progress') ?>" style="font-size: 12px;">
              <?= htmlspecialchars($statutMission) ?>
            </span>
          </div>
          <p class="page-subtitle" style="margin: 4px 0 0; color: #64748B; font-size: 13px;">
            Pour la commande <strong style="color: #1E3A5F;">#<?= htmlspecialchars($codeCommande) ?></strong>
          </p>
        </div>
        <a href="<?= RACINE ?>mission/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
          <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour aux missions
        </a>
      </div>

      <!-- === PANNEAU D'ACTIONS TERRAIN COURSIER === -->
      <?php if ($statutMission !== 'terminee' && $statutMission !== 'annulee'): ?>
        <div class="card" style="background: linear-gradient(135deg, #1E3A5F, #0F766E); color: #FFF; padding: 22px; border-radius: 16px; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(30, 58, 95, 0.3);">
          <h3 style="margin: 0 0 6px; font-size: 17px; font-weight: 700;">Actions Terrain du Livreur</h3>
          <p style="margin: 0 0 16px; font-size: 13px; opacity: 0.9;">Cliquez dès que vous franchissez chaque étape pour notifier le client et le pressing en temps réel.</p>

          <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <?php if ($typeMission === 'collecte'): ?>
              <?php if ($statutMission === 'en_attente'): ?>
                <button type="button" class="btn btn-primary" onclick="missionEnRouteCollecte('<?= $codeMission ?>')" style="background: #D97706; border-color: #D97706; font-size: 14px; padding: 10px 18px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                  <i data-lucide="navigation" style="width: 18px; height: 18px;"></i> 1. Je pars pour la collecte
                </button>
              <?php elseif ($statutMission === 'en_cours'): ?>
                <button type="button" class="btn btn-primary" onclick="missionLingeCollecte('<?= $codeMission ?>')" style="background: #2563EB; border-color: #2563EB; font-size: 14px; padding: 10px 18px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                  <i data-lucide="package-check" style="width: 18px; height: 18px;"></i> 2. Linge collecté chez le client
                </button>
                <button type="button" class="btn btn-primary" onclick="missionDeposeAuPressing('<?= $codeMission ?>')" style="background: #059669; border-color: #059669; font-size: 14px; padding: 10px 18px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                  <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> 3. Linge déposé au pressing
                </button>
              <?php endif; ?>

            <?php else: /* Livraison */ ?>
              <?php if ($statutMission === 'en_attente'): ?>
                <button type="button" class="btn btn-primary" onclick="missionEnRouteLivraison('<?= $codeMission ?>')" style="background: #D97706; border-color: #D97706; font-size: 14px; padding: 10px 18px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                  <i data-lucide="navigation" style="width: 18px; height: 18px;"></i> 1. Je pars pour la livraison
                </button>
              <?php elseif ($statutMission === 'en_cours'): ?>
                <button type="button" class="btn btn-primary" onclick="missionRemiseAuClient('<?= $codeMission ?>')" style="background: #059669; border-color: #059669; font-size: 14px; padding: 10px 18px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                  <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> 2. Linge remis au client (Livrée)
                </button>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      <?php else: ?>
        <div style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 14px 18px; border-radius: 12px; margin-bottom: 24px; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="check-circle" style="width: 20px; height: 20px; color: #059669;"></i>
          Mission terminée avec succès.
        </div>
      <?php endif; ?>

      <!-- === ITINÉRAIRE & DÉTAILS === -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <!-- Carte Client -->
        <div class="card" style="padding: 20px;">
          <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px; border-bottom: 1px solid #E2E8F0; padding-bottom: 10px;">
            <i data-lucide="user" style="color: #1E3A5F; width: 20px; height: 20px;"></i>
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #1E293B;">Adresse du Client</h3>
          </div>
          <div style="display: flex; flex-direction: column; gap: 12px; font-size: 14px;">
            <div>
              <span style="color: #64748B; font-size: 12px; font-weight: 600; display: block;">Destinataire</span>
              <strong style="font-size: 15px;"><?= htmlspecialchars($nomClient) ?></strong>
            </div>
            <div>
              <span style="color: #64748B; font-size: 12px; font-weight: 600; display: block;">Téléphone du client</span>
              <a href="tel:<?= htmlspecialchars($telClient) ?>" class="btn btn-sm btn-primary" style="margin-top: 4px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                <i data-lucide="phone-call" style="width: 14px; height: 14px;"></i> Appeler : <?= htmlspecialchars($telClient) ?>
              </a>
            </div>
            <div>
              <span style="color: #64748B; font-size: 12px; font-weight: 600; display: block;">Lieu de rendez-vous</span>
              <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px; margin-top: 4px; font-weight: 600; color: #334155;">
                <i data-lucide="map-pin" style="width: 16px; height: 16px; vertical-align: middle; color: #DC2626; display: inline;"></i> <?= htmlspecialchars($adrClient) ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Carte Pressing -->
        <div class="card" style="padding: 20px;">
          <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px; border-bottom: 1px solid #E2E8F0; padding-bottom: 10px;">
            <i data-lucide="store" style="color: #0F766E; width: 20px; height: 20px;"></i>
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #1E293B;">Pressing Partenaire</h3>
          </div>
          <div style="display: flex; flex-direction: column; gap: 12px; font-size: 14px;">
            <div>
              <span style="color: #64748B; font-size: 12px; font-weight: 600; display: block;">Établissement</span>
              <strong style="font-size: 15px; color: #0F766E;"><?= htmlspecialchars($nomPressing) ?></strong>
            </div>
            <?php if (!empty($telPressing)): ?>
              <div>
                <span style="color: #64748B; font-size: 12px; font-weight: 600; display: block;">Contact Pressing</span>
                <a href="tel:<?= htmlspecialchars($telPressing) ?>" style="color: #1E3A5F; font-weight: 600; text-decoration: none;">
                  <?= htmlspecialchars($telPressing) ?>
                </a>
              </div>
            <?php endif; ?>
            <?php if (!empty($adrPressing)): ?>
              <div>
                <span style="color: #64748B; font-size: 12px; font-weight: 600; display: block;">Adresse Pressing</span>
                <span style="color: #334155;"><?= htmlspecialchars($adrPressing) ?></span>
              </div>
            <?php endif; ?>
            <div style="margin-top: 6px; padding-top: 10px; border-top: 1px solid #F1F5F9; display: flex; justify-content: space-between; align-items: center;">
              <span style="font-weight: 600; color: #64748B;">Montant Commande :</span>
              <strong style="font-size: 16px; color: #059669;"><?= number_format($montantCmd, 0, ',', ' ') ?> FCFA</strong>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
const baseApiUrl = (typeof LINK !== 'undefined') ? LINK : ((typeof RACINE !== 'undefined') ? RACINE : '/admin-lavex/');

function missionEnRouteCollecte(code) {
  if (typeof showConfirm === 'function') {
    showConfirm('Confirmer votre départ pour la collecte chez le client ?', function() {
      $.post(baseApiUrl + 'mission/enRouteCollecte', { code_mission: code }, function(rep) {
        if (typeof showToast === 'function') showToast(rep.message || 'En route', rep.status ? 'success' : 'error');
        if (rep.status) setTimeout(() => window.location.reload(), 800);
      }, 'json').fail(function() { if (typeof showToast === 'function') showToast('Erreur serveur', 'error'); });
    });
  }
}

function missionLingeCollecte(code) {
  if (typeof showConfirm === 'function') {
    showConfirm('Confirmer que le linge a bien été récupéré chez le client ?', function() {
      $.post(baseApiUrl + 'mission/lingeCollecte', { code_mission: code }, function(rep) {
        if (typeof showToast === 'function') showToast(rep.message || 'Linge collecté', rep.status ? 'success' : 'error');
        if (rep.status) setTimeout(() => window.location.reload(), 800);
      }, 'json').fail(function() { if (typeof showToast === 'function') showToast('Erreur serveur', 'error'); });
    });
  }
}

function missionDeposeAuPressing(code) {
  if (typeof showConfirm === 'function') {
    showConfirm('Confirmer le dépôt du linge au pressing ?', function() {
      $.post(baseApiUrl + 'mission/deposeAuPressing', { code_mission: code }, function(rep) {
        if (typeof showToast === 'function') showToast(rep.message || 'Linge déposé', rep.status ? 'success' : 'error');
        if (rep.status) setTimeout(() => window.location.reload(), 800);
      }, 'json').fail(function() { if (typeof showToast === 'function') showToast('Erreur serveur', 'error'); });
    });
  }
}

function missionEnRouteLivraison(code) {
  if (typeof showConfirm === 'function') {
    showConfirm('Confirmer votre départ du pressing pour livrer le client ?', function() {
      $.post(baseApiUrl + 'mission/enRouteLivraison', { code_mission: code }, function(rep) {
        if (typeof showToast === 'function') showToast(rep.message || 'En route', rep.status ? 'success' : 'error');
        if (rep.status) setTimeout(() => window.location.reload(), 800);
      }, 'json').fail(function() { if (typeof showToast === 'function') showToast('Erreur serveur', 'error'); });
    });
  }
}

function missionRemiseAuClient(code) {
  if (typeof showConfirm === 'function') {
    showConfirm('Confirmer la remise du linge propre au client ?', function() {
      $.post(baseApiUrl + 'mission/remiseAuClient', { code_mission: code }, function(rep) {
        if (typeof showToast === 'function') showToast(rep.message || 'Linge remis', rep.status ? 'success' : 'error');
        if (rep.status) setTimeout(() => window.location.reload(), 800);
      }, 'json').fail(function() { if (typeof showToast === 'function') showToast('Erreur serveur', 'error'); });
    });
  }
}
</script>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
