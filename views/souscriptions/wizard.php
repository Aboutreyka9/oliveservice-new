<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">

      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="file-text" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Nouvelle Souscription Client
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Saisie des informations du client, choix des packs et validation du sous-dossier
            </p>
          </div>
        </div>

        <a href="<?= RACINE ?>souscription/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;">
          <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour aux souscriptions
        </a>
      </div>

      <!-- INDICATEURS DE PROGRESSION (ONGLETS SANS ÉTAPE NUMÉROTÉE) -->
      <div style="display: flex; gap: 12px; margin-bottom: 24px; align-items: center; flex-wrap: wrap;">
        <div id="step-indicator-1" class="step-indicator active" style="flex: 1; min-width: 200px; padding: 14px 18px; text-align: center; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; border-radius: 12px; font-weight: 800; font-size: 13px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 8px;">
          <i data-lucide="user" style="width: 16px; height: 16px;"></i> Informations Client
        </div>
        <div style="color: #CBD5E1; font-size: 18px; display: none;" class="step-arrow">→</div>
        <div id="step-indicator-2" class="step-indicator" style="flex: 1; min-width: 200px; padding: 14px 18px; text-align: center; background: #FFFFFF; color: #64748B; border: 1px solid #E2E8F0; border-radius: 12px; font-weight: 800; font-size: 13px; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 8px;">
          <i data-lucide="shopping-bag" style="width: 16px; height: 16px;"></i> Sélection des Packs
        </div>
        <div style="color: #CBD5E1; font-size: 18px; display: none;" class="step-arrow">→</div>
        <div id="step-indicator-3" class="step-indicator" style="flex: 1; min-width: 200px; padding: 14px 18px; text-align: center; background: #FFFFFF; color: #64748B; border: 1px solid #E2E8F0; border-radius: 12px; font-weight: 800; font-size: 13px; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 8px;">
          <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i> Récapitulatif & Validation
        </div>
      </div>

      <!-- CARTE FORMULAIRE PRINCIPALE -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box;">
        <div id="form-messages" style="display: none; margin-bottom: 20px; padding: 14px 18px; border-radius: 10px; font-weight: 700; font-size: 14px;"></div>
        
        <form id="form-souscription-wizard" action="<?= RACINE ?>souscription/wizardSubmit" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">

          <!-- BLOC 1 : INFORMATIONS CLIENT -->
          <div id="step-1" class="form-step">
            <div style="margin-bottom: 24px;">
              <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
                <i data-lucide="user" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Informations personnelles du client
              </h3>
              
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
                <div class="form-group" style="width: 100%; box-sizing: border-box;">
                  <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                    Nom complet <span style="color: #EF4444;">*</span>
                  </label>
                  <input type="text" name="nom_client" id="nom_client" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A;" required placeholder="Ex: KOUASSI Jean">
                </div>

                <div class="form-group" style="width: 100%; box-sizing: border-box;">
                  <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                    Téléphone <span style="color: #EF4444;">*</span>
                  </label>
                  <input type="text" name="telephone_client" id="telephone_client" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A;" required placeholder="Ex: 0708091011">
                </div>

                <div class="form-group" style="width: 100%; box-sizing: border-box;">
                  <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                    Genre <span style="color: #EF4444;">*</span>
                  </label>
                  <select name="sexe_client" id="sexe_client" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A;" required>
                    <option value="">-- Sélectionner --</option>
                    <option value="M">Masculin</option>
                    <option value="F">Féminin</option>
                  </select>
                </div>

                <div class="form-group" style="width: 100%; box-sizing: border-box;">
                  <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                    Lieu de résidence <span style="color: #EF4444;">*</span>
                  </label>
                  <input type="text" name="lieu_residence_client" id="lieu_residence_client" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A;" required placeholder="Ex: Cocody">
                </div>

                <div class="form-group" style="width: 100%; box-sizing: border-box;">
                  <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                    Email <small style="color: #64748B;">(optionnel)</small>
                  </label>
                  <input type="email" name="email_client" id="email_client" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A;" placeholder="Ex: client@email.com">
                </div>

                <div class="form-group" style="width: 100%; box-sizing: border-box;">
                  <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                    Profession <small style="color: #64748B;">(optionnel)</small>
                  </label>
                  <input type="text" name="profession_client" id="profession_client" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; color: #0F172A;" placeholder="Ex: Commerçant">
                </div>
              </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0;">
              <button type="button" id="btn-step-1-next" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 12px 28px; font-size: 14px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); cursor: pointer;">
                Continuer <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
              </button>
            </div>
          </div>

          <!-- BLOC 2 : SÉLECTION DES PACKS -->
          <div id="step-2" class="form-step" style="display: none;">
            <div style="margin-bottom: 24px;">
              <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
                <i data-lucide="shopping-bag" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Sélection des packs
              </h3>

              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div class="form-group" style="width: 100%; box-sizing: border-box;">
                  <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                    Session d'activité <span style="color: #EF4444;">*</span>
                  </label>
                  <select id="filter-session" class="form-control select2" style="width: 100%; box-sizing: border-box;" required>
                    <option value="">-- Choisir une session --</option>
                    <?php if (!empty($sessions) && is_array($sessions)): ?>
                      <?php foreach ($sessions as $s): ?>
                        <option value="<?= $s['code_session'] ?>" data-zone="<?= htmlspecialchars($s['zone_code'] ?? '') ?>"><?= htmlspecialchars($s['libelle_session']) ?></option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>

                <div class="form-group" style="width: 100%; box-sizing: border-box;">
                  <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
                    Catégorie
                  </label>
                  <select id="filter-categorie" class="form-control select2" style="width: 100%; box-sizing: border-box;">
                    <option value="">Toutes les catégories</option>
                    <?php if (!empty($categories) && is_array($categories)): ?>
                      <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['code_categorie_pack'] ?>"><?= htmlspecialchars($cat['libelle_categorie_pack']) ?></option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>

              <div id="packs-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px;">
                <p style="color: #94A3B8; text-align: center; padding: 40px 0; font-style: italic;">Sélectionnez une session et/ou une catégorie pour afficher les packs disponibles.</p>
              </div>
            </div>

            <div style="display: flex; justify-content: space-between; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0;">
              <button type="button" id="btn-step-2-prev" class="btn" style="background: #F1F5F9; color: #475569; font-weight: 700; border-radius: 10px; padding: 12px 24px; text-decoration: none; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; gap: 8px;">
                <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Précédent
              </button>
              <button type="button" id="btn-step-2-next" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 12px 28px; font-size: 14px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); cursor: pointer;">
                Continuer <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
              </button>
            </div>
          </div>

          <!-- BLOC 3 : RÉCAPITULATIF & VALIDATION -->
          <div id="step-3" class="form-step" style="display: none;">
            <div style="margin-bottom: 24px;">
              <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
                <i data-lucide="check-circle" style="width: 18px; height: 18px; color: #059669;"></i> Récapitulatif et validation
              </h3>

              <!-- CARD CLIENT PREMIUM -->
              <div style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); border-radius: 14px; padding: 22px; color: #FFFFFF; margin-bottom: 24px; box-shadow: 0 8px 20px rgba(15, 23, 42, 0.15);">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 16px; padding-bottom: 14px; border-bottom: 1px solid rgba(255, 255, 255, 0.15); flex-wrap: wrap;">
                  <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 46px; height: 46px; border-radius: 50%; background: rgba(255, 255, 255, 0.15); display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
                      <i data-lucide="user" style="width: 24px; height: 24px; color: #38BDF8;"></i>
                    </div>
                    <div>
                      <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #93C5FD; font-weight: 700; display: block;">Client souscripteur</span>
                      <h4 id="recap-nom" style="font-size: 18px; font-weight: 800; margin: 2px 0 0 0; color: #FFFFFF;">-</h4>
                    </div>
                  </div>
                  <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; background: rgba(56, 189, 248, 0.15); color: #38BDF8; padding: 5px 14px; border-radius: 20px; border: 1px solid rgba(56, 189, 248, 0.3);">
                    <i data-lucide="shield-check" style="width: 14px; height: 14px;"></i> Profil Client Validé
                  </span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
                  <div style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 10px; padding: 10px 14px; display: flex; align-items: center; gap: 10px;">
                    <i data-lucide="phone" style="width: 18px; height: 18px; color: #38BDF8; flex-shrink: 0;"></i>
                    <div>
                      <span style="display: block; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #94A3B8; font-weight: 700;">Téléphone</span>
                      <span id="recap-telephone" style="font-size: 13px; font-weight: 700; color: #F8FAFC;">-</span>
                    </div>
                  </div>

                  <div style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 10px; padding: 10px 14px; display: flex; align-items: center; gap: 10px;">
                    <i data-lucide="user-check" style="width: 18px; height: 18px; color: #38BDF8; flex-shrink: 0;"></i>
                    <div>
                      <span style="display: block; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #94A3B8; font-weight: 700;">Genre</span>
                      <span id="recap-sexe" style="font-size: 13px; font-weight: 700; color: #F8FAFC;">-</span>
                    </div>
                  </div>

                  <div style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 10px; padding: 10px 14px; display: flex; align-items: center; gap: 10px;">
                    <i data-lucide="map-pin" style="width: 18px; height: 18px; color: #38BDF8; flex-shrink: 0;"></i>
                    <div>
                      <span style="display: block; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #94A3B8; font-weight: 700;">Résidence</span>
                      <span id="recap-lieu" style="font-size: 13px; font-weight: 700; color: #F8FAFC;">-</span>
                    </div>
                  </div>

                  <div style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 10px; padding: 10px 14px; display: flex; align-items: center; gap: 10px;">
                    <i data-lucide="mail" style="width: 18px; height: 18px; color: #38BDF8; flex-shrink: 0;"></i>
                    <div>
                      <span style="display: block; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #94A3B8; font-weight: 700;">Email</span>
                      <span id="recap-email" style="font-size: 13px; font-weight: 700; color: #F8FAFC;">-</span>
                    </div>
                  </div>

                  <div style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 10px; padding: 10px 14px; display: flex; align-items: center; gap: 10px;">
                    <i data-lucide="briefcase" style="width: 18px; height: 18px; color: #38BDF8; flex-shrink: 0;"></i>
                    <div>
                      <span style="display: block; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #94A3B8; font-weight: 700;">Profession</span>
                      <span id="recap-profession" style="font-size: 13px; font-weight: 700; color: #F8FAFC;">-</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- TABLEAU RÉCAPITULATIF DES PACKS -->
              <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; margin-bottom: 24px; overflow-x: auto;">
                <h4 style="font-size: 14px; font-weight: 800; color: #0F172A; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                  <i data-lucide="package" style="width: 16px; height: 16px; color: #1E3A5F;"></i> Packs sélectionnés
                </h4>
                <table id="table-recap-packs" class="table display nowrap" style="width:100%; border-collapse: collapse; font-size: 13px;">
                  <thead>
                    <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 2px solid #E2E8F0;">
                      <th style="padding: 10px 12px; text-transform: uppercase; font-size: 11px; font-weight: 800;">Pack</th>
                      <th style="padding: 10px 12px; text-transform: uppercase; font-size: 11px; font-weight: 800;">Catégorie</th>
                      <th style="padding: 10px 12px; text-align: right; text-transform: uppercase; font-size: 11px; font-weight: 800;">Montant/Jour</th>
                      <th style="padding: 10px 12px; text-align: center; text-transform: uppercase; font-size: 11px; font-weight: 800;">Articles</th>
                      <th style="padding: 10px 12px; text-align: center; text-transform: uppercase; font-size: 11px; font-weight: 800;">Durée (jours)</th>
                      <th style="padding: 10px 12px; text-align: right; text-transform: uppercase; font-size: 11px; font-weight: 800;">Cotisation Pack (Total)</th>
                      <th style="padding: 10px 12px; text-align: center; text-transform: uppercase; font-size: 11px; font-weight: 800;">Action</th>
                    </tr>
                  </thead>
                  <tbody id="recap-packs-body"></tbody>
                  <tfoot>
                    <tr style="background: #F1F5F9; font-weight: 800; color: #1E3A5F;">
                      <td colspan="2" style="padding: 12px; text-align: right;">Total Cotisation / Jour :</td>
                      <td id="recap-cotis-jour-total" style="padding: 12px; text-align: right; color: #2563EB; font-size: 15px;">0 FCFA</td>
                      <td colspan="2" style="padding: 12px; text-align: right;">Total Prévu Souscription :</td>
                      <td id="recap-montant-total" style="padding: 12px; text-align: right; color: #059669; font-size: 16px;">0 FCFA</td>
                      <td></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>

            <input type="hidden" name="nom_client" id="hidden-nom_client">
            <input type="hidden" name="telephone_client" id="hidden-telephone_client">
            <input type="hidden" name="email_client" id="hidden-email_client">
            <input type="hidden" name="sexe_client" id="hidden-sexe_client">
            <input type="hidden" name="lieu_residence_client" id="hidden-lieu_residence_client">
            <input type="hidden" name="profession_client" id="hidden-profession_client">
            <input type="hidden" name="zone_code" id="hidden-zone_code" value="">
            <input type="hidden" name="session_code" id="hidden-session_code" value="">
            <input type="hidden" name="packs" id="hidden-packs" value="">

            <div style="display: flex; justify-content: space-between; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0;">
              <button type="button" id="btn-step-3-prev" class="btn" style="background: #F1F5F9; color: #475569; font-weight: 700; border-radius: 10px; padding: 12px 24px; text-decoration: none; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; gap: 8px;">
                <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Précédent
              </button>
              <button type="submit" id="btn-submit-wizard" class="btn" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; font-weight: 800; border-radius: 10px; padding: 12px 28px; font-size: 15px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3); cursor: pointer;">
                <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Valider la Souscription
              </button>
            </div>
          </div>

        </form>
      </div>

    </div>
  </main>
