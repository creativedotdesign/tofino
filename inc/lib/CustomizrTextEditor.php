<?php
if (!class_exists('WP_Customize_Control'))
	return null;

class Text_Editor_Custom_Control extends WP_Customize_Control {
    public $type = 'textarea';
    /**
    ** Render the content on the theme customizer page
    */
    public function render_content() { ?>
      <label>
        <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
        <?php
          $settings = array(
            'media_buttons' => false,
            'quicktags' => true,
            'teeny' => true,
            'wpautop' => false
          );

          $this->filter_editor_setting_link();

          wp_editor($this->value(), $this->id, $settings);
        ?>
      </label>
      <script>
      (function($) {
        wp.customizerCtrlEditor = {
          init: function() {
            $(window).load(function() {
              $('textarea.wp-editor-area').each(function() {
                var tArea = $(this),
                  id = tArea.attr('id'),
                  editor = tinyMCE.get(id),
                  setChange,
                  content;

                if (editor) {
                  editor.onChange.add(function (ed, e) {
                    ed.save();
                    content = editor.getContent();
                    clearTimeout(setChange);
                    setChange = setTimeout(function() {
                        tArea.val(content).trigger('change');
                    },500);
                  });
                }

                tArea.css({
                  visibility: 'visible'
                }).on('keyup', function() {
                  content = tArea.val();
                  clearTimeout(setChange);
                  setChange = setTimeout(function(){
                      content.trigger('change');
                  },500);
                });
              });
            });
          }
        };

        wp.customizerCtrlEditor.init();
      })(jQuery);
      </script><?php
    do_action('admin_footer');
    do_action('admin_print_footer_scripts');
  }

  private function filter_editor_setting_link() {
    add_filter('the_editor', function($output) {
      return preg_replace( '/<textarea/', '<textarea ' . $this->get_link(), $output, 1 );
    });
  }
}