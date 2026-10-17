<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$item = $item ?? [];
$souscriptions = $souscriptions ?? [];
$cotisations = $cotisations ?? [];
$encryptedId = $encryptedId ?? '';

$nomClient = trim($item['nom_client'] ?? '');
$prenomClient = trim($item['prenom_client'] ?? '');
$nomComplet = trim($nomClient . ' ' . $prenomClient);
if (empty($nomComplet)) $nomComplet = 'Client Sans Nom';

// Calcul des initiales pour le badge avatar
$words = explode(' ', $nomComplet);
$initials = '';
foreach ($words as $w) {
    if (!empty($w)) $initials .= mb_substr($w, 0, 1, 'UTF-8');
}
$initials = mb_strtoupper(mb_substr($initials, 0, 2, 'UTF-8'), 'UTF-8');
if (empty($initials)) $initials = 'CL';

// KPIs & Statistiques
$totalCotise = 0;
foreach ($cotisations as $c) {
    $st = strtolower(trim($c['statut_cautisation_client'] ?? 'valide'));
    if ($st !== 'ennule' && $st !== 'annule') {
        $totalCotise += (float)($c['montant_cautisation_client'] ?? 0);
    }
}
$nbSouscriptions = count($souscriptions);
$nbCotisations = count($cotisations);
$dernierVersement = !empty($cotisations) ? $cotisations[0] : null;

$canCollect = Context::hasPermission('COMMERCIAL_COLLECT_COTISATION');
$canEdit = Context::hasPermission('GESTIONNAIRE_EDIT_SOUSCRIPTION');

// Numéro de téléphone nettoyé et lien WhatsApp direct
$rawTel = trim($item['telephone_client'] ?? '');
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

// Formatage du genre / sexe
$sexeRaw = strtolower(trim($item['sexe_client'] ?? ''));
$isFemme = in_array($sexeRaw, ['f', 'femme', 'féminin', 'feminin']);
$isHomme = in_array($sexeRaw, ['m', 'homme', 'masculin']);
if ($isFemme) {
    $sexeBadge = '<span style="background: #FDF2F8; color: #BE185D; border: 1px solid #FBCFE8; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;"><i data-lucide="user" style="width: 12px; height: 12px;"></i> Femme</span>';
} elseif ($isHomme) {
    $sexeBadge = '<span style="background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;"><i data-lucide="user" style="width: 12px; height: 12px;"></i> Homme</span>';
} else {
    $sexeBadge = '';
}

// Profession
$profession = trim($item['profession_client'] ?? '');

// Ancienneté du compte
$createdDate = !empty($item['created_at_client']) ? new DateTime($item['created_at_client']) : null;
$now = new DateTime();
if ($createdDate) {
    $interval = $createdDate->diff($now);
    if ($interval->y > 0) {
        $anciennete = "Client depuis {$interval->y} an" . ($interval->y > 1 ? 's' : '');
    } elseif ($interval->m > 0) {
        $anciennete = "Client depuis {$interval->m} mois";
    } elseif ($interval->d > 0) {
        $anciennete = "Client depuis {$interval->d} jour" . ($interval->d > 1 ? 's' : '');
    } else {
        $anciennete = "Inscrit aujourd'hui";
    }
    $dateCreationFmt = $createdDate->format('d/m/Y à H:i');
} else {
    $anciennete = '-';
    $dateCreationFmt = '-';
}

// Commercial référent & historique d'affectation
$historyCommercial = $historyCommercial ?? [];
$commerciaux = $commerciaux ?? [];
$activeCommercial = $activeCommercial ?? null;

$nomCommercial = trim(($item['commercial_nom'] ?? '') . ' ' . ($item['commercial_prenom'] ?? ''));
if (!empty($activeCommercial['nom_user'])) {
    $nomCommercial = trim($activeCommercial['nom_user'] . ' ' . ($activeCommercial['prenom_user'] ?? ''));
    $commercialMatricule = !empty($activeCommercial['matricule_user']) ? $activeCommercial['matricule_user'] : ($activeCommercial['commercial_code'] ?? '');
    $commercialTelephone = $activeCommercial['telephone_user'] ?? '';
} else {
    if (empty($nomCommercial)) {
        $nomCommercial = !empty($item['user_code']) ? $item['user_code'] : 'Non assigné';
    }
    $commercialMatricule = !empty($item['commercial_matricule']) ? $item['commercial_matricule'] : ($item['user_code'] ?? '');
    $commercialTelephone = $item['commercial_telephone'] ?? '';
}

