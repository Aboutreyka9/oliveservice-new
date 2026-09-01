<?php
require_once __DIR__ . '/../../public/inc/header.php';
$tarif = isset($tarif) ? $tarif : [];
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header">
        <div>
          <h1>Détails du tarif</h1>
          <p class="page-subtitle">Informations complètes</p>
        </div>
        <a href="<?= RACINE ?>tarif/list" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Retour</a>
      </div>

      <div class="detail-card">
        <div class="detail-card-header"><h2>Tarif</h2></div>
        <div class="detail-card-body">
          <div class="info-list">
            <div class="info-item">
              <span class="info-label">Code</span>
              <span class="info-value code-badge"><?= htmlspecialchars($tarif['code_tarif'] ?? '') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Pressing</span>
              <span class="info-value"><?= htmlspecialchars($tarif['pressing_code'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Article</span>
              <span class="info-value"><?= htmlspecialchars($tarif['article_code'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Service</span>
              <span class="info-value"><?= htmlspecialchars($tarif['service_code'] ?? '-') ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Prix</span>
              <span class="info-value"><?= htmlspecialchars($tarif['prix_tarif'] ?? '-') ?> FCFA</span>
            </div>
            <div class="info-item">
              <span class="info-label">Statut</span>
              <span class="info-value">
                <span class="badge-status <?= ($tarif['statut_tarif'] ?? '') == 'actif' ? 'delivered' : 'cancelled' ?>">
                  <?= htmlspecialchars($tarif['statut_tarif'] ?? '') ?>
                </span>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
