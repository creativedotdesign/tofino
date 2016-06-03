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

    $(this).each(function() {

      var defaults = { //Defaults
        resposneDiv: '.js-form-result',
        action: $(this).attr('id'),
        method: '',
        btnProgressText: 'Wait..',
      };

      var opts = $.extend({}, defaults, options);

      var request,
          $form   = $(this),
          $result = $(opts.resposneDiv),
          $action = opts.action; // Set action as form id value

      if (request) { // If request exists, bail.
        request.abort();
      }

      opts.beforeSerializeData(); // Callback function

      var serializedData  = $(this).serialize(),
          $btnSubmit      = $(this).find(':submit'),
          btnOrgText      = $btnSubmit.text(), // Get original text value
          btnProgressText = opts.btnProgressText;

      $(this).find(':input').prop('disabled', true);
      $btnSubmit.text(btnProgressText).prop('disabled', true); // Set in progress text

      request = $.post(
        tofinoJS.ajaxUrl, {
          action: $action, // Passed to WP for the ajax action
          data: serializedData,
          nextNonce: tofinoJS.nextNonce
        }
      );

      request.done(function(response, textStatus, errorThrown) { // eslint-disable-line
        console.log(response);
        if (response.success === true) {
          $result.removeClass('alert-danger').addClass('alert alert-success').html(response.message);
          $form.find(':input').val(''); // Reset fields.
          $form.hide(); // Hide form
          $form.find(':submit').text(btnOrgText); // Set send button text back to default
        } else {
          $result.addClass('alert alert-danger').html(response.message);
          $form.find(':input').prop('disabled', false); // Re-enable fields
          $form.find(':submit').text(btnOrgText).prop('disabled', false); // Reset submit btn
          //console.error("The following error occured: " + textStatus, errorThrown);

          // Remove any existing failed validation classes
          $form.find('.form-control-danger').removeClass('form-control-danger');
          $form.find('.has-danger').removeClass('has-danger');

          if (response.type === 'validation') {
            console.log(response.extra);
            var invalidFields = $.parseJSON(response.extra);
            $.each(invalidFields, function(key) {
              $form.find('[name=' + key + ']').addClass('form-control-danger');
              $form.find('[name=' + key + ']').closest('.form-group').addClass('has-danger');
            });
          }
        }
      });

      request.fail(function(response, textStatus, errorThrown) { // eslint-disable-line
        console.log(response);
        $result.addClass('alert alert-danger').html('An error occured.');
        $form.find(':input').prop('disabled', false); // Re-enable fields
        $form.find(':submit').text(btnOrgText).prop('disabled', false); // Reset submit btn
        //console.error("The following error occured: " + textStatus, errorThrown);
      });

    });

  };
}));