$canTransfer = Context::can(['ADMIN_MANAGE_USERS', 'GESTIONNAIRE_VIEW_ALL_CLIENTS', 'GESTIONNAIRE_MANAGE_PACKS']);
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE LA FICHE CLIENT -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 16px;">
          <!-- Avatar initiales -->
          <div style="width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 800; box-shadow: 0 4px 14px rgba(30, 58, 95, 0.3); border: 2px solid #FFFFFF;">
            <?= htmlspecialchars($initials) ?>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              <?= htmlspecialchars($nomComplet) ?>
            </h1>
            <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px; flex-wrap: wrap;">
              <span style="font-size: 12px; font-weight: 700; color: #1E3A5F; background: #EEF2FF; padding: 3px 10px; border-radius: 6px; border: 1px solid #C7D2FE;">
                Code : <?= htmlspecialchars($item['code_client'] ?? '-') ?>
              </span>
              <span style="font-size: 12px; font-weight: 700; color: #047857; background: #ECFDF5; padding: 3px 10px; border-radius: 6px; border: 1px solid #A7F3D0;">
                Zone : <?= htmlspecialchars($item['libelle_zone'] ?? 'Non assignée') ?>
              </span>
              <?php if (($item['statut_client'] ?? 'actif') === 'actif'): ?>
                <span style="font-size: 12px; font-weight: 800; color: #059669; background: #D1FAE5; padding: 3px 10px; border-radius: 20px;">
                  ● Client Actif
                </span>
              <?php else: ?>
                <span style="font-size: 12px; font-weight: 800; color: #DC2626; background: #FEE2E2; padding: 3px 10px; border-radius: 20px;">
                  ● Inactif
                </span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>client/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour à la liste
          </a>
          <a href="<?= RACINE ?>client/edition/<?= $encryptedId ?>" class="btn" style="background: #F1F5F9; border: 1px solid #CBD5E1; color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none;">
            <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier le Profil
          </a>
          <a href="<?= RACINE ?>souscription/ressouscription?client_code=<?= urlencode($item['code_client'] ?? '') ?>" class="btn" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);">
            <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i> Ressouscription
          </a>
          <a href="<?= RACINE ?>souscription/wizard" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 20px; text-decoration: none; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Nouvelle Souscription
          </a>
          <?php if ($canTransfer): ?>
            <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#modal-transfer-commercial" style="background: #EFF6FF; border: 1.5px solid #BFDBFE; color: #1D4ED8; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; cursor: pointer; box-shadow: 0 1px 3px rgba(37,99,235,0.08);">
              <i data-lucide="user-plus" style="width: 16px; height: 16px; color: #1D4ED8;"></i> Réaffecter / Transférer
            </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- CARTE KPIS & SYNTHÈSE FINANCIÈRE -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- KPI 1 : Total cotisé -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 14px;">
          <div style="width: 46px; height: 46px; border-radius: 12px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="wallet" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Total Cotisé</span>
            <div style="font-size: 18px; font-weight: 800; color: #059669; margin-top: 2px;">
              <?= number_format($totalCotise, 0, ',', ' ') ?> <span style="font-size: 12px;">FCFA</span>
            </div>
          </div>
        </div>

        <!-- KPI 2 : Souscriptions -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 14px;">
          <div style="width: 46px; height: 46px; border-radius: 12px; background: #EEF2FF; color: #4F46E5; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="package" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Souscriptions</span>
            <div style="font-size: 18px; font-weight: 800; color: #1E3A5F; margin-top: 2px;">
              <?= $nbSouscriptions ?> <span style="font-size: 12px; font-weight: 600;">souscription<?= $nbSouscriptions > 1 ? 's' : '' ?></span>
            </div>
          </div>
        </div>

        <!-- KPI 3 : Versements effectués -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 14px;">
          <div style="width: 46px; height: 46px; border-radius: 12px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="receipt" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Versements Effectués</span>
            <div style="font-size: 18px; font-weight: 800; color: #2563EB; margin-top: 2px;">
              <?= $nbCotisations ?> <span style="font-size: 12px; font-weight: 600;">cotisation(s)</span>
            </div>
          </div>
        </div>

        <!-- KPI 4 : Dernier versement -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 14px;">
          <div style="width: 46px; height: 46px; border-radius: 12px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="calendar" style="width: 24px; height: 24px;"></i>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Dernier Versement</span>
            <div style="font-size: 14px; font-weight: 800; color: #0F172A; margin-top: 2px;">
              <?php if ($dernierVersement): ?>
                <?= date('d/m/Y', strtotime($dernierVersement['date_cautisation'] ?? $dernierVersement['created_at_cautisation_client'])) ?>
                <span style="color: #059669; font-size: 12px;">(<?= number_format((float)$dernierVersement['montant_cautisation_client'], 0, ',', ' ') ?> F)</span>
              <?php else: ?>
                <span style="color: #94A3B8; font-style: italic;">Aucun versement</span>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>

      <!-- CARTE 1 : SIGNALÉTIQUE & INFORMATIONS DU CLIENT -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 26px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #F1F5F9; flex-wrap: wrap; gap: 12px;">
          <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="user-check" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Informations & Coordonnées Client
          </h3>
          <span style="font-size: 12px; color: #64748B; font-weight: 600;">
            <i data-lucide="shield-check" style="width: 14px; height: 14px; color: #059669; display: inline-block; vertical-align: -2px;"></i> Fiche profil vérifiée
          </span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px;">

          <!-- BLOC 1 : IDENTITÉ & PROFIL -->
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
              <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Identité & Profil</span>
                <div style="width: 28px; height: 28px; border-radius: 8px; background: #EEF2FF; color: #4F46E5; display: flex; align-items: center; justify-content: center;">
                  <i data-lucide="user" style="width: 15px; height: 15px;"></i>
                </div>
              </div>
              <div style="font-size: 16px; font-weight: 800; color: #0F172A; line-height: 1.25; margin-bottom: 8px;">
                <?= htmlspecialchars($nomComplet) ?>
              </div>
              <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px;">
                <?= $sexeBadge ?>
                <?php if (!empty($profession)): ?>
                  <span style="background: #FFFFFF; color: #334155; border: 1px solid #CBD5E1; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                    <i data-lucide="briefcase" style="width: 11px; height: 11px; color: #64748B;"></i> <?= htmlspecialchars($profession) ?>
                  </span>
                <?php endif; ?>
              </div>
            </div>
            <div style="font-size: 12px; color: #475569; background: #FFFFFF; border: 1px solid #E2E8F0; padding: 6px 10px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between;">
              <span style="display: inline-flex; align-items: center; gap: 5px; color: #64748B; font-weight: 600;">
                <i data-lucide="credit-card" style="width: 13px; height: 13px; color: #1E3A5F;"></i> CNI :
              </span>
              <strong style="color: #0F172A; font-family: monospace; font-size: 12px;"><?= htmlspecialchars($item['cni_client'] ?? $item['numero_cni'] ?? '-') ?></strong>
            </div>
          </div>

          <!-- BLOC 2 : CONTACTS & ACTIONS RAPIDES -->
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
              <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Contacts & Joignabilité</span>
                <div style="width: 28px; height: 28px; border-radius: 8px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center;">
                  <i data-lucide="phone-call" style="width: 15px; height: 15px;"></i>
                </div>
              </div>
              <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; margin-bottom: 10px;">
                <a href="tel:<?= htmlspecialchars($cleanTel) ?>" style="font-size: 15px; font-weight: 800; color: #059669; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;" title="Cliquer pour appeler">
                  <i data-lucide="phone" style="width: 15px; height: 15px;"></i> <?= htmlspecialchars($item['telephone_client'] ?? '-') ?>
                </a>
                <?php if (!empty($waLink)): ?>
                  <a href="<?= $waLink ?>" target="_blank" rel="noopener noreferrer" style="background: #25D366; color: #FFFFFF; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 2px 4px rgba(37,211,102,0.25); transition: opacity 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'" title="Démarrer une discussion WhatsApp">
                    <i data-lucide="message-circle" style="width: 13px; height: 13px;"></i> WhatsApp
                  </a>
                <?php endif; ?>
              </div>
            </div>
            <div style="font-size: 12px; color: #475569; background: #FFFFFF; border: 1px solid #E2E8F0; padding: 6px 10px; border-radius: 8px;">
              <div style="display: flex; align-items: center; gap: 6px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                <i data-lucide="mail" style="width: 13px; height: 13px; color: #2563EB; flex-shrink: 0;"></i>
                <?php if (!empty($item['email_client'])): ?>
                  <a href="mailto:<?= htmlspecialchars($item['email_client']) ?>" style="color: #2563EB; font-weight: 600; text-decoration: none; overflow: hidden; text-overflow: ellipsis;" title="Envoyer un e-mail">
                    <?= htmlspecialchars($item['email_client']) ?>
                  </a>
                <?php else: ?>
                  <span style="color: #94A3B8; font-style: italic;">Email non renseigné</span>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- BLOC 3 : LOCALISATION GÉOGRAPHIQUE -->
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
              <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Localisation & Zone</span>
                <div style="width: 28px; height: 28px; border-radius: 8px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center;">
                  <i data-lucide="map-pin" style="width: 15px; height: 15px;"></i>
                </div>
              </div>
              <div style="margin-bottom: 10px;">
                <span style="background: #EFF6FF; color: #1D4ED8; font-size: 12px; font-weight: 800; padding: 4px 10px; border-radius: 6px; border: 1px solid #BFDBFE; display: inline-flex; align-items: center; gap: 5px;">
                  <i data-lucide="map-pin" style="width: 12px; height: 12px;"></i> <?= htmlspecialchars($item['libelle_zone'] ?? 'Zone Non assignée') ?>
                </span>
              </div>
            </div>
            <div style="font-size: 12px; color: #475569; background: #FFFFFF; border: 1px solid #E2E8F0; padding: 6px 10px; border-radius: 8px;">
              <div style="display: flex; align-items: center; gap: 6px;">
                <i data-lucide="home" style="width: 13px; height: 13px; color: #64748B; flex-shrink: 0;"></i>
                <span style="color: #64748B; font-weight: 600;">Résidence :</span>
                <strong style="color: #0F172A;"><?= htmlspecialchars($item['lieu_residence_client'] ?? $item['quartier_client'] ?? 'Non renseignée') ?></strong>
              </div>
            </div>
          </div>

          <!-- BLOC 4 : SUIVI COMMERCIAL & ENREGISTREMENT -->
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
              <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Gestion Commerciale</span>
                <div style="width: 28px; height: 28px; border-radius: 8px; background: #F0FDF4; color: #16A34A; display: flex; align-items: center; justify-content: center;">
                  <i data-lucide="briefcase" style="width: 15px; height: 15px;"></i>
                </div>
              </div>
              <div style="font-size: 14px; font-weight: 800; color: #1E3A5F; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                <i data-lucide="user-check" style="width: 15px; height: 15px; color: #059669;"></i> <?= htmlspecialchars($nomCommercial) ?>
              </div>
              <?php if (!empty($commercialTelephone)): ?>
                <div style="font-size: 12px; color: #059669; font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 5px;">
                  <i data-lucide="phone" style="width: 12px; height: 12px;"></i> <?= htmlspecialchars($commercialTelephone) ?>
                </div>
              <?php endif; ?>
              <?php if (!empty($commercialMatricule)): ?>
                <div style="margin-bottom: 8px;">
                  <span style="font-size: 11px; color: #64748B; background: #FFFFFF; border: 1px solid #E2E8F0; padding: 2px 7px; border-radius: 4px; font-family: monospace; font-weight: 700;">
                    Code : <?= htmlspecialchars($commercialMatricule) ?>
                  </span>
                </div>
              <?php endif; ?>
              <?php if ($canTransfer): ?>
                <button type="button" class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#modal-transfer-commercial" style="background: #EFF6FF; border: 1px solid #BFDBFE; color: #1D4ED8; font-weight: 700; font-size: 11.5px; border-radius: 8px; padding: 6px 10px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; width: 100%; margin-bottom: 10px; cursor: pointer; transition: all 0.2s ease;">
                  <i data-lucide="refresh-cw" style="width: 13px; height: 13px;"></i> Réaffecter le commercial
                </button>
              <?php endif; ?>
            </div>
            <div style="font-size: 11.5px; color: #475569; background: #FFFFFF; border: 1px solid #E2E8F0; padding: 6px 10px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between;">
              <span style="display: inline-flex; align-items: center; gap: 5px; color: #64748B; font-weight: 600;">
                <i data-lucide="calendar" style="width: 13px; height: 13px; color: #64748B;"></i> <?= $dateCreationFmt ?>
              </span>
              <span style="color: #059669; font-weight: 700; font-size: 11px;"><?= $anciennete ?></span>
            </div>
          </div>

        </div>
      </div>

      <!-- CARTE 2 : LISTE DES SOUSCRIPTIONS DE CE CLIENT -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 26px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); width: 100%; box-sizing: border-box; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #F1F5F9; flex-wrap: wrap; gap: 12px;">
          <div>
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="layers" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Souscriptions de Packs (<?= $nbSouscriptions ?>)
            </h3>
          </div>
         
        </div>

        <?php if (empty($souscriptions)): ?>
          <div style="text-align: center; padding: 40px 20px; color: #94A3B8;">
            <i data-lucide="package-open" style="width: 40px; height: 40px; margin-bottom: 8px; opacity: 0.5;"></i>
            <p style="font-size: 14px; margin: 0; font-style: italic;">Aucune souscription enregistrée pour ce client.</p>
          </div>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                  <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Code Souscription</th>
                  <th style="padding: 12px 14px; text-align: center; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Statut</th>
                  <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Session</th>
                  <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Cotis. / Jour</th>
                  <th style="padding: 12px 14px; text-align: right; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Total Souscription</th>
                  <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; min-width: 140px;">Progression</th>
                  <th style="padding: 12px 14px; text-align: right; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Total Cotisé</th>
                  <th style="padding: 12px 14px; text-align: right; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Reste à Payer</th>
                  <th style="padding: 12px 14px; text-align: center; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Date</th>
                  <th style="padding: 12px 14px; text-align: right; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($souscriptions as $s): ?>
                  <?php 
                    $statut = strtolower(trim($s['statut_souscription'] ?? 'valide'));
                    $badgeStyle = 'background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE;';
                    $libelleStatut = 'Validée';
                    if ($statut === 'solde' || $statut === 'soldé' || $statut === 'soldee') {
                        $badgeStyle = 'background: #DCFCE7; color: #15803D; border: 1px solid #BBF7D0;';
                        $libelleStatut = 'Soldée';
                    } elseif ($statut === 'annule' || $statut === 'annulé' || $statut === 'annulee') {
                        $badgeStyle = 'background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA;';
                        $libelleStatut = 'Annulée';
                    } elseif ($statut === 'reconduite') {
                        $badgeStyle = 'background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A;';
                        $libelleStatut = 'Reconduite';
                    }

                    $prixCotis = (float)($s['calculated_prix_cotisation'] ?? 0);
                    $totSous = (float)($s['calculated_total_souscription'] ?? 0);
                    $totCotise = (float)($s['calculated_total_cotise'] ?? 0);
                    $soldeRestant = (float)($s['calculated_solde_restant'] ?? 0);
                    $joursCotises = (int)($s['calculated_jours_cotises'] ?? 0);
                    $nbJours = (int)($s['calculated_nb_jours'] ?? 0);
                    $pct = (int)($s['calculated_progression'] ?? 0);
                    $progColor = $pct >= 100 ? '#10B981' : ($pct >= 50 ? '#F59E0B' : '#EF4444');
                    $dateSous = !empty($s['created_at_souscription']) ? date('d-m-Y', strtotime($s['created_at_souscription'])) : '-';
                    $encryptedSousId = $s['encrypted_id'] ?? '';
                  ?>
                  <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.2s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                    <!-- 1. Code Souscription -->
                    <td style="padding: 12px 14px;">
                      <span style="font-family: monospace; font-weight: 700; background: #F1F5F9; color: #1E3A5F; padding: 4px 8px; border-radius: 6px; border: 1px solid #CBD5E1;">
                        <?= htmlspecialchars($s['code_souscription']) ?>
                      </span>
                    </td>
                    <!-- 2. Statut -->
                    <td style="padding: 12px 14px; text-align: center;">
                      <span style="display: inline-block; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; <?= $badgeStyle ?>">
                        <?= $libelleStatut ?>
                      </span>
                    </td>
                    <!-- 3. Session -->
                    <td style="padding: 12px 14px; color: #334155; font-weight: 600;">
                      <?= htmlspecialchars($s['libelle_session'] ?? '-') ?>
                    </td>
                    <!-- 4. Cotis. / Jour -->
                    <td style="padding: 12px 14px;">
                      <span style="font-weight: 700; color: #2563EB;">
                        <?= number_format($prixCotis, 0, ',', ' ') ?> FCFA
                      </span>
                    </td>
                    <!-- 5. Total Souscription -->
                    <td style="padding: 12px 14px; text-align: right;">
                      <strong style="color: #1E3A5F;">
                        <?= number_format($totSous, 0, ',', ' ') ?> FCFA
                      </strong>
                    </td>
                    <!-- 6. Progression -->
                    <td style="padding: 12px 14px;">
                      <div style="min-width: 130px;">
                        <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 3px;">
                          <strong style="color: #334155;"><?= $joursCotises ?> / <?= $nbJours ?> j</strong>
                          <strong style="color: <?= $progColor ?>;"><?= $pct ?>%</strong>
                        </div>
                        <div class="progress" style="height: 6px; background: #E2E8F0; border-radius: 3px; overflow: hidden; margin: 0;">
                          <div class="progress-bar" style="width: <?= $pct ?>%; background: <?= $progColor ?>; height: 100%; border-radius: 3px;"></div>
                        </div>
                      </div>
                    </td>
                    <!-- 7. Total Cotisé -->
                    <td style="padding: 12px 14px; text-align: right;">
                      <strong style="color: #15803D;">
                        <?= number_format($totCotise, 0, ',', ' ') ?> FCFA
                      </strong>
                    </td>
                    <!-- 8. Reste à Payer -->
                    <td style="padding: 12px 14px; text-align: right;">
                      <?php if ($soldeRestant <= 0): ?>
                        <span style="color: #15803D; font-weight: 800; background: #DCFCE7; padding: 2px 8px; border-radius: 12px; font-size: 11px; border: 1px solid #BBF7D0;">Soldé</span>
                      <?php else: ?>
                        <strong style="color: #DC2626;"><?= number_format($soldeRestant, 0, ',', ' ') ?> FCFA</strong>
                      <?php endif; ?>
                    </td>
                    <!-- 9. Date -->
                    <td style="padding: 12px 14px; text-align: center; color: #64748B; font-weight: 600; font-size: 12px;">
                      <?= $dateSous ?>
                    </td>
                    <!-- 10. Actions -->
                    <td style="padding: 12px 14px; text-align: right;">
                      <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 4px;">
                        <?php if ($canCollect): ?>
                          <a href="<?= RACINE ?>cautisation-payment/situation/<?= htmlspecialchars($s['code_souscription']) ?>" class="btn btn-sm" style="background:#10B981; border-color:#10B981; color:#FFF; font-weight:700; border-radius:6px; padding:5px 10px;" title="Situation des versements">
                            <i data-lucide="credit-card" style="width:13px; height:13px; display:inline-block; vertical-align:middle;"></i> Situation
                          </a>
                        <?php endif; ?>
                        <?php if ($canEdit): ?>
                          <a href="<?= RACINE ?>souscription/edition/<?= $encryptedSousId ?>" class="btn btn-sm" style="background:#F1F5F9; border-color:#CBD5E1; color:#334155; font-weight:700; border-radius:6px; padding:5px 8px;" title="Éditer">
                            <i data-lucide="edit-3" style="width:13px; height:13px; display:inline-block; vertical-align:middle;"></i>
                          </a>
                        <?php endif; ?>
                        <a href="<?= RACINE ?>souscription/details/<?= $encryptedSousId ?>" class="btn btn-sm" style="background:#1E3A5F; border-color:#1E3A5F; color:#FFF; font-weight:700; border-radius:6px; padding:5px 8px;" title="Détails complets">
                          <i data-lucide="eye" style="width:13px; height:13px; display:inline-block; vertical-align:middle;"></i> Détails
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- CARTE 3 (NOUVEAU) : TABLEAU DES COTISATIONS DU CLIENT -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 26px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #F1F5F9; flex-wrap: wrap; gap: 12px;">
          <div>
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="receipt" style="width: 18px; height: 18px; color: #059669;"></i> Historique des Cotisations & Versements (<?= $nbCotisations ?>)
            </h3>
          </div>
        </div>

        <?php if (empty($cotisations)): ?>
          <div style="text-align: center; padding: 40px 20px; color: #94A3B8;">
            <i data-lucide="inbox" style="width: 40px; height: 40px; margin-bottom: 8px; opacity: 0.5;"></i>
            <p style="font-size: 14px; margin: 0; font-style: italic;">Aucune cotisation enregistrée pour ce client jusqu'à présent.</p>
          </div>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                  <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Date & Heure</th>
                  <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Code Cotisation</th>
                  <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Code Souscription</th>
                  <th style="padding: 12px 14px; text-align: right; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Montant Versé</th>
                  <th style="padding: 12px 14px; text-align: center; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Jours Payés</th>
                  <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Mode</th>
                  <th style="padding: 12px 14px; text-align: center; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Statut</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($cotisations as $c): ?>
                  <?php 
                    $modeLabel = strtolower(trim($c['mode_paiement'] ?? 'especes'));
                    $modeStyle = 'background: #ECFDF5; color: #047857;';
                    $modeIcon = '💵';
                    if (strpos($modeLabel, 'mobile') !== false) {
                        $modeStyle = 'background: #F3E8FF; color: #7E22CE;';
                        $modeIcon = '📱';
                    } else if (strpos($modeLabel, 'cheque') !== false || strpos($modeLabel, 'chèque') !== false) {
                        $modeStyle = 'background: #FEF3C7; color: #B45309;';
                        $modeIcon = '📝';
                    } else if (strpos($modeLabel, 'virement') !== false) {
                        $modeStyle = 'background: #EFF6FF; color: #1D4ED8;';
                        $modeIcon = '🏦';
                    }

                    $rawStatut = strtolower(trim($c['statut_cautisation_client'] ?? 'valide'));
                    $statutLabel = 'Validé';
                    $statutStyle = 'background:#ECFDF5; color:#047857; border: 1px solid #A7F3D0;';
                    
                    if ($rawStatut === 'en attente' || $rawStatut === 'en_attente') {
                        $statutLabel = 'En attente';
                        $statutStyle = 'background:#FEF3C7; color:#B45309; border: 1px solid #FDE68A;';
                    } else if ($rawStatut === 'annule' || $rawStatut === 'ennule') {
                        $statutLabel = 'Annulé';
                        $statutStyle = 'background:#FEE2E2; color:#B91C1C; border: 1px solid #FCA5A5;';
                    }

                    $dateDisplay = !empty($c['date_cautisation']) ? date('d/m/Y H:i', strtotime($c['date_cautisation'])) : (!empty($c['created_at_cautisation_client']) ? date('d/m/Y H:i', strtotime($c['created_at_cautisation_client'])) : '-');
                  ?>
                  <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.2s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 12px 14px; color: #0F172A; font-weight: 700;">
                      <?= htmlspecialchars($dateDisplay) ?>
                    </td>
                    <td style="padding: 12px 14px;">
                      <code style="font-weight: 800; color: #059669; background: #ECFDF5; padding: 4px 8px; border-radius: 6px; font-family: monospace;">
                        <?= htmlspecialchars($c['code_cautisation_client'] ?? '-') ?>
                      </code>
                    </td>
                    <td style="padding: 12px 14px;">
                      <?php if (!empty($c['souscription_code'] ?? $c['code_souscription'])): ?>
                        <a href="<?= RACINE ?>cautisation-payment/situation?code=<?= htmlspecialchars($c['souscription_code'] ?? $c['code_souscription']) ?>" style="color: #1E3A5F; font-weight: 700; text-decoration: none;">
                          <code style="background: #F1F5F9; padding: 4px 8px; border-radius: 6px; font-family: monospace;">
                            <?= htmlspecialchars($c['souscription_code'] ?? $c['code_souscription']) ?>
                          </code>
                        </a>
                      <?php else: ?>
                        -
                      <?php endif; ?>
                    </td>
                    <td style="padding: 12px 14px; text-align: right; font-weight: 800; color: #059669; font-size: 14px;">
                      <?= number_format((float)($c['montant_cautisation_client'] ?? 0), 0, ',', ' ') ?> FCFA
                    </td>
                    <td style="padding: 12px 14px; text-align: center;">
                      <span style="background:#EEF2FF; color:#4F46E5; padding:4px 10px; border-radius:8px; font-weight:800; font-size:12px; border: 1px solid #C7D2FE;">
                        <?= (int)($c['nombre_jour'] ?? $c['nombre_jour_paye'] ?? 0) ?> j
                      </span>
                    </td>
                    <td style="padding: 12px 14px;">
                      <span style="<?= $modeStyle ?> padding:4px 10px; border-radius:8px; font-weight:700; font-size:12px; display:inline-block;">
                        <?= $modeIcon ?> <?= ucfirst(htmlspecialchars(str_replace('_', ' ', $modeLabel))) ?>
                      </span>
                    </td>
                    <td style="padding: 12px 14px; text-align: center;">
                      <span class="badge" style="<?= $statutStyle ?> padding:5px 12px; border-radius:20px; font-weight:800; font-size:11px; display:inline-block;">
                        <?= $statutLabel ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- CARTE : HISTORIQUE DES AFFECTATIONS COMMERCIALES -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 26px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); width: 100%; box-sizing: border-box; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #F1F5F9; flex-wrap: wrap; gap: 12px;">
          <div>
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="history" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Historique des Affectations Commerciales (<?= count($historyCommercial) ?>)
            </h3>
            <p style="font-size: 12px; color: #64748B; margin: 4px 0 0 0;">
              Traçabilité des transferts de portefeuille. Les versements et commissions antérieurs demeurent strictement scellés au commercial de chaque période.
            </p>
          </div>
          <?php if ($canTransfer): ?>
            <button type="button" class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#modal-transfer-commercial" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 8px 14px; display: inline-flex; align-items: center; gap: 6px; border: none; cursor: pointer; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.2);">
              <i data-lucide="user-plus" style="width: 14px; height: 14px;"></i> Nouvelle Réaffectation
            </button>
          <?php endif; ?>
        </div>

        <?php if (empty($historyCommercial)): ?>
          <div style="text-align: center; padding: 36px 20px; color: #94A3B8; background: #F8FAFC; border-radius: 12px; border: 1px dashed #CBD5E1;">
            <i data-lucide="shield-check" style="width: 36px; height: 36px; margin-bottom: 8px; opacity: 0.6; color: #059669;"></i>
            <p style="font-size: 13.5px; font-weight: 600; color: #334155; margin: 0 0 4px 0;">Affectation initiale en cours</p>
            <p style="font-size: 12px; margin: 0; color: #64748B;">Ce client est actuellement géré par <?= htmlspecialchars($nomCommercial) ?> sans transfert ultérieur enregistré.</p>
          </div>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                  <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Commercial Responsable</th>
                  <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Période de Prise en Charge</th>
                  <th style="padding: 12px 14px; text-align: center; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Durée</th>
                  <th style="padding: 12px 14px; text-align: center; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Statut</th>
                  <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Zone</th>
                  <th style="padding: 12px 14px; text-align: left; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Enregistré par</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($historyCommercial as $hc): ?>
                  <?php
                    $isActif = (($hc['statut_client_commercial'] ?? 'actif') === 'actif');
                    $nomComm = trim(($hc['nom_commercial'] ?? '') . ' ' . ($hc['prenom_commercial'] ?? ''));
                    if (empty($nomComm)) $nomComm = $hc['commercial_code'] ?? 'Inconnu';

                    $dateDebut = !empty($hc['created_at_client_commercials']) ? new DateTime($hc['created_at_client_commercials']) : null;
                    $dateFin = (!empty($hc['updated_at_client_commercials']) && !$isActif) ? new DateTime($hc['updated_at_client_commercials']) : null;

                    // Calcul dynamique de la durée
                    $dureeTxt = '-';
                    if ($dateDebut) {
                        $refDate = $dateFin ?: new DateTime();
                        $diff = $dateDebut->diff($refDate);
                        if ($diff->y > 0) {
                            $dureeTxt = "{$diff->y} an(s)" . ($diff->m > 0 ? " {$diff->m} m" : '');
                        } elseif ($diff->m > 0) {
                            $dureeTxt = "{$diff->m} mois" . ($diff->d > 0 ? " {$diff->d} j" : '');
                        } elseif ($diff->d > 0) {
                            $dureeTxt = "{$diff->d} jour(s)";
                        } else {
                            $dureeTxt = "< 24h";
                        }
                    }

                    $nomAdmin = trim(($hc['nom_admin'] ?? '') . ' ' . ($hc['prenom_admin'] ?? ''));
                    if (empty($nomAdmin)) $nomAdmin = $hc['user_code'] ?? 'Système';
                  ?>
                  <tr style="border-bottom: 1px solid #F1F5F9; <?= $isActif ? 'background: #F0FDF4;' : '' ?> transition: background 0.2s;" onmouseover="this.style.background='<?= $isActif ? '#DCFCE7' : '#F8FAFC' ?>'" onmouseout="this.style.background='<?= $isActif ? '#F0FDF4' : 'transparent' ?>'">
                    <td style="padding: 12px 14px;">
                      <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 32px; height: 32px; border-radius: 8px; background: <?= $isActif ? '#DCFCE7; color: #15803D;' : '#F1F5F9; color: #64748B;' ?> display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px;">
                          <?= htmlspecialchars(mb_strtoupper(mb_substr($nomComm, 0, 2))) ?>
                        </div>
                        <div>
                          <strong style="color: #0F172A; font-size: 13px;"><?= htmlspecialchars($nomComm) ?></strong>
                          <div style="font-size: 11px; color: #64748B; display: flex; align-items: center; gap: 6px; margin-top: 1px;">
                            <?php if (!empty($hc['matricule_user'])): ?>
                              <span style="font-family: monospace; background: #FFFFFF; border: 1px solid #E2E8F0; padding: 1px 4px; border-radius: 4px;"><?= htmlspecialchars($hc['matricule_user']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($hc['telephone_commercial'])): ?>
                              <span><i data-lucide="phone" style="width: 10px; height: 10px; display: inline-block;"></i> <?= htmlspecialchars($hc['telephone_commercial']) ?></span>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td style="padding: 12px 14px;">
                      <div style="font-weight: 700; color: #1E3A5F; font-size: 12.5px;">
                        Du <?= $dateDebut ? $dateDebut->format('d/m/Y à H:i') : '-' ?>
                      </div>
                      <div style="font-size: 11px; color: <?= $isActif ? '#059669' : '#64748B' ?>; font-weight: 600; margin-top: 2px;">
                        <?= $isActif ? '● En cours d\'activité (Actif)' : 'Au ' . ($dateFin ? $dateFin->format('d/m/Y à H:i') : '-') ?>
                      </div>
                    </td>
                    <td style="padding: 12px 14px; text-align: center;">
                      <span style="background: #FFFFFF; border: 1px solid #E2E8F0; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 12px; color: #334155;">
                        <?= $dureeTxt ?>
                      </span>
                    </td>
                    <td style="padding: 12px 14px; text-align: center;">
                      <?php if ($isActif): ?>
                        <span class="badge" style="background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; padding: 4px 10px; border-radius: 20px; font-weight: 800; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                          <span style="width: 6px; height: 6px; border-radius: 50%; background: #059669; display: inline-block;"></span> Actif
                        </span>
                      <?php else: ?>
                        <span class="badge" style="background: #F1F5F9; color: #64748B; border: 1px solid #E2E8F0; padding: 4px 10px; border-radius: 20px; font-weight: 700; font-size: 11px;">
                          Clôturé
                        </span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 12px 14px; color: #475569; font-size: 12px;">
                      <span style="display: inline-flex; align-items: center; gap: 4px;">
                        <i data-lucide="map-pin" style="width: 12px; height: 12px; color: #2563EB;"></i>
                        <?= htmlspecialchars($hc['libelle_zone'] ?? $item['libelle_zone'] ?? 'Zone client') ?>
                      </span>
                    </td>
                    <td style="padding: 12px 14px; color: #334155; font-size: 12px;">
                      <div style="font-weight: 700; color: #1E293B;"><?= htmlspecialchars($nomAdmin) ?></div>
                      <span style="font-size: 11px; color: #94A3B8;">Opérateur système</span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<!-- MODAL DE TRANSFERT / RÉAFFECTATION DU CLIENT -->
