<?php
include_once("deploy.php");

print ("= Updating Source =\n");
chdir (SRC_PATH);
system ("git fetch origin");
system ("git reset --hard");
system ("git checkout -q origin/master");
system ("git log -1");
chdir ($cwd);
print ("\n");


print ("= Updating GDoc =\n");
include ("update-gdoc-functions.php");
print ("\n");

recompile_and_sync();
