// We need jQuery
var $ = window.jQuery;

export default {
  init() {
    // JavaScript to be fired on contact page page
    $('.contact-form').ajaxForm();
  },
  loaded() {
    // Javascript to be fired on page once fully loaded
  }
};
