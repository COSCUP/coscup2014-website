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
(function(){var e={b:function(){var a=document,b=a.body,d=window.applicationCache;if(d)if(b){this.info=a.getElementById("appcache_info");if(!this.info){a.a=a.createElement;var c=a.a("a"),a=a.a("div");a.id="appcache_info";c.href="";c.addEventListener("click",function(a){a.stopPropagation();window.location.reload()},!0);a.appendChild(c);b.firstChild&&b.insertBefore(a,b.firstChild);this.info=a}"checking downloading progress noupdate cached updateready obsolete error".split(" ").forEach(function(a){d.addEventListener(a,
e)})}else console.log("Premature init. Put the <script> in <body>.")},handleEvent:function(a){this.info.className=this.info.className.replace(/ ?appcache\-.+\b/g,"")+" appcache-"+a.type;this.count=this.count||0;"progress"===a.type&&(this.count++,this.info.setAttribute("data-progress",a.total?a.loaded+1+"/"+a.total:this.count))}};e.b()})();

jQuery(function ($) {

  var lang = ($('html').attr('lang') || 'zh-TW').toLowerCase();
  var rootURL = 'http://coscup.org/2013';
  var themeURL = 'http://coscup.org/2014-theme';

  function mobileInit() {
    /* prepend menu icon */
    $('#nav-wrap').prepend('<div id="menu-icon">menu</div>');
    var toggleMenu = function() {
      console.log("toggleMenu!!!");
      $("#mainNav").toggle();
      $("#menu-icon").toggleClass("active");
    };

    /* toggle nav */
    $("#menu-icon").on("click", toggleMenu);
    $("#mainNav li").on("click", toggleMenu);

    var elem = document.getElementById('mySwipe');
    window.mySwipe = Swipe(elem, {
      auto: 3000
    });
  }

  var isMobile = ($('#mySwipe').css('display') === "block" )? true : false;
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
          initialURL = location.href;

        $('a').live(
          'click',
          function (ev) {
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

            $(this).parent('#nav li').addClass('loading');

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
          var initialPop = (!popped && location.href == initialURL);
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

            if (!$h.find('#nav').is('.empty')) {
              $('#nav').html($h.find('#nav').children());
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
  if ($('#nav.empty').length) {
    // Fetch site nav from remove JSON api
    $.getJSON(
      rootURL + '/api/menu/?callback=?',
      function (data) {
        var $nav = $('#nav').removeClass('empty');
        $nav.html(data[lang].replace(/href="(\/[^\/])/g, 'href="http://coscup.org$1'));
        $('#nav a[href*="' + window.location.hostname + '"]').parent().addClass('current');
      }
    );
  }

  // init: Load sponsors from API if it's empty (happen on sub-domain sites),
  // or if the page is loaded from AppCache
  if ($('#sponsor.empty').length ||
    (window.applicationCache && window.applicationCache.status !== 0)) {
    // Fetch sponsors from remove JSON api
    $.getJSON(
      rootURL + '/api/sponsors/?callback=?',
      function (data) {
        var $sponsors = $('#sponsor').removeClass('empty');
        var $mobileSponsors = null;
        if ($('#mySwipe.empty').length) {
          $mobileSponsors = $('#mySwipe.empty').removeClass('empty')
        }
        var titles = (
          {
            'en' : {
              'diamond' : 'Diamond Level Sponsors',
              'gold' : 'Gold Level Sponsors',
              'silver' : 'Silver Level Sponsors',
              'bronze' : 'Bronze Level Sponsors',
              'cohost' : 'Co-host',
              'media' : 'Media Partners'
            },
            'zh-tw' : {
              'diamond' : '鑽石級贊助',
              'gold' : '黃金級贊助',
              'silver' : '白銀級贊助',
              'bronze' : '青銅級贊助',
              'cohost' : '協辦單位',
              'media' : '媒體夥伴'
            },
            'zh-cn' : {
              'diamond' : '钻石级赞助',
              'gold' : '黄金级赞助',
              'silver' : '白银级赞助',
              'bronze' : '青铜级赞助',
              'cohost' : '协办单位',
              'media' : '媒体伙伴'
            }
          }
        )[lang];

        // Save existing nodes
        var $existingSponsors = $sponsors.children();
        console.log($existingSponsors);
        $sponsors.empty();

        $.each(
          [
            'diamond',
            'gold',
            'silver',
            'bronze',
            'cohost',
            'media'
          ],
          function (i, level) {
            if (!data['sponsors'][level]) return;
            $sponsors.append('<h2>' + titles[level] + '</h2>');
            var $u = $('<ul class="' + level + '" />');
            $.each(
              data['sponsors'][level],
              function (i, sponsor) {
                // Assume that there is no special chars to escape
                $u.append(
                  '<li><a href="' + sponsor.url + '" target="_blank">'
                  + '<img title="' + sponsor.name[lang] + '" src="' + sponsor.logoUrl + '" />'
                  + '</a></li>'
                );
              }
            );
            $sponsors.append($u);
          }
        );
        // Restore existing sponsors
        $sponsors.append($existingSponsors);

        if ($mobileSponsors) {
          var $allSponsors = [];
          $.each(
            [
              'diamond',
              'gold',
              'silver',
              'bronze',
              'media'
            ],
            function (i, level) {
              if (!data['sponsors'][level]) return;
              $.each(
                data['sponsors'][level],
                function (i, sponsor) {
                  $allSponsors.push(sponsor);
                }
              );
            }
          );
          $mobileSponsors.append("<div class='swipe-wrap'>");
          var $wrap = $mobileSponsors.children(0);
          for (var j = 0; j < $allSponsors.length; j += 2) {
            var sponsor1 = $allSponsors[j];
            var sponsor2 = $allSponsors[j+1];
            if (!sponsor2) {
              sponsor2 = { url: '#', name: { 'zh-tw': '' }, logoUrl: '' };
            }
            $wrap.append(
              '<div><span>'
                + '<a href="' + sponsor1.url + '" target="_blank" title="' + sponsor1.name[lang] + '">'
                + '<img alt="' + sponsor1.name[lang] + '" src="' + sponsor1.logoUrl + '" width="40%"/></a>'
                + '<a href="' + sponsor2.url + '" target="_blank" title="' + sponsor2.name[lang] + '">'
                + '<img alt="' + sponsor2.name[lang] + '" src="' + sponsor2.logoUrl + '" width="40%"/></a>'
                + '</span></div>');
          }
          window.mySwipe = Swipe($mobileSponsors.get(0), {
            auto: 3000,
          });
        }
      }
    );
  }

  function loadNewsWidget() {
    if ($('.news > .news_list.empty').length ||
        (window.applicationCache && window.applicationCache.status !== 0)) {
      $.getJSON(
        rootURL + '/api/news/?callback=?',
        function (data) {
          var $news_list = $('.news > .news_list.empty').removeClass('empty');
          $.each(
            data['news'].slice(0, 3),
            function (i, news) {
              $news_list.append(
                '<div class="list"><span>' + news.date + '<b>' + news.source + '</b></span>' +
                  '<a href="' + news.link + '" target="_blank">' +
                  '<div class="title">' + news.title+ '</div></a></div>'
              );
            });
        });
    }
  }
  $(window).bind('pageload', loadNewsWidget);

  // init: Analytics tracking for Sponsors
  $('.sponsors a, #mobileSponsorLogo a').live(
    'click',
    function () {
      if (window._gaq) _gaq.push(['_trackEvent', 'Sponsors 2014', this.href]);
      return true;
    }
  );
  $('#mobileSponsorLogo a').live(
    'click',
    function () {
      if (window._gaq) _gaq.push(['_trackEvent', 'Sponsors 2014 (Mobile only)', this.href]);
      return true;
    }
  );

  // init: CSS hover menu alternative for touch devices
  // Need to test on actual device
  $('#nav > ul > li').bind(
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
  function mobileSponsorLogo() {
    var pool = [],
    multi = {
      diamond: 10,
      gold: 5,
      silver: 2,
      bronze: 1
    };

    $('#mobileSponsorLogo').remove();

    $.each(
      multi,
      function (level, m) {
        $('#sidebar > .sponsors .' + level + ' a').each(
          function () {
            var i = m;
            while (i--) {
              pool.push(this);
            }
          }
        );
      }
    );

    if (pool.length === 0) return;

    $('#nav').after(
      $('<p id="mobileSponsorLogo" />').append(
        $(pool[Math.floor(pool.length * Math.random())]).clone()
      )
    );
  }
  $(window).bind('pageload', mobileSponsorLogo);

  // pageload: Put a shortcut to the current section on program page
  function currentSessionShortcut() {
    // Program page only
    if (!$('.shortcuts').length) return;

    // Not running after the event
    if (
      (new Date()).getTime()
      > parseInt($('.program tbody th:last').attr('rel') + '000', 10)
    ) return;

    var $a = $('<a href="#" />').text(
      {
        'zh-tw': '目前議程',
        'zh-cn': '目前议程',
        'en': 'Current Session'
      }[lang]
    );


    $a.bind(
      'click',
      function (ev) {
        var ct = (new Date()).getTime(),
        target;
        ev.stopPropagation();
        ev.preventDefault();

        $('.program tbody th').each(
          function (i, el) {
            var ot = parseInt($(el).attr('rel') + '000', 10);
            if (ct < ot && target) {
              $(document.body).animate(
                {
                  'scrollTop': $(target).offset().top - 20
                },
                180
              );
              $(window).trigger('scroll');
              return false;
            }
            if (ct > ot) {
              target = el;
            }
          }
        );
        if (!target) {
          $(document.body).animate(
            {
              'scrollTop': $('.program tbody th:first').offset().top - 20
            },
            180
          );
          $(window).trigger('scroll');
        }
      }
    );

    $('.shortcuts').append($('<li class="fullwidth" />').append($a));

    // back button

    $('.shortcuts').after(
      '<a class="mobile_top" href="#"></a>'
    );

    var $mobile_top = $('.mobile_top'),
    mtTimer;

    $mobile_top.bind(
      'click',
      function () {
        $(window).trigger('scroll');
        $(document.body).animate(
          {
            'scrollTop': $('.shortcuts').offset().top - 20
          },
          180
        );
        return false;
      }
    );


    $(window).bind(
      'resize.mt scroll.mt',
      function () {
        $mobile_top.removeClass('show');
        clearTimeout(mtTimer);
        mtTimer = setTimeout(
          function () {
            if ($('.shortcuts').offset().top < $(document.body).scrollTop()) {
              $mobile_top.css(
                'top',
                $(document.body).scrollTop()
                + (window.innerHeight || $(window).height()) // innerHeight reports the correct viewpoint height in iPhone
                - $mobile_top.height()
                - 15
              ).addClass('show');
            }
          },
          200
        );
      }
    );
    $(window).one(
      'pageunload',
      function (ev) {
        clearTimeout(mtTimer);
        $(window).unbind('resize.mt scroll.mt');
      }
    );
  }
  $(window).bind('pageload', currentSessionShortcut);

  // pageload: background candy on header and footer
  function moveBackground() {
    // header & footer bg image
    $('#header').css(
      'background-position',
      'center -' + (75*Math.floor(Math.random()*4)).toString(10) + 'px'
    );

    $('#footer').css(
      'background-position',
      'center -' + (75*Math.floor(Math.random()*6)).toString(10) + 'px'
    );
  }
  //$(window).bind('pageload', moveBackground);

  // pageload: checkAppCache
  // if the browser tab has been opened for kAppCacheCheckingInterval
  // the next pageload will trigger AppCache check update
  var kAppCacheCheckingInterval = 60*1000; // a minute
  var lastTimeCheck = (new Date()).getTime();
  function checkAppCache(ev, samepage) {
    if (!window.applicationCache || window.applicationCache.status == 0)
      return;

    var t = (new Date()).getTime();
    if (t < lastTimeCheck + kAppCacheCheckingInterval && !samepage)
      return;

    lastTimeCheck = t;
    window.applicationCache.update();
  };
  $(window).bind('checkAppCache', checkAppCache);

  // fullpageload: social buzz on homepage #sidebar2
  function socialBuzz() {
    // if (!$('#socialbuzz').length)
    //   return;

    $('#socialbuzz').delegate(
      'a',
      'click',
      function () {
        window.open(this.href);
        return false;
      }
    );
    $(window).one(
      'pageunload',
      function () {
        $('#socialbuzz').undelegate('a', 'click');
      }
    );

    var plurks, twits;
    var showSocialBuzz = function (plurks, twits) {
      var $u = $('<ul />');

      /*
      * 一個 username 只會出現一次（跨 Twitter / Plurk 比對）
      * 跳過從 Plurk 送過來的 Twitter
      * 跳過 Retweet / RePlurk
      */

      var usernames = [];

      if (twits) {
        var i = 0, t;
        while (i < 2) {
          t = twits.results.shift();
          if (!t) break;
          if (/plurk\.com/.test(t.source)) continue; // sync from Plurk
          if (/^RT/.test(t.text)) continue; // Retweet
          if ($.inArray(t.from_user, usernames) !== -1) continue; // same username
          usernames.push(t.from_user);

          $u.append(
            $('<li />').append(
              $('<span class="text" />').html(t.text)
            ).append(
              '<span class="meta">'
              + '<a href="https://twitter.com/#!/' + t.from_user + '/status/' + t.id_str + '">@' + t.from_user + '</a>'
              + '</span>'
            )
          );
          i++;
        }
      }
      if (plurks) {
        var i = 0, t;
        while (i < 2) {
          t = plurks.plurks.shift();
          if (!t) break;
          if (!plurks.users[t.user_id]) continue; // Plurk API quirk
          if (/plurk\.com\/(m\/)?\p\//.test(t.content)) continue; // RePlurk, contain a Plurk URL within this Plurk
          if ($.inArray(plurks.users[t.user_id].nick_name, usernames) !== -1) continue; // same username, possible 3rd party sync
          usernames.push(plurks.users[t.user_id].nick_name);

          $u.append(
            $('<li />').append(
              $('<span class="text" />').html(t.content)
            ).append(
              '<span class="meta">'
              + '<a href="http://www.plurk.com/p/' + t.plurk_id.toString(36) + '">@' + plurks.users[t.user_id].nick_name + '</a>'
              + '</span>'
            )
          );
          i++;
        }
      }
      $('#socialbuzz').empty().append($u);
    }

    $.getJSON(
      //window.location.href.match(/^http:\/\/[ipv6\.]*coscup.org\/[^\/]+\//)[0] + 'api/plurk/',
      //FIXME before we figure out how to generate plurk API, let use 2012's copy temporarily.
      'http://coscup.org/2012/api/plurk/',
      function (data) {
        plurks = data;
        showSocialBuzz(plurks, twits);
      }
    );
    $.getJSON(
      'https://search.twitter.com/search.json?q=coscup+OR+from%3Acoscup&callback=?',
      function (data) {
        twits = data;
        showSocialBuzz(plurks, twits);
      }
    );
  }
  $(window).bind('fullpageload', socialBuzz);

  // fullpageload: ipv6 block on homepage #sidebar2
  function ipv6block() {
    if (!$('#ipv6block').length)
      return;

    if (window.location.hostname === 'ipv6.coscup.org') {
        if (window._gaq) _gaq.push(['_trackEvent', 'IPv6 2014', 'connected']);
      $('#ipv6block').addClass('show').append(
        '<h2>IPv6 Connectivity</h2>'
        + '<p>You are currently using IPv6 connection.</p>'
      );
    } else {
      $.getJSON(
        // See http://ipv6-test.com/api/
        'http://v6.ipv6-test.com/api/myip.php?json&callback=?',
        function (data) {
          if (window._gaq) _gaq.push(['_trackEvent', 'IPv6 2014', 'ready but not connected']);
          $('#ipv6block').addClass('show').append(
            '<h2>Connect using IPv6</h2>'
            + '<p>Your network is IPv6 ready. Try it now by connect to <a href="http://ipv6.coscup.org/">ipv6.coscup.org</a>.</p>'
          );
        }
      );
    }
  }
  $(window).bind('fullpageload', ipv6block);

  // fullpageload: countdown on homepage #sidebar2
  function countdown() {
    clearTimeout(countdownTimer);
    if (!$('#countdown-time').length)
      return;

    var updateCountDown = function () {
      var dt = Math.floor(
        (new Date("Fri Jul 16 2012 20:00:00 GMT+0800 (CST)")
          - new Date())
        / 1E3
      );

      if (dt < 0) {
        clearTimeout(countdownTimer);
        $('#countdown').html(
          {
            en: '<a href="http://registrano.com/events/coscup2012-regist?locale=en">Register Now!</a>',
            'zh-tw': '<a href="http://registrano.com/events/coscup2012-regist">立刻報名！</a>',
            'zh-cn': '<a href="http://registrano.com/events/coscup2012-regist">立刻报名！</a>'
          }[lang]
        );
      }

      s = [];

      s[0] = /*((dt%60 < 10)?'0':'') + */ (dt%60).toString(10) + {
        en: ' seconds',
        'zh-cn': ' 秒',
        'zh-tw': ' 秒'
      }[lang];
      dt = Math.floor(dt/60);
      s[1] = /*((dt%60 < 10)?'0':'') + */ (dt%60).toString(10) + {
        en: ' minutes ',
        'zh-cn': ' 分 ',
        'zh-tw': ' 分 '
      }[lang];
      dt = Math.floor(dt/60);
      s[2] = (dt%24).toString(10) + {
        en: ' hours ',
        'zh-cn': ' 时 ',
        'zh-tw': ' 時 '
      }[lang];
      dt = Math.floor(dt/24);
      if (dt) {
        s[3] = dt.toString(10) + {
          en: ' days ',
          'zh-cn': ' 天 ',
          'zh-tw': ' 天 '
        }[lang];
      }
      $('#countdown-time').text(s.reverse().join(''));
    }

    var countdownTimer = setInterval(
      updateCountDown,
      1000
    );
    updateCountDown();
    $('#countdown').addClass('show');

    $(window).one(
      'pageunload',
      function (ev) {
        clearTimeout(countdownTimer);
      }
    );
  }
  $(window).bind('fullpageload', countdown);

  // deferpageload: hide iframe in .hideInMobile iframe
  function deferIframeLoad() {
    $('.hideInMobile iframe').each(
      function () {
        $(this).attr('data-src', this.src);
        this.src = '';
      }
    );

    $(window).bind(
      'resumepageload',
      function () {
        $('.hideInMobile iframe').each(
          function () {
            if ($(this).attr('data-src')) this.src
              = $(this).attr('data-src');
          }
        );
      }
    );
  }
  $(window).bind('deferpageload', deferIframeLoad);

  // pageload: the big program table on program page
  var programs;
  function insertProgramInfo() {
    if (!$('table.program').length)
      return;

    if (!programs) {
      var url = window.location.href
        .match(/^http:\/\/[ipv6\.]*coscup.org\/[^\/]+\//)[0]
        + 'api/program/';

      $.getJSON(
        url,
        function (data) {
          programs = data.program;
          insertProgramInfo();
        }
      );
      return;
    }

    $(window).delegate(
      '#video_modal, #video_close_button',
      'click',
      function () {
        $('.video_box').remove();
        $(window).unbind('scroll.repositionvideo resize.repositionvideo pageload.repositionvideo');
      }
    );

    // in iOS standalone mode, use javascript instead of hashtag scroll
    $(window).delegate(
      '.shortcuts a',
      'click',
      function (ev) {
        $(window).trigger('scroll');
        //if (!navigator.standalone) return;
        ev.preventDefault();
        $(document.body).animate(
          {
            'scrollTop': $(this.hash).offset().top - 20
          },
          180
        );
      }
    );

    $(window).one(
      'pageunload',
      function () {
        $(window).undelegate('#video_modal, #video_close_button', 'click');
        $(window).undelegate('.shortcuts a', 'click');
      }
    );

    var types = (function () {
      var types = {};
      $('.types li').each(
        function (i, el) {
          types[(i+1).toString(10)] = $(this).text();
        }
      );
      return types;
    })();

    $.fn.translateTo = function (left) {
      if (left) {
        left += 10;
        return this.addClass('translate').css({
          '-webkit-transform': 'translateX(' + left.toString(10) + 'px)',
          '-moz-transform': 'translateX(' + left.toString(10) + 'px)',
          '-ms-transform': 'translateX(' + left.toString(10) + 'px)',
          'transform': 'translateX(' + left.toString(10) + 'px)'
        });
      } else {
        return this.removeClass('translate').css({
          '-webkit-transform': '',
          '-moz-transform': '',
          '-ms-transform': '',
          'transform': ''
        });
      }
    };

    var scrollTimer;

    $('table.program').each(
      function () {
        var $this = $(this);
        $this.prev().after(
          $('<div class="program" />').append($this).bind(
            'scroll',
            function () {
              var $that = $(this);
              $that.find('thead th:first, tbody th').translateTo(0);
              clearTimeout(scrollTimer);
              scrollTimer = setTimeout(
                function () {
                  $that.find('thead th:first, tbody th').translateTo($that.scrollLeft());
                },
                200
              );
            }
          )/*.bind(
            'selectstart',
            function () {
              return false;
            }
          )*/.bind(
            'touchstart', //mousedown
            function (ev) {
              var $this = $(this),
              $window = $(window),
              posX = ev.clientX || ev.originalEvent.touches[0].clientX,
              posY = ev.clientY || ev.originalEvent.touches[0].clientY;
              if (!$this.hasClass('expend') || isMobileLayout()) return;
              $this.addClass('movestart');
              $window.bind(
                'touchmove', //mousemove
                function (ev) {
                  $this.removeClass('movestart').addClass('moving').scrollLeft(
                    $this.scrollLeft()
                    + posX
                    - (ev.clientX || ev.originalEvent.touches[0].clientX)
                  );
                  if (ev.type !== 'touchmove') {
                    // not to conflict y-dir scroll with non-cancelable(?) browser action
                    $window.scrollTop(
                      $window.scrollTop()
                      + posY
                      - (ev.clientY || ev.originalEvent.touches[0].clientY)
                    );
                  }
                  posX = ev.clientX || ev.originalEvent.touches[0].clientX;
                  posY = ev.clientY || ev.originalEvent.touches[0].clientY;
                }
              ).bind(
                'touchend', //mouseup
                function (ev) {
                  $window.unbind('touchmove touchend'); //mousemove mouseup
                  setTimeout(
                    function () {
                      $this.removeClass('moving movestart');
                    },
                    0
                  );
                  return false;
                }
              );
            }
          )
        );
      }
    );

    $('table.program td').each(
      function () {
        var $this = $(this),
        program = programs[$this.data('pid')];

        if (!program) return;

        if (program.type === 0) {
          var $outerMeta = $('<ul class="meta" />');
          if (program.slide) {
            var $slide = $('<li><a href="' + program.slide + '">' + {en:'Slide', 'zh-tw':'投影片', 'zh-cn':'投影片'}[lang || 'en'] + '</a></li>');
            $outerMeta.append($slide);
          }
          if (program.youtube) {
            var list = [].concat(program.youtube),
            program_embed_url = 'http://www.youtube.com/embed/' + list.shift() + '?hd=1',
            $youtube;

            if (program.youtube.length) program_embed_url += '&playlist=' + list.join(',');

            $youtube = $('<li><a href="' + program_embed_url + '" class="youtube_video">' + {en:'Video', 'zh-tw':'演講錄影', 'zh-cn':'演讲录影'}[lang || 'en'] + '</a></li>');

            $outerMeta.append($youtube);
          }
          if ($outerMeta.children().length) $this.append($outerMeta);
          return;
        }

        var $meta = $('<ul class="meta" />'),
        $outerMeta = $('<ul class="meta" />'),
        program_lang = ({'en': 'English', 'zh': '\u6f22\u8a9e'})[(this.className.match(/program_lang_(\w+)\b/) || [])[1]],
        program_type = types[(this.className.match(/program_type_(\w+)\b/) || [])[1]];

        if (program_lang) $meta.append($('<li />').text(program_lang));
        if (program_type) $meta.append($('<li />').text(program_type));
        if (program.slide) {
          var $slide = $('<li><a href="' + program.slide + '">' + {en:'Slide', 'zh-tw':'投影片', 'zh-cn':'投影片'}[lang || 'en'] + '</a></li>');
          $outerMeta.append($slide);
          $meta.append($slide.clone());
        }
        if (program.youtube) {
          var list = [].concat(program.youtube),
          program_embed_url = 'http://www.youtube.com/embed/' + list.shift() + '?hd=1',
          $youtube;

          if (program.youtube.length) program_embed_url += '&playlist=' + list.join(',');

          $youtube = $('<li><a href="' + program_embed_url + '" class="youtube_video">' + {en:'Video', 'zh-tw':'演講錄影', 'zh-cn':'演讲录影'}[lang || 'en'] + '</a></li>');

          $outerMeta.append($youtube);
          $meta.append($youtube.clone());
        }

        var $info = $('<div class="info" />');

        if ($meta.children().length) $info.append($meta);

        if (program['abstract']) { // abstract is a reserved word
          $info.append(
            $('<div class="abstract" />').html(program['abstract'])
          );
        }

        if (program.bio) {
          $info.append(
            $('<div class="bio" />').html(program.bio)
          );
        }

        if ($outerMeta.children().length) $this.append($outerMeta);
        if ($info.children().length) $this.append($info);
      }
    ).bind(
      'click',
      function () {
        var $this = $(this),
        $div = $this.parents('div.program'),
        room_id = parseInt((this.className.match(/program_room_(\w+)\b/) || [])[1]),
        y = $(window).scrollTop() - $this.offset().top;

        if ($div.hasClass('moving')) return false;

        if (window._gaq) _gaq.push(['_trackEvent', 'Program 2014', this.hash]);

        // For mobile
        $(this).toggleClass('expend');

        // For desktop
        $div.toggleClass('expend');
        $div.find('thead th:first, tbody th').translateTo($div.scrollLeft());

        switch (room_id) {
          case 0:
          $div.scrollLeft($div[0].scrollWidth*0.26);
          break;
          case 1:
          $div.scrollLeft(0);
          break;
          case 2:
          $div.scrollLeft($div[0].scrollWidth*0.13);
          break;
          case 3:
          $div.scrollLeft($div[0].scrollWidth*0.26);
          break;
          case 4:
          $div.scrollLeft($div[0].scrollWidth*0.39);
          break;
          case 5:
          $div.scrollLeft($div[0].scrollWidth);
          break;
        }
        $(window).scrollTop(
          $this.offset().top + y
        );
      }
    );

    $('.program p.name a').bind(
      'click',
      function (ev) {
        if (ev.which == 2 || ev.metaKey) return true;
        ev.preventDefault();
      }
    );

    $('.program .meta a, .program .info a').bind(
      'click',
      function (ev) {
        if (ev.which == 2 || ev.metaKey) return true;
        if ($(this).hasClass('youtube_video') && !isMobileLayout()) {
          $(document.body).append(
            '<div id="video_modal" class="video_box" />'
          ).append(
            '<iframe id="video_iframe" class="video_box" title="YouTube video player" width="854" height="483"  src="' + this.href + '" frameborder="0"  allowfullscreen="allowfullscreen"></iframe>'
          ).append(
            '<div id="video_close_button" class="video_box" />'
          );

          if ($('#video_modal').offset().top === 0) { // devices that doesn't support fixed position
            $('.video_box').css('position', 'absolute');
            var repositionVideo = function () {
              $('#video_modal').css(
                {
                  top: 0,
                  left: 0,
                  width: document.body.scrollWidth,
                  height: document.body.scrollHeight
                }
              );
              $('#video_iframe, #video_close_button').css(
                {
                  top: $(window).scrollTop() + (window.innerHeight || $(window).height())/2,
                  left: $(window).scrollLeft() + (window.innerWidth || $(window).width())/2
                }
              );
            }
            repositionVideo();
            $(window).bind(
              'scroll.repositionvideo resize.repositionvideo',
              repositionVideo
            ).bind(
              'pageload.repositionvideo',
              function (ev) {
                $(window).unbind('scroll.repositionvideo resize.repositionvideo').unbind(ev);
              }
            );
          }
          // window.open(this.href, 'coscup_youtube_video', 'width=854,height=483'); // 480p video size on youtube
        } else {
          window.open(this.href);
        }
        ev.preventDefault();
        ev.stopPropagation();
      }
    );
  }
  $(window).bind('pageload', insertProgramInfo);

  if (window.applicationCache && window.applicationCache.status !== 0) {
    //  This is a cached HTML. Let's insert date as version.
    $.ajax({
      url: '',
      success: function insertVersion(data, status, xhr) {
      var strings = {
        'en': [
          'Ver: ',
          'Check for update now'
        ],
        'zh-tw': [
          '版本：',
          '立刻檢查更新'
        ],
        'zh-cn': [
          '版本：',
          '立刻检查更新'
        ]
      };

      // date string is consider in local time here
      var lastModified = new Date(xhr.getResponseHeader('Last-Modified'));

      var pad = function (s) { return ((s < 10)?'0':'') + s; };
      var $copyright = $('#copyright');
      var $a = $(
        '<a href="#" title="' + strings[lang][1] + '">' +
          strings[lang][0] +
          'v' + lastModified.getUTCFullYear() +
          pad(lastModified.getUTCMonth() + 1) +
          pad(lastModified.getUTCDate()) + '.' +
          pad(lastModified.getUTCHours()) +
          pad(lastModified.getUTCMinutes()) +
        '</a>'
      );
      $a.bind('click', function (ev) {
        ev.preventDefault();
        ev.stopPropagation();

        $(document.body).addClass('appcache-allinfo');
        $('#appcache_info')[0].className = 'appcache-checking';
        setTimeout(function () {
          window.applicationCache.update();
        }, 500);

        return false;
      });

      $copyright.append('<span class="separator"> | </span>');
      $copyright.append($a);
    }
    });
  }

  // Start everything.
  PageHandler.init();

});
