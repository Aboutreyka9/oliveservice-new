<?php
require_once __DIR__ . '/../../public/inc/header.php';

$missions = $missions ?? [];
$livreurCode = $livreurCode ?? '';
$hasActiveMission = false;
$activeDestination = null;

$targetMissionCode = $_GET['mission'] ?? ($_GET['code'] ?? null);

if ($targetMissionCode) {
    foreach ($missions as $m) {
        if ($m['code_mission'] === $targetMissionCode) {
            $hasActiveMission = true;
            $isCol = ($m['type_mission'] === 'collecte');
            $destLat = !empty($m['latitude_mission']) ? (float)$m['latitude_mission'] : (!empty($m['latitude_client']) ? (float)$m['latitude_client'] : 5.358);
            $destLng = !empty($m['longitude_mission']) ? (float)$m['longitude_mission'] : (!empty($m['longitude_client']) ? (float)$m['longitude_client'] : -3.985);
            $destName = $isCol ? ($m['nom_client'] ?? 'Client (Collecte)') : ($m['nom_client'] ?? 'Client (Livraison)');
            $destAddress = !empty($m['adresse_mission']) ? $m['adresse_mission'] : ($m['adresse_client'] ?? 'Abidjan');

            $activeDestination = [
                'lat' => $destLat,
                'lng' => $destLng,
                'name' => $destName,
                'address' => $destAddress,
                'type' => $m['type_mission']
            ];
            break;
        }
    }
}

if (!$activeDestination) {
    foreach ($missions as $m) {
        if (($m['statut_mission'] ?? '') === 'en_cours') {
            $hasActiveMission = true;
            $isCol = ($m['type_mission'] === 'collecte');
            $destLat = !empty($m['latitude_mission']) ? (float)$m['latitude_mission'] : (!empty($m['latitude_client']) ? (float)$m['latitude_client'] : 5.358);
            $destLng = !empty($m['longitude_mission']) ? (float)$m['longitude_mission'] : (!empty($m['longitude_client']) ? (float)$m['longitude_client'] : -3.985);
            $destName = $isCol ? ($m['nom_client'] ?? 'Client (Collecte)') : ($m['nom_client'] ?? 'Client (Livraison)');
            $destAddress = !empty($m['adresse_mission']) ? $m['adresse_mission'] : ($m['adresse_client'] ?? 'Abidjan');

            $activeDestination = [
                'lat' => $destLat,
                'lng' => $destLng,
                'name' => $destName,
                'address' => $destAddress,
                'type' => $m['type_mission']
            ];
            break;
        }
    }
}
?>

<!-- Leaflet CSS & JS pour la carte interactive autonome -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= RACINE ?>public/json/livreur-gps-tracker.js?v=3"></script>

<style>
.live-driver-marker-container {
  position: relative;
  width: 36px;
  height: 36px;
}
.live-driver-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #2563EB;
  border: 2px solid #FFFFFF;
  box-shadow: 0 4px 10px rgba(37,99,235,0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  z-index: 2;
}
.live-driver-pulse {
  position: absolute;
  top: -6px;
  left: -6px;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: rgba(37,99,235,0.35);
  animation: pulseLiveRing 2s infinite ease-out;
  z-index: 1;
}
@keyframes pulseLiveRing {
  0% { transform: scale(0.6); opacity: 0.9; }
  100% { transform: scale(1.6); opacity: 0; }
}
@media (max-width: 900px) {
  #mapContainerGrid {
    grid-template-columns: 1fr !important;
  }
  #tourneeMap {
    min-height: 480px !important;
  }
  .content-wrapper {
    padding: 12px 10px 80px 10px !important;
  }
  .page-header {
    flex-direction: column !important;
    align-items: stretch !important;
  }
  .page-header-actions {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 8px !important;
    width: 100% !important;
  }
  .page-header-actions .btn {
    justify-content: center !important;
    height: 44px !important;
  }
  .mobile-driver-bottom-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 64px;
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-top: 1px solid #E2E8F0;
    display: flex;
    justify-content: space-around;
    align-items: center;
    z-index: 99999;
    box-shadow: 0 -4px 16px rgba(0,0,0,0.06);
  }
  .mobile-driver-nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: #64748B;
    font-size: 11px;
    font-weight: 600;
    gap: 3px;
    flex: 1;
    height: 100%;
  }
  .mobile-driver-nav-item.active {
    color: #2563EB;
    font-weight: 800;
  }
}
@media (min-width: 901px) {
  .mobile-driver-bottom-bar {
    display: none !important;
  }
}
.mission-point-card:hover {
  border-color: #2563EB !important;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
}
</style>

