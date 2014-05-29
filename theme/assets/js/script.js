"use strict";

jQuery(function ($) {
  // init: Load navigation from API if it's empty
  function menuInit() {
    if (!$('nav.empty').length) { // menu is not empty
      return;
    }
    // Fetch site nav from remove JSON api
    $.getJSON(
      rootURL + '/api/menu/?callback=?',
      function (data) {
        var $nav = $('nav').removeClass('empty');
        $nav.html(data[lang].replace(/href="(\/[^\/])/g, 'href="http://coscup.org$1'));
        $('nav a[href*="' + window.location.hostname + '"]').parent().addClass('current');
      }
    );
  }

  // FIXME: these code is not compatible with nav.empty
  // Initialize some components in mobile version
  function mobileInit() {
    var toggleMenu = function() {
      $("#mainNav").toggle();
      $("#mobile-menu-icon").toggleClass("active");
    };

    /* toggle nav */
    $("#mobile-menu-icon").on("click", toggleMenu);
    $("#mainNav li").on("click", toggleMenu);

    function initSponsor() {
      Swipe(document.getElementById('mySwipe'), {
        auto: 3000
      });
    }

    if ($('#mySwipe .swipe-wrap').length) {
      initSponsor();
    } else {
      $(window).bind('mobile-sponsor-ready', initSponsor);
    }
  }

  // start everything
  menuInit();
  if (isMobile) {
    mobileInit();
  }

});
