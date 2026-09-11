<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php 
$item = $item ?? [];
$packArticles = $packArticles ?? [];
$libellePack = $item['libelle_pack'] ?? 'Pack Produit';
$codePack = $item['code_pack'] ?? '-';
$prixCotisation = (float)($item['prix_cotisation_pack'] ?? 0);
$nombreJours = (int)($item['nombre_jour_session'] ?? 0);
$montantTotal = $prixCotisation * $nombreJours;
$imageName = $item['image_pack'] ?? null;
$hasImage = !empty($imageName) && file_exists(__DIR__ . '/../../public/assets/images/packs/' . $imageName);
$creatorName = trim(($item['prenom_user'] ?? '') . ' ' . ($item['nom_user'] ?? ''));
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE LA FICHE PACK -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);">
            <i data-lucide="package" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Fiche Pack : <?= htmlspecialchars($libellePack) ?>
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Code Pack : <code style="font-weight: 700; color: #1E3A5F; background: #F1F5F9; padding: 2px 8px; border-radius: 6px; font-size: 12px; border: 1px solid #CBD5E1;"><?= htmlspecialchars($codePack) ?></code> 
              &bull; Catégorie : <strong><?= htmlspecialchars($item['libelle_categorie_pack'] ?? '-') ?></strong>
            </p>
          </div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <a href="<?= RACINE ?>pack/list" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #334155; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 10px; padding: 10px 18px; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: #64748B;"></i> Retour aux packs
          </a>
          <a href="<?= RACINE ?>pack/edition/<?= $encryptedId ?>" class="btn" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: white; font-weight: 800; border-radius: 10px; padding: 10px 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
            <i data-lucide="edit" style="width: 16px; height: 16px;"></i> Modifier le Pack
          </a>
        </div>
      </div>

      <!-- KPI GRID / SYNTHÈSE RIDEAU -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- CARD 1: COTISATION JOUR -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 16px;">
          <div style="width: 44px; height: 44px; border-radius: 12px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="coins" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Cotisation / Jour</span>
            <div style="font-size: 19px; font-weight: 800; color: #1E3A5F; margin-top: 2px;">
              <?= number_format($prixCotisation, 0, ',', ' ') ?> <span style="font-size: 13px; font-weight: 600;">FCFA</span>
            </div>
          </div>
        </div>

        <!-- CARD 2: DURÉE SESSION -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 16px;">
          <div style="width: 44px; height: 44px; border-radius: 12px; background: #F0FDF4; color: #059669; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="calendar" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Durée de Session</span>
            <div style="font-size: 19px; font-weight: 800; color: #0F172A; margin-top: 2px;">
              <?= $nombreJours ?> <span style="font-size: 13px; font-weight: 600;">Jours</span>
            </div>
          </div>
        </div>

        <!-- CARD 3: MONTANT TOTAL -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 16px;">
          <div style="width: 44px; height: 44px; border-radius: 12px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="wallet" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Montant Total Pack</span>
            <div style="font-size: 19px; font-weight: 800; color: #059669; margin-top: 2px;">
              <?= number_format($montantTotal, 0, ',', ' ') ?> <span style="font-size: 13px; font-weight: 600;">FCFA</span>
            </div>
          </div>
        </div>

        <!-- CARD 4: COMPOSITION -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 14px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 16px;">
          <div style="width: 44px; height: 44px; border-radius: 12px; background: #F8FAFC; color: #475569; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #E2E8F0;">
            <i data-lucide="shopping-bag" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Articles Inclus</span>
            <div style="font-size: 19px; font-weight: 800; color: #0F172A; margin-top: 2px;">
              <?= count($packArticles) ?> <span style="font-size: 13px; font-weight: 600;">Article(s)</span>
            </div>
          </div>
        </div>

      </div>

      <!-- SECTION DÉTAILS PACK + VISUEL -->
      <div style="display: grid; grid-template-columns: <?= $hasImage ? '1fr 300px' : '1fr' ?>; gap: 24px; margin-bottom: 24px;">
        
        <!-- CARTE CARACTÉRISTIQUES PRINCIPALES -->
        <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); width: 100%; box-sizing: border-box;">
          <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid #F1F5F9; padding-bottom: 12px;">
            <i data-lucide="boxes" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Spécifications du Pack
          </h3>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
            
            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Désignation / Libellé</span>
              <div style="font-size: 16px; font-weight: 800; color: #0F172A; margin-top: 4px;"><?= htmlspecialchars($libellePack) ?></div>
            </div>

            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Catégorie de Pack</span>
              <div style="font-size: 15px; font-weight: 700; color: #334155; margin-top: 4px;">
                <span style="background: #F1F5F9; color: #1E3A5F; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 13px; border: 1px solid #E2E8F0;">
                  <?= htmlspecialchars($item['libelle_categorie_pack'] ?? '-') ?>
                </span>
              </div>
            </div>

            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Session Associée</span>
              <div style="font-size: 15px; font-weight: 700; color: #334155; margin-top: 4px;">
                <?= htmlspecialchars($item['libelle_session'] ?? '-') ?> 
                <span style="font-size: 12px; color: #64748B; font-weight: 500;">(<?= $nombreJours ?> jours)</span>
              </div>
            </div>

            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Zone Commerciale</span>
              <div style="font-size: 15px; font-weight: 700; color: #334155; margin-top: 4px;"><?= htmlspecialchars($item['libelle_zone'] ?? '-') ?></div>
            </div>

            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Année d'Activité</span>
              <div style="font-size: 15px; font-weight: 700; color: #334155; margin-top: 4px;"><?= htmlspecialchars($item['libelle_annee'] ?? '-') ?></div>
            </div>

            <div>
              <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Statut de l'Offre</span>
              <div style="margin-top: 6px;">
                <?php if (($item['statut_pack'] ?? '') === 'actif'): ?>
                  <span style="display: inline-flex; align-items: center; gap: 4px; background:#ECFDF5; color:#059669; padding:5px 12px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #A7F3D0;">
                    <i data-lucide="check-circle" style="width: 14px; height: 14px;"></i> Pack Actif
                  </span>
                <?php else: ?>
                  <span style="display: inline-flex; align-items: center; gap: 4px; background:#FEE2E2; color:#B91C1C; padding:5px 12px; border-radius:20px; font-weight:800; font-size:12px; border:1px solid #FECACA;">
                    <i data-lucide="x-circle" style="width: 14px; height: 14px;"></i> Inactif
                  </span>
                <?php endif; ?>
              </div>
            </div>

            <?php if (!empty($item['created_at_pack'])): ?>
              <div>
                <span style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Date de Création</span>
                <div style="font-size: 13px; font-weight: 600; color: #475569; margin-top: 4px;">
                  <?= date('d/m/Y H:i', strtotime($item['created_at_pack'])) ?>
                  <?php if (!empty($creatorName)): ?>
                    <span style="color: #94A3B8; font-weight: 500;">par <?= htmlspecialchars($creatorName) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>

          </div>
        </div>

        <!-- VISUEL IMAGE DU PACK (SI DISPONIBLE) -->
        <?php if ($hasImage): ?>
          <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 20px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
            <h4 style="font-size: 12px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 14px 0;">Visuel du Pack</h4>
            <div style="width: 100%; height: 200px; border-radius: 12px; overflow: hidden; border: 1px solid #E2E8F0; background: #F8FAFC; display: flex; align-items: center; justify-content: center;">
              <img src="<?= RACINE ?>public/assets/images/packs/<?= htmlspecialchars($imageName) ?>" alt="<?= htmlspecialchars($libellePack) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
            </div>
          </div>
        <?php endif; ?>

      </div>

      <!-- CARTE COMPOSITION DU PACK -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); width: 100%; box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #F1F5F9; padding-bottom: 12px; flex-wrap: wrap; gap: 12px;">
          <h3 style="font-size: 14px; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="layers" style="width: 18px; height: 18px; color: #1E3A5F;"></i> Composition & Articles du Pack (<?= count($packArticles) ?>)
          </h3>
          <span style="font-size: 12px; font-weight: 700; color: #64748B; background: #F1F5F9; padding: 4px 12px; border-radius: 20px; border: 1px solid #E2E8F0;">
            Articles inclus dans l'offre
          </span>
        </div>

        <?php if (empty($packArticles)): ?>
          <div style="text-align: center; padding: 40px 20px; background: #F8FAFC; border-radius: 12px; border: 1px dashed #CBD5E1;">
            <i data-lucide="package-open" style="width: 40px; height: 40px; color: #94A3B8; margin-bottom: 8px;"></i>
            <p style="color: #64748B; font-weight: 600; margin: 0 0 4px 0;">Aucun article rattaché à ce pack</p>
            <p style="color: #94A3B8; font-size: 13px; margin: 0;">Ce pack ne contient pas encore d'articles individuels configurés.</p>
          </div>
        <?php else: ?>
          <div style="width: 100%; overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <thead>
                <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 2px solid #E2E8F0;">
                  <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px; width: 50px;">#</th>
                  <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px;">Désignation Article</th>
                  <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px;">Code Article</th>
                  <th style="padding: 12px 14px; font-weight: 800; text-transform: uppercase; font-size: 11px;">Description</th>
                  <th style="padding: 12px 14px; text-align: center; font-weight: 800; text-transform: uppercase; font-size: 11px; width: 140px;">Quantité Inclus</th>
                </tr>
              </thead>
              <tbody>
                <?php $idx = 1; foreach ($packArticles as $pa): ?>
                  <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.15s ease;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 12px 14px; font-weight: 700; color: #94A3B8;"><?= $idx++ ?></td>
                    <td style="padding: 12px 14px; font-weight: 800; color: #0F172A;">
                      <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 32px; height: 32px; border-radius: 8px; background: #EFF6FF; color: #1E3A5F; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px;">
                          <i data-lucide="tag" style="width: 16px; height: 16px;"></i>
                        </div>
                        <?= htmlspecialchars($pa['libelle_article'] ?? $pa['article_code']) ?>
                      </div>
                    </td>
                    <td style="padding: 12px 14px;">
                      <code style="font-weight: 700; color: #1E3A5F; background: #F1F5F9; padding: 3px 8px; border-radius: 6px; font-size: 12px; border: 1px solid #CBD5E1;">
                        <?= htmlspecialchars($pa['article_code'] ?? '-') ?>
                      </code>
                    </td>
                    <td style="padding: 12px 14px; color: #64748B; font-size: 13px;">
                      <?= !empty($pa['description_article']) ? htmlspecialchars($pa['description_article']) : '<span style="color:#CBD5E1; font-style:italic;">Aucune description</span>' ?>
                    </td>
                    <td style="padding: 12px 14px; text-align: center;">
                      <span style="background: #EFF6FF; color: #1E3A5F; padding: 6px 14px; border-radius: 10px; font-weight: 800; font-size: 13px; border: 1px solid #BFDBFE; display: inline-flex; align-items: center; gap: 4px;">
                        x<?= (int)($pa['quantite_article'] ?? 1) ?>
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
