<?php
require_once __DIR__ . '/../../public/inc/header.php';
$pressing = isset($pressing) ? $pressing : [];
$isEdit = !empty($pressing['id_pressing']);
$villes = isset($villes) ? $villes : [];
$quartiers = isset($quartiers) ? $quartiers : [];
$forfaits = isset($forfaits) ? $forfaits : [];
?>

<style>
/* === MOBILE PWA UX OPTIMIZATIONS FOR PRESSING ONBOARDING & EDIT FORM === */
@media (max-width: 768px) {
  .content-wrapper {
    padding: 12px 10px 80px 10px !important;
  }
  .page-header {
    flex-direction: column !important;
    align-items: stretch !important;
    margin-bottom: 16px !important;
    gap: 10px !important;
  }
  .page-header .btn {
    width: 100% !important;
    justify-content: center !important;
    height: 44px !important;
  }
  .form-grid {
    grid-template-columns: 1fr !important;
    gap: 12px !important;
  }
  .card-body {
    padding: 16px 12px !important;
  }
  .content-wrapper {
    padding: 12px 10px 80px 10px !important;
    max-width: 100vw !important;
    box-sizing: border-box !important;
  }
  .form-control, .input-with-icon {
    max-width: 100% !important;
    box-sizing: border-box !important;
  }
  .form-actions {
    flex-direction: column !important;
    gap: 10px !important;
  }
  .form-actions .btn {
    width: 100% !important;
    height: 48px !important;
    font-size: 15px !important;
    justify-content: center !important;
  }
  .wizard-step-item span {
    font-size: 11px !important;
  }
}
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header" style="margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main, #1e293b); margin: 0; display: flex; align-items: center; gap: 8px;">
            <?php if ($isEdit): ?>
              <i data-lucide="edit-3" style="color: #2563eb;"></i> Modifier le pressing
            <?php else: ?>
              <i data-lucide="rocket" style="color: #2563eb;"></i> Nouveau Pressing - Onboarding Tout-en-Un
            <?php endif; ?>
          </h1>
          <p class="page-subtitle" style="color: #64748b; margin-top: 4px;">
            <?= $isEdit ? 'Mise à jour des coordonnées' : 'Création simultanée de l\'établissement, du compte gérant et du forfait B2B' ?>
          </p>
        </div>
        <a href="<?= RACINE ?>pressing/list" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; padding: 8px 16px;">
          <i data-lucide="arrow-left"></i> Retour à la liste
        </a>
      </div>

      <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: hidden;">
        <?php if (!$isEdit): ?>
        <!-- WIZARD STEP HEADER -->
        <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 20px 24px;">
          <div style="display: flex; justify-content: space-between; align-items: center; max-width: 700px; margin: 0 auto; position: relative;">
            <div style="position: absolute; top: 18px; left: 10%; right: 10%; height: 3px; background: #e2e8f0; z-index: 1;"></div>
            
            <div class="wizard-step-item active" id="step-btn-1" style="z-index: 2; text-align: center; cursor: pointer;" onclick="goToStep(1)">
              <div style="width: 38px; height: 38px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; margin: 0 auto 6px; box-shadow: 0 0 0 4px #dbeafe;">1</div>
              <span style="font-size: 13px; font-weight: 600; color: #1e293b; display: inline-flex; align-items: center; gap: 4px;">
                <i data-lucide="building-2" style="width: 14px; height: 14px;"></i> 1. Pressing
              </span>
            </div>

            <div class="wizard-step-item" id="step-btn-2" style="z-index: 2; text-align: center; cursor: pointer;" onclick="goToStep(2)">
              <div style="width: 38px; height: 38px; border-radius: 50%; background: #e2e8f0; color: #64748b; display: flex; align-items: center; justify-content: center; font-weight: 700; margin: 0 auto 6px;">2</div>
              <span style="font-size: 13px; font-weight: 600; color: #64748b; display: inline-flex; align-items: center; gap: 4px;">
                <i data-lucide="user-check" style="width: 14px; height: 14px;"></i> 2. Gérant (1er User)
              </span>
            </div>

            <div class="wizard-step-item" id="step-btn-3" style="z-index: 2; text-align: center; cursor: pointer;" onclick="goToStep(3)">
              <div style="width: 38px; height: 38px; border-radius: 50%; background: #e2e8f0; color: #64748b; display: flex; align-items: center; justify-content: center; font-weight: 700; margin: 0 auto 6px;">3</div>
              <span style="font-size: 13px; font-weight: 600; color: #64748b; display: inline-flex; align-items: center; gap: 4px;">
                <i data-lucide="credit-card" style="width: 14px; height: 14px;"></i> 3. Forfait B2B
              </span>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <div class="card-body" style="padding: 32px;">
          <form class="formEditPressing" id="onboardingForm" novalidate>
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_pressing" name="id_pressing" value="<?= htmlspecialchars($pressing['id_pressing'] ?? '') ?>">

            <!-- SECTION 1 : PRESSING -->
            <div class="wizard-section" id="wizard-step-1">
              <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="building-2" style="color: #2563eb;"></i> Informations de l'Établissement
              </h3>

              <div class="form-grid">
                <div class="form-field">
                  <label for="libelle_pressing">Nom du Pressing <span style="color:#ef4444;">*</span></label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('store'); ?></span>
                    <input type="text" class="form-control" id="libelle_pressing" name="libelle_pressing"
                           placeholder="ex: Pressing Riviera Palmeraie" value="<?= htmlspecialchars($pressing['libelle_pressing'] ?? '') ?>" required>
                  </div>
                </div>

                <div class="form-field">
                  <label for="telephone_pressing">Téléphone Atelier / Service Client</label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('phone'); ?></span>
                    <input type="text" class="form-control" id="telephone_pressing" name="telephone_pressing"
                           placeholder="ex: 0707070707" value="<?= htmlspecialchars($pressing['telephone_pressing'] ?? '') ?>">
                  </div>
                </div>

                <div class="form-field">
                  <label for="email_pressing">Email Officiel du Pressing</label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('mail'); ?></span>
                    <input type="email" class="form-control" id="email_pressing" name="email_pressing"
                           placeholder="contact@pressing.ci" value="<?= htmlspecialchars($pressing['email_pressing'] ?? '') ?>">
                  </div>
                </div>

                <div class="form-field">
                  <label for="ville_code">Ville</label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('map-pin'); ?></span>
                    <select class="form-control" id="ville_code" name="ville_code">
                      <option value="">Sélectionner une ville</option>
                      <?php foreach ($villes as $v): ?>
                        <option value="<?= $v['code_ville'] ?>" <?= ($pressing['ville_code'] ?? '') == $v['code_ville'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($v['libelle_ville']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <div class="form-field">
                  <label for="quartier_code" style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Quartier</span>
                    <span style="font-size: 11px; color: #2563EB; font-weight: 600;"><i data-lucide="search" style="width: 12px; height: 12px; vertical-align: -1px;"></i> Recherche rapide</span>
                  </label>
                  <div style="position: relative;">
                    <input type="text" id="quartier_search" class="form-control" placeholder="Rechercher un quartier (ex: Koko, Nimbo, Air France...)" autocomplete="off" style="margin-bottom: 6px; font-size: 13px; border-radius: 8px; border: 1.5px solid #2563eb; background: #f0f6ff;">
                    <div class="input-with-icon">
                      <span class="input-icon"><?= Validator::icon('map'); ?></span>
                      <select class="form-control" id="quartier_code" name="quartier_code">
                        <option value="">Sélectionner un quartier</option>
                        <?php foreach ($quartiers as $q): ?>
                          <option value="<?= $q['code_quartier'] ?>" data-ville="<?= $q['ville_code'] ?>" <?= ($pressing['quartier_code'] ?? '') == $q['code_quartier'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($q['libelle_quartier']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <small style="color: #64748B; font-size: 11px; display: block; margin-top: 4px;">Tapez les premières lettres du quartier pour filtrer la liste en temps réel.</small>
                </div>

                <div class="form-field" style="grid-column: 1 / -1;">
                  <label for="adresse_pressing" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                    <span>Adresse / Localisation précise</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="getCurrentGpsLocation()" style="font-size: 11px; padding: 4px 10px; border-radius: 6px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                      <i data-lucide="crosshair" style="width: 13px; height: 13px;"></i> Detecter ma position GPS
                    </button>
                  </label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('navigation'); ?></span>
                    <textarea class="form-control" id="adresse_pressing" name="adresse_pressing" rows="2" placeholder="Rue, Carrefour, Repère..."><?= htmlspecialchars($pressing['adresse_pressing'] ?? '') ?></textarea>
                  </div>
                  <input type="hidden" id="latitude_pressing" name="latitude_pressing" value="<?= htmlspecialchars($pressing['latitude_pressing'] ?? '') ?>">
                  <input type="hidden" id="longitude_pressing" name="longitude_pressing" value="<?= htmlspecialchars($pressing['longitude_pressing'] ?? '') ?>">
                  <small id="gps-status-info" style="color: #64748B; font-size: 11px; display: block; margin-top: 4px;">
                    <?= (!empty($pressing['latitude_pressing']) && !empty($pressing['longitude_pressing'])) ? '📍 Coordonnées GPS enregistrées : ' . htmlspecialchars($pressing['latitude_pressing']) . ', ' . htmlspecialchars($pressing['longitude_pressing']) : 'Si non renseigné, la position GPS (latitude/longitude) sera automatiquement récupérée par le serveur à l\'enregistrement.' ?>
                  </small>
                </div>

                <div style="grid-column: 1 / -1; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                  <!-- CHAMP FICHIER : LOGO DU PRESSING -->
                  <div class="form-field">
                    <label for="logo_file" style="font-weight: 700; font-size: 13px; color: #1E293B; margin-bottom: 6px; display: block;">
                      Logo Officiel du Pressing (Icône Rond/Carré)
                    </label>
                    <div style="display: flex; gap: 14px; align-items: flex-start;">
                      <div id="logoPreviewWrapper" style="width: 70px; height: 70px; border-radius: 12px; border: 2px dashed #CBD5E1; background: #F8FAFC; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; position: relative;">
                        <?php 
                          $currentLogo = $pressing['logo_pressing'] ?? '';
                          $hasLogo = !empty($currentLogo);
                          $logoUrl = $hasLogo ? ((strpos($currentLogo, 'http') === 0) ? $currentLogo : RACINE . 'public/assets/images/pressings/' . $currentLogo) : '';
                        ?>
                        <?php if ($hasLogo): ?>
                          <img id="logoPreviewImg" src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo actuel" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                          <img id="logoPreviewImg" src="" alt="Aperçu Logo" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                          <i id="logoPlaceholderIcon" class="fa fa-building" style="font-size: 24px; color: #94A3B8;"></i>
                        <?php endif; ?>
                      </div>
                      <div style="flex: 1;">
                        <div class="input-with-icon">
                          <span class="input-icon"><?= Validator::icon('image'); ?></span>
                          <input type="file" class="form-control" id="logo_file" name="logo_file" accept="image/png,image/jpeg,image/webp,image/svg+xml,image/gif" onchange="previewPressingLogo(this)">
                        </div>
                        <small style="color: #64748B; font-size: 11px; display: block; margin-top: 4px;">Logo/Insigne officiel (PNG, JPG, WEBP, SVG)</small>
                      </div>
                    </div>
                  </div>

                  <!-- CHAMP FICHIER : MINIATURE / COUVERTURE DU PRESSING -->
                  <div class="form-field">
                    <label for="miniature_file" style="font-weight: 700; font-size: 13px; color: #1E293B; margin-bottom: 6px; display: block;">
                      Miniature / Photo de Couverture (Affiche Lavex) <i data-lucide="star" style="width: 14px; height: 14px; color: #2563EB; vertical-align: -1px; display: inline;"></i>
                    </label>
                    <div style="display: flex; gap: 14px; align-items: flex-start;">
                      <div id="miniaturePreviewWrapper" style="width: 110px; height: 70px; border-radius: 12px; border: 2px dashed #CBD5E1; background: #F8FAFC; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; position: relative;">
                        <?php 
                          $currentMiniature = $pressing['miniature_pressing'] ?? '';
                          $hasMiniature = !empty($currentMiniature);
                          $miniatureUrl = $hasMiniature ? ((strpos($currentMiniature, 'http') === 0) ? $currentMiniature : RACINE . 'public/assets/images/pressings/' . $currentMiniature) : '';
                        ?>
                        <?php if ($hasMiniature): ?>
                          <img id="miniaturePreviewImg" src="<?= htmlspecialchars($miniatureUrl) ?>" alt="Miniature actuelle" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                          <img id="miniaturePreviewImg" src="" alt="Aperçu Miniature" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                          <i id="miniaturePlaceholderIcon" class="fa fa-camera" style="font-size: 24px; color: #94A3B8;"></i>
                        <?php endif; ?>
                      </div>
                      <div style="flex: 1;">
                        <div class="input-with-icon">
                          <span class="input-icon"><?= Validator::icon('image'); ?></span>
                          <input type="file" class="form-control" id="miniature_file" name="miniature_file" accept="image/png,image/jpeg,image/webp,image/svg+xml,image/gif" onchange="previewPressingMiniature(this)">
                        </div>
                        <small style="color: #2563EB; font-size: 11px; display: block; margin-top: 4px; font-weight: 600;">Image principale affichée sur la plateforme Lavex</small>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- SECTION LIVRAISON & COLLECTE AU SAC -->
                <div class="form-field" style="grid-column: 1 / -1; margin-top: 10px; padding-top: 16px; border-top: 1px dashed #E2E8F0;">
                  <h4 style="font-size: 14px; font-weight: 700; color: #1E293B; margin: 0 0 14px 0; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="truck" style="width: 16px; height: 16px; color: #2563EB;"></i> Configuration Livraison & Collecte au Sac
                  </h4>
                  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
                    
                    <div class="form-field" style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 14px 16px; border-radius: 10px; grid-column: 1 / -1;">
                      <label for="mode_logistique" style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
                        Mode de Gestion Logistique (Qui assure la livraison ?)
                      </label>
                      <div class="input-with-icon">
                        <span class="input-icon"><?= Validator::icon('truck'); ?></span>
                        <select class="form-control" id="mode_logistique" name="mode_logistique" onchange="toggleLogisticsFields(this.value)">
                          <option value="pressing" <?= ($pressing['mode_logistique'] ?? 'pressing') === 'pressing' ? 'selected' : '' ?>>
                            Livraison assurée par le Pressing (Ses propres livreurs et tarifs)
                          </option>
                          <option value="lavex" <?= ($pressing['mode_logistique'] ?? '') === 'lavex' ? 'selected' : '' ?>>
                            Livraison assurée par la Flotte Officielle Lavex
                          </option>
                          <option value="retrait_uniquement" <?= ($pressing['mode_logistique'] ?? '') === 'retrait_uniquement' ? 'selected' : '' ?>>
                            Retrait en Atelier Uniquement (Pas de service de livraison)
                          </option>
                        </select>
                      </div>
                      <small style="color: #64748B; font-size: 11px; display: block; margin-top: 6px;">
                        Permet de définir si ce pressing utilise ses propres livreurs ou la flotte Lavex.
                      </small>
                    </div>

                    <div class="form-field mode-pressing-field" id="container_frais_collecte" style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 14px 16px; border-radius: 10px;">
                      <label for="frais_collecte_pressing" style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
                        Frais de Collecte du Pressing (FCFA)
                      </label>
                      <div class="input-with-icon">
                        <span class="input-icon"><?= Validator::icon('dollar-sign'); ?></span>
                        <input type="number" class="form-control" id="frais_collecte_pressing" name="frais_collecte_pressing"
                               placeholder="ex: 1000" min="0" step="100" value="<?= htmlspecialchars($pressing['frais_collecte_pressing'] ?? '1000') ?>">
                      </div>
                      <small style="color: #64748B; font-size: 11px; display: block; margin-top: 6px;">
                        Fixé par le pressing pour sa propre flotte de collecte.
                      </small>
                    </div>

                    <div class="form-field mode-pressing-field" id="container_frais_livraison" style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 14px 16px; border-radius: 10px;">
                      <label for="frais_livraison_pressing" style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
                        Frais de Livraison du Pressing (FCFA)
                      </label>
                      <div class="input-with-icon">
                        <span class="input-icon"><?= Validator::icon('dollar-sign'); ?></span>
                        <input type="number" class="form-control" id="frais_livraison_pressing" name="frais_livraison_pressing"
                               placeholder="ex: 1000" min="0" step="100" value="<?= htmlspecialchars($pressing['frais_livraison_pressing'] ?? '1000') ?>">
                      </div>
                      <small style="color: #64748B; font-size: 11px; display: block; margin-top: 6px;">
                        Fixé par le pressing pour sa propre flotte de livraison.
                      </small>
                    </div>

                    <div class="form-field" id="container_notice_lavex" style="background: #EFF6FF; border: 1px solid #BFDBFE; padding: 14px 16px; border-radius: 10px; display: none; grid-column: 1 / -1;">
                      <div style="font-size: 12.5px; color: #1E40AF; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="shield-check" style="width: 18px; height: 18px; color: #2563EB;"></i>
                        <span>Flotte Officielle Lavex active : La collecte et la livraison sont gérées par Lavex aux tarifs standards de la plateforme (1 000 FCFA / trajet).</span>
                      </div>
                    </div>

                    <div class="form-field" id="container_notice_retrait" style="background: #F3F4F6; border: 1px solid #E5E7EB; padding: 14px 16px; border-radius: 10px; display: none; grid-column: 1 / -1;">
                      <div style="font-size: 12.5px; color: #4B5563; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="store" style="width: 18px; height: 18px; color: #6B7280;"></i>
                        <span>Retrait atelier uniquement : Aucun frais de transport n'est appliqué aux commandes de ce pressing.</span>
                      </div>
                    </div>

                    <?php if (!empty($isSuperAdmin)): ?>
                    <div class="form-field" id="container_taux_commission" style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 14px 16px; border-radius: 10px;">
                      <label for="taux_commission_pressing" style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
                        Taux de Commission Partenaire (%)
                      </label>
                      <div class="input-with-icon">
                        <span class="input-icon"><?= Validator::icon('percent'); ?></span>
                        <input type="number" class="form-control" id="taux_commission_pressing" name="taux_commission_pressing"
                               placeholder="ex: 0.00 ou 10.00" min="0" max="100" step="0.5" value="<?= htmlspecialchars($pressing['taux_commission_pressing'] ?? '0.00') ?>">
                      </div>
                      <small style="color: #64748B; font-size: 11px; display: block; margin-top: 6px;">
                        Pourcentage prélevé par Lavex (Fixé par le Super Admin, par défaut à 0.00% au lancement).
                      </small>
                    </div>
                    <?php endif; ?>

                    <div class="form-field mode-pressing-field" id="container_propose_livraison" style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 14px 16px; border-radius: 10px;">
                      <label style="font-weight: 700; font-size: 13px; color: #1E293B; display: flex; align-items: center; justify-content: space-between; cursor: pointer; margin: 0;">
                        <span>Proposer le Service de Livraison</span>
                        <input type="checkbox" id="propose_livraison" name="propose_livraison" value="1" <?= (!isset($pressing['propose_livraison']) || !empty($pressing['propose_livraison'])) ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: #2563EB;">
                      </label>
                      <small style="color: #64748B; font-size: 11px; display: block; margin-top: 6px;">
                        Activer la collecte et la livraison à domicile pour ce pressing.
                      </small>
                    </div>

                    <div class="form-field mode-pressing-field" id="container_livraison_gratuite" style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 14px 16px; border-radius: 10px;">
                      <label style="font-weight: 700; font-size: 13px; color: #1E293B; display: flex; align-items: center; justify-content: space-between; cursor: pointer; margin: 0;">
                        <span>Proposer la Livraison Gratuite</span>
                        <input type="checkbox" id="livraison_gratuite" name="livraison_gratuite" value="1" <?= (!empty($pressing['livraison_gratuite'])) ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: #2563EB;">
                      </label>
                      <small style="color: #64748B; font-size: 11px; display: block; margin-top: 6px;">
                        Offre la livraison au client si le seuil ci-dessous est atteint.
                      </small>
                    </div>

                    <div class="form-field mode-pressing-field" id="container_seuil_livraison_gratuite" style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 14px 16px; border-radius: 10px;">
                      <label for="seuil_livraison_gratuite" style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
                        Seuil Minimum Livraison Gratuite (FCFA)
                      </label>
                      <div class="input-with-icon">
                        <span class="input-icon"><?= Validator::icon('dollar-sign'); ?></span>
                        <input type="number" class="form-control" id="seuil_livraison_gratuite" name="seuil_livraison_gratuite"
                               placeholder="ex: 15000" min="0" step="500" value="<?= htmlspecialchars($pressing['seuil_livraison_gratuite'] ?? '0') ?>">
                      </div>
                    </div>

                    <div class="form-field mode-pressing-field" id="container_delai_livraison" style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 14px 16px; border-radius: 10px;">
                      <label for="delai_livraison_pressing" style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
                        Temps / Délais Moyen de Livraison
                      </label>
                      <div class="input-with-icon">
                        <span class="input-icon"><?= Validator::icon('clock'); ?></span>
                        <input type="text" class="form-control" id="delai_livraison_pressing" name="delai_livraison_pressing"
                               placeholder="ex: 24h - 48h, Express 12h" value="<?= htmlspecialchars($pressing['delai_livraison_pressing'] ?? '24h - 48h') ?>">
                      </div>
                      <small style="color: #64748B; font-size: 11px; display: block; margin-top: 6px;">
                        Affiché sur l'application Lavex pour informer les clients (ex: 24h - 48h).
                      </small>
                    </div>

                    <div class="form-field" style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 14px 16px; border-radius: 10px;">
                      <label style="font-weight: 700; font-size: 13px; color: #1E293B; display: flex; align-items: center; justify-content: space-between; cursor: pointer; margin: 0;">
                        <span>Accepter la Collecte au Sac (Sans Détail)</span>
                        <input type="checkbox" id="accepte_colis_sans_detail" name="accepte_colis_sans_detail" value="1" <?= (!isset($pressing['accepte_colis_sans_detail']) || !empty($pressing['accepte_colis_sans_detail'])) ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: #2563EB;">
                      </label>
                      <small style="color: #64748B; font-size: 11px; display: block; margin-top: 6px;">
                        Permet de confier des sacs sans détail d'articles à la réservation.
                      </small>
                    </div>

                  </div>
                </div>
              </div>

              <?php if (!$isEdit): ?>
              <div style="text-align: right; margin-top: 24px;">
                <button type="button" class="btn btn-primary" onclick="goToStep(2)" style="border-radius: 10px; padding: 10px 24px; font-weight: 600; background: #1E3A5F; border-color: #1E3A5F;">
                  Étape Suivante (Compte Gérant) <i data-lucide="arrow-right"></i>
                </button>
              </div>
              <?php endif; ?>
            </div>

            <?php if (!$isEdit): ?>
            <!-- SECTION 2 : GERANT (1ER USER) -->
            <div class="wizard-section" id="wizard-step-2" style="display: none;">
              <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="user-check" style="color: #2563eb;"></i> Compte Gérant / Propriétaire (1er Administrateur du Pressing)
              </h3>
              
              <div class="alert alert-info" style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; font-size: 13px;">
                <i data-lucide="info" style="vertical-align: -2px; margin-right: 6px;"></i>
                Ces accès permettront au propriétaire du pressing de se connecter immédiatement à son tableau de bord d'administration <strong>admin-lavex</strong>.
              </div>

              <div class="form-grid">
                <div class="form-field">
                  <label for="nom_user">Nom complet du Gérant <span style="color:#ef4444;">*</span></label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('user'); ?></span>
                    <input type="text" class="form-control" id="nom_user" name="nom_user" placeholder="ex: Kouassi Jean" required>
                  </div>
                </div>

                <div class="form-field">
                  <label for="prenom_user">Prénom(s)</label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('user'); ?></span>
                    <input type="text" class="form-control" id="prenom_user" name="prenom_user" placeholder="ex: Marc">
                  </div>
                </div>

                <div class="form-field">
                  <label for="email_user">Email de Connexion (Login Admin) <span style="color:#ef4444;">*</span></label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('mail'); ?></span>
                    <input type="email" class="form-control" id="email_user" name="email_user" placeholder="gerant@pressing.ci" required>
                  </div>
                </div>

                <div class="form-field">
                  <label for="password_user">Mot de passe de connexion initial <span style="color:#ef4444;">*</span></label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('lock'); ?></span>
                    <input type="text" class="form-control" id="password_user" name="password_user" value="12345" readonly style="background-color: #f1f5f9; color: #475569; cursor: not-allowed; font-weight: 700;">
                  </div>
                  <small style="color: #64748B; font-size: 11px; display: block; margin-top: 4px;">Mot de passe par défaut généré automatiquement pour le premier accès (12345).</small>
                </div>

                <div class="form-field">
                  <label for="telephone_user">Téléphone Personnel / Mobile Money</label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('smartphone'); ?></span>
                    <input type="text" class="form-control" id="telephone_user" name="telephone_user" placeholder="0505050505">
                  </div>
                </div>
              </div>

              <div style="display: flex; justify-content: space-between; margin-top: 24px;">
                <button type="button" class="btn btn-outline-secondary" onclick="goToStep(1)" style="border-radius: 10px; padding: 10px 24px;">
                  <i data-lucide="arrow-left"></i> Précédent
                </button>
                <button type="button" class="btn btn-primary" onclick="goToStep(3)" style="border-radius: 10px; padding: 10px 24px; font-weight: 600; background: #1E3A5F; border-color: #1E3A5F;">
                  Étape Suivante (Forfait B2B) <i data-lucide="arrow-right"></i>
                </button>
              </div>
            </div>

            <!-- SECTION 3 : FORFAIT B2B -->
            <div class="wizard-section" id="wizard-step-3" style="display: none;">
              <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="credit-card" style="color: #2563eb;"></i> Choix du Forfait B2B & Abonnement Initial
              </h3>

              <div class="forfaits-grid-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 18px; margin-bottom: 24px;">
                <?php foreach ($forfaits as $idx => $f): ?>
                  <?php 
                    $isSelected = ($idx === 0); 
                    $isGratuit = ((float)$f['montant_forfait'] == 0);
                    $badgeText = $isGratuit ? 'Essai Gratuit' : ((float)$f['montant_forfait'] >= 50000 ? 'Formule Premium' : ((float)$f['montant_forfait'] >= 30000 ? 'Recommandé' : 'Standard'));
                    $badgeBg = $isSelected ? '#0F766E' : '#64748B';

                    $advList = [];
                    if (!empty($f['avantages_forfait'])) {
                        $decoded = json_decode($f['avantages_forfait'], true);
                        if (is_array($decoded)) {
                            $advList = $decoded;
                        } else {
                            $advList = array_values(array_filter(array_map('trim', explode("\n", $f['avantages_forfait']))));
                        }
                    }
                  ?>
                  <div class="forfait-card-box <?= $isSelected ? 'selected' : '' ?>" 
                       id="forfait-card-<?= $f['code_forfait'] ?>"
                       onclick="selectForfaitCard('<?= $f['code_forfait'] ?>', <?= (float)$f['montant_forfait'] ?>, <?= (int)$f['duree_mois_forfait'] ?>)"
                       style="position: relative; border: 1.5px solid <?= $isSelected ? '#0F766E' : '#E2E8F0' ?>; background: #FFFFFF; border-radius: 14px; padding: 20px; cursor: pointer; transition: all 0.2s ease; display: flex; flex-direction: column; justify-content: space-between; box-shadow: <?= $isSelected ? '0 4px 16px rgba(15,118,110,0.12)' : '0 1px 3px rgba(0,0,0,0.04)' ?>;">
                    
                    <div>
                      <!-- En-tête Badge & Radio Status -->
                      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; padding: 3px 9px; border-radius: 6px; background: <?= $badgeBg ?>; color: #FFFFFF;">
                          <?= $badgeText ?>
                        </span>
                        <div class="card-radio-icon" style="width: 22px; height: 22px; border-radius: 50%; border: 2px solid <?= $isSelected ? '#0F766E' : '#CBD5E1' ?>; background: <?= $isSelected ? '#0F766E' : '#FFF' ?>; display: flex; align-items: center; justify-content: center; color: #FFF; transition: all 0.2s;">
                          <?php if ($isSelected): ?><i data-lucide="check" style="width: 14px; height: 14px; stroke-width: 3;"></i><?php endif; ?>
                        </div>
                      </div>

                      <input type="radio" name="forfait_code" value="<?= $f['code_forfait'] ?>" <?= $isSelected ? 'checked' : '' ?> style="display: none;" id="radio-forfait-<?= $f['code_forfait'] ?>">

                      <!-- Titre du Plan -->
                      <h4 style="font-weight: 800; font-size: 17px; color: #0F172A; margin: 0 0 4px 0;"><?= htmlspecialchars($f['libelle_forfait']) ?></h4>
                      
                      <!-- Bloc Prix -->
                      <div style="display: flex; align-items: baseline; gap: 4px; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid #F1F5F9;">
                        <span style="font-size: 24px; font-weight: 800; color: #0F766E;"><?= number_format($f['montant_forfait'], 0, ',', ' ') ?></span>
                        <span style="font-size: 12px; font-weight: 600; color: #64748B;">FCFA / <?= (int)$f['duree_mois_forfait'] ?> mois</span>
                      </div>

                      <?php if (!empty($f['description_forfait'])): ?>
                        <p style="font-size: 12px; color: #64748B; margin: 0 0 14px 0; line-height: 1.4;">
                          <?= htmlspecialchars($f['description_forfait']) ?>
                        </p>
                      <?php endif; ?>

                      <!-- Liste verticale des Avantages -->
                      <?php if (!empty($advList)): ?>
                        <div style="margin-bottom: 14px;">
                          <div style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Avantages inclus :</div>
                          <div style="display: flex; flex-direction: column; gap: 8px;">
                            <?php foreach ($advList as $adv): ?>
                              <div style="font-size: 13px; font-weight: 500; color: #1E293B; display: flex; align-items: flex-start; gap: 8px; line-height: 1.4;">
                                <svg width="15" height="15" fill="none" stroke="#0F766E" viewBox="0 0 24 24" style="flex-shrink: 0; margin-top: 2px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span><?= htmlspecialchars($adv) ?></span>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        </div>
                      <?php endif; ?>
                    </div>

                    <!-- Bouton Sélection -->
                    <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #F1F5F9;">
                      <button type="button" class="btn-select-plan" style="width: 100%; height: 38px; border-radius: 8px; font-size: 13px; font-weight: 700; border: <?= $isSelected ? 'none' : '1px solid #CBD5E1' ?>; background: <?= $isSelected ? '#0F766E' : '#F8FAFC' ?>; color: <?= $isSelected ? '#FFFFFF' : '#334155' ?>; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 6px;">
                        <span><?= $isSelected ? 'Sélectionné' : 'Choisir ce forfait' ?></span>
                      </button>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>

              <div class="form-grid">
                <div class="form-field">
                  <label for="duree_mois">Durée de la période (en mois)</label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('calendar'); ?></span>
                    <input type="number" class="form-control" id="duree_mois" name="duree_mois" value="3" min="1" max="36">
                  </div>
                  <small style="color: #10b981; font-weight: 600; font-size: 11px; display: block; margin-top: 4px;">
                    Offre de Lancement : 3 mois (90 jours) d'essai offerts !
                  </small>
                </div>

                <div class="form-field">
                  <label for="montant_abonnement">Montant Facturé (FCFA)</label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('dollar-sign'); ?></span>
                    <input type="number" class="form-control" id="montant_abonnement" name="montant_abonnement" placeholder="Montant">
                  </div>
                </div>

                <div class="form-field">
                  <label for="date_debut_abonnement">Date de Début d'Abonnement</label>
                  <div class="input-with-icon">
                    <span class="input-icon"><?= Validator::icon('clock'); ?></span>
                    <input type="date" class="form-control" id="date_debut_abonnement" name="date_debut_abonnement" value="<?= date('Y-m-d') ?>">
                  </div>
                </div>
              </div>

              <div style="display: flex; justify-content: space-between; margin-top: 32px; border-top: 1px solid #f1f5f9; padding-top: 20px;">
                <button type="button" class="btn btn-outline-secondary" onclick="goToStep(2)" style="border-radius: 10px; padding: 10px 24px;">
                  <i data-lucide="arrow-left"></i> Précédent
                </button>

                <button type="submit" class="btn btn-success btn_actions btnEditPressing" style="border-radius: 10px; padding: 12px 32px; font-weight: 700; background: #10b981; border-color: #10b981;">
                  <span class="btn-text">
                    <i data-lucide="check-circle"></i> Valider & Activer le Pressing (Tout-en-Un)
                  </span>
                </button>
              </div>
            </div>
            <?php else: ?>
            <!-- BOUTON POUR MODIFICATION PRESSING SEUL -->
            <div class="form-actions" style="margin-top: 24px;">
              <button type="submit" class="btn btn-primary btn_actions btnEditPressing" style="border-radius: 10px; padding: 10px 24px; background: #1E3A5F; border-color: #1E3A5F;">
                <span class="btn-text">
                  <i data-lucide="save"></i> Sauvegarder les modifications
                </span>
              </button>
              <a href="<?= RACINE ?>pressing/list" class="btn btn-secondary" style="border-radius: 10px; padding: 10px 24px;">
                Annuler
              </a>
            </div>
            <?php endif; ?>

          </form>
        </div>
      </div>

    </div>
  </main>
</div>

<script>
function goToStep(step) {
  $('.wizard-section').hide();
  $('#wizard-step-' + step).show();
  if (window.lucide) window.lucide.createIcons();

  $('.wizard-step-item').each(function(idx) {
    const sNum = idx + 1;
    const circle = $(this).find('div');
    const label = $(this).find('span');

    if (sNum === step) {
      circle.css({'background': '#2563eb', 'color': '#fff', 'box-shadow': '0 0 0 4px #dbeafe'});
      label.css({'color': '#1e293b', 'font-weight': '700'});
    } else if (sNum < step) {
      circle.css({'background': '#10b981', 'color': '#fff', 'box-shadow': 'none'});
      label.css({'color': '#10b981', 'font-weight': '600'});
    } else {
      circle.css({'background': '#e2e8f0', 'color': '#64748b', 'box-shadow': 'none'});
      label.css({'color': '#64748b', 'font-weight': '600'});
    }
  });
}

function selectForfaitCard(code, montant, duree) {
  $('.forfait-card-box').css({
    'border-color': '#E2E8F0',
    'background': '#FFFFFF',
    'box-shadow': '0 1px 3px rgba(0,0,0,0.04)',
    'transform': 'scale(1)'
  }).removeClass('selected');

  $('.card-radio-icon').css({
    'border-color': '#CBD5E1',
    'background': '#FFFFFF'
  }).html('');

  $('.btn-select-plan').css({
    'border': '1px solid #CBD5E1',
    'background': '#F8FAFC',
    'color': '#334155'
  }).html('Choisir ce forfait');

  const selectedCard = $('#forfait-card-' + code);
  selectedCard.css({
    'border-color': '#0F766E',
    'background': '#FFFFFF',
    'box-shadow': '0 4px 16px rgba(15,118,110,0.12)',
    'transform': 'translateY(-1px)'
  }).addClass('selected');

  selectedCard.find('.card-radio-icon').css({
    'border-color': '#0F766E',
    'background': '#0F766E'
  }).html('<i data-lucide="check" style="width: 14px; height: 14px; stroke-width: 3; color: #fff;"></i>');

  selectedCard.find('.btn-select-plan').css({
    'border': 'none',
    'background': '#0F766E',
    'color': '#FFFFFF'
  }).html('Sélectionné');

  $('#radio-forfait-' + code).prop('checked', true);

  if (montant !== undefined) $('#montant_abonnement').val(montant);
  if (duree !== undefined) $('#duree_mois').val(duree);

  if (window.lucide) window.lucide.createIcons();
}

function filterQuartiersByVille() {
  const selectedVille = $('#ville_code').val();
  const searchTerm = $('#quartier_search').val() ? $('#quartier_search').val().toLowerCase().trim() : '';

  $('#quartier_code option').each(function() {
    const optionText = $(this).text().toLowerCase();
    const qVille = $(this).attr('data-ville');
    const isVilleMatch = !qVille || !selectedVille || qVille === selectedVille;
    const isSearchMatch = !searchTerm || optionText.includes(searchTerm);

    if (isVilleMatch && isSearchMatch) {
      $(this).show().prop('disabled', false);
    } else {
      $(this).hide().prop('disabled', true);
    }
  });

  const currentQuartier = $('#quartier_code option:selected');
  if (currentQuartier.length && currentQuartier.attr('data-ville') && currentQuartier.attr('data-ville') !== selectedVille) {
    $('#quartier_code').val('');
  }
}

$('#ville_code').on('change', filterQuartiersByVille);

$('#quartier_search').on('keyup input', function() {
  filterQuartiersByVille();
  const searchTerm = $(this).val().trim();
  if (searchTerm.length > 0) {
    const firstMatch = $('#quartier_code option:not(:disabled)[value!=""]:first');
    if (firstMatch.length) {
      $('#quartier_code').val(firstMatch.val());
    }
  }
});

function compressAndPreviewImage(input, previewImgId, placeholderIconId, maxMB) {
  if (input.files && input.files[0]) {
    const file = input.files[0];
    const maxSizeBytes = (maxMB || 3) * 1024 * 1024;

    if (file.size > maxSizeBytes) {
      if (typeof showToast === 'function') {
        showToast('Photo volumineuse (' + (file.size / (1024 * 1024)).toFixed(1) + ' Mo). Compression automatique...', 'info');
      }

      const reader = new FileReader();
      reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
          const canvas = document.createElement('canvas');
          let width = img.width;
          let height = img.height;
          const maxDim = 1400;

          if (width > maxDim || height > maxDim) {
            if (width > height) {
              height = Math.round((height * maxDim) / width);
              width = maxDim;
            } else {
              width = Math.round((width * maxDim) / height);
              height = maxDim;
            }
          }

          canvas.width = width;
          canvas.height = height;
          const ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0, width, height);

          canvas.toBlob(function(blob) {
            try {
              const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".webp", {
                type: 'image/webp',
                lastModified: Date.now()
              });

              const dataTransfer = new DataTransfer();
              dataTransfer.items.add(compressedFile);
              input.files = dataTransfer.files;
            } catch(err) {}

            $('#' + previewImgId).attr('src', canvas.toDataURL('image/webp', 0.85)).show();
            if (placeholderIconId) $('#' + placeholderIconId).hide();

            if (typeof showToast === 'function') {
              showToast('Image optimisée avec succès !', 'success');
            }
          }, 'image/webp', 0.85);
        };
        img.src = e.target.result;
      };
      reader.readAsDataURL(file);
    } else {
      var reader = new FileReader();
      reader.onload = function(e) {
        $('#' + previewImgId).attr('src', e.target.result).show();
        if (placeholderIconId) $('#' + placeholderIconId).hide();
      };
      reader.readAsDataURL(file);
    }
  }
}

