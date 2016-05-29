var $ = window.jQuery;

var request,
    $form = $('.form-processor'),
    $result = $('.js-form-result'),
    $action = $form.attr('id'); // Set action as form id value

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
      action: $action, //Passed to WP for the ajax action
      data: serializedData,
      nextNonce: tofinoJS.nextNonce
    });

  request.done(function(response, textStatus, errorThrown) { // eslint-disable-line
    console.log(response);
    if (response.success === true) {
      $result.removeClass('alert-danger').addClass('alert alert-success').html(response.message);
      $form.find(':input').val(''); // Reset fields.
      $form.hide(); // Hide form
      $form.find(':submit').text('Send'); // Set send button text back to default
    } else {
      $result.addClass('alert alert-danger').html(response.message);
      $form.find(':input').prop('disabled', false); // Re-enable fields
      $form.find(':submit').text('Send').prop('disabled', false); // Reset submit btn
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
    $form.find(':submit').text('Send').prop('disabled', false); // Reset submit btn
    //console.error("The following error occured: " + textStatus, errorThrown);
  });
});
