<?php
  // Récupération des années académiques pour le sélecteur rapide
  $activeAnneeLibelle = $_SESSION['annee_active_libelle'] ?? '2025-2026';
?>
<?php if (!empty($_SESSION['flash_success'])): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      if (typeof showToast === 'function') {
        showToast(<?= json_encode($_SESSION['flash_success']) ?>, 'success');
      }
    });
  </script>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      if (typeof showToast === 'function') {
        showToast(<?= json_encode($_SESSION['flash_error']) ?>, 'error');
      }
    });
  </script>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<header class="topbar">
    <div class="topbar-left">
        <button class="btn-icon mobile-menu-btn" id="mobileMenuBtn" title="Menu mobile">
            <i data-lucide="menu"></i>
        </button>
        
        <div class="search-wrapper search-wrapper--desktop">
            <div class="search-box">
                <i data-lucide="search"></i>
                <input type="text" id="globalSearch" placeholder="Rechercher clients, souscriptions, packs, cotisations..." autocomplete="off">
                <button class="search-clear" id="searchClear" type="button">
                    <i data-lucide="x" style="width:14px;height:14px;"></i>
                </button>
                <span class="search-shortcut" id="searchShortcut">Ctrl K</span>
            </div>
            <div class="search-results" id="searchResults"></div>
        </div>
        <button class="btn-icon search-toggle" id="searchToggle" title="Rechercher">
            <i data-lucide="search"></i>
        </button>
        <div class="search-wrapper search-wrapper--mobile" id="searchMobile">
            <div class="search-box">
                <i data-lucide="search"></i>
                <input type="text" id="globalSearchMobile" placeholder="Rechercher..." autocomplete="off">
                <button class="search-clear" id="searchClearMobile" type="button">
                    <i data-lucide="x" style="width:14px;height:14px;"></i>
                </button>
            </div>
            <div class="search-results" id="searchResultsMobile"></div>
        </div>
    </div>
    <div class="topbar-actions">
        <div class="quick-actions" style="position: relative;">
            <button class="btn-icon" id="quickActionsBtn" title="Actions rapides">
                <i data-lucide="zap"></i>
            </button>
            <div class="dropdown-panel" id="quickActionsPanel">
                <div class="dropdown-header">
                    <h3>Raccourcis Olive Service</h3>
                </div>
                <div class="dropdown-grid">
                    <a href="<?= RACINE ?>souscription/wizard" class="dropdown-card">
                        <i data-lucide="file-plus"></i>
                        <span>Nouvelle Souscription</span>
                    </a>
                    <a href="<?= RACINE ?>cautisation-payment/search-form" class="dropdown-card">
                        <i data-lucide="wallet"></i>
                        <span>Collecter Cotisation</span>
                    </a>
                    <a href="<?= RACINE ?>caisse_commercial/formulaire" class="dropdown-card">
                        <i data-lucide="lock"></i>
                        <span>Ma Caisse</span>
                    </a>
                    <a href="<?= RACINE ?>client/list" class="dropdown-card">
                        <i data-lucide="users"></i>
                        <span>Clients</span>
                    </a>
                    <a href="<?= RACINE ?>pack/list" class="dropdown-card">
                        <i data-lucide="boxes"></i>
                        <span>Packs Articles</span>
                    </a>
                    <a href="<?= RACINE ?>versement/list" class="dropdown-card">
                        <i data-lucide="send"></i>
                        <span>Versements</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="theme-wrapper" style="position: relative;">
            <button class="btn-icon" id="themeBtn" title="Personnaliser les couleurs & thèmes">
                <i data-lucide="palette"></i>
            </button>
            <div class="dropdown-panel theme-expanded-panel" id="themePanel" style="width: 520px; max-width: 95vw; max-height: 84vh; overflow-y: auto; padding: 24px; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
                <!-- En-tête -->
                <div class="theme-panel-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1.5px solid var(--border-color, #E2E8F0);">
                    <div>
                        <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: var(--text-primary, #1E293B); display: flex; align-items: center; gap: 10px;">
                            <i data-lucide="palette" style="width:20px; height:20px; color: var(--primary-color);"></i> Personnalisation & Apparence
                        </h3>
                        <p style="margin: 3px 0 0 30px; font-size: 12px; color: var(--text-secondary, #64748B);">Adaptez les couleurs, polices et affichages à votre confort</p>
                    </div>
                    <button class="theme-panel-close" id="themePanelClose" style="background: var(--bg-secondary, #F1F5F9); border: 1px solid var(--border-color, #CBD5E1); border-radius: 8px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #64748B; transition: all 0.2s ease;">
                        <i data-lucide="x" style="width:18px;height:18px;"></i>
                    </button>
                </div>

                <!-- GRILLE 2 COLONNES POUR LES COULEURS DE STRUCTURE -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 16px;">
                    <!-- Thème Principal / Accents École -->
                    <div style="background: var(--bg-secondary, #F8FAFC); border: 1px solid var(--border-color, #E2E8F0); border-radius: 12px; padding: 14px;">
                        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary, #64748B); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="award" style="width: 14px; height: 14px; color: var(--primary-color);"></i> Couleur Institutionnelle
                        </div>
                        <div class="theme-options" id="primaryOptions" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                            <div class="theme-option active" data-category="primary" data-value="navy" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 10px; cursor: pointer; border: 1.5px solid #CBD5E1; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #18385F; border: 2px solid #FFFFFF; box-shadow: 0 1px 3px rgba(0,0,0,0.25); flex-shrink: 0;"></div>
                                <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Marine</span>
                            </div>
                            <div class="theme-option" data-category="primary" data-value="bordeaux" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 10px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #5C0808; border: 2px solid #FFFFFF; box-shadow: 0 1px 3px rgba(0,0,0,0.25); flex-shrink: 0;"></div>
                                <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Bordeaux</span>
                            </div>
                            <div class="theme-option" data-category="primary" data-value="royal" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 10px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #2563EB; border: 2px solid #FFFFFF; box-shadow: 0 1px 3px rgba(0,0,0,0.25); flex-shrink: 0;"></div>
                                <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Royal</span>
                            </div>
                            <div class="theme-option" data-category="primary" data-value="emerald" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 10px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #047857; border: 2px solid #FFFFFF; box-shadow: 0 1px 3px rgba(0,0,0,0.25); flex-shrink: 0;"></div>
                                <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Émeraude</span>
                            </div>
                        </div>
                    </div>

                    <!-- Mode Sombre / Clair Global -->
                    <div style="background: var(--bg-secondary, #F8FAFC); border: 1px solid var(--border-color, #E2E8F0); border-radius: 12px; padding: 14px;">
                        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary, #64748B); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="sun-moon" style="width: 14px; height: 14px; color: var(--primary-color);"></i> Mode D'Affichage
                        </div>
                        <div class="theme-options" id="contentOptions" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                            <div class="theme-option active" data-category="content" data-value="light" style="display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 12px 8px; border-radius: 10px; cursor: pointer; border: 1.5px solid #CBD5E1; background: #FFFFFF;">
                                <i data-lucide="sun" style="width: 20px; height: 20px; color: #F59E0B;"></i>
                                <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Mode Clair</span>
                            </div>
                            <div class="theme-option" data-category="content" data-value="dark" style="display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 12px 8px; border-radius: 10px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <i data-lucide="moon" style="width: 20px; height: 20px; color: #6366F1;"></i>
                                <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Dark Mode</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION TYPOGRAPHIE & TAILLE -->
                <div style="background: var(--bg-secondary, #F8FAFC); border: 1px solid var(--border-color, #E2E8F0); border-radius: 12px; padding: 14px; margin-bottom: 16px;">
                    <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary, #64748B); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="type" style="width: 14px; height: 14px; color: var(--primary-color);"></i> Police & Typographie
                    </div>
                    <div class="theme-options" id="fontOptions" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 8px; margin-bottom: 12px;">
                        <div class="theme-option active" data-category="font" data-value="inter" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 10px; cursor: pointer; border: 1.5px solid #CBD5E1; background: #FFFFFF; font-family: 'Inter', sans-serif;">
                            <span style="font-size: 15px; font-weight: 800; color: var(--primary-color);">Aa</span>
                            <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Inter</span>
                        </div>
                        <div class="theme-option" data-category="font" data-value="poppins" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 10px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF; font-family: 'Poppins', sans-serif;">
                            <span style="font-size: 15px; font-weight: 800; color: var(--primary-color);">Aa</span>
                            <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Poppins</span>
                        </div>
                        <div class="theme-option" data-category="font" data-value="roboto" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 10px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF; font-family: 'Roboto', sans-serif;">
                            <span style="font-size: 15px; font-weight: 800; color: var(--primary-color);">Aa</span>
                            <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Roboto</span>
                        </div>
                        <div class="theme-option" data-category="font" data-value="merriweather" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 10px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF; font-family: 'Merriweather', serif;">
                            <span style="font-size: 15px; font-weight: 800; color: var(--primary-color);">Aa</span>
                            <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Serif</span>
                        </div>
                    </div>

                    <!-- Taille & Densité -->
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; padding-top: 10px; border-top: 1px dashed var(--border-color, #E2E8F0);">
                        <span style="font-size: 12px; font-weight: 700; color: #475569;">Densité d'affichage :</span>
                        <div class="theme-options" id="fontSizeOptions" style="display: flex; gap: 8px;">
                            <div class="theme-option" data-category="fontsize" data-value="small" style="padding: 6px 12px; border-radius: 8px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Compact (13px)</span>
                            </div>
                            <div class="theme-option active" data-category="fontsize" data-value="normal" style="padding: 6px 12px; border-radius: 8px; cursor: pointer; border: 1.5px solid #CBD5E1; background: #FFFFFF;">
                                <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Normal (14px)</span>
                            </div>
                            <div class="theme-option" data-category="fontsize" data-value="large" style="padding: 6px 12px; border-radius: 8px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Confort (16px)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION BARRES DE NAVIGATION (TOPBAR & SIDEBAR) -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 16px;">
                    <!-- Barre Supérieure (Topbar) -->
                    <div style="background: var(--bg-secondary, #F8FAFC); border: 1px solid var(--border-color, #E2E8F0); border-radius: 12px; padding: 14px;">
                        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary, #64748B); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="layout" style="width: 14px; height: 14px; color: var(--primary-color);"></i> Barre Supérieure
                        </div>
                        <div class="theme-options" id="topbarOptions" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px;">
                            <div class="theme-option active" data-category="topbar" data-value="light" style="display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 6px 4px; border-radius: 8px; cursor: pointer; border: 1.5px solid #CBD5E1; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #FFFFFF; border: 2px solid #CBD5E1;"></div>
                                <span class="theme-option-label" style="font-size: 10px; font-weight: 700; color: #334155;">Claire</span>
                            </div>
                            <div class="theme-option" data-category="topbar" data-value="dark" style="display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 6px 4px; border-radius: 8px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #1F2937; border: 2px solid #FFFFFF; box-shadow: 0 1px 3px rgba(0,0,0,0.2);"></div>
                                <span class="theme-option-label" style="font-size: 10px; font-weight: 700; color: #334155;">Sombre</span>
                            </div>
                            <div class="theme-option" data-category="topbar" data-value="navy" style="display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 6px 4px; border-radius: 8px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #18385F; border: 2px solid #FFFFFF; box-shadow: 0 1px 3px rgba(0,0,0,0.2);"></div>
                                <span class="theme-option-label" style="font-size: 10px; font-weight: 700; color: #334155;">Marine</span>
                            </div>
                            <div class="theme-option" data-category="topbar" data-value="bordeaux" style="display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 6px 4px; border-radius: 8px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #5C0808; border: 2px solid #FFFFFF; box-shadow: 0 1px 3px rgba(0,0,0,0.2);"></div>
                                <span class="theme-option-label" style="font-size: 10px; font-weight: 700; color: #334155;">Bordeaux</span>
                            </div>
                        </div>
                    </div>

                    <!-- Barre Latérale (Sidebar) -->
                    <div style="background: var(--bg-secondary, #F8FAFC); border: 1px solid var(--border-color, #E2E8F0); border-radius: 12px; padding: 14px;">
                        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary, #64748B); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="sidebar" style="width: 14px; height: 14px; color: var(--primary-color);"></i> Menu Latéral
                        </div>
                        <div class="theme-options" id="sidebarOptions" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px;">
                            <div class="theme-option active" data-category="sidebar" data-value="light" style="display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 6px 4px; border-radius: 8px; cursor: pointer; border: 1.5px solid #CBD5E1; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #FFFFFF; border: 2px solid #CBD5E1;"></div>
                                <span class="theme-option-label" style="font-size: 10px; font-weight: 700; color: #334155;">Claire</span>
                            </div>
                            <div class="theme-option" data-category="sidebar" data-value="dark" style="display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 6px 4px; border-radius: 8px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #0F172A; border: 2px solid #FFFFFF; box-shadow: 0 1px 3px rgba(0,0,0,0.2);"></div>
                                <span class="theme-option-label" style="font-size: 10px; font-weight: 700; color: #334155;">Sombre</span>
                            </div>
                            <div class="theme-option" data-category="sidebar" data-value="navy" style="display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 6px 4px; border-radius: 8px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #18385F; border: 2px solid #FFFFFF; box-shadow: 0 1px 3px rgba(0,0,0,0.2);"></div>
                                <span class="theme-option-label" style="font-size: 10px; font-weight: 700; color: #334155;">Marine</span>
                            </div>
                            <div class="theme-option" data-category="sidebar" data-value="bordeaux" style="display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 6px 4px; border-radius: 8px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                                <div class="theme-swatch" style="width: 22px; height: 22px; border-radius: 50%; background: #5C0808; border: 2px solid #FFFFFF; box-shadow: 0 1px 3px rgba(0,0,0,0.2);"></div>
                                <span class="theme-option-label" style="font-size: 10px; font-weight: 700; color: #334155;">Bordeaux</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION ARRONDIS DES BORDURES -->
                <div style="background: var(--bg-secondary, #F8FAFC); border: 1px solid var(--border-color, #E2E8F0); border-radius: 12px; padding: 14px; margin-bottom: 20px;">
                    <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary, #64748B); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="square" style="width: 14px; height: 14px; color: var(--primary-color);"></i> Style des Arrondis (Cartes & Boutons)
                    </div>
                    <div class="theme-options" id="radiusOptions" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                        <div class="theme-option" data-category="radius" data-value="sharp" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 8px 12px; border-radius: 4px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                            <div style="width: 16px; height: 16px; border: 2px solid var(--primary-color); border-radius: 2px;"></div>
                            <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Carré (4px)</span>
                        </div>
                        <div class="theme-option active" data-category="radius" data-value="normal" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 8px 12px; border-radius: 10px; cursor: pointer; border: 1.5px solid #CBD5E1; background: #FFFFFF;">
                            <div style="width: 16px; height: 16px; border: 2px solid var(--primary-color); border-radius: 6px;"></div>
                            <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Moderne (10px)</span>
                        </div>
                        <div class="theme-option" data-category="radius" data-value="round" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 8px 12px; border-radius: 14px; cursor: pointer; border: 1.5px solid transparent; background: #FFFFFF;">
                            <div style="width: 16px; height: 16px; border: 2px solid var(--primary-color); border-radius: 12px;"></div>
                            <span class="theme-option-label" style="font-size: 11px; font-weight: 700; color: #334155;">Arrondi (16px)</span>
                        </div>
                    </div>
                </div>

                <!-- Bouton Réinitialiser -->
                <div style="text-align: center; padding-top: 6px;">
                    <button type="button" id="themeResetBtn" style="background: var(--bg-secondary, #F1F5F9); border: 1.5px dashed var(--border-color, #CBD5E1); border-radius: 10px; padding: 10px 16px; font-size: 12px; font-weight: 700; color: #64748B; cursor: pointer; width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s ease;">
                        <i data-lucide="rotate-ccw" style="width: 15px; height: 15px;"></i> Réinitialiser tous les paramètres par défaut
                    </button>
                </div>
            </div>
        </div>
        <div class="notification-wrapper">
            <button class="btn-icon" id="notificationBtn" title="Notifications" style="position: relative;">
                <i data-lucide="bell"></i>
                <?php if (isset($unreadNotifsCount) && $unreadNotifsCount > 0): ?>
                    <span class="badge" id="navNotifBadge"><?= $unreadNotifsCount > 99 ? '99+' : $unreadNotifsCount ?></span>
                <?php endif; ?>
            </button>
            <div class="dropdown-panel" id="notificationPanel" style="width: 360px; padding: 0; overflow: hidden; border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); border: 1px solid #E2E8F0;">
                <div class="dropdown-header" style="padding: 14px 16px; background: #F8FAFC; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #1E293B;">Notifications</h3>
                        <?php if (isset($unreadNotifsCount) && $unreadNotifsCount > 0): ?>
                            <span id="navUnreadTag" style="background: #ECFDF5; color: #059669; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 10px; border: 1px solid #A7F3D0;">
                                <?= $unreadNotifsCount ?> non lue<?= $unreadNotifsCount > 1 ? 's' : '' ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if (isset($unreadNotifsCount) && $unreadNotifsCount > 0): ?>
                        <button type="button" id="navMarkAllBtn" onclick="quickMarkAllNotifsRead(event)" style="background: none; border: none; font-size: 11px; font-weight: 600; color: #059669; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; padding: 0;">
                            <i data-lucide="check-check" style="width: 14px; height: 14px;"></i> Tout marquer lu
                        </button>
                    <?php endif; ?>
                </div>
                <div class="notification-list" style="max-height: 380px; overflow-y: auto;">
                    <?php if (empty($recentAdminNotifs)): ?>
                        <div style="padding: 32px 16px; text-align: center; color: #94A3B8; font-size: 13px;">
                            <i data-lucide="bell-off" style="width: 32px; height: 32px; margin: 0 auto 8px; stroke-width: 1.5; opacity: 0.5;"></i>
                            <p style="margin: 0; font-weight: 500;">Aucune notification pour le moment</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentAdminNotifs as $notif): 
                            $isUnread = empty($notif['lu_notification']);
                            $type = $notif['type_notification'] ?? 'cotisation';
                            $icon = 'bell';
                            $iconColor = '#64748B';
                            $iconBg = '#F1F5F9';

                            if ($type === 'cotisation') {
                                $icon = 'coins';
                                $iconColor = '#059669';
                                $iconBg = '#ECFDF5';
                            } elseif ($type === 'versement') {
                                $icon = 'arrow-up-right';
                                $iconColor = '#2563EB';
                                $iconBg = '#EFF6FF';
                            } elseif ($type === 'souscription') {
                                $icon = 'sparkles';
                                $iconColor = '#D97706';
                                $iconBg = '#FEF3C7';
                            }
                            $destUrl = !empty($notif['url_notification']) ? $notif['url_notification'] : 'javascript:void(0)';
                        ?>
                            <a href="<?= htmlspecialchars($destUrl) ?>" 
                               class="notification-card-item"
                               data-notif-id="<?= (int)$notif['id_notification'] ?>"
                               onclick="quickMarkNotifRead(event, <?= (int)$notif['id_notification'] ?>, '<?= htmlspecialchars($destUrl) ?>')"
                               style="display: flex; gap: 12px; padding: 12px 16px; border-bottom: 1px solid #F1F5F9; text-decoration: none; color: inherit; transition: background 0.15s ease; <?= $isUnread ? 'background: #F0FDF4; border-left: 3px solid #059669;' : 'background: #FFFFFF;' ?>">
                                <div style="width: 34px; height: 34px; border-radius: 8px; background: <?= $iconBg ?>; color: <?= $iconColor ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px;">
                                    <i data-lucide="<?= $icon ?>" style="width: 17px; height: 17px;"></i>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: baseline; gap: 6px;">
                                        <strong style="font-size: 12px; color: #1E293B; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: <?= $isUnread ? '700' : '600' ?>;">
                                            <?= htmlspecialchars($notif['titre_notification'] ?? 'Notification') ?>
                                        </strong>
                                        <?php if ($isUnread): ?>
                                            <span class="unread-dot" style="width: 7px; height: 7px; border-radius: 50%; background: #059669; flex-shrink: 0;"></span>
                                        <?php endif; ?>
                                    </div>
                                    <p style="margin: 3px 0 0; font-size: 11px; color: #475569; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?= htmlspecialchars($notif['message_notification'] ?? '') ?>
                                    </p>
                                    <span style="color: #94A3B8; font-size: 10px; display: block; margin-top: 4px;">
                                        <?= !empty($notif['created_at_notification']) ? date('d/m à H:i', strtotime($notif['created_at_notification'])) : '' ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div style="padding: 10px 16px; background: #F8FAFC; border-top: 1px solid #E2E8F0; text-align: center;">
                    <a href="<?= RACINE ?>notification/list" style="font-size: 12px; font-weight: 700; color: #1E3A5F; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        <span>Voir tout l'historique</span>
                        <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="profile-wrapper" style="position: relative;">
            <div class="admin-profile" id="profileBtn" style="cursor: pointer;">
                <span class="avatar-circle" style="width: 32px; height: 32px; border-radius: 50%; background: #1E3A5F; color: #FFF; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">
                    <?= strtoupper(substr($currentUserName ?? 'A', 0, 1)) ?>
                </span>
                <span><?= htmlspecialchars($currentUserName ?? 'Utilisateur') ?> <i data-lucide="chevron-down"></i></span>
            </div>
            <div class="dropdown-panel" id="profilePanel">
                <div class="profile-header">
                    <div>
                        <strong><?= htmlspecialchars($currentUserName ?? 'Utilisateur') ?></strong>
                        <small style="color: #64748B; display: block;"><?= htmlspecialchars($currentUserEmail ?? '') ?></small>
                        <?php 
                          $navRoles = $_SESSION[USERS_AUTH]['roles'] ?? [];
                          if (empty($navRoles) && !empty($_SESSION[USERS_AUTH]['role_code'])) {
                              $navRoles = [$_SESSION[USERS_AUTH]['role_code']];
                          }
                          if (!empty($navRoles)):
                        ?>
                          <div style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 6px;">
                            <?php foreach($navRoles as $nr): ?>
                              <span style="font-size: 10px; font-weight: 700; background: rgba(24, 56, 95, 0.08); color: var(--primary-color, #18385F); padding: 2px 6px; border-radius: 4px;">
                                <?= htmlspecialchars(str_replace('ROLE_', '', $nr)) ?>
                              </span>
                            <?php endforeach; ?>
                          </div>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="<?= RACINE ?>user/profil" class="dropdown-item"><i data-lucide="user"></i> Mon profil</a>
                <hr>
                <a href="<?= RACINE ?>user/decon" class="dropdown-item logout"><i data-lucide="log-out"></i> Déconnexion</a>
            </div>
        </div>
    </div>
</header>