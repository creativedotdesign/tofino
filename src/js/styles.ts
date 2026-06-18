// Front-end stylesheet entry.
//
// Kept separate from js/app.ts so the site-wide CSS can be loaded — or
// suppressed — independently of the front-end JS, in BOTH dev and production.
// A child theme that owns the single site-wide Tailwind build returns false
// from the `tofino/use_vite_css` filter (keyed on 'js/app.ts'); Vite::use_vite()
// then simply doesn't enqueue this entry, so the parent's CSS never loads —
// previously impossible in dev, where Vite injects a JS-imported stylesheet
// over HMR regardless of any PHP-side enqueue gate.
//
// In production this entry's extracted CSS is enqueued as a <link>; in dev Vite
// injects it when this module executes.
import '@/css/app.css';
import.meta.glob('../../features/*/style.css', { eager: true });
import.meta.glob('../../modules/*/style.css', { eager: true });
