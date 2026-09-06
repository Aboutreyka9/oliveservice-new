<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$item = $item ?? [];
$typeDepense = $typeDepense ?? [];
$montant = (float)($item['montant_depense'] ?? 0);
$isActif = (($item['statut_depense'] ?? '') === 'actif');
$nomAuteur = trim(($item['nom_user'] ?? '') . ' ' . ($item['prenom_user'] ?? ''));
$dateEngage = !empty($item['created_at_depense']) ? date('d/m/Y à H:i', strtotime($item['created_at_depense'])) : (!empty($item['date_depense']) ? date('d/m/Y', strtotime($item['date_depense'])) : '-');
$datePeriode = !empty($item['periode_depense']) ? date('d/m/Y', strtotime($item['periode_depense'])) : (!empty($item['date_depense']) ? date('d/m/Y', strtotime($item['date_depense'])) : '-');
$dateModif = !empty($item['updated_at_depense']) ? date('d/m/Y à H:i', strtotime($item['updated_at_depense'])) : null;

// Mode de règlement libellé
$modes = [
    'espece' => 'Espèce (Sortie de Caisse)',
    'mobile_money' => 'Mobile Money (Orange / Wave / MTN)',
    'virement' => 'Virement Bancaire / Chèque'
];
$modeLibelle = $modes[$item['mode_reglement'] ?? ''] ?? (ucfirst($item['mode_reglement'] ?? 'Espèces'));

// Logo établissement
$rawLogo = !empty($item['logo_etablissement']) ? $item['logo_etablissement'] : ($globalEtablissementLogo ?? '');
$logoEtabSrc = '';
if (!empty($rawLogo)) {
    $logoEtabSrc = (strpos($rawLogo, 'http') === 0) ? $rawLogo : RACINE . ltrim($rawLogo, '/');
}

// Détection de l'extension de la pièce justificative
$pj = $item['piece_joint'] ?? null;
$pjExt = $pj ? strtolower(pathinfo($pj, PATHINFO_EXTENSION)) : '';
$isImage = in_array($pjExt, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
$isPdf = ($pjExt === 'pdf');
?>
<style>
/* ================= STYLES FICHE DÉPENSE PREMIUM ================= */
.detail-page-wrap {
  padding: 24px;
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

/* En-tête */
.detail-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 24px;
}

.detail-header-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.detail-header-icon {
  width: 50px;
  height: 50px;
  border-radius: 14px;
  background: linear-gradient(135deg, #DC2626 0%, #1E3A5F 100%);
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 14px rgba(220, 38, 38, 0.25);
}

.detail-header-title h1 {
  font-size: 22px;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
  line-height: 1.2;
}

.detail-header-title p {
  color: #64748B;
  font-size: 13px;
  margin: 4px 0 0 0;
}

.detail-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  align-items: center;
}

.btn-header-back {
  background: #FFFFFF;
  border: 1px solid #CBD5E1;
  color: #334155;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;
  border-radius: 9px;
  padding: 10px 16px;
  text-decoration: none;
  font-size: 13px;
  transition: all 0.2s ease;
}

.btn-header-back:hover {
  background: #F1F5F9;
  color: #0F172A;
}

.btn-header-print {
  background: #F8FAFC;
  border: 1px solid #CBD5E1;
  color: #1E3A5F;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;
  border-radius: 9px;
  padding: 10px 16px;
  cursor: pointer;
  font-size: 13px;
  transition: all 0.2s ease;
}

.btn-header-print:hover {
  background: #EFF6FF;
  border-color: #93C5FD;
}

.btn-header-edit {
  background: #1E3A5F;
  border: 1px solid #1E3A5F;
  color: #FFFFFF;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;
  border-radius: 9px;
  padding: 10px 20px;
  text-decoration: none;
  font-size: 13px;
  box-shadow: 0 4px 10px rgba(30, 58, 95, 0.2);
  transition: all 0.2s ease;
}

.btn-header-edit:hover {
  background: #152B47;
  color: #FFFFFF;
  transform: translateY(-1px);
}

.btn-header-locked {
  background: #F1F5F9;
  border: 1px solid #E2E8F0;
  color: #94A3B8;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;
  border-radius: 9px;
  padding: 10px 18px;
  font-size: 13px;
  cursor: not-allowed;
}

/* BANNIÈRE SYNTHÈSE HERO */
.detail-hero-card {
  background: #FFFFFF;
  border-radius: 16px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
  padding: 24px 28px;
  margin-bottom: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 20px;
  position: relative;
  overflow: hidden;
}

.detail-hero-card::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 6px;
  background: <?= $isActif ? '#10B981' : '#F59E0B' ?>;
}

