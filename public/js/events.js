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

    // Check authentication status using only JWT token
    const checkAuth = () => {
        const token = sessionStorage.getItem('jwt_token');
        const userData = sessionStorage.getItem('user');
        return { 
            token, 
            userData: userData ? JSON.parse(userData) : null 
        };
    };

    // Helper to decode user info from JWT
    function getUserFromJWT() {
        const token = sessionStorage.getItem('jwt_token');
        if (!token) return null;
        try {
            const payload = JSON.parse(atob(token.split('.')[1]));
            return payload.data || null;
        } catch (e) {
            return null;
        }
    }

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

            const { token, userData } = checkAuth();
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
            
            if (!response.ok) {
                if (response.status === 401) {
                    // Token expired or invalid
                    sessionStorage.removeItem('jwt_token');
                    sessionStorage.removeItem('user');
                    if (currentTab === 'joined' || currentTab === 'created') {
                        eventGrid.innerHTML = '<p>Your session has expired. Please <a href="/local_greeter/login">log in</a> again.</p>';
                        paginationDiv.innerHTML = '';
                        return;
                    }
                }
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (data.events) {
                renderEvents(data.events);
                renderPagination(data.total_events || data.events.length);
            } else {
                console.error('Invalid response format:', data);
                eventGrid.innerHTML = '<p>Failed to load events. Please try again later.</p>';
            }
        } catch (error) {
            console.error('Error fetching events:', error);
            eventGrid.innerHTML = '<p>An error occurred while loading events. Please try again later.</p>';
        }
    }

    function formatDate(dateString) {
        if (!dateString || dateString.includes('Invalid')) {
            return 'TBD';
        }
        const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        return new Date(dateString).toLocaleDateString(undefined, options);
    }

    function getDefaultImage(sportType) {
        const defaultImages = {
            'football': '/local_greeter/public/images/football.jpg',
            'basketball': '/local_greeter/public/images/basketball.jpeg',
            'tennis': '/local_greeter/public/images/tennis.jpg',
            'volleyball': '/local_greeter/public/images/volleyball.jpg',
            'multi-sport': '/local_greeter/public/images/other.jpg',
            'default': '/local_greeter/public/images/other.jpg'
        };
        return defaultImages[sportType.toLowerCase()] || defaultImages['default'];
    }

    function renderEvents(events) {
        eventGrid.innerHTML = '';
        if (events.length === 0) {
            eventGrid.innerHTML = '<p>No events found for this category.</p>';
            return;
        }

        const { userData } = checkAuth();
        const userId = userData ? userData.id : null;

        events.forEach(event => {
            const eventCard = document.createElement('div');
            eventCard.classList.add('event-card');

            let buttonsHTML = '';
            const isUserCreator = userId && event.organizer_id == userId;

            // Determine which button to show based on current tab and user relationship
            if (currentTab === 'created') {
                // In "My Created Events" tab - always show edit button
                buttonsHTML = `<a href="/local_greeter/edit-event?event_id=${event.event_id}" class="btn btn-secondary">Edit Event</a>`;
            } else if (currentTab === 'joined') {
                // In "My Joined Events" tab - always show leave button
                buttonsHTML = `<button class="btn btn-danger leave-event-btn" data-event-id="${event.event_id}">Leave Event</button>`;
            } else {
                // In "Public Events" tab - show appropriate button based on relationship
                if (isUserCreator) {
                    buttonsHTML = `<a href="/local_greeter/edit-event?event_id=${event.event_id}" class="btn btn-secondary">Edit Event</a>`;
                } else {
                    // Check if user is already a participant using the is_participant field
                    const isUserJoined = event.is_participant === true;
                    if (isUserJoined) {
                        buttonsHTML = `<button class="btn btn-danger leave-event-btn" data-event-id="${event.event_id}">Leave Event</button>`;
                    } else {
                        buttonsHTML = `<button class="btn btn-primary join-event-btn" data-event-id="${event.event_id}">Join Event</button>`;
                    }
                }
            }

            const imageUrl = event.image_path ? `/local_greeter/public/images/events/${event.image_path}` : getDefaultImage(event.sport_type);

            eventCard.innerHTML = `
                <img src="${imageUrl}" alt="${event.title}" class="card-img-top">
                <div class="card-body">
                    <h3 class="card-title">${event.title}</h3>
                    <p class="card-text location"><i class="icon-map-pin"></i> ${event.address}</p>
                    <p class="card-text date"><i class="icon-calendar"></i> ${formatDate(event.start_time)}</p>
                    <p class="card-text participants"><i class="icon-users"></i> ${event.current_participants}/${event.max_participants} participants</p>
                    <p class="card-text cost"><i class="icon-dollar-sign"></i> ${event.cost > 0 ? '$' + event.cost : 'Free'}</p>
                    <p class="card-text description">${event.description}</p>
                    <div class="event-actions mt-auto">
                        ${buttonsHTML}
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
                // Re-fetch events to update the list and button states
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

            if (response.ok && data.success) {
                alert(data.message);
                // Update the button to show "Leave Event"
                buttonElement.textContent = 'Leave Event';
                buttonElement.classList.remove('btn-primary', 'join-event-btn');
                buttonElement.classList.add('btn-danger', 'leave-event-btn');
                buttonElement.dataset.eventId = eventId;
                
                // Add event listener for the new leave button
                buttonElement.addEventListener('click', async (e) => {
                    const eventId = e.target.dataset.eventId;
                    await leaveEvent(eventId);
                });
                
                // Re-fetch events to update participant count
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

        if (totalPages <= 1) return;

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