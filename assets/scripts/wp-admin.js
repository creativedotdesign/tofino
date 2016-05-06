import Cookies from 'js-cookie';

(function($) {
  $(document).ready(function() {
    $('.maintenance-mode-alert button').on('click', function() {
      Cookies.set('tofino_maintenance_alert_dismissed', 'true');
      $('.maintenance-mode-alert').fadeOut('fast');
    });
  });
}(jQuery));