function previewPressingLogo(input) {
  compressAndPreviewImage(input, 'logoPreviewImg', 'logoPlaceholderIcon', 3);
}

function previewPressingMiniature(input) {
  compressAndPreviewImage(input, 'miniaturePreviewImg', 'miniaturePlaceholderIcon', 3);
}

function toggleSeuilLivraison() {
  const isChecked = $('#livraison_gratuite').is(':checked');
  const input = $('#seuil_livraison_gratuite');
  if (isChecked) {
    input.prop('disabled', false).css({'background': '#FFFFFF', 'opacity': '1', 'cursor': 'text'});
  } else {
    input.prop('disabled', true).css({'background': '#F1F5F9', 'opacity': '0.6', 'cursor': 'not-allowed'});
  }
}

$('#livraison_gratuite').on('change', toggleSeuilLivraison);

function toggleLogisticsFields(mode) {
  if (!mode) mode = $('#mode_logistique').val();
  if (mode === 'pressing') {
    $('#container_frais_collecte, #container_frais_livraison, #container_propose_livraison, #container_livraison_gratuite, #container_seuil_livraison_gratuite, #container_delai_livraison').show();
    $('#container_delai_livraison label').text('Temps / Délais Moyen de Livraison (Pressing)');
    $('#container_notice_lavex, #container_notice_retrait').hide();
    $('#propose_livraison').prop('checked', true);
  } else if (mode === 'lavex') {
    $('#container_frais_collecte, #container_frais_livraison, #container_propose_livraison, #container_livraison_gratuite, #container_seuil_livraison_gratuite, #container_delai_livraison').hide();
    $('#container_notice_lavex').show();
    $('#container_notice_retrait').hide();
    $('#propose_livraison').prop('checked', true);
  } else if (mode === 'retrait_uniquement') {
    $('#container_frais_collecte, #container_frais_livraison, #container_propose_livraison, #container_livraison_gratuite, #container_seuil_livraison_gratuite').hide();
    $('#container_delai_livraison').show();
    $('#container_delai_livraison label').text('Temps Moyen de Traitement en Atelier');
    $('#container_notice_lavex').hide();
    $('#container_notice_retrait').show();
    $('#propose_livraison').prop('checked', false);
  }
  if (window.lucide) window.lucide.createIcons();
}

