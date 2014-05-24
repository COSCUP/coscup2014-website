<?php
function get_news_list_from_gdoc() {

    $handle = @fopen('https://spreadsheets.google.com/pub?key=' . NEWS_LIST_KEY . '&range=A2%3AD999&output=csv', 'r');

    if (!$handle)
    {
        return FALSE; // failed
    }

    $NEWS_LIST = array();

    // date, title, source, link
    while (($NEWS = fgetcsv($handle)) !== FALSE)
    {
        $date = trim($NEWS[0]);
        $title = trim($NEWS[1]);
        $source = trim($NEWS[2]);
        $link = trim($NEWS[3]);

        if ($date === "" ||
            $title === "" ||
            $link === "") {
            continue;
        }

        $NEWS_obj = array(
            'date' => $date,
            'title' => $title,
            'source' => $source,
            'link' => $link
        );

        array_push ($NEWS_LIST, $NEWS_obj);
    }

    fclose($handle);

    return $NEWS_LIST;
}

function get_news_list_html($NEWS_LIST, $lang = 'zh-tw') {

    $l10n = array(
        'en' => array(
            'News' => 'News'
        ),
        'zh-tw' => array(
            'News' => '媒體報導'
        ),
        'zh-cn' => array(
            'News' => '媒体报导'
        )
    );

    $html = '';
    $html .= sprintf("<h1>%s</h1>\n", htmlspecialchars($l10n[$lang]['News']));
    $html .= "<div class=\"news news-list\">\n";
    foreach ($NEWS_LIST as $idx => &$news)
    {
        $html .= "    <div class=\"list\">\n";
        $html .= sprintf("  <span>%s<b>%s</b></span>\n", 
                            htmlspecialchars($news['date']), htmlspecialchars($news['source']));
        $html .= sprintf("  <div class=\"title\">%s</div>\n", htmlspecialchars($news['title']));
        $html .= sprintf("  <div class=\"link\"><a href=\"%s\" target=\"_blank\">%s</a></div>\n",
                         htmlspecialchars($news['link']),
                         htmlspecialchars($news['link']));
        $html .= "    </div>\n";
    }
    $html .= "</div>\n"; // <div class="news">
    return $html;
}

$news_list = get_news_list_from_gdoc();

if ($news_list === FALSE)
{
    print "ERROR! Unable to download news list from Google Docs.\n";
}
else
{
    foreach ($news_list_output as $lang => $path)
    {
        $news_list_html = get_news_list_html($news_list, $lang);
        print "Write news into " . $path . " .\n";
        $fp = fopen($path, "w");
        fwrite($fp, $news_list_html);
        fclose($fp);
    }

    print "Write news into " . $json_output["news"] . " .\n";
    $fp = fopen ($json_output["news"], "w");
    fwrite ($fp, json_encode(array('news' => $news_list)));
    fclose ($fp);
}

