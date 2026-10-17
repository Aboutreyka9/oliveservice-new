<?php
require_once __DIR__ . '/../../public/inc/header.php';
$user = isset($user) ? $user : [];
$role = isset($role) ? $role : [];
$userRoles = isset($userRoles) ? $userRoles : [];
$isCommercial = isset($isCommercial) ? (bool)$isCommercial : false;
$clients = isset($clients) ? $clients : [];
$kpis = isset($kpis) ? $kpis : [
    'total_clients' => count($clients),
    'clients_actifs' => 0,
    'clients_inactifs' => 0,
    'clients_transferes' => 0,
    'total_souscriptions' => 0,
    'total_cotisations' => 0,
    'commission_taux' => 0,
    'commission_estimee' => 0
];
$encryptedId = $encryptedId ?? '';

$nomUser = trim($user['nom_user'] ?? '');
$prenomUser = trim($user['prenom_user'] ?? '');
$nomComplet = trim($nomUser . ' ' . $prenomUser);
if (empty($nomComplet)) $nomComplet = 'Utilisateur Sans Nom';

// Calcul des initiales pour le badge avatar
$words = explode(' ', $nomComplet);
$initials = '';
foreach ($words as $w) {
    if (!empty($w)) $initials .= mb_substr($w, 0, 1, 'UTF-8');
}
$initials = mb_strtoupper(mb_substr($initials, 0, 2, 'UTF-8'), 'UTF-8');
if (empty($initials)) $initials = 'US';

$isActif = (($user['statut_user'] ?? '') === 'actif');

// Numéro de téléphone nettoyé et lien WhatsApp
$rawTel = trim($user['telephone_user'] ?? '');
$cleanTel = preg_replace('/[^0-9]/', '', $rawTel);
$waLink = null;
if (!empty($cleanTel)) {
    if (strlen($cleanTel) === 10) {
        $waNumber = '225' . $cleanTel;
    } elseif (strpos($cleanTel, '225') === 0) {
        $waNumber = $cleanTel;
    } else {
        $waNumber = $cleanTel;
    }
    $waLink = "https://wa.me/{$waNumber}";
}

$displayRoles = !empty($userRoles) ? $userRoles : (!empty($role) ? [$role] : []);
?>
<style>
/* Tabs Navigation */
.user-tabs-nav {
  display: flex;
  gap: 8px;
  border-bottom: 2px solid #E2E8F0;
  margin-bottom: 24px;
}
.user-tab-btn {
  background: transparent;
  border: none;
  padding: 12px 20px;
  font-weight: 700;
  font-size: 14px;
  color: #64748B;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  border-bottom: 3px solid transparent;
  margin-bottom: -2px;
  transition: all 0.2s ease;
}
.user-tab-btn:hover {
  color: #1E3A5F;
  background: rgba(30, 58, 95, 0.04);
  border-radius: 8px 8px 0 0;
}
.user-tab-btn.active {
  color: #1E3A5F;
  border-bottom-color: #1E3A5F;
}
.user-tab-btn .badge-pill {
  background: #E2E8F0;
  color: #475569;
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 12px;
  font-weight: 800;
}
.user-tab-btn.active .badge-pill {
  background: #1E3A5F;
  color: #FFFFFF;
}

/* KPI Cards */
.kpi-card {
  background: #FFFFFF;
  border: 1px solid #E2E8F0;
  border-radius: 14px;
  padding: 20px 22px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04);
  display: flex;
  align-items: center;
  gap: 16px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
}
.kpi-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

/* Filter Pills */
.btn-filter-pill {
  background: #FFFFFF;
  border: 1.5px solid #CBD5E1;
  color: #475569;
  border-radius: 8px;
  padding: 6px 14px;
  font-size: 12px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  transition: all 0.15s ease;
}
.btn-filter-pill:hover {
  background: #F1F5F9;
  color: #1E3A5F;
  border-color: #94A3B8;
}
.btn-filter-pill.active {
  background: #1E3A5F;
  color: #FFFFFF;
  border-color: #1E3A5F;
}
.btn-filter-pill .pill-count {
  background: rgba(0,0,0,0.08);
  padding: 2px 6px;
  border-radius: 10px;
  font-size: 11px;
}
.btn-filter-pill.active .pill-count {
  background: rgba(255,255,255,0.25);
  color: #FFFFFF;
}