function getCurrentGpsLocation() {
  const statusEl = document.getElementById('gps-status-info');
  if (!navigator.geolocation) {
    alert("La géolocalisation n'est pas supportée par votre navigateur.");
    return;
  }
  if (statusEl) {
    statusEl.style.color = "#2563EB";
    statusEl.textContent = "Recherche de votre position GPS en cours...";
  }
  navigator.geolocation.getCurrentPosition(function(pos) {
    const lat = pos.coords.latitude;
    const lng = pos.coords.longitude;
    document.getElementById('latitude_pressing').value = lat;
    document.getElementById('longitude_pressing').value = lng;
    if (statusEl) {
      statusEl.style.color = "#059669";
      statusEl.innerHTML = "✓ Coordonnées GPS capturées (" + lat.toFixed(6) + ", " + lng.toFixed(6) + ")";
    }
  }, function(err) {
    if (statusEl) {
      statusEl.style.color = "#D97706";
      statusEl.textContent = "Impossible d'accéder au GPS direct. La position sera calculée automatiquement par le serveur à l'enregistrement.";
    }
  }, { enableHighAccuracy: true, timeout: 10000 });
}

$(document).ready(function() {
  const firstForfait = $('input[name="forfait_code"]:checked').val();
  if (firstForfait) {
    $('#forfait-card-' + firstForfait).css({'border-color': '#2563eb', 'background': '#eff6ff'});
  }
  filterQuartiersByVille();
  toggleSeuilLivraison();
  toggleLogisticsFields();
});
</script>

<script src="<?= RACINE ?>public/json/entities/pressings.js?v=5"></script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
