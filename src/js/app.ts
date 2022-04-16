// Import local deps
import scripts from './main';

// Import CSS
import '@/css/main.css';

let domResolve;
const domReady: Promise<void> = new Promise((resolve) => {
  domResolve = resolve;
});

// Trigger resolve when DOM Ready
document.addEventListener('DOMContentLoaded', domResolve);

// init app when ready
domReady.then(scripts.init).finally(scripts.finalize);

let appLoaded;
const appReady: Promise<void> = new Promise((resolve) => {
  appLoaded = resolve;
});

// Trigger resolve when loaded
window.addEventListener('load', appLoaded);

appReady.then(scripts.loaded);
