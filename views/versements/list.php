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
            <i data-lucide="arrow-down-left" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Versements Commerciaux
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Suivi, contrôle et validation des versements des agents commerciaux auprès de la caisse centrale
            </p>
          </div>
        </div>

        <?php if (Context::can('COMMERCIAL_MAKE_VERSEMENT')): ?>
        <a href="<?= RACINE ?>versement/formulaire" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 12px 22px; font-size: 14px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); text-decoration: none; cursor: pointer;">
          <i data-lucide="arrow-down-left" style="width: 18px; height: 18px;"></i> Saisir un Versement
        </a>
        <?php endif; ?>
      </div>

      <!-- CSRF TOKEN -->
      <input type="hidden" id="csrf_token" value="<?= Validator::generateCsrfToken() ?>">

      <!-- CARTE TABLEAU PRINCIPALE (NAVY PREMIUM) -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-versements" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse; font-size: 13px;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 2px solid #E2E8F0;">
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Code Versement</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Commercial</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Zone</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Période</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Code Caisse</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: right;">Montant</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: center;">Statut</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- MODALE DE VALIDATION COMPTABLE DU VERSEMENT -->
<div class="modal-overlay" id="modalValiderVersement" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(5px); padding: 16px; box-sizing: border-box;">
  <div class="modal" style="max-width: 750px; width: 100%; max-height: calc(100vh - 32px); display: flex; flex-direction: column; background: #FFFFFF; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.3); overflow: hidden; border: 1px solid #E2E8F0; margin: auto;">
    <div class="modal-header" style="flex-shrink: 0; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; padding: 20px 24px; display: flex; justify-content: space-between; align-items: center;">
      <h3 class="modal-title" style="font-weight: 800; font-size: 16px; margin: 0; color: #FFFFFF; display: flex; align-items: center; gap: 10px;">
        <i data-lucide="check-circle" style="width: 20px; height: 20px; color: #10B981;"></i> Contrôle & Validation Comptable
      </h3>
      <button type="button" class="modal-close-btn" id="modalValiderClose" style="background: rgba(255,255,255,0.12); border: none; color: #FFFFFF; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; transition: background 0.2s;" title="Fermer">
        <i data-lucide="x" style="width: 18px; height: 18px;"></i>
      </button>
    </div>

    <form id="form-valider-versement" style="margin: 0; display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden;">
      <input type="hidden" name="id_versement" id="val_id_versement" value="">
      <div class="modal-body" style="padding: 24px; flex: 1; min-height: 0; overflow-y: auto;">
        
        <!-- CARTE RÉCAPITULATIVE DU VERSEMENT -->
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px; margin-bottom: 20px;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; border-bottom: 1px dashed #CBD5E1; padding-bottom: 10px; flex-wrap: wrap; gap: 8px;">
            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Code Versement</span>
              <div style="font-weight: 800; font-size: 15px; color: #1E3A5F; font-family: monospace;" id="val_code_versement">-</div>
            </div>
            <div style="text-align: right;">
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Montant Versé</span>
              <div style="font-weight: 900; font-size: 18px; color: #059669;" id="val_montant_fmt">0 FCFA</div>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; font-size: 13px;">
            <div>
              <span style="color: #64748B; font-weight: 600;">Commercial :</span> 
              <strong id="val_commercial_nom" style="color: #0F172A; display: block; margin-top: 2px;">-</strong>
            </div>
            <div>
              <span style="color: #64748B; font-weight: 600;">Zone :</span> 
              <strong id="val_zone_nom" style="color: #0F172A; display: block; margin-top: 2px;">-</strong>
            </div>
            <div>
              <span style="color: #64748B; font-weight: 600;">Période :</span> 
              <strong id="val_periode_txt" style="color: #0F172A; display: block; margin-top: 2px;">-</strong>
            </div>
            <div>
              <span style="color: #64748B; font-weight: 600;">Bordereau / Réf :</span> 
              <strong id="val_reference_txt" style="color: #0F172A; font-family: monospace; display: block; margin-top: 2px;">-</strong>
            </div>
          </div>
        </div>

        <!-- PROCÈS-VERBAL COMPACT & DÉPOUILLEMENT DE LA CAISSE -->
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px; margin-bottom: 20px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px; flex-wrap: wrap; gap: 8px;">
            <h4 style="font-size: 13px; font-weight: 800; color: #1E3A5F; margin: 0; display: flex; align-items: center; gap: 8px;">
              <i data-lucide="receipt" style="width: 16px; height: 16px; color: #059669;"></i> Procès-Verbal de Caisse & Dépouillement
            </h4>
            <span style="font-size: 11px; font-weight: 700; color: #059669; background: #ECFDF5; border: 1px solid #A7F3D0; padding: 3px 10px; border-radius: 12px;">
              Séance Associée : <code id="hist_linked_caisse_code" style="font-weight: 800; color: #1E3A5F; font-family: monospace;">-</code>
            </span>
          </div>

          <!-- LOADER / SPINNER -->
          <div id="caisse_history_loading" style="display: none; text-align: center; padding: 20px; color: #64748B;">
            <i class="fa fa-spinner fa-spin" style="font-size: 20px; color: #1E3A5F;"></i>
            <p style="font-size: 12px; margin-top: 8px; font-weight: 600;">Chargement du procès-verbal de caisse...</p>
          </div>

          <!-- CONTENU COMPACT -->
          <div id="caisse_history_content">
            <!-- GRILLE DE SYNTHÈSE DE LA SÉANCE -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px; margin-bottom: 16px;">
              <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px;">
                <span style="font-size: 10px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block;">Espèces Collectées</span>
                <strong id="hist_caisse_attendu" style="font-size: 14px; font-weight: 900; color: #1E3A5F; display: block; margin-top: 2px;">0 FCFA</strong>
                <span style="font-size: 10px; color: #64748B;">Cash guichet attendu</span>
              </div>

              <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px;">
                <span style="font-size: 10px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block;">Mobile Money / Autre</span>
                <strong id="hist_total_momo" style="font-size: 14px; font-weight: 900; color: #7E22CE; display: block; margin-top: 2px;">0 FCFA</strong>
                <span style="font-size: 10px; color: #64748B;">Paiements digitaux</span>
              </div>

              <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px;">
                <span style="font-size: 10px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block;">Montant Déclaré (Pot)</span>
                <strong id="hist_caisse_pot" style="font-size: 14px; font-weight: 900; color: #059669; display: block; margin-top: 2px;">0 FCFA</strong>
                <span style="font-size: 10px; color: #059669; font-weight: 600;">Versement transmis</span>
              </div>

              <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px;">
                <span style="font-size: 10px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block;">Écart Constaté</span>
                <strong id="hist_caisse_ecart" style="font-size: 14px; font-weight: 900; color: #059669; display: block; margin-top: 2px;">0 FCFA</strong>
                <span id="hist_ecart_badge" style="font-size: 10px; font-weight: 700; color: #059669;">Conforme</span>
              </div>
            </div>

            <!-- TABLEAU DES COTISATIONS ENCAISSÉES DANS CETTE CAISSE -->
            <div style="margin-top: 10px;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span style="font-size: 12px; font-weight: 800; color: #0F172A;">
                  Détail des cotisations collectées (<span id="hist_nb_caisse_cotis">0</span>)
                </span>
              </div>
              <div style="max-height: 220px; overflow-y: auto; background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                  <thead>
                    <tr style="background: #F8FAFC; color: #64748B; border-bottom: 1px solid #E2E8F0; text-align: left;">
                      <th style="padding: 8px 10px; font-weight: 800;">Souscription</th>
                      <th style="padding: 8px 10px; font-weight: 800;">Client</th>
                      <th style="padding: 8px 10px; font-weight: 800;">Mode</th>
                      <th style="padding: 8px 10px; font-weight: 800;">Date & Heure</th>
                      <th style="padding: 8px 10px; font-weight: 800; text-align: right;">Montant</th>
                      <th style="padding: 8px 10px; font-weight: 800; text-align: center;">Statut</th>
                    </tr>
                  </thead>
                  <tbody id="hist_tbody_caisse_cotis">
                    <tr><td colspan="6" style="text-align: center; padding: 14px; color: #94A3B8;">Aucune cotisation attachée à cette caisse</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- DÉCISION DU CONTRÔLE -->
        <div class="form-group" style="margin-bottom: 18px;">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">
            Décision de validation <span style="color: #EF4444;">*</span>
          </label>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
            <label style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1.5px solid #A7F3D0; background: #ECFDF5; border-radius: 10px; cursor: pointer; font-weight: 700; font-size: 13px; color: #047857;">
              <input type="radio" name="statut_versement" value="valide" checked style="accent-color: #059669; width: 16px; height: 16px;">
              <span>Valider le versement</span>
            </label>
            <label style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1.5px solid #FECACA; background: #FEF2F2; border-radius: 10px; cursor: pointer; font-weight: 700; font-size: 13px; color: #DC2626;">
              <input type="radio" name="statut_versement" value="annule" style="accent-color: #DC2626; width: 16px; height: 16px;">
              <span>Rejeter / Annuler</span>
            </label>
          </div>
        </div>

        <!-- COMMENTAIRE / OBSERVATIONS -->
        <div class="form-group" style="margin-bottom: 0;">
          <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">
            Observations / Note de validation
          </label>
          <textarea name="commentaire_validation" id="val_commentaire" rows="3" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px; font-size: 13px; font-weight: 500; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" placeholder="Ex: Montant vérifié et perçu sans écart par la caisse centrale."></textarea>
          <p style="font-size: 11px; color: #64748B; margin: 6px 0 0 0; display: flex; align-items: center; gap: 4px;">
            <i data-lucide="info" style="width: 13px; height: 13px; color: #0284C7;"></i> 
            La validation basculera automatiquement toutes les cotisations en attente de ce commercial en statut « validé ».
          </p>
        </div>

      </div>

      <div class="modal-footer" style="flex-shrink: 0; background: #F8FAFC; padding: 16px 24px; border-top: 1px solid #E2E8F0; display: flex; justify-content: flex-end; gap: 12px;">
        <button type="button" class="btn modal-close-btn" style="background: #FFFFFF; color: #475569; font-weight: 700; border-radius: 8px; padding: 10px 18px; border: 1px solid #CBD5E1; font-size: 13px; cursor: pointer;">
          Annuler
        </button>
        <button type="submit" id="btn-submit-valider-versement" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; font-weight: 800; border-radius: 8px; padding: 10px 22px; border: none; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
          <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i> Confirmer la Décision
        </button>
      </div>
    </form>
  </div>
</div>

<script src="<?= RACINE ?>public/assets/js/modules/versements.js?v=1.2"></script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
