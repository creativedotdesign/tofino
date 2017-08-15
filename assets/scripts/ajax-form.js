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
    var $submitActor = null;
    var $submitActors = $(this).find(':submit'); // All submit buttons in form

    $(this).on('submit', function(e) {
      e.preventDefault(); // Don't really submit.

      if (null === $submitActor) {
        // If no actor is explicitly clicked, the browser will
        // automatically choose the first in source-order
        // so we do the same here
        $submitActor = $submitActors[0];
      }

      if ($(this).hasClass('submitting')) {
        return false;
      }

      $(this).addClass('submitting');

      var defaults = { // Defaults
        responseDiv: '.js-form-result',
        action: $(this).data('wp-action'), // The PHP function name to call via AJAX
        btnProgressText: 'Wait..',
        hideFormAfterSucess: true,
        beforeSerializeData: function() {},
        beforeRedirect: function() {},
        afterSuccess: function() {},
        afterError: function() {}
      };

      var opts = $.extend({}, defaults, options);

      opts.responseDiv = $(opts.responseDiv);

      var $form = $(this);

      opts.beforeSerializeData(); // Callback function

      var serializedData  = $(this).serialize(),
          $btnSubmit      = $submitActor,
          btnOrgText      = $btnSubmit.text(), // Get original text value
          btnProgressText = opts.btnProgressText;

      $(this).find(':input').prop('disabled', true); // Set the disabled state
      $btnSubmit.text(btnProgressText); // Set in progress text

      var request = $.post(
        tofinoJS.ajaxUrl, {
          action: opts.action, // Passed to WP for the ajax action
          data: serializedData,
          nextNonce: tofinoJS.nextNonce
        }
      );

      request.done(function(response) {
        $form.removeClass('submitting');
        if (response.success === true) {
          if (response.redirect) {
            opts.beforeRedirect(); // Callback function
            window.location = response.redirect;
            return false;
          }

          opts.responseDiv
            .removeClass('alert-danger')
            .addClass('alert alert-success')
            .html(response.message);

          $form.find(':input').val(''); // Reset fields.
          $submitActor.text(btnOrgText); // Set send button text back to default

          if (opts.hideFormAfterSucess === true) {
            $form.hide(); // Hide form
          } else {
            $form.find(':input').prop('disabled', false); // Re-enable fields
          }

          opts.afterSuccess(); // Callback function
        } else {
          opts.responseDiv
            .addClass('alert alert-danger')
            .html(response.message);

          $form.find(':input').prop('disabled', false); // Re-enable fields
          $submitActor.text(btnOrgText); // Reset submit btn to org text

          // Remove any existing failed validation classes
          $form.find('.form-control-danger').removeClass('form-control-danger');
          $form.find('.has-danger').removeClass('has-danger');

          $form.find(':input')
            .not(':input[type=button], :input[type=submit], :input[type=reset], :checkbox') // Select all inputs not buttons not checkbox
            .addClass('is-valid') // All valid / green. Server only returns invalid fields
            .each(function() {
              $(this).closest('.form-group').addClass('has-success');
            });

          $form.find(':checkbox').closest('.checkbox').addClass('has-success');

          if (response.type === 'validation') {
            var invalidFields = $.parseJSON(response.extra);
            var msgHTML = '';

            $.each(invalidFields, function(key, value) {
              msgHTML += '<li>' + value + '</li>';

              $form.find('[name=' + key + ']')
                .removeClass('form-control-success')
                .removeClass('is-valid') // For checkboxes
                .addClass('is-invalid');

              $form.find('[name=' + key + ']')
                .removeClass('is-valid')
                .addClass('is-invalid');

              if ($('[name=' + key + ']').is(':checkbox')) {
                $('[name=' + key + ']')
                  .closest('.checkbox')
                  .removeClass('has-success')
                  .addClass('has-danger');
              }
            });

            opts.responseDiv.append('<ul>' + msgHTML + '</ul>');
          }

          opts.afterError(); // Callback function
        }
      });

      request.fail(function() {
        opts.responseDiv
          .addClass('alert alert-danger')
          .html('An error occured.');
        $form.find(':input').prop('disabled', false); // Re-enable fields
        $submitActor.text(btnOrgText); // Reset submit btn
        $form.removeClass('submitting');
      });
    });

    $submitActors.click(function() { // Assign button on click
      $submitActor = $(this);
    });
  };
}));
