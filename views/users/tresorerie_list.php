<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
  $userRoles = $_SESSION[USERS_AUTH]['roles'] ?? [];
  if (empty($userRoles)) {
      $singleRole = $_SESSION[USERS_AUTH]['role_code'] ?? ($_SESSION['role_code'] ?? '');
      $userRoles = !empty($singleRole) ? [$singleRole] : [];
  }
  $isSuperAdminUser = in_array('ROLE_SUPERADMIN', $userRoles, true);
?>
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
              Utilisateurs & Caisses Trésorerie
            </h1>
            <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0; font-weight: 500;">
              Vue consolidée des comptes utilisateurs, statuts des caisses et performances financières
            </p>
          </div>
        </div>
      </div>

      <!-- CARTE DE STATISTIQUES FINANCIÈRES & CAISSES -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- CARD 1: Total Utilisateurs -->
        <div style="background: #FFFFFF; border-radius: 14px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.03); display: flex; align-items: center; gap: 14px;">
          <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(30, 58, 95, 0.1); color: #1E3A5F; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="users" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Utilisateurs Actifs</div>
            <div style="font-size: 20px; font-weight: 800; color: #0F172A; margin-top: 2px;">
              <?= number_format($stats['total_users'] ?? 0, 0, ',', ' ') ?>
            </div>
          </div>
        </div>

        <!-- CARD 2: Caisses Ouvertes -->
        <div style="background: #FFFFFF; border-radius: 14px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.03); display: flex; align-items: center; gap: 14px;">
          <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(5, 150, 105, 0.1); color: #059669; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="archive" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Caisses Ouvertes</div>
            <div style="font-size: 20px; font-weight: 800; color: #059669; margin-top: 2px;">
              <?= number_format($stats['caisses_ouvertes'] ?? 0, 0, ',', ' ') ?>
            </div>
          </div>
        </div>

        <!-- CARD 3: Cumul Versements Validés -->
        <div style="background: #FFFFFF; border-radius: 14px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.03); display: flex; align-items: center; gap: 14px;">
          <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(59, 130, 246, 0.1); color: #2563EB; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="arrow-down-left" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Versements Validés</div>
            <div style="font-size: 20px; font-weight: 800; color: #2563EB; margin-top: 2px;">
              <?= number_format($stats['total_versements'] ?? 0, 0, ',', ' ') ?> <span style="font-size: 12px; font-weight: 600;">FCFA</span>
            </div>
          </div>
        </div>

        <!-- CARD 4: Cumul Commissions -->
        <div style="background: #FFFFFF; border-radius: 14px; padding: 18px 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.03); display: flex; align-items: center; gap: 14px;">
          <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(217, 119, 6, 0.1); color: #D97706; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="award" style="width: 22px; height: 22px;"></i>
          </div>
          <div>
            <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Total Commissions</div>
            <div style="font-size: 20px; font-weight: 800; color: #D97706; margin-top: 2px;">
              <?= number_format($stats['total_commissions'] ?? 0, 0, ',', ' ') ?> <span style="font-size: 12px; font-weight: 600;">FCFA</span>
            </div>
          </div>
        </div>

      </div>

      <!-- BARRE DE FILTRAGE PAR ZONE -->
      <div style="background: #FFFFFF; border-radius: 14px; padding: 16px 20px; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.03); margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
          <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: #1E293B; font-size: 13px;">
            <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(5, 150, 105, 0.1); color: #059669; display: flex; align-items: center; justify-content: center;">
              <i data-lucide="map-pin" style="width: 17px; height: 17px;"></i>
            </div>
            <span>Zone Commerciale / Périmètre :</span>
          </div>

          <div style="min-width: 260px;">
            <?php if (!empty($hasJoker)): ?>
              <select id="filter-zone" class="form-control" style="border: 1.5px solid #CBD5E1; border-radius: 8px; padding: 8px 12px; font-size: 13px; font-weight: 600; color: #1E293B; background: #FFF; width: 100%; cursor: pointer;">
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
                <select id="filter-zone" class="form-control" disabled readonly aria-readonly="true" style="border: 1.5px solid #E2E8F0; border-radius: 8px; padding: 8px 12px; font-size: 13px; font-weight: 700; color: #1E3A5F; background: #F8FAFC; cursor: not-allowed; min-width: 220px; pointer-events: none;">
                  <option value="<?= htmlspecialchars($userZoneCode ?? '') ?>" selected>
                    <?= htmlspecialchars($userZoneLibelle ?? 'Zone assignée') ?>
                  </option>
                </select>
                <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; color: #B45309; background: #FEF3C7; border: 1px solid #FDE68A; padding: 4px 8px; border-radius: 6px; white-space: nowrap;">
                  <i data-lucide="lock" style="width: 12px; height: 12px;"></i> Zone assignée
                </span>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <?php if (!empty($hasJoker)): ?>
          <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; color: #059669; background: #ECFDF5; border: 1px solid #A7F3D0; padding: 5px 12px; border-radius: 20px;">
            <i data-lucide="shield-check" style="width: 14px; height: 14px;"></i> Accès Trésorerie Global (Toutes zones)
          </span>
        <?php endif; ?>
      </div>

      <!-- CARTE TABLEAU PRINCIPALE (NAVY PREMIUM) -->
      <div class="card-premium" style="background: #FFFFFF; border-radius: 16px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01); width: 100%; box-sizing: border-box; overflow: hidden;">
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table id="table-users-tresorerie" class="table display nowrap" style="width:100%; max-width:100%; border-collapse: collapse; font-size: 13px;">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; color: #64748B; border-bottom: 2px solid #E2E8F0;">
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Code / Utilisateur</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Contact</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Rôle(s) Attribué(s)</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Zone</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: center;">État Caisse</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: right;">Versements Validés</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: center;">Taux Comm.</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: right;">Total Commission</th>
                <th style="padding: 14px 16px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; text-align: center;">Statut Compte</th>
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

