document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('map').setView([47.1585, 27.6014], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Add marker for Iași city center
    L.marker([47.1585, 27.6014]).addTo(map)
        .bindPopup('<b>Iași City Center</b>')
        .openPopup();

    // Add circle to show city area
    L.circle([47.1585, 27.6014], {
        color: 'blue',
        fillColor: '#30f',
        fillOpacity: 0.1,
        radius: 2000
    }).addTo(map).bindPopup('Iași metropolitan area');

    // Add scale control
    L.control.scale().addTo(map);
});