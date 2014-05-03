<?php
include_once("deploy.php");

print ("= Updating Marksite =\n");
chdir (MARKSITE_PATH);
system ("git fetch origin");
system ("git checkout -q origin/master");
system ("git log -1");
chdir ($cwd);
print ("\n");

recompile_and_sync();
