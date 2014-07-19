<?php
function make_program($raw_data)
{
    $fields = array(
      'name', 'from', 'to',
      'room', 'type', 'community',
      'speaker', 'speakerTitle', 'bio', 'abstract', 'language', 'slide', 'youtube'
    );
    $program = array();
    $arr_length = count($fields);
    for ($i = 0; $i <= $arr_length; $i++) {
      if (trim($raw_data[$i]) !== '') {
        $program[$fields[$i]] = trim($raw_data[$i]);
      }
    }
    return $program;
}
function validate_program_data($program)
{
    $ret = TRUE;
    $checklist = array('name', 'from', 'to');
    foreach ($checklist as $item) {
        if (!isset($program[$item]) || $program[$item] === '') {
            print "SKIP program " . $program['name'];
            print " because '" . $item . "' is empty.\n";
            $ret = FALSE;
        }
    }
    return $ret;
}

function get_program_list_from_gdoc($source_url)
{
    $handle = @fopen($source_url, 'r');

    if (!$handle) {
        return FALSE; // failed
    }

    $program_list = array();

    while (($PROG = fgetcsv($handle)) !== FALSE) {
        $program = make_program($PROG);
        if (!validate_program_data($program)) {
            continue;
        }

        // use strtotime to convert to unix timestamp.
        $program['from'] = strtotime($program['from']);
        $program['to'] = strtotime($program['to']);

        // empty string or NULL will get 0
        $program['room'] = intval($program['room']);
        $program['type'] = intval($program['type']);
        $program['community'] = intval($program['community']);

        if (isset($program['bio'])) {
            $program['bio'] = Markdown_Without_Markup(linkify($program['bio']));
        }
        if (isset($program['abstract'])) {
            $program['abstract'] = Markdown_Without_Markup(linkify($program['abstract']));
        }

        if (isset($program['youtube'])) {
            $videos = array();
            foreach (explode("\n", $program['youtube']) as $url) {
                if (trim($url)) {
                    $videos[] = preg_replace('/^.+v=([^"&?\/ ]{11}).*$/', '$1', trim($url)); // only get the ID
                }
            }
            $program['youtube'] = $videos;
        }

        // setting room = -1 and leaving speaker empty indicate a break session
        $program['isBreak'] = ($program['room'] < 0 && !isset($program['speaker']))? true : false;

        $program_list[] = $program;
    }

    fclose($handle);

    return $program_list;
}

function get_program_types_from_gdoc($source_url)
{

    $handle = @fopen($source_url, 'r');

    if (!$handle) {
        return FALSE; // failed
    }

    $type_list = array();

    // id, name
    while (($type = fgetcsv($handle)) !== FALSE) {
        $type_list[intval($type[0])] = $type[1];
    }

    fclose($handle);

    return $type_list;
}

function get_program_rooms_from_gdoc($source_url)
{

    $handle = @fopen($source_url, 'r');

    if (!$handle) {
        return FALSE; // failed
    }

    $room_list = array();

    // id, name, nameEn, nameZhCn
    while (($room = fgetcsv($handle)) !== FALSE) {
        $room_list[intval($room[0])] = array(
            'zh-tw' => $room[1],
            'en' => $room[2],
            'zh-cn' => $room[3]
        );
    }

    fclose($handle);

    return $room_list;
}