</div>

<script>
var selectedPacks = [];

function showMessage(type, message) {
  var $msg = $('#form-messages');
  $msg.removeClass('alert-success alert-danger alert-warning alert-info')
      .css({ 
        'display': 'block', 
        'background': type === 'success' ? '#ECFDF5' : type === 'danger' ? '#FEE2E2' : type === 'warning' ? '#FEF3C7' : '#EFF6FF', 
        'color': type === 'success' ? '#047857' : type === 'danger' ? '#B91C1C' : type === 'warning' ? '#92400E' : '#1E3A5F', 
        'border': '1px solid ' + (type === 'success' ? '#A7F3D0' : type === 'danger' ? '#FCA5A5' : type === 'warning' ? '#FDE68A' : '#BFDBFE') 
      })
      .html('<strong>' + (type === 'success' ? 'Succès' : type === 'danger' ? 'Erreur' : type === 'warning' ? 'Attention' : 'Information') + ' :</strong> ' + message);
}

function hideMessage() {
  $('#form-messages').hide();
}

function showStep(step) {
  $('.form-step').hide();
  $('#step-' + step).show();

  $('.step-indicator').each(function() {
    var idx = parseInt($(this).attr('id').split('-')[2]);
    if (idx === step) {
      $(this).css({
        'background': 'linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%)',
        'color': '#FFFFFF',
        'border-color': '#1E3A5F',
        'box-shadow': '0 4px 12px rgba(15, 23, 42, 0.2)'
      });
    } else {
      $(this).css({
        'background': '#FFFFFF',
        'color': '#64748B',
        'border-color': '#E2E8F0',
        'box-shadow': 'none'
      });
    }
  });

  hideMessage();
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }
  if (step === 3) {
    renderRecap();
  }
}

