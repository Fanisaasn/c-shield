/** Enhance existing public elements without adding markup or hiding content by default. */
export function initPublicMotion(Chart) {
    const reduced = matchMedia('(prefers-reduced-motion: reduce)');
    const desktopPointer = matchMedia('(hover: hover) and (pointer: fine) and (min-width: 768px)');
    const animations = new Map();
    const entered = new Set();
    const visibleCards = new Set();
    const cardDepth = new Map();
    let direction = 1;
    let previousScroll = scrollY;
    let observer;

    function reveal(element, delay = 0) {
        if (reduced.matches || !element.animate || element.contains(document.activeElement)) return;
        animations.get(element)?.cancel();
        const animation = element.animate([
            { opacity: 0, transform: `translateY(${direction * 12}px)` },
            { opacity: 1, transform: 'translateY(0)' },
        ], { duration: 440, delay, easing: 'cubic-bezier(.2,.65,.3,1)', fill: 'backwards' });
        animations.set(element, animation);
        animation.finished.then(() => {
            if (animations.get(element) !== animation) return;
            animations.delete(element);
            const bounds = element.getBoundingClientRect();
            if (bounds.bottom <= 0 || bounds.top >= innerHeight) entered.delete(element);
        }).catch(() => {});
    }

    // Keep native video, iframe, modal, and form containers out of transform effects.
    const cards = [...document.querySelectorAll('main a.group.rounded-xl')]
        .filter(card => !card.querySelector('iframe, video, form'));
    cards.forEach(card => card.classList.add('motion-card'));
    document.querySelectorAll('main a.rounded-md, main button.rounded-md, #main-nav a.rounded-md')
        .forEach(button => button.classList.add('motion-button'));

    const heroCopy = document.querySelector('[data-motion-hero-copy]');
    const targets = [...new Set([...(heroCopy?.children ?? []), ...cards,
        ...document.querySelectorAll('main h1, main h2, main h3')])]
        .filter(el => !el.closest('form, #survey-modal') &&
            !cards.some(card => card !== el && card.contains(el)));
    if ('IntersectionObserver' in window) {
        observer = new IntersectionObserver(entries => {
            let stagger = 0;
            entries.forEach(entry => {
                const el = entry.target;
                if (!entry.isIntersecting) {
                    // The entrance's own translation can cross the viewport edge.
                    // Let it settle before re-arming, so slow scrolling cannot flicker.
                    if (!animations.has(el)) entered.delete(el);
                    visibleCards.delete(el);
                    return;
                }
                if (cards.includes(el)) visibleCards.add(el);
                // Re-arm only after a full exit, avoiding flicker at the entry threshold.
                if (entry.intersectionRatio >= .12 && !entered.has(el)) {
                    entered.add(el);
                    reveal(el, Math.min(stagger++ * 45, 135));
                }
            });
            schedule();
        }, { threshold: [0, .12] });
        targets.forEach(el => observer.observe(el));
    }

    document.addEventListener('focusin', event => {
        animations.forEach((animation, el) => {
            if (el.contains(event.target)) { animation.cancel(); animations.delete(el); }
        });
    });

    const header = document.querySelector('body > header');
    const hero = document.querySelector('[data-motion-hero]');
    const background = document.querySelector('[data-motion-background]');
    let heroVisible = true;
    let frame = 0;
    let pointer = null;

    function render() {
        frame = 0;
        // Read geometry before writing styles; only one update per animation frame.
        const bounds = background && heroVisible && !reduced.matches && desktopPointer.matches
            ? hero.getBoundingClientRect() : null;
        const x = bounds && pointer ? ((pointer.x - bounds.left) / bounds.width - .5) * 6 : 0;
        const y = bounds ? Math.max(-6, Math.min(6, -bounds.top * .025)) +
            (pointer ? ((pointer.y - bounds.top) / bounds.height - .5) * 4 : 0) : 0;
        const depths = [...visibleCards].map(card => {
            const rect = card.getBoundingClientRect();
            const center = rect.top - (cardDepth.get(card) ?? 0) + rect.height / 2;
            return [card, reduced.matches || !desktopPointer.matches ? 0 :
                Math.max(-3, Math.min(3, (innerHeight / 2 - center) * .012))];
        });
        header?.classList.toggle('motion-scrolled', scrollY > 8);
        depths.forEach(([card, depth]) => {
            cardDepth.set(card, depth);
            card.style.setProperty('--motion-depth', `${depth}px`);
        });
        if (background) {
            background.style.setProperty('--motion-x', `${x}px`);
            background.style.setProperty('--motion-y', `${y}px`);
        }
    }
    function schedule() { if (!frame) frame = requestAnimationFrame(render); }
    addEventListener('scroll', () => {
        if (scrollY !== previousScroll) direction = scrollY > previousScroll ? 1 : -1;
        previousScroll = scrollY;
        schedule();
    }, { passive: true });
    addEventListener('resize', schedule, { passive: true });
    hero?.addEventListener('pointermove', event => {
        if (reduced.matches || !desktopPointer.matches) return;
        pointer = { x: event.clientX, y: event.clientY };
        schedule();
    }, { passive: true });
    hero?.addEventListener('pointerleave', () => { pointer = null; schedule(); });
    if (hero && 'IntersectionObserver' in window) {
        new IntersectionObserver(([entry]) => {
            heroVisible = entry.isIntersecting;
            hero.classList.toggle('motion-paused', !heroVisible || document.hidden);
        }).observe(hero);
        document.addEventListener('visibilitychange', () => {
            hero.classList.toggle('motion-paused', !heroVisible || document.hidden);
        });
    }

    // Defer existing chart animation until visible, and replay after a full exit.
    const visibleCharts = new Set();
    const chartObserver = 'IntersectionObserver' in window ? new IntersectionObserver(entries => {
        entries.forEach(entry => {
            const chart = Chart.getChart(entry.target);
            if (!chart) return;
            if (!entry.isIntersecting) {
                visibleCharts.delete(chart);
                chart.stop();
            } else if (entry.intersectionRatio >= .2 && !visibleCharts.has(chart)) {
                visibleCharts.add(chart);
                if (reduced.matches) return;
                chart.options.animation = { duration: 450 };
                chart.reset();
                chart.update();
            }
        });
    }, { threshold: [0, .2] }) : null;
    Chart.register({
        id: 'publicMotionPreference',
        beforeInit(chart) {
            chart.options.animation = false;
        },
        afterInit(chart) {
            chartObserver?.observe(chart.canvas);
        },
        afterDestroy(chart) {
            chartObserver?.unobserve(chart.canvas);
            visibleCharts.delete(chart);
        },
    });
    reduced.addEventListener('change', () => {
        if (reduced.matches) {
            animations.forEach(animation => animation.cancel());
            animations.clear();
            Object.values(Chart.instances).forEach(chart => {
                chart.stop();
                chart.options.animation = false;
                chart.update('none');
            });
        }
        entered.clear();
        observer?.disconnect();
        targets.forEach(el => observer?.observe(el));
        visibleCharts.clear();
        chartObserver?.disconnect();
        Object.values(Chart.instances).forEach(chart => chartObserver?.observe(chart.canvas));
        pointer = null;
        schedule();
    });
    desktopPointer.addEventListener('change', () => { pointer = null; schedule(); });
    render();
}