function get_program_community_from_gdoc($source_url)
{
    $handle = @fopen($source_url, 'r');

    if (!$handle) {
        return FALSE; // failed
    }

    $community_list = array();

    // id, name
    while (($type = fgetcsv($handle)) !== FALSE) {
        $community_list[intval($type[0])] = $type[1];
    }

    fclose($handle);

    return $community_list;
}
function get_program_list_html(&$program_list, &$type_list, &$room_list, $community_list, $lang = 'zh-tw')
{

    $l10n = array(
        'en' => array(
            'time' => 'Time',
            'am_day_1' => 'Day 1 AM',
            'pm_day_1' => 'Day 1 PM',
            'am_day_2' => 'Day 2 AM',
            'pm_day_2' => 'Day 2 PM'
        ),
        'zh-tw' => array(
            'time' => '時間',
            'am_day_1' => 'Day 1 早',
            'pm_day_1' => 'Day 1 午',
            'am_day_2' => 'Day 2 早',
            'pm_day_2' => 'Day 2 午'
        ),
        'zh-cn' => array(
            'time' => '时间',
            'am_day_1' => 'Day 1 早',
            'pm_day_1' => 'Day 1 午',
            'am_day_2' => 'Day 2 早',
            'pm_day_2' => 'Day 2 午'
        )
    );

    /*$name_replace = array(
        'Check In' => array(
            'zh-tw' => '報到',
            'zh-cn' => '报到'
        ),
        'Tea Break' => array(
            'zh-tw' => '休息',
            'zh-cn' => '休息'
        ),
        'Lunch' => array(
            'zh-tw' => '午餐',
            'zh-cn' => '午餐'
        )
    );*/

    $draft = array(
        'zh-tw' => '* 議程表仍有變動，請常回來查看本網頁，不另通知',
        'zh-cn' => '* 议程表仍有变动，请常回来查看本网页，不另通知',
        'en' => '* We are still updating, check out often!'
    );
    $bof = array(
        'zh-tw' => 'COSCUP 2014 BOF 內容',
        'zh-cn' => 'COSCUP 2014 BOF 內容',
        'en' => 'COSCUP 2014 BOF detail'
    );

    $ps_list = array(
        'zh-tw' => '* 跨時段議程',
        'zh-cn' => '* 跨时段议程',
        'en' => '* cross-time session'
    );

    // constructing data structures

    $structure      = array();
    $time_structure = array();
    $continue       = array();

    foreach ($program_list as $id => &$program) {

        $program['id'] = $id;

        if (!isset($structure[$program['from']])) {
            $structure[$program['from']] = array();
        }

        $structure[$program['from']][$program['room']] =& $program;
        $time_structure[] = $program['from'];
        $time_structure[] = $program['to'];
    }

    $time_structure = array_unique($time_structure);
    sort($time_structure);

    // start generate html

    $html = array();

    $html['program'] = '';

    $html['program'] .= '<ul class="class_tag">';
    foreach ($type_list as $type_id => $type_name) {
        if ($type_id <= 0) {
            continue;
        }
        $html['program'] .= sprintf('<li class="colorTag-%d">%s</li>' . "\n", $type_id, htmlspecialchars($type_name));
    }
    $html['program'] .= '</ul>' . "\n\n";

    $html['program'] .= '<div id="navTab" class="nav"><ul class="no-decoration">';

    foreach (array(1, 2) as $day) {
        $html['program'] .= sprintf('<li><a href="#day%d_am">%s</a></li>' . "\n", $day, $l10n[$lang]["am_day_$day"]);
        $html['program'] .= sprintf('<li><a href="#day%d_pm">%s</a></li>' . "\n", $day, $l10n[$lang]["pm_day_$day"]);
    }
    $html['program'] .= '</ul></div>' . "\n\n";
    $html['program'] .= '<span style="color:red">' . $draft[$lang] . '</span>';
    $bof_link = "https://docs.google.com/document/d/1akcB6ad1koXY6Q3En93wYN77LSatVKORlQRN-8P7nRs/edit?usp=docslist_api  ";
    $html['program'] .= '<div class="bof"><a href="'. $bof_link .'" target="_blank">'. $bof[$lang] .'</a></div>';
    

    $last_stamp    = 0;
    $day_increment = 0;

    foreach ($time_structure as $time_id => $time_stamp) {
        if (!isset($structure[$time_stamp])) {
            continue;
        }

        $last_time           = getdate($last_stamp);
        $this_time           = getdate($time_stamp);
        $time_stamp_end      = $time_structure[$time_id + 1];
        $this_time_formatted = strftime("%R", $time_stamp);
        $to_time_formatted   = strftime("%R", $time_stamp_end);

        if (($last_time['hours'] <= 12 && $this_time['hours'] > 12) || $last_time['yday'] != $this_time['yday']) {
            if ($day_increment > 0) {
                $html['program'] .= '<div class="time_finish"></div>' . "\n";
            }
            if ($last_time['yday'] != $this_time['yday']) {
                $day_increment += 1;
            }
            $noon     = ($this_time['hours'] <= 12) ? "am" : "pm";
            $day_noon = $noon . "_day_" . $day_increment;

            $html['program'] .= '<h2 class="pro" id="day' . $day_increment . '_' . $noon . '">' . $l10n[$lang][$day_noon] . ' (' . $this_time['mon'] . '/' . $this_time['mday'] . ')' . '</h2>' . "\n";
        }

        $html['program'] .= sprintf('<div rel="%d" class="time">%s — %s</div>', $time_stamp, $this_time_formatted, $to_time_formatted);

        $counter = 0;
        $html['program'] .= '<div class="article">' . "\n";

        $structure[$time_stamp] = array_merge($structure[$time_stamp], $continue);
        $continue = array();

        usort($structure[$time_stamp], function($a, $b)
        {
            if ($a['room'] == 9)
                return -1;
            if ($b['room'] == 9)
                return 1;
            return ($a['room'] - $b['room']);
        });

        // check and enqueue multiple slots
        foreach ($structure[$time_stamp] as &$program) {
            // We need to process multi-span session again
            if ($program['to'] !== $time_stamp_end) {
                $program['isMultiSlot']  = true;
                $continue[$program['room']] = $program;
            }

        }

        // layout each program within this time slot
        foreach ($structure[$time_stamp] as &$program) {
            // check in & break & lunch
            if ($program['isBreak']) {
                $html['program'] .= sprintf('<span class="title">%s</span>', htmlspecialchars($program['name']));
                break;
            }

            // opening, anouncement, closing
            if ($program['room'] === 0 && $program['type'] === 0) {
                $html['program'] .= sprintf('<span class="title">%s</span>', htmlspecialchars($program['name']));
                $html['program'] .= sprintf('<br/><span class="sub_title">%s</span>', htmlspecialchars($program['speaker']));
                break;
            }

            // speaker talks
            $counter++;
            //room == 0 is a keynote, room == 9 is a cross rooms session
            $eventClass = ($program['room'] === 0 || $program['room'] === 9) ? "program keynote" : "program";
            $html['program'] .= sprintf('<div class="%s" data-id="%d">', $eventClass, $program['id']);
            $html['program'] .= sprintf('  <div class="metadata track_tag colorTag-%d">', $program['type']);
            $html['program'] .= sprintf('  <div class="head"><div class="place">%s</div>', htmlspecialchars($room_list[$program['room']][$lang]));
            if (isset($program['youtube'])) {
                $html['program'] .= sprintf('  <div class="video"><a href="http://www.youtube.com/watch?v=%s">', $program['youtube'][0]);
                $html['program'] .= '<img src="/2014/assets/icon_camera.png" /></a></div>';
            }
            if ($program['slide']) {
                $html['program'] .= sprintf('  <div class="slide"><a href="%s"><img src="/2014/assets/icon_slide.png" /></a></div>', $program['slide']);
            }
            $html['program'] .= sprintf('  <div class="community">%s</div>', htmlspecialchars($community_list[$program['community']]));
            $html['program'] .= '</div><div class="body">';
            $html['program'] .= sprintf('  <div class="topic %s">%s</div>', $program['language'], htmlspecialchars($program['name']));
            $html['program'] .= sprintf('  <div class="speaker">%s</div>', htmlspecialchars($program['speaker']));
            $html['program'] .= sprintf('  <div class="speaker-title">%s</div>', htmlspecialchars($program['speakerTitle']));
            if ($program['isMultiSlot']) {
                $html['program'] .= sprintf('  <div class="ps">%s</div>', htmlspecialchars($ps_list[$lang]));
            }
            $html['program'] .= "</div></div></div>\n";
        }
        $html['program'] .= "</div>\n"; //end <article>
        $last_stamp = $time_stamp;
    }

    $html['program'] .= '<div class="time_finish"></div>' . "\n";

    return $html;
}