function loadPacks() {
  var sessionCode = $('#filter-session').val();
  var categorieCode = $('#filter-categorie').val();
  var container = $('#packs-container');

  if (!sessionCode) {
    container.html('<p style="color: #94A3B8; text-align: center; padding: 40px 0; font-style: italic;">Sélectionnez une session pour afficher les packs disponibles.</p>');
    return;
  }

  $.ajax({
    url: '<?= RACINE ?>souscription/wizardData',
    data: { session_code: sessionCode, categorie_code: categorieCode },
    dataType: 'json',
    success: function(res) {
      if (res.status === 1 && res.data.length) {
        var html = '';
        res.data.forEach(function(pack) {
          var isSelected = selectedPacks.indexOf(pack.code_pack) !== -1;
          var borderColor = isSelected ? '#1E3A5F' : '#E2E8F0';
          var bgColor = isSelected ? '#F0F9FF' : '#FFFFFF';
          var shadow = isSelected ? '0 4px 14px rgba(30, 58, 95, 0.15)' : '0 1px 3px rgba(0,0,0,0.05)';
          html += '<div class="pack-card" data-code="' + pack.code_pack + '" style="background: ' + bgColor + '; border: 2px solid ' + borderColor + '; border-radius: 14px; padding: 18px; cursor: pointer; box-shadow: ' + shadow + '; transition: all 0.2s;">' +
            '<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">' +
              '<div>' +
                '<strong style="font-size: 15px; color: #0F172A; display: block; margin-bottom: 6px;">' + (pack.libelle_pack || 'Pack') + '</strong>' +
                '<span style="display: inline-block; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; background: #1E3A5F; color: #FFF;">' + (pack.libelle_categorie_pack || '') + '</span>' +
              '</div>' +
              (pack.image_pack ? '<img src="<?= RACINE ?>public/assets/images/packs/' + pack.image_pack + '" style="width: 54px; height: 54px; object-fit: cover; border-radius: 10px; border: 1px solid #E2E8F0;">' : '<div style="width: 54px; height: 54px; background: #F1F5F9; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #64748B; font-size: 11px; font-weight: 700;">Pack</div>') +
            '</div>' +
            '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12px; color: #475569;">' +
              '<div><strong>Montant :</strong> <span style="color: #059669; font-weight: 800;">' + Number(pack.prix_cotisation_pack || 0).toLocaleString('fr-FR') + ' FCFA</span></div>' +
              '<div><strong>Durée :</strong> ' + (pack.nombre_jour_session || 0) + ' jours</div>' +
              '<div><strong>Articles :</strong> ' + (pack.nombre_articles || 0) + ' article(s)</div>' +
              '<div><strong>Souscriptions :</strong> ' + (pack.nombre_souscriptions || 0) + '</div>' +
            '</div>' +
          '</div>';
        });
        container.html(html);
        if (window.lucide) lucide.createIcons();
      } else {
        container.html('<p style="color: #94A3B8; text-align: center; padding: 40px 0; font-style: italic;">Aucun pack disponible pour ces critères.</p>');
      }
    },
    error: function() {
      container.html('<p style="color: #DC2626; text-align: center; padding: 40px 0;">Erreur lors du chargement des packs.</p>');
    }
  });
}

