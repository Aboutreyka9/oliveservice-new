<?php
require_once __DIR__ . '/../../public/inc/header.php';

$order        = $order ?? [];
$lignes       = $lignes ?? [];
$livreurs     = $livreurs ?? [];
$missions     = $missions ?? [];
$articles     = $articles ?? [];
$services     = $services ?? [];
$tarifs       = $tarifs ?? [];
$encryptedId  = $encryptedId ?? '';

$codeCommande = $order['code_commande'] ?? '';
$clientNom    = $order['nom_client'] ?? 'Client';
$clientTel    = $order['telephone_client'] ?? '-';
$clientAdresse= $order['adresse_client'] ?? ($order['adresse_livraison_commande'] ?? 'Adresse non renseignée');
$pressingNom  = $order['libelle_pressing'] ?? 'Pressing';
$typeCmd      = $order['type_commande'] ?? 'detaillee';
$nbSacs       = $order['nb_sacs_colis'] ?? 1;
$montantTotal = (float)($order['montant_total_commande'] ?? 0);
$fraisCollecte= (float)($order['frais_collecte_commande'] ?? 0);
$fraisLivraison=(float)($order['frais_livraison_commande'] ?? 0);
$remiseCmd    = (float)($order['remise_commande'] ?? 0);
$statutSuivi  = $order['statut_suivi_commande'] ?? 'creee';
$createdAt    = !empty($order['created_at_commande']) ? date('d/m/Y H:i', strtotime($order['created_at_commande'])) : '-';

