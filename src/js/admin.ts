// Import CSS
import '@/css/base/admin.css';

document.addEventListener('DOMContentLoaded', () => {
  if (document.querySelector('.maintenance-mode-alert')) {
    const button: HTMLElement | null = document.querySelector('.maintenance-mode-alert button');

    if (button) {
      button.addEventListener('click', () => {
        const date = new Date();

        date.setTime(date.getTime() + 1 * 24 * 60 * 60 * 1000);

        const expires = 'expires=' + date.toUTCString();

        document.cookie = 'tofino_maintenance_alert_dismissed=true;' + expires + '; path=/';

        const alert: HTMLElement | null = document.querySelector('.maintenance-mode-alert');

        if (alert) {
          // Hide the alert
          alert.style.display = 'none';
        }
      });
    }
  }

  // Check if we are on a post edit screen
  if (document.querySelector('.wp-admin form#post')) {
    const layouts = acf.getFields({
      name: 'page_template',
    });

    let selectedLayout = [];

    if (layouts.length > 0) {
      const layoutField = layouts[0];

      layoutField.on('change', () => {
        const selected = layoutField.val();

        if (selected) {
          selectedLayout = JSON.parse(selected); // Convert to string array to JS array
        }
      });
    }

    const addLayoutbtn = document.querySelector(
      '.acf-field-acfe-button[data-name="update_layout"]'
    );

    if (addLayoutbtn) {
      addLayoutbtn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();

        addLayoutsToContentModules(selectedLayout);
      });
    }

    const addLayoutsToContentModules = (layouts: string[]) => {
      const field = acf.getField('field_62586c9af1a1a');

      // Get all current layouts
      const currentLayouts = field.$layouts();

      if (currentLayouts.length > 0) {
        // Remove all current layouts (jQuery each)
        currentLayouts.each((index: number, layoutElement: HTMLElement) => {
          layoutElement.remove();
        });
      }

      if (field) {
        layouts.forEach((layout) => {
          field.add({
            layout: layout,
          });
        });

        field.showNotice({
          text: 'Pre-defined modules successfully added to the content area.',
          type: 'success',
          dismiss: true,
        });

        // Hide the notice after 5 seconds
        setTimeout(() => {
          field.removeNotice();
        }, 4000);
      }
    };
  }
});
