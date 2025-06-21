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
        const userData = getUserFromJWT();
        return { 
            token, 
            userData
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

    // Helper to check if JWT is valid and not expired
    function isJWTValid() {
        const token = sessionStorage.getItem('jwt_token');
        if (!token) return false;
        try {
            const payload = JSON.parse(atob(token.split('.')[1]));
            // Check for expiration
            if (!payload.exp || Date.now() >= payload.exp * 1000) {
                sessionStorage.removeItem('jwt_token');
                return false;
            }
            return true;
        } catch (e) {
            sessionStorage.removeItem('jwt_token');
            return false;
        }
    }

    async function fetchEvents(page, filters = {}) {
        try {
            const offset = (page - 1) * limit;
            // Dynamically determine the base path for the API
            const basePath = window.location.pathname.split('/').includes('local_greeter') ? '/local_greeter' : '';
            let url = new URL(window.location.origin + basePath + '/api/index.php');
            
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
                if (!token || !isJWTValid()) {
                    eventGrid.innerHTML = '<p>You must be logged in to view this section. Please <a href="/local_greeter/login">log in</a>.</p>';
                    paginationDiv.innerHTML = '';
                    return;
                }

                const userData = getUserFromJWT();
                if (!userData || !userData.user_id) {
                    eventGrid.innerHTML = '<p>Your session seems to be invalid. Please <a href="/local_greeter/login">log in</a> again.</p>';
                    paginationDiv.innerHTML = '';
                    return;
                }

                options.body = JSON.stringify({
                    user_id: userData.user_id,
                });
            }

            // Debug log request
            console.log('Fetching events:', { url: url.toString(), options });

            const response = await fetch(url, options);
            
            // Debug log response
            console.log('API response:', response);

            if (!response.ok) {
                if (response.status === 401) {
                    // Token expired or invalid
                    sessionStorage.removeItem('jwt_token');
                    if (currentTab === 'joined' || currentTab === 'created') {
                        eventGrid.innerHTML = '<p>Your session has expired. Please <a href="/local_greeter/login">log in</a> again.</p>';
                        paginationDiv.innerHTML = '';
                        return;
                    }
                }
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            // Debug log data
            console.log('API data:', data);

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

        const userData = getUserFromJWT();
        const userId = userData ? userData.user_id : null;

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
                // In "My Joined Events" tab - show leave button only if not organizer
                if (!isUserCreator) {
                    buttonsHTML = `<button class="btn btn-danger leave-event-btn" data-event-id="${event.event_id}">Leave Event</button>`;
                }
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
                <div class="event-card-image" style="height:200px;overflow:hidden;">
                    <img src="${imageUrl}" alt="${event.title}" style="width:100%;height:100%;object-fit:cover;">
                </div>
                <div class="event-card-content">
                    <h3>${event.title}</h3>
                    <p>${event.description}</p>
                    <p><strong>Date:</strong> ${formatDate(event.start_time)}</p>
                    <p><strong>Location:</strong> ${event.field_name || event.address || 'TBD'}</p>
                    <p><strong>Sport Type:</strong> ${event.sport_type}</p>
                    <p><strong>Participants:</strong> ${event.current_participants} / ${event.max_participants || '∞'}</p>
                    <div class="event-card-buttons">
                        ${buttonsHTML}
                    </div>
                </div>
            `;

            eventGrid.appendChild(eventCard);
        });

        // Attach event listeners to join/leave buttons after rendering
        eventGrid.querySelectorAll('.join-event-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const eventId = e.target.dataset.eventId;
                await joinEvent(eventId);
            });
        });
        eventGrid.querySelectorAll('.leave-event-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const eventId = e.target.dataset.eventId;
                await leaveEvent(eventId);
            });
        });
    }

    function renderPagination(totalEvents) {
        const totalPages = Math.ceil(totalEvents / limit);
        let paginationHTML = '<nav><ul class="pagination">';

        if (currentPage > 1) {
            paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="1">First</a></li>`;
            paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a></li>`;
        }

        if (currentPage < totalPages) {
            paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage + 1}">Next</a></li>`;
            paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">Last</a></li>`;
        }

        paginationHTML += '</ul></nav>';
        paginationDiv.innerHTML = paginationHTML;
    }

    function applyFilters() {
        currentPage = 1;
        fetchEvents(currentPage, {
            sportType: sportTypeFilter.value,
            search: searchInput.value
        });
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
            // Debug log
            console.log('Tab clicked:', button.textContent, 'currentTab:', currentTab);
            // Fetch events for the selected tab
            applyFilters();
        });
    });

    // Initial fetch for the default tab on page load
    applyFilters();

    applyFiltersButton.addEventListener('click', (e) => {
        e.preventDefault();
        applyFilters();
    });

    async function joinEvent(eventId) {
        const token = sessionStorage.getItem('jwt_token');
        if (!token) {
            alert('You must be logged in to join an event.');
            window.location.href = '/local_greeter/login';
            return;
        }
        try {
            const basePath = window.location.pathname.split('/').includes('local_greeter') ? '/local_greeter' : '';
            const response = await fetch(window.location.origin + basePath + '/api/index.php?action=joinEvent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ event_id: eventId })
            });
            const data = await response.json();
            if (response.ok && data.success) {
                alert('You have joined the event!');
                applyFilters();
            } else {
                alert(data.message || 'Failed to join event.');
            }
        } catch (error) {
            alert('Error joining event.');
        }
    }

    async function leaveEvent(eventId) {
        const token = sessionStorage.getItem('jwt_token');
        if (!token) {
            alert('You must be logged in to leave an event.');
            window.location.href = '/local_greeter/login';
            return;
        }
        try {
            const basePath = window.location.pathname.split('/').includes('local_greeter') ? '/local_greeter' : '';
            const response = await fetch(window.location.origin + basePath + '/api/index.php?action=leaveEvent', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ event_id: eventId })
            });
            const data = await response.json();
            if (response.ok && data.success) {
                alert('You have left the event!');
                applyFilters();
            } else {
                alert(data.message || 'Failed to leave event.');
            }
        } catch (error) {
            alert('Error leaving event.');
        }
    }


});