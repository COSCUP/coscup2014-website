<?php

// Utility functions
function linkify($text){
	$text = preg_replace('/(?<!\[|\<|\]\()(https?:\/\/[a-zA-Z0-9\/\&\$\#\+\;\:\?\@\%\.\-\=\_]+)/', '[$0]($0)', $text);
	// FIXME: find better way to regexp this
	return $text;
}

function anchor_name($s) {
	return str_replace(" ", "-", trim($s));
}

