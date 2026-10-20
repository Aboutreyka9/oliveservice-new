<?php
require_once __DIR__ . '/../../public/inc/header.php';
$item = isset($item) ? $item : [];
$stats = isset($stats) ? $stats : [];
$sessions = isset($sessions) ? $sessions : [];
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="calendar" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Exercice : <?= htmlspecialchars($item['libelle_annee'] ?? 'Année') ?>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Code Exercice : <code style="font-weight: 700; color: #1E3A5F; background: #F1F5F9; padding: 2px 8px; border-radius: 6px; font-size: 12px; border: 1px solid #CBD5E1;"><?= htmlspecialchars($item['code_annee'] ?? '') ?></code>
            </p>
          </div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>annee/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour à la liste
          </a>
          <?php if (!empty($encryptedId) && Context::can('GESTIONNAIRE_MANAGE_ANNEES')): ?>
          <a href="<?= RACINE ?>annee/edition/<?= $encryptedId ?>" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 10px 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
            <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier l'année
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- CARTE SYNTHÈSE & STATISTIQUES (NAVY PREMIUM) -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); margin-bottom: 24px; width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
          <i data-lucide="activity" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Caractéristiques & Indicateurs de l'Exercice
        </h3>

        <!-- Grille de métriques -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 24px;">
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px;">
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Exercice</span>
            <div style="font-size: 20px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($item['libelle_annee'] ?? '-') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Code : <code><?= htmlspecialchars($item['code_annee'] ?? '-') ?></code></div>
          </div>

          <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 12px; padding: 18px;">
            <span style="font-size: 11px; font-weight: 700; color: #1E3A5F; text-transform: uppercase; letter-spacing: 0.5px;">Souscriptions Totales</span>
            <div style="font-size: 24px; font-weight: 800; color: #1E3A5F; margin-top: 4px;"><?= number_format((int)($stats['total_souscriptions'] ?? 0), 0, ',', ' ') ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px; font-weight: 500;"><?= (int)($stats['total_valides'] ?? 0) ?> active(s), <?= (int)($stats['total_soldees'] ?? 0) ?> soldée(s)</div>
          </div>

          <div style="background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 12px; padding: 18px;">
            <span style="font-size: 11px; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 0.5px;">Cotisations Encaissées</span>
            <div style="font-size: 22px; font-weight: 800; color: #059669; margin-top: 4px;"><?= number_format((float)($stats['total_cotise'] ?? 0), 0, ',', ' ') ?> <small style="font-size: 13px;">FCFA</small></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px; font-weight: 500;">Sur souscriptions actives</div>
          </div>

          <div style="background: #FAF5FF; border: 1px solid #E9D5FF; border-radius: 12px; padding: 18px;">
            <span style="font-size: 11px; font-weight: 700; color: #7E22CE; text-transform: uppercase; letter-spacing: 0.5px;">Sessions Ouvertes</span>
            <div style="font-size: 24px; font-weight: 800; color: #7E22CE; margin-top: 4px;"><?= count($sessions) ?></div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px; font-weight: 500;">Périodes de collecte</div>
          </div>
        </div>

        <!-- Détails de la ligne de la table DB annees -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; padding-top: 18px; border-top: 1px solid #F1F5F9; font-size: 13px;">
          <div>
            <strong style="color: #64748B; display:block; font-size:11px; text-transform:uppercase;">Date Début :</strong>
            <span style="font-weight: 700; color: #0F172A;"><?= !empty($item['date_debut_annee']) ? date('d/m/Y', strtotime($item['date_debut_annee'])) : 'Non définie' ?></span>
          </div>
          <div>
            <strong style="color: #64748B; display:block; font-size:11px; text-transform:uppercase;">Date Fin :</strong>
            <span style="font-weight: 700; color: #0F172A;"><?= !empty($item['date_fin_annee']) ? date('d/m/Y', strtotime($item['date_fin_annee'])) : 'Non définie' ?></span>
          </div>
          <div>
            <strong style="color: #64748B; display:block; font-size:11px; text-transform:uppercase;">Pénalité Reconduction :</strong>
            <span style="font-weight: 800; color: #D97706; background:#FFFBEB; padding:3px 10px; border-radius:6px; border:1px solid #FDE68A; display:inline-block; margin-top:2px;"><?= number_format((float)($item['penalite_annee'] ?? 0), 2) ?> %</span>
          </div>
          <div>
            <strong style="color: #64748B; display:block; font-size:11px; text-transform:uppercase;">Pénalité Restauration :</strong>
            <span style="font-weight: 800; color: #7C3AED; background:#FAF5FF; padding:3px 10px; border-radius:6px; border:1px solid #E9D5FF; display:inline-block; margin-top:2px;"><?= number_format((float)($item['penalite_restore'] ?? 0), 2) ?> %</span>
          </div>
          <div>
            <strong style="color: #64748B; display:block; font-size:11px; text-transform:uppercase;">Zone Commerciale :</strong>
            <span style="font-weight: 700; color: #0F172A;"><?= htmlspecialchars($item['libelle_zone'] ?? ($item['zone_code'] ?? 'Toutes zones')) ?></span>
          </div>
          <div>
            <strong style="color: #64748B; display:block; font-size:11px; text-transform:uppercase;">Établissement :</strong>
            <span style="font-weight: 700; color: #0F172A;"><?= htmlspecialchars($item['libelle_etablissement'] ?? ($item['etablissement_code'] ?? '-')) ?></span>
          </div>
          <div>
            <strong style="color: #64748B; display:block; font-size:11px; text-transform:uppercase;">Date de Création :</strong>
            <span style="font-weight: 600; color: #475569;"><?= !empty($item['created_at_annee']) ? date('d/m/Y H:i', strtotime($item['created_at_annee'])) : '-' ?></span>
          </div>
          <div>
            <strong style="color: #64748B; display:block; font-size:11px; text-transform:uppercase;">Statut :</strong>
            <?php 
              $st = strtolower($item['statut_annee'] ?? 'planifie');
              if ($st === 'actif'): 
            ?>
              <span style="display: inline-flex; align-items: center; gap: 4px; background:#ECFDF5; color:#059669; padding:3px 10px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #A7F3D0; margin-top:2px;">Actif (En cours)</span>
            <?php elseif ($st === 'planifie'): ?>
              <span style="display: inline-flex; align-items: center; gap: 4px; background:#EFF6FF; color:#1D4ED8; padding:3px 10px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #BFDBFE; margin-top:2px;">Planifié</span>
            <?php else: ?>
              <span style="display: inline-flex; align-items: center; gap: 4px; background:#F1F5F9; color:#475569; padding:3px 10px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #CBD5E1; margin-top:2px;">Clôturé</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- TABLEAU DES SESSIONS RATTACHÉES -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); width: 100%; box-sizing: border-box;">
        <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 18px 0; display: flex; align-items: center; gap: 8px;">
          <i data-lucide="layers" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Sessions Associées à cet Exercice
        </h3>

        <?php if (empty($sessions)): ?>
          <div style="padding: 24px; text-align: center; color: #94A3B8; font-size: 13px;">
            Aucune session n'a encore été configurée pour cet exercice.
          </div>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 2px solid #E2E8F0;">
                  <th style="padding: 10px 14px; font-weight: 800; font-size: 11px; text-transform: uppercase;">Code Session</th>
                  <th style="padding: 10px 14px; font-weight: 800; font-size: 11px; text-transform: uppercase;">Libellé Session</th>
                  <th style="padding: 10px 14px; font-weight: 800; font-size: 11px; text-transform: uppercase;">Période</th>
                  <th style="padding: 10px 14px; font-weight: 800; font-size: 11px; text-transform: uppercase; text-align: center;">Durée (Jours)</th>
                  <th style="padding: 10px 14px; font-weight: 800; font-size: 11px; text-transform: uppercase; text-align: center;">Statut</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($sessions as $s): ?>
                  <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 12px 14px; font-weight: 700; font-family: monospace; color: #1E3A5F;">
                      <?= htmlspecialchars($s['code_session'] ?? '-') ?>
                    </td>
                    <td style="padding: 12px 14px; font-weight: 700; color: #0F172A;">
                      <?= htmlspecialchars($s['libelle_session'] ?? '-') ?>
                    </td>
                    <td style="padding: 12px 14px; color: #475569;">
                      <?= !empty($s['date_debut_session']) ? date('d/m/Y', strtotime($s['date_debut_session'])) : '-' ?> &rarr; <?= !empty($s['date_fin_session']) ? date('d/m/Y', strtotime($s['date_fin_session'])) : '-' ?>
                    </td>
                    <td style="padding: 12px 14px; text-align: center; font-weight: 800; color: #1E3A5F;">
                      <?= (int)($s['nombre_jour_session'] ?? 0) ?> jours
                    </td>
                    <td style="padding: 12px 14px; text-align: center;">
                      <?php if (($s['statut_session'] ?? '') === 'actif'): ?>
                        <span style="background: #ECFDF5; color: #059669; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; border: 1px solid #A7F3D0;">Actif</span>
                      <?php else: ?>
                        <span style="background: #F1F5F9; color: #64748B; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">Inactif</span>
                      <?php endif; ?>
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
