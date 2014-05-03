<?php 

define('SPONSOR_LIST_KEY', 'YOUR_GOOGLE_SPREADSHEET_API_KEY_WHICH_HAS_SPONSORS');
define('PROGRAM_LIST_KEY', 'YOUR_GOOGLE_SPREADSHEET_API_KEY_WHICH_HAS_PROGRAM_LIST');
define('NEWS_LIST_KEY', 'YOUR_GOOGLE_SPREADSHEET_API_KEY_WHICH_HAS_NEWS_LIST');
define('LIVE_LIST_KEY', 'YOUR_GOOGLE_SPREADSHEET_API_KEY_WHICH_HAS_LIVE_LIST');

define('MARKSITE_PATH', 'marksite/');
define('THEME_PATH', '../theme/');
define('SRC_PATH', '../src/');
define('TMP_PATH', 'tmp/');
define('WEBSITE_PATH', '../../2013-beta/');  // Final output

define('RUNNING_USER', 'www-data');  // http running user, remember to change all files' ownership to this user.

$sponsors_output = array(
	"sidebar" => array(
		"zh-tw" => "../src/blocks/sponsors-zh-tw.html",
		"zh-cn" => "../src/blocks/sponsors-zh-cn.html",
		"en" => "../src/blocks/sponsors-en.html"
	),
	"mobile-sidebar" => array(
		"zh-tw" => "../src/blocks/sponsors-mobile.html"
	),
	"page" => array(
		"zh-tw" => "../src/zh-tw/sponsors/index.md",
		"zh-cn" => "../src/zh-cn/sponsors/index.md",
		"en" => "../src/en/sponsors/index.md"
	)
);

$program_list_output = array(
  "program" => array (
    "zh-tw" => "../src/zh-tw/program/index.html",
    "zh-cn" => "../src/zh-cn/program/index.html",
    "en" => "../src/en/program/index.html"
  )
);

$news_list_output = array(
    "zh-tw" => "../src/zh-tw/news/index.html",
    "zh-cn" => "../src/zh-cn/news/index.html",
    "en" => "../src/en/news/index.html"
);

$live_list_output = array(
  "zh-tw" => "../src/zh-tw/live/index.html",
  "zh-cn" => "../src/zh-cn/live/index.html",
  "en" => "../src/en/live/index.html"
);

$json_output = array(
	"menu" => "tmp/api/menu/menu.json.js",
	"sponsors" => "tmp/api/sponsors/sponsors.json.js",
	"program" => "tmp/api/program/program.json.js",
	"news" => "tmp/api/news/news.json.js"
);

