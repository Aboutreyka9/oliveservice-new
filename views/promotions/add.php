<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>

  <main class="main-content">
    <div class="content-header" style="margin-bottom: 24px;">
      <a href="<?= RACINE ?>promotion/list" style="display: inline-flex; align-items: center; gap: 6px; color: #64748B; font-weight: 600; text-decoration: none; margin-bottom: 12px; font-size: 13px;">
        <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour à la liste
      </a>
      <h1 style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 0;">
        Créer un Nouveau Code Promo
      </h1>
    </div>

    <?php if (isset($_SESSION['error_msg'])): ?>
      <div style="background: #FEF2F2; border: 1px solid #FCA5A5; color: #B91C1C; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-weight: 600;">
        <?= htmlspecialchars($_SESSION['error_msg']) ?>
      </div>
      <?php unset($_SESSION['error_msg']); ?>
    <?php endif; ?>

    <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 24px; max-width: 680px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
      <form action="<?= RACINE ?>promotion/add" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
          <div class="form-field">
            <label style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
              Code Promo <span style="color: #DC2626;">*</span>
            </label>
            <input type="text" class="form-control" name="code_promo" placeholder="ex: LAVEX20" style="text-transform: uppercase; font-weight: 800;" required>
          </div>

          <div class="form-field">
            <label style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
              Type de Réduction <span style="color: #DC2626;">*</span>
            </label>
            <select class="form-control" name="type_reduction" required>
              <option value="pourcentage">Pourcentage (%)</option>
              <option value="montant_fixe">Montant Fixe (FCFA)</option>
            </select>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
          <div class="form-field">
            <label style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
              Valeur de la Réduction <span style="color: #DC2626;">*</span>
            </label>
            <input type="number" step="0.01" class="form-control" name="valeur_reduction" placeholder="ex: 20 ou 1000" required>
            <small style="color: #64748B; font-size: 11px;">Entrer 20 pour 20% ou 1000 pour 1000 FCFA</small>
          </div>

          <div class="form-field">
            <label style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
              Plafond Max Réduction (FCFA)
            </label>
            <input type="number" step="100" class="form-control" name="reduction_max" placeholder="ex: 5000 (Optionnel)">
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
          <div class="form-field">
            <label style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
              Montant Minimum Commande (FCFA)
            </label>
            <input type="number" step="100" class="form-control" name="montant_minimum_commande" value="0" placeholder="0">
          </div>

          <div class="form-field">
            <label style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
              Limite d'utilisations par client
            </label>
            <input type="number" class="form-control" name="limite_utilisations_par_client" value="1" min="1" required>
          </div>
        </div>

        <div class="form-field" style="margin-bottom: 16px;">
          <label style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
            Description / Titre de la promotion
          </label>
          <input type="text" class="form-control" name="description_promo" placeholder="ex: Offre de bienvenue -20% sur la 1ère commande">
        </div>

        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 14px; border-radius: 10px; margin-bottom: 20px;">
          <label style="font-weight: 700; font-size: 13px; color: #1E293B; display: flex; align-items: center; gap: 8px; cursor: pointer; margin: 0;">
            <input type="checkbox" name="premiere_commande_uniquement" value="1" style="width: 18px; height: 18px;">
            <span>Réservé exclusivement à la 1ère commande du client</span>
          </label>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
          <div class="form-field">
            <label style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
              Statut
            </label>
            <select class="form-control" name="statut_promo">
              <option value="actif">Actif</option>
              <option value="inactif">Inactif</option>
            </select>
          </div>

          <div class="form-field">
            <label style="font-weight: 700; font-size: 13px; color: #1E293B; display: block; margin-bottom: 6px;">
              Limite globale d'utilisations
            </label>
            <input type="number" class="form-control" name="limite_utilisations_globale" placeholder="Illimité (laisser vide)">
          </div>
        </div>

        <div style="text-align: right;">
          <button type="submit" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; padding: 12px 24px; font-weight: 700; border-radius: 10px;">
            Créer le Code Promo
          </button>
        </div>
      </form>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
