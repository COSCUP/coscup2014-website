<?php
include_once ("markdown-without-markup.php");

date_default_timezone_set('Asia/Taipei');
setlocale (LC_ALL, "en_US.UTF-8");

// Utility functions
function linkify($text){
	$text = preg_replace('/(?<!\[|\<|\]\()(https?:\/\/[a-zA-Z0-9\/\&\$\#\+\;\:\?\@\%\.\-\=\_]+)/', '[$0]($0)', $text);
	// FIXME: find better way to regexp this
	return $text;
}

print ("== Generating Sponsors ==\n");
include ("compile-sponsor.php");
print ("\n");

print ("== Generating News ==\n");
include ("compile-news.php");
print ("\n");

print ("== Generating Program ==\n");
include ("compile-program.php");
print ("\n");

print ("== Generating Live Streaming ==\n");
include ("compile-live.php");
print ("\n");