function renderRecap() {
  $('#recap-nom').text($('#nom_client').val().trim() || '-');
  $('#recap-telephone').text($('#telephone_client').val().trim() || '-');
  var sexeVal = $('#sexe_client').val();
  var sexeTxt = sexeVal === 'M' ? 'Masculin' : (sexeVal === 'F' ? 'Féminin' : '-');
  $('#recap-sexe').text(sexeTxt);
  $('#recap-lieu').text($('#lieu_residence_client').val().trim() || '-');
  $('#recap-email').text($('#email_client').val().trim() || '-');
  $('#recap-profession').text($('#profession_client').val().trim() || '-');

  var tbody = $('#recap-packs-body');
  tbody.empty();
  var totalCotisJour = 0;
  var totalPrévuGlobal = 0;
  var sessionCode = $('#filter-session').val();

  if (!sessionCode || selectedPacks.length === 0) {
    tbody.html('<tr><td colspan="7" style="padding: 20px; text-align: center; color: #94A3B8;">Aucun pack sélectionné.</td></tr>');
    $('#recap-cotis-jour-total').text('0 FCFA');
    $('#recap-montant-total').text('0 FCFA');
    return;
  }

  $.ajax({
    url: '<?= RACINE ?>souscription/wizardData',
    data: { session_code: sessionCode, categorie_code: '' },
    dataType: 'json',
    success: function(res) {
      if (res.status === 1 && res.data && res.data.length) {
        selectedPacks.forEach(function(code) {
          var pack = res.data.find(function(p) { return p.code_pack === code; });
          if (pack) {
            var cotisJour = parseFloat(pack.prix_cotisation_pack || 0);
            var dureeJours = parseInt(pack.nombre_jour_session || 0);
            var totalCotisationPack = cotisJour * dureeJours;

            totalCotisJour += cotisJour;
            totalPrévuGlobal += totalCotisationPack;

            var row = '<tr data-code="' + pack.code_pack + '" style="border-bottom: 1px solid #E2E8F0;">' +
              '<td style="padding: 10px 12px; font-weight: 700; color: #0F172A;">' + (pack.libelle_pack || 'Pack') + '</td>' +
              '<td style="padding: 10px 12px;"><span style="display: inline-block; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; background: #1E3A5F; color: #FFF;">' + (pack.libelle_categorie_pack || '') + '</span></td>' +
              '<td style="padding: 10px 12px; text-align: right; font-weight: 700; color: #2563EB;">' + cotisJour.toLocaleString('fr-FR') + ' FCFA</td>' +
              '<td style="padding: 10px 12px; text-align: center;">' + (pack.nombre_articles || 0) + '</td>' +
              '<td style="padding: 10px 12px; text-align: center;">' + dureeJours + ' j</td>' +
              '<td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #059669;">' + totalCotisationPack.toLocaleString('fr-FR') + ' FCFA</td>' +
              '<td style="padding: 10px 12px; text-align: center;">' +
                '<button type="button" class="btn btn-sm remove-pack-row" style="border-radius: 8px; font-weight: 600; background: #DC2626; border: none; color: #FFF; padding: 5px 10px; cursor: pointer;">' +
                  '<i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>' +
                '</button>' +
              '</td>' +
            '</tr>';
            tbody.append(row);
          }
        });
        $('#recap-cotis-jour-total').text(totalCotisJour.toLocaleString('fr-FR') + ' FCFA');
        $('#recap-montant-total').text(totalPrévuGlobal.toLocaleString('fr-FR') + ' FCFA');
        if (window.lucide) lucide.createIcons();
      }
    }
  });
}

