/*
 * Fill up sponsor information on sidebar or mobile view
 */

define(function(require) {

  'use strict';

  var $ = require('jquery');
  var context = require('context');

  var api_url = context.origin + context.api_path + '/sponsors/?callback=?';
  var sponsorLevels = ['diamond', 'gold', 'silver', 'bronze', 'cohost', 'media'];

  function getData(callback) {
    $.getJSON(api_url, function(data) {
        // FIXME make sure it won't have event racing
        document.l10n.ready(function() {
          callback(data);
        });
      }
    );
  }

  function desktopSponsorList(data) {
    var $sponsors = $('#sponsor').removeClass('empty');

    // Save existing nodes
    var $existingSponsors = $sponsors.children();
    $sponsors.empty();

    $.each(sponsorLevels, function (i, level) {
        if (!data['sponsors'][level]) {
          return;
        }
        var $h2 = $('<h2 data-l10n-id="' + level + '" />');
        $h2.text(document.l10n.getSync(level));
        $sponsors.append($h2);
        var $u = $('<ul class="' + level + ' no-decoration" />');
        $.each(data['sponsors'][level], function (i, sponsor) {
          // Assume that there is no special chars to escape
          $u.append(
            '<li><a href="' + sponsor.url + '" target="_blank">'
            + '<img title="' + sponsor.name[context.lang] + '" src="' + context.origin + sponsor.logoUrl + '" />'
            + '</a></li>'
          );
        });
        $sponsors.append($u);
      }
    );
    // Restore existing sponsors
    $sponsors.append($existingSponsors);
  }

  function mobileSponsorList(data) {
    var $allSponsors = [];
    $.each(sponsorLevels, function (i, level) {
        if (!data['sponsors'][level]) return;
        $.each(
          data['sponsors'][level],
          function (i, sponsor) {
            $allSponsors.push(sponsor);
          }
        );
      }
    );
    var $wrap = $('<div class="swipe-wrap" />');
    for (var j = 0; j < $allSponsors.length; j += 2) {
      var sponsor1 = $allSponsors[j];
      var sponsor2 = $allSponsors[j+1];
      if (!sponsor2) {
        sponsor2 = { url: '#', name: { 'zh-tw': '' }, logoUrl: '' };
      }
      $wrap.append(
        '<div><span>'
          + '<a href="' + sponsor1.url + '" target="_blank" title="' + sponsor1.name[context.lang] + '">'
          + '<img alt="' + sponsor1.name[context.lang] + '" src="' + context.origin + sponsor1.logoUrl + '" width="40%"/></a>'
          + '<a href="' + sponsor2.url + '" target="_blank" title="' + sponsor2.name[context.lang] + '">'
          + '<img alt="' + sponsor2.name[context.lang] + '" src="' + context.origin + sponsor2.logoUrl + '" width="40%"/></a>'
          + '</span></div>');
    }
    $('#mySwipe').removeClass('empty').append($wrap);
    require(['lib/swipe'], function(Swipe) {
      Swipe(document.getElementById('mySwipe'), {
        auto: 3000
      });
    });
  }

  // init: Load sponsors from API if it's empty,
  var id = context.mobile? '#mySwipe' : '#sponsor';
  var callback = context.mobile? mobileSponsorList : desktopSponsorList;
  if ($(id + '.empty').length) {
    getData(callback);
  }
});
