// Uses CommonJS, AMD or browser globals to create a jQuery plugin.
(function (factory) {
  if (typeof define === 'function' && define.amd) {
    // AMD. Register as an anonymous module.
    define(['jquery'], factory);
  } else if (typeof module === 'object' && module.exports) {
    // Node/CommonJS
    module.exports = function(root, jQuery) {
      if (jQuery === undefined) {
        if (typeof window !== 'undefined') {
          jQuery = require('jquery');
        }
        else {
          jQuery = require('jquery')(root);
        }
      }
      factory(jQuery);
      return jQuery;
    };
  } else {
    // Browser globals
    factory(jQuery);
  }
}(function ($) {
  $.fn.ajaxForm = function (options) {

    $(this).each(function () {
      var request;

      $(this).on('submit', function(e) {

        e.preventDefault(); // Don't really submit.

        var defaults = { //Defaults
          responseDiv: '.js-form-result',
          action: $(this).data('wp-action'), //The PHP function name to call via AJAX
          btnProgressText: 'Wait..',
          beforeSerializeData: function() {},
          beforeRedirect: function() {},
          afterSuccess: function() {}
        };

        var opts = $.extend({}, defaults, options);

        opts.responseDiv = $(opts.responseDiv);

        // var request,
        var $form = $(this);

        if (request) { // If request exists, bail.
          request.abort();
        }

        opts.beforeSerializeData(); // Callback function

        var serializedData  = $(this).serialize(),
            $btnSubmit      = $(this).find(':submit'),
            btnOrgText      = $btnSubmit.text(), // Get original text value
            btnProgressText = opts.btnProgressText;

        $(this).find(':input').prop('disabled', true); // Set the disabled state
        $btnSubmit.text(btnProgressText); // Set in progress text

        request = $.post(
          tofinoJS.ajaxUrl, {
            action: opts.action, // Passed to WP for the ajax action
            data: serializedData,
            nextNonce: tofinoJS.nextNonce
          }
        );

        request.done(function(response, textStatus, errorThrown) { // eslint-disable-line
          if (response.success === true) {
            if (response.redirect) {
              opts.beforeRedirect(); // Callback function
              window.location = response.redirect;
              return false;
            }

            opts.responseDiv.removeClass('alert-danger').addClass('alert alert-success').html(response.message);
            $form.find(':input').val(''); // Reset fields.
            $form.find(':submit').text(btnOrgText); // Set send button text back to default
            $form.hide(); // Hide form

            opts.afterSuccess(); // Callback function
          } else {
            opts.responseDiv.addClass('alert alert-danger').html(response.message);
            $form.find(':input').prop('disabled', false); // Re-enable fields
            $form.find(':submit').text(btnOrgText); // Reset submit btn to org text

            // Remove any existing failed validation classes
            $form.find('.form-control-danger').removeClass('form-control-danger');
            $form.find('.has-danger').removeClass('has-danger');

            var inputs = $form.find(':input')
              .not(':input[type=button], :input[type=submit], :input[type=reset]'); // Select all inputs not buttons

            inputs.addClass('form-control-success'); // All valid / green. Server only returns invalid fields

            $(inputs).each(function() {
              $(this).closest('.form-group').addClass('has-success');
            });

            if (response.type === 'validation') {
              var invalidFields = $.parseJSON(response.extra);
              $.each(invalidFields, function(key) {
                $form.find('[name=' + key + ']').removeClass('form-control-success').addClass('form-control-danger')
                  .closest('.form-group').removeClass('has-success').addClass('has-danger');
              });
            }
          }
        });

        request.fail(function(response, textStatus, errorThrown) { // eslint-disable-line
          opts.responseDiv.addClass('alert alert-danger').html('An error occured.');
          $form.find(':input').prop('disabled', false); // Re-enable fields
          $form.find(':submit').text(btnOrgText); // Reset submit btn
        });
      });
    });
  };
}));
