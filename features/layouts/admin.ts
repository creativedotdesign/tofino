/**
 * Initialises ACF layout utilities on the WordPress admin post edit screen.
 * Wires up a page-template selector field to an "Update Layout" button that
 * bulk-replaces content module rows with the chosen pre-defined layout.
 *
 * @returns void
 */
const layouts = (): void => {
  if (!document.querySelector('.wp-admin form#post')) {
    return;
  }

  const pageTemplateFields = acf.getFields({
    name: 'page_template',
  });

  let selectedLayout: string[] = [];

  if (pageTemplateFields.length > 0) {
    const layoutField = pageTemplateFields[0];
    const selected = layoutField.val();

    selectedLayout = selected ? JSON.parse(selected) : [];

    layoutField.on('change', () => {
      const nextSelected = layoutField.val();

      // Reset when the empty option is chosen; otherwise parse the JSON array.
      selectedLayout = nextSelected ? JSON.parse(nextSelected) : [];
    });
  }

  /**
   * Replaces all existing content module rows with a new set of ACF layouts.
   *
   * @param layoutNames - An array of ACF layout names to add to the content modules field.
   * @returns void
   */
  const addLayoutsToContentModules = (layoutNames: string[]): void => {
    const field = acf.getField('field_62586c9af1a1a');

    if (!field) {
      return;
    }

    field.$layouts().each((_index: number, layoutElement: HTMLElement) => {
      layoutElement.remove();
    });

    layoutNames.forEach((layout) => {
      field.add({ layout });
    });

    field.showNotice({
      text: 'Pre-defined modules successfully added to the content area.',
      type: 'success',
      dismiss: true,
    });

    setTimeout(() => {
      field.removeNotice();
    }, 4000);
  };

  const addLayoutButton = document.querySelector<HTMLElement>(
    '.acf-field-acfe-button[data-name="update_layout"] button, .acf-field-acfe-button[data-name="update_layout"] .acfe-button',
  );

  addLayoutButton?.addEventListener('click', (event) => {
    event.preventDefault();
    event.stopPropagation();

    addLayoutsToContentModules(selectedLayout);
  });
};

export default layouts;
