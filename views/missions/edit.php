<?php
require_once __DIR__ . '/../../public/inc/header.php';
$mission = isset($mission) ? $mission : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1><?= isset($mission['id_mission']) ? 'Modifier la mission' : 'Ajouter une mission' ?></h1>
          <p class="page-subtitle">Gestion des missions</p>
        </div>
        <a href="<?= RACINE ?>mission/list" class="btn btn-sm btn-outline-secondary">
          <i data-lucide="arrow-left"></i>
          Retour à la liste
        </a>
      </div>

      <div class="form-card">
        <div class="card-header">
          <div>
            <h2>Informations de la mission</h2>
          </div>
          <?php if (isset($mission['statut_mission'])): ?>
            <span class="badge-status <?= in_array($mission['statut_mission'], ['en_cours','terminee']) ? 'delivered' : 'cancelled' ?>">
              <?= htmlspecialchars($mission['statut_mission']) ?>
            </span>
          <?php endif; ?>
        </div>

        <div class="card-body">
          <form class="formEditMission">
            <?= Validator::csrfField() ?>
            <input type="hidden" id="id_mission" name="id_mission" value="<?= htmlspecialchars($mission['id_mission'] ?? '') ?>">

             <div class="form-grid">
               <div class="form-field">
                 <label for="commande_code">Commande</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('shopping-cart'); ?></span>
                   <input type="text" class="form-control" id="commande_code" name="commande_code"
                          value="<?= htmlspecialchars($mission['commande_code'] ?? '') ?>" required>
                 </div>
                 <div class="error-message" id="commandeError"></div>
               </div>

               <div class="form-field">
                 <label for="type_mission">Type</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('briefcase'); ?></span>
                   <select class="form-control" id="type_mission" name="type_mission" required>
                     <option value="">Sélectionner</option>
                     <option value="collecte" <?= ($mission['type_mission'] ?? '') === 'collecte' ? 'selected' : '' ?>>Collecte</option>
                     <option value="livraison" <?= ($mission['type_mission'] ?? '') === 'livraison' ? 'selected' : '' ?>>Livraison</option>
                   </select>
                 </div>
                 <div class="error-message" id="typeError"></div>
               </div>

               <div class="form-field">
                 <label for="livreur_code">Livreur</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('user'); ?></span>
                   <input type="text" class="form-control" id="livreur_code" name="livreur_code"
                          value="<?= htmlspecialchars($mission['livreur_code'] ?? '') ?>">
                 </div>
                 <div class="error-message" id="livreurError"></div>
               </div>

               <div class="form-field">
                 <label for="adresse_mission">Adresse</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('map-pin'); ?></span>
                   <textarea class="form-control" id="adresse_mission" name="adresse_mission"><?= htmlspecialchars($mission['adresse_mission'] ?? '') ?></textarea>
                 </div>
                 <div class="error-message" id="adresseError"></div>
               </div>

               <div class="form-field">
                 <label for="observation_mission">Observation</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('file-text'); ?></span>
                   <textarea class="form-control" id="observation_mission" name="observation_mission"><?= htmlspecialchars($mission['observation_mission'] ?? '') ?></textarea>
                 </div>
                 <div class="error-message" id="observationError"></div>
               </div>

               <?php if (isset($mission['statut_mission'])): ?>
               <div class="form-field">
                 <label for="statut_mission">Statut</label>
                 <div class="input-with-icon">
                   <span class="input-icon"><?= Validator::icon('toggle-left'); ?></span>
                   <select class="form-control" id="statut_mission" name="statut_mission">
                     <option value="en_attente" <?= ($mission['statut_mission'] ?? '') === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                     <option value="en_cours" <?= ($mission['statut_mission'] ?? '') === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                     <option value="terminee" <?= ($mission['statut_mission'] ?? '') === 'terminee' ? 'selected' : '' ?>>Terminée</option>
                     <option value="annulee" <?= ($mission['statut_mission'] ?? '') === 'annulee' ? 'selected' : '' ?>>Annulée</option>
                   </select>
                 </div>
                 <div class="error-message" id="statutError"></div>
               </div>
               <?php endif; ?>
             </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary btn_actions btnEditMission" style="background: #1E3A5F; border-color: #1E3A5F;">
                <span class="btn-text">
                  <i data-lucide="save"></i>
                  Sauvegarder
                </span>
              </button>
              <a href="<?= RACINE ?>mission/list" class="btn btn-secondary">
                <i data-lucide="x"></i>
                Annuler
              </a>
            </div>
          </form>
        </div>
      </div>

    </div>
  </main>
</div>

<script src="<?= RACINE ?>public/json/entities/missions.js?v=4"></script>
<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
