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
            let popupContent = "Sports Field";
            if (el.tags) {
                if (el.tags.name) {
                    popupContent = `<b>${el.tags.name}</b>`;
                } else if (el.tags.sport) {
                    popupContent = `<b>${el.tags.sport.charAt(0).toUpperCase() + el.tags.sport.slice(1)} Pitch</b>`;
                }
                if (el.tags.address && el.tags.address.street && el.tags.address.house_number) {
                    popupContent += `<br>${el.tags.address.street} ${el.tags.address.house_number}`;
                } else if (el.tags.addr && el.tags.addr.street && el.tags.addr.housenumber) {
                    popupContent += `<br>${el.tags.addr.street} ${el.tags.addr.housenumber}`;
                } else if (el.tags.addr && el.tags.addr.full) {
                    popupContent += `<br>${el.tags.addr.full}`;
                }
            }
            L.marker([lat, lon])
                .addTo(map)
                .bindPopup(popupContent);
        }
    });
})
.catch(err => console.error("Error loading Overpass data", err)); 