<div class="app-layout">
  <?php require_once __DIR__ . '/../../public/inc/sidbar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/../../public/inc/nav.php'; ?>

    <div class="content-wrapper">
      <div class="page-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
          <h1 style="margin: 0; font-size: 22px; font-weight: 800; color: #1E293B; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="map" style="color: #2563EB;"></i> Carte des Tournées & Guidage Trajet Live
          </h1>
          <p class="page-subtitle" style="margin: 4px 0 0; color: #64748B; font-size: 13px;">
            Tracé d'itinéraire en direct entre votre moto et votre point d'arrivée
          </p>
        </div>

        <div class="page-header-actions" style="display: flex; gap: 8px;">
          <a href="<?= RACINE ?>" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
            <i data-lucide="layout-dashboard" style="width: 16px; height: 16px;"></i> Accueil
          </a>
          <a href="<?= RACINE ?>mission/list" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
            <i data-lucide="list" style="width: 16px; height: 16px;"></i> Missions
          </a>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: stretch;" id="mapContainerGrid">
        
        <!-- CARTE PLEIN ÉCRAN -->
        <div class="card" style="padding: 0; border-radius: 16px; overflow: hidden; border: 1px solid #E2E8F0; min-height: 600px; position: relative;">
          
          <button type="button" class="btn btn-sm btn-primary" onclick="LivreurGpsTracker.toggleFollowMode()" style="position: absolute; top: 14px; right: 14px; z-index: 1000; display: inline-flex; align-items: center; gap: 6px; font-weight: 700; box-shadow: 0 4px 10px rgba(0,0,0,0.15); background: #1E3A5F; border-color: #1E3A5F;">
            <i data-lucide="crosshair" style="width: 14px; height: 14px;"></i> Suivre ma position
          </button>

          <div id="tourneeMap" style="width: 100%; height: 100%; min-height: 600px;"></div>
        </div>

        <!-- LISTE LATÉRALE DES POINTS DE LA TOURNÉE -->
        <div class="card" style="border-radius: 16px; border: 1px solid #E2E8F0; padding: 18px; background: #FFFFFF; max-height: 620px; overflow-y: auto;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid #E2E8F0;">
            <h3 style="margin: 0; font-size: 15px; font-weight: 800; color: #1E293B;">Points de passage</h3>
            <span style="background: #EFF6FF; color: #2563EB; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 10px;">
              <?= count($missions) ?> mission(s)
            </span>
          </div>

          <?php if (empty($missions)): ?>
            <div style="padding: 30px 10px; text-align: center; color: #94A3B8; font-size: 13px;">
              Aucun point de tournée actuellement.
            </div>
          <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 10px;">
              <?php foreach ($missions as $idx => $m): 
                $isCol = ($m['type_mission'] === 'collecte');
                $mLat = !empty($m['latitude_mission']) ? (float)$m['latitude_mission'] : (!empty($m['latitude_client']) ? (float)$m['latitude_client'] : 5.35 + ($idx * 0.008));
                $mLng = !empty($m['longitude_mission']) ? (float)$m['longitude_mission'] : (!empty($m['longitude_client']) ? (float)$m['longitude_client'] : -3.98 - ($idx * 0.006));
                $mAdr = !empty($m['adresse_mission']) ? $m['adresse_mission'] : ($m['adresse_client'] ?? 'Abidjan');
                $gpsUrl = "https://www.google.com/maps/dir/?api=1&destination={$mLat},{$mLng}";
                $isEnCours = ($m['statut_mission'] === 'en_cours');
              ?>
                <div class="mission-point-card" onclick="selectMissionDestination(<?= $mLat ?>, <?= $mLng ?>, '<?= htmlspecialchars($m['nom_client'] ?? 'Client') ?>', '<?= htmlspecialchars($mAdr) ?>', '<?= htmlspecialchars($m['code_mission']) ?>')" style="padding: 12px; border-radius: 10px; border: <?= $isEnCours ? '2px solid #2563EB' : '1px solid #E2E8F0' ?>; background: <?= $isEnCours ? '#EFF6FF' : '#F8FAFC' ?>; cursor: pointer; transition: all 0.2s ease;">
                  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <strong style="font-size: 13px; color: #1E293B;">#<?= htmlspecialchars($m['code_mission']) ?></strong>
                    <span style="font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; background: <?= $isCol ? '#FEF3C7' : '#EFF6FF' ?>; color: <?= $isCol ? '#92400E' : '#1E40AF' ?>; display: inline-flex; align-items: center; gap: 4px;">
                      <i data-lucide="<?= $isCol ? 'package' : 'truck' ?>" style="width: 12px; height: 12px;"></i> <?= $isCol ? 'Collecte' : 'Livraison' ?>
                    </span>
                  </div>
                  <div style="font-size: 12px; color: #475569; margin-bottom: 6px;">
                    <i class="fa fa-user" style="font-size: 11px; color: #94A3B8;"></i> <?= htmlspecialchars($m['nom_client'] ?? 'Client') ?>
                  </div>
                  <div style="font-size: 11px; color: #64748B; margin-bottom: 8px;">
                    <i class="fa fa-map-marker-alt" style="color: #DC2626;"></i> <?= htmlspecialchars($mAdr) ?>
                  </div>
                  <div style="display: flex; gap: 6px;">
                    <a href="<?= $gpsUrl ?>" target="_blank" onclick="event.stopPropagation();" class="btn btn-sm btn-primary" style="padding: 4px 8px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                      <i class="fa fa-location-arrow"></i> GPS Google Maps
                    </a>
                    <?php if (!empty($m['telephone_client'])): ?>
                      <a href="tel:<?= htmlspecialchars($m['telephone_client']) ?>" onclick="event.stopPropagation();" class="btn btn-sm btn-secondary" style="padding: 4px 8px; font-size: 11px;">
                        <i class="fa fa-phone" style="color: #059669;"></i>
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

      </div>

    </div>
  </main>
