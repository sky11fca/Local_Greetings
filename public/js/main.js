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
}); 