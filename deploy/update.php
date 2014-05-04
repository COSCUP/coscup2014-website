<?php
include_once ("deploy.php");

print ("= Reverting Source =\n");
chdir (SRC_PATH);
// system ("git reset --hard");
system ("git log -1");
chdir ($cwd);
print ("\n");

// Copy all source files to a src tmp folder
system ('rsync -a --delete ' . SRC_PATH . ' ' . SRC_TMP_PATH);

print ("= Updating Dynamic Content (from Google Docs) =\n");
include ("update-dynamic-content.php");
print ("\n");

recompile_and_sync();

