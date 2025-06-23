// public/js/create-event.js

document.addEventListener('DOMContentLoaded', async () => {
    const createEventForm = document.getElementById('createEventForm');
    const sportsFieldSelect = document.getElementById('sports_field_id');

    // Function to fetch sports fields and populate the dropdown
    async function fetchSportsFields() {
        try {
            const response = await fetch('/local_greeter/api/index.php?action=getSportsFields');
            const data = await response.json();

            if (response.ok) {
                sportsFieldSelect.innerHTML = '<option value="">Select a Sports Field</option>';
                data.fields.forEach(field => {
                    const option = document.createElement('option');
                    option.value = field.field_id;
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