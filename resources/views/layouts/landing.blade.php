<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Faith Community Loboc Parish')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; }
        html, body { width: 100%; min-height: 100%; margin: 0; }
        html { overflow-x: hidden; overflow-y: auto; }
        html { scroll-behavior: smooth; }
        body { min-height: 100svh; overflow-x: hidden; overflow-y: auto; }
        body { font-family: Georgia, 'Times New Roman', serif; color: #102d50; }
        .gold { color: #d8ab31; }
        .gold-bg { background: #d8ab31; }
        .nav-shadow { box-shadow: 0 8px 25px rgba(16,45,80,.12); }
        .page { width: 100%; min-height: 100svh; position: relative; }
        .content-page { height: auto; min-height: 100svh; overflow: visible; }
        .content-main { height: auto; min-height: 100svh; }
        .landing-page { height: auto; overflow: visible; }
        .landing-main { height: auto; min-height: 100svh; overflow: visible; }
        .landing-section { scroll-margin-top: 8rem; }
        .service-card { box-shadow: 0 8px 22px rgba(16,45,80,.10); transition: transform .2s ease, box-shadow .2s ease; }
        .service-card:hover { transform: translateY(-3px); box-shadow: 0 14px 30px rgba(16,45,80,.16); }
        .soft-card { box-shadow: 0 12px 30px rgba(16,45,80,.10); }
        .site-nav-row { flex-wrap: nowrap; }
        .site-nav-links { flex-wrap: nowrap; white-space: nowrap; }
        .site-nav-link { color: #102d50; padding-bottom: .5rem; border-bottom: 2px solid transparent; transition: color .2s ease, border-color .2s ease; }
        .site-nav-link:hover { color: #c29419; }
        .site-nav-link.is-active { border-bottom-color: #d8ab31; }
        .mobile-menu-links > a.is-active { color: #c29419; }
        .mobile-menu-toggle { display: none; }
        .mobile-menu[hidden] { display: none !important; }
        .mobile-menu-links { display: flex; align-items: center; gap: 1.25rem; }
        .mobile-menu-links > a { color: #102d50; font-weight: 600; }
        .mobile-menu-auth { display: flex; gap: .5rem; margin-left: auto; }
        .mobile-login, .mobile-register { border-radius: .75rem; padding: .55rem .8rem; font-size: .875rem; font-weight: 600; }
        .mobile-login { border: 1px solid #102d50; color: #102d50; }
        .mobile-register { background: #123d67; color: white; }
        .landing-hero { flex: 1 0 auto; min-height: 25rem; }
        .landing-hero-row { min-height: 100%; }
        .landing-copy { width: min(100%, 620px); }
        .landing-services { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        @media (max-width: 1100px) {
            .desktop-nav { gap: 1rem !important; }
            .hero-title { font-size: clamp(1.9rem, 7.5vw, 4.6rem) !important; }
            .verse-card { display: none; }
        }
        @media (max-width: 1229px) {
            /* Logo + links + auth buttons need ~1195px (+ scrollbar) on one row; below that, collapse into the menu. */
            .site-nav-links, .site-auth-links { display: none !important; }
            .mobile-menu-toggle { display: inline-flex !important; }
            .mobile-menu:not([hidden]) { display: block; border-top: 1px solid rgba(16,45,80,.12); margin-top: .75rem; padding-top: .5rem; }
            .mobile-menu-links { align-items: stretch; flex-direction: column; gap: 0; }
            .mobile-menu-links > a { padding: .7rem .5rem; border-radius: .5rem; }
            .mobile-menu-links > a:hover, .mobile-menu-links > a.is-active { background: #fffaf0; color: #c29419; }
            .mobile-menu-auth { margin: .5rem 0 .25rem; }
        }
        @media (max-width: 900px) {
            .landing-services { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 760px) {
            .site-nav { padding: .65rem .75rem !important; }
            .site-nav-row { gap: .65rem !important; }
            .site-logo { gap: .5rem !important; }
            .site-logo img { height: 2.5rem !important; }
            .site-logo h1, .site-logo p { font-size: .8rem !important; line-height: 1.1 !important; }
            .landing-main { padding: 7.5rem 1rem 1rem !important; }
            .content-main { padding-top: 12rem !important; }
            .landing-hero { min-height: auto; padding: 1.5rem 0 2rem; }
            .landing-hero-row { align-items: flex-start; }
            .landing-copy { max-width: none; }
            .landing-actions { flex-wrap: wrap; }
            .landing-actions a { padding: .7rem 1rem !important; }
        }
        @media (max-width: 500px) {
            .page > header { padding-right: .5rem; padding-left: .5rem; }
            .site-nav { padding: .5rem !important; }
            .site-logo h1, .site-logo p { font-size: .7rem !important; }
            .site-auth-links { gap: .3rem !important; }
            .site-auth-link { padding: .45rem .5rem !important; font-size: .7rem; }
            .landing-main { padding-top: 6.5rem !important; }
            .hero-title { font-size: clamp(1.65rem, 8vw, 2.4rem) !important; }
            .landing-services { grid-template-columns: minmax(0, 1fr); }
            .content-main { padding-left: 1rem !important; padding-right: 1rem !important; padding-top: 13.5rem !important; }
            .about-card { padding: 1.25rem !important; }
        }
        @media (max-height: 650px) and (min-width: 761px) {
            .landing-main { padding-top: 9rem !important; }
            .landing-hero { min-height: 20rem; }
        }
    </style>
    @stack('styles')
</head>
<body>

@yield('page')

@stack('scripts')
</body>
</html>
