// We need jQuery
var $ = window.jQuery;

export default {
  init() {
    // JavaScript to be fired on contact page page
    $('.contact-form').ajaxForm({
      beforeSerializeData: function() { // Before data serialize callback
      },
      afterSucess: function() { // Success callback function.
      },
      afterError: function() { // Error callback function
      }
    });
  },
  loaded() {
    // Javascript to be fired on page once fully loaded
  }
};
