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
        // data-password-toggle="<input id>" targets a specific field; empty falls back to #password.
        const password = document.getElementById(toggle.dataset.passwordToggle || 'password');
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

// Registration form: live password checklist, match hint and double-submit guard.
const registerForm = document.querySelector('[data-register-form]');

if (registerForm instanceof HTMLFormElement) {
    const password = registerForm.querySelector('#password');
    const confirmation = registerForm.querySelector('#password_confirmation');
    const matchHint = registerForm.querySelector('[data-password-match]');
    const submit = registerForm.querySelector('[data-register-submit]');
    const submitLabel = registerForm.querySelector('[data-submit-label]');
    const mobile = registerForm.querySelector('#mobile');

    const rules = {
        length: (value) => value.length >= 8,
        upper: (value) => /\p{Lu}/u.test(value),
        lower: (value) => /\p{Ll}/u.test(value),
        number: (value) => /\d/.test(value),
    };

    const updateRules = () => {
        if (!(password instanceof HTMLInputElement)) {
            return;
        }

        Object.entries(rules).forEach(([name, test]) => {
            const item = registerForm.querySelector(`[data-rule="${name}"]`);
            const dot = item?.querySelector('[data-rule-dot]');
            const passed = test(password.value);

            item?.classList.toggle('text-emerald-700', passed);
            item?.classList.toggle('text-slate-500', !passed);
            dot?.classList.toggle('bg-emerald-500', passed);
            dot?.classList.toggle('bg-slate-300', !passed);
        });
    };

    const updateMatch = () => {
        if (!(password instanceof HTMLInputElement) || !(confirmation instanceof HTMLInputElement) || !(matchHint instanceof HTMLElement)) {
            return;
        }

        if (confirmation.value === '') {
            matchHint.classList.add('hidden');
            confirmation.setCustomValidity('');

            return;
        }

        const matches = confirmation.value === password.value;
        matchHint.textContent = matches ? 'Passwords match.' : 'Passwords do not match yet.';
        matchHint.classList.remove('hidden');
        matchHint.classList.toggle('text-emerald-700', matches);
        matchHint.classList.toggle('text-red-600', !matches);
        confirmation.setCustomValidity(matches ? '' : 'The passwords do not match.');
    };

    password?.addEventListener('input', () => {
        updateRules();
        updateMatch();
    });
    confirmation?.addEventListener('input', updateMatch);

    // Keep only characters that can appear in a phone number.
    mobile?.addEventListener('input', () => {
        if (mobile instanceof HTMLInputElement) {
            mobile.value = mobile.value.replace(/[^\d+\s\-()]/g, '');
        }
    });

    registerForm.addEventListener('submit', (event) => {
        if (!registerForm.checkValidity()) {
            event.preventDefault();
            registerForm.reportValidity();

            return;
        }

        if (submit instanceof HTMLButtonElement) {
            submit.disabled = true;
        }

        if (submitLabel instanceof HTMLElement) {
            submitLabel.textContent = 'Creating account…';
        }
    });

    // Restore the button if the page is shown again from the back/forward cache.
    window.addEventListener('pageshow', () => {
        if (submit instanceof HTMLButtonElement) {
            submit.disabled = false;
        }

        if (submitLabel instanceof HTMLElement) {
            submitLabel.textContent = 'Create account';
        }
    });

    updateRules();
}
