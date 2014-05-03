<?php
include_once ("deploy.php");

print ("= Reverting Source =\n");
chdir (SRC_PATH);
system ("git reset --hard");
system ("git log -1");
chdir ($cwd);
print ("\n");

if ($_POST["fullupdate"] == 1)
{
	print ("= Updating Marksite =\n");
	chdir (MARKSITE_PATH);
	system ("git pull origin master");
	system ("git log -1");
	chdir ($cwd);
	print ("\n");


	print ("= Updating Sponsorship Form =\n");
	chdir (SPONSORSHIP_FORM_PATH);
	system ("git pull origin master");
	system ("git log -1");
	chdir ($cwd);
	print ("\n");

	print ("= Syncing Sponsorship Form =\n");
	system ('rsync -av --delete ' . SPONSORSHIP_FORM_PATH . ' ' . CMS_MODULE_PATH . ' 2>&1');
	print ("\n");


	print ("= Updating Source =\n");
	chdir (SRC_PATH);
	system ("git pull origin master");
	system ("git log -1");
	chdir ($cwd);
	print ("\n");

	print ("= Updating Theme =\n");
	chdir (THEME_PATH);
	system ("git reset --hard");
	system ("git pull origin master");
	system ("git log -1");
	chdir ($cwd);
	print ("\n");

	print ("= Syncing Theme =\n");
	system ('rsync -av --delete ' . THEME_PATH.'drupal/' . ' ' . CMS_THEME_PATH . ' 2>&1');
	print ("\n");
}

print ("= Updating GDoc =\n");
include ("update-gdoc-functions.php");
print ("\n");


recompile_and_sync();
