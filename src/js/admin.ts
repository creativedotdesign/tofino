import '@/css/base/admin.css';
import { acfLayouts } from '@/js/modules/layouts';
import { maintenanceMode } from '@/js/modules/maintenanceMode';
import { acfAutosize } from '@/js/modules/acfAutosize';

document.addEventListener('DOMContentLoaded', () => {
  acfLayouts();
  maintenanceMode();
  acfAutosize();
});
