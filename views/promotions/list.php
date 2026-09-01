<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>

  <main class="main-content">
    <div class="content-header" style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
      <div>
        <h1 style="font-size: 24px; font-weight: 800; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 10px;">
          <i data-lucide="ticket" style="width: 28px; height: 28px; color: #2563EB;"></i>
          Gestion des Codes Promo & Offres
        </h1>
        <p style="color: #64748B; font-size: 14px; margin: 4px 0 0;">
          Créez, administrez et suivez l'utilisation de vos campagnes promotionnelles (pourcentages, réductions fixes, 1ère commande).
        </p>
      </div>
      <a href="<?= RACINE ?>promotion/add" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; padding: 10px 20px; font-weight: 700; border-radius: 10px; display: inline-flex; align-items: center; gap: 8px;">
        <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i>
        Nouveau Code Promo
      </a>
    </div>

    <?php if (isset($_SESSION['success_msg'])): ?>
      <div style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #047857; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
        <?= htmlspecialchars($_SESSION['success_msg']) ?>
      </div>
      <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_msg'])): ?>
      <div style="background: #FEF2F2; border: 1px solid #FCA5A5; color: #B91C1C; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="alert-triangle" style="width: 18px; height: 18px;"></i>
        <?= htmlspecialchars($_SESSION['error_msg']) ?>
      </div>
      <?php unset($_SESSION['error_msg']); ?>
    <?php endif; ?>

    <div class="card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
      <div class="table-responsive">
        <table class="table" style="width: 100%; border-collapse: separate; border-spacing: 0;">
          <thead>
            <tr style="background: #F8FAFC; color: #64748B; font-size: 12px; font-weight: 700; text-transform: uppercase;">
              <th style="padding: 14px 16px; border-bottom: 1px solid #E2E8F0; text-align: left;">Code Promo</th>
              <th style="padding: 14px 16px; border-bottom: 1px solid #E2E8F0; text-align: left;">Type & Réduction</th>
              <th style="padding: 14px 16px; border-bottom: 1px solid #E2E8F0; text-align: left;">Condition</th>
              <th style="padding: 14px 16px; border-bottom: 1px solid #E2E8F0; text-align: center;">Utilisations</th>
              <th style="padding: 14px 16px; border-bottom: 1px solid #E2E8F0; text-align: center;">Statut</th>
              <th style="padding: 14px 16px; border-bottom: 1px solid #E2E8F0; text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($promotions)): ?>
              <tr>
                <td colspan="6" style="text-align: center; padding: 40px; color: #94A3B8; font-weight: 600;">
                  Aucun code promo créé pour le moment.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($promotions as $p): ?>
                <tr style="border-bottom: 1px solid #F1F5F9;">
                  <td style="padding: 14px 16px; font-weight: 800; color: #1E293B; font-size: 14px;">
                    <span style="background: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE; padding: 4px 10px; border-radius: 8px; letter-spacing: 0.5px;">
                      <?= htmlspecialchars($p['code_promo']) ?>
                    </span>
                    <?php if (!empty($p['description_promo'])): ?>
                      <div style="font-size: 12px; color: #64748B; font-weight: 400; margin-top: 4px; max-width: 260px;">
                        <?= htmlspecialchars($p['description_promo']) ?>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td style="padding: 14px 16px; font-weight: 700; color: #1E293B; font-size: 13px;">
                    <?php if ($p['type_reduction'] === 'pourcentage'): ?>
                      <span style="color: #059669; font-weight: 800;">-<?= number_format($p['valeur_reduction'], 0) ?>%</span>
                      <?php if (!empty($p['reduction_max'])): ?>
                        <small style="color: #64748B; font-weight: 500; display: block;">Plafond: <?= number_format($p['reduction_max'], 0, '', ' ') ?> FCFA</small>
                      <?php endif; ?>
                    <?php else: ?>
                      <span style="color: #2563EB; font-weight: 800;">-<?= number_format($p['valeur_reduction'], 0, '', ' ') ?> FCFA</span>
                    <?php endif; ?>
                  </td>
                  <td style="padding: 14px 16px; font-size: 12.5px; color: #475569;">
                    <?php if ($p['premiere_commande_uniquement']): ?>
                      <span style="background: #FEF3C7; color: #D97706; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; display: inline-block; margin-bottom: 2px;">1ère Commande</span><br>
                    <?php endif; ?>
                    <?php if ($p['montant_minimum_commande'] > 0): ?>
                      Min: <?= number_format($p['montant_minimum_commande'], 0, '', ' ') ?> FCFA
                    <?php else: ?>
                      Sans minimum
                    <?php endif; ?>
                  </td>
                  <td style="padding: 14px 16px; text-align: center; font-weight: 700; color: #1E293B; font-size: 14px;">
                    <?= (int)$p['total_utilisations'] ?>
                    <?php if (!empty($p['limite_utilisations_globale'])): ?>
                      <span style="color: #94A3B8; font-weight: 500; font-size: 12px;">/ <?= (int)$p['limite_utilisations_globale'] ?></span>
                    <?php endif; ?>
                  </td>
                  <td style="padding: 14px 16px; text-align: center;">
                    <?php if ($p['statut_promo'] === 'actif'): ?>
                      <span style="background: #DCFCE7; color: #166534; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700;">Actif</span>
                    <?php else: ?>
                      <span style="background: #F1F5F9; color: #64748B; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700;">Inactif</span>
                    <?php endif; ?>
                  </td>
                  <td style="padding: 14px 16px; text-align: right;">
                    <div style="display: flex; justify-content: flex-end; gap: 8px;">
                      <a href="<?= RACINE ?>promotion/edit/<?= $p['id_promo'] ?>" class="btn btn-sm btn-outline-secondary" title="Modifier" style="padding: 6px 10px; border-radius: 8px;">
                        <i data-lucide="edit-2" style="width: 14px; height: 14px;"></i>
                      </a>
                      <a href="<?= RACINE ?>promotion/delete/<?= $p['id_promo'] ?>" onclick="return confirm('Voulez-vous vraiment supprimer ce code promo ?')" class="btn btn-sm btn-outline-danger" title="Supprimer" style="padding: 6px 10px; border-radius: 8px; color: #DC2626; border-color: #FCA5A5;">
                        <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
