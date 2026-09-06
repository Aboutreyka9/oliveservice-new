<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$depenses = isset($depenses) ? $depenses : [];
$totalDepenses = $totalDepenses ?? 0;
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE LA FICHE TYPE DÉPENSE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="tag" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Fiche Ligne / Catégorie de Dépense
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Catégorie budgétaire : <strong><?= htmlspecialchars($item['libelle_type_depense'] ?? '-') ?></strong>
            </p>
          </div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>type_depense/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour aux catégories
          </a>
          <a href="<?= RACINE ?>type_depense/edition/<?= $encryptedId ?>" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 10px 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
            <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier la catégorie
          </a>
        </div>
      </div>

      <!-- CARTE 1 : SPÉCIFICATIONS DU TYPE DE CHARGE -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
          <i data-lucide="tag" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Spécifications du Type de Charge
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div style="background: #F8FAFC; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Intitulé du Poste</span>
            <div style="font-size: 18px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($item['libelle_type_depense'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px; font-weight: 500;">
              Code : <code style="font-weight: 700; color: #1E3A5F; background: #E2E8F0; padding: 2px 6px; border-radius: 4px;"><?= htmlspecialchars($item['code_type_depense'] ?? '-') ?></code>
            </div>
          </div>

          <div style="background: #FEF2F2; border-radius: 12px; padding: 18px; border: 1px solid #FECACA;">
            <span style="font-size: 11px; font-weight: 700; color: #991B1B; text-transform: uppercase; letter-spacing: 0.5px;">Cumul Engagé</span>
            <div style="font-size: 22px; font-weight: 800; color: #DC2626; margin-top: 4px;"><?= number_format($totalDepenses, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 4px; font-weight: 600;"><?= count($depenses) ?> dépense(s) validée(s)</div>
          </div>

          <div style="background: #F8FAFC; border-radius: 12px; padding: 18px; border: 1px solid #E2E8F0;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Statut</span>
            <div>
              <?php if (($item['statut_type_depense'] ?? '') === 'actif'): ?>
                <span style="display: inline-flex; align-items: center; gap: 6px; background:#ECFDF5; color:#059669; padding:6px 14px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #A7F3D0;">
                  <i data-lucide="check-circle" style="width: 14px; height: 14px;"></i> Actif
                </span>
              <?php else: ?>
                <span style="display: inline-flex; align-items: center; gap: 6px; background:#FEE2E2; color:#DC2626; padding:6px 14px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #FECACA;">
                  <i data-lucide="x-circle" style="width: 14px; height: 14px;"></i> Inactif
                </span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <?php if (!empty($item['description_type_depense'])): ?>
          <div style="margin-top: 24px; padding: 16px 20px; background: #F8FAFC; border-left: 4px solid #1E3A5F; border-radius: 8px; font-size: 13px; color: #334155; border: 1px solid #E2E8F0; border-left-width: 4px;">
            <strong style="color: #0F172A; font-weight: 800;">Description / Affectation :</strong>
            <div style="margin-top: 4px; line-height: 1.5; font-weight: 500;"><?= htmlspecialchars($item['description_type_depense']) ?></div>
          </div>
        <?php endif; ?>
      </div>

      <!-- CARTE 2 : HISTORIQUE DES DÉPENSES LIÉES -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
          <i data-lucide="receipt" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Dépenses Enregistrées sur cette Ligne (<?= count($depenses) ?>)
        </h3>

        <?php if (empty($depenses)): ?>
          <p style="color: #94A3B8; text-align: center; padding: 30px 0; font-style: italic; font-weight: 500;">Aucune dépense imputée à ce jour sur cette catégorie.</p>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 2px solid #E2E8F0;">
                  <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Réf Dépense</th>
                  <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Date</th>
                  <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Bénéficiaire / Motif</th>
                  <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: right;">Montant Décaissement</th>
                  <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: center;">Statut</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($depenses as $d): ?>
                  <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 12px 14px; font-family: monospace; font-weight: 700; color: #1E3A5F;">
                      <a href="<?= RACINE ?>depense/details/<?= $this->validator->crypter($d['id_depense']) ?>" style="color: #1E3A5F; text-decoration: none; font-weight: 800; background: #F1F5F9; padding: 4px 8px; border-radius: 6px; border: 1px solid #CBD5E1;">
                        <?= htmlspecialchars($d['code_depense'] ?? ('DEP-' . $d['id_depense'])) ?>
                      </a>
                    </td>
                    <td style="padding: 12px 14px; color: #334155; font-weight: 600;"><?= !empty($d['date_depense']) ? date('d/m/Y', strtotime($d['date_depense'])) : '-' ?></td>
                    <td style="padding: 12px 14px; color: #0F172A; font-weight: 700;"><?= htmlspecialchars($d['beneficiaire_depense'] ?? ($d['motif_depense'] ?? '-')) ?></td>
                    <td style="padding: 12px 14px; text-align: right; font-weight: 800; color: #DC2626;">
                      <?= number_format((float)($d['montant_depense'] ?? ($d['montant'] ?? 0)), 0, ',', ' ') ?> FCFA
                    </td>
                    <td style="padding: 12px 14px; text-align: center;">
                      <span style="background:#ECFDF5; color:#059669; border:1px solid #A7F3D0; padding:4px 10px; border-radius:20px; font-weight:800; font-size:11px;">Validé</span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
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
