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
        // Check for JWT token in sessionStorage instead of localStorage
        const token = sessionStorage.getItem('jwt_token');
        const isLoggedIn = token !== null && token !== '';
        console.log('JWT token exists:', isLoggedIn);

        if (profileButton && profilePictureLink) {
            console.log('Profile button and picture link elements found.');
            if (isLoggedIn) {
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

    // Listen for storage changes from other tabs/windows only
    // This prevents the event from firing when the same page updates storage
    let lastToken = sessionStorage.getItem('jwt_token');
    window.addEventListener('storage', (e) => {
        // Only update if the JWT token changed and it's from a different tab/window
        if (e.key === 'jwt_token' && e.newValue !== lastToken) {
            lastToken = e.newValue;
            updateProfileDisplay();
        }
    });
}); 