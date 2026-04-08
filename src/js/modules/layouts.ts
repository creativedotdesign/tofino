/**
 * Initialises ACF layout utilities on the WordPress admin post edit screen.
 * Wires up a page-template selector field to an "Update Layout" button that
 * bulk-replaces content module rows with the chosen pre-defined layout.
 * Also hides internal __Page Modules rows from the ACF field group list screen.
 *
 * @returns void
 */
export const acfLayouts = () => {
  // Check if we are on a post edit screen
  if (document.querySelector('.wp-admin form#post')) {
    const layouts = acf.getFields({
      name: 'page_template',
    });

    let selectedLayout: string[] = [];

    if (layouts.length > 0) {
      const layoutField = layouts[0];

      layoutField.on('change', () => {
        const selected = layoutField.val();

        // Reset when the empty option is chosen; otherwise parse the JSON array
        selectedLayout = selected ? JSON.parse(selected) : [];
      });
    }

    /**
     * Replaces all existing content module rows with a new set of ACF layouts.
     *
     * @param layouts - An array of ACF layout names to add to the content modules field.
     * @returns void
     */
    const addLayoutsToContentModules = (layouts: string[]) => {
      const fieldKey = document.querySelector('.auto-generate-page-modules')
        ? 'field_content_modules'
        : 'field_62586c9af1a1a';

      const field = acf.getField(fieldKey);

      if (!field) {
        return;
      }

      // Remove all current layout rows
      field.$layouts().each((_index: number, layoutElement: HTMLElement) => {
        layoutElement.remove();
      });

      layouts.forEach((layout) => {
        field.add({ layout });
      });

      field.showNotice({
        text: 'Pre-defined modules successfully added to the content area.',
        type: 'success',
        dismiss: true,
      });

      // Dismiss the notice after 4 seconds
      setTimeout(() => {
        field.removeNotice();
      }, 4000);
    };

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
  }

  if (
    document.querySelector(
      '.wp-admin.acf-admin-page.acf-admin-field-groups.auto-generate-page-modules'
    )
  ) {
    document.querySelectorAll('#posts-filter #the-list .row-title').forEach((elem) => {
      if (elem.textContent?.includes('__Page Modules')) {
        elem.closest('tr')?.setAttribute('style', 'display: none');
      }
    });
  }
};
