<?php
  if (!isset($globalEtablissementLogo)) {
      try {
          $db = (new Database())->getCon();
          $stmt = $db->query("SELECT logo_etablissement, libelle_etablissement FROM etablissements ORDER BY id_etablissement ASC LIMIT 1");
          $etabRow = $stmt->fetch(PDO::FETCH_ASSOC);
          $globalEtablissementLogo = $etabRow['logo_etablissement'] ?? '';
          $globalEtablissementNom = $etabRow['libelle_etablissement'] ?? 'OLIVE SERVICE';
      } catch (Exception $e) {
          $globalEtablissementLogo = '';
          $globalEtablissementNom = 'OLIVE SERVICE';
      }
  }
  $currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

  // --- SYSTÈME D'AUTORISATIONS & RBAC DU SIDEBAR (OLIVE SERVICE 4 PROFILS) ---
  $userRoles = Context::roles();
  $userRoleCode = Context::role();
  $isSuperAdmin = Context::isSuperAdmin();
  $userPermissions = Context::permissions();

  $canAccess = function(array $requiredPerms = []) use ($isSuperAdmin, $userPermissions) {
      if ($isSuperAdmin) return true;
      if (in_array('*', $userPermissions, true)) return true;
      foreach ($requiredPerms as $perm) {
          if (in_array($perm, $userPermissions, true)) return true;
      }
      return false;
  };
?>
<style>
  /* --- BASE SIDEBAR STYLES --- */
  .sidebar {
    transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .sidebar-accordion-toggle {
    cursor: pointer;
    user-select: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px;
    margin: 4px 0;
    border-radius: 8px;
    font-weight: 700;
    color: #475569;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.2s ease;
  }
  .sidebar-accordion-toggle:hover {
    background: rgba(30, 58, 95, 0.05);
    color: var(--primary-color);
  }
  .sidebar-accordion-toggle .chevron-icon {
    width: 14px;
    height: 14px;
    transition: transform 0.25s ease;
  }
  .sidebar-accordion-toggle[aria-expanded="true"] .chevron-icon {
    transform: rotate(180deg);
  }
  .sidebar-accordion-toggle[aria-expanded="true"] {
    color: var(--primary-color);
  }
  .sidebar-nav .nav-section-items {
    padding-left: 6px;
    display: none;
  }
  .sidebar-nav .nav-section-items.show {
    display: block;
  }
  .sidebar-nav .nav-item.sub {
    font-size: 13px;
    padding: 8px 12px 8px 16px;
    border-left: 2px solid transparent;
    margin: 2px 0;
    transition: all 0.2s ease;
  }
  .sidebar-nav .nav-item.sub.active,
  .sidebar-nav .nav-item.sub:hover {
    border-left-color: var(--primary-color);
    background: rgba(30, 58, 95, 0.06);
    color: var(--primary-color);
    font-weight: 700;
  }

  .sidebar-academic-badge .mini-badge {
    display: none;
  }

  /* --- COMPACT MINI SIDEBAR (COLLAPSED STATE) --- */
  .sidebar.collapsed {
    width: 76px !important;
    min-width: 76px !important;
    max-width: 76px !important;
    overflow-x: hidden;
  }
  .sidebar.collapsed .sidebar-header {
    padding: 12px 6px !important;
    justify-content: center !important;
    min-height: 64px !important;
  }
  .sidebar.collapsed .logo {
    display: none !important;
  }
  .sidebar.collapsed .sidebar-toggle {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 38px !important;
    height: 38px !important;
    border-radius: 8px !important;
    background: #EFF6FF !important;
    color: var(--primary-color) !important;
    border: 1.5px solid #BFDBFE !important;
    margin: 0 auto !important;
    cursor: pointer !important;
  }
  .sidebar.collapsed .sidebar-toggle:hover {
    background: #DBEAFE !important;
  }
  
  .sidebar.collapsed .sidebar-academic-badge {
    padding: 6px 4px !important;
    margin: 6px 6px !important;
  }
  .sidebar.collapsed .sidebar-academic-badge .full-badge {
    display: none !important;
  }
  .sidebar.collapsed .sidebar-academic-badge .mini-badge {
    display: block !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    color: var(--primary-color) !important;
  }

  .sidebar.collapsed .sidebar-accordion-toggle {
    display: none !important;
  }
  .sidebar.collapsed .nav-section-items {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    padding: 0 !important;
  }
  .sidebar.collapsed .nav-section {
    padding: 6px 0 !important;
    margin: 4px 0 !important;
    border-top: 1px solid #E2E8F0 !important;
    width: 100% !important;
  }
  .sidebar.collapsed .nav-item {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 44px !important;
    height: 42px !important;
    margin: 3px auto !important;
    padding: 0 !important;
    border-radius: 8px !important;
    position: relative !important;
    border-left: none !important;
  }
  .sidebar.collapsed .nav-item span {
    display: none !important;
  }
  .sidebar.collapsed .nav-item i,
  .sidebar.collapsed .nav-item [data-lucide] {
    width: 20px !important;
    height: 20px !important;
    margin: 0 !important;
  }
  .sidebar.collapsed .nav-item.active {
    background: var(--primary-color) !important;
    color: #FFFFFF !important;
  }
  .sidebar.collapsed .nav-item.active i,
  .sidebar.collapsed .nav-item.active [data-lucide] {
    color: #FFFFFF !important;
  }

  /* Tooltip flottant au survol en mode réduit */
  .sidebar.collapsed .nav-item:hover::after {
    content: attr(data-title);
    position: fixed;
    left: 86px;
    background: #0F172A;
    color: #FFFFFF;
    font-size: 12px;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 6px;
    white-space: nowrap;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.3);
    z-index: 99999;
    pointer-events: none;
    line-height: 1.4;
  }

  .main-content.expanded {
    margin-left: 76px !important;
    width: calc(100% - 76px) !important;
    max-width: calc(100vw - 76px) !important;
  }
  .footer.expanded {
    margin-left: 76px !important;
    width: calc(100% - 76px) !important;
  }
