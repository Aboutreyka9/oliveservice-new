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
            <i data-lucide="wallet" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Ma Caisse Journalière & Clôture
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Tableau de bord de caisse commercial, bilan d'activité et clôture automatique
            </p>
          </div>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <?php if (Context::hasPermission('COMMERCIAL_MAKE_VERSEMENT') || Context::isAdmin()): ?>
            <a href="<?= RACINE ?>versement/formulaire" class="btn" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2); transition: all 0.2s ease;">
              <i data-lucide="arrow-down-left" style="width: 16px; height: 16px; color: #FFFFFF;"></i> Faire un Versement
            </a>
          <?php endif; ?>
          <a href="<?= RACINE ?>caisse_commercial/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;">
            <i data-lucide="history" style="width: 16px; height: 16px; color: #64748B;"></i> Historique des Caisses
          </a>
        </div>
      </div>

      <!-- LOADER EN ATTENTE -->
      <div id="caisse-loader" style="text-align: center; padding: 50px; background: #FFFFFF; border-radius: 16px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem; color: #1E3A5F !important;">
          <span class="visually-hidden">Chargement...</span>
        </div>
        <p style="margin-top: 14px; font-weight: 700; color: #64748B;">Chargement du statut de votre caisse...</p>
      </div>

      <!-- CAS 1 : CAISSE ACTUELLEMENT OUVERTE -->
      <div id="section-caisse-ouverte" style="display: none;">
        
        <!-- BANNER STATUT OUVERT (NAVY GRADIENT) -->
        <div class="card-premium" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; border-radius: 16px; padding: 28px; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25);">
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
              <span style="background: #ECFDF5; color: #059669; font-weight: 800; font-size: 11px; padding: 6px 14px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid #A7F3D0; display: inline-flex; align-items: center; gap: 6px;">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #059669; display: inline-block;"></span> Caisse Ouverte Aujourd'hui
              </span>
              <h2 style="font-size: 22px; font-weight: 900; margin: 12px 0 4px 0; letter-spacing: -0.5px; color: #FFFFFF;" id="open_session_code">CAISSE-000</h2>
              <div style="font-size: 13px; opacity: 0.9; font-weight: 500;">
                Ouverte le <strong id="open_session_date" style="color: #FFFFFF;">-</strong> à <strong id="open_session_heure" style="color: #FFFFFF;">-</strong>
              </div>
            </div>
            <div style="display: flex; gap: 12px;">
              <button type="button" class="btn btn-open-modal-details" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); color: #FFFFFF; font-weight: 800; border-radius: 10px; padding: 12px 20px; border: 1px solid rgba(255,255,255,0.25); display: inline-flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.2s;">
                <i data-lucide="eye" style="width: 18px; height: 18px; color: #FFFFFF;"></i> Voir Détails des Cotisations
              </button>
            </div>
          </div>
        </div>

        <!-- CARDS DE RÉSUMÉ AUTOMATIQUE -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 24px;">
          <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);">
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Recette Totale Collectée</div>
            <div style="font-size: 26px; font-weight: 900; color: #059669; margin-top: 6px;" id="open_total_general">0 FCFA</div>
            <div style="font-size: 12px; color: #94A3B8; margin-top: 4px; font-weight: 500;">Calculé automatiquement</div>
          </div>

          <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);">
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Nombre de Cotisations</div>
            <div style="font-size: 26px; font-weight: 900; color: #1E3A5F; margin-top: 6px;" id="open_nb_cotisations">0</div>
            <div style="font-size: 12px; color: #94A3B8; margin-top: 4px; font-weight: 500;">Transactions enregistrées</div>
          </div>

          <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);">
            <div style="font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Fond Initial Déclaré</div>
            <div style="font-size: 26px; font-weight: 900; color: #0F172A; margin-top: 6px;" id="open_fond_initial">0 FCFA</div>
            <div style="font-size: 12px; color: #94A3B8; margin-top: 4px; font-weight: 500;">Saisi à l'ouverture</div>
          </div>

          <div class="card-premium" style="background: #F8FAFC; border-radius: 16px; padding: 20px; border: 1px solid #E2E8F0;">
            <div style="font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Répartition par mode</div>
            <div style="margin-top: 8px; font-size: 13px; font-weight: 700; color: #334155; line-height: 1.6;">
              💵 Espèces : <span id="open_especes" style="color: #0F172A; font-weight: 800;">0 FCFA</span><br>
              📱 Mobile Money : <span id="open_mobile" style="color: #7E22CE; font-weight: 800;">0 FCFA</span><br>
              🏦 Chèques/Vir. : <span id="open_cheques" style="color: #0284C7; font-weight: 800;">0 FCFA</span>
            </div>
          </div>
        </div>

        <!-- FORMULAIRE DE CLÔTURE DIRECTE -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);">
          <div style="font-size: 15px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 12px;">
            <i data-lucide="lock" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Procéder à la Clôture de Caisse du Jour
          </div>
          
          <form id="form-cloturer-caisse">
            <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
            
            <div class="form-group" style="margin-bottom: 24px;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Note / Observations de clôture (Optionnel)</label>
              <textarea class="form-control" name="observations" style="width: 100%; box-sizing: border-box; padding: 14px; font-size: 14px; font-weight: 600; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC; min-height: 100px;" placeholder="Ex: Clôture de caisse effectuée sans écart. Monnaie restante au guichet." rows="3"></textarea>
            </div>

            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; border-top: 1px solid #E2E8F0; padding-top: 24px;">
              <button type="submit" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 12px 28px; font-size: 14px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); cursor: pointer;">
                <i data-lucide="lock" style="width: 18px; height: 18px;"></i> Clôturer Ma Caisse du Jour
              </button>
              <button type="button" class="btn btn-open-modal-details" style="background: #F1F5F9; color: #475569; font-weight: 700; border-radius: 10px; padding: 12px 24px; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                <i data-lucide="list" style="width: 18px; height: 18px;"></i> Revoir la Liste des Encaissements
              </button>
            </div>
          </form>
        </div>

      </div>

      <!-- CAS 2 : PAS DE CAISSE OUVERTE (AFFICHER BILAN PRÉCÉDENT & OUVERTURE DU JOUR) -->
      <div id="section-caisse-fermee" style="display: none;">
        
        <!-- BANNER STATUT FERMÉ -->
        <div class="card-premium" style="background: #FEF3C7; border: 1.5px solid #FDE68A; border-radius: 16px; padding: 24px; margin-bottom: 24px;">
          <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div>
              <span style="background: #D97706; color: #FFFFFF; font-weight: 800; font-size: 11px; padding: 6px 14px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="alert-circle" style="width: 14px; height: 14px;"></i> Pas de caisse ouverte aujourd'hui
              </span>
              <h2 style="font-size: 19px; font-weight: 800; color: #92400E; margin: 12px 0 4px 0;">Votre caisse journalière n'est pas encore activée</h2>
              <div style="font-size: 13px; color: #B45309; font-weight: 500;">
                Ouvrez votre caisse ci-dessous pour pouvoir collecter des cotisations sur le terrain.
              </div>
            </div>
          </div>
        </div>

        <!-- BILAN DE LA DERNIÈRE SESSION -->
        <div id="card-dernier-bilan" class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); display: none;">
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">
              📋 Bilan de votre dernière caisse clôturée
            </h3>
            <button type="button" class="btn btn-open-modal-details" style="background: #F1F5F9; color: #1E3A5F; font-weight: 700; border-radius: 8px; padding: 8px 16px; border: 1px solid #CBD5E1; display: inline-flex; align-items: center; gap: 6px; font-size: 13px;">
              <i data-lucide="eye" style="width: 14px; height: 14px;"></i> Voir Détails des Cotisations
            </button>
          </div>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 18px; background: #F8FAFC; padding: 20px; border-radius: 12px; border: 1px solid #E2E8F0;">
            <div>
              <div style="font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Code Clôture</div>
              <div style="font-size: 15px; font-weight: 800; color: #1E3A5F; font-family: monospace; margin-top: 4px;" id="last_code_lbl">-</div>
            </div>
            <div>
              <div style="font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Date de Caisse</div>
              <div style="font-size: 15px; font-weight: 800; color: #0F172A; margin-top: 4px;" id="last_date_lbl">-</div>
            </div>
            <div>
              <div style="font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Recette Totale</div>
              <div style="font-size: 16px; font-weight: 800; color: #059669; margin-top: 4px;" id="last_total_lbl">0 FCFA</div>
            </div>
            <div>
              <div style="font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Statut Validation Finance</div>
              <div style="font-size: 14px; font-weight: 800; margin-top: 4px;" id="last_statut_lbl">-</div>
            </div>
          </div>
        </div>

        <!-- FORMULAIRE D'OUVERTURE RAPIDE -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 32px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);">
          <div style="font-size: 15px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 12px;">
            <i data-lucide="unlock" style="color: #059669; width: 20px; height: 20px;"></i> Effectuer l'Ouverture de Caisse du Jour
          </div>

          <form id="form-ouvrir-caisse">
            <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 24px;">
              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Fond de caisse initial (FCFA)</label>
                <input type="number" name="fond_initial" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 16px; font-weight: 800; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" value="0" placeholder="Ex: 0 ou 10000" required>
              </div>
              <div class="form-group" style="width: 100%; box-sizing: border-box;">
                <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px;">Observations de démarrage</label>
                <input type="text" name="observations" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; font-weight: 600; color: #0F172A; border-radius: 10px; border: 1px solid #CBD5E1; outline: none; background: #F8FAFC;" placeholder="Ex: Prise de poste à 08h00">
              </div>
            </div>

            <div style="border-top: 1px solid #E2E8F0; padding-top: 24px;">
              <button type="submit" class="btn" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; font-weight: 800; border-radius: 10px; padding: 12px 28px; font-size: 14px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2); cursor: pointer;">
                <i data-lucide="unlock" style="width: 18px; height: 18px;"></i> Activer & Ouvrir Ma Caisse du Jour
              </button>
            </div>
          </form>
        </div>

      </div>

    </div>
  </main>
