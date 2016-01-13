<?php
$DEBUG=false;

include_once "common.php";
include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';
$pluginName = "StockTicker";

$gitURL = "https://github.com/LightsOnHudson/FPP-Plugin-".$pluginName.".git";


$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;
if (file_exists($pluginConfigFile))
	$pluginSettings = parse_ini_file($pluginConfigFile);


	if(urldecode($pluginSettings['DEBUG'] != "" || urldecode($pluginSettings['DEBUG'] != 0))) {
		$DEBUG=urldecode($pluginSettings['DEBUG']);
	}

	foreach ($pluginSettings as $key => $value) {

		if($DEBUG) {
			echo "Key: ".$key." " .$value."\n";
		}
			$$key = urldecode($value);

	}
	
$PLAYLIST_NAME="";
$MAJOR = "98";
$MINOR = "01";
$eventExtension = ".fevt";
//arg0 is  the program
//arg1 is the first argument in the registration this will be --list
//$DEBUG=true;

$messageQueue_Plugin = "MessageQueue";
$MESSAGE_QUEUE_PLUGIN_ENABLED=false;


$logFile = $settings['logDirectory']."/".$pluginName.".log";



$messageQueuePluginPath = $settings['pluginDirectory']."/".$messageQueue_Plugin."/";

$messageQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE",$messageQueue_Plugin));

if(file_exists($messageQueuePluginPath."functions.inc.php"))
{
	include $messageQueuePluginPath."functions.inc.php";
	$MESSAGE_QUEUE_PLUGIN_ENABLED=true;

} else {
	logEntry("Message Queue Plugin not installed, exiting");
	echo "Message Queue plugin not installed, please install this pre-requisite first";
//	exit(1);
}





$pluginUpdateFile = $settings['pluginDirectory']."/".$pluginName."/"."pluginUpdate.inc";


//logEntry("plugin update file: ".$pluginUpdateFile);


if(isset($_POST['updatePlugin']))
{
	$updateResult = updatePluginFromGitHub($gitURL, $branch="master", $pluginName);

	echo $updateResult."<br/> \n";
}


if(isset($_POST['submit']))
{
	


//	echo "Writring config fie <br/> \n";
	
	WriteSettingToFile("ENABLED",urlencode($_POST["ENABLED"]),$pluginName);
	WriteSettingToFile("LAST_READ",urlencode($_POST["LAST_READ"]),$pluginName);
	WriteSettingToFile("API_USER_ID",urlencode($_POST["API_USER_ID"]),$pluginName);
	WriteSettingToFile("API_KEY",urlencode($_POST["API_KEY"]),$pluginName);
	WriteSettingToFile("IMMEDIATE_OUTPUT",urlencode($_POST["IMMEDIATE_OUTPUT"]),$pluginName);
	WriteSettingToFile("MATRIX_LOCATION",urlencode($_POST["MATRIX_LOCATION"]),$pluginName);
	WriteSettingToFile("MARKET_TYPE",urlencode($_POST["MARKET_TYPE"]),$pluginName);
	WriteSettingToFile("QUOTES",urlencode($_POST["QUOTES"]),$pluginName);

}

if (file_exists($pluginConfigFile)) {
	$pluginSettings = parse_ini_file($pluginConfigFile);


	if(urldecode($pluginSettings['DEBUG'] != "" || urldecode($pluginSettings['DEBUG'] != 0))) {
		$DEBUG=urldecode($pluginSettings['DEBUG']);
	}

		foreach ($pluginSettings as $key => $value) {

			if($DEBUG) {
				echo "Key: ".$key." " .$value."\n";
			}
				$$key = urldecode($value);
			}
	}  else {
		logEntry("No plugin config file plugin.".$pluginName." exists");
	}

	
	if((int)$LAST_READ == 0 || $LAST_READ == "") {
		$LAST_READ=0;
	}

?>

<html>
<head>
</head>

