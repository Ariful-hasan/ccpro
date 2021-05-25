<?php

//readfile("http://192.168.10.8/gplexcc/dashboard_data.php");
//readfile("http://58.65.224.8/gplexcc/dashboard_data.php");
readfile("http://192.168.10.67/dashboard_data.php");
exit;
error_reporting(7);
include("./dashvar/var.txt");
if(file_exists("./dashvar/tmp.txt")) unlink("./dashvar/tmp.txt");
exit;
