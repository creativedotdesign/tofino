/* ========================================================================
 * DOM-based Routing
 * Based on http://goo.gl/EUTi53 by Paul Irish
 *
 * Only fires on body classes that match. If a body class contains a dash,
 * replace the dash with an underscore when adding it to the object below.
 *
 * .noConflict()
 * The routing is enclosed within an anonymous function so that you can
 * always reference jQuery with $, even when in .noConflict() mode.
 *
 * ======================================================================== */
(function ($) {
  // Use this variable to set up the common and page specific functions. If you
  // rename this variable, you will also need to rename the namespace below.
  var tofino = {
    // JavaScript to be fired on all pages
    common: {
      init: function () {
        //Iniitalize svg4everybody
        svg4everybody();

        //List for notication close
        $('#tofino-notification .close').on('click', function () {
          Cookies.set('tofino-notification-closed', 'yes');
        });

        //Assign sticky
        var $sticky = $('.sticky');

        //Sticky polyfill for css position: sticky
        $sticky.Stickyfill();

        //Assign stick offset
        var stickyTop = $('.sticky').offset().top;

        $(window).scroll(function(){
          if ($(this).scrollTop() > stickyTop){
            $sticky.addClass('stuck');
            //$('body').addClass('menu-fixed');
          } else {
            $sticky.removeClass('stuck');
            //$('body').removeClass('menu-fixed');
          }
        });
      }
    },
    // Home page
    home: {
      init: function () {
      }
    },
  };
  // The routing fires all common scripts, followed by the page specific scripts.
  // Add additional events for more control over timing e.g. a finalize event
  var UTIL = {
    fire: function (func, funcname, args) {
      var namespace = tofino;
      funcname = funcname === undefined ? 'init' : funcname;
      if (func !== '' && namespace[func] && typeof namespace[func][funcname] === 'function') {
        namespace[func][funcname](args);
      }
    },
    loadEvents: function () {
      UTIL.fire('common');
      $.each(document.body.className.replace(/-/g, '_').split(/\s+/), function (i, classnm) {
        UTIL.fire(classnm);
      });
    }
  };
  $(document).ready(UTIL.loadEvents);
}(jQuery));  // Fully reference jQuery after this point.
