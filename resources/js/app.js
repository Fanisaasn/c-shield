import './bootstrap';
import Chart from 'chart.js/auto';
import { initPublicMotion } from './motion';

window.Chart = Chart;

if (document.body?.hasAttribute('data-public-motion')) {
    initPublicMotion(Chart);
}

const navToggle = document.getElementById('main-nav-toggle');
const mainNav = document.getElementById('main-nav');

navToggle?.addEventListener('click', () => {
    const isOpen = !mainNav?.classList.toggle('hidden');
    navToggle.setAttribute('aria-expanded', String(isOpen));
    navToggle.setAttribute('aria-label', isOpen ? 'Tutup menu' : 'Buka menu');
});

mainNav?.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
        if (window.innerWidth < 768) {
            mainNav.classList.add('hidden');
            navToggle?.setAttribute('aria-expanded', 'false');
            navToggle?.setAttribute('aria-label', 'Buka menu');
        }
    });
});
