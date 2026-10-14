<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px; width: 100%; max-width: 100%; box-sizing: border-box;">
      
      <!-- EN-TÊTE DE PAGE -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);">
            <i data-lucide="user-check" style="width: 24px; height: 24px; color: #FFFFFF;"></i>
          </div>
          <div>
            <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0; line-height: 1.2;">
              Répertoire des Agents & Force de Vente
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Gestion des commerciaux terrain, affectations par zone et suivi des portefeuilles
            </p>
          </div>
        </div>

        <?php if (Context::can('ADMIN_MANAGE_USERS') || Context::can('GESTIONNAIRE_MANAGE_PACKS')): ?>
        <a href="<?= RACINE ?>user/formulaire" class="btn" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; font-weight: 800; border-radius: 10px; padding: 12px 22px; font-size: 14px; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2); text-decoration: none; cursor: pointer;">
          <i data-lucide="user-plus" style="width: 18px; height: 18px;"></i> Recruter un Commercial
        </a>
        <?php endif; ?>
      </div>

      <!-- BARRE DE FILTRAGE PAR ZONE -->
      <div style="background: #FFFFFF; border-radius: 14px; padding: 16px 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.03); margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
          <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: #1E293B; font-size: 13px;">
            <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(5, 150, 105, 0.1); color: #059669; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="map-pin" style="width: 17px; height: 17px;"></i>
            </div>
            <span>Secteur / Zone Commerciale :</span>
          </div>

          <div style="min-width: 260px;">
            <?php if (!empty($hasJoker)): ?>
              <select id="filter-zone-gest" class="form-control" disabled readonly aria-readonly="true" style="border: 1.5px solid #CBD5E1; border-radius: 8px; padding: 8px 12px; font-size: 13px; font-weight: 700; color: #1E3A5F; background: #F8FAFC; width: 100%; cursor: not-allowed; opacity: 0.85;">
                <option value="">Toutes les zones (Affichage Global)</option>
                <?php if (!empty($zones)): ?>
                  <?php foreach ($zones as $z): ?>
                    <option value="<?= htmlspecialchars($z['code_zone']) ?>" <?= ($userZoneCode ?? Context::zone()) === $z['code_zone'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($z['libelle_zone']) ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            <?php else: ?>
              <div style="display: flex; align-items: center; gap: 8px;">
                <select id="filter-zone-gest" class="form-control" disabled readonly aria-readonly="true" style="border: 1.5px solid #E2E8F0; border-radius: 8px; padding: 8px 12px; font-size: 13px; font-weight: 700; color: #1E3A5F; background: #F8FAFC; cursor: not-allowed; min-width: 220px; opacity: 0.85;">
                  <option value="<?= htmlspecialchars($userZoneCode ?? '') ?>" selected>
                    <?= htmlspecialchars($userZoneLibelle ?? 'Zone assignée') ?>
                  </option>
                </select>
                <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; color: #B45309; background: #FEF3C7; border: 1px solid #FDE68A; padding: 4px 8px; border-radius: 6px; white-space: nowrap;">
                  <i data-lucide="lock" style="width: 12px; height: 12px;"></i> Zone restreinte
                </span>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <?php if (!empty($hasJoker)): ?>
          <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; color: #059669; background: #ECFDF5; border: 1px solid #A7F3D0; padding: 5px 12px; border-radius: 20px;">
            <i data-lucide="shield-check" style="width: 14px; height: 14px;"></i> Accès SuperAdmin (Vue globale)
          </span>
        <?php endif; ?>
      </div>

      <!-- CARTE TABLEAU PRINCIPALE -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-users-gest" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse; font-size: 13px;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 2px solid #E2E8F0;">
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Code Agent</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Commercial / Agent</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Contact (Tél / Email)</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Zone Affectée</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: center;">Portefeuille Clients</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: center;">Souscriptions Actives</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: center;">Statut</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<script>
$(function() {
  const racine = window.AppConfig ? window.AppConfig.racine : (window.RACINE || '/');
  const $table = $('#table-users-gest');

  if ($table.length) {
    const dataTable = $table.DataTable({
      ajax: {
        url: racine + 'user/apiGestionnaireList',
        type: 'POST',
        data: function(d) {
          d.zone_code = $('#filter-zone-gest').val() || '';
        }
      },
      processing: true,
      autoWidth: false,
      columns: [
        { 
          data: 'code', 
          render: (d) => `<code style="font-weight:700; color:#059669; background:#ECFDF5; padding:4px 8px; border-radius:6px; font-size:12px; border:1px solid #A7F3D0;">${d || '-'}</code>`
        },
        { 
          data: null, 
          render: (d) => `<strong style="color:#0F172A; font-size:14px; font-weight:800;">${d.nom || ''} ${d.prenom || ''}</strong>`
        },
        { 
          data: null, 
          render: (d) => `
            <div><i data-lucide="phone" style="width:12px;height:12px;color:#059669;margin-right:4px;"></i><strong>${d.telephone || '-'}</strong></div>
            <div style="font-size:11px; color:#64748B;">${d.email || ''}</div>
          `
        },
        { 
          data: 'zone', 
          render: (d) => `<span style="background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; padding:3px 10px; border-radius:6px; font-weight:700; font-size:11px;">${d || 'Globale'}</span>`
        },
        { 
          data: 'total_clients', 
          className: 'text-center',
          render: (d) => `<span style="background:#F1F5F9; color:#0F172A; font-weight:800; padding:4px 10px; border-radius:20px; font-size:12px;">${d || 0} clients</span>`
        },
        { 
          data: 'total_souscriptions', 
          className: 'text-center',
          render: (d) => `<span style="background:#F0FDFA; color:#0D9488; border:1px solid #99F6E4; font-weight:800; padding:4px 12px; border-radius:20px; font-size:12px;">${d || 0} souscr.</span>`
        },
        { 
          data: 'statut', 
          className: 'text-center',
          render: (d) => d === 'actif'
            ? `<span style="background:#ECFDF5; color:#059669; border:1px solid #A7F3D0; padding:4px 12px; border-radius:20px; font-weight:800; font-size:11px;">Actif</span>`
            : `<span style="background:#FEE2E2; color:#DC2626; border:1px solid #FECACA; padding:4px 12px; border-radius:20px; font-weight:800; font-size:11px;">Inactif</span>`
        },
        { 
          data: null, 
          className: 'text-end',
          orderable: false,
          render: function(d) {
            return `
              <div style="display:flex; justify-content:flex-end; gap:6px; align-items:center;">
                <a href="${racine}user/details/${d.editId}" class="btn" style="background:#1E3A5F; color:#FFF; font-weight:700; border-radius:8px; padding:6px 12px; font-size:12px; text-decoration:none;" title="Fiche Agent"><i data-lucide="eye" style="width:14px;height:14px;"></i> Détails</a>
                <a href="${racine}user/edition/${d.editId}" class="btn" style="background:#F1F5F9; color:#1E3A5F; font-weight:700; border-radius:8px; padding:6px 12px; font-size:12px; text-decoration:none; border:1px solid #CBD5E1;" title="Modifier"><i data-lucide="edit" style="width:14px;height:14px;"></i> Modifier</a>
              </div>
            `;
          }
        }
      ],
      language: { url: racine + 'json/datatables-i18n-fr-FR.json' },
      drawCallback: () => {
        if (window.lucide) lucide.createIcons();
      }
    });

    $('#filter-zone-gest').on('change', function() {
      dataTable.ajax.reload();
    });
  }
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
