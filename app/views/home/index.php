<?php require __DIR__ . '/../__components/header.php'; ?>

<h1>Welcome <?=htmlspecialchars($_SESSION['username'])?>!</h1>

<p>Check out this cool map I can draw</p>


<div id="map"></div>


<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<script>
    const map = L.map('map', {
        maxBounds: [[47.1585, 27.5726], [47.1765, 27.6014]],
        minZoom: 13,
        maxZoom: 19,
    }).setView([47.1585, 27.6014], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map);

    const sportsFields = [
        {
            name: "Teren Fotbal Copou",
            coords: [47.1765, 27.5726],
            type: "football"
        },
        {
            name: "Teren Baschet Tudor",
            coords: [47.1689, 27.5872],
            type: "basketball"
        },
        {
            name: "Teren Tenis Palas",
            coords: [47.1585, 27.6014],
            type: "tennis"
        }
    ];

    sportsFields.forEach(sportsField => {
        const icon = L.divIcon({
            className: `sport-icon ${sportsField.type}`,
            html: `<div class="sport-marker ${sportsField.type}"></div>`,
            iconSize: [30, 30]
        });

        L.marker(sportsField.coords, {icon: icon})
            .addTo(map)
            .bindPopup(`<b>${sportsField.name}</b><br>Type: ${sportsField.type}`);
    });


</script>

<style>
    .sport-marker {
        width: 25px;
        height: 25px;
        border-radius: 50%;
        background: #3388ff;
        border: 2px solid white;
        position: relative;
    }

    .sport-marker.football {
        background: #2ecc71;
    }

    .sport-marker.basketball {
        background: #e74c3c;
    }

    .sport-marker.tennis {
        background: #f39c12;
    }
</style>

<?php require __DIR__ . '/../__components/footer.php'; ?>
