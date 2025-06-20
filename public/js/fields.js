document.addEventListener('DOMContentLoaded', () => {
    const fieldGrid = document.querySelector('.field-grid');
    const searchField = document.getElementById('search-field');
    const sportTypeFilter = document.getElementById('sport-type-filter');
    const applyFiltersBtn = document.getElementById('apply-filters');
    const paginationDiv = document.querySelector('.pagination');

    let currentPage = 1;
    const limit = 9; // Number of fields per page

    async function fetchSportsFields(filters = {}) {
        try {
            const params = new URLSearchParams({
                limit: limit,
                offset: (currentPage - 1) * limit,
                ...filters
            });
            const response = await fetch(`/local_greeter/api/index.php?action=listFields&${params.toString()}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            renderSportsFields(data.fields);
            renderPagination(data.total);
        } catch (error) {
            console.error('Error fetching sports fields:', error);
            fieldGrid.innerHTML = '<p>Error loading sports fields. Please try again later.</p>';
            paginationDiv.innerHTML = '';
        }
    }

    function renderSportsFields(fields) {
        fieldGrid.innerHTML = ''; // Clear previous fields
        if (fields.length === 0) {
            fieldGrid.innerHTML = '<p>No sports fields found matching your criteria.</p>';
            return;
        }

        fields.forEach(field => {
            const fieldCard = document.createElement('div');
            fieldCard.classList.add('field-card');

            // Get sport type image based on field type
            const sportImages = {
                'football': 'images/football.jpg',
                'basketball': 'images/basketball.jpg',
                'tennis': 'images/tennis.jpg',
                'volleyball': 'images/tennis.jpg', // Using tennis image as fallback
                'multi-sport': 'images/tennis.jpg', // Using tennis image as fallback
                'yoga': 'images/yoga.jpg'
            };

            const imageUrl = sportImages[field.type] || 'images/map.png';
            
            // Parse amenities if it's JSON
            let amenitiesText = '';
            if (field.amenities) {
                try {
                    const amenities = JSON.parse(field.amenities);
                    if (Array.isArray(amenities)) {
                        amenitiesText = amenities.join(', ');
                    }
                } catch (e) {
                    amenitiesText = field.amenities;
                }
            }

            // Parse opening hours if it's JSON
            let openingHoursText = '';
            if (field.opening_hours) {
                try {
                    const hours = JSON.parse(field.opening_hours);
                    if (typeof hours === 'object') {
                        openingHoursText = Object.entries(hours)
                            .map(([day, time]) => `${day}: ${time}`)
                            .join(', ');
                    }
                } catch (e) {
                    openingHoursText = field.opening_hours;
                }
            }

            fieldCard.innerHTML = `
                <img src="${imageUrl}" alt="${field.name}">
                <div class="field-card-content">
                    <h3>${field.name}</h3>
                    <p class="location">Address: ${field.address || 'N/A'}</p>
                    <p class="sport-type">Sport Type: ${field.type}</p>
                    <p class="coordinates">Coordinates: ${field.latitude}, ${field.longitude}</p>
                    ${amenitiesText ? `<p class="amenities">Amenities: ${amenitiesText}</p>` : ''}
                    ${openingHoursText ? `<p class="hours">Hours: ${openingHoursText}</p>` : ''}
                    <p class="access">Access: ${field.is_public ? 'Public' : 'Private'}</p>
                </div>
            `;
            fieldGrid.appendChild(fieldCard);
        });
    }

    function renderPagination(totalFields) {
        paginationDiv.innerHTML = '';
        const totalPages = Math.ceil(totalFields / limit);

        if (totalPages > 1) {
            const prevButton = document.createElement('button');
            prevButton.textContent = 'Previous';
            prevButton.disabled = currentPage === 1;
            prevButton.addEventListener('click', () => {
                currentPage--;
                applyFilters();
            });
            paginationDiv.appendChild(prevButton);

            for (let i = 1; i <= totalPages; i++) {
                const pageButton = document.createElement('button');
                pageButton.textContent = i;
                pageButton.classList.toggle('active', i === currentPage);
                pageButton.addEventListener('click', () => {
                    currentPage = i;
                    applyFilters();
                });
                paginationDiv.appendChild(pageButton);
            }

            const nextButton = document.createElement('button');
            nextButton.textContent = 'Next';
            nextButton.disabled = currentPage === totalPages;
            nextButton.addEventListener('click', () => {
                currentPage++;
                applyFilters();
            });
            paginationDiv.appendChild(nextButton);
        }
    }

    function applyFilters() {
        const filters = {};
        const searchQuery = searchField.value.trim();
        const sportType = sportTypeFilter.value;

        if (searchQuery) {
            filters.search = searchQuery;
        }
        if (sportType) {
            filters.sport_type = sportType;
        }

        currentPage = 1; // Reset to first page on new filter
        fetchSportsFields(filters);
    }

    // Event Listeners
    applyFiltersBtn.addEventListener('click', applyFilters);
    // Optional: Add event listeners for 'enter' key on input fields
    searchField.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') applyFilters();
    });

    // Initial load
    fetchSportsFields();
}); 