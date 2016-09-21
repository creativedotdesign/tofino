export default {
  init() {
    console.log('Run COntact code');
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
  },
  loaded() {
    // Javascript to be fired on page once fully loaded
    console.log('Contact loaded!');
  }
};
