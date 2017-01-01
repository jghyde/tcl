
(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.tcl = {
    attach: function (context, settings) {
      $('.iosslider').iosSlider({
        startAtSlide: 2,
        keyboardControls: true,
        autoSlideHoverPause: true,
        infiniteSlider: true,
        desktopClickDrag: true,
        navPrevSelector: "#prev",
        navNextSelector: "#next",
        autoSlide: true,
        elasticPullResistance: 0.4
      });
      $('nav#block-topnav h2.visually-hidden' ).click(function() {
        if ($('nav#block-topnav ul.menu.nav').css('display') == 'none') {
          $('nav#block-topnav ul.menu.nav').show(500);
        }
        else {
          $('nav#block-topnav ul.menu.nav').hide(500);
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);