</div>

<script>
let map = null;
const missionMarkers = {};
const rawMissions = <?= json_encode($missions, JSON_UNESCAPED_UNICODE) ?>;
const livreurCode = '<?= htmlspecialchars($livreurCode) ?>';
const hasActive = <?= $hasActiveMission ? 'true' : 'false' ?>;
const activeDest = <?= json_encode($activeDestination, JSON_UNESCAPED_UNICODE) ?>;

document.addEventListener('DOMContentLoaded', function() {
  const defaultLat = 5.3484;
  const defaultLng = -4.0105;

  map = L.map('tourneeMap').setView([defaultLat, defaultLng], 13);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap contributors • LAVEX Live Routing'
  }).addTo(map);

  // Marqueurs Client Collecte (Orange)
  const collecteIcon = L.icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-gold.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
  });

  // Marqueurs Client Livraison (Vert)
  const livraisonIcon = L.icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
  });

  const bounds = [];

  rawMissions.forEach(function(m, idx) {
    const isCol = (m.type_mission === 'collecte');
    let lat = parseFloat(m.latitude_mission || m.latitude_client);
    let lng = parseFloat(m.longitude_mission || m.longitude_client);

    if (isNaN(lat) || isNaN(lng) || (lat === 0 && lng === 0)) {
      lat = defaultLat + ((idx + 1) * 0.009 * (idx % 2 === 0 ? 1 : -1));
      lng = defaultLng + ((idx + 1) * 0.007 * (idx % 3 === 0 ? 1 : -1));
    }

    const icon = isCol ? collecteIcon : livraisonIcon;
    const marker = L.marker([lat, lng], { icon: icon }).addTo(map);

    const targetAdr = m.adresse_mission || m.adresse_client || 'Abidjan';
    const gpsUrl = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
    const telLink = m.telephone_client ? `<a href="tel:${m.telephone_client}" style="color:#059669;font-weight:700;text-decoration:none;"><i class="fa fa-phone"></i> ${m.telephone_client}</a>` : '-';

    const popupHtml = `
      <div style="font-family: inherit; font-size: 13px; line-height: 1.5; min-width: 200px;">
        <strong style="color: #1E293B; font-size: 14px; display: block;">#${m.code_mission}</strong>
        <span style="font-size: 11px; padding: 2px 6px; border-radius: 4px; background: ${isCol ? '#FEF3C7' : '#EFF6FF'}; color: ${isCol ? '#92400E' : '#1E40AF'}; font-weight: 700;">
          ${isCol ? 'Collecte' : 'Livraison'}
        </span>
        <div style="margin: 8px 0 4px; color: #475569;">
          <strong>Client :</strong> ${m.nom_client || 'Client'}<br>
          <strong>Tél :</strong> ${telLink}<br>
          <strong>Adresse :</strong> ${targetAdr}
        </div>
        <div style="margin-top: 10px; display: flex; flex-direction: column; gap: 6px;">
          <button onclick="selectMissionDestination(${lat}, ${lng}, '${m.nom_client || 'Client'}', '${targetAdr}', '${m.code_mission}')" style="background: #2563EB; color: #FFF; border: none; padding: 6px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; cursor: pointer;">
            <i class="fa fa-crosshairs"></i> Tracer l'itinéraire vers ce point
          </button>
          <a href="${gpsUrl}" target="_blank" style="background: #1E3A5F; color: #FFF; text-align: center; padding: 6px 10px; border-radius: 6px; font-weight: 700; text-decoration: none; font-size: 11px;">
            <i class="fa fa-location-arrow"></i> Ouvrir GPS Google Maps
          </a>
        </div>
      </div>
    `;

    marker.bindPopup(popupHtml);
    missionMarkers[m.code_mission] = marker;
    bounds.push([lat, lng]);
  });

  if (bounds.length > 0) {
    map.fitBounds(bounds, { padding: [40, 40] });
  }

  // Démarrer le tracé d'itinéraire et la géolocalisation live si mission active
  if (typeof LivreurGpsTracker !== 'undefined') {
    LivreurGpsTracker.init(map, livreurCode, hasActive, activeDest);
  }

  // Si une mission est passée en paramètre d'URL (?mission=... ou ?code=...), centrer et ouvrir le popup
  const urlParams = new URLSearchParams(window.location.search);
  const targetCode = urlParams.get('mission') || urlParams.get('code');
  if (targetCode && missionMarkers[targetCode]) {
    setTimeout(function() {
      map.setView(missionMarkers[targetCode].getLatLng(), 15);
      missionMarkers[targetCode].openPopup();
    }, 400);
  }
});

