<?php
/**
 * Script de génération du PDF officiel RBAC : detail_gestion_rbac.pdf
 * Olive Service ERP / CRM
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/const.php';
require_once __DIR__ . '/config/Database.php';

use Mpdf\Mpdf;

$db = (new Database())->getCon();

// 1. Récupération des données DB
$roles = $db->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$permissions = $db->query("SELECT * FROM permissions ORDER BY module_permission ASC, code_permission ASC")->fetchAll(PDO::FETCH_ASSOC);
$rolePermissions = $db->query("SELECT role_code, permission_code FROM role_permissions")->fetchAll(PDO::FETCH_ASSOC);

$rpMatrix = [];
foreach ($rolePermissions as $rp) {
    $rpMatrix[$rp['permission_code']][] = $rp['role_code'];
}

// Configuration mPDF
$mpdf = new Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'margin_left' => 14,
    'margin_right' => 14,
    'margin_top' => 20,
    'margin_bottom' => 20,
    'margin_header' => 8,
    'margin_footer' => 8,
    'default_font' => 'dejavusans'
]);

$mpdf->SetTitle('Olive Service - Détail de la Gestion RBAC & Sécurité');
$mpdf->SetAuthor('Équipe Système & Sécurité Olive Service');
$mpdf->SetCreator('Olive Service ERP');

// En-tête et pied de page
$mpdf->SetHTMLHeader('
<div style="border-bottom: 1.5px solid #CBD5E1; padding-bottom: 6px; font-size: 8pt; color: #64748B; font-family: sans-serif; display: flex; justify-content: space-between;">
    <span style="font-weight: bold; color: #1E3A5F;">OLIVE SERVICE ERP / CRM</span> &mdash;
    <span>RÉFÉRENTIEL OFFICIEL DES DROITS & CONTRÔLE D\'ACCÈS (RBAC)</span>
    <span style="float: right; color: #059669; font-weight: bold;">DOCUMENT SÉCURISÉ v2.0</span>
</div>
');

$mpdf->SetHTMLFooter('
<div style="border-top: 1px solid #E2E8F0; padding-top: 6px; font-size: 8pt; color: #94A3B8; font-family: sans-serif;">
    <table width="100%" style="border: none;">
        <tr>
            <td style="border: none; text-align: left; color: #64748B;">Olive Service &bull; Tous droits réservés &bull; Confidentiel Système</td>
            <td style="border: none; text-align: right; font-weight: bold; color: #1E3A5F;">Page {PAGENO} / {nbpg}</td>
        </tr>
    </table>
</div>
');

// CSS Stylesheet pour le PDF
$stylesheet = '
    body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; color: #1E293B; font-size: 9.5pt; line-height: 1.45; }
    h1 { color: #0F172A; font-size: 20pt; margin-bottom: 4px; font-weight: 800; }
    h2 { color: #1E3A5F; font-size: 13pt; margin-top: 18px; margin-bottom: 8px; font-weight: 700; border-bottom: 2px solid #E2E8F0; padding-bottom: 4px; }
    h3 { color: #0F172A; font-size: 11pt; margin-top: 12px; margin-bottom: 6px; font-weight: 700; }
    p { margin-bottom: 8px; text-align: justify; }
    .badge { display: inline-block; padding: 2px 7px; font-size: 7.5pt; font-weight: bold; border-radius: 4px; text-transform: uppercase; }
    .badge-navy { background-color: #1E3A5F; color: #FFFFFF; }
    .badge-emerald { background-color: #ECFDF5; color: #059669; border: 1px solid #A7F3D0; }
    .badge-amber { background-color: #FEF3C7; color: #B45309; border: 1px solid #FDE68A; }
    .badge-rose { background-color: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA; }
    .badge-slate { background-color: #F1F5F9; color: #475569; border: 1px solid #CBD5E1; }
    
    table.data-table { width: 100%; border-collapse: collapse; margin-top: 8px; margin-bottom: 14px; font-size: 8pt; }
    table.data-table th { background-color: #1E3A5F; color: #FFFFFF; font-weight: bold; text-align: left; padding: 6px 8px; border: 1px solid #1E3A5F; }
    table.data-table td { padding: 5px 8px; border: 1px solid #E2E8F0; vertical-align: middle; }
    table.data-table tr:nth-child(even) td { background-color: #F8FAFC; }
    
    .card-box { background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 10px 14px; margin-bottom: 12px; }
    .callout-success { background-color: #F0FDF4; border-left: 4px solid #059669; padding: 8px 12px; margin: 10px 0; font-size: 8.5pt; }
    .callout-danger { background-color: #FEF2F2; border-left: 4px solid #DC2626; padding: 8px 12px; margin: 10px 0; font-size: 8.5pt; }
    .callout-info { background-color: #F0F9FF; border-left: 4px solid #0284C7; padding: 8px 12px; margin: 10px 0; font-size: 8.5pt; }
    
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .code { font-family: monospace; font-size: 7.8pt; background-color: #F1F5F9; padding: 1px 4px; border-radius: 3px; color: #0F172A; }
';

$html = '
<style>' . $stylesheet . '</style>

<!-- PAGE DE GARDE -->
<div style="text-align: center; padding-top: 40px; padding-bottom: 40px;">
    <div style="font-size: 13pt; font-weight: 800; color: #059669; letter-spacing: 2px; text-transform: uppercase;">
        Olive Service &bull; Système Intégré de Gestion Commerciale & Financière
    </div>
    <div style="margin-top: 30px; margin-bottom: 25px;">
        <h1 style="font-size: 26pt; color: #1E3A5F; line-height: 1.2; margin: 0;">RÉFÉRENTIEL RBAC COMPLET</h1>
        <div style="font-size: 15pt; color: #0F172A; font-weight: 600; margin-top: 10px;">
            Contrôle d\'Accès Basé sur les Rôles, Cartographie Sécuritaire & Découpage Granulaire
        </div>
        <div style="font-size: 11pt; color: #64748B; margin-top: 12px; font-weight: 500;">
            Protection des Contrôleurs, Routes, Vues, Liens de Navigation et Boutons d\'Action
        </div>
    </div>

    <div style="width: 140px; height: 4px; background: linear-gradient(90deg, #1E3A5F, #059669); margin: 25px auto; border-radius: 2px;"></div>

    <div class="card-box" style="max-width: 550px; margin: 30px auto; text-align: left; background: #FFFFFF; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <table style="width: 100%; border: none; font-size: 9pt;">
            <tr>
                <td style="border: none; padding: 5px; color: #64748B; width: 40%; font-weight: bold;">Statut du Document :</td>
                <td style="border: none; padding: 5px; color: #059669; font-weight: bold;">RÉFORME SÉCURITAIRE APPLIQUÉE (v2.0)</td>
            </tr>
            <tr>
                <td style="border: none; padding: 5px; color: #64748B; font-weight: bold;">Date de Référence :</td>
                <td style="border: none; padding: 5px; color: #0F172A;">' . date('d F Y') . '</td>
            </tr>
            <tr>
                <td style="border: none; padding: 5px; color: #64748B; font-weight: bold;">Rôles Modélisés :</td>
                <td style="border: none; padding: 5px; color: #1E3A5F; font-weight: bold;">4 Métiers Opérationnels + 1 Super Administrateur</td>
            </tr>
            <tr>
                <td style="border: none; padding: 5px; color: #64748B; font-weight: bold;">Permissions Actives :</td>
                <td style="border: none; padding: 5px; color: #1E3A5F; font-weight: bold;">34 Permissions Granulaires Enregistrées</td>
            </tr>
            <tr>
                <td style="border: none; padding: 5px; color: #64748B; font-weight: bold;">Niveau de Sécurité :</td>
                <td style="border: none; padding: 5px; color: #DC2626; font-weight: bold;">DOUBLE CONTRÔLE (Serveur 403 + Client UI)</td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 40px; font-size: 8.5pt; color: #94A3B8;">
        Ce document constitue le registre exhaustif de gouvernance des habilitations pour l\'ensemble des modules :<br>
        <strong>Commercial &bull; Finance & Comptabilité &bull; Gestion du Catalogue &bull; Administration Globale</strong>
    </div>
</div>

<pagebreak />

<!-- SECTION 1 : SYNTHÈSE ET ARCHITECTURE -->
<h2>1. Synthèse de la Réforme de Sécurité & Architecture RBAC</h2>
<p>
    Antérieurement à cette réforme, l\'application reposait principalement sur une vérification binaire d\'authentification (<code>$this->requireAuth()</code>). Tout utilisateur connecté possédait potentiellement la capacité d\'invoquer des actions sensibles (validation de versements, modification d\'articles ou de dépenses, suppression d\'utilisateurs) dès lors qu\'il connaissait ou forgeait les requêtes HTTP correspondantes.
</p>
<p>
    La mise à niveau sécuritaire introduit un modèle <strong>RBAC (Role-Based Access Control)</strong> rigoureux et étanche à deux niveaux :
</p>

<div class="callout-success">
    <strong>1. Verrouillage Serveur Strict (Backend) :</strong>
    Chaque contrôleur et chaque méthode vérifie désormais explicitement les autorisations requises via <code>$this->requirePermission(...)</code> ou <code>$this->requireRole(...)</code>. Si l\'utilisateur ne possède pas le droit requis, la requête est immédiatement avortée avec un code <strong>HTTP 403 Forbidden</strong>. En cas d\'appel AJAX, un payload JSON normalisé <code>{"status": 0, "message": "Accès refusé..."}</code> est retourné.
</div>

<div class="callout-info">
    <strong>2. Dégradation et Masquage Dynamique de l\'Interface (Frontend) :</strong>
    Les éléments d\'interface sont synchronisés avec les droits de la session :
    <ul style="margin: 4px 0 0 16px; padding: 0;">
        <li>Les menus et rubriques de la barre latérale (<code>sidbar.php</code>) et de la barre de navigation (<code>nav.php</code>) ne s\'affichent que si l\'utilisateur possède le rôle ou la permission adéquate via <code>Context::can()</code>.</li>
        <li>Les boutons d\'action majeurs (ex. "Saisir un versement", "Nouvelle dépense", "Nouvel article", "Nouvel utilisateur") sont conditionnés en PHP.</li>
        <li>Les boutons de tableau DataTables (Éditer, Activer/Désactiver le statut) sont restreints via <code>window.AppConfig.can(...)</code> injecté dynamiquement dans <code>header.php</code>.</li>
    </ul>
</div>

<!-- SECTION 2 : RÉFÉRENTIEL DES RÔLES -->
<h2>2. Référentiel des Rôles et Périmètres Métier</h2>
<p>
    La plateforme structure l\'organisation du travail autour de 4 rôles opérationnels majeurs, supervisés par le Super Administrateur :
</p>

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 25%;">Code Rôle</th>
            <th style="width: 30%;">Dénomination</th>
            <th style="width: 45%;">Périmètre & Responsabilité Opérationnelle</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><span class="badge badge-navy">ROLE_COMMERCIAL</span></td>
            <td><strong>Commercial Terrain</strong></td>
            <td>Prospection, enrôlement des clients, création de souscriptions, encaissement des cotisations terrain, ouverture/clôture de sa propre caisse journalière, et initiation des versements vers la finance.</td>
        </tr>
        <tr>
            <td><span class="badge badge-navy">ROLE_FINANCE</span></td>
            <td><strong>Responsable Finance & Compta</strong></td>
            <td>Contrôle financier, validation ou rejet des versements initiés par les commerciaux, supervision des clôtures de caisse, engagement et saisie des dépenses et gestion des types de dépenses.</td>
        </tr>
        <tr>
            <td><span class="badge badge-navy">ROLE_GESTIONNAIRE</span></td>
            <td><strong>Gestionnaire Catalogue & Logistique</strong></td>
            <td>Définition du catalogue des articles, composition des packs promotionnels, planification des sessions annuelles, suivi et remise logistique des packs (distributions aux souscripteurs soldés).</td>
        </tr>
        <tr>
            <td><span class="badge badge-navy">ROLE_ADMIN</span></td>
            <td><strong>Administrateur Système</strong></td>
            <td>Administration technique, création et gestion des comptes utilisateurs, attribution des rôles et des permissions, configuration des zones géographiques et des paramètres de l\'établissement.</td>
        </tr>
        <tr>
            <td><span class="badge badge-emerald">ROLE_SUPERADMIN</span></td>
            <td><strong>Super Administrateur</strong></td>
            <td>Droits suprêmes d\'audit, dérogation technique, accès illimité à tous les modules, contournement des verrous d\'activation de jetons et maintenance globale.</td>
        </tr>
    </tbody>
</table>

<pagebreak />

<!-- SECTION 3 : REGISTRE EXHAUSTIF DES 34 PERMISSIONS -->
<h2>3. Registre Exhaustif des 34 Permissions Granulaires par Module</h2>
<p>
    La base de données recense exactement 34 permissions réparties dans 5 modules fonctionnels. Le tableau ci-dessous dresse l\'inventaire complet de ces privilèges unitaires :
</p>

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 8%;">N°</th>
            <th style="width: 28%;">Code Permission</th>
            <th style="width: 22%;">Module</th>
            <th style="width: 42%;">Libellé / Finalité Métier</th>
        </tr>
    </thead>
    <tbody>';

$i = 1;
foreach ($permissions as $perm) {
    $modBadge = 'badge-slate';
    if ($perm['module_permission'] === 'COMMERCIAL') $modBadge = 'badge-amber';
    elseif ($perm['module_permission'] === 'FINANCE') $modBadge = 'badge-emerald';
    elseif ($perm['module_permission'] === 'GESTIONNAIRE' || $perm['module_permission'] === 'CATALOGUE') $modBadge = 'badge-navy';
    elseif ($perm['module_permission'] === 'ADMINISTRATION') $modBadge = 'badge-rose';

    $html .= '
        <tr>
            <td class="text-center">' . $i++ . '</td>
            <td><span class="code"><strong>' . htmlspecialchars($perm['code_permission']) . '</strong></span></td>
            <td><span class="badge ' . $modBadge . '">' . htmlspecialchars($perm['module_permission']) . '</span></td>
            <td>' . htmlspecialchars($perm['libelle_permission']) . '</td>
        </tr>';
}

$html .= '
    </tbody>
</table>

<pagebreak />

<!-- SECTION 4 : MATRICE CROISÉE RÔLES X PERMISSIONS -->
<h2>4. Matrice Croisée Rôles &times; Permissions (Habilitations)</h2>
<p>
    Le tableau croisé ci-après détaille l\'affectation exacte de chacune des 34 permissions aux 4 rôles opérationnels (<code>ROLE_COMMERCIAL</code>, <code>ROLE_FINANCE</code>, <code>ROLE_GESTIONNAIRE</code>, <code>ROLE_ADMIN</code>) ainsi qu\'au <code>ROLE_SUPERADMIN</code>.
</p>

<table class="data-table" style="font-size: 7.2pt;">
    <thead>
        <tr>
            <th style="width: 32%;">Permission</th>
            <th style="width: 14%;" class="text-center">Commercial</th>
            <th style="width: 14%;" class="text-center">Finance</th>
            <th style="width: 14%;" class="text-center">Gestionnaire</th>
            <th style="width: 13%;" class="text-center">Admin</th>
            <th style="width: 13%;" class="text-center">SuperAdmin</th>
        </tr>
    </thead>
    <tbody>';

foreach ($permissions as $perm) {
    $code = $perm['code_permission'];
    $assignedRoles = $rpMatrix[$code] ?? [];

    $isCom = in_array('ROLE_COMMERCIAL', $assignedRoles, true);
    $isFin = in_array('ROLE_FINANCE', $assignedRoles, true);
    $isGes = in_array('ROLE_GESTIONNAIRE', $assignedRoles, true);
    $isAdm = in_array('ROLE_ADMIN', $assignedRoles, true);
    $isSup = in_array('ROLE_SUPERADMIN', $assignedRoles, true) || $isAdm; // SuperAdmin possède toujours les droits

    $mark = function($val) {
        return $val 
            ? '<span style="color:#059669; font-weight:bold; font-size:10pt;">&check;</span>' 
            : '<span style="color:#CBD5E1; font-size:9pt;">&minus;</span>';
    };

    $html .= '
        <tr>
            <td><span class="code">' . htmlspecialchars($code) . '</span></td>
            <td class="text-center">' . $mark($isCom) . '</td>
            <td class="text-center">' . $mark($isFin) . '</td>
            <td class="text-center">' . $mark($isGes) . '</td>
            <td class="text-center">' . $mark($isAdm) . '</td>
            <td class="text-center">' . $mark($isSup) . '</td>
        </tr>';
}

$html .= '
    </tbody>
</table>

<pagebreak />

<!-- SECTION 5 : CARTOGRAPHIE DÉTAILLÉE PAR MODULE -->
<h2>5. Sécurisation Granulaire par Module : Contrôleurs, Routes, Vues, Liens et Boutons</h2>
<p>
    Cette section recense pour chaque ressource du système : l\'URL, le contrôleur concerné, les permissions requises, l\'emplacement exact des liens et boutons dans l\'interface, et le comportement automatique en cas d\'accès non autorisé.
</p>

<h3>5.1 Module Commercial & Ventes</h3>
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 18%;">Route / Action</th>
            <th style="width: 22%;">Permission Requise</th>
            <th style="width: 18%;">Rôles Habilités</th>
            <th style="width: 24%;">Emplacement Vues / Liens / Boutons</th>
            <th style="width: 18%;">Comportement si non autorisé</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><span class="code">/client/list</span><br><span class="code">/client/apiList</span></td>
            <td><span class="code">COMMERCIAL_VIEW_OWN_CLIENTS</span> ou <span class="code">GESTIONNAIRE_VIEW_ALL_CLIENTS</span></td>
            <td>Commercial, Gestionnaire, Admin, SuperAdmin</td>
            <td>Lien Sidebar "Clients"<br>Vue <code>views/clients/list.php</code></td>
            <td>Lien masqué dans sidebar.<br>Requête directe &rarr; HTTP 403.</td>
        </tr>
        <tr>
            <td><span class="code">/client/formulaire</span><br><span class="code">/client/add</span></td>
            <td><span class="code">COMMERCIAL_ADD_CLIENT</span></td>
            <td>Commercial, Admin, SuperAdmin</td>
            <td>Bouton "+ Nouveau Client" dans <code>views/clients/list.php</code></td>
            <td>Bouton non affiché.<br>POST direct &rarr; JSON 403.</td>
        </tr>
        <tr>
            <td><span class="code">/client/edition/{id}</span><br><span class="code">/client/edit</span></td>
            <td><span class="code">GESTIONNAIRE_EDIT_CLIENT</span></td>
            <td>Gestionnaire, Admin, SuperAdmin</td>
            <td>Bouton "Éditer" dans colonne actions DataTable (<code>clients.js</code>)</td>
            <td>Bouton retiré du DOM par DataTables.<br>Accès URL &rarr; HTTP 403.</td>
        </tr>
        <tr>
            <td><span class="code">/souscription/list</span></td>
            <td><span class="code">COMMERCIAL_VIEW_OWN_SOUSCRIPTIONS</span> ou <span class="code">GESTIONNAIRE_VIEW_ALL_SOUSCRIPTIONS</span></td>
            <td>Commercial, Gestionnaire, Admin, SuperAdmin</td>
            <td>Lien Sidebar "Souscriptions"<br>Vue <code>views/souscriptions/list.php</code></td>
            <td>Lien sidebar masqué.<br>Accès direct &rarr; HTTP 403.</td>
        </tr>
        <tr>
            <td><span class="code">/souscription/createWizard</span><br><span class="code">/souscription/wizardSubmit</span></td>
            <td><span class="code">COMMERCIAL_ADD_SOUSCRIPTION</span></td>
            <td>Commercial, Admin, SuperAdmin</td>
            <td>Bouton "+ Nouvelle souscription" dans <code>views/souscriptions/list.php</code></td>
            <td>Bouton non généré.<br>POST direct &rarr; JSON 403.</td>
        </tr>
        <tr>
            <td><span class="code">/cautisation/searchForm</span><br><span class="code">/cautisation/store</span></td>
            <td><span class="code">COMMERCIAL_COLLECT_COTISATION</span></td>
            <td>Commercial, Admin, SuperAdmin</td>
            <td>Lien Sidebar "Paiement Cotisation"<br>Vue <code>views/cautisations_payment/search.php</code></td>
            <td>Lien masqué.<br>Soumission formulaire &rarr; JSON 403.</td>
        </tr>
        <tr>
            <td><span class="code">/caisse_commercial/list</span></td>
            <td><span class="code">COMMERCIAL_MANAGE_OWN_CAISSE</span> ou <span class="code">FINANCE_VIEW_CLOTURES_CAISSE</span></td>
            <td>Commercial, Finance, Admin, SuperAdmin</td>
            <td>Lien Sidebar "Ma Caisse Journalière"<br>Vue <code>views/caisse_commercial/list.php</code></td>
            <td>Lien masqué.<br>Accès direct &rarr; HTTP 403.</td>
        </tr>
        <tr>
            <td><span class="code">/versement/formulaire</span><br><span class="code">/versement/add</span></td>
            <td><span class="code">COMMERCIAL_MAKE_VERSEMENT</span></td>
            <td>Commercial, Admin, SuperAdmin</td>
            <td>Bouton "Saisir un Versement" dans <code>views/versements/list.php</code></td>
            <td>Bouton invisible (<code>Context::can</code>).<br>POST &rarr; JSON 403.</td>
        </tr>
    </tbody>
</table>

<pagebreak />

<h3>5.2 Module Finance & Gestion des Fonds</h3>
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 18%;">Route / Action</th>
            <th style="width: 22%;">Permission Requise</th>
            <th style="width: 18%;">Rôles Habilités</th>
            <th style="width: 24%;">Emplacement Vues / Liens / Boutons</th>
            <th style="width: 18%;">Comportement si non autorisé</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><span class="code">/versement/valider</span><br><span class="code">/versement/changer</span></td>
            <td><span class="code">FINANCE_VALIDATE_VERSEMENT</span></td>
            <td>Finance, Admin, SuperAdmin</td>
            <td>Boutons "Valider" et "Rejeter" dans <code>views/versements/details.php</code></td>
            <td>Boutons masqués (interdits aux commerciaux).<br>POST &rarr; JSON 403.</td>
        </tr>
        <tr>
            <td><span class="code">/caisse_commercial/changer</span><br><span class="code">/caisse_commercial/edit</span></td>
            <td><span class="code">FINANCE_MANAGE_CLOTURES_CAISSE</span></td>
            <td>Finance, Admin, SuperAdmin</td>
            <td>Bouton de validation de clôture de caisse commerciale</td>
            <td>Action bloquée côté serveur.<br>Réponse JSON 403.</td>
        </tr>
        <tr>
            <td><span class="code">/depense/list</span><br><span class="code">/depense/apiList</span></td>
            <td><span class="code">FINANCE_MANAGE_DEPENSES</span></td>
            <td>Finance, Admin, SuperAdmin</td>
            <td>Lien Sidebar "Dépenses & Frais"<br>Vue <code>views/depenses/list.php</code></td>
            <td>Lien sidebar masqué.<br>Accès direct &rarr; HTTP 403.</td>
        </tr>
        <tr>
            <td><span class="code">/depense/formulaire</span><br><span class="code">/depense/add</span></td>
            <td><span class="code">FINANCE_MANAGE_DEPENSES</span></td>
            <td>Finance, Admin, SuperAdmin</td>
            <td>Bouton "Nouvelle Dépense" dans <code>views/depenses/list.php</code></td>
            <td>Bouton non rendu.<br>POST direct &rarr; JSON 403.</td>
        </tr>
        <tr>
            <td><span class="code">/type_depense/*</span></td>
            <td><span class="code">FINANCE_MANAGE_TYPE_DEPENSES</span></td>
            <td>Finance, Admin, SuperAdmin</td>
            <td>Lien Sidebar "Types de Dépenses"<br>Bouton "+ Nouveau Type Dépense"<br>Toggle statut & boutons Éditer</td>
            <td>Lien masqué.<br>Boutons masqués & toggles inactifs.<br>Accès direct &rarr; HTTP 403.</td>
        </tr>
    </tbody>
</table>

<h3>5.3 Module Catalogue, Sessions & Logistique</h3>
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 18%;">Route / Action</th>
            <th style="width: 22%;">Permission Requise</th>
            <th style="width: 18%;">Rôles Habilités</th>
            <th style="width: 24%;">Emplacement Vues / Liens / Boutons</th>
            <th style="width: 18%;">Comportement si non autorisé</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><span class="code">/article/*</span></td>
            <td><span class="code">GESTIONNAIRE_MANAGE_ARTICLES</span></td>
            <td>Gestionnaire, Admin, SuperAdmin</td>
            <td>Lien Sidebar "Articles & Produits"<br>Bouton "+ Nouvel Article"<br>Toggle statut & bouton Éditer DataTables</td>
            <td>Lien sidebar masqué.<br>Boutons omis.<br>Interdit aux commerciaux & finance.</td>
        </tr>
        <tr>
            <td><span class="code">/pack/*</span></td>
            <td><span class="code">GESTIONNAIRE_MANAGE_PACKS</span></td>
            <td>Gestionnaire, Admin, SuperAdmin</td>
            <td>Lien Sidebar "Packs Promotionnels"<br>Bouton "+ Nouveau Pack"<br>Toggle statut & bouton Éditer (<code>packs.js</code>)</td>
            <td>Lien masqué.<br>Bouton masqué.<br>Accès direct &rarr; HTTP 403.</td>
        </tr>
        <tr>
            <td><span class="code">/session/*</span></td>
            <td><span class="code">GESTIONNAIRE_MANAGE_SESSIONS</span></td>
            <td>Gestionnaire, Admin, SuperAdmin</td>
            <td>Lien Sidebar "Sessions d\'Activité"<br>Bouton "+ Nouvelle Session"<br>Toggle statut & bouton Éditer</td>
            <td>Lien masqué.<br>Accès direct &rarr; HTTP 403.</td>
        </tr>
        <tr>
            <td><span class="code">/distribution/*</span></td>
            <td><span class="code">GESTIONNAIRE_MANAGE_DISTRIBUTIONS</span></td>
            <td>Gestionnaire, Admin, SuperAdmin</td>
            <td>Lien Sidebar "Distributions & Remises"<br>Bouton "+ Valider une Distribution"<br>Bouton Éditer</td>
            <td>Lien masqué.<br>Bouton d\'ajout conditionné.<br>Accès non autorisé &rarr; HTTP 403.</td>
        </tr>
        <tr>
            <td><span class="code">/annee/*</span></td>
            <td><span class="code">GESTIONNAIRE_MANAGE_ANNEES</span></td>
            <td>Gestionnaire, Admin, SuperAdmin</td>
            <td>Lien Sidebar "Années Académiques"<br>Bouton "+ Nouvelle Année"<br>Toggle statut & bouton Éditer</td>
            <td>Lien masqué.<br>Accès direct &rarr; HTTP 403.</td>
        </tr>
    </tbody>
</table>

<pagebreak />

<h3>5.4 Module Administration Système & Sécurité</h3>
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 18%;">Route / Action</th>
            <th style="width: 22%;">Permission Requise</th>
            <th style="width: 18%;">Rôles Habilités</th>
            <th style="width: 24%;">Emplacement Vues / Liens / Boutons</th>
            <th style="width: 18%;">Comportement si non autorisé</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><span class="code">/user/list</span><br><span class="code">/user/formulaire</span><br><span class="code">/user/changer</span></td>
            <td><span class="code">ADMIN_MANAGE_USERS</span></td>
            <td>Admin, SuperAdmin</td>
            <td>Lien Sidebar "Utilisateurs & Personnel"<br>Bouton "+ Nouvel Utilisateur"<br>Toggle statut & bouton Éditer</td>
            <td>Rubrique entière masquée.<br>Toute tentative GET/POST &rarr; HTTP/JSON 403.</td>
        </tr>
        <tr>
            <td><span class="code">/role/*</span></td>
            <td><span class="code">ADMIN_MANAGE_ROLES</span></td>
            <td>Admin, SuperAdmin</td>
            <td>Lien Sidebar "Rôles & Profils"<br>Bouton "+ Nouveau Rôle"<br>Toggle statut & bouton Éditer</td>
            <td>Lien masqué.<br>Accès direct &rarr; HTTP 403.</td>
        </tr>
        <tr>
            <td><span class="code">/permission/*</span></td>
            <td><span class="code">ADMIN_MANAGE_PERMISSIONS</span></td>
            <td>Admin, SuperAdmin</td>
            <td>Lien Sidebar "Permissions Granulaires"<br>Bouton "+ Ajouter Permission"<br>Toggle statut & bouton Éditer</td>
            <td>Lien masqué.<br>Accès direct &rarr; HTTP 403.</td>
        </tr>
        <tr>
            <td><span class="code">/zone/*</span></td>
            <td><span class="code">ADMIN_MANAGE_ZONES</span></td>
            <td>Admin, SuperAdmin</td>
            <td>Lien Sidebar "Zones Commerciales"<br>Bouton "+ Nouvelle Zone"<br>Toggle statut & bouton Éditer</td>
            <td>Lien masqué.<br>Accès direct &rarr; HTTP 403.</td>
        </tr>
        <tr>
            <td><span class="code">/etablissement/*</span></td>
            <td><span class="code">ADMIN_MANAGE_ETABLISSEMENTS</span></td>
            <td>Admin, SuperAdmin</td>
            <td>Lien Sidebar "Configuration Établissement"<br>Formulaire de mise à jour identité visuelle</td>
            <td>Lien masqué.<br>Soumission &rarr; HTTP 403.</td>
        </tr>
        <tr>
            <td><span class="code">/fonction/*</span></td>
            <td><span class="code">ADMIN_MANAGE_USERS</span></td>
            <td>Admin, SuperAdmin</td>
            <td>Lien Sidebar "Fonctions & Postes RH"<br>Bouton "+ Ajouter Fonction"<br>Toggle statut & bouton Éditer</td>
            <td>Lien masqué.<br>Accès direct &rarr; HTTP 403.</td>
        </tr>
    </tbody>
</table>

<!-- SECTION 6 : CLOISONNEMENT DES DONNÉES -->
<h2>6. Mécanisme de Filtrage Multi-Tenant & Cloisonnement des Données</h2>
<p>
    En complément des permissions RBAC sur les écrans et les contrôleurs, un cloisonnement strict des données en base garantit la confidentialité entre zones géographiques, établissements et agents :
</p>

<div class="card-box">
    <table style="width: 100%; border: none; font-size: 8.5pt;">
        <tr>
            <td style="border: none; width: 25%; font-weight: bold; color: #1E3A5F;">etablissement_code</td>
            <td style="border: none;">Isolation stricte de la structure juridique et financière. Aucune requête ne peut croiser les données de deux établissements distincts.</td>
        </tr>
        <tr>
            <td style="border: none; font-weight: bold; color: #1E3A5F;">zone_code</td>
            <td style="border: none;">Découpage territorial opérationnel. Les commerciaux et gestionnaires n\'accèdent qu\'aux enregistrements (clients, souscriptions, cotisations, caisses, versements, distributions) associés à leur zone d\'affectation.</td>
        </tr>
        <tr>
            <td style="border: none; font-weight: bold; color: #1E3A5F;">annee_code</td>
            <td style="border: none;">Cloisonnement de l\'exercice comptable et de la campagne annuelle en cours.</td>
        </tr>
        <tr>
            <td style="border: none; font-weight: bold; color: #1E3A5F;">user_code (Agent)</td>
            <td style="border: none;">Pour les utilisateurs ayant le rôle <code>ROLE_COMMERCIAL</code>, les requêtes sur les clients, souscriptions, cotisations et caisses filtrent systématiquement par leur propre identifiant d\'agent, interdisant la consultation des dossiers gérés par leurs collègues.</td>
        </tr>
    </table>
</div>

<!-- SECTION 7 : POLITIQUE DE RÉPONSE ET GESTION DES ERREURS -->
<h2>7. Politiques de Réponse et Comportement en cas d\'Accès Non Autorisé</h2>
<div class="callout-danger">
    <strong>Réponse aux requêtes Web Standards (Navigation par navigateur) :</strong><br>
    Lorsqu\'un utilisateur tente d\'accéder par URL directe à une page dont il ne possède pas les droits, le système déclenche <code>BaseController::requirePermission()</code>. Celui-ci affiche immédiatement la vue d\'erreur dédiée <code>views/errors/403.php</code> avec un statut <strong>HTTP 403 Forbidden</strong>, un message explicite stipulant la permission manquante, et un bouton de retour sécurisé vers le tableau de bord autorisé.
</div>

<div class="callout-danger">
    <strong>Réponse aux requêtes Asynchrones (AJAX / Fetch / DataTables) :</strong><br>
    Pour toute requête effectuée avec l\'en-tête <code>X-Requested-With: XMLHttpRequest</code> ou sollicitant une API, le contrôleur émet un en-tête <strong>HTTP/1.1 403 Forbidden</strong> ainsi qu\'un corps JSON standardisé :<br>
    <pre style="background:#FFFFFF; border:1px solid #FECACA; padding:6px; border-radius:4px; font-size:8pt; margin:4px 0;">{ "status": 0, "success": false, "message": "Accès refusé : vous n\'avez pas l\'autorisation nécessaire (CODE_PERMISSION)." }</pre>
    Le client JavaScript (Toastr / Alert) intercepte cette réponse et informe l\'utilisateur sans interruption du fonctionnement général de la page.
</div>

<pagebreak />

<!-- SECTION 8 : BONNES PRATIQUES & CONCLUSION -->
<h2>8. Bonnes Pratiques d\'Administration & Procédure d\'Attribution</h2>
<p>
    Pour préserver l\'intégrité du système de contrôle d\'accès, l\'équipe de direction et les administrateurs doivent observer les règles de gouvernance suivantes :
</p>
<ol style="margin-left: 20px; line-height: 1.6;">
    <li><strong>Principe du Moindre Privilège :</strong> Chaque collaborateur ne doit recevoir que le rôle strictement nécessaire à l\'exercice quotidien de ses fonctions. Il est formellement déconseillé d\'attribuer <code>ROLE_ADMIN</code> à un agent opérationnel.</li>
    <li><strong>Séparation des Pouvoirs (Versement vs Validation) :</strong> Le commercial qui initie la collecte et le versement (<code>ROLE_COMMERCIAL</code>) ne doit en aucun cas disposer de la permission de validation financière (<code>FINANCE_VALIDATE_VERSEMENT</code>). Cette séparation est scellée dans la matrice RBAC.</li>
    <li><strong>Activation des Comptes par Jeton :</strong> La création d\'un utilisateur génère un jeton envoyé par email. Seul le <code>ROLE_SUPERADMIN</code> est autorisé à activer manuellement un compte dont le jeton n\'a pas encore été validé par l\'intéressé.</li>
    <li><strong>Audit Régulier des Rôles :</strong> Il est recommandé d\'exporter et de réexaminer périodiquement ce registre pour s\'assurer qu\'aucune dérive d\'habilitation n\'a été introduite dans la table <code>role_permissions</code>.</li>
</ol>

<div style="margin-top: 35px; border-top: 2px solid #1E3A5F; padding-top: 15px;">
    <table style="width: 100%; border: none;">
        <tr>
            <td style="border: none; width: 50%; vertical-align: top;">
                <div style="font-weight: bold; color: #1E3A5F; font-size: 9pt;">Visa Direction Technique & Sécurité</div>
                <div style="font-size: 8pt; color: #64748B; margin-top: 4px;">Système de Contrôle d\'Accès Olive Service ERP</div>
                <div style="margin-top: 30px; font-weight: bold; color: #059669;">&check; SÉCURITÉ CERTIFIÉE v2.0</div>
            </td>
            <td style="border: none; width: 50%; vertical-align: top; text-align: right;">
                <div style="font-weight: bold; color: #1E3A5F; font-size: 9pt;">Visa Direction Générale</div>
                <div style="font-size: 8pt; color: #64748B; margin-top: 4px;">Validation de la Matrice d\'Habilitations</div>
                <div style="margin-top: 30px; font-weight: bold; color: #0F172A;">Document Exécutoire Officiel</div>
            </td>
        </tr>
    </table>
</div>
';

$mpdf->WriteHTML($html);

$pdfPath = __DIR__ . '/detail_gestion_rbac.pdf';
$mpdf->Output($pdfPath, 'F');

if (file_exists($pdfPath)) {
    $sizeKb = round(filesize($pdfPath) / 1024, 2);
    echo "SUCCESS: PDF generated successfully at: {$pdfPath} ({$sizeKb} KB)" . PHP_EOL;
} else {
    echo "ERROR: PDF generation failed." . PHP_EOL;
    exit(1);
}
