#!/usr/bin/php
<?php

//error_reporting(0);

$pluginName ="StockTicker";
$myPid = getmypid();

$messageQueue_Plugin = "MessageQueue";
$MESSAGE_QUEUE_PLUGIN_ENABLED=false;

$DEBUG=false;
$LOG_LEVEL=0;
$skipJSsettings = 1;
$fppWWWPath = '/opt/fpp/www/';
set_include_path(get_include_path() . PATH_SEPARATOR . $fppWWWPath);

$ENABLED="";



require("common.php");

include_once("functions.inc.php");
include_once("commonFunctions.inc.php");

require ("lock.helper.php");

$logFile = $settings['logDirectory']."/".$pluginName.".log";

$messageQueuePluginPath = $pluginDirectory."/".$messageQueue_Plugin."/";

$messageQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE",$messageQueue_Plugin));

if(file_exists($messageQueuePluginPath."functions.inc.php"))
        {
                include $messageQueuePluginPath."functions.inc.php";
                $MESSAGE_QUEUE_PLUGIN_ENABLED=true;

        } else {
                logEntry("Message Queue Plugin not installed, some features will be disabled");
        }



define('LOCK_DIR', '/tmp/');
define('LOCK_SUFFIX', '.lock');

$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;
if (file_exists($pluginConfigFile))
	$pluginSettings = parse_ini_file($pluginConfigFile);


	if(urldecode($pluginSettings['DEBUG'] != "" || urldecode($pluginSettings['DEBUG'] != 0))) {
		$DEBUG=urldecode($pluginSettings['DEBUG']);
	}
	
foreach ($pluginSettings as $key => $value) {

	if($DEBUG)
		echo "Key: ".$key." " .$value."\n";

	$$key = urldecode($value);

}



if(($pid = lockHelper::lock()) === FALSE) {
	exit(0);

}

if($ENABLED != "on" && $ENABLED != "1") {
	logEntry("Plugin Status: DISABLED Please enable in Plugin Setup to use & Restart FPPD Daemon");
	lockHelper::unlock();
	exit(0);
}

$MATRIX_MESSAGE_PLUGIN_NAME = "MatrixMessage";
//page name to run the matrix code to output to matrix (remote or local);
$MATRIX_EXEC_PAGE_NAME = "matrix.php";

	
	

$LOG_LEVEL = getFPPLogLevel();
logEntry("Log level in translated from fpp settings file: ".$LOG_LEVEL);



$logFile = $settings['logDirectory']."/".$pluginName.".log";



if($DEBUG){
	logEntry("________________________");
	logEntry("Plugin Settings");
	logEntry("________________________");
	
	while (list($key, $val) = each($pluginSettings)) {
		logEntry("$key => $val");
	}
	
}

logEntry("Log Level: ".$LOG_LEVEL);




lockHelper::unlock();


?>