<div id="StockTicker" class="settings">
<fieldset>
<legend>Stock Ticker Support Instructions</legend>

<p>Known Issues:
<ul>
<li>None at this time</li>
</ul>

<p>Configuration:
<ul>
<li>Configure your API Key and/or username</li>
<li>Enter in the quotes that you want data on separated by commas</li>
<li>Select the market type: NASDAQ, etc</li>
</ul>
<ul>
<li>Add the crontabAdd options to your crontab to have the plugin run every X minutes to process commands</li>

</ul>



<form method="post" action="http://<? echo $_SERVER['SERVER_NAME']?>/plugin.php?plugin=<?echo $pluginName;?>&page=plugin_setup.php">


<?
//will add a 'reset' to this later

echo "<input type=\"hidden\" name=\"LAST_READ\" value=\"".$LAST_READ."\"> \n";


$restart=0;
$reboot=0;

echo "ENABLE PLUGIN: ";

if($ENABLED== 1 || $ENABLED == "on") {
		echo "<input type=\"checkbox\" checked name=\"ENABLED\"> \n";
//PrintSettingCheckbox("Radio Station", "ENABLED", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
	} else {
		echo "<input type=\"checkbox\"  name=\"ENABLED\"> \n";
}

echo "<p/> \n";
echo "Immediately output to Matrix (Run MATRIX plugin): ";

if($IMMEDIATE_OUTPUT == "on" || $IMMEDIATE_OUTPUT == 1) {
	echo "<input type=\"checkbox\" checked name=\"IMMEDIATE_OUTPUT\"> \n";
	//PrintSettingCheckbox("Radio Station", "ENABLED", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
} else {
	echo "<input type=\"checkbox\"  name=\"IMMEDIATE_OUTPUT\"> \n";
}
echo "<p/> \n";
?>
MATRIX Message Plugin Location: (IP Address. default 127.0.0.1);
<input type="text" size="15" value="<? if($MATRIX_LOCATION !="" ) { echo $MATRIX_LOCATION; } else { echo "127.0.0.1";}?>" name="MATRIX_LOCATION" id="MATRIX_LOCATION"></input>
<p/>
<?

echo "<p/> \n";
echo "QUOTES (Comma Separated): \n";

echo "<input type=\"text\" name=\"QUOTES\" size=\"32\" value=\"".$QUOTES."\"> \n";

echo "<p/> \n";

echo "Market Type: \n";
echo "<select name=\"MARKET_TYPE\"> \n";
	if($MARKET_TYPE !="" ) {
              switch ($MARKET_TYPE)
				{
					case "NASDAQ":
                                		echo "<option selected value=\"".$MARKET_TYPE."\">".$MARKET_TYPE."</option> \n";
                                		echo "<option value=\"LSE\">LSE</option> \n";
                                		break;
					case "LSE":
                                		echo "<option selected value=\"".$MARKET_TYPE."\">London Stock Exchange(LSE)</option> \n";
                                		echo "<option value=\"NASDAQ\">NASDAQ</option> \n";
                        			break;
			
				
	
				}
	
			} else {

                                echo "<option value=\"NASDAQ\">NASDAQ</option> \n";
                                echo "<option value=\"LSE\">LSE</option> \n";
			}
               
			echo "</select> \n";
echo "<p/> \n";

?>
<p/>
<input id="submit_button" name="submit" type="submit" class="buttons" value="Save Config">
<?
 if(file_exists($pluginUpdateFile))
 {
 	//echo "updating plugin included";
	include $pluginUpdateFile;
}
?>
</form>


<form method="post" action="http://<? echo $_SERVER['SERVER_NAME']?>/plugin.php?plugin=<?echo $pluginName;?>&page=stockMessageManagement.php">
<input id="MessageManagementButton" name="Stock Message Management" type="submit" value="Stock Message Management">
</form>




<p>To report a bug, please file it against the sms Control plugin project on Git:<? echo $gitURL;?> 
</fieldset>
</div>
<br />
</html>
