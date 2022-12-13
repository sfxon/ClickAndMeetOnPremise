<?php

///////////////////////////////////////////////////////////////////////////////
// @title		Blitz - Business Logic.
// @author	Steve Kraemer
// @address	
// @created	2015-12-12
// @info		Base file. This is the starting point of the application.
// @license	All rights reserved.
//////////////////////////////////////////////////////////////////////////////
date_default_timezone_set('Europe/Berlin');

$measure_begin = microtime(true);


//error_reporting(E_ALL);
//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
//ini_set("display_errors", 1);


require_once('core/cCore.class.php');

$cCore = new cCore();
$cCore->run();

/*
$measure_end = microtime(true);
$difference = $measure_end - $measure_begin;
echo 'end:';
var_dump($difference);

*/

?>