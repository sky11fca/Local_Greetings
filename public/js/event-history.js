document.addEventListener('DOMContentLoaded', () => {
    const eventGrid = document.getElementById('event-history-grid');
    const paginationDiv = document.getElementById('history-pagination');
    let currentPage = 1;
    const limit = 8;

    async function fetchHistory(page = 1) {
        const token = localStorage.getItem('jwt_token');
        if (!token) {
            eventGrid.innerHTML = '<p>You must be logged in to view your event history. Please <a href="/local_greeter/login">log in</a>.</p>';
            return;
        }

        try {
            const offset = (page - 1) * limit;
            const response = await fetch(`/local_greeter/api/index.php?action=getPastEvents&limit=${limit}&offset=${offset}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                renderEvents(data.events);
                renderPagination(data.total_events || 0);
            } else {
                throw new Error(data.message || 'Failed to load event history.');
            }
        } catch (error) {
            console.error('Error fetching event history:', error);
            eventGrid.innerHTML = `<p>Could not load your event history: ${error.message}</p>`;
        }
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString.replace(' ', 'T'));
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    }

    function renderEvents(events) {
        eventGrid.innerHTML = '';
        if (!events || events.length === 0) {
            eventGrid.innerHTML = '<p>You have no past events to show.</p>';
            return;
        }

        events.forEach(event => {
            const eventCard = document.createElement('div');
            eventCard.classList.add('event-card');
            
            eventCard.innerHTML = `
                <div class="event-card-content">
                    <h3>${event.title}</h3>
                    <p class="location">${event.field_name} - ${event.address}</p>
                    <p class="date">Finished on: ${formatDate(event.end_time)}</p>
                    <p class="sport-type">Sport: ${event.sport_type}</p>
                </div>
            `;
            eventGrid.appendChild(eventCard);
        });
    }

    function renderPagination(totalEvents) {
        paginationDiv.innerHTML = '';
        const totalPages = Math.ceil(totalEvents / limit);
        if (totalPages <= 1) return;

        for (let i = 1; i <= totalPages; i++) {
            const button = document.createElement('button');
            button.textContent = i;
            button.classList.toggle('active', i === currentPage);
            button.addEventListener('click', () => {
                currentPage = i;
                fetchHistory(currentPage);
            });
            paginationDiv.appendChild(button);
        }
    }

    fetchHistory();
}); 