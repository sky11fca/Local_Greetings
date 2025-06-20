<?php
// Set page-specific variables
$pageTitle = "Sports Fields - Iași Sports Network";
$currentPage = "fields";

// Additional CSS files for this page
$additionalCSS = [
    "/local_greeter/public/css/fields.css"
];

// Additional scripts for this page
$additionalScripts = [
    "/local_greeter/public/js/fields.js"
];

// Include the header template
include __DIR__ . '/../templates/header.php';
?>

<main>
    <section id="fields-list">
        <div class="container">
            <h2>All Sports Fields</h2>
            <div class="filters">
                <input type="text" id="search-field" placeholder="Search by location...">
                <select id="sport-type-filter">
                    <option value="">All Sport Types</option>
                    <option value="football">Football</option>
                    <option value="basketball">Basketball</option>
                    <option value="tennis">Tennis</option>
                    <option value="volleyball">Volleyball</option>
                    <option value="multi-sport">Multi-Sport</option>
                </select>
                <button class="btn btn-primary" id="apply-filters">Apply Filters</button>
            </div>
            <div class="field-grid">
                <!-- Sports field cards will be loaded here by JavaScript -->
            </div>
            <div class="pagination">
                <!-- Pagination controls will go here -->
            </div>
        </div>
    </section>
</main>

<?php
// Include the footer template
include __DIR__ . '/../templates/footer.php';
?> 