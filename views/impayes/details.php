<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$montantDu = (float)($item['montant_restant'] ?? ($item['montant_du'] ?? 0));
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; box-sizing: border-box;">
      
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;">Fiche Relance Impayé & Dette Scolaire</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Étudiant : <strong><?= htmlspecialchars(($item['nom_etudiant'] ?? '') . ' ' . ($item['prenom_etudiant'] ?? '')) ?></strong> &bull; Réf : <strong><?= htmlspecialchars($item['code_relance'] ?? '-') ?></strong></p>
        </div>
        <div style="display: flex; gap: 12px;">
          <a href="<?= RACINE ?>impayes/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour aux impayés
          </a>
          <a href="<?= RACINE ?>paiement/formulaire" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
            <i data-lucide="credit-card" style="width: 18px; height: 18px;"></i> Encaisser le règlement
          </a>
        </div>
      </div>

      <!-- CARD 1 (COL-12) : SYNTHÈSE DE LA RELANCE -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 15px; font-weight: 800; color: #1E3A5F; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #EFF6FF; padding-bottom: 10px;">
          <i data-lucide="bell-ring" style="width: 18px; height: 18px;"></i> Bilan de la Relance Envoyée
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #991B1B; text-transform: uppercase;">Montant Dû / Arriéré</span>
            <div style="font-size: 24px; font-weight: 900; color: #DC2626; margin-top: 4px;"><?= number_format($montantDu, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Solde débiteur constaté</div>
          </div>

          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">Étudiant Débiteur</span>
            <div style="font-size: 16px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars(($item['nom_etudiant'] ?? '') . ' ' . ($item['prenom_etudiant'] ?? '')) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Matricule : <code><?= htmlspecialchars($item['matricule_etudiant'] ?? '-') ?></code></div>
          </div>

          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;">Classe & Année</span>
            <div style="font-size: 16px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= htmlspecialchars($item['libelle_classe'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Année : <?= htmlspecialchars($item['libelle_annee'] ?? '-') ?></div>
          </div>

          <div style="background: #FAF5FF; border: 1px solid #E9D5FF; border-radius: 10px; padding: 16px;">
            <span style="font-size: 11px; font-weight: 700; color: #7E22CE; text-transform: uppercase;">Canal & Expédition</span>
            <div style="font-size: 16px; font-weight: 800; color: #7E22CE; margin-top: 4px; text-transform: uppercase;"><?= htmlspecialchars($item['type_relance'] ?? ($item['canal_relance'] ?? 'SMS')) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Date : <?= !empty($item['date_relance']) ? date('d/m/Y', strtotime($item['date_relance'])) : date('d/m/Y') ?></div>
          </div>
        </div>

        <div style="padding-top: 18px; border-top: 1px solid #F1F5F9; margin-top: 18px;">
          <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; display: block; margin-bottom: 4px;">Contenu du message envoyé / Notification :</span>
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px 18px; font-size: 13px; color: #334155; line-height: 1.6;">
            <?= nl2br(htmlspecialchars($item['message_relance'] ?? ($item['motif_relance'] ?? 'Relance automatique pour régularisation de scolarité.'))) ?>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>
<script>$(document).ready(function() { if (window.lucide) lucide.createIcons(); });</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
