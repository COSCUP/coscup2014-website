"use strict";

/*

請使用位於 window 的 PageHandler 事件來執行您需要的 Javascript

* pageload: 頁面內容 HTML 被載入，無論是新載入的網頁，還是 AJAX 動態更新內容。
* fullpageload: 頁面的樣式是桌面瀏覽器，或是
* deferpageload: 頁面的樣式是手機瀏覽器，如果之後視窗被放大到桌面瀏覽器的樣子則會有
* resumepageload: 頁面的樣式回到桌面瀏覽器
* pageunload: 頁面即將被 AJAX 動態替換

如果是 Header/footer/sidebar 的範圍的 Javascript 操作，則不需要使用 PageHandler 事件。

*/

/* AppCacheUI: https://github.com/timdream/appcacheui */
// Note: code removed

jQuery(function ($) {

  // FIXME: these code is not compatible with nav.empty
  // Initialize some components in mobile version
  function mobileInit() {
    var toggleMenu = function() {
      $("#mainNav").toggle();
      $("#menu-icon").toggleClass("active");
    };

    /* toggle nav */
    $("#menu-icon").on("click", toggleMenu);
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

  if (isMobile) {
    mobileInit();
  }

  // FIXME: CSS dependent test
  function isMobileLayout() {
    return false;
    //return !$('#title:visible').length;
  }

  // init: the PageHandler that trigger
  // pageload/fullpageload/deferpageload/resumepageload events
  var PageHandler = {
    // init
    init: function () {
      this._loadPage();

      // partial page update to href using XHR
      // we only do this in browsers with History API
      if (window.history.pushState) {
        // http://stackoverflow.com/questions/4688164/window-bind-popstate
        // Deal with popstate fire on first load
        // See also https://hacks.mozilla.org/2011/03/history-api-changes-in-firefox-4/
        // on difference between Safari 5 vs Fx4.
        var popped = ('state' in window.history),
          initialURL = window.location.href;

        $('a').on('click', function (ev) {
            // skip mid/right/cmd click
            if (ev.which == 2 || ev.metaKey) return true;
            // skip external links
            if (
              this.hostname !== window.location.hostname
              || !/2014/.test(this.pathname)
              || !(new RegExp(lang)).test(this.pathname.toLowerCase())
              || this.getAttribute('href').substr(0, 1) === '#'  // just a hash link
              || (/nocache/.test(this.getAttribute('rel')))
            ) return true;

            var href = this.href,
            samepage = (this.href === window.location.href);

            // Must be called before getPage() so relative links on the new page could be resolved properly
            history.pushState({'is':'pushed'}, '', href);

            // However, this.href will change for a relative link beyond this point
            PageHandler._getPage(href, samepage, true);

            // Given the fact we had pushed a new state,
            // the next popState event must not be initialPop even with initialURL.
            popped = true;

            return false;
          }
        );

        window.onpopstate = function (ev) {
          // Ignore inital popstate that some browsers fire on page load
          var initialPop = (!popped && window.location.href == initialURL);
          popped = true;
          if (initialPop) return;

          PageHandler._getPage(window.location.href, false, false);
        };
      }
    },
    // partial page update to href using XHR
    _getPage: function (href, samepage, resetScroll) {
      $(window).unbind('resize.defer');
      $(window).trigger('pageunload');
      if (this._xhr)
        this._xhr.abort();

      var $content = $('#content').addClass('loading');
      this._xhr = $.ajax(
        {
          url: href,
          dataType: 'html',
          complete: function (res, status) {
            if (status !== "success" && status !== "notmodified") {
              // error
              window.location.replace(href);
              return;
            }

            $content.removeClass('loading');
            if (resetScroll)
              $(window).scrollTop(0);

            var $h = $('<div />').append(
              res.responseText
              .match(/<body\b([^\u0000]+)<\/body>/)[0]
              .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
            );

            document.title = res.responseText.match(/<title>(.+)<\/title>/)[1];
            $('#content').html($h.find('#content').children()).removeClass('loading');

            if (!$h.find('nav').is('.empty')) {
              $('nav').html($h.find('nav').children());
            }

            if (window._gaq)
              _gaq.push(['_trackPageview']);

            var hash = window.location.hash;
            if (hash !== '') {
              if ($(hash).length > 0) {
                $(window).scrollTop($(hash).offset().top);
              }
            }

            PageHandler._loadPage(samepage);
          }
        }
      );
    },
    _xhr: undefined,
    // happens every time the HTML is updated
    _loadPage: function (samepage) {
      // trigger other functions to load on this page
      $(window).trigger('pageload', [samepage]);

      // Find out if we are currently on mobile layout
      // if so, defer/stop imagetile and iframe from loading
      // removing 'src' in <img> won't help so not doing it
      if (!isMobileLayout()) {
        // load desktop stuff
        this._fullLoad();
        return;
      }

      // mobile layout
      if (window._gaq)
        _gaq.push(['_trackEvent', 'Mobile 2014', window.location.href]);

      // Make sure we give back the desktop stuff if the user
      // had load the page in mobile layout but then resize the window
      // to full desktop
      $(window).bind(
        'resize.defer',
        function () {
          if (isMobileLayout())
            return;
          $(this).unbind('resize.defer');

          // load desktop stuff and stuff unloaded;
          PageHandler._resumeLoad();
          PageHandler._fullLoad();
        }
      );

      // unload stuff that is desktop only
      this._deferLoad();
    },
    _deferLoad: function () {
      $(window).trigger('deferpageload');
    },
    _resumeLoad: function () {
      $(window).trigger('resumepageload');
    },
    _fullLoad: function() {
      $(window).trigger('fullpageload');
    }
  };

  // init: Load navigation from API if it's empty
  // happen on sub-domain sites
  if ($('nav.empty').length) {
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

  // init: Load sponsors from API if it's empty (happen on sub-domain sites),
  // or if the page is loaded from AppCache
  // Note: code moved to sponsor.js

  // pageload: news widget
  // Note: code moved to widget.js

  // init: Analytics tracking for Sponsors
  // Note: code removed

  // init: CSS hover menu alternative for touch devices
  // Need to test on actual device
  $('nav > ul > li').bind(
    'touchstart',
    function () {
      var $this = $(this);
      $this.addClass('selected');
      $(document.body).bind(
        'touchend',
        function (ev) {
          $this.removeClass('selected');
          $(this).unbind(ev);
        }
      );
    }
  );

  // pageload: Put random selected mobile logo into header
  // mobileSponsorLogo() is also called by .sponsors.empty function
  // Note: code removed 

  // pageload: Put a shortcut to the current section on program page
  // Note: code removed

  // pageload: checkAppCache
  // if the browser tab has been opened for kAppCacheCheckingInterval
  // the next pageload will trigger AppCache check update
  // Note: code removed

  // fullpageload: social buzz on homepage #sidebar2
  // Note: code moved to widget.js

  // fullpageload: ipv6 block on homepage #sidebar2
  // Note: code removed

  // fullpageload: countdown on homepage #sidebar2
  // Note: code removed

  // deferpageload: hide iframe in .hideInMobile iframe
  // Note: code removed

  // pageload: the big program table on program page
  // Note: code removed

  // This is a cached HTML. Let's insert date as version.
  // Note: code removed

  // Start everything.
  PageHandler.init();

});