<?php require_once __DIR__ . '/../../public/inc/footer-script.php'; ?>
<script>
$(document).ready(function() {
  var table = $('#table-users-tresorerie').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      url: '<?= RACINE ?>user/apiTresorerieList',
      type: 'POST',
      data: function(d) {
        d.zone_code = $('#filter-zone').val();
      }
    },
    columns: [
      { data: null, render: function(d) {
        var nom = (d.nom || '') + ' ' + (d.prenom || '');
        return '<div>' +
                 '<div style="font-weight:700; color:#0F172A; font-size:13.5px;">' + nom + '</div>' +
                 '<div style="font-size:11px; color:#64748B; font-weight:600;"><span style="color:#059669; font-weight:700;">' + d.code + '</span></div>' +
               '</div>';
      }},
      { data: null, render: function(d) {
        var email = d.email ? '<div style="font-size:12px; color:#334155; font-weight:600;"><i data-lucide="mail" style="width:12px;height:12px;display:inline;vertical-align:-1px;margin-right:3px;"></i>' + d.email + '</div>' : '';
        var tel = d.telephone ? '<div style="font-size:11px; color:#64748B; font-weight:500;"><i data-lucide="phone" style="width:11px;height:11px;display:inline;vertical-align:-1px;margin-right:3px;"></i>' + d.telephone + '</div>' : '';
        return email + tel;
      }},
      { data: null, render: function(d) {
        if (!d.roles_list || d.roles_list.length === 0) {
          return '<span style="background:#F1F5F9; color:#64748B; padding:3px 8px; border-radius:6px; font-size:11px; font-weight:600;">Non attribué</span>';
        }
        return d.roles_list.map(function(r) {
          var bg = '#EFF6FF', col = '#1D4ED8';
          if (r.indexOf('Admin') !== -1 || r.indexOf('Super') !== -1) { bg = '#FEE2E2'; col = '#991B1B'; }
          else if (r.indexOf('Finance') !== -1 || r.indexOf('Comptable') !== -1 || r.indexOf('Caisse') !== -1) { bg = '#ECFDF5'; col = '#047857'; }
          else if (r.indexOf('Commercial') !== -1) { bg = '#FEF3C7'; col = '#B45309'; }
          return '<span style="background:' + bg + '; color:' + col + '; padding:3px 8px; border-radius:6px; font-size:11px; font-weight:700; display:inline-block; margin:2px;">' + r + '</span>';
        }).join(' ');
      }},
      { data: 'zone', render: function(z) {
        return '<span style="font-weight:600; color:#334155;"><i data-lucide="map-pin" style="width:12px;height:12px;display:inline;vertical-align:-1px;margin-right:3px;color:#059669;"></i>' + z + '</span>';
      }},
      { data: 'caisse_ouverte', className: 'text-center', render: function(isOuverte) {
        if (isOuverte) {
          return '<span style="background:#ECFDF5; color:#047857; border:1px solid #A7F3D0; font-size:11px; padding:4px 10px; border-radius:20px; font-weight:700; display:inline-flex; align-items:center; gap:4px;"><span style="width:6px;height:6px;border-radius:50%;background:#10B981;display:inline-block;"></span> Ouverte</span>';
        }
        return '<span style="background:#F1F5F9; color:#64748B; border:1px solid #E2E8F0; font-size:11px; padding:4px 10px; border-radius:20px; font-weight:600; display:inline-block;">Fermée</span>';
      }},
      { data: 'total_versements', className: 'text-end', render: function(v) {
        return '<span style="font-weight:800; color:#0F172A; font-size:13.5px;">' + Number(v).toLocaleString('fr-FR') + ' <span style="font-size:11px; font-weight:600; color:#64748B;">FCFA</span></span>';
      }},
      { data: 'commission_rate', className: 'text-center', render: function(c) {
        if (!c || c <= 0) return '<span style="color:#94A3B8; font-size:12px;">0 %</span>';
        return '<span style="background:#FEF3C7; color:#B45309; font-weight:800; padding:2px 8px; border-radius:6px; font-size:12px;">' + c + ' %</span>';
      }},
      { data: 'total_commission', className: 'text-end', render: function(c) {
        if (!c || c <= 0) return '<span style="color:#94A3B8; font-size:12px;">0 FCFA</span>';
        return '<span style="font-weight:800; color:#D97706; font-size:13.5px;">' + Number(c).toLocaleString('fr-FR') + ' <span style="font-size:11px; font-weight:600; color:#B45309;">FCFA</span></span>';
      }},
      { data: 'statut', className: 'text-center', render: function(s) {
        if (s === 'actif') {
          return '<span style="background:#ECFDF5; color:#047857; padding:3px 9px; border-radius:12px; font-size:11px; font-weight:700;">Actif</span>';
        }
        return '<span style="background:#FEF2F2; color:#B91C1C; padding:3px 9px; border-radius:12px; font-size:11px; font-weight:700;">Inactif</span>';
      }},
      { data: null, orderable: false, className: 'text-end', render: function(d) {
        var editId = d.editId || d.id;
        var html = '<div style="display:flex; justify-content:flex-end; gap:6px;">';
        html += '<a href="' + window.RACINE + 'user/details/' + editId + '" class="btn" style="background:#1E3A5F; color:#FFFFFF; font-weight:700; border-radius:8px; padding:5px 10px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-size:12px;" title="Voir profil"><i data-lucide="eye" style="width:13px;height:13px;"></i> Profil</a>';
        html += '<a href="' + window.RACINE + 'caisse_commercial/list?user_code=' + d.code + '" class="btn" style="background:#ECFDF5; color:#047857; font-weight:700; border-radius:8px; padding:5px 10px; text-decoration:none; border:1px solid #A7F3D0; display:inline-flex; align-items:center; gap:4px; font-size:12px;" title="Journal des Caisses"><i data-lucide="archive" style="width:13px;height:13px;"></i> Caisses</a>';
        html += '</div>';
        return html;
      }}
    ],
    language: { url: '<?= RACINE ?>json/datatables-i18n-fr-FR.json' },
    drawCallback: function() { if (window.lucide) lucide.createIcons(); }
  });

  $('#filter-zone').on('change', function() {
    table.ajax.reload();
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
