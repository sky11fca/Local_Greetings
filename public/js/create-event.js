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

    async function getFieldType(fieldId) {
        try {
            const response = await fetch("/local_greeter/api/index.php?action=getFieldByID", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    'field_id': fieldId
                })
            });
            const data = await response.json();

            if(!data.success){
                throw new Error(data.message);
            }

            return data.field;


        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        }
    }

    // Fetch sports fields when the page loads
    fetchSportsFields();

    // Handle form submission
    if (createEventForm) {
        createEventForm.addEventListener('submit', async (e) => {
            e.preventDefault();


            // Get JWT token from local storage
            const token = sessionStorage.getItem('jwt_token');
            if (!token) {
                alert('You must be logged in to create an event.');
                window.location.href = '/local_greeter/app/views/login.html';
                return;
            }

            const selectedFieldId = createEventForm.sports_field_id.value;

            if(!selectedFieldId){
                alert('Please select a sports field');
                return;
            }

            console.log(selectedFieldId);

            const fieldDetails = await getFieldType(selectedFieldId);
            if(!fieldDetails){
                alert('Failed to get field details');
                return;
            }

            const formData = {
                title: createEventForm.title.value,
                description: createEventForm.description.value,
                start_time: createEventForm.start_time.value,
                end_time: createEventForm.end_time.value,
                max_participants: createEventForm.max_participants.value,
                field_id: selectedFieldId,
                field_type: fieldDetails.type,
            }

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

                    //RSS NEWS GENERATION

                    try{
                        const rssResponse = await fetch('/local_greeter/api/index.php?action=sendRssFeed', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${token}`
                            },
                            body: JSON.stringify({
                                'event_id': data.event_id,
                            })
                        })

                        const rssData = await rssResponse.json();
                        if(!rssResponse.ok){
                            console.error('Failed to get rss data');
                        }
                    } catch(error) {
                        console.error('Error:', error);
                    }

                    window.location.href = '/local_greeter/app/views/events.html';
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