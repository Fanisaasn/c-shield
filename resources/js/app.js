import './bootstrap';
import Chart from 'chart.js/auto';
import { initPublicMotion } from './motion';

window.Chart = Chart;

if (document.body?.hasAttribute('data-public-motion')) {
    initPublicMotion(Chart);
}
