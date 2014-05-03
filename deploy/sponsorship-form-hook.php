<?php
include_once("deploy.php");

print ("= Updating Sponsorship Form =\n");
chdir (SPONSORSHIP_FORM_PATH);
system ("git pull origin master");
system ("git log -1");
chdir ($cwd);
print ("\n");

print ("= Syncing Sponsorship Form =\n");
system ('rsync -av --delete ' . SPONSORSHIP_FORM_PATH . ' ' . CMS_MODULE_PATH . ' 2>&1');
print ("\n");
?>
