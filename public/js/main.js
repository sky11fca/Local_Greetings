// You can add any interactive JavaScript here

// OpenStreetMap Integration
const mapElement = document.getElementById('map');
if (mapElement) {
    const map = L.map('map').setView([47.1585, 27.6014], 13); // Centered on Iași

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Fetch sports field locations and add markers
    fetch('/api/fields')
        .then(response => response.json())
        .then(fields => {
            fields.forEach(field => {
                if (field.latitude && field.longitude) {
                    L.marker([field.latitude, field.longitude])
                        .addTo(map)
                        .bindPopup(`<b>${field.name}</b><br>${field.address}`);
                }
            });
        })
        .catch(error => console.error('Error fetching sports fields:', error));
} 