$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  if ($.fn.select2) {
    $('.select2').select2({ width: '100%' });
  }

  $('#btn-step-1-next').on('click', function() {
    var nom = $('#nom_client').val().trim();
    var tel = $('#telephone_client').val().trim();
    var sexe = $('#sexe_client').val();
    var lieu = $('#lieu_residence_client').val().trim();
    if (!nom || !tel || !sexe || !lieu) {
      showMessage('danger', 'Veuillez remplir tous les champs obligatoires (Nom, Téléphone, Genre, Lieu de résidence).');
      return;
    }
    showStep(2);
    loadPacks();
  });

  $('#btn-step-2-prev').on('click', function() {
    showStep(1);
  });

  $('#btn-step-3-prev').on('click', function() {
    showStep(2);
  });

  $(document).on('click', '.pack-card', function() {
    var code = $(this).data('code');
    var idx = selectedPacks.indexOf(code);
    if (idx === -1) {
      selectedPacks.push(code);
    } else {
      selectedPacks.splice(idx, 1);
    }
    loadPacks();
  });

  $('#filter-session').on('change', function() {
    selectedPacks = [];
    loadPacks();
  });
  $('#filter-categorie').on('change', function() {
    loadPacks();
  });

  $(document).on('click', '.remove-pack-row', function() {
    var code = $(this).closest('tr').data('code');
    var idx = selectedPacks.indexOf(code);
    if (idx !== -1) {
      selectedPacks.splice(idx, 1);
    }
    renderRecap();
    loadPacks();
  });

  $('#btn-step-2-next').on('click', function() {
    if (!$('#filter-session').val()) {
      showMessage('danger', 'Veuillez sélectionner une session d\'activité.');
      return;
    }
    if (selectedPacks.length === 0) {
      showMessage('danger', 'Veuillez sélectionner au moins un pack.');
      return;
    }
    showStep(3);
  });

  $('#form-souscription-wizard').on('submit', function(e) {
    e.preventDefault();
    if (!$('#filter-session').val()) {
      showMessage('danger', 'Veuillez sélectionner une session d\'activité.');
      return;
    }
    if (selectedPacks.length === 0) {
      showMessage('danger', 'Aucun pack sélectionné.');
      return;
    }

    var $btnSubmit = $('#btn-submit-wizard');
    $btnSubmit.prop('disabled', true)
              .css({ 'opacity': '0.65', 'cursor': 'not-allowed', 'pointer-events': 'none' })
              .html('<i data-lucide="loader" style="width: 18px; height: 18px; animation: spin 1s linear infinite;"></i> Traitement en cours...');
    if (window.lucide) lucide.createIcons();

    $('#hidden-nom_client').val($('#nom_client').val().trim());
    $('#hidden-telephone_client').val($('#telephone_client').val().trim());
    $('#hidden-email_client').val($('#email_client').val().trim());
    $('#hidden-sexe_client').val($('#sexe_client').val());
    $('#hidden-lieu_residence_client').val($('#lieu_residence_client').val().trim());
    var selectedSessionZone = $('#filter-session option:selected').data('zone') || '<?= Context::zone() ?>';
    $('#hidden-zone_code').val(selectedSessionZone);
    $('#hidden-session_code').val($('#filter-session').val() || '');
    $('#hidden-packs').val(JSON.stringify(selectedPacks));

    var formData = $(this).serialize();
    $.ajax({
      url: $(this).attr('action'),
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function(res) {
        if (res.status === 1 || res.success) {
          if (window.toastr) toastr.success(res.message || 'Souscription créée avec succès');
          setTimeout(function() { window.location.href = '<?= RACINE ?>souscription/list'; }, 1500);
        } else {
          showMessage('danger', res.message || 'Erreur lors de l\'enregistrement');
          if (window.toastr) toastr.error(res.message || 'Erreur lors de l\'enregistrement');
          resetSubmitBtn();
        }
      },
      error: function() {
        showMessage('danger', 'Erreur réseau ou serveur indisponible');
        if (window.toastr) toastr.error('Erreur réseau');
        resetSubmitBtn();
      }
    });

    function resetSubmitBtn() {
      $btnSubmit.prop('disabled', false)
                .css({ 'opacity': '1', 'cursor': 'pointer', 'pointer-events': 'auto' })
                .html('<i data-lucide="check-circle" style="width: 18px; height: 18px;"></i> Valider la Souscription');
      if (window.lucide) lucide.createIcons();
    }
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
