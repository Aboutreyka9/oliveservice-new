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
      .btn-action-icon.warning {
        background: #FFFBEB;
        border-color: #FDE68A;
        color: #D97706;
      }
      .btn-action-icon.warning:hover {
        background: #D97706;
        border-color: #D97706;
        color: #FFFFFF;
        box-shadow: 0 2px 6px rgba(217, 119, 6, 0.25);
      }
      .recon-actions-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
      }
      .recon-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 18px;
        margin-bottom: 24px;
      }
      .recon-kpi-card {
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
      .recon-kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--kpi-accent, #1E3A5F);
        border-radius: 14px 14px 0 0;
      }
      .recon-kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 20px -5px rgba(15, 23, 42, 0.08);
      }
      .recon-kpi-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
      }
      .recon-kpi-title {
        font-size: 11px;
        font-weight: 800;
        color: #64748B;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }
      .recon-kpi-icon {
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
      .recon-kpi-card:hover .recon-kpi-icon {
        transform: scale(1.08);
      }
      .recon-kpi-val {
        font-size: 24px;
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -0.5px;
        color: #0F172A;
        margin-bottom: 10px;
      }
      .recon-kpi-footer {
        font-size: 12px;
        color: #64748B;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 6px;
      }
      .recon-kpi-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
      }
      .btn-filter-recon {
        transition: all 0.2s ease;
      }
      .btn-filter-recon:hover {
        border-color: #94A3B8;
        transform: translateY(-1px);
      }
      </style>

      <!-- EN-TÊTE DE PAGE DÉDIÉE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #D97706 0%, #B45309 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(217, 119, 6, 0.3);">
            <i data-lucide="refresh-cw" style="width: 26px; height: 26px; color: #FFFFFF;"></i>
          </div>
          <div>
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
              <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
                Reconductions des Contrats
              </h1>
              <span style="background: #FEF3C7; color: #92400E; font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid #FDE68A;">
                Page Dédiée
              </span>
            </div>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Supervision, reports de souscriptions, renouvellement d'abonnements et réengagements clients
            </p>
          </div>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <button type="button" id="btn-reload-table" class="btn" style="background: #FFFFFF; border: 1px solid #E2E8F0; color: #475569; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 16px; cursor: pointer; transition: all 0.2s ease;">
            <i data-lucide="rotate-cw" style="width: 16px; height: 16px;"></i> Actualiser
          </button>
          <?php if (Context::can('COMMERCIAL_ADD_SOUSCRIPTION')): ?>
          <a href="<?= RACINE ?>souscription/ressouscription" class="btn" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #FFFFFF; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; text-decoration: none; box-shadow: 0 2px 6px rgba(5,150,105,0.25);">
            <i data-lucide="refresh-cw" style="width: 18px; height: 18px;"></i> Ressouscription Client
          </a>
          <a href="<?= RACINE ?>souscription/wizard" class="btn btn-primary" style="background: #1E3A5F; border-color: #1E3A5F; display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px; box-shadow: 0 2px 6px rgba(30,58,95,0.25);">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> Nouvelle Souscription
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- 4 CARTES KPIS DYNAMIQUES (Strictement évaluées depuis la BDD) -->
      <div class="recon-kpi-grid">

        <!-- Card 1: Contrats Reconduits -->
        <div class="recon-kpi-card" data-filter-target="Reconduite" style="--kpi-accent: linear-gradient(90deg, #F59E0B, #D97706); --kpi-bg-icon: #FFFBEB; --kpi-color-icon: #D97706;" title="Cliquer pour afficher les contrats reconduits">
          <div class="recon-kpi-header">
            <span class="recon-kpi-title">Contrats Reconduits</span>
            <div class="recon-kpi-icon">
              <i data-lucide="refresh-cw" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div class="recon-kpi-val" style="color: #D97706;">
            <?= number_format($stats['total_reconduite_count'] ?? 0, 0, ',', ' ') ?> <small style="font-size: 13px; font-weight: 700; color: #B45309;">contrat(s)</small>
          </div>
          <div class="recon-kpi-footer">
            <span class="recon-kpi-badge" style="background: #FFFBEB; color: #92400E; border: 1px solid #FDE68A;">
              <i data-lucide="users" style="width: 12px; height: 12px;"></i> <?= $stats['clients_reconduits_count'] ?? 0 ?> client(s) distincts
            </span>
            <span style="font-size: 11px; color: #B45309; font-weight: 600;">Statut : reconduite</span>
          </div>
        </div>

        <!-- Card 2: Volume Financier Reconduit -->
        <div class="recon-kpi-card" data-filter-target="Reconduite" style="--kpi-accent: linear-gradient(90deg, #2563EB, #1D4ED8); --kpi-bg-icon: #EFF6FF; --kpi-color-icon: #2563EB;" title="Volume contractuel total des souscriptions reconduites">
          <div class="recon-kpi-header">
            <span class="recon-kpi-title">Volume Financier Reconduit</span>
            <div class="recon-kpi-icon">
              <i data-lucide="trending-up" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div class="recon-kpi-val" style="color: #1E3A5F;">
            <?= number_format($stats['total_reconduite_engage'] ?? 0, 0, ',', ' ') ?> <small style="font-size: 13px; font-weight: 700; color: #64748B;">FCFA</small>
          </div>
          <div class="recon-kpi-footer">
            <span class="recon-kpi-badge" style="background: #EFF6FF; color: #1E40AF; border: 1px solid #BFDBFE;">
              <i data-lucide="shield-check" style="width: 12px; height: 12px;"></i> Recouvrement : <?= $stats['taux_reconduite_recouvrement'] ?? 0 ?>%
            </span>
            <span style="font-size: 11px; color: #64748B;">Engagements reportés</span>
          </div>
        </div>

        <!-- Card 3: Cotisations Recouvrées -->
        <div class="recon-kpi-card" data-filter-target="Reconduite" style="--kpi-accent: linear-gradient(90deg, #10B981, #047857); --kpi-bg-icon: #ECFDF5; --kpi-color-icon: #059669;" title="Total des cotisations enregistrées sur les contrats reconduits">
          <div class="recon-kpi-header">
            <span class="recon-kpi-title">Cotisations Encaissées</span>
            <div class="recon-kpi-icon">
              <i data-lucide="wallet" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div class="recon-kpi-val" style="color: #059669;">
            <?= number_format($stats['total_reconduite_cotise'] ?? 0, 0, ',', ' ') ?> <small style="font-size: 13px; font-weight: 700; color: #64748B;">FCFA</small>
          </div>
          <div>
            <div style="height: 6px; background: #E2E8F0; border-radius: 3px; overflow: hidden; margin-bottom: 8px;">
              <div style="width: <?= min(100, $stats['taux_reconduite_recouvrement'] ?? 0) ?>%; background: #10B981; height: 100%;"></div>
            </div>
            <div class="recon-kpi-footer">
              <span class="recon-kpi-badge" style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0;">
                <i data-lucide="check-circle" style="width: 12px; height: 12px;"></i> <?= $stats['taux_reconduite_recouvrement'] ?? 0 ?>% payé
              </span>
              <span style="font-size: 11px; color: #64748B;">Sur contrats reconduits</span>
            </div>
          </div>
        </div>

        <!-- Card 4: Contrats Éligibles à la Reconduction -->
        <div class="recon-kpi-card" data-filter-target="Éligible" style="--kpi-accent: linear-gradient(90deg, #1E3A5F, #0F172A); --kpi-bg-icon: #EFF6FF; --kpi-color-icon: #1E3A5F;" title="Cliquer pour afficher les contrats prêts à être reconduits">
          <div class="recon-kpi-header">
            <span class="recon-kpi-title">Contrats Éligibles au Report</span>
            <div class="recon-kpi-icon">
              <i data-lucide="user-check" style="width: 20px; height: 20px;"></i>
            </div>
          </div>
          <div class="recon-kpi-val" style="color: #1E3A5F;">
            <?= number_format($stats['total_eligible_count'] ?? 0, 0, ',', ' ') ?> <small style="font-size: 13px; font-weight: 700; color: #64748B;">contrat(s)</small>
          </div>
          <div class="recon-kpi-footer">
            <span class="recon-kpi-badge" style="background: #DCFCE7; color: #15803D; border: 1px solid #BBF7D0;">
              <i data-lucide="zap" style="width: 12px; height: 12px;"></i> <?= number_format($stats['total_eligible_engage'] ?? 0, 0, ',', ' ') ?> FCFA
            </span>
            <span style="font-size: 11px; color: #15803D; font-weight: 700;">Prêts pour réengagement</span>
          </div>
        </div>

      </div>

      <!-- BARRE DE FILTRES RAPIDES DÉDIÉE -->
      <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; background: #FFFFFF; border-radius: 12px; padding: 12px 18px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
          <span style="font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; margin-right: 6px; letter-spacing: 0.5px;">Vue :</span>
          
          <button type="button" class="btn btn-sm btn-filter-recon active" data-status="Reconduite" style="background: #1E3A5F; color: #FFFFFF; border: 1px solid #1E3A5F; font-weight: 700; border-radius: 20px; padding: 6px 14px; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
            <i data-lucide="refresh-cw" style="width: 14px; height: 14px;"></i>
            <span>Contrats Reconduits</span>
            <span style="background: #FEF3C7; color: #92400E; padding: 2px 7px; border-radius: 10px; font-size: 11px; font-weight: 800; border: 1px solid #FDE68A;"><?= $stats['total_reconduite_count'] ?? 0 ?></span>
          </button>
          
          <button type="button" class="btn btn-sm btn-filter-recon" data-status="Éligible" style="background: #F8FAFC; color: #475569; border: 1px solid #CBD5E1; font-weight: 700; border-radius: 20px; padding: 6px 14px; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
            <i data-lucide="check-circle-2" style="width: 14px; height: 14px; color: #10B981;"></i>
            <span>Éligibles à la Reconduction</span>
            <span style="background: #DCFCE7; color: #15803D; padding: 2px 7px; border-radius: 10px; font-size: 11px; font-weight: 800; border: 1px solid #BBF7D0;"><?= $stats['total_eligible_count'] ?? 0 ?></span>
          </button>
          
          <button type="button" class="btn btn-sm btn-filter-recon" data-status="all" style="background: #F8FAFC; color: #475569; border: 1px solid #CBD5E1; font-weight: 700; border-radius: 20px; padding: 6px 14px; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
            <span>Tous les contrats</span>
            <span style="background: #F1F5F9; color: #475569; padding: 2px 7px; border-radius: 10px; font-size: 11px; font-weight: 800;"><?= $stats['total_tous_count'] ?? 0 ?></span>
          </button>
        </div>

        <div style="font-size: 12px; color: #64748B; font-weight: 600;">
          <i data-lucide="info" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; margin-right: 4px; color: #0284C7;"></i>
          Filtrez ou sélectionnez un contrat pour effectuer une reconduction
        </div>
      </div>

      <!-- CARTE TABLEAU PRINCIPAL -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 24px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-reconductions" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B;">
                <th style="padding: 12px;">Code Contrat</th>
                <th style="padding: 12px; text-align: center;">État Reconduction</th>
                <th style="padding: 12px;">Client</th>
                <th style="padding: 12px;">Session</th>
                <th style="padding: 12px;">Packs</th>
                <th style="padding: 12px;">Cotis. / Jour</th>
                <th style="padding: 12px; text-align: right;">Total Souscription</th>
                <th style="padding: 12px;">Progression</th>
                <th style="padding: 12px; text-align: right;">Total Cotisé</th>
                <th style="padding: 12px; text-align: center;">Date Reconduction</th>
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

<!-- MODAL RAPIDE DE RECONDUCTION -->
<div class="modal-overlay" id="modal-reconduire-contrat" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(5px); padding: 16px; box-sizing: border-box;">
  <div class="modal-card" style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 580px; max-height: calc(100vh - 40px); box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35); overflow: hidden; display: flex; flex-direction: column; border: 1px solid #E2E8F0; margin: auto;">
    
    <!-- EN-TÊTE MODALE -->
    <div class="modal-header" style="background: linear-gradient(135deg, #1E3A5F 0%, #0F172A 100%); color: #FFFFFF; border: none; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #F59E0B; flex-shrink: 0;">
      <div style="display: flex; align-items: center; gap: 12px;">
        <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(245, 158, 11, 0.2); border: 1px solid rgba(245, 158, 11, 0.4); display: flex; align-items: center; justify-content: center;">
          <i data-lucide="refresh-cw" style="width: 20px; height: 20px; color: #FBBF24;"></i>
        </div>
        <div>
          <h5 class="modal-title" style="font-size: 16px; font-weight: 800; margin: 0; color: #FFFFFF; line-height: 1.2;">
            Reconduction du Contrat
          </h5>
          <span style="font-size: 11px; color: #94A3B8; font-weight: 500;">
            Report et réengagement pour une nouvelle session
          </span>
        </div>
      </div>
      <button type="button" class="btn-modal-close" style="background: rgba(255,255,255,0.12); border: none; color: #FFFFFF; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; transition: background 0.2s;" title="Fermer">
        <i data-lucide="x" style="width: 18px; height: 18px; color: #FFFFFF;"></i>
      </button>
    </div>

    <!-- CORPS MODALE -->
    <form id="form-modal-reconduire" method="POST" action="<?= RACINE ?>souscription/reconduire" style="display: flex; flex-direction: column; flex: 1; min-height: 0; margin: 0;">
      <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
      <input type="hidden" name="id" id="recon-modal-id" value="">
      <input type="hidden" name="code_souscription" id="recon-modal-code" value="">

      <div class="modal-body" style="padding: 24px; overflow-y: auto; flex: 1; min-height: 0;">

        <!-- CARTOUCHE INFO CONTRAT -->
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #E2E8F0;">
            <div>
              <span style="color: #64748B; font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; display: block; margin-bottom: 2px;">Contrat Sélectionné</span>
              <span id="recon-modal-code-display" style="font-family: monospace; font-size: 15px; font-weight: 800; color: #1E3A5F; background: #EFF6FF; padding: 3px 8px; border-radius: 6px; border: 1px solid #BFDBFE;">-</span>
            </div>
            <div style="text-align: right;">
              <span style="color: #64748B; font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; display: block; margin-bottom: 2px;">Statut Actuel</span>
              <span id="recon-modal-statut-display" style="font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 20px; background: #DCFCE7; color: #15803D; border: 1px solid #BBF7D0;">Éligible</span>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; font-size: 13px;">
            <div>
              <span style="color: #64748B; font-size: 11px; text-transform: uppercase; font-weight: 700; display: block;">Client</span>
              <strong id="recon-modal-client-display" style="color: #0F172A; font-size: 13px; display: block;">-</strong>
              <small id="recon-modal-phone-display" style="color: #64748B; display: block; font-size: 11px;">-</small>
            </div>
            <div>
              <span style="color: #64748B; font-size: 11px; text-transform: uppercase; font-weight: 700; display: block;">Session en cours</span>
              <strong id="recon-modal-session-display" style="color: #1E3A5F; font-size: 13px;">-</strong>
            </div>
            <div>
              <span style="color: #64748B; font-size: 11px; text-transform: uppercase; font-weight: 700; display: block;">Total Cotisé</span>
              <strong id="recon-modal-cotise-display" style="color: #059669; font-size: 14px;">-</strong>
            </div>
            <div>
              <span style="color: #64748B; font-size: 11px; text-transform: uppercase; font-weight: 700; display: block;">Montant Prévu</span>
              <strong id="recon-modal-total-display" style="color: #1E3A5F; font-size: 14px;">-</strong>
            </div>
          </div>
        </div>

        <!-- EXPLICATION RECONDUCTION -->
        <div style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; display: flex; gap: 12px; align-items: flex-start;">
          <i data-lucide="info" style="width: 20px; height: 20px; color: #D97706; flex-shrink: 0; margin-top: 2px;"></i>
          <div style="font-size: 12px; color: #92400E; line-height: 1.5;">
            Cette opération marque le contrat courant comme <strong>Reconduit</strong> et vous permet d'engager immédiatement la nouvelle souscription de ce client pour la session suivante.
          </div>
        </div>

        <!-- SÉLECTION DE LA NOUVELLE SESSION (OPTIONNELLE) -->
        <?php if (!empty($sessions)): ?>
        <div style="margin-bottom: 18px;">
          <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">
            Session cible pour le nouvel engagement (Optionnel)
          </label>
          <select name="target_session_code" id="target-session-code" class="form-control" style="width: 100%; border-radius: 8px; border: 1px solid #CBD5E1; padding: 10px 12px; font-size: 13px; background: #FFFFFF;">
            <option value="">-- Conserver le choix ultérieur ou choisir la session --</option>
            <?php foreach ($sessions as $sess): ?>
              <option value="<?= htmlspecialchars($sess['code_session']) ?>">
                <?= htmlspecialchars($sess['libelle_session']) ?> (<?= (int)$sess['nombre_jour_session'] ?> jours)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>

        <!-- MOTIF / NOTES -->
        <div style="margin-bottom: 10px;">
          <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">
            Note ou motif de la reconduction (Optionnel)
          </label>
          <textarea name="notes" id="recon-notes" class="form-control" rows="2" placeholder="Ex: Reconduction demandée par le client pour la session suivante..." style="width: 100%; border-radius: 8px; border: 1px solid #CBD5E1; padding: 10px 12px; font-size: 13px; box-sizing: border-box;"></textarea>
        </div>

      </div>

      <!-- PIED DE MODALE -->
      <div class="modal-footer" style="flex-shrink: 0; background: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;">
        <button type="button" class="btn btn-modal-close" style="background: #FFFFFF; color: #475569; font-weight: 700; border-radius: 8px; padding: 10px 18px; border: 1px solid #CBD5E1; font-size: 13px; cursor: pointer; transition: all 0.2s;">
          Annuler
        </button>
        <button type="submit" id="btn-confirm-reconduire" class="btn" style="background: linear-gradient(135deg, #D97706 0%, #B45309 100%); color: #FFFFFF; font-weight: 800; font-size: 13px; border-radius: 8px; padding: 10px 22px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.3); transition: all 0.2s;">
          <i data-lucide="refresh-cw" style="width: 16px; height: 16px;"></i>
          Confirmer la Reconduction
        </button>
      </div>

    </form>
  </div>
</div>

<script src="<?= RACINE ?>public/assets/js/modules/reconductions.js?v=1.1"></script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
