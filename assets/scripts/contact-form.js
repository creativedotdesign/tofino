(function($) {
  var request,
      $form = $('.contact-form'),
      $result = $('.js-form-result');

  $form.on('submit', function(e) {
    e.preventDefault(); // Don't really submit.

    if (request) { // If request exists, bail.
      request.abort();
    }

    var serializedData = $(this).serialize();

    $(this).find(':input').prop('disabled', true);
    $(this).find(':submit').text('Sending').prop('disabled', true);

    request = $.post(
      tofinoJS.ajaxUrl, {
        action: 'contact-form',
        data: serializedData,
        nextNonce: tofinoJS.nextNonce
      });

    request.done(function(response, textStatus, errorThrown) { // eslint-disable-line
      //console.log(response);
      if (response.success === true) {
        $result.addClass('success').html(response.message);
        $form.find(':input').val(''); // Reset fields.
        $form.hide(); // Hide form
      } else {
        $result.addClass('failed').html(response.message);
        $form.find(':input').prop('disabled', false); // Re-enable fields
        $form.find(':submit').text('Send').prop('disabled', false); // Reset submit btn
        //console.error("The following error occured: " + textStatus, errorThrown);
      }
    });

    request.fail(function(response, textStatus, errorThrown) { // eslint-disable-line
      //console.log(response);
      $result.addClass('failed').html('An error occured.');
      $form.find(':input').prop('disabled', false); // Re-enable fields
      $form.find(':submit').text('Send').prop('disabled', false); // Reset submit btn
      //console.error("The following error occured: " + textStatus, errorThrown);
    });
  });
}(jQuery));
