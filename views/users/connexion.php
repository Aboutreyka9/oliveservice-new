<?php
require_once __DIR__ . '/../../public/inc/header.php';

try {
    $db = (new Database())->getCon();
    $stmt = $db->query("SELECT logo_etablissement, libelle_etablissement FROM etablissements ORDER BY id_etablissement ASC LIMIT 1");
    $etabRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $loginLogo = $etabRow['logo_etablissement'] ?? '';
} catch (Exception $e) {
    $loginLogo = '';
}
?>

<div class="app-layout auth-layout">
  <main class="auth-content">
    <div class="auth-card">
      <div class="auth-header">
        <div class="logo">
          <?php if (!empty($loginLogo)): ?>
            <?php $logoUrl = (strpos($loginLogo, 'http') === 0) ? $loginLogo : RACINE . ltrim($loginLogo, '/'); ?>
            <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" style="max-height: 50px; max-width: 180px; object-fit: contain;">
          <?php else: ?>
            <?= LOGO ?>
          <?php endif; ?>
        </div>
        <h1 style="font-size: 20px; font-weight: 800; color: #0F172A; margin-top: 10px;">Système d'Information GEICG</h1>
        <p style="color: #64748B; font-size: 13px;">Connexion à l'administration de l'établissement</p>
      </div>

      <div class="auth-body">
        <form class="formConnexion" method="POST">
          <?= Validator::csrfField() ?>

          <div class="form-field">
            <label for="login">Email ou Téléphone</label>
            <div class="input-with-icon">
              <span class="input-icon"><?= Validator::icon('user'); ?></span>
              <input type="text" class="form-control" id="login" name="login" placeholder="Ex: admin@gmail.com ou 0102030405" required autofocus>
            </div>
          </div>

          <div class="form-field">
            <label for="password">Mot de passe</label>
            <div class="input-with-icon">
              <span class="input-icon"><?= Validator::icon('lock'); ?></span>
              <input type="password" class="form-control" id="password" name="password" required>
              <button type="button" class="password-toggle" id="togglePassword" aria-label="Afficher le mot de passe">
                <i data-lucide="eye" id="eyeIcon"></i>
              </button>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-block btn_actions btnConnexion" style="background: #1E3A5F; border-color: #1E3A5F;">
            <span class="btn-text">
              <i data-lucide="log-in"></i>
              Se connecter
            </span>
          </button>
        </form>
      </div>
      <div class="auth-footer">
        &copy; 2026 GEICG - Grande École
      </div>
    </div>
  </main>
</div>

<?php 
require_once __DIR__ . '/../../public/inc/footer-link.php';
?>

