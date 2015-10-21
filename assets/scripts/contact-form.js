(function($) {
  var request,
      $form = $('#contact-form'),
      $result_box = $form.find('.js-form-result');

  $form.on('submit', function(e) {
    e.preventDefault(); // Don't really submit.

    if (request) { // If request exists, bail.
      request.abort();
    }

    var serializedData = $(this).serialize();

    $(this).find(":input").prop("disabled", true);
    $(this).find(':submit').text("Sending").prop("disabled", true);

    request = $.post(
      tofinoAjax.ajaxUrl, {
        action: 'contact-form',
        data: serializedData,
        nextNonce: tofinoAjax.nextNonce
    });

    request.done(function(response, textStatus, errorThrown) {
      $form.find(":input").text('Send').prop('disabled', false);
      if (response.success === true) {
        $form.find(':input').val(''); // Reset fields.
        $result_box.addClass('success').html(response.message);
      } else {
        $result_box.addClass('failed').html(response.message);
        //console.error("The following error occured: " + textStatus, errorThrown);
      }
    });

    request.fail(function(response, textStatus, errorThrown) {
      $result_box.addClass('failed').html('An error occured.');
      //console.error("The following error occured: " + textStatus, errorThrown);
    });
  });
}(jQuery));
