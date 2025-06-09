// Initialize map centered on Iași
const map = L.map('map').setView([47.1585, 27.6014], 13);

// Add OpenStreetMap tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Overpass API query
const overpassUrl = "https://overpass-api.de/api/interpreter";
const query = `
    [out:json][timeout:25];
    area["name"="Iași"]["admin_level"="8"]->.searchArea;
    (
        node["leisure"="pitch"](area.searchArea);
        way["leisure"="pitch"](area.searchArea);
        relation["leisure"="pitch"](area.searchArea);
    );
    out center;
`;

// Fetch and add pitches to map
fetch(overpassUrl, {
    method: "POST",
    body: query,
})
.then(res => res.json())
.then(data => {
    data.elements.forEach(el => {
        const lat = el.lat || el.center?.lat;
        const lon = el.lon || el.center?.lon;
        if (lat && lon) {
            L.marker([lat, lon])
                .addTo(map)
                .bindPopup("Sports Field (leisure=pitch)");
        }
    });
})
.catch(err => console.error("Error loading Overpass data", err)); 