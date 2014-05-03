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

print ("= Updating GDoc =\n");
include ("update-gdoc-functions.php");
print ("\n");

recompile_and_sync();

// Copy additional files
system ('rsync -a ./files/ ' . WEBSITE_PATH . '/logos');

// Remove en and zh-cn temporarily
// system ('rm -rf ' . WEBSITE_PATH . '/en ' . WEBSITE_PATH . '/zh-cn');
