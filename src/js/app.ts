// Import local deps
import scripts from './main';

// Import CSS
import '@/css/main.css';

// DOM Ready
window.addEventListener('DOMContentLoaded', () => {
  scripts.init();
});

// Fully loaded
window.addEventListener('load', () => {
  scripts.loaded();
});
