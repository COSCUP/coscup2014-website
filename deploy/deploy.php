<?php 
include_once("config.php");
include_once("google_translate.php");

header('Content-Type: text/plain');
setlocale(LC_ALL, 'en_US.UTF-8');

if (
	!isset($argv) &&
	$_SERVER['REQUEST_METHOD'] !== 'POST'
) 
{
	die("Error: Not a POST request nor run from command line.");
}

if ( trim(exec('whoami')) !== RUNNING_USER ) 
{
	die("Error: Please run with the specified user: ".RUNNING_USER);
}

print ("= Create ".TMP_PATH."/api folder structure =\n");
system("rm -rf ".escapeshellarg(TMP_PATH));
mkdir(dirname($json_output['menu']), 0777, true);
mkdir(dirname($json_output['sponsors']), 0777, true);
mkdir(dirname($json_output['program']), 0777, true);
mkdir(dirname($json_output['news']), 0777, true);

$cwd = getcwd();

function recompile_and_sync()
{
	# workaround trying to use value in config.
	include ("config.php");

	print ("= Compiling Content =\n");

	chdir (MARKSITE_PATH);
	include 'marksite.php';
	chdir ("..");
	print ("\n");

	print ("= Writing menu.json.js =\n");
	$fp = fopen ($json_output["menu"], "w");
	$r = array();
	foreach($marksite->menu as $locale => $menuitem)
	{
		$r[$locale] = "<ul>" . $marksite->menu_recursion($menuitem['menu'], 1, 2, false) . "</ul>";
	}
	fwrite ($fp, json_encode($r));
	fclose ($fp);
	print ("\n");

	if (file_exists(TMP_PATH.'site.appcache'))
	{

		print ("= Writing commit hashes to manifest =\n");
		$cwd = getcwd();
		chdir (THEME_PATH);
		$theme_hash = trim(system("git rev-parse HEAD"));
		chdir ($cwd);
		$fp = fopen (TMP_PATH.'site.appcache', "a");
		fwrite ($fp, "\n# THEME $theme_hash\n");
		print ("\n");

	}

	print ("= Syncing Content to target WEBSITE_PATH =\n");
	system ('rsync -a --delete ' . TMP_PATH . ' ' . WEBSITE_PATH);
	print ("\n");
}