.hero-amount-label {
  font-size: 11px;
  font-weight: 800;
  color: #64748B;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  margin-bottom: 4px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.hero-amount-val {
  font-size: 32px;
  font-weight: 900;
  color: #DC2626;
  line-height: 1.1;
  letter-spacing: -0.5px;
}

.hero-status-wrap {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.badge-status-lg {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 18px;
  border-radius: 30px;
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 0.3px;
}

.badge-status-actif {
  background: #ECFDF5;
  color: #059669;
  border: 1px solid #A7F3D0;
}

.badge-status-inactif {
  background: #FFFBEB;
  color: #D97706;
  border: 1px solid #FDE68A;
}

/* GRILLE D'INFORMATIONS */
.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.info-card {
  background: #FFFFFF;
  border-radius: 14px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
  padding: 22px;
}

.info-card-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 18px;
  padding-bottom: 12px;
  border-bottom: 1px solid #F1F5F9;
}

.info-card-header h3 {
  font-size: 14px;
  font-weight: 800;
  color: #1E3A5F;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 10px 0;
  border-bottom: 1px dashed #F1F5F9;
  font-size: 13px;
}

.info-row:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.info-label {
  color: #64748B;
  font-weight: 600;
}

.info-val {
  color: #0F172A;
  font-weight: 700;
  text-align: right;
  max-width: 60%;
}

/* BLOC DESCRIPTION */
.desc-card {
  background: #FFFFFF;
  border-radius: 14px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
  padding: 22px;
  margin-bottom: 24px;
}

.desc-content {
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-left: 4px solid #1E3A5F;
  border-radius: 8px;
  padding: 16px 20px;
  font-size: 14px;
  color: #334155;
  line-height: 1.6;
}

/* BLOC PIÈCE JOINTE */
.pj-card {
  background: #FFFFFF;
  border-radius: 14px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
  padding: 22px;
  margin-bottom: 24px;
}

.pj-preview-img-wrap {
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid #CBD5E1;
  max-width: 480px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.06);
  background: #F8FAFC;
}

.pj-preview-img {
  width: 100%;
  height: auto;
  display: block;
  transition: transform 0.25s ease;
}

.pj-preview-img:hover {
  transform: scale(1.02);
}

.pj-actions-bar {
  display: flex;
  gap: 10px;
  margin-top: 14px;
  flex-wrap: wrap;
}

