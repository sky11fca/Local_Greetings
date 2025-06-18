// public/js/events.js

document.addEventListener('DOMContentLoaded', async () => {
    const eventGrid = document.querySelector('#events-list .event-grid');
    const sportTypeFilter = document.getElementById('sport-type-filter');
    const applyFiltersButton = document.getElementById('apply-filters');
    const paginationDiv = document.querySelector('.pagination');
    const tabButtons = document.querySelectorAll('.tab-btn');

    const limit = 8; // Number of events per page
    let currentPage = 1;
    let currentTab = 'public';

    async function fetchEvents(page, sportType = null) {
        try {
            const offset = (page - 1) * limit;
            //let url = `/api/events?limit=${limit}&offset=${offset}`;

            let url = '/local_greeter/api/index.php?action=getEvents';

            // Add tab-specific endpoint
            if (currentTab === 'joined') {
                url = '/local_greeter/api/index.php?action=getJoinedEvents';
            } else if (currentTab === 'created') {
                url = `/local_greeter/api/index.php?action=getCreatedEvents`;
            }
            
            if (sportType) {
                url += `&sport_type=${sportType}`;
            }

            const token = localStorage.getItem('jwt_token');
            const headers = {
                'Content-Type': 'application/json'
            };
            
            if (token) {
                headers['Authorization'] = `Bearer ${token}`;
            }

            const options = {
                headers,
                method: currentTab === 'joined' || currentTab === 'created' ? 'POST' : 'GET',
            }

            if(currentTab === 'joined' || currentTab === 'created'){
                const userData = JSON.parse(sessionStorage.getItem('user'));
                options.body = JSON.stringify({
                    user_id: userData.id,
                });
            }


            const response = await fetch(url, options);
            const data = await response.json();

            if (response.ok) {
                renderEvents(data.events);
                renderPagination(data.total_events || 20);
            } else {
                console.error('Failed to fetch events:', data.error);
                eventGrid.innerHTML = '<p>Failed to load events. Please try again later.</p>';
            }
        } catch (error) {
            console.error('Error fetching events:', error);
            eventGrid.innerHTML = '<p>An error occurred. Please try again later.</p>';
        }
    }

    function renderEvents(events) {
        eventGrid.innerHTML = '';
        if (events.length === 0) {
            eventGrid.innerHTML = '<p>No events found.</p>';
            return;
        }

        events.forEach(event => {
            const eventCard = document.createElement('div');
            eventCard.classList.add('event-card');
            
            eventCard.innerHTML = `
                <img src="/local_greeter/public/images/default-profile.png" alt="${event.title}">
                <div class="event-card-content">
                    <h3>${event.title}</h3>
                    <p class="location">${event.address}</p>
                    <p class="date">${new Date(event.start_time).toLocaleDateString()} - ${new Date(event.end_time).toLocaleDateString()}</p>
                    <p class="participants">${event.current_participants}/${event.max_participants} participants</p>
                    ${event.cost > 0 ? `<p class="cost">Cost: $${event.cost.toFixed(2)}</p><p class="cost-per-participant">Cost per participant: $${(event.cost / Math.max(1, event.current_participants)).toFixed(2)}</p>` : '<p class="cost">Cost: Free</p>'}
                    <p>${event.description.substring(0, 100)}...</p>
                    ${currentTab === 'public' ? `<button class="btn btn-primary join-event-btn" data-event-id="${event.event_id}">Join Event</button>` : ''}
                </div>
            `;
            eventGrid.appendChild(eventCard);
        });

        // Add event listeners for the 'Join Event' buttons
        document.querySelectorAll('.join-event-btn').forEach(button => {
            button.addEventListener('click', async (e) => {
                const eventId = e.target.dataset.eventId;
                await joinEvent(eventId, e.target);
            });
        });
    }

    async function joinEvent(eventId, buttonElement) {
        const token = sessionStorage.getItem('jwt_token');
        if (!token) {
            alert('You must be logged in to join an event.');
            window.location.href = '/local_greeter/app/views/login.html';
            return;
        }

        console.log(eventId);

        try {
            const response = await fetch("/local_greeter/api/index.php?action=joinEvent" , {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
                ,
                body: JSON.stringify({
                    event_id: eventId,
                    sessions_token: token //PLACEHOLDER solution
                })
            });

            const data = await response.json();

            if (response.ok) {
                alert(data.message);
                // Update the UI to reflect the joined status
                buttonElement.textContent = 'Joined';
                buttonElement.disabled = true;
                buttonElement.classList.remove('btn-primary');
                buttonElement.classList.add('btn-secondary');
                // Re-fetch events to update participant count and cost per participant
                fetchEvents(currentPage, sportTypeFilter.value);
            } else {
                alert(data.error || 'Failed to join event');
            }
        } catch (error) {
            console.error('Error joining event:', error);
            alert('An error occurred. Please try again.');
        }
    }

    function renderPagination(totalEvents) {
        paginationDiv.innerHTML = '';
        const totalPages = Math.ceil(totalEvents / limit);

        for (let i = 1; i <= totalPages; i++) {
            const button = document.createElement('button');
            button.textContent = i;
            button.classList.add('btn');
            if (i === currentPage) {
                button.classList.add('active');
            }
            button.addEventListener('click', () => {
                currentPage = i;
                fetchEvents(currentPage, sportTypeFilter.value);
            });
            paginationDiv.appendChild(button);
        }
    }

    // Tab switching functionality
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Update active tab
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            // Update current tab and reset page
            currentTab = button.dataset.tab;
            currentPage = 1;
            
            // Fetch events for the selected tab
            fetchEvents(currentPage, sportTypeFilter.value);
        });
    });

    applyFiltersButton.addEventListener('click', () => {
        currentPage = 1;
        fetchEvents(currentPage, sportTypeFilter.value);
    });

    // Initial fetch
    fetchEvents(currentPage);
}); 