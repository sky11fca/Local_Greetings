document.addEventListener('DOMContentLoaded', () => {
    const fieldGrid = document.querySelector('.field-grid');
    const searchField = document.getElementById('search-field');
    const sportTypeFilter = document.getElementById('sport-type-filter');
    const radiusFilter = document.getElementById('radius-filter');
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
            const response = await fetch(`/api/sports-fields?${params.toString()}`);
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

            fieldCard.innerHTML = `
                <img src="${field.image_url || 'images/map.png'}" alt="${field.name}">
                <div class="field-card-content">
                    <h3>${field.name}</h3>
                    <p class="location">Location: ${field.location}</p>
                    <p class="sport-type">Sport: ${field.sport_type}</p>
                    <p>${field.description || ''}</p>
                    <div class="rating">Rating: ${field.average_rating ? field.average_rating.toFixed(1) : 'N/A'} (${field.review_count || 0} reviews)</div>
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
        const radius = radiusFilter.value;

        if (searchQuery) {
            filters.location = searchQuery;
        }
        if (sportType) {
            filters.sport_type = sportType;
        }
        if (radius) {
            filters.radius = radius;
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