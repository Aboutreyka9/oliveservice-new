<?php
require_once __DIR__ . '/../../public/inc/header.php';
$pressings = isset($pressings) ? $pressings : [];
$forfaits = isset($forfaits) ? $forfaits : [];
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
?>

<style>
/* === MOBILE PWA UX OPTIMIZATIONS FOR B2B SUBSCRIPTIONS === */
@media (max-width: 768px) {
  .content-wrapper {
    padding: 12px 10px 80px 10px !important;
  }
  .page-header {
    flex-direction: column !important;
    align-items: stretch !important;
    margin-bottom: 16px !important;
    gap: 12px !important;
  }
  .page-header-actions {
    width: 100% !important;
  }
  .page-header-actions .btn {
    width: 100% !important;
    justify-content: center !important;
    height: 48px !important;
    font-size: 15px !important;
  }
}
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
          <h1 style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="credit-card" style="color: #2563EB;"></i> Abonnements Pressings B2B
          </h1>
          <p class="page-subtitle" style="color: #64748B; margin: 4px 0 0 0;">Gestion des forfaits, abonnements actifs et renouvellements</p>
        </div>
        <?php if ($isSuperAdmin): ?>
          <div class="page-header-actions">
            <button type="button" class="btn btn-primary" onclick="openCreateAbonnementModal()" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
              <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Créer un abonnement
            </button>
          </div>
        <?php endif; ?>
      </div>

      <div class="card" style="border-radius: 14px; padding: 20px;">
        <div class="mobile-list-container"></div>
        <div class="table-responsive-mobile">
          <table class="table" id="dataTable" style="width: 100%;">
            <thead>
              <tr>
                <th>N°</th>
                <th>Code</th>
                <th>Pressing</th>
                <th>Forfait B2B</th>
                <th>Montant</th>
                <th>Période & Validité</th>
                <th>Statut</th>
                <th style="text-align: center;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <!-- ==========================================
           MODALE 1 : CRÉATION D'ABONNEMENT PRESSING
           ========================================== -->
      <?php if ($isSuperAdmin): ?>
      <div id="modal-creer-abonnement" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: #FFF; border-radius: 16px; width: 92%; max-width: 580px; max-height: 90vh; overflow-y: auto; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
          
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
              <div style="width: 38px; height: 38px; border-radius: 10px; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="credit-card" style="width: 20px; height: 20px;"></i>
              </div>
              <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #1E293B;">Nouvel Abonnement Pressing</h3>
            </div>
            <button type="button" onclick="closeCreateAbonnementModal()" style="background: none; border: none; font-size: 24px; color: #94A3B8; cursor: pointer;">&times;</button>
          </div>

          <form id="form-creer-abonnement" onsubmit="submitCreateAbonnement(event)">
            <?= Validator::csrfField() ?>
            <input type="hidden" name="force_replace" id="force_replace" value="0">

            <!-- ALERTE DYNAMIQUE SI ABONNEMENT EN COURS -->
            <div id="alert-abonnement-existant" style="display: none; background: #FFFBEB; border: 1.5px solid #FCD34D; border-radius: 12px; padding: 14px; margin-bottom: 16px;">
              <div style="display: flex; gap: 10px; align-items: flex-start;">
                <div style="color: #D97706; font-size: 20px; line-height: 1;"><i class="fa fa-exclamation-triangle"></i></div>
                <div style="flex: 1;">
                  <strong style="color: #92400E; font-size: 14px; display: block;">Abonnement actif en cours détecté</strong>
                  <p id="alert-existant-msg" style="color: #B45309; font-size: 12px; margin: 4px 0 10px 0; line-height: 1.4;"></p>
                  <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <button type="button" id="btn-redirect-renouveler" class="btn btn-sm" style="background: #059669; color: #FFF; border: none; font-size: 12px; font-weight: 700; padding: 6px 12px; border-radius: 6px; cursor: pointer;">
                      <i class="fa fa-sync"></i> Prolonger / Renouveler plutôt
                    </button>
                    <button type="button" onclick="confirmForceReplace()" class="btn btn-sm" style="background: #FFF; color: #B45309; border: 1px solid #FCD34D; font-size: 12px; font-weight: 700; padding: 6px 12px; border-radius: 6px; cursor: pointer;">
                      Remplacer le forfait actuel
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pressing -->
            <div class="form-group" style="margin-bottom: 16px;">
              <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Pressing Partenaire *</label>
              <select id="abn_pressing_code" name="pressing_code" class="form-control" required onchange="checkPressingActiveSub(this.value)" style="width: 100%;">
                <option value="">-- Choisir un pressing --</option>
                <?php foreach ($pressings as $p): ?>
                  <option value="<?= htmlspecialchars($p['code_pressing']) ?>">
                    <?= htmlspecialchars($p['libelle_pressing']) ?> (<?= htmlspecialchars($p['code_pressing']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Forfait B2B -->
            <div class="form-group" style="margin-bottom: 16px;">
              <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Forfait B2B *</label>
              <select id="abn_forfait_code" name="forfait_code" class="form-control" required onchange="onForfaitChange(this)" style="width: 100%;">
                <option value="">-- Choisir un forfait --</option>
                <?php foreach ($forfaits as $f): ?>
                  <option value="<?= htmlspecialchars($f['code_forfait']) ?>" 
                          data-montant="<?= (float)$f['montant_forfait'] ?>" 
                          data-duree="<?= (int)$f['duree_mois_forfait'] ?>">
                    <?= htmlspecialchars($f['libelle_forfait']) ?> — <?= number_format($f['montant_forfait'], 0, ',', ' ') ?> FCFA (<?= (int)$f['duree_mois_forfait'] ?> mois)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
              <!-- Durée en mois -->
              <div class="form-group">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Durée</label>
                <select id="abn_duree_mois" name="duree_mois" class="form-control" onchange="recalcCreateDates()">
                  <option value="1">1 Mois</option>
                  <option value="3">3 Mois (Trimestriel)</option>
                  <option value="6">6 Mois (Semestriel)</option>
                  <option value="12">12 Mois (Annuel)</option>
                </select>
              </div>

              <!-- Montant -->
              <div class="form-group">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Montant (FCFA)</label>
                <input type="number" id="abn_montant" name="montant_abonnement" class="form-control" min="0" step="500" value="0">
              </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px;">
              <!-- Date Début -->
              <div class="form-group">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Date de début</label>
                <input type="date" id="abn_date_debut" name="date_debut_abonnement" class="form-control" value="<?= date('Y-m-d') ?>" onchange="recalcCreateDates()">
              </div>

              <!-- Date Fin -->
              <div class="form-group">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Date d'expiration</label>
                <input type="date" id="abn_date_fin" name="date_fin_abonnement" class="form-control" readonly style="background: #F8FAFC; font-weight: 700; color: #2563EB;">
              </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #E2E8F0; padding-top: 16px;">
              <button type="button" onclick="closeCreateAbonnementModal()" class="btn btn-secondary">Annuler</button>
              <button type="submit" class="btn btn-primary btnSubmitCreateAbn" style="display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i> Activer l'abonnement
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- ==========================================
           MODALE 2 : RENOUVELLEMENT D'ABONNEMENT
           ========================================== -->
      <div id="modal-renouveler-abonnement" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: #FFF; border-radius: 16px; width: 92%; max-width: 540px; max-height: 90vh; overflow-y: auto; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
          
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
              <div style="width: 38px; height: 38px; border-radius: 10px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="refresh-cw" style="width: 20px; height: 20px;"></i>
              </div>
              <div>
                <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #1E293B;">Renouveler l'Abonnement</h3>
                <small id="renouv-abn-code" style="color: #64748B; font-weight: 700;"></small>
              </div>
            </div>
            <button type="button" onclick="closeRenouvelerModal()" style="background: none; border: none; font-size: 24px; color: #94A3B8; cursor: pointer;">&times;</button>
          </div>

          <form id="form-renouveler-abonnement" onsubmit="submitRenouvelerAbonnement(event)">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="renouv_id_abonnement" name="id_abonnement_pressing" value="">
            <input type="hidden" id="renouv_current_date_fin" value="">

            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px 16px; margin-bottom: 16px;">
              <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                <span style="color: #64748B; font-size: 13px;">Pressing :</span>
                <strong id="renouv-pressing-name" style="color: #1E293B; font-size: 13px;"></strong>
              </div>
              <div style="display: flex; justify-content: space-between;">
                <span style="color: #64748B; font-size: 13px;">Échéance actuelle :</span>
                <strong id="renouv-date-fin-actuelle" style="color: #2563EB; font-size: 13px;"></strong>
              </div>
            </div>

            <!-- Choix du Forfait -->
            <div class="form-group" style="margin-bottom: 16px;">
              <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Forfait</label>
              <select id="renouv_forfait_code" name="forfait_code" class="form-control" required onchange="onRenouvForfaitChange(this)" style="width: 100%;">
                <?php foreach ($forfaits as $f): ?>
                  <option value="<?= htmlspecialchars($f['code_forfait']) ?>" 
                          data-montant="<?= (float)$f['montant_forfait'] ?>" 
                          data-duree="<?= (int)$f['duree_mois_forfait'] ?>">
                    <?= htmlspecialchars($f['libelle_forfait']) ?> — <?= number_format($f['montant_forfait'], 0, ',', ' ') ?> FCFA
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
              <!-- Période de prolongation -->
              <div class="form-group">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Prolonger de</label>
                <select id="renouv_duree_mois" name="duree_mois" class="form-control" onchange="recalcRenouvDates()">
                  <option value="1">+1 Mois</option>
                  <option value="3">+3 Mois (Trimestre)</option>
                  <option value="6">+6 Mois (Semestre)</option>
                  <option value="12">+12 Mois (1 An)</option>
                </select>
              </div>

              <!-- Montant -->
              <div class="form-group">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">Montant (FCFA)</label>
                <input type="number" id="renouv_montant" name="montant_abonnement" class="form-control" min="0" step="500" value="0">
              </div>
            </div>

            <!-- Nouvelle Date d'Échéance -->
            <div class="form-group" style="margin-bottom: 20px; background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 10px; padding: 12px 16px;">
              <span style="display: block; font-size: 12px; font-weight: 700; color: #047857; text-transform: uppercase;">Nouvelle date d'expiration</span>
              <h4 id="renouv-nouvelle-date-fin" style="font-size: 18px; font-weight: 800; color: #065F46; margin: 2px 0 0 0;">-</h4>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #E2E8F0; padding-top: 16px;">
              <button type="button" onclick="closeRenouvelerModal()" class="btn btn-secondary">Annuler</button>
              <button type="submit" class="btn btn-primary btnSubmitRenouv" style="background: #059669; border-color: #059669; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i> Confirmer le renouvellement
              </button>
            </div>
          </form>
        </div>
      </div>
      <?php endif; ?>

      <script src="<?= RACINE ?>public/json/mobile-list.js"></script>
      <script src="<?= RACINE ?>public/json/entities/abonnements_pressings.js?v=3"></script>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
