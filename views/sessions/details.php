<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$item = $item ?? [];
$annee = $annee ?? [];
$packs = $packs ?? [];
$souscriptions = $souscriptions ?? [];
$stats = $stats ?? [];
$libelleSession = $item['libelle_session'] ?? 'Session d\'Activité';
$totalCotise = (float)($stats['total_cotise'] ?? 0);
$totalPrevu = (float)($stats['total_prevu'] ?? 0);
$tauxProg = ($totalPrevu > 0) ? min(100, round(($totalCotise / $totalPrevu) * 100)) : 0;
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE LA FICHE SESSION -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="clock" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Fiche Session : <?= htmlspecialchars($libelleSession) ?>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Code : <code style="font-weight: 700; color: #1E3A5F; background: #F1F5F9; padding: 2px 8px; border-radius: 6px; font-size: 12px; border: 1px solid #CBD5E1;"><?= htmlspecialchars($item['code_session'] ?? '-') ?></code> &bull; Année : <strong><?= htmlspecialchars($annee['libelle_annee'] ?? '-') ?></strong>
            </p>
          </div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>session/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour aux sessions
          </a>
          <a href="<?= RACINE ?>session/edition/<?= $encryptedId ?>" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 10px 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
            <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier la Session
          </a>
        </div>
      </div>

      <!-- CARTE SYNTHÈSE DE LA SESSION -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
          <i data-lucide="activity" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Caractéristiques & Synthèse de la Session
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Durée Totale Prévue</span>
            <div style="font-size: 20px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= (int)($item['nombre_jour_session'] ?? 0) ?> Jours</div>
            <div style="font-size: 13px; color: #64748B; margin-top: 2px; font-weight: 500;">Du <?= !empty($item['date_debut_session']) ? date('d/m/Y', strtotime($item['date_debut_session'])) : '-' ?> au <?= !empty($item['date_fin_session']) ? date('d/m/Y', strtotime($item['date_fin_session'])) : '-' ?></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Souscriptions Active</span>
            <div style="font-size: 20px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= (int)($stats['total_souscriptions'] ?? 0) ?> Contrats</div>
            <div style="font-size: 13px; color: #64748B; margin-top: 2px; font-weight: 500;">Packs rattachés : <?= count($packs) ?></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Total Cotisé à ce jour</span>
            <div style="font-size: 20px; font-weight: 800; color: #059669; margin-top: 4px;"><?= number_format($totalCotise, 0, ',', ' ') ?> FCFA</div>
            <div style="font-size: 13px; color: #64748B; margin-top: 2px; font-weight: 600;">Taux de collecte : <strong style="color: #059669;"><?= $tauxProg ?>%</strong></div>
          </div>

          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Statut d'Ouverture</span>
            <div style="margin-top: 6px;">
              <?php if (($item['statut_session'] ?? '') === 'actif'): ?>
                <span style="display: inline-flex; align-items: center; gap: 4px; background:#ECFDF5; color:#059669; padding:6px 14px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #A7F3D0;">
                  <i data-lucide="check-circle" style="width: 14px; height: 14px;"></i> Session Active
                </span>
              <?php else: ?>
                <span style="display: inline-flex; align-items: center; gap: 4px; background:#FEE2E2; color:#B91C1C; padding:6px 14px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #FECACA;">
                  <i data-lucide="x-circle" style="width: 14px; height: 14px;"></i> Session Fermée
                </span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- CARTE PACKS ASSOSIÉS -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
          <i data-lucide="boxes" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Packs Associés à cette Session (<?= count($packs) ?>)
        </h3>

        <?php if (empty($packs)): ?>
          <p style="color: #94A3B8; text-align: center; padding: 24px 0; font-style: italic;">Aucun pack spécifiquement rattaché à cette session pour le moment.</p>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 2px solid #E2E8F0;">
                  <th style="padding: 12px; font-weight: 800; text-transform: uppercase; font-size: 11px;">Libellé Pack</th>
                  <th style="padding: 12px; text-align: right; font-weight: 800; text-transform: uppercase; font-size: 11px;">Cotisation / jour</th>
                  <th style="padding: 12px; text-align: center; font-weight: 800; text-transform: uppercase; font-size: 11px;">Jours Totaux</th>
                  <th style="padding: 12px; text-align: right; font-weight: 800; text-transform: uppercase; font-size: 11px;">Objectif Total</th>
                  <th style="padding: 12px; text-align: center; font-weight: 800; text-transform: uppercase; font-size: 11px;">Statut</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($packs as $p): ?>
                  <?php 
                    $cotis = (float)($p['prix_cotisation_pack'] ?? 0);
                    $jrs = (int)($p['nombre_jour_pack'] ?? 0);
                    $obj = $cotis * $jrs;
                  ?>
                  <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 12px; font-weight: 700; color: #0F172A;"><?= htmlspecialchars($p['libelle_pack']) ?></td>
                    <td style="padding: 12px; text-align: right; color: #059669; font-weight: 800;"><?= number_format($cotis, 0, ',', ' ') ?> FCFA</td>
                    <td style="padding: 12px; text-align: center;">
                      <span style="background:#F1F5F9; color:#1E3A5F; padding:4px 10px; border-radius:6px; font-weight:800; font-size:12px; border:1px solid #CBD5E1;"><?= $jrs ?> j</span>
                    </td>
                    <td style="padding: 12px; text-align: right; font-weight: 800; color: #1E3A5F;"><?= number_format($obj, 0, ',', ' ') ?> FCFA</td>
                    <td style="padding: 12px; text-align: center;">
                      <span style="display:inline-block; padding:4px 10px; border-radius:20px; font-weight:800; font-size:11px; text-transform:uppercase; <?= ($p['statut_pack'] ?? '') === 'actif' ? 'background:#ECFDF5; color:#059669; border:1px solid #A7F3D0;' : 'background:#F1F5F9; color:#64748B; border:1px solid #CBD5E1;' ?>">
                        <?= ucfirst($p['statut_pack'] ?? 'actif') ?>
                      </span>
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
