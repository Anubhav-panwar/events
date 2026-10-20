import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

// Make charts available before Alpine initializes analytics components.
window.Chart = Chart;
window.Alpine = Alpine;

Alpine.start();
