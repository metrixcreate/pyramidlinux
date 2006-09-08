<?
/**********************************************
* system.php
* System settings (ntp server, hostname, etc.)
***********************************************/

include("./include/header.php");	//echo the default Header entries



?>

<script src="include/submitonce.js"></script>
<h2>Captive Portals</h2>
<p>
<? 
//check privileges
if ($_SESSION["access_ifs"]!="true"){
	echo "<p class = \"error\">You have no permission to access this section of WiFiAdmin</p>";
	exit();
}

// default action
if(!isset($_GET['action']))
	$_GET['action'] = "show_captiveportal_settings";

switch ($_GET["action"]){
	case "show_captiveportal_settings":
?>		<form onSubmit="submitonce(this)" action="<?echo $_SERVER['PHP_SELF']?>" method="GET">
		<p><fieldset title="WifiDog Settings">
		<legend>WifiDog Settings</legend>
		<table>
		<tr><td>WifiDog Captive Portal</td><td><input type="checkbox" name="wifidog" value="on" <?if(get_rc("S99wifidog")) echo 'checked';?>></td></tr>
		</table>
		</fieldset></p>

	
		<input type ="hidden" name="action" value = "save_captiveportal_settings">
		<input type="submit" value="Commit Changes">
		</form><?
		break;
	case "save_captiveportal_settings":
		echo "<h5>Saving captive portal settings...</h5><ul>";

		
		if(isset($_GET["wifidog"])) {
		  if(!get_rc("S99wifidog")) {
		    echo "<li>Enabling WifiDog daemon.</li>";
		    set_rc("wifidog",99);
		  }
		}
		else {
		  if(get_rc("S99wifidog")) {  
		    echo "<li>Disabling WifiDog daemon.</li>";
		    set_rc("wifidog",0);
		  }
		}
		echo '</ul><p><a href="'.$_SERVER['PHP_SELF'].'">Back to captive portal settings</a>';
		break;
}


include("./include/footer.php");
?>
