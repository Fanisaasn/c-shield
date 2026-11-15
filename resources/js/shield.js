/** Progressive enhancement: content remains readable without JavaScript. */
export function initShield() {
    if (!document.body.classList.contains('shield-site')) return;
    const reduced = matchMedia('(prefers-reduced-motion: reduce)');
    const fine = matchMedia('(hover: hover) and (pointer: fine) and (min-width: 768px)');
    const header = document.querySelector('header');
    const menu = document.querySelector('#main-nav');
    const toggle = document.querySelector('[data-menu-toggle]');
    const closeMenu = () => { menu.classList.add('hidden'); toggle.setAttribute('aria-expanded', 'false'); };
    toggle.addEventListener('click', () => {
        const open = toggle.getAttribute('aria-expanded') !== 'true';
        menu.classList.toggle('hidden', !open);
        toggle.setAttribute('aria-expanded', String(open));
    });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') { closeMenu(); toggle.focus(); } });
    menu.addEventListener('click', event => { if (event.target.closest('a')) closeMenu(); });

    const cards = [...document.querySelectorAll('main a.group.rounded-xl, main article.group.rounded-xl')];
    cards.forEach(card => card.classList.add('shield-card'));
    const reveals = [...new Set([...document.querySelectorAll('[data-reveal], main section > div > h1, main .grid > .rounded-xl'), ...cards])];
    if ('IntersectionObserver' in window && !reduced.matches) {
        const observer = new IntersectionObserver(entries => entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const el = entry.target;
            el.classList.remove('reveal-pending');
            el.classList.add('reveal-visible');
            // Remove the entrance transform before pointer interaction takes over.
            setTimeout(() => el.classList.remove('reveal-visible'), 1100);
            observer.unobserve(el);
        }), { threshold: .08 });
        reveals.forEach((el, i) => { el.style.setProperty('--reveal-delay', `${i % 4 * 60}ms`); el.classList.add('reveal-pending'); observer.observe(el); });
        reduced.addEventListener('change', () => {
            if (reduced.matches) { observer.disconnect(); reveals.forEach(el => el.classList.remove('reveal-pending')); }
        });
    }

    const counters = document.querySelectorAll('[data-count]');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(entries => entries.forEach(({ target, isIntersecting }) => {
            if (!isIntersecting) return;
            observer.unobserve(target);
            const total = Number(target.dataset.count);
            if (reduced.matches || !Number.isFinite(total)) return;
            const start = performance.now();
            const tick = now => {
                const progress = reduced.matches ? 1 : Math.min((now - start) / 1100, 1);
                target.textContent = Math.round(total * (1 - Math.pow(1 - progress, 3))).toLocaleString('id-ID');
                if (progress < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        }), { threshold: .5 });
        counters.forEach(el => observer.observe(el));
    }

    const hero = document.querySelector('.shield-hero');
    const layer = document.querySelector('[data-hero-layer]');
    let pointerFrame = 0;
    let activeCard = null;
    let pendingPointer = null;
    const resetPointer = () => {
        cancelAnimationFrame(pointerFrame); pointerFrame = 0; pendingPointer = null;
        if (activeCard) ['--rx', '--ry', '--cx', '--cy'].forEach(key => activeCard.style.removeProperty(key));
        activeCard = null;
        if (layer) { layer.style.removeProperty('--px'); layer.style.removeProperty('--py'); }
    };
    const renderPointer = () => {
        pointerFrame = 0;
        if (!pendingPointer || reduced.matches || !fine.matches) return;
        const { element, x, y, type } = pendingPointer;
        // One geometry read per frame; all style writes follow it.
        const bounds = element.getBoundingClientRect();
        const nx = (x - bounds.left) / bounds.width - .5;
        const ny = (y - bounds.top) / bounds.height - .5;
        if (type === 'hero') {
            layer.style.setProperty('--px', `${nx * 12}px`); layer.style.setProperty('--py', `${ny * 10}px`);
        } else {
            element.style.setProperty('--rx', `${-ny * 4}deg`); element.style.setProperty('--ry', `${nx * 4}deg`);
            element.style.setProperty('--cx', `${(nx + .5) * 100}%`); element.style.setProperty('--cy', `${(ny + .5) * 100}%`);
        }
    };
    const track = (element, type) => {
        element.addEventListener('pointermove', event => {
            if (!fine.matches || reduced.matches) return;
            if (type === 'card') activeCard = element;
            pendingPointer = { element, type, x: event.clientX, y: event.clientY };
            if (!pointerFrame) pointerFrame = requestAnimationFrame(renderPointer);
        }, { passive: true });
        element.addEventListener('pointerleave', resetPointer);
    };
    cards.forEach(card => track(card, 'card'));
    if (hero && layer) track(hero, 'hero');
    reduced.addEventListener('change', resetPointer); fine.addEventListener('change', resetPointer);

    const sectionLinks = [...document.querySelectorAll('.section-nav a')];
    const sections = sectionLinks.map(link => document.querySelector(link.hash));
    const stages = [...document.querySelectorAll('[data-stage]')];
    const workflow = document.querySelector('.security-workflow');
    const label = document.querySelector('[data-workflow-label]');
    let scrollFrame = 0;
    let currentSection = -1;
    let currentStage = -1;
    const updateScroll = () => {
        scrollFrame = 0;
        // Batch geometry reads before DOM mutations.
        const sectionTops = sections.map(el => el.getBoundingClientRect().top);
        const stageBounds = stages.map(el => el.getBoundingClientRect());
        const marker = innerHeight * .48;
        let sectionIndex = 0;
        sectionTops.forEach((top, i) => { if (top <= 180) sectionIndex = i; });
        let stageIndex = 0;
        stageBounds.forEach((rect, i) => { if (rect.top <= marker) stageIndex = i; });
        header.classList.toggle('is-scrolled', scrollY > 20);
        if (sectionIndex !== currentSection) {
            currentSection = sectionIndex;
            sectionLinks.forEach((link, i) => { if (i === sectionIndex) link.setAttribute('aria-current', 'location'); else link.removeAttribute('aria-current'); });
        }
        if (stages.length) {
            const first = stageBounds[0].top;
            const last = stageBounds.at(-1).top;
            workflow.style.setProperty('--progress', Math.max(0, Math.min(1, (marker - first) / Math.max(1, last - first))));
            if (stageIndex !== currentStage) {
                currentStage = stageIndex;
                stages.forEach((stage, i) => stage.classList.toggle('is-active', i === stageIndex));
                label.textContent = stages[stageIndex].dataset.stage;
            }
        }
    };
    const scheduleScroll = () => { if (!scrollFrame) scrollFrame = requestAnimationFrame(updateScroll); };
    addEventListener('scroll', scheduleScroll, { passive: true });
    addEventListener('resize', scheduleScroll, { passive: true });
    updateScroll();
}
