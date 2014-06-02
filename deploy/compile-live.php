<?php
function get_live_list_from_gdoc($source_url)
{

    $handle = @fopen($source_url, 'r');

    if (!$handle) {
        return FALSE; // failed
    }

    $LIVE_LIST = array();

    // from, to, title, speaker, url, isOnline
    while (($live = fgetcsv($handle)) !== FALSE) {
        if (trim($live[4]) === "") {
            continue;
        }

        $LIVE_obj = array(
            'from' => strtotime($live[0]),
            'to' => strtotime($live[1]),
            'title' => trim($live[2]),
            'speaker' => trim($live[3]),
            'url' => trim($live[4]),
            'isOnline' => (trim($live[5]) === 'yes')
        );

        array_push($LIVE_LIST, $LIVE_obj);
    }

    fclose($handle);

    return $LIVE_LIST;
}

function get_live_list_html($LIVE_LIST, $lang = 'zh-tw')
{

    $l10n = array(
        'en' => array(
            'Live' => 'Live Broadcast',
            'Online' => 'On air',
            'Over' => 'Over'
        ),
        'zh-tw' => array(
            'Live' => '線上直播',
            'Online' => '放送中',
            'Over' => '已經結束'
        ),
        'zh-cn' => array(
            'Live' => '在线观看',
            'Online' => '播放中',
            'Over' => '放映结束'
        )
    );
    rsort($LIVE_LIST);
    $html = '';
    $html .= sprintf("<h1 id=\"Live\">%s</h1>\n", htmlspecialchars($l10n[$lang]['Live']));
    $html .= "<div class=\"live\">\n";
    foreach ($LIVE_LIST as $idx => &$live) {
        $html .= "    <div class=\"list\">\n";
        $html .= "<div>\n";
        $formated_from = strftime("%m/%d %R", $live['from']);
        $formated_to   = strftime("%R", $live['to']);
        $html .= sprintf("  <span>%s - %s</span>\n", htmlspecialchars($formated_from), htmlspecialchars($formated_to));
        if ($live['isOnline']) {
            $html .= sprintf("  <span class=\"online\">%s</span>\n", htmlspecialchars($l10n[$lang]['Online']));
        } else {
            $html .= sprintf("  <span class=\"online end\">%s</span>\n", htmlspecialchars($l10n[$lang]['Over']));
        }
        $html .= "</div>\n";
        $html .= sprintf("  <div class=\"title\">%s</div>\n", htmlspecialchars($live['title']));
        $html .= sprintf("  <div class=\"speaker\">%s</div>\n", htmlspecialchars($live['speaker']));

        if ($live['isOnline']) {
            $html .= sprintf("  <div class=\"link\"><a href=\"%s\" target=\"_blank\">%s</a></div>\n", htmlspecialchars($live['url']), htmlspecialchars($live['url']));
            $html .= sprintf("  <iframe src=\"%s\" frameborder=\"0\" allowfullscreen></iframe>\n", htmlspecialchars($live['url']));
        }

        $html .= "    </div>\n";
    }
    $html .= "</div>\n"; // <div class="live">
    return $html;
}

$live_list = get_live_list_from_gdoc($live_sheets['live']);

if ($live_list === FALSE) {
    print "Notice: skip Live list from Google Docs.\n";
} else {
    foreach ($live_list_output as $lang => $path) {
        $live_list_html = get_live_list_html($live_list, $lang);
        print "Write live into " . $path . " .\n";
        $fp = fopen($path, "w");
        fwrite($fp, $live_list_html);
        fclose($fp);
    }
}

