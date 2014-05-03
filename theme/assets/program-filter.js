$(window).on('fullpageload', function () {
  if ($('#navTab').length === 0)
    return;

$programs = $('#content .program');
$times = $('#content .time');
$tags = $('.class_tag li');

$.expr[':'].programtag = function (obj, index, meta, stack) {
    var tag = meta[3];

    return $(obj).find('.track_tag').is('.' + tag)
};

$('#content .class_tag').on('click', 'li', function (event) {
    var $target = $(event.currentTarget),
        tag = $target.attr('class').match(/colorTag-\d+/)[0],
        choosed = $programs.filter(":programtag(" + tag + ")"),
        others = $programs.filter(":not(:programtag(" + tag + "))"),
        active = $target.is('.inactive')

    if (active) {
        $target.removeClass('inactive');
        choosed.removeClass('inactive').show();
    } else if ($tags.length - $tags.filter('.inactive').length === 1) {
        return;
    } else {
        $target.addClass('inactive');
        choosed.addClass('inactive').hide();
    }

    $times.each(function (index, time) {
        var $time = $(time),
            $article = $time.next(),
            visible = !!$article.find('> .program:not(.inactive)').length || !!$article.find('> .sub_title').length;

        if (visible) {
            $time.show();
            $article.show();
        } else {
            $time.hide();
            $article.hide();
        }
    });

});

});