.btn-pj-view {
  background: #1E3A5F;
  color: #FFFFFF;
  border-radius: 8px;
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn-pj-view:hover {
  background: #152B47;
  color: #FFFFFF;
}

.btn-pj-dl {
  background: #F1F5F9;
  color: #334155;
  border: 1px solid #CBD5E1;
  border-radius: 8px;
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn-pj-dl:hover {
  background: #E2E8F0;
  color: #0F172A;
}

.empty-pj-box {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #F8FAFC;
  border: 1px dashed #CBD5E1;
  border-radius: 10px;
  padding: 18px 24px;
  color: #64748B;
  font-size: 13px;
}

/* BLOC TRAÇABILITÉ AUDIT */
.audit-card {
  background: #FFFFFF;
  border-radius: 14px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
  padding: 20px 24px;
}

.audit-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.audit-item {
  display: flex;
  align-items: center;
  gap: 10px;
}

.audit-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: #F1F5F9;
  color: #475569;
  display: flex;
  align-items: center;
  justify-content: center;
}

.audit-texts span {
  display: block;
  font-size: 11px;
  color: #64748B;
  font-weight: 600;
  text-transform: uppercase;
}

.audit-texts strong {
  display: block;
  font-size: 13px;
  color: #1E293B;
  margin-top: 2px;
}

/* BLOC EXCLUSIF IMPRESSION (MASQUÉ À L'ÉCRAN) */
.print-header-bon,
.print-signatures-bon,
.print-footer-bon {
  display: none;
}

/* GESTION COMPLÈTE DE L'IMPRESSION (BORDEREAU / BON DE DÉCAISSEMENT A4) */
@media print {
  @page {
    size: A4 portrait;
    margin: 8mm 10mm 8mm 10mm;
  }

  html, body {
    background: #FFFFFF !important;
    color: #0F172A !important;
    font-size: 10pt !important;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif !important;
    line-height: 1.3 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    overflow: visible !important;
  }

  /* Masquage strict de la navigation, barres d'outils et éléments web */
  aside, .sidebar, #sidebar,
  header.topbar, .topbar,
  footer.footer, #footer, .footer,
  .bottom-nav, #bottomNav,
  .modal-overlay, #genericModal, #confirmModal,
  .mobile-actions-overlay, #mobileActionOverlay,
  .dropdown-panel, #panelProfil,
  .detail-header, /* Remplacé par le print-header-bon officiel */
  .detail-actions,
  .btn-header-back, .btn-header-print, .btn-header-edit, .btn-header-locked,
  .pj-actions-bar,
  .empty-pj-box,
  .no-pj-print {
    display: none !important;
  }

  /* Réinitialisation complète des conteneurs de layout */
  .app-layout, .main-content, .content-wrapper, .detail-page-wrap {
    margin: 0 !important;
    margin-left: 0 !important;
    padding: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 100% !important;
    box-shadow: none !important;
    border: none !important;
    background: #FFFFFF !important;
    overflow: visible !important;
  }

  /* 1. EN-TÊTE OFFICIEL BORDEREAU DE CAISSE */
  .print-header-bon {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    border-bottom: 2px solid #0F172A !important;
    padding-bottom: 8px !important;
    margin-bottom: 10px !important;
    gap: 12px !important;
  }

  .print-header-left {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    flex: 1.2 !important;
  }

  .print-etab-logo {
    max-height: 48px !important;
    max-width: 75px !important;
    object-fit: contain !important;
  }

  .print-etab-info h2 {
    font-size: 12.5px !important;
    font-weight: 800 !important;
    color: #0F172A !important;
    margin: 0 0 2px 0 !important;
    text-transform: uppercase !important;
    line-height: 1.2 !important;
  }

  .print-etab-info p {
    font-size: 8.5px !important;
    color: #475569 !important;
    margin: 1px 0 !important;
    line-height: 1.2 !important;
  }

  .print-header-center {
    flex: 1.1 !important;
    text-align: center !important;
    padding: 0 6px !important;
  }

  .print-header-center .bon-badge-title {
    font-size: 13px !important;
    font-weight: 900 !important;
    color: #0F172A !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    border: 1.5px solid #0F172A !important;
    padding: 3px 12px !important;
    display: inline-block !important;
    border-radius: 4px !important;
    background: #F8FAFC !important;
  }

  .print-header-center .bon-num {
    font-size: 12px !important;
    font-weight: 800 !important;
    color: #DC2626 !important;
    margin-top: 2px !important;
    font-family: monospace !important;
  }

  .print-header-center .bon-subtitle {
    font-size: 8px !important;
    color: #64748B !important;
    text-transform: uppercase !important;
    margin-top: 1px !important;
    letter-spacing: 0.3px !important;
  }

  .print-header-right {
    flex: 0.9 !important;
    text-align: right !important;
    font-size: 8.5px !important;
    color: #475569 !important;
    line-height: 1.3 !important;
  }

  .print-header-right p {
    margin: 1px 0 !important;
  }

  /* 2. BANNIÈRE SYNTHÈSE HERO */
  .detail-hero-card {
    border: 1.5px solid #0F172A !important;
    border-radius: 6px !important;
    padding: 8px 14px !important;
    margin-bottom: 10px !important;
    box-shadow: none !important;
    background: #F8FAFC !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  .detail-hero-card::before {
    display: none !important;
  }

  .hero-amount-label {
    font-size: 9px !important;
    color: #475569 !important;
    margin-bottom: 2px !important;
  }

  .hero-amount-val {
    font-size: 20px !important;
    font-weight: 900 !important;
    color: #000000 !important;
    letter-spacing: -0.3px !important;
  }

  .badge-status-lg {
    padding: 4px 10px !important;
    font-size: 10px !important;
    border: 1px solid #475569 !important;
    background: #FFFFFF !important;
    color: #000000 !important;
    border-radius: 4px !important;
  }

  /* 3. GRILLE DES INFORMATIONS EN 3 COLONNES */
  .detail-grid {
    display: grid !important;
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 8px !important;
    margin-bottom: 8px !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  .info-card {
    border: 1px solid #94A3B8 !important;
    border-radius: 6px !important;
    padding: 8px 10px !important;
    box-shadow: none !important;
    background: #FFFFFF !important;
  }

  .info-card-header {
    margin-bottom: 4px !important;
    padding-bottom: 3px !important;
    border-bottom: 1px solid #E2E8F0 !important;
  }

  .info-card-header h3 {
    font-size: 9px !important;
    font-weight: 800 !important;
    color: #0F172A !important;
    letter-spacing: 0.3px !important;
  }

  .info-row {
    padding: 3px 0 !important;
    font-size: 9px !important;
    border-bottom: 1px dashed #E2E8F0 !important;
  }

  .info-label {
    color: #475569 !important;
    font-size: 8.5px !important;
    font-weight: 600 !important;
  }

  .info-val {
    color: #0F172A !important;
    font-size: 9px !important;
    font-weight: 700 !important;
  }

  /* 4. BLOC MOTIF & DESCRIPTION */
  .desc-card {
    border: 1px solid #94A3B8 !important;
    border-radius: 6px !important;
    padding: 8px 10px !important;
    box-shadow: none !important;
    margin-bottom: 8px !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  .desc-content {
    font-size: 9px !important;
    padding: 6px 10px !important;
    border: 1px solid #CBD5E1 !important;
    background: #FAFAFA !important;
    line-height: 1.35 !important;
    border-left: 3px solid #0F172A !important;
  }

  /* 5. BLOC PIÈCE JOINTE (SI PRÉSENTE) */
  .pj-card {
    border: 1px solid #94A3B8 !important;
    border-radius: 6px !important;
    padding: 8px 10px !important;
    box-shadow: none !important;
    margin-bottom: 8px !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  .pj-preview-img-wrap {
    max-width: 200px !important;
    max-height: 100px !important;
    border: 1px solid #CBD5E1 !important;
  }

  .pj-preview-img {
    max-height: 100px !important;
    object-fit: contain !important;
  }

  /* 6. VOLET TRAÇABILITÉ AUDIT */
  .audit-card {
    border: 1px solid #CBD5E1 !important;
    border-radius: 6px !important;
    padding: 6px 10px !important;
    box-shadow: none !important;
    margin-bottom: 10px !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  .audit-grid {
    display: flex !important;
    justify-content: space-between !important;
    gap: 10px !important;
  }

  .audit-item {
    gap: 4px !important;
  }

  .audit-icon {
    display: none !important;
  }

  .audit-texts span {
    font-size: 7.5px !important;
    color: #64748B !important;
    text-transform: uppercase !important;
  }

  .audit-texts strong {
    font-size: 9px !important;
    color: #0F172A !important;
  }

  /* 7. BLOC SIGNATURES OFFICIELLES (3 COLONNES) */
  .print-signatures-bon {
    display: grid !important;
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 10px !important;
    margin-top: 6px !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  .sign-box {
    border: 1.5px solid #0F172A !important;
    border-radius: 6px !important;
    padding: 6px 8px !important;
    min-height: 80px !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    background: #FFFFFF !important;
  }

  .sign-box-title {
    font-size: 9px !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    color: #0F172A !important;
    border-bottom: 1px solid #CBD5E1 !important;
    padding-bottom: 2px !important;
    margin-bottom: 3px !important;
  }

  .sign-box-desc {
    font-size: 8.5px !important;
    color: #334155 !important;
    line-height: 1.25 !important;
  }

  .sign-box-footer {
    font-size: 7.5px !important;
    color: #94A3B8 !important;
    border-top: 1px dotted #CBD5E1 !important;
    padding-top: 2px !important;
    text-align: right !important;
  }

  /* 8. PIED DE PAGE LÉGAL IMPRESSION */
  .print-footer-bon {
    display: block !important;
    text-align: center !important;
    font-size: 7.5px !important;
    color: #64748B !important;
    border-top: 1px solid #CBD5E1 !important;
    margin-top: 8px !important;
    padding-top: 3px !important;
  }
}
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="detail-page-wrap">
      
      <!-- EN-TÊTE OFFICIEL BORDEREAU DE DÉPENSE (VISIBLE UNIQUEMENT À L'IMPRESSION) -->
      <div class="print-header-bon">
        <div class="print-header-left">
          <?php if (!empty($logoEtabSrc)): ?>
            <img src="<?= htmlspecialchars($logoEtabSrc) ?>" alt="Logo" class="print-etab-logo">
          <?php endif; ?>
          <div class="print-etab-info">
            <h2><?= htmlspecialchars($item['libelle_etablissement'] ?? 'ÉTABLISSEMENT SCOLAIRE') ?></h2>
            <?php if (!empty($item['adresse_etablissement'])): ?>
              <p><i data-lucide="map-pin" style="width:11px;height:11px;"></i> <?= htmlspecialchars($item['adresse_etablissement']) ?></p>
            <?php endif; ?>
            <?php if (!empty($item['telephone_etablissement'])): ?>
              <p><i data-lucide="phone" style="width:11px;height:11px;"></i> Tél : <?= htmlspecialchars($item['telephone_etablissement']) ?></p>
            <?php endif; ?>
            <p>Zone : <?= htmlspecialchars($item['libelle_zone'] ?? '-') ?> &bull; Exercice : <?= htmlspecialchars($item['libelle_annee'] ?? '-') ?></p>
          </div>
        </div>

        <div class="print-header-center">
          <div class="bon-badge-title">BON DE DÉCAISSEMENT</div>
          <div class="bon-num">N° <?= htmlspecialchars($item['code_depense'] ?? '-') ?></div>
          <div class="bon-subtitle">Bordereau comptable de sortie de caisse</div>
        </div>

        <div class="print-header-right">
          <p><strong>Date d'édition :</strong> <?= date('d/m/Y à H:i') ?></p>
          <p><strong>Opérateur :</strong> <?= htmlspecialchars($_SESSION['nom'] ?? 'Administration') ?></p>
          <p><strong>Statut :</strong> <?= $isActif ? 'VALIDÉE & COMPTABILISÉE' : 'EN ATTENTE D\'APPROBATION' ?></p>
        </div>
      </div>

      <!-- EN-TÊTE DE LA FICHE -->
      <div class="detail-header">
        <div class="detail-header-left">
          <div class="detail-header-icon">
            <i data-lucide="receipt" style="width: 26px; height: 26px;"></i>
          </div>
          <div class="detail-header-title">
            <h1>
              <span>Fiche Dépense :</span>
              <code style="font-weight: 800; color: #DC2626; font-size: 20px; background: #FEF2F2; padding: 2px 10px; border-radius: 6px; border: 1px solid #FECACA;"><?= htmlspecialchars($item['code_depense'] ?? '-') ?></code>
            </h1>
            <p>Bordereau comptable de décaissment & ordonnancement des charges d'exploitation</p>
          </div>
        </div>

        <div class="detail-actions">
          <a href="<?= RACINE ?>depense/list" class="btn-header-back">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour aux dépenses
          </a>
          <button type="button" onclick="window.print()" class="btn-header-print" title="Imprimer le bordereau de dépense">
            <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Imprimer
          </button>
          <a href="<?= RACINE ?>depense/edition/<?= $encryptedId ?>" class="btn-header-edit" title="Modifier cette dépense">
            <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i> Modifier Dépense
          </a>
        </div>
      </div>

      <!-- BANNIÈRE HERO : MONTANT & STATUT -->
      <div class="detail-hero-card">
        <div>
          <div class="hero-amount-label">
            <i data-lucide="trending-down" style="width: 14px; height: 14px; color: #DC2626;"></i>
            <span>Montant Total Décaissé</span>
          </div>
          <div class="hero-amount-val">
            -<?= number_format(abs($montant), 0, ',', ' ') ?> <span style="font-size: 18px; font-weight: 800;">FCFA</span>
          </div>
        </div>

        <div class="hero-status-wrap">
          <div style="text-align: right; margin-right: 12px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block;">Statut de la Dépense</span>
            <span style="font-size: 12px; color: #94A3B8;">Contrôle caisse</span>
          </div>
          <?php if ($isActif): ?>
            <div class="badge-status-lg badge-status-actif">
              <i data-lucide="check-circle-2" style="width: 18px; height: 18px;"></i>
              <span>Actif (Validée & Comptabilisée)</span>
            </div>
          <?php else: ?>
            <div class="badge-status-lg badge-status-inactif">
              <i data-lucide="clock" style="width: 18px; height: 18px;"></i>
              <span>Inactif (En attente d'approbation)</span>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- GRILLE DES INFORMATIONS OPÉRATIONNELLES -->
      <div class="detail-grid">

        <!-- CARTE 1 : CATÉGORIE & RÈGLEMENT -->
        <div class="info-card">
          <div class="info-card-header">
            <i data-lucide="tags" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
            <h3>Catégorie & Mode de Règlement</h3>
          </div>
          <div class="info-row">
            <span class="info-label">Catégorie Dépense</span>
            <span class="info-val" style="color: #1E3A5F; font-weight: 800; font-size: 14px;">
              <?= htmlspecialchars($item['libelle_type_depense'] ?? ($typeDepense['libelle_type_depense'] ?? '-')) ?>
            </span>
          </div>
          <div class="info-row">
            <span class="info-label">Code Type</span>
            <span class="info-val">
              <code style="background: #F1F5F9; padding: 2px 6px; border-radius: 4px; font-size: 11px;"><?= htmlspecialchars($item['type_depense_code'] ?? '-') ?></code>
            </span>
          </div>
          <div class="info-row">
            <span class="info-label">Mode de Règlement</span>
            <span class="info-val" style="text-transform: uppercase; color: #0F172A;">
              <?= htmlspecialchars($modeLibelle) ?>
            </span>
          </div>
          <div class="info-row">
            <span class="info-label">Période / Date Déclarée</span>
            <span class="info-val" style="color: #0F172A;">
              <?= htmlspecialchars($datePeriode) ?>
            </span>
          </div>
        </div>

        <!-- CARTE 2 : CADRE ORGANISATIONNEL -->
        <div class="info-card">
          <div class="info-card-header">
            <i data-lucide="building-2" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
            <h3>Cadre Organisationnel</h3>
          </div>
          <div class="info-row">
            <span class="info-label">Établissement</span>
            <span class="info-val" style="font-size: 12px; line-height: 1.4;">
              <?= htmlspecialchars($item['libelle_etablissement'] ?? ($item['etablissement_code'] ?? '-')) ?>
            </span>
          </div>
          <div class="info-row">
            <span class="info-label">Zone d'Affectation</span>
            <span class="info-val">
              <span class="badge" style="background: #EFF6FF; color: #1D4ED8; font-weight: 700; border: 1px solid #BFDBFE;">
                <?= htmlspecialchars($item['libelle_zone'] ?? ($item['zone_code'] ?? '-')) ?>
              </span>
            </span>
          </div>
          <div class="info-row">
            <span class="info-label">Année d'Exercice</span>
            <span class="info-val" style="color: #1E3A5F; font-weight: 800;">
              <?= htmlspecialchars($item['libelle_annee'] ?? ($item['annee_code'] ?? '-')) ?>
            </span>
          </div>
        </div>

        <!-- CARTE 3 : AUTEUR & RESPONSABILITÉ -->
        <div class="info-card">
          <div class="info-card-header">
            <i data-lucide="user-check" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
            <h3>Auteur de la Saisie</h3>
          </div>
          <div class="info-row">
            <span class="info-label">Agent Initiateur</span>
            <span class="info-val" style="color: #0F172A; font-weight: 800;">
              <?= htmlspecialchars($nomAuteur ?: 'Utilisateur Système') ?>
            </span>
          </div>
          <div class="info-row">
            <span class="info-label">Code Utilisateur</span>
            <span class="info-val">
              <code style="background: #F1F5F9; padding: 2px 6px; border-radius: 4px; font-size: 11px;"><?= htmlspecialchars($item['user_code'] ?? '-') ?></code>
            </span>
          </div>
          <div class="info-row">
            <span class="info-label">Contact Téléphonique</span>
            <span class="info-val">
              <?= htmlspecialchars($item['telephone_user'] ?? '-') ?>
            </span>
          </div>
          <div class="info-row">
            <span class="info-label">Date & Heure Saisie</span>
            <span class="info-val" style="color: #64748B;">
              <?= htmlspecialchars($dateEngage) ?>
            </span>
          </div>
        </div>

      </div>

      <!-- BLOC DESCRIPTION DÉTAILLÉE -->
      <div class="desc-card">
        <div class="info-card-header" style="margin-bottom: 14px;">
          <i data-lucide="file-text" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
          <h3>Motif & Description Détaillée du Frais</h3>
        </div>
        <div class="desc-content">
          <?= nl2br(htmlspecialchars($item['description_depense'] ?? ($item['motif_depense'] ?? 'Aucun motif renseigné pour cette dépense.'))) ?>
        </div>
      </div>

      <!-- BLOC PIÈCE JUSTIFICATIVE -->
      <div class="pj-card <?= empty($pj) ? 'no-pj-print' : '' ?>">
        <div class="info-card-header" style="margin-bottom: 16px;">
          <i data-lucide="paperclip" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
          <h3>Pièce Justificative (Facture, Reçu, Bon de Caisse)</h3>
        </div>

        <?php if (!empty($pj)): ?>
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
              <span class="badge" style="background: #1E3A5F; color: #FFFFFF; font-weight: 700; padding: 4px 10px; border-radius: 6px; font-size: 11px;">
                Fichier Joint : <?= strtoupper($pjExt) ?>
              </span>
              <span style="font-size: 13px; font-weight: 600; color: #334155; font-family: monospace;">
                <?= htmlspecialchars($pj) ?>
              </span>
            </div>

            <?php if ($isImage): ?>
              <!-- PRÉVISUALISATION IMAGE -->
              <div class="pj-preview-img-wrap">
                <a href="<?= RACINE ?>public/assets/images/depenses/<?= htmlspecialchars($pj) ?>" target="_blank" title="Cliquez pour agrandir">
                  <img src="<?= RACINE ?>public/assets/images/depenses/<?= htmlspecialchars($pj) ?>" alt="Facture / Reçu" class="pj-preview-img">
                </a>
              </div>
            <?php elseif ($isPdf): ?>
              <!-- PRÉVISUALISATION PDF -->
              <div style="display: flex; align-items: center; gap: 14px; background: #FEF2F2; border: 1px solid #FECACA; border-radius: 10px; padding: 16px 20px; max-width: 480px;">
                <div style="width: 44px; height: 44px; border-radius: 10px; background: #DC2626; color: white; display: flex; align-items: center; justify-content: center;">
                  <i data-lucide="file-text" style="width: 24px; height: 24px;"></i>
                </div>
                <div>
                  <strong style="color: #991B1B; font-size: 14px; display: block;">Document Facture PDF</strong>
                  <span style="color: #B91C1C; font-size: 12px;">Format PDF prêt à être consulté ou imprimé</span>
                </div>
              </div>
            <?php endif; ?>

            <div class="pj-actions-bar">
              <a href="<?= RACINE ?>public/assets/images/depenses/<?= htmlspecialchars($pj) ?>" target="_blank" class="btn-pj-view">
                <i data-lucide="external-link" style="width: 15px; height: 15px;"></i> Ouvrir le document
              </a>
              <a href="<?= RACINE ?>public/assets/images/depenses/<?= htmlspecialchars($pj) ?>" download class="btn-pj-dl">
                <i data-lucide="download" style="width: 15px; height: 15px;"></i> Télécharger
              </a>
            </div>
          </div>
        <?php else: ?>
          <div class="empty-pj-box">
            <i data-lucide="file-question" style="width: 24px; height: 24px; color: #94A3B8;"></i>
            <div>
              <strong style="color: #475569; display: block;">Aucune pièce justificative attachée</strong>
              <span>Ce frais a été enregistré sans téléchargement de facture ou reçu numérique (champ optionnel).</span>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- VOLET DE TRAÇABILITÉ & AUDIT -->
      <div class="audit-card">
        <div class="audit-grid">
          <div class="audit-item">
            <div class="audit-icon">
              <i data-lucide="calendar" style="width: 18px; height: 18px;"></i>
            </div>
            <div class="audit-texts">
              <span>Date d'enregistrement</span>
              <strong><?= htmlspecialchars($dateEngage) ?></strong>
            </div>
          </div>

          <div class="audit-item">
            <div class="audit-icon">
              <i data-lucide="refresh-cw" style="width: 18px; height: 18px;"></i>
            </div>
            <div class="audit-texts">
              <span>Dernière modification</span>
              <strong><?= $dateModif ? htmlspecialchars($dateModif) : 'Aucune modification' ?></strong>
            </div>
          </div>

          <div class="audit-item">
            <div class="audit-icon">
              <i data-lucide="shield-check" style="width: 18px; height: 18px;"></i>
            </div>
            <div class="audit-texts">
              <span>Niveau de contrôle</span>
              <strong><?= $isActif ? 'Approuvée & Validée' : 'En attente d\'ordonnancement' ?></strong>
            </div>
          </div>
        </div>
      </div>

      <!-- BLOC SIGNATURES OFFICIELLES (IMPRESSION SEULEMENT) -->
      <div class="print-signatures-bon">
        <div class="sign-box">
          <div class="sign-box-title">1. Initiateur / Agent Demandeur</div>
          <div class="sign-box-desc">
            <strong><?= htmlspecialchars($nomAuteur ?: 'Agent Initiateur') ?></strong><br>
            <span>Date : <?= htmlspecialchars($dateEngage) ?></span>
          </div>
          <div class="sign-box-footer">Signature & Date</div>
        </div>

        <div class="sign-box">
          <div class="sign-box-title">2. Caisse / Règlement</div>
          <div class="sign-box-desc">
            <strong>Mode : <?= htmlspecialchars($modeLibelle) ?></strong><br>
            <span>Montant : <?= number_format(abs($montant), 0, ',', ' ') ?> FCFA</span>
          </div>
          <div class="sign-box-footer">Cachet & Signature Caisse</div>
        </div>

        <div class="sign-box">
          <div class="sign-box-title">3. Direction / Ordonnancement</div>
          <div class="sign-box-desc">
            <strong>Statut : <?= $isActif ? 'APPROUVÉ & VALIDÉ' : 'EN ATTENTE DE VISA' ?></strong><br>
            <span>Mention "Bon à décaisser"</span>
          </div>
          <div class="sign-box-footer">Visa & Cachet Direction</div>
        </div>
      </div>

      <!-- PIED DE PAGE IMPRESSION (OFFICIEL) -->
      <div class="print-footer-bon">
        Document comptable officiel certifié généré par Olive Service le <?= date('d/m/Y à H:i') ?>. Ce bon justifie l'imputation et la sortie des fonds engagés ci-dessus.
      </div>

    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