<?php if ($canTransfer): ?>
<div class="modal-overlay" id="modal-transfer-commercial" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(5px); padding: 16px; box-sizing: border-box;">
  <div class="modal-card" style="max-width: 560px; width: 100%; max-height: calc(100vh - 40px); display: flex; flex-direction: column; background: #FFFFFF; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35); overflow: hidden; border: 1px solid #E2E8F0; margin: auto;">
    
    <!-- EN-TÊTE MODAL -->
    <div style="flex-shrink: 0; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
      <div style="display: flex; align-items: center; gap: 12px;">
        <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(255,255,255,0.12); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
          <i data-lucide="user-plus" style="width: 20px; height: 20px; color: #FFFFFF;"></i>
        </div>
        <div>
          <h4 style="font-weight: 800; font-size: 16px; margin: 0; color: #FFFFFF; line-height: 1.2;">
            Réaffectation Commerciale
          </h4>
          <span style="font-size: 11.5px; color: #94A3B8;">Transfert de portefeuille sans mélange financier</span>
        </div>
      </div>
      <button type="button" class="btn-close-modal" id="modal-transfer-close" style="background: rgba(255,255,255,0.12); border: none; color: #FFFFFF; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; transition: background 0.2s;" title="Fermer">
        <i data-lucide="x" style="width: 18px; height: 18px;"></i>
      </button>
    </div>

    <!-- CORPS DU MODAL -->
    <form id="form-transfer-commercial" method="POST" action="<?= RACINE ?>client/transferCommercial" style="margin: 0; display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="client_code" value="<?= htmlspecialchars($item['code_client'] ?? '') ?>">

      <div class="modal-body" style="padding: 24px; flex: 1; min-height: 0; overflow-y: auto;">

        <!-- AVERTISSEMENT DE SÉPARATION FINANCIÈRE -->
        <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-left: 4px solid #2563EB; border-radius: 10px; padding: 14px; margin-bottom: 20px;">
          <div style="display: flex; gap: 10px; align-items: flex-start;">
            <i data-lucide="shield-check" style="width: 20px; height: 20px; color: #2563EB; flex-shrink: 0; margin-top: 2px;"></i>
            <div style="font-size: 12px; color: #1E3A5F; line-height: 1.5;">
              <strong style="display: block; font-size: 12.5px; margin-bottom: 2px;">Règle d'Isolation des Versements & Commissions :</strong>
              Les cotisations et commissions antérieures de l'ancien commercial demeurent <strong>totalement scellées</strong>. Le nouveau commercial encaissera et percevra ses commissions uniquement à compter de ce transfert.
            </div>
          </div>
        </div>

        <!-- CLIENT CONCERNÉ -->
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 16px; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center;">
          <div>
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase;">Client concerné</span>
            <div style="font-size: 14px; font-weight: 800; color: #0F172A; margin-top: 2px;"><?= htmlspecialchars($nomComplet) ?></div>
            <div style="font-size: 11px; font-family: monospace; color: #2563EB; font-weight: 700; margin-top: 1px;"><?= htmlspecialchars($item['code_client'] ?? '-') ?></div>
          </div>
          <div style="text-align: right;">
            <span style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase;">Commercial Actuel</span>
            <div style="font-size: 13px; font-weight: 700; color: #059669; margin-top: 2px;"><?= htmlspecialchars($nomCommercial) ?></div>
          </div>
        </div>

        <!-- CHOIX DU NOUVEAU COMMERCIAL -->
        <div style="margin-bottom: 18px;">
          <label for="select-new-commercial" style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 6px;">
            Sélectionner le Nouveau Commercial Référent <span style="color: #DC2626;">*</span>
          </label>
          <select name="commercial_code" id="select-new-commercial" class="form-select" required style="width: 100%; border-radius: 8px; border: 1.5px solid #CBD5E1; padding: 11px 12px; font-size: 13.5px; font-weight: 600; background: #FFFFFF; color: #0F172A; box-sizing: border-box;">
            <option value="">-- Choisir un commercial --</option>
            <?php foreach ($commerciaux as $comm): ?>
              <?php 
                $cCode = $comm['code_user'];
                $cNom = trim($comm['nom_user'] . ' ' . ($comm['prenom_user'] ?? ''));
                $cTel = !empty($comm['telephone_user']) ? ' (' . $comm['telephone_user'] . ')' : '';
                $cCommPct = !empty($comm['commission']) ? ' - ' . $comm['commission'] . '%' : '';
                $isCurrent = ($cCode === ($activeCommercial['commercial_code'] ?? $item['user_code'] ?? ''));
              ?>
              <option value="<?= htmlspecialchars($cCode) ?>" <?= $isCurrent ? 'disabled' : '' ?>>
                <?= htmlspecialchars($cNom . $cTel . $cCommPct) ?><?= $isCurrent ? ' (Actuel)' : '' ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- MOTIF / COMMENTAIRE -->
        <div>
          <label for="input-motif-transfer" style="display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 6px;">
            Motif du transfert ou Note interne (Facultatif)
          </label>
          <input type="text" name="motif" id="input-motif-transfer" class="form-control" placeholder="Ex: Réorganisation de secteur, indisponibilité de l'ancien commercial..." style="width: 100%; border-radius: 8px; border: 1.5px solid #CBD5E1; padding: 11px 12px; font-size: 13px; background: #FFFFFF; color: #0F172A; box-sizing: border-box;">
        </div>

      </div>

      <!-- PIED DU MODAL -->
      <div class="modal-footer" style="flex-shrink: 0; background: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;">
        <button type="button" class="btn" id="btn-cancel-transfer" style="background: #FFFFFF; border: 1px solid #CBD5E1; color: #475569; font-weight: 700; border-radius: 8px; padding: 10px 18px; cursor: pointer; font-size: 13px;">
          Annuler
        </button>
        <button type="submit" id="btn-submit-transfer" class="btn" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 10px 22px; border: none; box-shadow: 0 4px 10px rgba(5, 150, 105, 0.25); display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px;">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Valider la Réaffectation
        </button>
      </div>

    </form>
  </div>
