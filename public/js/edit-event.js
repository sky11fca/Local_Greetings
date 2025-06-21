document.addEventListener('DOMContentLoaded', async () => {
    const editEventForm = document.getElementById('editEventForm');
    if (!editEventForm) return;

    const eventIdInput = document.getElementById('event_id');
    const titleInput = document.getElementById('title');
    const descriptionInput = document.getElementById('description');
    const sportsFieldSelect = document.getElementById('sports_field_id');
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const maxParticipantsInput = document.getElementById('max_participants');

    const urlParams = new URLSearchParams(window.location.search);
    const eventId = urlParams.get('event_id');

    if (!eventId) {
        alert('No event ID provided. Redirecting to events page.');
        window.location.href = '/local_greeter/events';
        return;
    }

    // Function to fetch all sports fields for the dropdown
    async function fetchSportsFields() {
        try {
            const response = await fetch('/local_greeter/api/index.php?action=getSportsFields');
            const data = await response.json();
            if (response.ok) {
                data.fields.forEach(field => {
                    const option = document.createElement('option');
                    option.value = field.field_id;
                    option.textContent = field.name;
                    sportsFieldSelect.appendChild(option);
                });
            } else {
                throw new Error(data.message || 'Failed to fetch sports fields');
            }
        } catch (error) {
            console.error('Error fetching sports fields:', error);
            alert('Could not load sports fields. Please try refreshing the page.');
        }
    }

    // Function to format MySQL datetime to datetime-local input format
    function formatDateTimeForInput(dateTimeStr) {
        if (!dateTimeStr) return '';
        // In some cases, the 'Z' for UTC is not present. We add it to stabilize parsing.
        const date = new Date(dateTimeStr.replace(' ', 'T') + 'Z');
        // Adjust for local timezone offset
        const timezoneOffset = date.getTimezoneOffset() * 60000;
        const localDate = new Date(date.getTime() - timezoneOffset);
        // Format to 'YYYY-MM-DDTHH:MM'
        return localDate.toISOString().slice(0, 16);
    }

    // Function to fetch the specific event's details
    async function fetchEventDetails() {
        try {
            const response = await fetch(`/local_greeter/api/index.php?action=getEvent&event_id=${eventId}`);
            const data = await response.json();
            
            if (response.ok && data.success) {
                const event = data.event;
                eventIdInput.value = event.event_id;
                titleInput.value = event.title;
                descriptionInput.value = event.description;
                startTimeInput.value = formatDateTimeForInput(event.start_time);
                endTimeInput.value = formatDateTimeForInput(event.end_time);
                maxParticipantsInput.value = event.max_participants;

                // Set the selected sports field after they have been loaded
                sportsFieldSelect.value = event.field_id;
            } else {
                throw new Error(data.message || 'Failed to fetch event details.');
            }
        } catch (error) {
            console.error('Error fetching event details:', error);
            alert('Could not load event details. Redirecting...');
            window.location.href = '/local_greeter/events';
        }
    }

    // Load initial data
    await fetchSportsFields();
    await fetchEventDetails();

    // Handle form submission
    editEventForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const token = sessionStorage.getItem('jwt_token');

        if (!token) {
            alert('You must be logged in to update an event.');
            return;
        }

        const formData = {
            event_id: eventIdInput.value,
            title: titleInput.value,
            description: descriptionInput.value,
            field_id: sportsFieldSelect.value,
            start_time: startTimeInput.value,
            end_time: endTimeInput.value,
            max_participants: maxParticipantsInput.value,
        };

        try {
            const response = await fetch('/local_greeter/api/index.php?action=updateEvent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (response.ok && data.success) {
                alert('Event updated successfully!');
                window.location.href = '/local_greeter/events';
            } else {
                throw new Error(data.message || 'Failed to update event.');
            }
        } catch (error) {
            console.error('Error updating event:', error);
            alert(`Update failed: ${error.message}`);
        }
    });

    // Handle delete button click
    const deleteButton = document.getElementById('delete-event-btn');
    deleteButton.addEventListener('click', async () => {
        if (!confirm('Are you sure you want to permanently delete this event? This action cannot be undone.')) {
            return;
        }

        const token = sessionStorage.getItem('jwt_token');
        if (!token) {
            alert('You must be logged in to delete an event.');
            return;
        }

        try {
            const response = await fetch('/local_greeter/api/index.php?action=deleteEvent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ event_id: eventId })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                alert('Event deleted successfully.');
                window.location.href = '/local_greeter/events';
            } else {
                throw new Error(data.message || 'Failed to delete event.');
            }
        } catch (error) {
            console.error('Error deleting event:', error);
            alert(`Delete failed: ${error.message}`);
        }
    });
}); 