</style>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo" style="display: flex; align-items: center; justify-content: center; max-height: 48px;">
            <?php if (!empty($globalEtablissementLogo)): ?>
                <?php $logoUrl = (strpos($globalEtablissementLogo, 'http') === 0) ? $globalEtablissementLogo : RACINE . ltrim($globalEtablissementLogo, '/'); ?>
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo Olive Service" style="max-height: 42px; max-width: 140px; object-fit: contain;">
            <?php else: ?>
                <span style="letter-spacing: 1px; color: #059669; font-size: 20px; font-weight: 800;">
                    OLIVE SERVICE
                </span>
            <?php endif; ?>
        </div>
        <button class="sidebar-toggle" id="sidebarToggle" title="Réduire / Déployer le menu">
            <i data-lucide="menu"></i>
        </button>
    </div>

    <!-- Badges Session / Année Active -->
    <div class="sidebar-academic-badge p-2 mx-2 my-2 rounded bg-light border text-center">
        <div class="full-badge">
            <div class="text-uppercase text-muted" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px;">Profil : <?= htmlspecialchars($userRoleCode) ?></div>
            <div class="fw-bold text-success" style="font-size: 13px;">
                <?= htmlspecialchars($_SESSION['annee_active_libelle'] ?? 'Session Active') ?>
            </div>
        </div>
        <div class="mini-badge" title="Session Active">
            OLIVE
        </div>
    </div>

    <nav class="sidebar-nav">
        <!-- ACCUEIL (Visible pour tous) -->
        <a href="<?= RACINE ?>" class="nav-item <?= in_array($currentUri, [RACINE, RACINE . 'public/', '/', '/public/', '/oliveservice/', '/oliveservice/public/', '/geicg/', '/geicg/public/'], true) ? 'active' : '' ?>" data-title="Tableau de bord">
            <i data-lucide="layout-dashboard"></i> <span>Tableau de bord</span>
        </a>

        <!-- === MODULE COMMERCIAL (COMMERCIAL) === -->
        <?php if ($canAccess(['COMMERCIAL_VIEW_OWN_CLIENTS', 'COMMERCIAL_ADD_CLIENT', 'COMMERCIAL_ADD_SOUSCRIPTION', 'COMMERCIAL_COLLECT_COTISATION'])): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-commercial" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="briefcase" style="width: 16px; height: 16px; color: #059669;"></i> <span>Espace Commercial</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-commercial">
                <a href="<?= RACINE ?>client/list" class="nav-item sub <?= strpos($currentUri, '/client/') !== false ? 'active' : '' ?>" data-title="Mes Clients">
                    <i data-lucide="users"></i> <span>Mes Clients</span>
                </a>
                <a href="<?= RACINE ?>souscription/list" class="nav-item sub <?= strpos($currentUri, '/souscription/list') !== false ? 'active' : '' ?>" data-title="Mes Souscriptions">
                    <i data-lucide="file-text"></i> <span>Mes Souscriptions</span>
                </a>
                <a href="<?= RACINE ?>souscription/wizard" class="nav-item sub <?= strpos($currentUri, '/souscription/wizard') !== false ? 'active' : '' ?>" data-title="Nouvelle Souscription">
                    <i data-lucide="file-plus"></i> <span>Nouvelle Souscription</span>
                </a>
                <a href="<?= RACINE ?>cautisation-payment/search-form" class="nav-item sub <?= strpos($currentUri, '/cautisation-payment/') !== false ? 'active' : '' ?>" data-title="Collecter Cotisation">
                    <i data-lucide="wallet"></i> <span>Collecter Cotisation</span>
                </a>
            </div>
        </div>

        <!-- MODULE MA CAISSE & VERSEMENTS (COMMERCIAL) -->
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-decharger" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="wallet" style="width: 16px; height: 16px; color: #047857;"></i> <span>Ma Caisse & Verser</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-decharger">
                <a href="<?= RACINE ?>caisse_commercial/formulaire" class="nav-item sub <?= strpos($currentUri, '/caisse_commercial/') !== false ? 'active' : '' ?>" data-title="Ma Caisse Journalière">
                    <i data-lucide="lock"></i> <span>Ma Caisse Journalière</span>
                </a>
                <a href="<?= RACINE ?>versement/formulaire" class="nav-item sub <?= strpos($currentUri, '/versement/formulaire') !== false ? 'active' : '' ?>" data-title="Faire un Versement">
                    <i data-lucide="send"></i> <span>Faire un Versement</span>
                </a>
                <a href="<?= RACINE ?>versement/list" class="nav-item sub <?= strpos($currentUri, '/versement/list') !== false ? 'active' : '' ?>" data-title="Mes Versements">
                    <i data-lucide="history"></i> <span>Mes Versements</span>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- === MODULE GESTIONNAIRE : CATALOGUE ARTICLES & PACKS === -->
        <?php if ($canAccess(['GESTIONNAIRE_MANAGE_PACKS', 'GESTIONNAIRE_MANAGE_ARTICLES', 'GESTIONNAIRE_MANAGE_CATEGORIE_PACKS'])): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-catalogue" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="package" style="width: 16px; height: 16px; color: #2563EB;"></i> <span>Catalogue & Packs</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-catalogue">
                <a href="<?= RACINE ?>article/list" class="nav-item sub <?= strpos($currentUri, '/article/') !== false ? 'active' : '' ?>" data-title="Articles Produits">
                    <i data-lucide="shopping-bag"></i> <span>Articles Produits</span>
                </a>
                <a href="<?= RACINE ?>categorie_pack/list" class="nav-item sub <?= strpos($currentUri, '/categorie_pack/') !== false ? 'active' : '' ?>" data-title="Catégories de Packs">
                    <i data-lucide="tags"></i> <span>Catégories Packs</span>
                </a>
                <a href="<?= RACINE ?>pack/list" class="nav-item sub <?= strpos($currentUri, '/pack/') !== false ? 'active' : '' ?>" data-title="Packs d'Articles">
                    <i data-lucide="boxes"></i> <span>Packs d'Articles</span>
                </a>
            </div>
        </div>

        <!-- MODULE GESTIONNAIRE : LOGISTIQUE & DISTRIBUTIONS -->
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-distribution" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="truck" style="width: 16px; height: 16px; color: #D97706;"></i> <span>Logistique & Retraits</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-distribution">
                <a href="<?= RACINE ?>distribution/list" class="nav-item sub <?= strpos($currentUri, '/distribution/') !== false ? 'active' : '' ?>" data-title="Distributions Packs">
                    <i data-lucide="package-check"></i> <span>Distributions Packs</span>
                </a>
            </div>
        </div>

        <!-- MODULE GESTIONNAIRE : RECRUTEMENT COMMERCIAL -->
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-recrutement" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="user-plus" style="width: 16px; height: 16px; color: #10B981;"></i> <span>Recrutement Commercial</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-recrutement">
                <a href="<?= RACINE ?>user/formulaire" class="nav-item sub <?= strpos($currentUri, '/user/formulaire') !== false ? 'active' : '' ?>" data-title="Nouveau Commercial">
                    <i data-lucide="user-plus"></i> <span>Recruter un Commercial</span>
                </a>
                <a href="<?= RACINE ?>user/list" class="nav-item sub <?= (strpos($currentUri, '/user/list') !== false || strpos($currentUri, '/user/edition') !== false || strpos($currentUri, '/user/details') !== false) ? 'active' : '' ?>" data-title="Liste des Commerciaux">
                    <i data-lucide="users"></i> <span>Liste des Commerciaux</span>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- === MODULE FINANCE : FINANCES & CAISSE CENTRALE === -->
        <?php if ($canAccess(['FINANCE_VIEW_ALL_COTISATIONS', 'FINANCE_VALIDATE_VERSEMENT', 'FINANCE_MANAGE_DEPENSES'])): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-finance" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="credit-card" style="width: 16px; height: 16px; color: #DC2626;"></i> <span>Finances & Trésorerie</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-finance">
                <a href="<?= RACINE ?>souscription/list" class="nav-item sub <?= strpos($currentUri, '/souscription/list') !== false ? 'active' : '' ?>" data-title="Liste souscriptions">
                    <i data-lucide="file-text"></i> <span>Liste souscriptions</span>
                </a>
                <a href="<?= RACINE ?>client/list" class="nav-item sub <?= strpos($currentUri, '/client/list') !== false ? 'active' : '' ?>" data-title="Liste clients">
                    <i data-lucide="users"></i> <span>Liste clients</span>
                </a>
                <a href="<?= RACINE ?>cotisation/list" class="nav-item sub <?= strpos($currentUri, '/cotisation/list') !== false ? 'active' : '' ?>" data-title="Suivi des Cotisations">
                    <i data-lucide="search"></i> <span>Suivi Cotisations</span>
                </a>
                <a href="<?= RACINE ?>versement/list" class="nav-item sub <?= strpos($currentUri, '/versement/') !== false ? 'active' : '' ?>" data-title="Validation Versements">
                    <i data-lucide="arrow-down-left"></i> <span>Versements Commerciaux</span>
                </a>
                <a href="<?= RACINE ?>type_depense/list" class="nav-item sub <?= strpos($currentUri, '/type_depense/') !== false ? 'active' : '' ?>" data-title="Types de Dépenses">
                    <i data-lucide="tags"></i> <span>Types de Dépenses</span>
                </a>
                <a href="<?= RACINE ?>depense/list" class="nav-item sub <?= strpos($currentUri, '/depense/') !== false ? 'active' : '' ?>" data-title="Dépenses d'Exploitation">
                    <i data-lucide="arrow-up-right"></i> <span>Dépenses Exploitation</span>
                </a>
                <a href="<?= RACINE ?>caisse_commercial/list" class="nav-item sub <?= strpos($currentUri, '/caisse_commercial/list') !== false ? 'active' : '' ?>" data-title="Journal des Caisses">
                    <i data-lucide="archive"></i> <span>Journal des Caisses</span>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- === MODULE ADMINISTRATION & RBAC === -->
        <?php if ($canAccess(['ADMIN_MANAGE_USERS', 'ADMIN_MANAGE_ROLES', 'ADMIN_MANAGE_PERMISSIONS', 'ADMIN_MANAGE_ZONES', 'ADMIN_MANAGE_ETABLISSEMENTS'])): ?>
        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-admin" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="shield-check" style="width: 16px; height: 16px; color: #7C3AED;"></i> <span>Administration & Accès</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-admin">
                <a href="<?= RACINE ?>user/list" class="nav-item sub <?= strpos($currentUri, '/user/') !== false ? 'active' : '' ?>" data-title="Utilisateurs Système">
                    <i data-lucide="users"></i> <span>Utilisateurs Système</span>
                </a>
                <a href="<?= RACINE ?>fonction/list" class="nav-item sub <?= strpos($currentUri, '/fonction/') !== false ? 'active' : '' ?>" data-title="Fonctions Utilisateurs">
                    <i data-lucide="briefcase"></i> <span>Fonctions Utilisateurs</span>
                </a>
                <a href="<?= RACINE ?>role/list" class="nav-item sub <?= strpos($currentUri, '/role/') !== false ? 'active' : '' ?>" data-title="Rôles & Groupes">
                    <i data-lucide="shield"></i> <span>Rôles & Groupes RBAC</span>
                </a>
                <a href="<?= RACINE ?>permission/list" class="nav-item sub <?= strpos($currentUri, '/permission/') !== false ? 'active' : '' ?>" data-title="Permissions Granulaires">
                    <i data-lucide="key"></i> <span>Permissions Granulaires</span>
                </a>
            </div>
        </div>

        <div class="nav-section">
            <div class="sidebar-accordion-toggle" data-bs-target="#sec-config" aria-expanded="false">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="settings" style="width: 16px; height: 16px; color: #64748B;"></i> <span>Configuration Système</span>
                </div>
                <i data-lucide="chevron-down" class="chevron-icon"></i>
            </div>
            <div class="nav-section-items" id="sec-config">
                <a href="<?= RACINE ?>annee/list" class="nav-item sub <?= strpos($currentUri, '/annee/') !== false ? 'active' : '' ?>" data-title="Année d'Activité">
                    <i data-lucide="calendar"></i> <span>Années</span>
                </a>
                <a href="<?= RACINE ?>session/list" class="nav-item sub <?= strpos($currentUri, '/session/') !== false ? 'active' : '' ?>" data-title="Sessions de Cotisation">
                    <i data-lucide="clock"></i> <span>Sessions</span>
                </a>
                <a href="<?= RACINE ?>zone/list" class="nav-item sub <?= strpos($currentUri, '/zone/') !== false && strpos($currentUri, '/zone_commercial/') === false ? 'active' : '' ?>" data-title="Zones Géographiques">
                    <i data-lucide="map-pin"></i> <span>Zones Géographiques</span>
                </a>
                <a href="<?= RACINE ?>etablissement/config" class="nav-item sub <?= strpos($currentUri, '/etablissement/') !== false ? 'active' : '' ?>" data-title="Établissements">
                    <i data-lucide="landmark"></i> <span>Établissements</span>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </nav>
