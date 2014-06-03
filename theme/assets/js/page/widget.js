/*
 * Here are components we need on homepage
 *
 * - News widget: use internal news api call to load the first 3 news posts
 * - socialBuzz: use internal plurk api and external twitter api to load posts from social media 
 *
 * */
define(['jquery', 'context'], function($, context) {

  'use strict';

  var api_url = context.origin + context.api_path + '/news/?callback=?'; 

  function loadNewsWidget() {
    if ($('.news > .news_list.empty').length) {
      $.getJSON(api_url, function(data) {
          if (!data || data['news'].length === 0) {
            $('.news').hide();
            return;
          }
          var $news_list = $('.news > .news_list.empty').removeClass('empty');
          $.each(data['news'].slice(0, 3), function (i, news) {
            $news_list.append(
              '<div class="list"><span>' + news.date + '<b>' + news.source + '</b></span>' +
                '<a href="' + news.link + '" target="_blank">' +
                '<div class="title">' + news.title+ '</div></a></div>'
            );
          });
      });
    }
  }

  function socialBuzz() {

    $('#socialbuzz').delegate(
      'a',
      'click',
      function () {
        window.open(this.href);
        return false;
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

  function paddingZero(num) {
    return (num < 10)? '0'+num : num;
  }

  function countdown(end) {
    var $warning = $('<div style="font-size: 14px;" data-l10n-id="countdownWarning"></div>');
    $warning.text(document.l10n.getSync('countdownWarning'));
    var countdownText = document.l10n.getSync('countdown');
    var startedText = document.l10n.getSync('registrationStarted');
    var $countdown = $('<div></div>');
    $('.countdown').append($countdown);
    $('.countdown').append($warning);

    var id = setInterval(function() {
      var now = new Date();
      if (now > end) {
        $('.countdown a').text(startedText);
        clearInterval(id);
        return;
      }
      var diff = Math.floor((end - now) / 1000);
      var hour = Math.floor(diff / 3600);
      diff -= (3600 * hour);
      var min = paddingZero(Math.floor(diff / 60));
      diff -= (60 * min);
      var sec = paddingZero(diff);
      $countdown.text(countdownText + ': ' + hour + ' : ' + min + ' : ' + sec);
    }, 1000);
  }

  // init widgets
  loadNewsWidget();
  socialBuzz();
  /*document.l10n.ready(function() {
    countdown(new Date(1401796800000)); // epoch time of 2014-06-03 20:00
  });*/
});
