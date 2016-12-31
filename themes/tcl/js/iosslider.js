
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
    }
  };
})(jQuery, Drupal, drupalSettings);
