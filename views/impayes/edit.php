<?php require_once __DIR__ . '/../../public/inc/header.php'; ?>
<?php
$overdueList = (new ModelImpayes())->getOverdueStudents();
?>
<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>
    <div class="content-wrapper" style="padding: 24px;">
      
      <!-- Page Header -->
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
          <h1 style="font-size: 22px; font-weight: 800; color: #0F172A; margin: 0;"><?= !empty($item['id_relance']) ? 'Éditer ' : 'Nouvelle ' ?> Relance d'Impayés Scolaires</h1>
          <p style="color: #64748B; font-size: 13px; margin: 4px 0 0 0;">Émission et suivi des rappels de règlement auprès des étudiants et tuteurs</p>
        </div>
        <a href="<?= RACINE ?>impayes/list" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
          <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Historique des Relances
        </a>
      </div>

      <!-- Preview Banner Fiche Synthèse Impayé -->
      <div id="overdue-preview-banner" class="card" style="display: none; background: #FFFBEB; border: 1.5px solid #FCD34D; border-radius: 12px; padding: 20px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(245,158,11,0.08);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
          
          <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 52px; height: 52px; border-radius: 50%; background: #D97706; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 18px;" id="prev_avatar">ET</div>
            <div>
              <div style="font-weight: 800; color: #78350F; font-size: 16px;" id="prev_nom">Nom Élève</div>
              <div style="font-size: 13px; color: #92400E; margin-top: 2px;">
                Matricule : <code id="prev_matricule" style="font-weight:800; color:#D97706;">-</code> &bull; 
                Classe : <span id="prev_classe" style="font-weight:700;">-</span>
              </div>
            </div>
          </div>

          <div style="display: flex; gap: 16px; flex-wrap: wrap;">
            <div style="background: #FFFFFF; padding: 10px 16px; border-radius: 10px; border: 1px solid #FDE68A;">
              <div style="font-size: 11px; font-weight: 800; color: #92400E; text-transform: uppercase;">Parent / Tuteur</div>
              <div style="font-size: 14px; font-weight: 800; color: #78350F; margin-top: 2px;" id="prev_parent">Non renseigné</div>
            </div>

            <div style="background: #FFFFFF; padding: 10px 16px; border-radius: 10px; border: 1px solid #FDE68A;">
              <div style="font-size: 11px; font-weight: 800; color: #92400E; text-transform: uppercase;">Téléphone Contact</div>
              <div style="font-size: 14px; font-weight: 800; color: #D97706; margin-top: 2px;" id="prev_tel">-</div>
            </div>

            <div style="background: #FEF2F2; padding: 10px 16px; border-radius: 10px; border: 1px solid #FCA5A5;">
              <div style="font-size: 11px; font-weight: 800; color: #991B1B; text-transform: uppercase;">Solde Impayé Dû</div>
              <div style="font-size: 16px; font-weight: 800; color: #DC2626; margin-top: 2px;" id="prev_solde">0 FCFA</div>
            </div>
          </div>

        </div>
      </div>

      <!-- Form Card -->
      <div class="card" style="background: #FFFFFF; border-radius: 12px; padding: 28px; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
        <form id="form-relance-impaye" action="<?= RACINE ?>impayes/<?= !empty($item['id_relance']) ? 'edit' : 'add' ?>" method="POST" style="width: 100%;">
          <input type="hidden" name="csrf_token" value="<?= Validator::generateCsrfToken() ?>">
          <?php if (!empty($item['id_relance'])): ?>
            <input type="hidden" name="id_relance" value="<?= $item['id_relance'] ?>">
          <?php endif; ?>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; width: 100%;">
            
            <!-- Sélection Élève en Impayé -->
            <div class="form-group" style="width: 100%; grid-column: 1 / -1;">
              <label style="display: block; font-weight: 700; font-size: 13.5px; color: #1E3A5F; margin-bottom: 6px;">
                <i data-lucide="user-x" style="width: 16px; height: 16px; vertical-align: -2px; color: #DC2626;"></i> Sélectionner l'étudiant en retard de paiement <span style="color: #EF4444;">*</span>
              </label>
              <select class="form-control select2" id="select_overdue_student" name="etudiant_code" style="width: 100%;" required>
                <option value="">-- Rechercher l'étudiant débiteur par Matricule ou Nom --</option>
                <?php foreach($overdueList as $ov): ?>
                  <?php
                    $reliquat = (float)$ov['montant_scolarite_inscription'] - (float)$ov['total_paye'];
                    $nom = trim($ov['nom_etudiant'] . ' ' . $ov['prenom_etudiant']);
                    $labelOpt = "{$ov['matricule_etudiant']} - {$nom} ({$ov['libelle_classe']}) [Reste: " . number_format($reliquat, 0, ',', ' ') . " FCFA]";
                  ?>
                  <option value="<?= $ov['code_etudiant'] ?>" 
                          data-nom="<?= htmlspecialchars($nom) ?>"
                          data-matricule="<?= htmlspecialchars($ov['matricule_etudiant']) ?>"
                          data-classe="<?= htmlspecialchars($ov['libelle_classe'] ?? '-') ?>"
                          data-parent="<?= htmlspecialchars($ov['nom_parent']) ?>"
                          data-tel="<?= htmlspecialchars($ov['tel_parent']) ?>"
                          data-solde="<?= $reliquat ?>"
                          data-inscription="<?= $ov['code_inscription'] ?>"
                          <?= (($item['etudiant_code'] ?? '') == $ov['code_etudiant']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($labelOpt) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <input type="hidden" id="inp_inscription_code" name="inscription_code" value="<?= htmlspecialchars($item['inscription_code'] ?? '') ?>">
            <input type="hidden" id="inp_montant_impaye" name="montant_impaye" value="<?= htmlspecialchars($item['montant_impaye'] ?? 0) ?>">

            <!-- Niveau de Relance -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Niveau & Sévérité de la relance <span style="color: #EF4444;">*</span></label>
              <select class="form-control" id="inp_niveau_relance" name="niveau_relance" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1;" required>
                <option value="rappel_amiable" <?= (($item['niveau_relance'] ?? '') === 'rappel_amiable') ? 'selected' : '' ?>>Niveau 1 : Rappel Amiable (Notification d'échéance)</option>
                <option value="relance_ferme" <?= (($item['niveau_relance'] ?? '') === 'relance_ferme') ? 'selected' : '' ?>>Niveau 2 : Relance Ferme (Délai 48h avant pénalités)</option>
                <option value="mise_en_demeure" <?= (($item['niveau_relance'] ?? '') === 'mise_en_demeure') ? 'selected' : '' ?>>Niveau 3 : Mise en Demeure / Suspension de cours</option>
              </select>
            </div>

            <!-- Canal de communication WhatsApp Unique -->
            <input type="hidden" name="canal_relance" value="whatsapp">
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Canal de transmission <span style="color: #166534;">*</span></label>
              <div style="padding: 11px 14px; background: #DCFCE7; border: 1.5px solid #86EFAC; border-radius: 8px; font-weight: 800; color: #166534; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="message-square" style="width: 18px; height: 18px; color: #16A34A;"></i> Message WhatsApp Pro (Canal Unique)
              </div>
            </div>

            <!-- Numéro WhatsApp destinataire -->
            <div class="form-group" style="width: 100%; box-sizing: border-box;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Numéro WhatsApp Parent / Tuteur <span style="color: #EF4444;">*</span></label>
              <input type="text" id="inp_telephone_destinataire" class="form-control" style="width: 100%; box-sizing: border-box; padding: 11px 14px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; font-weight: 700;" name="telephone_destinataire" value="<?= htmlspecialchars($item['telephone_destinataire'] ?? '') ?>" placeholder="Ex: 0708091011 ou 2250708091011" required>
            </div>

            <!-- Message pré-rédigé WhatsApp -->
            <div class="form-group" style="width: 100%; box-sizing: border-box; grid-column: 1 / -1;">
              <label style="display: block; font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 6px;">Contenu du message WhatsApp (Généré automatiquement) <span style="color: #EF4444;">*</span></label>
              <textarea id="inp_message_relance" class="form-control" style="width: 100%; box-sizing: border-box; padding: 12px 16px; font-size: 14px; border-radius: 8px; border: 1px solid #CBD5E1; line-height: 1.5; color: #0F172A;" name="message_relance" rows="4" required><?= htmlspecialchars($item['message_relance'] ?? '') ?></textarea>
            </div>

          </div>

          <div style="display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; width: 100%; flex-wrap: wrap;">
            <button type="submit" id="btn_save_and_send_wa" class="btn btn-success" style="background: #16A34A; border-color: #16A34A; font-weight: 800; border-radius: 8px; padding: 11px 28px; display: inline-flex; align-items: center; gap: 8px; color: #FFF;">
              <i data-lucide="message-square" style="width: 18px; height: 18px;"></i> Envoyer par WhatsApp & Enregistrer
            </button>
            <a href="<?= RACINE ?>impayes/list" class="btn btn-secondary" style="font-weight: 600; border-radius: 8px; padding: 11px 24px;">Annuler</a>
          </div>

        </form>
      </div>

    </div>
  </main>
</div>

<script>
$(document).ready(function() {
  if (window.lucide) lucide.createIcons();

  if ($.fn.select2) {
    $('#select_overdue_student').select2({
      placeholder: "-- Rechercher l'étudiant débiteur par Matricule ou Nom --",
      allowClear: true,
      width: '100%'
    });
  }

  function updateRelanceTemplate() {
    var $opt = $('#select_overdue_student option:selected');
    if (!$opt.val()) {
      $('#overdue-preview-banner').slideUp(200);
      return;
    }

    var nom = $opt.data('nom') || '';
    var mat = $opt.data('matricule') || '';
    var classe = $opt.data('classe') || '';
    var parent = $opt.data('parent') || '';
    var tel = $opt.data('tel') || '';
    var solde = parseFloat($opt.data('solde')) || 0;
    var inscription = $opt.data('inscription') || '';

    var initials = nom.split(' ').map(function(n) { return n[0]; }).join('').substr(0,2).toUpperCase();
    $('#prev_avatar').text(initials || 'ET');
    $('#prev_nom').text(nom);
    $('#prev_matricule').text(mat);
    $('#prev_classe').text(classe);
    $('#prev_parent').text(parent);
    $('#prev_tel').text(tel);
    $('#prev_solde').text(Number(solde).toLocaleString('fr-FR') + ' FCFA');

    $('#inp_inscription_code').val(inscription);
    $('#inp_montant_impaye').val(solde);
    $('#inp_telephone_destinataire').val(tel);

    var soldeFmt = Number(solde).toLocaleString('fr-FR') + ' FCFA';
    var niv = $('#inp_niveau_relance').val();
    var msg = "";

    if (niv === 'rappel_amiable') {
      msg = "Bonjour M/Mme " + parent + ", nous vous rappelons amicalement que le reliquat de scolarité de l'élève " + nom + " (" + mat + ") s'élève à " + soldeFmt + ". Merci de régulariser la situation au guichet de l'établissement.";
    } else if (niv === 'relance_ferme') {
      msg = "AVERTISSEMENT : M/Mme " + parent + ", la scolarité de l'élève " + nom + " (" + mat + ") accuse un retard de " + soldeFmt + ". Merci de procéder au paiement sous 48h afin d'éviter les pénalités de retard.";
    } else {
      msg = "MISE EN DEMEURE : M/Mme " + parent + ", malgré nos relances, le solde de " + soldeFmt + " pour l'élève " + nom + " n'a pas été réglé. À défaut de régularisation sous 24h, l'accès aux cours et examens sera temporairement suspendu.";
    }

    if (!$('#inp_message_relance').data('user-edited')) {
      $('#inp_message_relance').val(msg);
    }

    $('#overdue-preview-banner').slideDown(250);
  }

  $('#select_overdue_student, #inp_niveau_relance').on('change', function() {
    $('#inp_message_relance').data('user-edited', false);
    updateRelanceTemplate();
  });

  $('#inp_message_relance').on('input', function() {
    $(this).data('user-edited', true);
  });

  if ($('#select_overdue_student').val()) {
    updateRelanceTemplate();
  }

  // AJAX submit to guarantee database persistence THEN open WhatsApp
  $('#form-relance-impaye').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var actionUrl = $form.attr('action');
    var formData = $form.serialize();

    var rawPhone = $('#inp_telephone_destinataire').val().replace(/[^0-9]/g, '');
    if (rawPhone && !rawPhone.startsWith('225') && rawPhone.length === 10) {
      rawPhone = '225' + rawPhone;
    }
    var text = encodeURIComponent($('#inp_message_relance').val());
    var waUrl = 'https://api.whatsapp.com/send?phone=' + rawPhone + '&text=' + text;

    loading('#btn_save_and_send_wa', true, 'Enregistrement en cours...');

    $.ajax({
      url: actionUrl,
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function(res) {
        loading('#btn_save_and_send_wa', false, 'Envoyer par WhatsApp & Enregistrer');
        if (res.status === 1 || res.status === true) {
          showToast('Relance d\'impayé enregistrée avec succès dans le système !', 'success');
          if (rawPhone) {
            window.open(waUrl, '_blank');
          }
          setTimeout(function() {
            window.location.href = window.RACINE + 'impayes/list';
          }, 600);
        } else {
          showToast(res.message || 'Erreur lors de l\'enregistrement de la relance', 'error');
        }
      },
      error: function(xhr) {
        loading('#btn_save_and_send_wa', false, 'Envoyer par WhatsApp & Enregistrer');
        showToast('Erreur lors de l\'enregistrement de la relance', 'error');
      }
    });
  });
});
</script>
<?php require_once __DIR__ . '/../../public/inc/footer-link.php'; ?>
