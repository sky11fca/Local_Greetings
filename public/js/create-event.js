// public/js/create-event.js

document.addEventListener('DOMContentLoaded', async () => {
    const createEventForm = document.getElementById('createEventForm');
    const sportsFieldSelect = document.getElementById('sports_field_id');

    // Function to fetch sports fields and populate the dropdown
    async function fetchSportsFields() {
        try {
            const response = await fetch('/api/fields');
            const data = await response.json();

            if (response.ok) {
                sportsFieldSelect.innerHTML = '<option value="">Select a Sports Field</option>';
                data.fields.forEach(field => {
                    const option = document.createElement('option');
                    option.value = field.id;
                    option.textContent = field.name + ' - ' + field.address;
                    sportsFieldSelect.appendChild(option);
                });
            } else {
                console.error('Failed to fetch sports fields:', data.error);
                alert('Failed to load sports fields. Please try again later.');
            }
        } catch (error) {
            console.error('Error fetching sports fields:', error);
            alert('An error occurred while loading sports fields.');
        }
    }

    // Fetch sports fields when the page loads
    fetchSportsFields();

    // Handle form submission
    if (createEventForm) {
        createEventForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(createEventForm);
            const eventData = {};
            formData.forEach((value, key) => (eventData[key] = value));

            // Get JWT token from local storage
            const token = localStorage.getItem('jwt_token');
            if (!token) {
                alert('You must be logged in to create an event.');
                window.location.href = 'login.html';
                return;
            }

            try {
                const response = await fetch('/api/events', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify(eventData),
                });

                const data = await response.json();

                if (response.ok) {
                    alert(data.message);
                    createEventForm.reset(); // Clear the form
                    // Optionally redirect to the event details page or events list
                    window.location.href = 'events.html'; 
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