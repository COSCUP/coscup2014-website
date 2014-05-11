/*
 * Here are components we need on homepage
 *
 * - News widget: use internal news api call to load the first 3 news posts
 * - socialBuzz: use internal plurk api and external twitter api to load posts from social media 
 *
 * */

jQuery(function ($) {

  // pageload: news widget
  function loadNewsWidget() {
    if ($('.news > .news_list.empty').length) {
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
});
