import './bootstrap';

const menuToggle = document.querySelector('[data-mobile-menu-toggle]');
const mobileMenu = document.querySelector('[data-mobile-menu]');

if (menuToggle instanceof HTMLButtonElement && mobileMenu instanceof HTMLElement) {
    const openIcon = menuToggle.querySelector('[data-menu-open-icon]');
    const closeIcon = menuToggle.querySelector('[data-menu-close-icon]');

    const setMenuOpen = (isOpen) => {
        if (mobileMenu.hasAttribute('data-mobile-menu-flex')) {
            mobileMenu.classList.toggle('hidden', !isOpen);
            mobileMenu.classList.toggle('flex', isOpen);
        } else {
            mobileMenu.hidden = !isOpen;
        }
        menuToggle.setAttribute('aria-expanded', String(isOpen));
        menuToggle.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
        openIcon?.classList.toggle('hidden', isOpen);
        closeIcon?.classList.toggle('hidden', !isOpen);
    };

    menuToggle.addEventListener('click', () => {
        setMenuOpen(menuToggle.getAttribute('aria-expanded') !== 'true');
    });

    mobileMenu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setMenuOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && menuToggle.getAttribute('aria-expanded') === 'true') {
            setMenuOpen(false);
            menuToggle.focus();
        }
    });
}

// Landing page: highlight the nav link for the section currently in view.
const navLinks = document.querySelectorAll('[data-nav-link]');

if (navLinks.length > 0) {
    const sectionIds = [...new Set([...navLinks].map((link) => link.dataset.navLink))];
    const sections = sectionIds
        .map((id) => document.getElementById(id))
        .filter((section) => section instanceof HTMLElement);

    const setActive = (id) => {
        navLinks.forEach((link) => {
            const isActive = link.dataset.navLink === id;
            link.classList.toggle('is-active', isActive);
            if (isActive) {
                link.setAttribute('aria-current', 'true');
            } else {
                link.removeAttribute('aria-current');
            }
        });
    };

    const updateActive = () => {
        // A section is "current" once its top passes just below the fixed navbar.
        const marker = 140;
        let current = 'home';
        let closestTop = -Infinity;

        // Nav order differs from page order, so pick the section nearest above the marker.
        sections.forEach((section) => {
            const top = section.getBoundingClientRect().top;
            if (top <= marker && top > closestTop) {
                closestTop = top;
                current = section.id;
            }
        });

        // At the very bottom the last (short) section may never reach the marker.
        if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2) {
            current = sections.reduce((last, section) => (section.offsetTop > last.offsetTop ? section : last)).id;
        }

        setActive(current);
    };

    window.addEventListener('scroll', updateActive, { passive: true });
    window.addEventListener('resize', updateActive);
    window.addEventListener('hashchange', updateActive);
    updateActive();
}

document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
    toggle.addEventListener('click', () => {
        const password = document.getElementById('password');
        const showIcon = toggle.querySelector('[data-password-icon-show]');
        const hideIcon = toggle.querySelector('[data-password-icon-hide]');

        if (!(password instanceof HTMLInputElement) || !(showIcon instanceof HTMLElement) || !(hideIcon instanceof HTMLElement)) {
            return;
        }

        const isVisible = password.type === 'password';
        password.type = isVisible ? 'text' : 'password';
        showIcon.classList.toggle('hidden', isVisible);
        hideIcon.classList.toggle('hidden', !isVisible);
        toggle.setAttribute('aria-label', isVisible ? 'Hide password' : 'Show password');
    });
});
