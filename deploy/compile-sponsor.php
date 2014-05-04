<?php
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
    'zh-tw' => '謝謝所有參與 COSCUP 2014 個人贊助方案的贊助者，因為有你們，促成了活動的舉行，感謝各位! 以下贊助者名字依贊助款金額與姓名筆劃順序排列：',
    'zh-cn' => '谢谢所有参与 COSCUP 2014 个人赞助方案的赞助者，因为有你们，促成了活动的举行，感谢各位! 以下赞助者名字依赞助款金额与姓名笔划顺序排列：',
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
      $sponsorLink = '/2014/'.$lang.'/sponsors/#special';
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


