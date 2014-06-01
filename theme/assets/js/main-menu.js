define(['jquery', 'context'], function($, context) {
  'use strict';

  var api_url = context.origin + context.api_path + '/menu/?callback=?';

  // init: Load navigation from API if it's empty
  function menuInit() {
    if (!$('nav.empty').length) { // menu is not empty
      return;
    }
    // Fetch site nav from remove JSON api
    $.getJSON(api_url, function(data) {
        var $nav = $('nav').removeClass('empty');
        $nav.html(data[context.lang].replace(/href="(\/[^\/])/g, 'href="' + context.origin + '$1'));
        $('nav a[href*="' + window.location.hostname + '"]').parent().addClass('current');
      }
    );
  }

  function initLanguageSetting() {
    if (!document.l10n) {
      return;
    }
    document.l10n.once(function() {
      document.l10n.requestLocales(context.lang);
    });
    $('#languages a').each(function() {
      $(this).click(function() {
        var lang = this.dataset.lang;
        document.l10n.requestLocales(lang);
      });
    });
  }

  function iniMobileMenu() {
    if (context.mobile) {
      var toggleMenu = function() {
        $("#mainNav").toggle();
        $("#mobile-menu-icon").toggleClass("active");
      };

      /* toggle nav */
      $("#mobile-menu-icon").on("click", toggleMenu);
      $("#mainNav li").on("click", toggleMenu);
    }
  }
  // start everything
  menuInit();
  iniMobileMenu();
  initLanguageSetting();

});