/* DataTables Styling */
#table-user-clients thead th {
  background: #F8FAFC !important;
  color: #475569 !important;
  font-size: 11px !important;
  font-weight: 800 !important;
  text-transform: uppercase !important;
  letter-spacing: 0.5px !important;
  border-bottom: 2px solid #E2E8F0 !important;
  padding: 12px 14px !important;
}
#table-user-clients tbody td {
  padding: 12px 14px !important;
  vertical-align: middle !important;
  border-bottom: 1px solid #F1F5F9 !important;
  font-size: 13px !important;
}
#table-user-clients tbody tr:hover {
  background: #F8FAFC !important;
}
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- BARRE D'ACTIONS DU HAUT -->
      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 8px;">
          <a href="<?= RACINE ?>user/list" class="btn" style="background: #FFFFFF; border: 1.5px solid #CBD5E1; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 9px 16px; font-size: 13px; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour à la liste
          </a>
          <span style="color: #94A3B8; font-size: 13px; font-weight: 600;">/</span>
          <span style="color: #64748B; font-size: 13px; font-weight: 700;">Fiche Profil</span>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>user/edition/<?= $encryptedId ?>" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 10px 18px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 13px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
            <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier l'utilisateur
          </a>
        </div>
      </div>

      <!-- CARTE BANDEAU HERO : IDENTITÉ DE L'UTILISATEUR -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05); margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
          
          <!-- Avatar + Nom + Badges -->
          <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
            <div style="position: relative;">
              <div style="width: 72px; height: 72px; border-radius: 20px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 800; box-shadow: 0 6px 16px rgba(30, 58, 95, 0.3); border: 3px solid #FFFFFF;">
                <?= htmlspecialchars($initials) ?>
              </div>
              <span style="position: absolute; bottom: 2px; right: 2px; width: 14px; height: 14px; border-radius: 50%; background: <?= $isActif ? '#10B981' : '#EF4444' ?>; border: 2.5px solid #FFFFFF;" title="<?= $isActif ? 'Compte Actif' : 'Compte Inactif' ?>"></span>
            </div>

            <div>
              <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
                  <?= htmlspecialchars($nomComplet) ?>
                </h1>
                
                <?php if ($isActif): ?>
                  <span style="display: inline-flex; align-items: center; gap: 5px; background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 800;">
                    <i data-lucide="check-circle" style="width: 12px; height: 12px;"></i> Actif
                  </span>
                <?php else: ?>
                  <span style="display: inline-flex; align-items: center; gap: 5px; background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 800;">
                    <i data-lucide="x-circle" style="width: 12px; height: 12px;"></i> Inactif
                  </span>
                <?php endif; ?>

                <?php if ($isCommercial): ?>
                  <span style="display: inline-flex; align-items: center; gap: 5px; background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 800;">
                    <i data-lucide="briefcase" style="width: 12px; height: 12px;"></i> Commercial Terrain
                  </span>
                <?php endif; ?>
              </div>

              <!-- Ligne de métadonnées -->
              <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; margin-top: 8px;">
                <span style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; font-weight: 700; color: #1E3A5F; background: #F1F5F9; padding: 3px 8px; border-radius: 6px; border: 1px solid #CBD5E1;">
                  <?= htmlspecialchars($user['code_user'] ?? '-') ?>
                </span>

                <?php if (!empty($user['matricule_user'])): ?>
                  <span style="font-size: 12px; font-weight: 700; color: #475569; display: inline-flex; align-items: center; gap: 4px;">
                    <i data-lucide="hash" style="width: 13px; height: 13px; color: #64748B;"></i>
                    Matricule: <?= htmlspecialchars($user['matricule_user']) ?>
                  </span>
                <?php endif; ?>

                <span style="font-size: 12px; font-weight: 600; color: #475569; display: inline-flex; align-items: center; gap: 4px;">
                  <i data-lucide="map-pin" style="width: 13px; height: 13px; color: #2563EB;"></i>
                  Zone : <strong style="color: #1E293B;"><?= htmlspecialchars($user['libelle_zone'] ?? 'Non définie') ?></strong>
                </span>

                <span style="font-size: 12px; font-weight: 600; color: #475569; display: inline-flex; align-items: center; gap: 4px;">
                  <i data-lucide="award" style="width: 13px; height: 13px; color: #D97706;"></i>
                  Fonction : <strong style="color: #1E293B;"><?= htmlspecialchars($user['libelle_fonction'] ?? 'Non définie') ?></strong>
                </span>

                <?php if (isset($user['commission']) && $user['commission'] !== null && $user['commission'] !== ''): ?>
                  <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 800; color: #047857; background: #ECFDF5; padding: 3px 8px; border-radius: 6px; border: 1px solid #A7F3D0;">
                    <i data-lucide="percent" style="width: 12px; height: 12px;"></i> Commission : <?= htmlspecialchars($user['commission']) ?>%
                  </span>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Coordonnées rapides (Email / Téléphone) -->
          <div style="display: flex; flex-direction: column; gap: 8px; min-width: 200px;">
            <?php if (!empty($user['email_user'])): ?>
              <a href="mailto:<?= htmlspecialchars($user['email_user']) ?>" style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #2563EB; text-decoration: none;">
                <i data-lucide="mail" style="width: 15px; height: 15px; color: #64748B;"></i>
                <?= htmlspecialchars($user['email_user']) ?>
              </a>
            <?php endif; ?>

            <?php if (!empty($user['telephone_user'])): ?>
              <div style="display: flex; align-items: center; gap: 8px;">
                <a href="tel:<?= htmlspecialchars($cleanTel) ?>" style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: #1E293B; text-decoration: none;">
                  <i data-lucide="phone" style="width: 15px; height: 15px; color: #64748B;"></i>
                  <?= htmlspecialchars($user['telephone_user']) ?>
                </a>
                <?php if ($waLink): ?>
                  <a href="<?= $waLink ?>" target="_blank" rel="noopener noreferrer" style="background: #25D366; color: white; padding: 2px 7px; border-radius: 6px; font-size: 11px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 3px;" title="Discuter sur WhatsApp">
                    <i data-lucide="message-circle" style="width: 12px; height: 12px;"></i> WhatsApp
                  </a>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>

      <!-- CARTES KPI EN TEMPS RÉEL (4 INDICATEURS CLÉS) -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- KPI 1 : Portefeuille Clients -->
        <div class="kpi-card">
          <div class="kpi-icon" style="background: #EFF6FF; color: #1D4ED8;">
            <i data-lucide="users" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Portefeuille Clients</div>
            <div style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 2px 0;"><?= (int)($kpis['total_clients'] ?? 0) ?></div>
            <div style="font-size: 11.5px; color: #059669; font-weight: 700;">
              <?= (int)($kpis['clients_actifs'] ?? 0) ?> actif<?= ((int)($kpis['clients_actifs'] ?? 0) > 1) ? 's' : '' ?>
              <?php if (!empty($kpis['clients_transferes'])): ?>
                • <span style="color: #64748B;"><?= (int)$kpis['clients_transferes'] ?> réaffecté(s)</span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- KPI 2 : Souscriptions Initiées -->
        <div class="kpi-card">
          <div class="kpi-icon" style="background: #EEF2FF; color: #4F46E5;">
            <i data-lucide="file-text" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Souscriptions</div>
            <div style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 2px 0;"><?= (int)($kpis['total_souscriptions'] ?? 0) ?></div>
            <div style="font-size: 11.5px; color: #64748B; font-weight: 600;">Dossiers enregistrés</div>
          </div>
        </div>

        <!-- KPI 3 : Cotisations Encaissées -->
        <div class="kpi-card">
          <div class="kpi-icon" style="background: #ECFDF5; color: #059669;">
            <i data-lucide="wallet" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Cotisations Encaissées</div>
            <div style="font-size: 20px; font-weight: 800; color: #059669; margin: 2px 0;">
              <?= number_format((float)($kpis['total_cotisations'] ?? 0), 0, ',', ' ') ?> <small style="font-size: 12px; font-weight: 700;">FCFA</small>
            </div>
            <div style="font-size: 11.5px; color: #64748B; font-weight: 600;">Volume collecté</div>
          </div>
        </div>

        <!-- KPI 4 : Commissions Estimées -->
        <div class="kpi-card">
          <div class="kpi-icon" style="background: #FFFBEB; color: #D97706;">
            <i data-lucide="percent" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Commissions Estimées</div>
            <div style="font-size: 20px; font-weight: 800; color: #D97706; margin: 2px 0;">
              <?= number_format((float)($kpis['commission_estimee'] ?? 0), 0, ',', ' ') ?> <small style="font-size: 12px; font-weight: 700;">FCFA</small>
            </div>
            <div style="font-size: 11.5px; color: #475569; font-weight: 700;">Taux : <?= htmlspecialchars((string)($kpis['commission_taux'] ?? 0)) ?>%</div>
          </div>
        </div>

      </div>

      <!-- ONGLETS DE NAVIGATION (PORTEFEUILLE CLIENTS / PROFIL & SÉCURITÉ) -->
      <div class="user-tabs-nav">
        <button type="button" class="user-tab-btn <?= ($isCommercial || !empty($clients)) ? 'active' : '' ?>" data-tab="tab-clients" id="btn-tab-clients">
          <i data-lucide="users" style="width: 17px; height: 17px;"></i>
          Portefeuille Clients
          <span class="badge-pill" id="badge-clients-count"><?= count($clients) ?></span>
        </button>
        <button type="button" class="user-tab-btn <?= (!$isCommercial && empty($clients)) ? 'active' : '' ?>" data-tab="tab-profil" id="btn-tab-profil">
          <i data-lucide="shield-check" style="width: 17px; height: 17px;"></i>
          Profil & Permissions RBAC
        </button>
      </div>

      <!-- CONTENU DES ONGLETS -->

      <!-- === ONGLET 1 : PORTEFEUILLE CLIENTS === -->
      <div id="tab-clients" class="tab-pane" style="display: <?= ($isCommercial || !empty($clients)) ? 'block' : 'none' ?>;">
        
        <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05); width: 100%; box-sizing: border-box;">
          
          <!-- En-tête de la table avec filtres rapides -->
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #F1F5F9;">
            <div>
              <h2 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="users" style="width: 20px; height: 20px; color: #1E3A5F;"></i>
                Portefeuille des Clients Assignés
              </h2>
              <p style="font-size: 12.5px; color: #64748B; margin: 4px 0 0 0; font-weight: 500;">
                Clients rattachés à ce commercial avec suivi de leurs souscriptions et cotisations
              </p>
            </div>

            <!-- Filtres rapides -->
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
              <button type="button" class="btn-filter-pill active" data-filter="all">
                Tous <span class="pill-count"><?= count($clients) ?></span>
              </button>
              <button type="button" class="btn-filter-pill" data-filter="actif">
                Actifs <span class="pill-count"><?= (int)($kpis['clients_actifs'] ?? 0) ?></span>
              </button>
              <button type="button" class="btn-filter-pill" data-filter="inactif">
                Inactifs <span class="pill-count"><?= (int)($kpis['clients_inactifs'] ?? 0) ?></span>
              </button>
              <?php if (!empty($kpis['clients_transferes'])): ?>
                <button type="button" class="btn-filter-pill" data-filter="transfere">
                  Transférés <span class="pill-count"><?= (int)$kpis['clients_transferes'] ?></span>
                </button>
              <?php endif; ?>
            </div>
          </div>

          <?php if (!empty($clients)): ?>
            <!-- TABLEAU DES CLIENTS -->
            <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
              <table id="table-user-clients" class="table display nowrap" style="width:100%; border-collapse: collapse;">
                <thead>
                  <tr>
                    <th>Code</th>
                    <th>Client & Contact</th>
                    <th>Téléphone</th>
                    <th>Zone</th>
                    <th style="text-align: center;">Souscriptions</th>
                    <th style="text-align: right;">Total Cotisé</th>
                    <th style="text-align: center;">Statut Client</th>
                    <th style="text-align: center;">Affectation</th>
                    <th style="text-align: center;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($clients as $c): ?>
                    <?php 
                      $cIdEncrypted = $c['encrypted_id'] ?? '';
                      $cNom = trim($c['nom_client'] ?? 'Client');
                      $cCode = htmlspecialchars($c['code_client'] ?? '-');
                      $cTel = trim($c['telephone_client'] ?? '');
                      $cCleanTel = preg_replace('/[^0-9]/', '', $cTel);
                      $cWaLink = null;
                      if (!empty($cCleanTel)) {
                          if (strlen($cCleanTel) === 10) $cWaNumber = '225' . $cCleanTel;
                          elseif (strpos($cCleanTel, '225') === 0) $cWaNumber = $cCleanTel;
                          else $cWaNumber = $cCleanTel;
                          $cWaLink = "https://wa.me/{$cWaNumber}";
                      }
                      $cIsActif = (($c['statut_client'] ?? 'actif') === 'actif');
                      $cAffectation = $c['statut_affectation'] ?? 'actif';
                      $cIsTransfere = ($cAffectation !== 'actif');

                      // Initiales client
                      $cWords = explode(' ', $cNom);
                      $cInitials = '';
                      foreach ($cWords as $cw) {
                          if (!empty($cw)) $cInitials .= mb_substr($cw, 0, 1, 'UTF-8');
                      }
                      $cInitials = mb_strtoupper(mb_substr($cInitials, 0, 2, 'UTF-8'), 'UTF-8') ?: 'CL';
                    ?>
                    <tr data-status="<?= $cIsTransfere ? 'transfere' : ($cIsActif ? 'actif' : 'inactif') ?>">
                      
                      <!-- Code Client -->
                      <td>
                        <span style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 11.5px; font-weight: 700; color: #1E3A5F; background: #F1F5F9; padding: 4px 8px; border-radius: 6px; border: 1px solid #CBD5E1;">
                          <?= $cCode ?>
                        </span>
                      </td>

                      <!-- Nom & Initiales -->
                      <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                          <div style="width: 34px; height: 34px; border-radius: 8px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; flex-shrink: 0;">
                            <?= htmlspecialchars($cInitials) ?>
                          </div>
                          <div>
                            <a href="<?= RACINE ?>client/details/<?= $cIdEncrypted ?>" style="font-weight: 800; color: #0F172A; text-decoration: none; font-size: 13.5px;" onmouseover="this.style.color='#2563EB'" onmouseout="this.style.color='#0F172A'">
                              <?= htmlspecialchars($cNom) ?>
                            </a>
                            <div style="font-size: 11px; color: #64748B; font-weight: 500;">
                              Inscrit le <?= !empty($c['created_at_client']) ? date('d/m/Y', strtotime($c['created_at_client'])) : '-' ?>
                            </div>
                          </div>
                        </div>
                      </td>

                      <!-- Téléphone & WhatsApp -->
                      <td>
                        <?php if (!empty($cTel)): ?>
                          <div style="display: flex; align-items: center; gap: 6px;">
                            <a href="tel:<?= htmlspecialchars($cCleanTel) ?>" style="font-weight: 700; color: #334155; text-decoration: none; font-size: 12.5px;">
                              <?= htmlspecialchars($cTel) ?>
                            </a>
                            <?php if ($cWaLink): ?>
                              <a href="<?= $cWaLink ?>" target="_blank" rel="noopener noreferrer" style="background: #25D366; color: white; padding: 2px 5px; border-radius: 4px; font-size: 10px; text-decoration: none;" title="WhatsApp direct">
                                <i data-lucide="message-circle" style="width: 10px; height: 10px;"></i>
                              </a>
                            <?php endif; ?>
                          </div>
                        <?php else: ?>
                          <span style="color: #94A3B8; font-size: 12px; font-style: italic;">Non renseigné</span>
                        <?php endif; ?>
                      </td>

                      <!-- Zone -->
                      <td>
                        <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 600; color: #475569;">
                          <i data-lucide="map-pin" style="width: 12px; height: 12px; color: #2563EB;"></i>
                          <?= htmlspecialchars($c['libelle_zone'] ?? 'Non définie') ?>
                        </span>
                      </td>

                      <!-- Souscriptions -->
                      <td style="text-align: center;">
                        <span style="background: #EFF6FF; color: #1D4ED8; font-weight: 800; font-size: 11.5px; padding: 3px 10px; border-radius: 12px; border: 1px solid #BFDBFE;">
                          <?= (int)($c['nb_souscriptions'] ?? 0) ?>
                        </span>
                      </td>

                      <!-- Total Cotisé -->
                      <td style="text-align: right;">
                        <strong style="color: #059669; font-size: 13px;">
                          <?= number_format((float)($c['total_cotise_commercial'] ?? 0), 0, ',', ' ') ?>
                        </strong>
                        <small style="color: #64748B; font-weight: 700; font-size: 10.5px;">FCFA</small>
                      </td>

                      <!-- Statut Client -->
                      <td style="text-align: center;">
                        <?php if ($cIsActif): ?>
                          <span style="display: inline-flex; align-items: center; gap: 4px; background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0; padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 800;">
                            <span style="width: 5px; height: 5px; border-radius: 50%; background: #059669; display: inline-block;"></span> Actif
                          </span>
                        <?php else: ?>
                          <span style="display: inline-flex; align-items: center; gap: 4px; background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA; padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 800;">
                            <span style="width: 5px; height: 5px; border-radius: 50%; background: #DC2626; display: inline-block;"></span> Inactif
                          </span>
                        <?php endif; ?>
                      </td>

                      <!-- Statut Affectation -->
                      <td style="text-align: center;">
                        <?php if (!$cIsTransfere): ?>
                          <span style="background: #EFF6FF; color: #1E40AF; border: 1px solid #BFDBFE; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                            <i data-lucide="user-check" style="width: 11px; height: 11px;"></i> Affecté
                          </span>
                        <?php else: ?>
                          <span style="background: #FFFBEB; color: #B45309; border: 1px solid #FDE68A; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;" title="Ce client a été réaffecté à un autre commercial">
                            <i data-lucide="arrow-right-left" style="width: 11px; height: 11px;"></i> Transféré
                          </span>
                        <?php endif; ?>
                      </td>

                      <!-- Actions -->
                      <td style="text-align: center;">
                        <a href="<?= RACINE ?>client/details/<?= $cIdEncrypted ?>" class="btn" style="background: #1E3A5F; color: #FFFFFF; padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 2px 4px rgba(30, 58, 95, 0.15);" title="Consulter la fiche client">
                          <i data-lucide="eye" style="width: 13px; height: 13px;"></i> Voir
                        </a>
                      </td>

                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

          <?php else: ?>
            <!-- État vide si aucun client -->
            <div style="text-align: center; padding: 48px 20px; background: #F8FAFC; border-radius: 12px; border: 1px dashed #CBD5E1;">
              <div style="width: 56px; height: 56px; border-radius: 16px; background: #EEF2FF; color: #4F46E5; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                <i data-lucide="users" style="width: 28px; height: 28px;"></i>
              </div>
              <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0 0 6px 0;">Aucun client dans le portefeuille</h3>
              <p style="font-size: 13px; color: #64748B; margin: 0; max-width: 420px; margin: 0 auto;">
                Cet utilisateur ne gère actuellement aucun client actif dans son portefeuille. Les clients affectés ou enregistrés apparaîtront ici automatiquement.
              </p>
            </div>
          <?php endif; ?>

        </div>
      </div>

      <!-- === ONGLET 2 : PROFIL & PERMISSIONS RBAC === -->
      <div id="tab-profil" class="tab-pane" style="display: <?= (!$isCommercial && empty($clients)) ? 'block' : 'none' ?>;">
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; width: 100%; box-sizing: border-box;">
          
          <!-- CARTE 1 : IDENTITÉ & COORDONNÉES DÉTAILLÉES -->
          <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05); height: fit-content;">
            
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="user" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Informations Générales
            </h3>

            <div style="display: flex; flex-direction: column; gap: 16px;">
              
              <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Nom & Prénoms</span>
                <div style="font-size: 14.5px; font-weight: 800; color: #0F172A; margin-top: 2px;"><?= htmlspecialchars($nomComplet) ?></div>
              </div>

              <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Code Utilisateur</span>
                <div style="margin-top: 4px;">
                  <code style="font-weight:700; color:#1E3A5F; background:#F1F5F9; padding:4px 8px; border-radius:6px; font-size:12px; border:1px solid #CBD5E1;"><?= htmlspecialchars($user['code_user'] ?? '-') ?></code>
                </div>
              </div>

              <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Email de connexion</span>
                <div style="font-size: 14px; font-weight: 700; color: #0F172A; margin-top: 2px;"><?= htmlspecialchars($user['email_user'] ?? 'Non renseigné') ?></div>
              </div>

              <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Téléphone</span>
                <div style="font-size: 14px; font-weight: 600; color: #334155; margin-top: 2px;"><?= htmlspecialchars($user['telephone_user'] ?? 'Non renseigné') ?></div>
              </div>

              <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Sexe</span>
                <div style="font-size: 13.5px; font-weight: 600; color: #1E293B; margin-top: 2px;">
                  <?= (($user['sexe_user'] ?? '') === 'F') ? 'Féminin (Femme)' : 'Masculin (Homme)' ?>
                </div>
              </div>

              <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Zone d'Affectation</span>
                <div style="font-size: 14px; font-weight: 700; color: #1E3A5F; margin-top: 2px;"><?= htmlspecialchars($user['libelle_zone'] ?? 'Globale') ?></div>
              </div>

              <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Fonction Attribuée</span>
                <div style="font-size: 14px; font-weight: 700; color: #0F172A; margin-top: 2px;"><?= htmlspecialchars($user['libelle_fonction'] ?? 'Non définie') ?></div>
              </div>

              <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Date de Création du Compte</span>
                <div style="font-size: 13px; color: #475569; margin-top: 2px; font-weight: 600;">
                  <?= !empty($user['created_at_user']) ? date('d/m/Y à H:i', strtotime($user['created_at_user'])) : '-' ?>
                </div>
              </div>

              <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Dernière Connexion</span>
                <div style="font-size: 13px; color: #64748B; margin-top: 2px; font-weight: 500;">
                  <?= !empty($user['last_connexion']) ? date('d/m/Y H:i', strtotime($user['last_connexion'])) : 'Jamais connecté' ?>
                </div>
              </div>

            </div>
          </div>

          <!-- CARTE 2 : RÔLES & PRIVILÈGES RBAC -->
          <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);">
            
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
              <i data-lucide="shield-check" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Rôles Attribués & Privilèges RBAC
            </h3>

            <div style="margin-bottom: 24px;">
              <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">Rôles Système Détenus</div>
              
              <?php if (!empty($displayRoles)): ?>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                  <?php foreach($displayRoles as $ur): ?>
                    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-left: 4px solid #1E3A5F; border-radius: 10px; padding: 14px 18px;">
                      <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                        <div style="font-size: 15px; font-weight: 800; color: #1E3A5F; display: flex; align-items: center; gap: 8px;">
                          <i data-lucide="award" style="width: 16px; height: 16px; color: #1E3A5F;"></i>
                          <?= htmlspecialchars($ur['libelle_role'] ?? 'Rôle non défini') ?>
                        </div>
                        <span style="background: #F1F5F9; color: #475569; font-weight: 700; font-size: 11px; padding: 3px 8px; border-radius: 6px; border: 1px solid #CBD5E1;">
                          <?= htmlspecialchars($ur['groupe'] ?? ($ur['module'] ?? 'Standard')) ?>
                        </span>
                      </div>
                      <?php if (!empty($ur['description'])): ?>
                        <p style="color: #64748B; font-size: 12.5px; margin: 6px 0 0 24px; font-weight: 500;"><?= htmlspecialchars($ur['description']) ?></p>
                      <?php endif; ?>

                      <div style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed #E2E8F0; display: flex; flex-wrap: wrap; gap: 16px;">
                        <span style="font-size: 12px; font-weight: 700; color: <?= !empty($ur['create_permission']) ? '#059669' : '#94A3B8' ?>; display: inline-flex; align-items: center; gap: 4px;">
                          <i data-lucide="<?= !empty($ur['create_permission']) ? 'check' : 'x' ?>" style="width: 14px; height: 14px;"></i> Créer
                        </span>
                        <span style="font-size: 12px; font-weight: 700; color: <?= !empty($ur['edit_permission']) ? '#0284C7' : '#94A3B8' ?>; display: inline-flex; align-items: center; gap: 4px;">
                          <i data-lucide="<?= !empty($ur['edit_permission']) ? 'check' : 'x' ?>" style="width: 14px; height: 14px;"></i> Modifier
                        </span>
                        <span style="font-size: 12px; font-weight: 700; color: <?= (!isset($ur['show_permission']) || $ur['show_permission'] == 1) ? '#475569' : '#94A3B8' ?>; display: inline-flex; align-items: center; gap: 4px;">
                          <i data-lucide="<?= (!isset($ur['show_permission']) || $ur['show_permission'] == 1) ? 'check' : 'x' ?>" style="width: 14px; height: 14px;"></i> Consulter
                        </span>
                        <span style="font-size: 12px; font-weight: 700; color: <?= !empty($ur['delete_permission']) ? '#DC2626' : '#94A3B8' ?>; display: inline-flex; align-items: center; gap: 4px;">
                          <i data-lucide="<?= !empty($ur['delete_permission']) ? 'check' : 'x' ?>" style="width: 14px; height: 14px;"></i> Supprimer
                        </span>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div style="color: #94A3B8; font-style: italic; font-size: 13px;">Aucun rôle attribué pour le moment.</div>
              <?php endif; ?>
            </div>

            <!-- Matrice des Actions Autorisées -->
            <div style="background: #F8FAFC; border-radius: 12px; padding: 20px; border: 1px solid #E2E8F0;">
              <h4 style="font-size: 13px; font-weight: 800; color: #0F172A; margin: 0 0 16px 0;">Matrice des Actions Autorisées :</h4>
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px;">
                
                <div style="display: flex; align-items: center; gap: 10px;">
                  <i data-lucide="<?= (isset($role['create_permission']) && $role['create_permission'] == 1) ? 'check-circle-2' : 'x-circle' ?>" style="width: 20px; height: 20px; color: <?= (isset($role['create_permission']) && $role['create_permission'] == 1) ? '#059669' : '#DC2626' ?>;"></i>
                  <div>
                    <div style="font-weight: 800; font-size: 13px; color: #0F172A;">Création</div>
                    <div style="font-size: 11px; color: #64748B; font-weight: 500;"><?= (isset($role['create_permission']) && $role['create_permission'] == 1) ? 'Autorisé' : 'Refusé' ?></div>
                  </div>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                  <i data-lucide="<?= (isset($role['edit_permission']) && $role['edit_permission'] == 1) ? 'check-circle-2' : 'x-circle' ?>" style="width: 20px; height: 20px; color: <?= (isset($role['edit_permission']) && $role['edit_permission'] == 1) ? '#0284C7' : '#DC2626' ?>;"></i>
                  <div>
                    <div style="font-weight: 800; font-size: 13px; color: #0F172A;">Modification</div>
                    <div style="font-size: 11px; color: #64748B; font-weight: 500;"><?= (isset($role['edit_permission']) && $role['edit_permission'] == 1) ? 'Autorisé' : 'Refusé' ?></div>
                  </div>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                  <i data-lucide="<?= (!isset($role['show_permission']) || $role['show_permission'] == 1) ? 'check-circle-2' : 'x-circle' ?>" style="width: 20px; height: 20px; color: <?= (!isset($role['show_permission']) || $role['show_permission'] == 1) ? '#059669' : '#DC2626' ?>;"></i>
                  <div>
                    <div style="font-weight: 800; font-size: 13px; color: #0F172A;">Consultation</div>
                    <div style="font-size: 11px; color: #64748B; font-weight: 500;"><?= (!isset($role['show_permission']) || $role['show_permission'] == 1) ? 'Autorisé' : 'Refusé' ?></div>
                  </div>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                  <i data-lucide="<?= (isset($role['delete_permission']) && $role['delete_permission'] == 1) ? 'check-circle-2' : 'x-circle' ?>" style="width: 20px; height: 20px; color: <?= (isset($role['delete_permission']) && $role['delete_permission'] == 1) ? '#059669' : '#DC2626' ?>;"></i>
                  <div>
                    <div style="font-weight: 800; font-size: 13px; color: #0F172A;">Suppression</div>
                    <div style="font-size: 11px; color: #64748B; font-weight: 500;"><?= (isset($role['delete_permission']) && $role['delete_permission'] == 1) ? 'Autorisé' : 'Refusé' ?></div>
                  </div>
                </div>

              </div>
            </div>

          </div>

        </div>

      </div>

    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  // Initialisation de DataTables pour le portefeuille clients
  var clientTable = null;
  if ($('#table-user-clients').length > 0) {
    clientTable = $('#table-user-clients').DataTable({
      responsive: true,
      pageLength: 10,
      order: [],
      language: {
        search: "Rechercher un client :",
        searchPlaceholder: "Nom, code, téléphone...",
        lengthMenu: "Afficher _MENU_ clients par page",
        info: "Affichage de _START_ à _END_ sur _TOTAL_ clients",
        infoEmpty: "Aucun client à afficher",
        infoFiltered: "(filtré sur _MAX_ clients au total)",
        zeroRecords: "Aucun client correspondant trouvé",
        paginate: {
          first: '<i data-lucide="chevrons-left"></i>',
          previous: '<i data-lucide="chevron-left"></i>',
          next: '<i data-lucide="chevron-right"></i>',
          last: '<i data-lucide="chevrons-right"></i>'
        }
      },
      drawCallback: function() {
        if (window.lucide) lucide.createIcons();
      }
    });
  }

  // Gestion des onglets
  $('.user-tab-btn').on('click', function(e) {
    e.preventDefault();
    var targetTab = $(this).data('tab');
    
    $('.user-tab-btn').removeClass('active');
    $(this).addClass('active');

    $('.tab-pane').hide();
    $('#' + targetTab).fadeIn(150);

    if (targetTab === 'tab-clients' && clientTable) {
      clientTable.columns.adjust().responsive.recalc();
    }

    if (window.lucide) lucide.createIcons();
  });

  // Filtres rapides sur la table des clients
  $('.btn-filter-pill').on('click', function(e) {
    e.preventDefault();
    $('.btn-filter-pill').removeClass('active');
    $(this).addClass('active');

    var filter = $(this).data('filter');

    if (!clientTable) return;

    if (filter === 'all') {
      $.fn.dataTable.ext.search.pop();
    } else {
      $.fn.dataTable.ext.search.pop();
      $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var rowNode = clientTable.row(dataIndex).node();
        var rowStatus = $(rowNode).attr('data-status');
        return rowStatus === filter;
      });
    }

    clientTable.draw();
  });
});
</script>

<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
