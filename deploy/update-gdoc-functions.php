<?php
include_once ("deploy.php");
include_once ("markdown-without-markup.php");

date_default_timezone_set('Asia/Taipei');
setlocale (LC_ALL, "en_US.UTF-8");

function linkify($text){
	$text = preg_replace('/(?<!\[|\<|\]\()(https?:\/\/[a-zA-Z0-9\/\&\$\#\+\;\:\?\@\%\.\-\=\_]+)/', '[$0]($0)', $text);
	// FIXME: find better way to regexp this
	return $text;
}

function get_program_list_from_gdoc() {

	$handle = @fopen('https://spreadsheets.google.com/pub?key=' . PROGRAM_LIST_KEY . '&range=A2%3AM999&output=csv', 'r');

	if (!$handle)
	{
		return FALSE; // failed
	}

	$program_list = array();

	// name, from, to, room, type, community, speaker, speakerTitle, bio, abstract, language, slide, youtube
	while (($program = fgetcsv($handle)) !== FALSE)
	{
		if (trim($program[0]) === '') continue;

		$program_obj = array(
			'name' => $program[0],

			// use strtotime to convert to unix timestamp.
			'from' => strtotime($program[1]),
			'to' => strtotime($program[2]),

			'room' => intval($program[3])
		);

		if (trim($program[4]) !== '')
		{
			$program_obj['type'] = intval($program[4]);
		}
		if (trim($program[5]) !== '')
		{
			$program_obj['community'] = intval($program[5]);
    } else {
			$program_obj['community'] = 0;
    }

		if (trim($program[6]))
		{
			$program_obj['speaker'] = $program[6];

			if (trim($program[7]))
			{
				$program_obj['speakerTitle'] = $program[7];
			}
			if (trim($program[8]))
			{
				$program_obj['bio'] = Markdown_Without_Markup(linkify($program[8]));
			}
      if (trim($program[9]))
      {
        $program_obj['abstract'] = Markdown_Without_Markup(linkify($program[9]));
      }
		}

		if (trim($program[10]))
		{
			$program_obj['lang'] = $program[10];
		}

		if (trim($program[11]))
		{
			$program_obj['slide'] = trim($program[11]);
		}

		if (trim($program[12]))
		{
			$program_obj['youtube'] = array();
			foreach (explode("\n", trim($program[12])) as $url)
			{
				if (trim($url))
				{
					$program_obj['youtube'][] = preg_replace('/^.+v=([^"&?\/ ]{11}).*$/', '$1', trim($url)); // only get the ID
				}
			}
		}

		if (
			isset($program_obj['type']) && $program_obj['type'] === 0
			&& $program_obj['room'] <= 0
			&& !isset($program_obj['speaker'])
		)
		{
			$program_obj['isBreak'] = true;
		}
		else
		{
			$program_obj['isBreak'] = false;
		}

		$program_list[] = $program_obj;
	}

	fclose($handle);

	return $program_list;
}

function get_program_types_from_gdoc() {

	// TODO: constant gid written in uri
	$handle = @fopen('https://spreadsheets.google.com/pub?key=' . PROGRAM_LIST_KEY . '&gid=3&range=A2%3AB999&output=csv', 'r');

	if (!$handle)
	{
		return FALSE; // failed
	}

	$type_list = array();

	// id, name
	while (($type = fgetcsv($handle)) !== FALSE)
	{
		$type_list[intval($type[0])] = $type[1];
	}

	fclose($handle);

	return $type_list;
}

function get_program_rooms_from_gdoc() {

	// TODO: constant gid written in uri
	$handle = @fopen('https://spreadsheets.google.com/pub?key=' . PROGRAM_LIST_KEY . '&gid=4&range=A2%3AD999&output=csv', 'r');

	if (!$handle)
	{
		return FALSE; // failed
	}

	$room_list = array();

	// id, name, nameEn, nameZhCn
	while (($room = fgetcsv($handle)) !== FALSE)
	{
		$room_list[intval($room[0])] = array(
			'zh-tw' => $room[1],
			'en' => $room[2],
			'zh-cn' => $room[3]
		);
	}

	fclose($handle);

	return $room_list;
}

