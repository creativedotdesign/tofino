// Uses CommonJS, AMD or browser globals to create a jQuery plugin.

(function (factory) {
  if (typeof define === 'function' && define.amd) {
    // AMD. Register as an anonymous module.
    define(['jquery'], factory);
  } else if (typeof module === 'object' && module.exports) {
    // Node/CommonJS
      module.exports = function( root, jQuery ) {
          if ( jQuery === undefined ) {
              // require('jQuery') returns a factory that requires window to
              // build a jQuery instance, we normalize how we use modules
              // that require this pattern but the window provided is a noop
              // if it's defined (how jquery works)
              if ( typeof window !== 'undefined' ) {
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
    $.fn.ajaxForm = function () { return true; };
}));

// var $ = window.jQuery;
//
// var request,
//     $form = $('.form-processor'),
//     $result = $('.js-form-result'),
//     $action = $form.attr('id'); // Set action as form id value
//
// $form.on('submit', function(e) {
//   e.preventDefault(); // Don't really submit.
//
//   if (request) { // If request exists, bail.
//     request.abort();
//   }
//
//   var serializedData = $(this).serialize();
//
//   $(this).find(':input').prop('disabled', true);
//   $(this).find(':submit').text('Sending').prop('disabled', true);
//
//   request = $.post(
//     tofinoJS.ajaxUrl, {
//       action: $action, //Passed to WP for the ajax action
//       data: serializedData,
//       nextNonce: tofinoJS.nextNonce
//     });
//
//   request.done(function(response, textStatus, errorThrown) { // eslint-disable-line
//     console.log(response);
//     if (response.success === true) {
//       $result.removeClass('alert-danger').addClass('alert alert-success').html(response.message);
//       $form.find(':input').val(''); // Reset fields.
//       $form.hide(); // Hide form
//       $form.find(':submit').text('Send'); // Set send button text back to default
//     } else {
//       $result.addClass('alert alert-danger').html(response.message);
//       $form.find(':input').prop('disabled', false); // Re-enable fields
//       $form.find(':submit').text('Send').prop('disabled', false); // Reset submit btn
//       //console.error("The following error occured: " + textStatus, errorThrown);
//
//       // Remove any existing failed validation classes
//       $form.find('.form-control-danger').removeClass('form-control-danger');
//       $form.find('.has-danger').removeClass('has-danger');
//
//       if (response.type === 'validation') {
//         console.log(response.extra);
//         var invalidFields = $.parseJSON(response.extra);
//         $.each(invalidFields, function(key) {
//           $form.find('[name=' + key + ']').addClass('form-control-danger');
//           $form.find('[name=' + key + ']').closest('.form-group').addClass('has-danger');
//         });
//       }
//     }
//   });
//
//   request.fail(function(response, textStatus, errorThrown) { // eslint-disable-line
//     console.log(response);
//     $result.addClass('alert alert-danger').html('An error occured.');
//     $form.find(':input').prop('disabled', false); // Re-enable fields
//     $form.find(':submit').text('Send').prop('disabled', false); // Reset submit btn
//     //console.error("The following error occured: " + textStatus, errorThrown);
//   });
// });
