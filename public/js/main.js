console.log('main.js loaded and executing.');

// You can add any interactive JavaScript here

document.addEventListener('DOMContentLoaded', () => {

    // Hamburger menu functionality
    const hamburgerMenu = document.querySelector('.hamburger-menu');
    const mainNav = document.querySelector('.main-nav');

    if (hamburgerMenu) {
        hamburgerMenu.addEventListener('click', () => {
            hamburgerMenu.classList.toggle('active');
            mainNav.classList.toggle('active');
        });
    }

    // Removed Profile Dropdown functionality

    // Profile Button/Picture Toggle functionality
    const profileButton = document.getElementById('profile-button');
    const profilePictureLink = document.getElementById('profile-picture-link');

    function updateProfileDisplay() {
        console.log('updateProfileDisplay called.');
        const isLoggedIn = localStorage.getItem('isLoggedIn');
        console.log('isLoggedIn status:', isLoggedIn);

        if (profileButton && profilePictureLink) {
            console.log('Profile button and picture link elements found.');
            if (isLoggedIn === 'true') {
                console.log('User is logged in. Hiding profile button, showing profile picture.');
                profileButton.classList.add('hidden');
                profilePictureLink.classList.remove('hidden');
            } else {
                console.log('User is logged out. Showing profile button, hiding profile picture.');
                profileButton.classList.remove('hidden');
                profilePictureLink.classList.add('hidden');
            }
            console.log('Profile button classes:', profileButton.classList.value);
            console.log('Profile picture link classes:', profilePictureLink.classList.value);
        } else {
            console.warn('Profile button or picture link elements not found in DOM.');
        }
    }

    // Initial update on page load
    updateProfileDisplay();

    // Listen for storage changes (e.g., login/logout from auth.js)
    window.addEventListener('storage', updateProfileDisplay);
}); 