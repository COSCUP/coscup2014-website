<?php 
# external data key
define('SPONSOR_LIST_KEY', 'YOUR_GOOGLE_SPREADSHEET_API_KEY_WHICH_HAS_SPONSORS');
define('PROGRAM_LIST_KEY', 'YOUR_GOOGLE_SPREADSHEET_API_KEY_WHICH_HAS_PROGRAM_LIST');
define('NEWS_LIST_KEY', 'YOUR_GOOGLE_SPREADSHEET_API_KEY_WHICH_HAS_NEWS_LIST');
define('LIVE_LIST_KEY', 'YOUR_GOOGLE_SPREADSHEET_API_KEY_WHICH_HAS_LIVE_LIST');

# deploy path
define('MARKSITE_PATH', 'marksite/');
define('THEME_PATH', '../theme/');
define('SRC_PATH', '../src/');
define('SRC_TMP_PATH', 'src-tmp/');
define('TMP_PATH', 'tmp/');
define('WEBSITE_PATH', '../../2014-beta/');  // Final output

# marksite configurations
define('MARKSITE_ABSOLUTE_PATH', '/2014-beta/' );
define('MARKSITE_SRC_PATH', '../src-tmp/' );
define('MARKSITE_DST_PATH', '../tmp/' );
define('MARKSITE_TEMPLATE_PATH', '../../theme/marksite/page.php' );
define('MARKSITE_APPCACHE', 'all' ); # appcache generation method: all, parsed, none

define('RUNNING_USER', 'www-data');  // http running user, remember to change all files' ownership to this user.

$sponsors_output = array(
  "zh-tw" => "src-tmp/zh-tw/sponsors/index.html",
  "zh-cn" => "src-tmp/zh-cn/sponsors/index.html",
  "en" => "src-tmp/en/sponsors/index.html"
);

$program_list_output = array(
  "program" => array (
    "zh-tw" => "src-tmp/zh-tw/program/index.html",
    "zh-cn" => "src-tmp/zh-cn/program/index.html",
    "en" => "src-tmp/en/program/index.html"
  )
);

$news_list_output = array(
  "zh-tw" => "src-tmp/zh-tw/news/index.html",
  "zh-cn" => "src-tmp/zh-cn/news/index.html",
  "en" => "src-tmp/en/news/index.html"
);

$live_list_output = array(
  "zh-tw" => "src-tmp/zh-tw/live/index.html",
  "zh-cn" => "src-tmp/zh-cn/live/index.html",
  "en" => "src-tmp/en/live/index.html"
);

$json_output = array(
  "menu" => "tmp/api/menu/menu.json.js",
  "sponsors" => "tmp/api/sponsors/sponsors.json.js",
  "program" => "tmp/api/program/program.json.js",
  "news" => "tmp/api/news/news.json.js"
);

