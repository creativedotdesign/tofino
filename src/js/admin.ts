import '@/css/base/admin.css'; // Import CSS
import { acfLayouts } from '@/js/modules/layouts';
import { maintenanceMode } from '@/js/modules/maintenanceMode';

document.addEventListener('DOMContentLoaded', () => {
  acfLayouts(); // Run ACF Layouts
  maintenanceMode(); // Run Maintenance Mode
});