</div>
<?php endif; ?>

<script>
$(document).ready(function() { 
  if (window.lucide) lucide.createIcons(); 

  // Ouverture et fermeture modale de transfert
  $('[data-bs-target="#modal-transfer-commercial"], [data-target="#modal-transfer-commercial"]').on('click', function(e) {
    e.preventDefault();
    $('#modal-transfer-commercial').fadeIn(200).css('display', 'flex');
    if (window.lucide) lucide.createIcons();
  });

  $('#modal-transfer-close, #btn-cancel-transfer').on('click', function() {
    $('#modal-transfer-commercial').fadeOut(150);
  });

  $('#modal-transfer-commercial').on('click', function(e) {
    if ($(e.target).is('#modal-transfer-commercial')) {
      $(this).fadeOut(150);
    }
  });

  $(document).on('keydown', function(e) {
    if (e.key === 'Escape' && $('#modal-transfer-commercial').is(':visible')) {
      $('#modal-transfer-commercial').fadeOut(150);
    }
  });

  $('#form-transfer-commercial').on('submit', function(e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $('#btn-submit-transfer');
    const newComm = $('#select-new-commercial').val();

    if (!newComm) {
      if (window.Swal) {
        Swal.fire('Commercial requis', 'Veuillez sélectionner un commercial dans la liste.', 'warning');
      } else {
        alert('Veuillez sélectionner un commercial.');
      }
      return;
    }

    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Transfert en cours...');

    $.ajax({
      url: $form.attr('action'),
      method: 'POST',
      data: $form.serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.status === 'success' || res.success) {
          if (window.Swal) {
            Swal.fire({
              icon: 'success',
              title: 'Réaffectation réussie !',
              text: res.message || 'Le client a été transféré avec succès au nouveau commercial.',
              confirmButtonColor: '#059669'
            }).then(function() {
              window.location.reload();
            });
          } else {
            alert(res.message || 'Transfert effectué avec succès.');
            window.location.reload();
          }
        } else {
          $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Valider la Réaffectation');
          if (window.lucide) lucide.createIcons();
          if (window.Swal) {
            Swal.fire('Erreur', res.message || 'Une erreur est survenue lors du transfert.', 'error');
          } else {
            alert(res.message || 'Erreur lors du transfert.');
          }
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i data-lucide="check" style="width: 16px; height: 16px;"></i> Valider la Réaffectation');
        if (window.lucide) lucide.createIcons();
        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Une erreur de communication est survenue.';
        if (window.Swal) {
          Swal.fire('Erreur système', msg, 'error');
        } else {
          alert(msg);
        }
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>