</div>

<!-- MODAL DETAILS DES COTISATIONS (STRUCTURE SYSTÈME GEICG NAVY PREMIUM) -->
<div class="modal-overlay" id="modalCotisationsDetails">
  <div class="modal" style="max-width: 920px; width: 92%; background: #FFFFFF; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25); overflow: hidden; border: 1px solid #E2E8F0;">
    
    <div class="modal-header" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; padding: 20px 28px; display: flex; justify-content: space-between; align-items: center;">
      <h3 class="modal-title" style="font-weight: 800; font-size: 17px; margin: 0; color: #FFFFFF; display: flex; align-items: center; gap: 10px;">
        <i data-lucide="list-checks" style="width: 22px; height: 22px; color: #60A5FA;"></i>
        <span>Procès-Verbal des Cotisations de la Caisse</span>
      </h3>
      <button type="button" class="modal-close" id="modalCotisationsClose" style="background: rgba(255,255,255,0.1); border: none; color: #FFFFFF; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; transition: background 0.2s;" title="Fermer">
        <i data-lucide="x" style="width: 20px; height: 20px;"></i>
      </button>
    </div>

    <div class="modal-body" style="padding: 28px; max-height: 70vh; overflow-y: auto;">
      <div style="overflow-x: auto; width: 100%; border: 1px solid #E2E8F0; border-radius: 12px;">
        <table class="table" style="width: 100%; min-width: 700px; border-collapse: collapse; font-size: 13px; margin: 0;">
          <thead>
            <tr style="background: #F8FAFC; color: #64748B; text-align: left; border-bottom: 2px solid #E2E8F0;">
              <th style="padding: 12px 14px; font-weight: 800; font-size: 11px; text-transform: uppercase;">Code Souscription</th>
              <th style="padding: 12px 14px; font-weight: 800; font-size: 11px; text-transform: uppercase;">Client</th>
              <th style="padding: 12px 14px; font-weight: 800; font-size: 11px; text-transform: uppercase;">Montant</th>
              <th style="padding: 12px 14px; font-weight: 800; font-size: 11px; text-transform: uppercase;">Mode</th>
              <th style="padding: 12px 14px; font-weight: 800; font-size: 11px; text-transform: uppercase;">Date Cotisation</th>
              <th style="padding: 12px 14px; font-weight: 800; font-size: 11px; text-transform: uppercase;">Prochain RDV</th>
              <th style="padding: 12px 14px; font-weight: 800; font-size: 11px; text-transform: uppercase;">Statut</th>
            </tr>
          </thead>
          <tbody id="tbl_cotisations_body">
            <tr>
              <td colspan="7" style="text-align: center; color: #94A3B8; padding: 30px; font-weight: 600;">
                Aucune cotisation enregistrée pour l'instant dans cette caisse.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="modal-footer" style="background: #F8FAFC; padding: 16px 28px; border-top: 1px solid #E2E8F0; display: flex; justify-content: flex-end;">
      <button type="button" class="btn" id="btn-close-modal-cotisations" style="font-weight: 700; border-radius: 8px; padding: 10px 22px; background: #64748B; color: #FFFFFF; border: none; cursor: pointer;">Fermer</button>
    </div>

  </div>
</div>

<script src="<?= RACINE ?>public/assets/js/modules/caisse_commercial.js?v=<?= time() ?>"></script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