$steps = STATUTS::SUIVI_COMMANDES;
?>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <!-- === EN-TÊTE DE LA COMMANDE === -->
      <div class="page-header" style="margin-bottom: 24px;">
        <div>
          <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <h1 style="margin: 0; font-size: 22px; font-weight: 800; color: #1E293B;">Commande #<?= htmlspecialchars($codeCommande) ?></h1>
            <?php if ($typeCmd === 'colis'): ?>
              <span style="background: #FEF3C7; color: #92400E; padding: 4px 10px; border-radius: 20px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;">
                <i data-lucide="package" style="width: 14px; height: 14px;"></i> Collecte de linge (<?= $nbSacs ?> sac<?= $nbSacs > 1 ? 's' : '' ?>)
              </span>
            <?php else: ?>
              <span style="background: #EFF6FF; color: #1E40AF; padding: 4px 10px; border-radius: 20px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;">
                <i data-lucide="shirt" style="width: 14px; height: 14px;"></i> Commande Détaillée
              </span>
            <?php endif; ?>
            <span class="badge-status <?= $statutSuivi === 'livree' ? 'delivered' : ($statutSuivi === 'refusee' || $statutSuivi === 'annulee' ? 'cancelled' : 'badge-status-progress') ?>" style="font-size: 12px; text-transform: uppercase;">
              <?= str_replace('_', ' ', htmlspecialchars($statutSuivi)) ?>
            </span>
          </div>
          <p class="page-subtitle" style="margin: 4px 0 0; color: #64748B; font-size: 13px;">
            Passée le <?= $createdAt ?> chez <strong><?= htmlspecialchars($pressingNom) ?></strong>
          </p>
        </div>
        <div style="display: flex; gap: 8px;">
          <a href="<?= RACINE ?>commande/ticket/<?= $encryptedId ?>" target="_blank" class="btn btn-outline-primary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; background: #EFF6FF; color: #1D4ED8; border-color: #BFDBFE;">
            <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Imprimer Reçu
          </a>
          <a href="<?= RACINE ?>commande/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Retour aux commandes
          </a>
        </div>
      </div>

      <!-- === BANDEAU D'ACTIONS CONTEXTUELLES === -->
      <div class="card" style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 18px; margin-bottom: 24px; border-radius: 14px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
          <div>
            <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #1E293B;">Actions rapides pour le pressing</h3>
            <p style="margin: 2px 0 0; font-size: 13px; color: #64748B;">Chaque action met à jour le statut et envoie immédiatement une notification Push + In-App au client.</p>
          </div>

          <div style="display: flex; gap: 8px; flex-wrap: wrap;" id="orderActionButtons">
            <?php if ($statutSuivi === 'creee'): ?>
              <button type="button" class="btn btn-primary" onclick="openAcceptModal()" style="background: #059669; border-color: #059669; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i> Accepter la commande
              </button>
              <button type="button" class="btn btn-secondary" onclick="openRefuseModal()" style="color: #DC2626; border-color: #FCA5A5; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="x-circle" style="width: 16px; height: 16px;"></i> Refuser
              </button>
              <button type="button" class="btn btn-secondary" onclick="openAssignLivreurModal('collecte')" style="display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="truck" style="width: 16px; height: 16px;"></i> Assigner coursier (Collecte)
              </button>

            <?php elseif ($typeCmd === 'colis' && in_array($statutSuivi, ['acceptee', 'collectee', 'recue_pressing'])): ?>
              <button type="button" class="btn btn-primary" onclick="openDevisModal()" style="background: #D97706; border-color: #D97706; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="clipboard-list" style="width: 16px; height: 16px;"></i> Saisir le devis après inventaire
              </button>
              <button type="button" class="btn btn-primary" onclick="openProcessModal()" style="display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="play" style="width: 16px; height: 16px;"></i> Démarrer le lavage
              </button>

            <?php elseif ($statutSuivi === 'prix_a_valider'): ?>
              <span style="background: #FEF3C7; color: #92400E; padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="clock" style="width: 14px; height: 14px;"></i> En attente de validation du devis (<?= number_format($montantTotal, 0, ',', ' ') ?> FCFA) par le client
              </span>
              <button type="button" class="btn btn-secondary" onclick="openDevisModal()" style="display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="edit-3" style="width: 15px; height: 15px;"></i> Modifier le devis
              </button>
              <button type="button" class="btn btn-primary" onclick="openProcessModal()" style="display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="play" style="width: 16px; height: 16px;"></i> Forcer le démarrage du lavage
              </button>

            <?php elseif (in_array($statutSuivi, ['acceptee', 'collectee', 'recue_pressing'])): ?>
              <button type="button" class="btn btn-primary" onclick="openProcessModal()" style="display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="play" style="width: 16px; height: 16px;"></i> Démarrer le lavage
              </button>

            <?php elseif ($statutSuivi === 'en_traitement'): ?>
              <button type="button" class="btn btn-primary" onclick="openReadyModal()" style="background: #059669; border-color: #059669; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="check" style="width: 16px; height: 16px;"></i> Marquer comme prête
              </button>

            <?php elseif ($statutSuivi === 'prete'): ?>
              <button type="button" class="btn btn-primary" onclick="openAssignLivreurModal('livraison')" style="display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="truck" style="width: 16px; height: 16px;"></i> Assigner coursier pour livraison
              </button>

            <?php elseif ($statutSuivi === 'en_livraison'): ?>
              <button type="button" class="btn btn-primary" onclick="openDeliverModal()" style="background: #059669; border-color: #059669; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i> Confirmer la remise au client (Livrée)
              </button>

            <?php elseif (in_array($statutSuivi, ['livree', 'annulee', 'refusee'])): ?>
              <span style="color: #64748B; font-weight: 600; font-size: 13px;">Commande clôturée</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- === GRILLE PRINCIPALE === -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 24px;">
        <!-- Carte Client -->
        <div class="card" style="padding: 20px;">
          <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px; border-bottom: 1px solid #E2E8F0; padding-bottom: 10px;">
            <i data-lucide="user" style="color: #1E3A5F; width: 20px; height: 20px;"></i>
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #1E293B;">Client & Livraison</h3>
          </div>
          <div style="display: flex; flex-direction: column; gap: 12px; font-size: 14px;">
            <div>
              <span style="color: #64748B; display: block; font-size: 12px; font-weight: 600;">Nom du client</span>
              <strong style="color: #1E293B; font-size: 15px;"><?= htmlspecialchars($clientNom) ?></strong>
            </div>
            <div>
              <span style="color: #64748B; display: block; font-size: 12px; font-weight: 600;">Téléphone</span>
              <strong><a href="tel:<?= htmlspecialchars($clientTel) ?>" style="color: #1E3A5F; text-decoration: none;"><?= htmlspecialchars($clientTel) ?></a></strong>
            </div>
            <div>
              <span style="color: #64748B; display: block; font-size: 12px; font-weight: 600;">Adresse de collecte / livraison</span>
              <span style="color: #334155;"><?= htmlspecialchars($clientAdresse) ?></span>
            </div>
            <?php if (!empty($order['observation_commande'])): ?>
              <div style="background: #FEF3C7; padding: 10px; border-radius: 8px; border-left: 3px solid #D97706;">
                <span style="color: #92400E; display: block; font-size: 12px; font-weight: 700;">Instructions du client :</span>
                <span style="color: #78350F; font-size: 13px;"><?= nl2br(htmlspecialchars($order['observation_commande'])) ?></span>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Carte Pressing & Règlements -->
        <div class="card" style="padding: 20px;">
          <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px; border-bottom: 1px solid #E2E8F0; padding-bottom: 10px;">
            <i data-lucide="wallet" style="color: #059669; width: 20px; height: 20px;"></i>
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #1E293B;">Montant & Règlement</h3>
          </div>
          <div style="display: flex; flex-direction: column; gap: 12px; font-size: 14px;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #F1F5F9; padding-bottom: 6px;">
              <span style="color: #64748B;">Pressing Traitant</span>
              <strong><?= htmlspecialchars($pressingNom) ?></strong>
            </div>
            <?php
              $sousTotalArticlesCalculated = 0;
              if (!empty($lignes)) {
                foreach ($lignes as $l) {
                  $sousTotalArticlesCalculated += (float)($l['sous_total_commande_detail'] ?? 0);
                }
              }
              if ($sousTotalArticlesCalculated <= 0) {
                $sousTotalArticlesCalculated = max(0, $montantTotal + $remiseCmd - $fraisCollecte - $fraisLivraison);
              }
            ?>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #F1F5F9; padding-bottom: 6px;">
              <span style="color: #64748B;">Sous-total des vêtements</span>
              <span><?= number_format($sousTotalArticlesCalculated, 0, ',', ' ') ?> FCFA</span>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #F1F5F9; padding-bottom: 6px;">
              <span style="color: #64748B;">Frais de collecte</span>
              <span><?= number_format($fraisCollecte, 0, ',', ' ') ?> FCFA</span>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #F1F5F9; padding-bottom: 6px;">
              <span style="color: #64748B;">Frais de livraison</span>
              <span><?= number_format($fraisLivraison, 0, ',', ' ') ?> FCFA</span>
            </div>
            <?php if ($remiseCmd > 0): ?>
              <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #F1F5F9; padding-bottom: 6px; color: #059669; font-weight: 700;">
                <span style="display: inline-flex; align-items: center; gap: 4px;">
                  <i data-lucide="tag" style="width: 14px; height: 14px;"></i> Remise / Avantage Promo
                </span>
                <span>-<?= number_format($remiseCmd, 0, ',', ' ') ?> FCFA</span>
              </div>
            <?php endif; ?>
            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 6px;">
              <span style="font-size: 15px; font-weight: 700; color: #1E293B;">Total à encaisser</span>
              <strong style="font-size: 20px; font-weight: 800; color: #059669;">
                <?= number_format($montantTotal, 0, ',', ' ') ?> FCFA
              </strong>
            </div>
            <?php if ($remiseCmd > 0): ?>
              <div style="font-size: 11.5px; color: #047857; background: #ECFDF5; border: 1px solid #A7F3D0; padding: 8px 10px; border-radius: 8px; margin-top: 4px;">
                <i data-lucide="info" style="width: 13px; height: 13px; color: #059669; vertical-align: middle;"></i>
                Le client a bénéficié d'une réduction promo de <strong><?= number_format($remiseCmd, 0, ',', ' ') ?> FCFA</strong> (Montant avant remise : <?= number_format($sousTotalArticlesCalculated + $fraisCollecte + $fraisLivraison, 0, ',', ' ') ?> FCFA).
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- === LIGNES D'ARTICLES OU COLIS === -->
      <div class="card" style="padding: 20px; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
          <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #1E293B;">Contenu de la commande</h3>
          <?php if ($typeCmd === 'colis'): ?>
            <button type="button" class="btn btn-sm btn-primary" onclick="openDevisModal()" style="background: #D97706; border-color: #D97706; display: inline-flex; align-items: center; gap: 6px;">
              <i data-lucide="plus-circle" style="width: 14px; height: 14px;"></i> <?= empty($lignes) ? 'Saisir l\'inventaire des articles' : 'Modifier l\'inventaire' ?>
            </button>
          <?php endif; ?>
        </div>

        <?php if (!empty($lignes)): ?>
          <div class="table-responsive-mobile">
            <table class="table" style="width: 100%;">
              <thead>
                <tr>
                  <th>Article</th>
                  <th>Service</th>
                  <th style="text-align: center;">Quantité</th>
                  <th style="text-align: right;">Prix Unitaire</th>
                  <th style="text-align: right;">Sous-Total</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($lignes as $l): ?>
                  <tr>
                    <td><strong><?= htmlspecialchars($l['libelle_article'] ?? ($l['article_code'] ?? 'Article')) ?></strong></td>
                    <td><span style="background: #F1F5F9; color: #1E293B; padding: 2px 8px; border-radius: 6px; font-size: 12px; font-weight: 600;"><?= htmlspecialchars($l['libelle_service'] ?? ($l['service_code'] ?? 'Lavage')) ?></span></td>
                    <td style="text-align: center;"><span style="font-weight: 700;"><?= (int)($l['quantite_commande_detail'] ?? 1) ?></span></td>
                    <td style="text-align: right;"><?= number_format((float)($l['prix_unitaire_commande_detail'] ?? 0), 0, ',', ' ') ?> FCFA</td>
                    <td style="text-align: right;"><strong style="color: #059669;"><?= number_format((float)($l['sous_total_commande_detail'] ?? 0), 0, ',', ' ') ?> FCFA</strong></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php elseif ($typeCmd === 'colis'): ?>
          <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="package" style="width: 28px; height: 28px;"></i>
            </div>
            <div style="flex: 1;">
              <h4 style="margin: 0 0 4px; font-size: 15px; font-weight: 700; color: #1E293B;">Collecte de linge au sac sans détail</h4>
              <p style="margin: 0; font-size: 13px; color: #64748B;">
                Nombre de sacs confiés : <strong><?= $nbSacs ?> sac<?= $nbSacs > 1 ? 's' : '' ?></strong>
                &nbsp;•&nbsp; <span style="color: #D97706; font-weight: 600;">Inventaire non encore saisi par le pressing</span>
              </p>
            </div>
          </div>
        <?php else: ?>
          <p style="color: #94A3B8; font-size: 14px; margin: 0;">Aucun détail d'article enregistré.</p>
        <?php endif; ?>
      </div>

      <!-- === MISSIONS DE LIVRAISON LIÉES === -->
      <?php if (!empty($missions)): ?>
        <div class="card" style="padding: 20px;">
          <h3 style="margin: 0 0 16px; font-size: 16px; font-weight: 700; color: #1E293B;">Missions de coursier associées</h3>
          <div class="table-responsive-mobile">
            <table class="table" style="width: 100%;">
              <thead>
                <tr>
                  <th>Mission</th>
                  <th>Type</th>
                  <th>Livreur</th>
                  <th>Téléphone</th>
                  <th>Statut Mission</th>
                  <th>Date Création</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($missions as $m): ?>
                  <tr>
                    <td><strong><?= htmlspecialchars($m['code_mission']) ?></strong></td>
                    <td><span style="font-weight: 700; text-transform: uppercase; font-size: 12px;"><?= htmlspecialchars($m['type_mission']) ?></span></td>
                    <td><?= htmlspecialchars($m['nom_livreur'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($m['telephone_livreur'] ?? '-') ?></td>
                    <td>
                      <span class="badge-status <?= $m['statut_mission'] === 'terminee' ? 'delivered' : 'badge-status-progress' ?>" style="font-size: 11px;">
                        <?= htmlspecialchars($m['statut_mission']) ?>
                      </span>
                    </td>
                    <td style="font-size: 12px; color: #64748B;"><?= date('d/m/Y H:i', strtotime($m['created_at_mission'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </main>
</div>

<!-- ==========================================
     MODALS DE GESTION DU WORKFLOW COMMANDE
     ========================================== -->

<!-- 1. Modal Confirmation Acceptation -->
<div id="modal-accept-order" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
  <div style="background: #FFF; border-radius: 16px; width: 90%; max-width: 420px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); text-align: center;">
    <div style="width: 56px; height: 56px; border-radius: 50%; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
      <i data-lucide="check-circle" style="width: 32px; height: 32px;"></i>
    </div>
    <h3 style="margin: 0 0 8px; font-size: 18px; font-weight: 800; color: #1E293B;">Accepter la commande ?</h3>
    <p style="margin: 0 0 20px; font-size: 13px; color: #64748B; line-height: 1.5;">
      Le client sera immédiatement notifié que sa commande <strong>#<?= htmlspecialchars($codeCommande) ?></strong> a été prise en charge par votre pressing.
    </p>
    <div style="display: flex; gap: 10px; justify-content: center;">
      <button type="button" class="btn btn-secondary" onclick="closeWorkflowModal('modal-accept-order')">Annuler</button>
      <button type="button" class="btn btn-primary" onclick="submitAccepter()" style="background: #059669; border-color: #059669;">Oui, accepter</button>
    </div>
  </div>
</div>

<!-- 2. Modal Refus avec Motif -->
<div id="modal-refuse-order" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
  <div style="background: #FFF; border-radius: 16px; width: 90%; max-width: 440px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
      <div style="width: 42px; height: 42px; border-radius: 50%; background: #FEF2F2; color: #DC2626; display: flex; align-items: center; justify-content: center;">
        <i data-lucide="x-circle" style="width: 24px; height: 24px;"></i>
      </div>
      <div>
        <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #1E293B;">Refuser la commande</h3>
        <small style="color: #64748B;">Commande #<?= htmlspecialchars($codeCommande) ?></small>
      </div>
    </div>
    <form id="form-refuse-order" onsubmit="submitRefuser(event)">
      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Motif du refus (optionnel mais recommandé) :</label>
        <textarea id="refuse_motif_input" rows="3" class="form-control" placeholder="ex: Capacité de traitement atteinte, créneau indisponible..." style="width: 100%; padding: 10px; font-size: 13px; border-radius: 8px;"></textarea>
      </div>
      <div style="display: flex; gap: 8px; justify-content: flex-end;">
        <button type="button" class="btn btn-secondary" onclick="closeWorkflowModal('modal-refuse-order')">Annuler</button>
        <button type="submit" class="btn btn-primary" style="background: #DC2626; border-color: #DC2626;">Confirmer le refus</button>
      </div>
    </form>
  </div>
</div>

<!-- 3. Modal Démarrer Traitement / Lavage -->
<div id="modal-process-order" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
  <div style="background: #FFF; border-radius: 16px; width: 90%; max-width: 420px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); text-align: center;">
    <div style="width: 56px; height: 56px; border-radius: 50%; background: #EFF6FF; color: #2563EB; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
      <i data-lucide="play" style="width: 28px; height: 28px;"></i>
    </div>
    <h3 style="margin: 0 0 8px; font-size: 18px; font-weight: 800; color: #1E293B;">Démarrer le lavage ?</h3>
    <p style="margin: 0 0 20px; font-size: 13px; color: #64748B; line-height: 1.5;">
      Le client sera notifié que ses vêtements sont actuellement en cours de nettoyage dans votre atelier.
    </p>
    <div style="display: flex; gap: 10px; justify-content: center;">
      <button type="button" class="btn btn-secondary" onclick="closeWorkflowModal('modal-process-order')">Annuler</button>
      <button type="button" class="btn btn-primary" onclick="submitLancerTraitement()">Oui, démarrer</button>
    </div>
  </div>
</div>

<!-- 4. Modal Marquer Prête -->
<div id="modal-ready-order" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
  <div style="background: #FFF; border-radius: 16px; width: 90%; max-width: 420px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); text-align: center;">
    <div style="width: 56px; height: 56px; border-radius: 50%; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
      <i data-lucide="sparkles" style="width: 32px; height: 32px;"></i>
    </div>
    <h3 style="margin: 0 0 8px; font-size: 18px; font-weight: 800; color: #1E293B;">Linge lavé & repassé ?</h3>
    <p style="margin: 0 0 20px; font-size: 13px; color: #64748B; line-height: 1.5;">
      Marquer la commande comme <strong>prête</strong> permettra d'assigner un livreur pour l'expédition au domicile du client.
    </p>
    <div style="display: flex; gap: 10px; justify-content: center;">
      <button type="button" class="btn btn-secondary" onclick="closeWorkflowModal('modal-ready-order')">Annuler</button>
      <button type="button" class="btn btn-primary" onclick="submitMarquerPrete()" style="background: #059669; border-color: #059669;">Confirmer prête</button>
    </div>
  </div>
</div>

<!-- 5. Modal Saisie Devis Colis avec Inventaire d'Articles -->
<div id="modal-devis" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
  <div style="background: #FFF; border-radius: 16px; width: 92%; max-width: 660px; max-height: 90vh; overflow-y: auto; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
    
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px;">
      <div style="display: flex; align-items: center; gap: 12px;">
        <div style="width: 42px; height: 42px; border-radius: 50%; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center;">
          <i data-lucide="clipboard-list" style="width: 24px; height: 24px;"></i>
        </div>
        <div>
          <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #1E293B;">Inventaire du sac de linge</h3>
          <small style="color: #64748B;">Commande #<?= htmlspecialchars($codeCommande) ?> • <?= $nbSacs ?> sac(s)</small>
        </div>
      </div>
      <button type="button" onclick="closeWorkflowModal('modal-devis')" style="background: none; border: none; font-size: 24px; color: #94A3B8; cursor: pointer;">&times;</button>
    </div>

    <form id="form-devis" onsubmit="submitDevis(event)">
      <input type="hidden" name="code_commande" value="<?= htmlspecialchars($codeCommande) ?>">
      <input type="hidden" name="items_json" id="devis_items_json" value="[]">

      <!-- SÉLECTEUR POUR AJOUTER UN ARTICLE À L'INVENTAIRE -->
      <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px; margin-bottom: 16px;">
        <h4 style="margin: 0 0 10px; font-size: 13px; font-weight: 700; color: #334155;">Ajouter un vêtement au devis</h4>
        
        <div style="display: grid; grid-template-columns: 2fr 1.5fr 1fr 1.2fr auto; gap: 8px; align-items: flex-end;">
          <div>
            <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Article</label>
            <select id="devis_article_select" class="form-control" onchange="autoFillDevisPrice()" style="width: 100%; padding: 8px 10px; font-size: 13px;">
              <option value="">-- Choisir --</option>
              <?php foreach ($articles as $art): ?>
                <option value="<?= htmlspecialchars($art['code_article']) ?>" data-label="<?= htmlspecialchars($art['libelle_article']) ?>">
                  <?= htmlspecialchars($art['libelle_article']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Service</label>
            <select id="devis_service_select" class="form-control" onchange="autoFillDevisPrice()" style="width: 100%; padding: 8px 10px; font-size: 13px;">
              <option value="">-- Service --</option>
              <?php foreach ($services as $srv): ?>
                <option value="<?= htmlspecialchars($srv['code_service']) ?>" data-label="<?= htmlspecialchars($srv['libelle_service']) ?>">
                  <?= htmlspecialchars($srv['libelle_service']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Qté</label>
            <input type="number" id="devis_qty" value="1" min="1" class="form-control" style="width: 100%; padding: 8px 6px; font-size: 13px; text-align: center;">
          </div>

          <div>
            <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Prix Unit. (FCFA)</label>
            <input type="number" id="devis_price" value="" min="0" step="50" class="form-control" style="width: 100%; padding: 8px 6px; font-size: 13px;" placeholder="Prix FCFA">
          </div>

          <div>
            <button type="button" class="btn btn-primary" onclick="addDevisLineItem()" style="padding: 8px 12px; height: 38px; display: inline-flex; align-items: center; gap: 4px; background: #D97706; border-color: #D97706;">
              <i data-lucide="plus" style="width: 16px; height: 16px;"></i> Ajouter
            </button>
          </div>
        </div>
      </div>

      <!-- TABLEAU DES ARTICLES INVENTORIÉS -->
      <div style="background: #FFF; border: 1px solid #E2E8F0; border-radius: 8px; overflow: hidden; margin-bottom: 16px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
          <thead>
            <tr style="background: #F1F5F9; text-align: left; color: #475569;">
              <th style="padding: 8px 12px;">Article</th>
              <th style="padding: 8px 12px;">Service</th>
              <th style="padding: 8px 12px; text-align: center;">Qté</th>
              <th style="padding: 8px 12px; text-align: right;">Prix Unit.</th>
              <th style="padding: 8px 12px; text-align: right;">Total</th>
              <th style="padding: 8px 12px; text-align: center; width: 40px;"></th>
            </tr>
          </thead>
          <tbody id="tbody-devis-items">
            <tr id="row-empty-devis">
              <td colspan="6" style="padding: 16px; text-align: center; color: #94A3B8;">
                Aucun vêtement inventorié pour le moment.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- RÉCAPITULATIF FINANCIER DU DEVIS -->
      <div style="background: #FEF3C7; padding: 14px; border-radius: 10px; border: 1px solid #FCD34D; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
        <div>
          <span style="font-size: 12px; color: #92400E; display: block;">Total Devis Client (Frais inclus)</span>
          <small style="color: #78350F;">Collecte (<?= number_format($fraisCollecte, 0, ',', ' ') ?>) + Livraison (<?= number_format($fraisLivraison, 0, ',', ' ') ?>)</small>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
          <input type="number" id="devis_montant_total" name="montant_total" required min="500" step="100" class="form-control" style="width: 150px; font-size: 18px; font-weight: 800; color: #B45309; text-align: right; background: #FFF;" readonly>
          <strong style="color: #92400E; font-size: 16px;">FCFA</strong>
        </div>
      </div>

      <div style="display: flex; gap: 8px; justify-content: flex-end;">
        <button type="button" class="btn btn-secondary" onclick="closeWorkflowModal('modal-devis')">Annuler</button>
        <button type="submit" class="btn btn-primary btnSubmitDevis" style="background: #D97706; border-color: #D97706; display: inline-flex; align-items: center; gap: 6px;">
          <i data-lucide="check" style="width: 16px; height: 16px;"></i> Valider le devis & Notifier le client
        </button>
      </div>
    </form>
  </div>
</div>

<!-- 6. Modal Assignation Livreur -->
<div id="modal-assign-livreur" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
  <div style="background: #FFF; border-radius: 16px; width: 90%; max-width: 440px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
      <div style="width: 42px; height: 42px; border-radius: 50%; background: #EFF6FF; color: #1E40AF; display: flex; align-items: center; justify-content: center;">
        <i data-lucide="truck" style="width: 24px; height: 24px;"></i>
      </div>
      <div>
        <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #1E293B;" id="assignModalTitle">Assigner un coursier</h3>
        <small style="color: #64748B;">Création d'une mission de transport</small>
      </div>
    </div>
    <form id="form-assign-livreur" onsubmit="submitAssignLivreur(event)">
      <input type="hidden" name="code_commande" value="<?= htmlspecialchars($codeCommande) ?>">
      <input type="hidden" name="type_mission" id="assign_type_mission" value="livraison">
      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Livreur disponible</label>
        <select name="livreur_code" required class="form-control" style="width: 100%; padding: 10px 14px; font-size: 14px;">
          <option value="">-- Choisir un coursier --</option>
          <?php foreach ($livreurs as $liv): ?>
            <option value="<?= htmlspecialchars($liv['code_livreur']) ?>">
              <?= htmlspecialchars($liv['nom_livreur']) ?> (<?= htmlspecialchars($liv['telephone_livreur']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display: flex; gap: 8px; justify-content: flex-end;">
        <button type="button" class="btn btn-secondary" onclick="closeWorkflowModal('modal-assign-livreur')">Annuler</button>
        <button type="submit" class="btn btn-primary">Assigner la mission</button>
      </div>
    </form>
  </div>
</div>

<script>
const currentCmdCode  = '<?= htmlspecialchars($codeCommande) ?>';
const currentCmdId    = '<?= (int)($order['id_commande'] ?? 0) ?>';
const fraisColCmd     = <?= $fraisCollecte ?>;
const fraisLivCmd     = <?= $fraisLivraison ?>;
const remiseCmdVal    = <?= $remiseCmd ?>;
const tarifsCatalog   = <?= json_encode($tarifs, JSON_UNESCAPED_UNICODE) ?>;
const allTarifsCatalog = <?= json_encode($allTarifs ?? [], JSON_UNESCAPED_UNICODE) ?>;
const baseApiUrl      = (typeof LINK !== 'undefined') ? LINK : ((typeof RACINE !== 'undefined') ? RACINE : '/admin-lavex/');

let devisItemsList = <?= !empty($lignes) ? json_encode(array_map(function($l) {
  return [
    'article_code'  => $l['article_code'],
    'article_label' => $l['libelle_article'] ?? $l['article_code'],
    'service_code'  => $l['service_code'],
    'service_label' => $l['libelle_service'] ?? $l['service_code'],
    'quantite'      => (int)$l['quantite_commande_detail'],
    'prix_unitaire' => (float)$l['prix_unitaire_commande_detail'],
    'sous_total'    => (float)$l['sous_total_commande_detail']
  ];
}, $lignes), JSON_UNESCAPED_UNICODE) : '[]' ?>;

function openWorkflowModal(id) {
  const el = document.getElementById(id);
  if (el) {
    el.style.display = 'flex';
    el.classList.add('active');
    if (typeof lucide !== 'undefined') lucide.createIcons();
  }
}

function closeWorkflowModal(id) {
  const el = document.getElementById(id);
  if (el) {
    el.style.display = 'none';
    el.classList.remove('active');
  }
}

// Rétrocompatibilité
window.openModal = window.openModal || openWorkflowModal;
window.closeModal = window.closeModal || closeWorkflowModal;

function openAcceptModal() { openWorkflowModal('modal-accept-order'); }

function submitAccepter() {
  closeWorkflowModal('modal-accept-order');
  $.post(baseApiUrl + 'commande/accepter', { code_commande: currentCmdCode }, function(rep) {
    if (typeof showToast === 'function') showToast(rep.message || 'Commande acceptée', rep.status ? 'success' : 'error');
    if (rep.status) setTimeout(() => window.location.reload(), 800);
  }, 'json').fail(function() { if (typeof showToast === 'function') showToast('Erreur serveur', 'error'); });
}

function openRefuseModal() { openWorkflowModal('modal-refuse-order'); }

function submitRefuser(e) {
  e.preventDefault();
  const motif = document.getElementById('refuse_motif_input').value;
  closeWorkflowModal('modal-refuse-order');
  $.post(baseApiUrl + 'commande/refuser', { code_commande: currentCmdCode, motif: motif }, function(rep) {
    if (typeof showToast === 'function') showToast(rep.message || 'Commande refusée', rep.status ? 'success' : 'error');
    if (rep.status) setTimeout(() => window.location.reload(), 800);
  }, 'json').fail(function() { if (typeof showToast === 'function') showToast('Erreur serveur', 'error'); });
}

function openProcessModal() { openWorkflowModal('modal-process-order'); }

function submitLancerTraitement() {
  closeWorkflowModal('modal-process-order');
  $.post(baseApiUrl + 'commande/lancerTraitement', { code_commande: currentCmdCode }, function(rep) {
    if (typeof showToast === 'function') showToast(rep.message || 'Traitement démarré', rep.status ? 'success' : 'error');
    if (rep.status) setTimeout(() => window.location.reload(), 800);
  }, 'json').fail(function() { if (typeof showToast === 'function') showToast('Erreur serveur', 'error'); });
}

function openReadyModal() { openWorkflowModal('modal-ready-order'); }

function submitMarquerPrete() {
  closeWorkflowModal('modal-ready-order');
  $.post(baseApiUrl + 'commande/marquerPrete', { code_commande: currentCmdCode }, function(rep) {
    if (typeof showToast === 'function') showToast(rep.message || 'Commande marquée comme prête', rep.status ? 'success' : 'error');
    if (rep.status) setTimeout(() => window.location.reload(), 800);
  }, 'json').fail(function() { if (typeof showToast === 'function') showToast('Erreur serveur', 'error'); });
}

function openDevisModal() {
  renderDevisItems();
  openWorkflowModal('modal-devis');
  
  if ($.fn.select2) {
    $('#devis_article_select').select2({
      dropdownParent: $('#modal-devis'),
      placeholder: '-- Choisir --',
      width: '100%'
    }).off('select2:select change').on('select2:select change', function() { 
      autoFillDevisPrice(); 
    });

    $('#devis_service_select').select2({
      dropdownParent: $('#modal-devis'),
      placeholder: '-- Service --',
      width: '100%'
    }).off('select2:select change').on('select2:select change', function() { 
      autoFillDevisPrice(); 
    });
  }
}

function autoFillDevisPrice() {
  const artCode = $('#devis_article_select').val() || document.getElementById('devis_article_select')?.value;
  const srvCode = $('#devis_service_select').val() || document.getElementById('devis_service_select')?.value;
  if (!artCode || !srvCode) return;

  let found = null;
  if (Array.isArray(tarifsCatalog) && tarifsCatalog.length > 0) {
    found = tarifsCatalog.find(t => t.article_code === artCode && t.service_code === srvCode);
  }
  if (!found && Array.isArray(allTarifsCatalog) && allTarifsCatalog.length > 0) {
    found = allTarifsCatalog.find(t => t.article_code === artCode && t.service_code === srvCode);
  }

  if (found && found.prix_tarif !== undefined && found.prix_tarif !== null) {
    const val = Math.round(parseFloat(found.prix_tarif));
    $('#devis_price').val(val);
    const inputEl = document.getElementById('devis_price');
    if (inputEl) inputEl.value = val;
  }
}

$(document).on('change', '#devis_article_select, #devis_service_select', function() {
  autoFillDevisPrice();
});

function addDevisLineItem() {
  const selArt = document.getElementById('devis_article_select');
  const selSrv = document.getElementById('devis_service_select');
  const artCode = selArt.value;
  const srvCode = selSrv.value;
  const artLabel = selArt.options[selArt.selectedIndex]?.getAttribute('data-label') || artCode;
  const srvLabel = selSrv.options[selSrv.selectedIndex]?.getAttribute('data-label') || srvCode;
  const qty = parseInt(document.getElementById('devis_qty').value) || 1;
  const price = parseFloat(document.getElementById('devis_price').value) || 0;

  if (!artCode) {
    if (typeof showToast === 'function') showToast('Veuillez choisir un article', 'warning');
    return;
  }
  if (!srvCode) {
    if (typeof showToast === 'function') showToast('Veuillez choisir un service', 'warning');
    return;
  }

  const existingIdx = devisItemsList.findIndex(i => i.article_code === artCode && i.service_code === srvCode);
  if (existingIdx >= 0) {
    devisItemsList[existingIdx].quantite += qty;
    devisItemsList[existingIdx].prix_unitaire = price;
    devisItemsList[existingIdx].sous_total = devisItemsList[existingIdx].quantite * price;
  } else {
    devisItemsList.push({
      article_code: artCode,
      article_label: artLabel,
      service_code: srvCode,
      service_label: srvLabel,
      quantite: qty,
      prix_unitaire: price,
      sous_total: qty * price
    });
  }

  renderDevisItems();
}

function removeDevisLineItem(idx) {
  devisItemsList.splice(idx, 1);
  renderDevisItems();
}

function renderDevisItems() {
  const tbody = document.getElementById('tbody-devis-items');
  tbody.innerHTML = '';

  if (devisItemsList.length === 0) {
    tbody.innerHTML = `
      <tr id="row-empty-devis">
        <td colspan="6" style="padding: 16px; text-align: center; color: #94A3B8;">
          Aucun vêtement inventorié pour le moment.
        </td>
      </tr>
    `;
  } else {
    devisItemsList.forEach((item, idx) => {
      const tr = document.createElement('tr');
      tr.style.borderBottom = '1px solid #F1F5F9';
      tr.innerHTML = `
        <td style="padding: 8px 12px;"><strong>${item.article_label}</strong></td>
        <td style="padding: 8px 12px;"><span style="background: #F1F5F9; color: #1E293B; padding: 2px 6px; border-radius: 4px; font-size: 11px;">${item.service_label}</span></td>
        <td style="padding: 8px 12px; text-align: center; font-weight: 700;">${item.quantite}</td>
        <td style="padding: 8px 12px; text-align: right;">${new Intl.NumberFormat('fr-FR').format(item.prix_unitaire)} FCFA</td>
        <td style="padding: 8px 12px; text-align: right; font-weight: 700; color: #059669;">${new Intl.NumberFormat('fr-FR').format(item.sous_total)} FCFA</td>
        <td style="padding: 8px 12px; text-align: center;">
          <button type="button" onclick="removeDevisLineItem(${idx})" style="background: none; border: none; color: #DC2626; cursor: pointer; font-size: 14px;" title="Supprimer">
            &times;
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  }

  const itemsSum = devisItemsList.reduce((acc, curr) => acc + curr.sous_total, 0);
  const total = itemsSum + fraisColCmd + fraisLivCmd - remiseCmdVal;
  document.getElementById('devis_montant_total').value = Math.max(0, total);
  document.getElementById('devis_items_json').value = JSON.stringify(devisItemsList);
}

function submitDevis(e) {
  e.preventDefault();
  if (devisItemsList.length === 0) {
    if (typeof showToast === 'function') showToast('Veuillez ajouter au moins un vêtement dans l\'inventaire', 'warning');
    return;
  }

  const form = $(e.target);
  const btn = form.find('.btnSubmitDevis');
  if (typeof loading === 'function') loading(btn, true, '<i class="fa fa-spinner fa-spin"></i> Enregistrement...');

  $.post(baseApiUrl + 'commande/saisirDevisColis', form.serialize(), function(rep) {
    if (typeof loading === 'function') loading(btn, false, '<i data-lucide="check"></i> Valider le devis');
    if (typeof showToast === 'function') showToast(rep.message || 'Devis enregistré', rep.status ? 'success' : 'error');
    if (rep.status) {
      closeWorkflowModal('modal-devis');
      setTimeout(() => window.location.reload(), 800);
    }
  }, 'json').fail(function() {
    if (typeof loading === 'function') loading(btn, false, '<i data-lucide="check"></i> Valider le devis');
    if (typeof showToast === 'function') showToast('Erreur serveur lors de la saisie du devis', 'error');
  });
}

function openAssignLivreurModal(type) {
  document.getElementById('assign_type_mission').value = type;
  document.getElementById('assignModalTitle').innerText = (type === 'collecte') ? 'Assigner coursier (Collecte)' : 'Assigner coursier (Livraison)';
  openWorkflowModal('modal-assign-livreur');
}

function submitAssignLivreur(e) {
  e.preventDefault();
  const formData = $(e.target).serialize();
  closeWorkflowModal('modal-assign-livreur');
  $.post(baseApiUrl + 'commande/assignerLivreur', formData, function(rep) {
    if (typeof showToast === 'function') showToast(rep.message || 'Livreur assigné', rep.status ? 'success' : 'error');
    if (rep.status) setTimeout(() => window.location.reload(), 800);
  }, 'json').fail(function() { if (typeof showToast === 'function') showToast('Erreur serveur', 'error'); });
}

function openDeliverModal() {
  if (typeof showConfirm === 'function') {
    showConfirm('Confirmer que la commande a bien été livrée au client ?', function() {
      $.post(baseApiUrl + 'commande/transition', { id_commande: currentCmdId, statut_suivi_commande: 'livree' }, function(rep) {
        if (typeof showToast === 'function') showToast(rep.message || 'Commande livrée', rep.status ? 'success' : 'error');
        if (rep.status) setTimeout(() => window.location.reload(), 800);
      }, 'json');
    });
  }
}
</script>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
