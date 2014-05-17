<?php
function get_sponsors_list_from_gdoc() {

	$handle = @fopen('https://spreadsheets.google.com/pub?key=' . SPONSOR_LIST_KEY . '&range=A2%3AI999&output=csv', 'r');

	if (!$handle)	{
		return FALSE; // failed
	}

	$SPONS = array();

	// name, level, url, logoUrl, desc, enName, enDesc, zhCnName, zhCnDesc
	while (($SPON = fgetcsv($handle)) !== FALSE) {

		$level = strtolower(trim($SPON[1]));
    // only keep sponsors who assigned level and logo image
		if (strlen($level) === 0) continue;
    if (trim($SPON[3]) === "") continue;

		if (!isset($SPONS[$level])) {
			$SPONS[$level] = array();
		}

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
    

		if (trim($SPON[5])) {
			$SPON_obj['name']['en'] = $SPON[5];
		}

		if (trim($SPON[6])) {
			$SPON_obj['desc']['en'] = Markdown_Without_Markup($SPON[6]);
		}

		if (trim($SPON[7])) {
			$SPON_obj['name']['zh-cn'] = $SPON[7];
		}

		if (trim($SPON[8])) {
			$SPON_obj['desc']['zh-cn'] = Markdown_Without_Markup(linkify($SPON[8]));
		}

		array_push ($SPONS[$level], $SPON_obj);
	}

	fclose($handle);

	return $SPONS;
}

function get_donate_list_from_gdoc() {
	$handle = @fopen('https://spreadsheets.google.com/pub?key=' . SPONSOR_LIST_KEY . '&gid=6&range=A2%3AB999&output=csv', 'r');

	if (!$handle) {
		return FALSE; // failed
	}

	$donate_list = array();

	// name, money
	while (($entry = fgetcsv($handle)) !== FALSE) {
    if ($entry[0] === 'anonymous') {
      $donate_list[0] = $entry[1];
      continue;
    }
    $money = intval($entry[1]);
    if (!isset($donate_list[$money])) {
      $donate_list[$money] = array();
    }
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

function get_sponsor_info_localize($SPON, $type='name', $locale='zh-tw', $fallback='zh-tw') {
	if ($SPON[$type][$locale]) {
		return $SPON[$type][$locale];
	}
	return $SPON[$type][$fallback];
}

function get_sponsors_html($SPONS, $DONATES, $type = 'sidebar', $lang = 'zh-tw') {

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

	$html = '';
	switch ($type)
	{
		case 'sidebar':
      foreach ($levels as &$level)
      {
        if (!$SPONS[$level] || $level === 'special')
          continue;

        $html .= sprintf("<h2 data-l10n-id='%s'></h2>\n", $level);
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
      $html .= sprintf('<h2 data-l10n-id="special"></h2>'."\n");
      $html .= sprintf('<ul>'."\n".'  <li><a href="%s" title="special" 
        data-l10n-id="specialThanks"></a></li>'."\n".'</ul>', $sponsorLink); 

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
          $html .= sprintf('<h1 id="donor" data-l10n-id="personal"></h1>'."\n");
          $html .= sprintf('<p data-l10n-id="donateDesc"></p>'."\n");

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
            $html .= sprintf('<script type="application/l10n-data+json">{"anonymousDonors": %s}</script>'."\n", $DONATES[0]);
            $html .= sprintf('<div data-l10n-id="donateAnonymous"></div>');
          }
          $html .= "</div></div>\n";
        }

        $html .= sprintf('<h1 id="%s" data-l10n-id="%s"></h1>'."\n", $level, $level);

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


