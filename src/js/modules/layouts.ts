export const acfLayouts = () => {
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
      const field = acf.getField('field_content_modules');

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

  if (
    document.querySelector(
      '.wp-admin.acf-admin-page.acf-admin-field-groups.auto-generate-page-modules'
    )
  ) {
    const rows = document.querySelectorAll('#posts-filter #the-list .row-title');

    if (rows) {
      rows.forEach((elem) => {
        if (elem.textContent && elem.textContent.includes('__Page Modules')) {
          // Find the parent row
          const parentRow = elem.closest('tr');

          (parentRow as HTMLElement).style.display = 'none';

          return;
        }
      });
    }
  }
};
