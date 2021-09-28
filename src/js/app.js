// Import local deps
import scripts from './main';

let domResolve;
const domReady = new Promise(resolve => {
  domResolve = resolve;
});

// Trigger resolve when DOM Ready
document.addEventListener('DOMContentLoaded', domResolve);

// init app when ready
domReady.then(scripts.init).finally(scripts.finalize);

let appLoaded;
const appReady = new Promise(resolve => {
  appLoaded = resolve;
});

// Trigger resolve when loaded
window.addEventListener('load', appLoaded);

appReady.then(scripts.loaded);