function selectMissionDestination(lat, lng, name, address, code) {
  if (map) {
    map.setView([lat, lng], 15);
    if (missionMarkers[code]) {
      missionMarkers[code].openPopup();
    }
    if (typeof LivreurGpsTracker !== 'undefined') {
      LivreurGpsTracker.setDestination({ lat: lat, lng: lng, name: name, address: address });
    }
  }
}
</script>

<!-- BARRE DE NAVIGATION INFÉRIEURE NATIVE PWA MOBILE -->
<div class="mobile-driver-bottom-bar">
  <a href="<?= RACINE ?>" class="mobile-driver-nav-item">
    <i data-lucide="layout-dashboard" style="width: 20px; height: 20px;"></i>
    <span>Accueil</span>
  </a>
  <a href="<?= RACINE ?>mission/carte" class="mobile-driver-nav-item active">
    <i data-lucide="navigation" style="width: 20px; height: 20px;"></i>
    <span>GPS Live</span>
  </a>
  <a href="<?= RACINE ?>mission/list" class="mobile-driver-nav-item">
    <i data-lucide="clipboard-list" style="width: 20px; height: 20px;"></i>
    <span>Missions</span>
  </a>
  <a href="<?= RACINE ?>user/profil" class="mobile-driver-nav-item">
    <i data-lucide="user" style="width: 20px; height: 20px;"></i>
    <span>Profil</span>
  </a>
</div>

<?php require_once __DIR__ . '/../../public/inc/footer.php'; ?>
