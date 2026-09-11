<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="tag" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Catégorie : <?= htmlspecialchars($item['libelle_categorie_pack'] ?? '') ?>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Code Unique : <code style="font-weight: 700; color: #1E3A5F; background: #F1F5F9; padding: 2px 8px; border-radius: 6px; font-size: 12px; border: 1px solid #CBD5E1;"><?= htmlspecialchars($item['code_categorie_pack'] ?? '') ?></code>
            </p>
          </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>categorie_pack/list" class="btn" style="background: #FFFFFF; border: 1px solid #CBD5E1; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour aux catégories
          </a>
          <?php if (Context::can('GESTIONNAIRE_MANAGE_PACKS')): ?>
          <a href="<?= RACINE ?>pack/formulaire?categorie=<?= urlencode($item['code_categorie_pack'] ?? '') ?>" class="btn" style="background: #059669; color: white; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2); border: none;">
            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Ajouter un Pack
          </a>
          <?php endif; ?>
          <a href="<?= RACINE ?>categorie_pack/edition/<?= $encryptedId ?>" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 10px 20px; font-size: 13.5px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
            <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Éditer la catégorie
          </a>
        </div>
      </div>

      <!-- CARTE DE STATISTIQUES EN-TÊTE -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- KPI 1: Packs Associés -->
        <div style="background: #FFFFFF; border-radius: 14px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.03); display: flex; align-items: center; gap: 14px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(30, 58, 95, 0.1); color: #1E3A5F; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="boxes" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Packs Associés</div>
            <div style="font-size: 20px; font-weight: 800; color: #0F172A; margin-top: 2px;">
              <?= number_format($stats['total_packs'] ?? 0, 0, ',', ' ') ?>
            </div>
          </div>
        </div>

        <!-- KPI 2: Souscriptions Liées -->
        <div style="background: #FFFFFF; border-radius: 14px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.03); display: flex; align-items: center; gap: 14px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(5, 150, 105, 0.1); color: #059669; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="file-text" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Souscriptions Liées</div>
            <div style="font-size: 20px; font-weight: 800; color: #059669; margin-top: 2px;">
              <?= number_format($stats['total_souscriptions'] ?? 0, 0, ',', ' ') ?>
            </div>
          </div>
        </div>

        <!-- KPI 3: Total Cotisations Collectées -->
        <div style="background: #FFFFFF; border-radius: 14px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.03); display: flex; align-items: center; gap: 14px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(37, 99, 235, 0.1); color: #2563EB; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="coins" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Cotisations Collectées</div>
            <div style="font-size: 20px; font-weight: 800; color: #2563EB; margin-top: 2px;">
              <?= number_format($stats['total_cotisations'] ?? 0, 0, ',', ' ') ?> <span style="font-size: 12px; font-weight: 600;">FCFA</span>
            </div>
          </div>
        </div>

      </div>

      <!-- DISPOSITION GRILLE PRINCIPALE (DETAILS & EMBEDDED PACKS TABLE) -->
      <div style="display: grid; grid-template-columns: 340px 1fr; gap: 24px; align-items: start;">
        
        <!-- BLOC GAUCHE: FICHE D'INFORMATIONS DE LA CATÉGORIE -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);">
          <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1.5px solid #F1F5F9;">
            <i data-lucide="info" style="width: 18px; height: 18px; color: #1E3A5F;"></i>
            <h3 style="font-size: 15px; font-weight: 800; color: #0F172A; margin: 0;">Informations Générales</h3>
          </div>

          <div style="display: flex; flex-direction: column; gap: 18px;">
            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px;">Libellé de la catégorie</span>
              <span style="font-size: 15px; font-weight: 800; color: #0F172A;"><?= htmlspecialchars($item['libelle_categorie_pack'] ?? '-') ?></span>
            </div>

            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px;">Code Catégorie</span>
              <code style="font-weight: 700; color: #1E3A5F; background: #F1F5F9; padding: 3px 8px; border-radius: 6px; font-size: 12px; border: 1px solid #CBD5E1; display: inline-block;"><?= htmlspecialchars($item['code_categorie_pack'] ?? '-') ?></code>
            </div>

            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Statut d'activation</span>
              <?php $isActif = ($item['statut_categorie_pack'] ?? '') === 'actif'; ?>
              <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 800; padding: 4px 12px; border-radius: 20px; background: <?= $isActif ? '#ECFDF5' : '#F1F5F9' ?>; color: <?= $isActif ? '#059669' : '#64748B' ?>; border: 1px solid <?= $isActif ? '#A7F3D0' : '#E2E8F0' ?>;">
                <i data-lucide="<?= $isActif ? 'check-circle' : 'x-circle' ?>" style="width: 14px; height: 14px;"></i>
                <?= ucfirst($item['statut_categorie_pack'] ?? 'inactif') ?>
              </span>
            </div>

            <?php if (!empty($item['created_at_categorie_pack'])): ?>
            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px;">Créée le</span>
              <span style="font-size: 13px; font-weight: 600; color: #334155;">
                <i data-lucide="calendar" style="width: 13px; height: 13px; display: inline; vertical-align: -1px; margin-right: 4px; color: #64748B;"></i>
                <?= date('d/m/Y à H:i', strtotime($item['created_at_categorie_pack'])) ?>
              </span>
            </div>
            <?php endif; ?>

            <div style="padding-top: 12px; border-top: 1px solid #F1F5F9;">
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Description</span>
              <div style="font-size: 13px; font-weight: 500; color: #334155; line-height: 1.5; background: #F8FAFC; padding: 12px; border-radius: 8px; border: 1px solid #E2E8F0;">
                <?= !empty($item['description_categorie_pack']) ? nl2br(htmlspecialchars($item['description_categorie_pack'])) : '<em style="color:#94A3B8;">Aucune description renseignée.</em>' ?>
              </div>
            </div>
          </div>
        </div>

        <!-- BLOC DROITE: LISTE DES PACKS DE LA CATÉGORIE -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1.5px solid #F1F5F9; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
              <i data-lucide="boxes" style="width: 20px; height: 20px; color: #1E3A5F;"></i>
              <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0;">Packs appartenant à cette catégorie</h3>
            </div>
            <span style="font-size: 12px; font-weight: 700; color: #059669; background: #ECFDF5; padding: 4px 10px; border-radius: 20px; border: 1px solid #A7F3D0;">
              <?= count($packs) ?> pack<?= count($packs) > 1 ? 's' : '' ?> enregistré<?= count($packs) > 1 ? 's' : '' ?>
            </span>
          </div>

          <?php if (empty($packs)): ?>
            <div style="text-align: center; padding: 40px 20px; background: #F8FAFC; border-radius: 12px; border: 1px dashed #CBD5E1;">
              <i data-lucide="box" style="width: 40px; height: 40px; color: #94A3B8; margin-bottom: 10px; opacity: 0.6;"></i>
              <h4 style="font-size: 14px; font-weight: 700; color: #475569; margin: 0 0 4px 0;">Aucun pack associé</h4>
              <p style="font-size: 12.5px; color: #64748B; margin: 0 0 16px 0;">Aucun pack d'articles n'a encore été créé dans cette catégorie.</p>
              <?php if (Context::can('GESTIONNAIRE_MANAGE_PACKS')): ?>
              <a href="<?= RACINE ?>pack/formulaire?categorie=<?= urlencode($item['code_categorie_pack'] ?? '') ?>" class="btn" style="background: #1E3A5F; color: white; font-size: 12.5px; font-weight: 700; border-radius: 8px; padding: 8px 16px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i> Créer le premier pack
              </a>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div style="width: 100%; overflow-x: auto;">
              <table id="table-packs-category" class="table display nowrap" style="width:100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                  <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 2px solid #E2E8F0;">
                    <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px;">Pack / Code</th>
                    <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px; text-align: right;">Prix Cotisation</th>
                    <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px;">Année / Session</th>
                    <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px;">Zone</th>
                    <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px; text-align: center;">Souscriptions</th>
                    <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px; text-align: center;">Statut</th>
                    <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px; text-align: right;">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($packs as $p): ?>
                    <tr style="border-bottom: 1px solid #F1F5F9;">
                      <td style="padding: 12px 14px;">
                        <div style="font-weight: 700; color: #0F172A; font-size: 13.5px;"><?= htmlspecialchars($p['libelle_pack'] ?? '-') ?></div>
                        <div style="font-size: 11px; color: #059669; font-weight: 700;"><?= htmlspecialchars($p['code_pack'] ?? '-') ?></div>
                      </td>
                      <td style="padding: 12px 14px; text-align: right;">
                        <span style="font-weight: 800; color: #0F172A; font-size: 13.5px;"><?= number_format($p['prix_cotisation_pack'] ?? 0, 0, ',', ' ') ?></span>
                        <span style="font-size: 11px; color: #64748B; font-weight: 600;">FCFA</span>
                      </td>
                      <td style="padding: 12px 14px;">
                        <div style="font-size: 12px; font-weight: 700; color: #334155;"><?= htmlspecialchars($p['libelle_annee'] ?? '-') ?></div>
                        <div style="font-size: 11px; color: #64748B; font-weight: 500;"><?= htmlspecialchars($p['libelle_session'] ?? '-') ?></div>
                      </td>
                      <td style="padding: 12px 14px;">
                        <span style="font-size: 12px; font-weight: 600; color: #334155;">
                          <i data-lucide="map-pin" style="width: 12px; height: 12px; display: inline; vertical-align: -1px; margin-right: 3px; color: #059669;"></i>
                          <?= htmlspecialchars($p['libelle_zone'] ?? '-') ?>
                        </span>
                      </td>
                      <td style="padding: 12px 14px; text-align: center;">
                        <span style="background: #EFF6FF; color: #1D4ED8; font-weight: 800; padding: 3px 10px; border-radius: 12px; font-size: 11.5px; border: 1px solid #BFDBFE;">
                          <?= (int)($p['total_souscriptions'] ?? 0) ?>
                        </span>
                      </td>
                      <td style="padding: 12px 14px; text-align: center;">
                        <?php $pActif = ($p['statut_pack'] ?? '') === 'actif'; ?>
                        <span style="background: <?= $pActif ? '#ECFDF5' : '#FEF2F2' ?>; color: <?= $pActif ? '#047857' : '#B91C1C' ?>; padding: 3px 9px; border-radius: 12px; font-size: 11px; font-weight: 700;">
                          <?= ucfirst($p['statut_pack'] ?? 'inactif') ?>
                        </span>
                      </td>
                      <td style="padding: 12px 14px; text-align: right;">
                        <a href="<?= RACINE ?>pack/details/<?= $p['encrypted_id'] ?>" class="btn" style="background: #1E3A5F; color: #FFFFFF; font-weight: 700; border-radius: 8px; padding: 5px 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; font-size: 12px;" title="Voir le pack">
                          <i data-lucide="eye" style="width: 13px; height: 13px;"></i> Voir
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>

        </div>

      </div>

    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer-script.php'; ?>
<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();
  if ($.fn.DataTable && $('#table-packs-category').length) {
    $('#table-packs-category').DataTable({
      pageLength: 10,
      language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
      drawCallback: function() { if (window.lucide) lucide.createIcons(); }
    });
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
