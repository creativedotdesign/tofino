import '@/css/admin.css';
import.meta.glob('../../features/*/admin.css', { eager: true });
import { acfAutosize } from '@/js/admin/acfAutosize';
import { adminFeatureScripts } from '@/js/core/themeAssets';

document.addEventListener('DOMContentLoaded', () => {
  acfAutosize();

  Object.values(adminFeatureScripts).forEach((load) => {
    load().then((mod) => mod.default?.());
  });
});
