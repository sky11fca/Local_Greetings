// public/js/create-event.js

document.addEventListener('DOMContentLoaded', async () => {
    const createEventForm = document.getElementById('createEventForm');
    const sportsFieldSelect = document.getElementById('sports_field_id');
    const fieldMap = document.getElementById('field-map');
    const selectedFieldInfo = document.getElementById('selected-field-info');
    const selectedFieldName = document.getElementById('selected-field-name');
    const clearSelectionBtn = document.getElementById('clear-selection');

    let map;
    let fieldMarkers = [];
    let selectedFieldId = null;
    let fieldsData = [];

    // Initialize map
    function initializeMap() {
        // Initialize map centered on Iași
        map = L.map('field-map').setView([47.1585, 27.6014], 13);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
    }

    // Create custom field marker icon
    function createFieldIcon(isSelected = false) {
        return L.divIcon({
            className: `field-marker ${isSelected ? 'selected' : ''}`,
            html: `<div style="
                background-color: ${isSelected ? '#28a745' : '#007bff'};
                width: 20px;
                height: 20px;
                border-radius: 50%;
                border: 3px solid white;
                box-shadow: 0 2px 4px rgba(0,0,0,0.3);
                cursor: pointer;
            "></div>`,
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });
    }

    // Add fields to map
    function addFieldsToMap(fields) {
        // Clear existing markers
        fieldMarkers.forEach(marker => map.removeLayer(marker));
        fieldMarkers = [];

        fields.forEach(field => {
            if (field.latitude && field.longitude) {
                const marker = L.marker([parseFloat(field.latitude), parseFloat(field.longitude)], {
                    icon: createFieldIcon(field.field_id == selectedFieldId)
                }).addTo(map);

                // Store field ID on marker for reference
                marker.fieldId = field.field_id;

                // Create popup content
                const popupContent = `
                    <div style="text-align: center;">
                        <b>${field.name}</b><br>
                        ${field.address}<br>
                        <small>Click to select this field</small>
                    </div>
                `;

                marker.bindPopup(popupContent);

                // Add click event to select field
                marker.on('click', () => {
                    selectField(field.field_id, field.name);
                });

                fieldMarkers.push(marker);
            }
        });
    }

    // Select a field from map
    function selectField(fieldId, fieldName) {
        selectedFieldId = fieldId;
        selectedFieldName.textContent = fieldName;
        selectedFieldInfo.style.display = 'flex';
        
        // Update dropdown
        sportsFieldSelect.value = fieldId;
        
        // Update map markers
        fieldMarkers.forEach(marker => {
            const markerFieldId = marker.fieldId; // We'll set this when creating markers
            if (markerFieldId == fieldId) {
                marker.setIcon(createFieldIcon(true));
            } else {
                marker.setIcon(createFieldIcon(false));
            }
        });
    }

    // Clear field selection
    function clearFieldSelection() {
        selectedFieldId = null;
        selectedFieldInfo.style.display = 'none';
        sportsFieldSelect.value = '';
        
        // Update map markers
        fieldMarkers.forEach(marker => {
            marker.setIcon(createFieldIcon(false));
        });
    }

    // Function to fetch sports fields and populate both dropdown and map
    async function fetchSportsFields() {
        try {
            const response = await fetch('/local_greeter/api/index.php?action=getSportsFields');
            const data = await response.json();

            if (response.ok) {
                fieldsData = data.fields;
                
                // Populate dropdown
                sportsFieldSelect.innerHTML = '<option value="">Select a Sports Field</option>';
                fieldsData.forEach(field => {
                    const option = document.createElement('option');
                    option.value = field.field_id;
                    option.textContent = field.name + ' - ' + field.address;
                    sportsFieldSelect.appendChild(option);
                });

                // Add fields to map
                addFieldsToMap(fieldsData);
            } else {
                console.error('Failed to fetch sports fields:', data.error);
                alert('Failed to load sports fields. Please try again later.');
            }
        } catch (error) {
            console.error('Error fetching sports fields:', error);
            alert('An error occurred while loading sports fields.');
        }
    }

    // Handle dropdown change
    sportsFieldSelect.addEventListener('change', (e) => {
        const fieldId = e.target.value;
        if (fieldId) {
            const field = fieldsData.find(f => f.field_id == fieldId);
            if (field) {
                selectField(field.field_id, field.name);
            }
        } else {
            clearFieldSelection();
        }
    });

    // Handle clear selection button
    clearSelectionBtn.addEventListener('click', clearFieldSelection);

    // Initialize map and fetch data
    initializeMap();
    fetchSportsFields();

    // Handle form submission
    if (createEventForm) {
        createEventForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const token = localStorage.getItem('jwt_token');
            if (!token) {
                alert('You must be logged in to create an event.');
                window.location.href = '/local_greeter/login';
                return;
            }
            
            const formData = {
                title: createEventForm.title.value,
                description: createEventForm.description.value,
                sport_type: createEventForm.sport_type.value,
                start_time: createEventForm.start_time.value,
                end_time: createEventForm.end_time.value,
                max_participants: createEventForm.max_participants.value,
                field_id: createEventForm.sports_field_id.value
            };

            try {
                const response = await fetch('/local_greeter/api/index.php?action=createEvent', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify(formData),
                });

                const data = await response.json();

                if (response.ok) {
                    alert(data.message);
                    createEventForm.reset();
                    clearFieldSelection();
                    window.location.href = '/local_greeter/events';
                } else {
                    alert(data.error || 'Failed to create event');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        });
    }
}); 