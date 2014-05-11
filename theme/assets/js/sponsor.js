/*
 * Fill up sponsor information on sidebar
 *
 * */

jQuery(function ($) {

  // init: Load sponsors from API if it's empty (happen on sub-domain sites),
  if ($('#sponsor.empty').length) {
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
});
