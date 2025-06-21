// public/js/events.js

document.addEventListener('DOMContentLoaded', async () => {
    const eventGrid = document.querySelector('#events-list .event-grid');
    const sportTypeFilter = document.getElementById('sport-type-filter');
    const searchInput = document.getElementById('search-event');
    const applyFiltersButton = document.getElementById('apply-filters');
    const paginationDiv = document.querySelector('.pagination');
    const tabButtons = document.querySelectorAll('.tab-btn');

    const limit = 8; // Number of events per page
    let currentPage = 1;
    let currentTab = 'public';

    async function fetchEvents(page, filters = {}) {
        try {
            const offset = (page - 1) * limit;
            let url = new URL(window.location.origin + '/local_greeter/api/index.php');
            
            let action = 'getEvents';
            if (currentTab === 'joined') action = 'getJoinedEvents';
            else if (currentTab === 'created') action = 'getCreatedEvents';
            
            url.searchParams.set('action', action);
            url.searchParams.set('limit', limit);
            url.searchParams.set('offset', offset);

            if (currentTab === 'public') {
                if (filters.sportType) {
                    url.searchParams.set('sport_type', filters.sportType);
                }
                if (filters.search) {
                    url.searchParams.set('search', filters.search);
                }
            }

            const token = sessionStorage.getItem('jwt_token');
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
                if (!token) {
                    eventGrid.innerHTML = '<p>You must be logged in to view this section. Please <a href="/local_greeter/login">log in</a>.</p>';
                    paginationDiv.innerHTML = '';
                    return;
                }

                const userData = JSON.parse(sessionStorage.getItem('user'));
                if (!userData || !userData.id) {
                    eventGrid.innerHTML = '<p>Your session seems to be invalid. Please <a href="/local_greeter/login">log in</a> again.</p>';
                    paginationDiv.innerHTML = '';
                    return;
                }

                options.body = JSON.stringify({
                    user_id: userData.id,
                });
            }

            const response = await fetch(url, options);
            const data = await response.json();

            if (response.ok) {
                renderEvents(data.events);
                renderPagination(data.total_events || 0);
            } else {
                console.error('Failed to fetch events:', data.error || data);
                eventGrid.innerHTML = '<p>Failed to load events. Please try again later.</p>';
            }
        } catch (error) {
            console.error('Error fetching events:', error);
            eventGrid.innerHTML = '<p>An error occurred. Please try again later.</p>';
        }
    }

    function formatDate(dateString) {
        // The date from MySQL is in 'YYYY-MM-DD HH:MM:SS' format.
        // To ensure cross-browser compatibility, we replace the space with a 'T'.
        const safeDateString = dateString.replace(' ', 'T');
        const date = new Date(safeDateString);
        
        if (isNaN(date.getTime())) {
            // Return a fallback string if the date is invalid
            return 'Invalid Date';
        }

        const options = {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        };
        return date.toLocaleString('en-US', options);
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
                <img src="/local_greeter/public/images/default-profile.png" alt="Event image for ${event.title}">
                <div class="event-card-content">
                    <h3>${event.title}</h3>
                    <p class="location">${event.address}</p>
                    <p class="date">${formatDate(event.start_time)}</p>
                    <p class="participants">${event.current_participants}/${event.max_participants} participants</p>
                    ${event.cost > 0 ? `<p class="cost">Cost: $${event.cost.toFixed(2)}</p><p class="cost-per-participant">Cost per participant: $${(event.cost / Math.max(1, event.current_participants)).toFixed(2)}</p>` : '<p class="cost">Cost: Free</p>'}
                    <p>${event.description.substring(0, 100)}...</p>
                    <div class="event-card-actions">
                        ${currentTab === 'public' ? `<button class="btn btn-primary join-event-btn" data-event-id="${event.event_id}">Join Event</button>` : ''}
                        ${currentTab === 'created' ? `<a href="/local_greeter/edit-event?event_id=${event.event_id}" class="btn btn-secondary">Edit Event</a>` : ''}
                        ${currentTab === 'joined' ? `<button class="btn btn-danger leave-event-btn" data-event-id="${event.event_id}">Leave Event</button>` : ''}
                    </div>
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

        document.querySelectorAll('.leave-event-btn').forEach(button => {
            button.addEventListener('click', async (e) => {
                const eventId = e.target.dataset.eventId;
                await leaveEvent(eventId);
            });
        });
    }

    async function leaveEvent(eventId) {
        if (!confirm('Are you sure you want to leave this event?')) {
            return;
        }

        const token = sessionStorage.getItem('jwt_token');
        if (!token) {
            alert('Your session seems to have expired. Please log in again.');
            window.location.href = '/local_greeter/login';
            return;
        }

        try {
            const response = await fetch('/local_greeter/api/index.php?action=leaveEvent', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ event_id: eventId })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                alert('You have successfully left the event.');
                // Re-fetch events to update the list
                applyFilters();
            } else {
                throw new Error(data.message || 'Failed to leave event.');
            }
        } catch (error) {
            console.error('Error leaving event:', error);
            alert(`Could not leave event: ${error.message}`);
        }
    }

    async function joinEvent(eventId, buttonElement) {
        const token = sessionStorage.getItem('jwt_token');
        if (!token) {
            alert('You must be logged in to join an event.');
            window.location.href = '/local_greeter/login';
            return;
        }

        try {
            const response = await fetch("/local_greeter/api/index.php?action=joinEvent", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ event_id: eventId })
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
                fetchEvents(currentPage, { sportType: sportTypeFilter.value });
            } else {
                alert(data.message || 'Failed to join event');
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
                fetchEvents(currentPage, { sportType: sportTypeFilter.value });
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
            applyFilters();
        });
    });

    function applyFilters() {
        currentPage = 1;
        const filters = {
            sportType: sportTypeFilter.value,
            search: searchInput.value
        };
        fetchEvents(currentPage, filters);
    }

    applyFiltersButton.addEventListener('click', applyFilters);

    // Initial fetch
    applyFilters();
}); 