function write_program_files($program_sheets, $program_list_output, $json_output)
{
    $program_list           = get_program_list_from_gdoc($program_sheets['program']);
    $program_types_list     = get_program_types_from_gdoc($program_sheets['type']);
    $program_rooms_list     = get_program_rooms_from_gdoc($program_sheets['room']);
    $program_community_list = get_program_community_from_gdoc($program_sheets['community']);

    if ($program_list === FALSE || $program_types_list === FALSE || $program_rooms_list === FALSE || $program_community_list === FALSE) {
        print "Notice: skip Program list from Google Docs.\n";
    } else {
        foreach ($program_list_output as $type => $l10n) {
            foreach ($l10n as $lang => $path) {
                $program_list_html = get_program_list_html($program_list, $program_types_list, $program_rooms_list, $program_community_list, $lang);
                print "Write program into " . $path . " .\n";
                $fp = fopen($path, "w");
                fwrite($fp, $program_list_html[$type]);
                fwrite($fp, '<div id="lock_background"><div id="program_detail" class="program"></div></div>');
                fclose($fp);
            }
        }

        print "Write program into " . $json_output["program"] . " .\n";
        $fp = fopen($json_output["program"], "w");
        fwrite($fp, json_encode(array(
            'program' => $program_list,
            'type' => $program_types_list,
            'room' => $program_rooms_list,
            'community' => $program_community_list
        )));
        fclose($fp);
    }
}

