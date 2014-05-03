<?php
include_once("deploy.php");

print ("= Updating Theme =\n");
chdir (THEME_PATH);
system ("git fetch origin");
system ("git reset --hard");
system ("git checkout -q origin/master");
system ("git log -1");
chdir ($cwd);
print ("\n");

print ("= Syncing Theme =\n");
system ('rsync -av --delete ' . THEME_PATH.'drupal/' . ' ' . CMS_THEME_PATH . ' 2>&1');
print ("\n");

recompile_and_sync();