function get_program_community_from_gdoc() {

	// TODO: constant gid written in uri
	$handle = @fopen('https://spreadsheets.google.com/pub?key=' . PROGRAM_LIST_KEY . '&gid=5&range=A2%3AB999&output=csv', 'r');

	if (!$handle)
	{
		return FALSE; // failed
	}

	$community_list = array();

	// id, name
	while (($type = fgetcsv($handle)) !== FALSE)
	{
		$community_list[intval($type[0])] = $type[1];
	}

	fclose($handle);

	return $community_list;
}
function get_program_list_html(&$program_list, &$type_list, &$room_list, $community_list, $lang = 'zh-tw') {

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

	$name_replace = array(
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
	);

  $draft = array(
    'zh-tw' => '* 議程表仍有變動，請常回來查看本網頁，不另通知',
    'zh-cn' => '* 议程表仍有变动，请常回来查看本网页，不另通知',
    'en' => '* We are still updating, check out often!'
  );

  $ps_list = array(
    'zh-tw' => '* 跨時段議程',
    'zh-cn' => '* 跨时段议程',
    'en' => '* cross-time session'
  );

	// constructing data structures

	$structure = array();
	$time_structure = array();
	$continue = array();

	foreach ($program_list as $id => &$program)
	{

		$program['id'] = $id;

		if(!isset($structure[$program['from']]))
		{
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
	foreach($type_list as $type_id => $type_name)
	{
		if ($type_id <= 0)
		{
			continue;
		}
		$html['program'] .= sprintf('<li class="colorTag-%d">%s</li>'."\n",
				$type_id,
				htmlspecialchars($type_name)
				);
	}
	$html['program'] .= '</ul>' . "\n\n";

	$html['program'] .= '<div id="navTab" class="nav"><ul>';

	foreach (array(1, 2) as $day) {
		$html['program'] .= sprintf('<li><a href="#day%d_am">%s</a></li>'."\n",
				$day,
				$l10n[$lang]["am_day_$day"]
				);
		$html['program'] .= sprintf('<li><a href="#day%d_pm">%s</a></li>'."\n",
				$day,
				$l10n[$lang]["pm_day_$day"]
				);
	}
	$html['program'] .= '</ul></div>' . "\n\n";
  $html['program'] .= '<span style="color:red">' . $draft[$lang].'</span>';

	$last_stamp = 0;
	$day_increment = 0;

	foreach ($time_structure as $time_id => $time_stamp)
	{
		if (!isset($structure[$time_stamp]))
		{
			continue;
		}

		$last_time = getdate($last_stamp);
		$this_time = getdate($time_stamp);
		$time_stamp_end = $time_structure[$time_id+1];
		$this_time_formatted = strftime("%R", $time_stamp);
		$to_time_formatted = strftime("%R", $time_stamp_end);

    if ( ($last_time['hours'] <= 12 && $this_time['hours'] > 12) || $last_time['yday'] != $this_time['yday'] )
		{
			if($day_increment > 0)
			{
				$html['program'] .= '<div class="time_finish"></div>'."\n";
      }
      if ($last_time['yday'] != $this_time['yday'])
      {
        $day_increment += 1;
      }
      $noon = ($this_time['hours'] <= 12)? "am" : "pm";
      $day_noon = $noon."_day_".$day_increment; 

			$html['program'] .= '<h2 class="pro" id="day' . $day_increment . '_' . $noon . '">'
				. $l10n[$lang][$day_noon]
				. ' (' . $this_time['mon'] . '/' . $this_time['mday'] . ')'
				. '</h2>'
				."\n";
    }

     $html['program'] .= sprintf('<div rel="%d" class="time">%s — %s</div>',
       $time_stamp,  $this_time_formatted, $to_time_formatted);

     $counter = 0;
     $html['program'] .= '<div class="article">'."\n";

     $structure[$time_stamp] = array_merge($structure[$time_stamp], $continue);
     $continue = array();

     usort($structure[$time_stamp], function ($a, $b) {
       if ($a['room'] == 9)
         return -1;
       if ($b['room'] == 9)
         return 1;
       return ($a['room'] - $b['room']);
     });

		 foreach ($structure[$time_stamp] as &$program)
     {
       // We need to process multi-span session again
       if ($program['to'] !== $time_stamp_end)
       {
         $program['isMultiSlot'] = true;
         $continue[$program['room']] = $program;
       }
			 
       // check in & break & lunch
       if ($program['isBreak'] && isset($name_replace[$program['name']]))
       {
         $html['program'] .= sprintf('<span class="title">%s</span>',
           htmlspecialchars($name_replace[$program['name']][$lang]));
         break;
       }

       // opening, anouncement, closing
       if ($program['room'] === 0 && $program['type'] === 0)
       {
         $html['program'] .= sprintf('<span class="title">%s</span>',
           htmlspecialchars($program['name']));
         $html['program'] .= sprintf('<br/><span class="sub_title">%s</span>',
           htmlspecialchars($program['speaker']));
         break;
       }

       // speaker talks
       $counter++;
       //room == 0 is a keynote, room == 9 is a cross rooms session
       $eventClass = ($program['room'] === 0 || $program['room'] === 9)? "program keynote" : "program";
       $html['program'] .= sprintf('<div class="%s" data-id="%d">', $eventClass, $program['id']);
       $html['program'] .= sprintf('  <div class="metadata track_tag colorTag-%d">', $program['type']);
       $html['program'] .= sprintf('  <div class="head"><div class="place">%s</div>', 
                          htmlspecialchars($room_list[$program['room']][$lang]));
       if (isset($program['youtube'])) {
         $html['program'] .= sprintf('  <div class="video"><a href="http://www.youtube.com/watch?v=%s">', $program['youtube'][0]);
         $html['program'] .= '<img src="/2013-theme/assets/icon_camera.png" /></a></div>';
       }
       if ($program['slide']) {
         $html['program'] .= sprintf('  <div class="slide"><a href="%s"><img src="/2013-theme/assets/icon_slide.png" /></a></div>', $program['slide']);
       }
       $html['program'] .= sprintf('  <div class="community">%s</div>',
           htmlspecialchars($community_list[$program['community']]));
       $html['program'] .= '</div><div class="body">';
       $html['program'] .= sprintf('  <div class="topic %s">%s</div>', $program['lang'], htmlspecialchars($program['name']));
       $html['program'] .= sprintf('  <div class="speaker">%s</div>', htmlspecialchars($program['speaker']));
       $html['program'] .= sprintf('  <div class="speaker-title">%s</div>', htmlspecialchars($program['speakerTitle']));
       if ($program['isMultiSlot']) 
       {
         $html['program'] .= sprintf('  <div class="ps">%s</div>', htmlspecialchars($ps_list[$lang]));
       }
       $html['program'] .= "</div></div></div>\n";
     }
     $html['program'] .= "</div>\n";   //end <article>
     $last_stamp = $time_stamp;
  }

  $html['program'] .= '<div class="time_finish"></div>'."\n";

  return $html;
}


function get_sponsors_list_from_gdoc() {

	$handle = @fopen('https://spreadsheets.google.com/pub?key=' . SPONSOR_LIST_KEY . '&range=A2%3AI999&output=csv', 'r');

	if (!$handle)
	{
		return FALSE; // failed
	}

	$SPONS = array();

	// name, level, url, logoUrl, desc, enName, enDesc, zhCnName, zhCnDesc
	while (($SPON = fgetcsv($handle)) !== FALSE)
	{

		$level = strtolower(trim($SPON[1]));
		if (strlen($level) === 0) continue;

		if (!isset($SPONS[$level]))
		{
			$SPONS[$level] = array();
		}

    // only show the sponsor who has logo image
    if (trim($SPON[3]) === "") continue;
    
    $SPON_obj = array(
      'name' => array(
        'zh-tw' => $SPON[0]
      ),
      'desc' => array(
        'zh-tw' => Markdown_Without_Markup($SPON[4])
      ),
      'url' => $SPON[2],
      'logoUrl' => $SPON[3],
    );
    

		if (trim($SPON[5]))
		{
			$SPON_obj['name']['en'] = $SPON[5];
		}

		if (trim($SPON[6]))
		{
			$SPON_obj['desc']['en'] = Markdown_Without_Markup($SPON[6]);
		}

		if (trim($SPON[7]))
		{
			$SPON_obj['name']['zh-cn'] = $SPON[7];
		}

		if (trim($SPON[8]))
		{
			$SPON_obj['desc']['zh-cn'] = Markdown_Without_Markup(linkify($SPON[8]));
		}

		array_push ($SPONS[$level], $SPON_obj);
	}

	fclose($handle);

	return $SPONS;
}

function get_donate_list_from_gdoc() {

	$handle = @fopen('https://spreadsheets.google.com/pub?key=' . SPONSOR_LIST_KEY . '&gid=6&range=A2%3AB999&output=csv', 'r');

	if (!$handle)
	{
		return FALSE; // failed
	}

	$donate_list = array();

	// name, money
	while (($entry = fgetcsv($handle)) !== FALSE)
  {
    if ($entry[0] === 'anonymous') {
      $donate_list[0] = $entry[1];
      continue;
    }
    $money = intval($entry[1]);
    if (!isset($donate_list[$money]))
      $donate_list[$money] = array();
		$donate_list[$money][] = $entry[0];
	}

  fclose($handle);

  krsort($donate_list);
  if (extension_loaded("intl")) {
    $collator = new Collator('zh_TW_STROKE');
    foreach ($donate_list as $m => &$names) {
      if ($m === 0)
        continue;
      $collator->sort($names);  // sorting by name
    }
  }

	return $donate_list;
}

function get_sponsor_info_localize($SPON, $type='name', $locale='zh-tw', $fallback='zh-tw')
{
	if ($SPON[$type][$locale])
	{
		return $SPON[$type][$locale];
	}
	return $SPON[$type][$fallback];
}

function get_sponsors_html($SPONS, $DONATES, $type = 'sidebar', $lang = 'zh-tw') {

	$levelTitlesL10n = array(
		'en' => array(
			'diamond' => 'Diamond',
			'gold' => 'Gold',
			'silver' => 'Silver',
			'bronze' => 'Bronze',
      'cohost' => 'Co-host', 
      'special' => 'Special Thanks',
      'media' => 'Media Partners',
      'personal' => 'Individual Sponsorship'
		),
		'zh-tw' => array(
			'diamond' => '鑽石級贊助',
			'gold' => '黃金級贊助',
			'silver' => '白銀級贊助',
			'bronze' => '青銅級贊助',
      'cohost' => '協辦單位', 
      'special' => '特別感謝',
      'media' => '媒體夥伴',
      'personal' => '個人贊助'
		),
		'zh-cn' => array(
			'diamond' => '钻石级赞助商',
      'gold' => '黄金级赞助',
      'silver' => '白银级赞助',
      'bronze' => '青铜级赞助',
      'cohost' => '协办单位', 
      'special' => '特别感谢',
      'media' => '媒体伙伴',
      'personal' => '个人赞助'
		)
	);

	// order of levels (fixed)
	$levels = array(
		'diamond',
		'gold',
		'silver',
		'bronze',
    'cohost',
    'special',
    'media'
	);

	$levelTitles = $levelTitlesL10n[$lang];
  $specialThanks = array(
    'zh-tw' => '請點選看看有那些支持 COSCUP 的夥伴們!',
    'zh-cn' => '请点选看看有那些支持 COSCUP 的伙伴们!',
    'en' => 'Click here to know more supporting partners!'
  );

  $donateDesc = array(
    'zh-tw' => '謝謝所有參與 COSCUP 2013 個人贊助方案的贊助者，因為有你們，促成了活動的舉行，感謝各位! 以下贊助者名字依贊助款金額與姓名筆劃順序排列：',
    'zh-cn' => '谢谢所有参与 COSCUP 2013 个人赞助方案的赞助者，因为有你们，促成了活动的举行，感谢各位! 以下赞助者名字依赞助款金额与姓名笔划顺序排列：',
    'en' => 'We appreciate your support! Because of you, COSCUP is doing better. The following names are ordered by sponsorship amount and number of strokes.'
  );
  $donateAnonymous = array(
    'zh-tw' => '及不具名的好朋友 %s 名',
    'zh-cn' => '及不具名的好朋友 %s 名',
    'en' => '... and %s anonymous donors'
  );

	$html = '';
	switch ($type)
	{
		case 'sidebar':
      foreach ($levels as &$level)
      {
        if (!$SPONS[$level] || $level === 'special')
          continue;

        $html .= sprintf("<h2>%s</h2>\n", htmlspecialchars($levelTitles[$level]));
        $html .= sprintf('<ul class="%s">'."\n", $level);

        foreach ($SPONS[$level] as $i => &$SPON)
        {
          $html .= sprintf('  <li><a href="%s" target="_blank" title="%s">'.
               '<img src="%s" alt="%s"/></a></li>'."\n",
              htmlspecialchars($SPON['url']),
              htmlspecialchars(get_sponsor_info_localize($SPON, 'name', $lang)),
              htmlspecialchars($SPON['logoUrl']),
              htmlspecialchars(get_sponsor_info_localize($SPON, 'name', $lang))
              );
        }

        $html .= "</ul>\n\n";
      }
      // add special thank
      $sponsorLink = '/2013/'.$lang.'/sponsors/#special';
      $html .= sprintf('<h2>%s</h2>'."\n", htmlspecialchars($levelTitles['special']));
      $html .= sprintf('<ul>'."\n".'  <li><a href="%s" title="%s">%s</a></li>'."\n".'</ul>',
                      $sponsorLink, 
                      htmlspecialchars($levelTitles['special']),
                      htmlspecialchars($specialThanks[$lang])
               );
      break;
    case 'mobile-sidebar':
      $counter = 0;
      foreach ($levels as &$level)
      {
        if (!$SPONS[$level])
          continue;

        foreach ($SPONS[$level] as $i => &$SPON)
        {
          if ($counter%2 === 0)  $html .= "<div><span>\n";

          $html .= sprintf('  <a href="%s" target="_blank" title="%s">'.
               '<img src="%s" alt="%s" /></a>'."\n",
              htmlspecialchars($SPON['url']),
              htmlspecialchars(get_sponsor_info_localize($SPON, 'name', $lang)),
              htmlspecialchars($SPON['logoUrl']),
              htmlspecialchars(get_sponsor_info_localize($SPON, 'name', $lang))
              );

          if ($counter%2 === 1)  $html .= "</span></div>\n";
          $counter += 1;
        }
      }
      if ($counter%2 === 1)  $html .= "</b></div>\n";
      break;

		case 'page':
      foreach ($levels as &$level)
      {
        if (!$SPONS[$level]) continue;
        // donor should before media partners
        if ($level === 'media' && count($DONATES) > 0) {
          $html .= sprintf('<h1 id="donor">%s</h1>'."\n", htmlspecialchars($levelTitles['personal']));
          $html .= sprintf('<p>%s</p>'."\n", htmlspecialchars($donateDesc[$lang]));

          $html .= '<div class="splist donor">'."\n";
          $html .= '<img />'."\n";  // just a placeholder
          $html .= '  <div class="spinfo"><ul>'."\n";
          foreach ($DONATES as $m => &$names) {
            if ($m === 0)
              continue;
            foreach ($names as $name) {
              $html .= sprintf('<li>%s</li>'."\n", htmlspecialchars($name));
            }
          }
          $html .= "</ul>\n";
          if (isset($DONATES[0])) {
            $html .= sprintf('<div>'.$donateAnonymous[$lang].'</div>', $DONATES[0]);
          }
          $html .= "</div></div>\n";
        }

        $html .= sprintf('<h1 id="%s">%s</h1>'."\n", $level, htmlspecialchars($levelTitles[$level]));

        foreach ($SPONS[$level] as $i => &$SPON)
        {
          $html .= '<div class="splist">'."\n";
          $html .= sprintf('<a href="%s" target="_blank"><img src="%s" alt="%s" />'."\n",
              htmlspecialchars($SPON['url']),
              htmlspecialchars($SPON['logoUrl']),
              get_sponsor_info_localize($SPON, 'name', $lang)
              );

          $html .= '  <div class="spinfo">'."\n";
          $html .= sprintf('    <h2>%s</h2>'."\n", get_sponsor_info_localize($SPON, 'name', $lang));
          if (trim(get_sponsor_info_localize($SPON, 'desc', $lang)))
          {
            $html .= sprintf('    %s', get_sponsor_info_localize($SPON, 'desc', $lang));
          }
          $html .= "  </div>\n</a></div>\n";
        }
      }


      break;
	}
	return $html;
}

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
    $html .= sprintf("<h1 id=\"News\">%s</h1>\n", htmlspecialchars($l10n[$lang]['News']));
    $html .= "<div class=\"news\">\n";
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

function get_live_list_from_gdoc() {

    $handle = @fopen('https://spreadsheets.google.com/pub?key=' . LIVE_LIST_KEY . '&range=A2%3AF999&output=csv', 'r');

    if (!$handle)
    {
        return FALSE; // failed
    }

    $LIVE_LIST = array();

    // from, to, title, speaker, url, isOnline
    while (($live = fgetcsv($handle)) !== FALSE)
    {
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

        array_push ($LIVE_LIST, $LIVE_obj);
    }

    fclose($handle);

    return $LIVE_LIST;
}

function get_live_list_html($LIVE_LIST, $lang = 'zh-tw') {

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
    foreach ($LIVE_LIST as $idx => &$live)
    {
        $html .= "    <div class=\"list\">\n";
        $html .= "<div>\n";
        $formated_from = strftime("%m/%d %R", $live['from']);
        $formated_to = strftime("%R", $live['to']);
        $html .= sprintf("  <span>%s - %s</span>\n", 
                            htmlspecialchars($formated_from), htmlspecialchars($formated_to));
        if ($live['isOnline']) {
          $html .= sprintf("  <span class=\"online\">%s</span>\n", htmlspecialchars($l10n[$lang]['Online']));
        }
        else {
          $html .= sprintf("  <span class=\"online end\">%s</span>\n", htmlspecialchars($l10n[$lang]['Over']));
        }
        $html .= "</div>\n";
        $html .= sprintf("  <div class=\"title\">%s</div>\n", htmlspecialchars($live['title']));
        $html .= sprintf("  <div class=\"speaker\">%s</div>\n", htmlspecialchars($live['speaker']));

        if ($live['isOnline']) {
          $html .= sprintf("  <div class=\"link\"><a href=\"%s\" target=\"_blank\">%s</a></div>\n",
                           htmlspecialchars($live['url']),
                           htmlspecialchars($live['url']));
          $html .= sprintf("  <iframe src=\"%s\" frameborder=\"0\" allowfullscreen></iframe>\n",
                           htmlspecialchars($live['url']));
        }

        $html .= "    </div>\n";
    }
    $html .= "</div>\n"; // <div class="live">
    return $html;
}


function anchor_name($s) {
	return str_replace(" ", "-", trim($s));
}


$SPONS = get_sponsors_list_from_gdoc();
$DONATES = get_donate_list_from_gdoc();

if ($SPONS === FALSE)
{
	print "ERROR! Unable to download sponsors list from Google Docs.\n";
}
else
{
	foreach ($sponsors_output as $type => $l10n)
	{
		foreach ($l10n as $lang => $path)
		{
			print "Write sponsors into " . $path . " .\n";
			$fp = fopen($path, "w");
			fwrite($fp, get_sponsors_html($SPONS, $DONATES, $type, $lang));
			fclose($fp);
		}
	}

  $donors = array();
  $anonymous = 0;
	foreach ($DONATES as $m => &$names) {
		if ($m === 0) {
      $anonymous = $names;
      continue;
    }
		foreach ($names as $name) {
			$donors[] = $name;
		}
	}

	print "Write sponsors into " . $json_output["sponsors"] . " .\n";
	$fp = fopen ($json_output["sponsors"], "w");
	fwrite ($fp, json_encode(
		array(
			'sponsors' => $SPONS,
      'donors' => $donors,
      'anonymous' => $anonymous
		)));
	fclose ($fp);
}

$program_list = get_program_list_from_gdoc();
$program_types_list = get_program_types_from_gdoc();
$program_rooms_list = get_program_rooms_from_gdoc();
$program_community_list = get_program_community_from_gdoc();

if (
	$program_list === FALSE
	|| $program_types_list === FALSE
	|| $program_rooms_list === FALSE
	|| $program_community_list === FALSE
)
{
	print "ERROR! Unable to download program list from Google Docs.\n";
}
else
{
	foreach ($program_list_output as $type => $l10n)
	{
		foreach ($l10n as $lang => $path)
		{
      $program_list_html = get_program_list_html($program_list, $program_types_list, $program_rooms_list, $program_community_list, $lang);
      print "Write program into " . $path . " .\n";
      $fp = fopen($path, "w");
      fwrite($fp, $program_list_html[$type]);
      fwrite($fp, '<div id="lock_background"><div id="program_detail" class="program"></div></div>');
			fclose($fp);
		}
	}

	print "Write program into " . $json_output["program"] . " .\n";
	$fp = fopen ($json_output["program"], "w");
	fwrite ($fp, json_encode(
		array(
			'program' => $program_list,
			'type' => $program_types_list,
			'room' => $program_rooms_list,
			'community' => $program_community_list
		)));
	fclose ($fp);
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

$live_list = get_live_list_from_gdoc();

if ($live_list === FALSE)
{
    print "ERROR! Unable to download live list from Google Docs.\n";
}
else
{
    foreach ($live_list_output as $lang => $path)
    {
        $live_list_html = get_live_list_html($live_list, $lang);
        print "Write live into " . $path . " .\n";
        $fp = fopen($path, "w");
        fwrite($fp, $live_list_html);
        fclose($fp);
    }
}
