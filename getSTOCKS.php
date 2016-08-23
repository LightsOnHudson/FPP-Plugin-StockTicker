#!/usr/bin/php
<?php

error_reporting(0);

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
define('LOCK_SUFFIX', $pluginName.'.lock');

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

if($ENABLED != "ON" && $ENABLED != "1") {
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

$messageText="";
logEntry("Log Level: ".$LOG_LEVEL);

$quotesToGet = explode(",",trim(strtoupper($QUOTES)));

if($DEBUG)
	logEntry("Quotes to get: ".$QUOTES);

if($DEBUG)
	logEntry("Market type: ".$MARKET_TYPE);
$quoteIndex =0;

	foreach ($quotesToGet as $q) {
		if($DEBUG)
				logEntry("getting quote for: ".$q);
				
		//Obtain Quote Info - This collects the Microsoft Stock Info
		$quote = file_get_contents("http://finance.google.co.uk/finance/info?client=ig&q=".$MARKET_TYPE.":".$q);
		
		//Remove CR's from ouput - make it one line
		$json = str_replace("\n", "", $quote);
		
		//Remove //, [ and ] to build qualified string
		$data = substr($json, 4, strlen($json) -5);
		
		//decode JSON data
		$json_output = json_decode($data, true);
		
		
		//Un-remark print_r to see all array keys
		if($DEBUG)
			print_r($json_output);
		
		//Output Stock price array key.
		$stockValue = $json_output['l'];
		$stockChange = $json_output['c'];
		
		//echo "\nPrice: ".$stockValue;
		//echo "\nChange: ".$stockChange;
		
		if(count($quotesToGet)>1 && $quoteIndex > 0) {
			$messageText .= " | ";
		}
		$messageText .= " ".$q." ".$stockValue." [".$stockChange."]";
		
		$quoteIndex++;
		
		//if there is more than one stock ticker, then put a PIPE symbol on the string
	}
	
	if($MESSAGE_QUEUE_PLUGIN_ENABLED) {
		addNewMessage($messageText,$pluginName,$QUOTES);
	} else {
		logEntry("MessageQueue plugin is not enabled/installed: Cannot add message: ".$messageText);
	}
	
	
	
	if($IMMEDIATE_OUTPUT != "on" && $IMMEDIATE_OUTPUT != "1") {
		logEntry("NOT immediately outputting to matrix");
	} else {
		logEntry("IMMEDIATE OUTPUT ENABLED");
		logEntry("Matrix location: ".$MATRIX_LOCATION);
		logEntry("Matrix Exec page: ".$MATRIX_EXEC_PAGE_NAME);
	
		if($MATRIX_LOCATION != "127.0.0.1") {
			$remoteCMD = "/usr/bin/curl -s --basic 'http://".$MATRIX_LOCATION."/plugin.php?plugin=".$MATRIX_MESSAGE_PLUGIN_NAME."&page=".$MATRIX_EXEC_PAGE_NAME."&nopage=1' > /dev/null";
			logEntry("REMOTE MATRIX TRIGGER: ".$remoteCMD);
			exec($remoteCMD);
		} else {
			$IMMEDIATE_CMD = $settings['pluginDirectory']."/".$MATRIX_MESSAGE_PLUGIN_NAME."/matrix.php";
			logEntry("LOCAL command: ".$IMMEDIATE_CMD);
			exec($IMMEDIATE_CMD);
		}
	}
lockHelper::unlock();


?>
