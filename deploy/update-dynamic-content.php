<?php
include_once("markdown-without-markup.php");
include_once("utils.php");

date_default_timezone_set('Asia/Taipei');
setlocale(LC_ALL, "en_US.UTF-8");

print("== Generating Sponsors ==\n");
include("compile-sponsor.php");
print("\n");

print("== Generating News ==\n");
include("compile-news.php");
print("\n");

print("== Generating Program ==\n");
include("compile-program.php");
write_program_files($program_sheets, $program_list_output, $json_output);
print("\n");

print("== Generating Live Streaming ==\n");
include("compile-live.php");
print("\n");

