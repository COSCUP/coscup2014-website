<?php

date_default_timezone_set('Asia/Taipei');
setlocale(LC_ALL, "en_US.UTF-8");
header("Content-Type: text/html; charset=UTF-8");

include_once("config-stage.php");
include_once("markdown-without-markup.php");
include_once("utils.php");
include_once("compile-program.php");

class fake_marksite
{
  function menu($n) {
    return "";
  }

  function generate() {
    global $program_sheets;
    $program_list = get_program_list_from_gdoc($program_sheets['program']);
    $program_types_list = get_program_types_from_gdoc($program_sheets['type']);
    $program_rooms_list = get_program_rooms_from_gdoc($program_sheets['room']);
    $program_community_list = get_program_community_from_gdoc($program_sheets['community']);

    if ($program_list &&
        $program_types_list &&
        $program_rooms_list &&
        $program_community_list) {
      $program_list_html = get_program_list_html($program_list,
                                                 $program_types_list,
                                                 $program_rooms_list,
                                                 $program_community_list,
                                                 'zh-tw');
      // Marksite template reads content from $transformed
      $transformed = $program_list_html['program'];
      $transformed .= "\n";
      $transformed .= '<div id="lock_background">'."\n";
      $transformed .= '  <div id="program_detail" class="program"></div>'."\n";
      $transformed .= '</div>'."\n";
      $transformed .= "\n";
      $title = "Program";
      $home_path = "";
      $this->current = array("");
      ob_start();
      include (THEME_PATH.MARKSITE_PATH."page.php");
      $output = ob_get_contents();
      ob_end_clean();
      print $output;
    } else {
      print "<p>Can't fetch data from google doc</p>\n";
    }
  }
}
	
function is_cli()
{
  if(defined('STDIN'))
  {
    return true;
  }
     
  if(empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0)
  {
    return true;
  }
     
  return false;
}

if(is_cli()) {
  $program_generator = new fake_marksite;
  $program_generator->generate();
}

/* Local Variables: */
/* indent-tabs-mode: nil */
/* c-basic-offset: 2 */
/* End: */
