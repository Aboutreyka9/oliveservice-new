<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">

      <style>
      .btn-action-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #E2E8F0;
        background: #FFFFFF;
        color: #475569;
        transition: all 0.2s ease;
        text-decoration: none;
        cursor: pointer;
      }
      .btn-action-icon:hover {
        background: #F1F5F9;
        color: #1E3A5F;
        border-color: #94A3B8;
        transform: translateY(-1px);
      }
      .btn-action-icon.primary {
        background: #1E3A5F;
        border-color: #1E3A5F;
        color: #FFFFFF;
      }
      .btn-action-icon.primary:hover {
        background: #152B47;
        color: #FFFFFF;
        box-shadow: 0 2px 6px rgba(30, 58, 95, 0.25);
      }
      .btn-action-icon.success {
        background: #ECFDF5;
        border-color: #A7F3D0;
        color: #059669;
      }
      .btn-action-icon.success:hover {
        background: #059669;
        border-color: #059669;
        color: #FFFFFF;
        box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25);
      }
      .sous-actions-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
      }
      .sous-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 18px;
        margin-bottom: 24px;
      }
      .sous-kpi-card {
        background: #FFFFFF;
        border-radius: 14px;
        padding: 20px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.02);
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        cursor: pointer;
      }
      .sous-kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--kpi-accent, #1E3A5F);
        border-radius: 14px 14px 0 0;
      }
      .sous-kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 20px -5px rgba(15, 23, 42, 0.08);
      }
      .sous-kpi-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
      }
      .sous-kpi-title {
        font-size: 11px;
        font-weight: 800;
        color: #64748B;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }
      .sous-kpi-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--kpi-bg-icon, #EFF6FF);
        color: var(--kpi-color-icon, #1E3A5F);
        transition: transform 0.2s ease;
      }
      .sous-kpi-card:hover .sous-kpi-icon {
        transform: scale(1.08);
      }
      .sous-kpi-val {
        font-size: 24px;
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -0.5px;
        color: #0F172A;
        margin-bottom: 10px;
      }
      .sous-kpi-footer {
        font-size: 12px;
        color: #64748B;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 6px;
      }
      .sous-kpi-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
      }
      .btn-filter-status {
        transition: all 0.2s ease;
      }
      .btn-filter-status:hover {
        border-color: #94A3B8;
        transform: translateY(-1px);
      }
      </style>

      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="file-text" style="color: #1E3A5F; width: 26px; height: 26px;"></i>
            <span>Souscriptions Clients</span>
          </h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Contrats d'abonnement packs et suivi des cotisations terrain</p>
        </div>
        <?php if (Context::can('COMMERCIAL_ADD_SOUSCRIPTION')): ?>
        <div style="display: flex; gap: 10px;">
          <a href="<?= RACINE ?>souscription/ressouscription" class="btn" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; text-decoration: none; box-shadow: 0 2px 6px rgba(5,150,105,0.25);">
            <i data-lucide="refresh-cw" style="width: 18px; height: 18px;"></i> Ressouscription
          </a>
          <a href="<?= RACINE ?>souscription/wizard" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 2px 6px rgba(30,58,95,0.25);">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle Souscription
          </a>
        </div>
        <?php endif; ?>
      </div>

      <!-- GRILLE DES KPIS / STATISTIQUES (6 CARTES DYNAMIQUES) -->
      <div class="sous-kpi-grid">

        <!-- Card 1: Total Souscriptions -->
        <div class="sous-kpi-card" data-filter-target="all" style="--kpi-accent: linear-gradient(90deg, #1E3A5F, #0F172A); --kpi-bg-icon: #EFF6FF; --kpi-color-icon: #1E3A5F;" title="Cliquer pour afficher toutes les souscriptions">
          <div class="sous-kpi-header">
            <span class="sous-kpi-title">Souscriptions Totales</span>
            <div class="sous-kpi-icon">
              <i data-lucide="file-text" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div class="sous-kpi-val" style="color: #0F172A;">
            <?= number_format($stats['total_souscriptions'] ?? 0, 0, ',', ' ') ?>
          </div>
          <div class="sous-kpi-footer">
            <div style="display: flex; gap: 6px; flex-wrap: wrap; font-size: 11px; font-weight: 700;">
              <span class="sous-kpi-badge" style="background: #EFF6FF; color: #1E3A5F; border: 1px solid #BFDBFE;">En cours: <?= $stats['total_valide_count'] ?? 0 ?></span>
              <span class="sous-kpi-badge" style="background: #DCFCE7; color: #15803D; border: 1px solid #BBF7D0;">Soldées: <?= $stats['total_solde_count'] ?? 0 ?></span>
            </div>
            <span style="font-size: 11px; color: #64748B; font-weight: 600;"><?= $stats['total_clients_souscripteurs'] ?? 0 ?> client(s)</span>
          </div>
        </div>

        <!-- Card 2: Montant Total Engagé -->
        <div class="sous-kpi-card" data-filter-target="all" style="--kpi-accent: linear-gradient(90deg, #2563EB, #1D4ED8); --kpi-bg-icon: #EFF6FF; --kpi-color-icon: #2563EB;" title="Montant cumulé de l'ensemble des souscriptions">
          <div class="sous-kpi-header">
            <span class="sous-kpi-title">Montant Total Engagé</span>
            <div class="sous-kpi-icon">
              <i data-lucide="trending-up" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div class="sous-kpi-val" style="color: #1E3A5F;">
            <?= number_format($stats['total_engage'] ?? 0, 0, ',', ' ') ?> <small style="font-size: 13px; font-weight: 700; color: #64748B;">FCFA</small>
          </div>
          <div class="sous-kpi-footer">
            <span class="sous-kpi-badge" style="background: #F1F5F9; color: #334155; border: 1px solid #CBD5E1;">
              Actifs : <?= number_format($stats['total_engage_actif'] ?? 0, 0, ',', ' ') ?> FCFA
            </span>
            <span style="font-size: 11px; color: #64748B;">Cumul contractuel</span>
          </div>
        </div>

        <!-- Card 3: Total Recouvré -->
        <div class="sous-kpi-card" data-filter-target="Soldée" style="--kpi-accent: linear-gradient(90deg, #10B981, #047857); --kpi-bg-icon: #ECFDF5; --kpi-color-icon: #059669;" title="Cliquer pour afficher les souscriptions soldées">
          <div class="sous-kpi-header">
            <span class="sous-kpi-title">Total Recouvré</span>
            <div class="sous-kpi-icon">
              <i data-lucide="wallet" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div class="sous-kpi-val" style="color: #059669;">
            <?= number_format($stats['total_cotise'] ?? 0, 0, ',', ' ') ?> <small style="font-size: 13px; font-weight: 700; color: #64748B;">FCFA</small>
          </div>
          <div>
            <div style="height: 6px; background: #E2E8F0; border-radius: 3px; overflow: hidden; margin-bottom: 8px;">
              <div style="width: <?= min(100, $stats['taux_recouvrement'] ?? 0) ?>%; background: #10B981; height: 100%;"></div>
            </div>
            <div class="sous-kpi-footer">
              <span class="sous-kpi-badge" style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0;">
                <i data-lucide="check-circle" style="width: 12px; height: 12px;"></i> <?= $stats['taux_recouvrement'] ?? 0 ?>% recouvré
              </span>
              <span style="font-size: 11px; color: #64748B;">Sur contrats actifs</span>
            </div>
          </div>
        </div>

        <!-- Card 4: Reste à Recouvrer -->
        <div class="sous-kpi-card" data-filter-target="Validée" style="--kpi-accent: linear-gradient(90deg, #EF4444, #B91C1C); --kpi-bg-icon: #FEF2F2; --kpi-color-icon: #DC2626;" title="Cliquer pour afficher les contrats en cours">
          <div class="sous-kpi-header">
            <span class="sous-kpi-title">Reste à Recouvrer</span>
            <div class="sous-kpi-icon">
              <i data-lucide="clock" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div class="sous-kpi-val" style="color: #DC2626;">
            <?= number_format($stats['reste_a_recouvrer'] ?? 0, 0, ',', ' ') ?> <small style="font-size: 13px; font-weight: 700; color: #64748B;">FCFA</small>
          </div>
          <div class="sous-kpi-footer">
            <span class="sous-kpi-badge" style="background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA;">
              <i data-lucide="alert-circle" style="width: 12px; height: 12px;"></i> <?= $stats['total_valide_count'] ?? 0 ?> contrat(s) en cours
            </span>
            <span style="font-size: 11px; color: #64748B;">Encaissements attendus</span>
          </div>
        </div>

        <!-- Card 5: Souscriptions Reconduites -->
        <div class="sous-kpi-card" data-filter-target="Reconduite" style="--kpi-accent: linear-gradient(90deg, #F59E0B, #D97706); --kpi-bg-icon: #FFFBEB; --kpi-color-icon: #D97706;" title="Cliquer pour afficher les contrats reconduits">
          <div class="sous-kpi-header">
            <span class="sous-kpi-title">Contrats Reconduits</span>
            <div class="sous-kpi-icon">
              <i data-lucide="refresh-cw" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div class="sous-kpi-val" style="color: #D97706;">
            <?= number_format($stats['total_reconduite_count'] ?? 0, 0, ',', ' ') ?> <small style="font-size: 13px; font-weight: 700; color: #B45309;">contrat(s)</small>
          </div>
          <div class="sous-kpi-footer">
            <span class="sous-kpi-badge" style="background: #FFFBEB; color: #92400E; border: 1px solid #FDE68A;">
              <i data-lucide="repeat" style="width: 12px; height: 12px;"></i> Engagé : <?= number_format($stats['total_reconduite_engage'] ?? 0, 0, ',', ' ') ?> FCFA
            </span>
            <span style="font-size: 11px; color: #92400E; font-weight: 600;">Cotisé : <?= number_format($stats['total_reconduite_cotise'] ?? 0, 0, ',', ' ') ?> FCFA</span>
          </div>
        </div>

        <!-- Card 6: Souscriptions Annulées -->
        <div class="sous-kpi-card" data-filter-target="Annulée" style="--kpi-accent: linear-gradient(90deg, #991B1B, #7F1D1D); --kpi-bg-icon: #FEF2F2; --kpi-color-icon: #991B1B;" title="Cliquer pour afficher les contrats annulés">
          <div class="sous-kpi-header">
            <span class="sous-kpi-title">Contrats Annulés</span>
            <div class="sous-kpi-icon">
              <i data-lucide="ban" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div class="sous-kpi-val" style="color: #991B1B;">
            <?= number_format($stats['total_annule_count'] ?? 0, 0, ',', ' ') ?> <small style="font-size: 13px; font-weight: 700; color: #991B1B;">contrat(s)</small>
          </div>
          <div class="sous-kpi-footer">
            <span class="sous-kpi-badge" style="background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA;">
              <i data-lucide="x-circle" style="width: 12px; height: 12px;"></i> Annulé : <?= number_format($stats['total_annule_engage'] ?? 0, 0, ',', ' ') ?> FCFA
            </span>
            <span style="font-size: 11px; color: #991B1B; font-weight: 600;">Cotisé : <?= number_format($stats['total_annule_cotise'] ?? 0, 0, ',', ' ') ?> FCFA</span>
          </div>
        </div>

      </div>

      <!-- BARRE DE FILTRES RAPIDES DYNAMIQUES -->
      <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; background: #FFFFFF; border-radius: 12px; padding: 12px 18px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
          <span style="font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; margin-right: 6px; letter-spacing: 0.5px;">Filtrer par :</span>
          
          <button type="button" class="btn btn-sm btn-filter-status active" data-status="all" style="background: #1E3A5F; color: #FFFFFF; font-weight: 700; border-radius: 20px; padding: 6px 14px; border: 1px solid #1E3A5F; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
            <span>Toutes les souscriptions</span>
            <span style="background: rgba(255,255,255,0.25); color: #FFFFFF; padding: 2px 7px; border-radius: 10px; font-size: 11px; font-weight: 800;"><?= $stats['total_souscriptions'] ?? 0 ?></span>
          </button>
          
          <button type="button" class="btn btn-sm btn-filter-status" data-status="Validée" style="background: #F8FAFC; color: #475569; font-weight: 700; border-radius: 20px; padding: 6px 14px; border: 1px solid #CBD5E1; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
            <span>En cours / Validées</span>
            <span style="background: #EFF6FF; color: #1E3A5F; padding: 2px 7px; border-radius: 10px; font-size: 11px; font-weight: 800; border: 1px solid #BFDBFE;"><?= $stats['total_valide_count'] ?? 0 ?></span>
          </button>
          
          <button type="button" class="btn btn-sm btn-filter-status" data-status="Soldée" style="background: #F8FAFC; color: #475569; font-weight: 700; border-radius: 20px; padding: 6px 14px; border: 1px solid #CBD5E1; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
            <span>Soldées</span>
            <span style="background: #DCFCE7; color: #15803D; padding: 2px 7px; border-radius: 10px; font-size: 11px; font-weight: 800; border: 1px solid #BBF7D0;"><?= $stats['total_solde_count'] ?? 0 ?></span>
          </button>
          
          <button type="button" class="btn btn-sm btn-filter-status" data-status="Reconduite" style="background: #F8FAFC; color: #475569; font-weight: 700; border-radius: 20px; padding: 6px 14px; border: 1px solid #CBD5E1; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
            <span>Reconduites</span>
            <span style="background: #FEF3C7; color: #92400E; padding: 2px 7px; border-radius: 10px; font-size: 11px; font-weight: 800; border: 1px solid #FDE68A;"><?= $stats['total_reconduite_count'] ?? 0 ?></span>
          </button>
          
          <button type="button" class="btn btn-sm btn-filter-status" data-status="Annulée" style="background: #F8FAFC; color: #475569; font-weight: 700; border-radius: 20px; padding: 6px 14px; border: 1px solid #CBD5E1; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
            <span>Annulées</span>
            <span style="background: #FEE2E2; color: #B91C1C; padding: 2px 7px; border-radius: 10px; font-size: 11px; font-weight: 800; border: 1px solid #FECACA;"><?= $stats['total_annule_count'] ?? 0 ?></span>
          </button>
        </div>
      </div>

      <!-- CARTE TABLEAU PRINCIPAL -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-souscriptions" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px;">Code Souscription</th>
                <th style="padding: 12px; text-align: center;">Statut</th>
                <th style="padding: 12px;">Client</th>
                <th style="padding: 12px;">Session</th>
                <th style="padding: 12px;">Cotis. / Jour</th>
                <th style="padding: 12px; text-align: right;">Total Souscription</th>
                <th style="padding: 12px;">Progression</th>
                <th style="padding: 12px; text-align: right;">Total Cotisé</th>
                <th style="padding: 12px; text-align: right;">Reste à Payer</th>
                <th style="padding: 12px;">Date</th>
                <th style="padding: 12px; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>
<script src="<?= RACINE ?>public/assets/js/modules/souscriptions.js?v=1.4"></script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
