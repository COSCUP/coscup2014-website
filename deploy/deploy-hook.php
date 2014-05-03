<?php
include_once("config.php");
include_once("deploy.php");

print ("= Updating Deploy Script =\n");
system ("git fetch origin");
system ("git reset --hard");
system ("git checkout -q origin/git");
system ("git log -1");
print ("\n");