</aside>

<script>
(function() {
  function initSidebarAccordions() {
    var toggles = document.querySelectorAll('.sidebar-accordion-toggle');
    toggles.forEach(function(toggle) {
      // Éviter d'attacher plusieurs écouteurs
      if (toggle._hasAccordionListener) return;
      toggle._hasAccordionListener = true;

      toggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var targetId = this.getAttribute('data-bs-target');
        if (!targetId) return;
        var target = document.querySelector(targetId);
        if (!target) return;

        var isExpanded = this.getAttribute('aria-expanded') === 'true';
        if (isExpanded) {
          target.classList.remove('show');
          target.style.display = 'none';
          this.setAttribute('aria-expanded', 'false');
        } else {
          target.classList.add('show');
          target.style.display = 'block';
          this.setAttribute('aria-expanded', 'true');
        }
      });
    });

    // Déplier automatiquement la section contenant le lien actif
    var activeLink = document.querySelector('.sidebar-nav .nav-item.sub.active');
    if (activeLink) {
      var parentItems = activeLink.closest('.nav-section-items');
      if (parentItems) {
        parentItems.classList.add('show');
        parentItems.style.display = 'block';
        var parentToggle = parentItems.parentElement ? parentItems.parentElement.querySelector('.sidebar-accordion-toggle') : null;
        if (parentToggle) {
          parentToggle.setAttribute('aria-expanded', 'true');
        }
      }
    } else {
      var firstSection = document.querySelector('.sidebar-nav .nav-section-items');
      if (firstSection) {
        firstSection.classList.add('show');
        firstSection.style.display = 'block';
        var firstToggle = firstSection.parentElement ? firstSection.parentElement.querySelector('.sidebar-accordion-toggle') : null;
        if (firstToggle) {
          firstToggle.setAttribute('aria-expanded', 'true');
        }
      }
    }

    if (window.lucide) {
      try { lucide.createIcons(); } catch(e) {}
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSidebarAccordions);
  } else {
    initSidebarAccordions();
  }
})();
</script>
