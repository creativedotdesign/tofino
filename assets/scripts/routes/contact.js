export default {
  init() {
    // JavaScript to be fired on contact page page

    $('.contact-form').ajaxForm({
      beforeSerializeData: function() {
        console.log('Before data serialize callback function.');
      },
      afterSucess: function() {
        console.log('Success callback function.');
      },
      afterError: function() {
        console.log('Error callback function.');
      }
    });
  }
};
