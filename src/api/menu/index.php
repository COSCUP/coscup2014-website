<?php

if (isset($_GET['callback']))
{
	if (!preg_match("/[\w\.\[\]\"]+$/", $_GET['callback']))
	{
		header('HTTP/1.1 400 Bad Request');
		die("Malformed callback");
	}
	header("Content-type: text/javascript; charset=utf-8");
	header('Access-Control-Allow-Origin: *');
	echo $_GET['callback'] . '(';
	readfile ("menu.json.js");
	echo ')';
}
else
{
	header("Content-type: text/javascript; charset=utf-8");
	header('Access-Control-Allow-Origin: *');
	readfile ("menu.json.js");
}

