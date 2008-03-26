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
		$ethernet_statuses = get_ethernet_status();
		$wifidog_ints=array();
		$wifidog_ints=get_wifidog_ints();
		
		
		
?>		<form onSubmit="submitonce(this)" action="<?echo $_SERVER['PHP_SELF']?>" method="GET">
		<p><fieldset title="WifiDog Settings">
		<legend>WifiDog Settings</legend>
		<table>
		<tr><td>Enable WifiDog Captive Portal</td><td><input type="checkbox" name="wifidog" value="on" <?if(get_rc("S99wifidog")) echo 'checked';?>></td></tr>
		<?$wifidog_ints=get_wifidog_ints();?>
		<tr><td>Internet Interface: 
		<? 
		$i = 0;
		foreach ( $ethernet_statuses as $device => $ethernet_status ) {
		  echo "<input type='radio' name='wifidog_ext_if' value='$device'";
		  if($device == $wifidog_ints[0]) echo 'checked';
		  echo "> $device &nbsp;&nbsp; ";
		  $i++;
		}
		?>
		</td></tr>
		<tr><td>Gateway Interface: 
		<?
		$i = 0;
		foreach ( $ethernet_statuses as $device => $ethernet_status ) {
		  echo "<input type='radio' name='wifidog_gw_if' value='$device'";
		  if($device == $wifidog_ints[1]) echo 'checked';
		  echo "> $device &nbsp;&nbsp; ";
		  $i++;
		}
		?>
		</td></tr>
		<tr><td><?$wifidog_auth_servers=get_wifidog_auth_servers()?></td></tr>
		<tr><td>Authserver <input type="textarea" name="authserverhostname" value="<? if(array_key_exists('Hostname', $wifidog_auth_servers)) echo $wifidog_auth_servers['Hostname'];?>"><td></tr>
		<tr><td>Server Path: <input type="textarea" name="authserverpath" value="<? if(array_key_exists('Path', $wifidog_auth_servers)) echo $wifidog_auth_servers['Path'];?>"></td></tr>
		<tr><td>Authserver has SSL enabled: yes<input type="radio" name='authserverssl' value="yes" <?if($wifidog_auth_servers['SSLAvailable'] == 'yes') echo 'checked'; ?> >  no<input type="radio" name='authserverssl' value="no" <?if($wifidog_auth_servers['SSLAvailable'] == 'no') echo 'checked'; ?> ></td></tr>
		</table>
		
    	<?// print_r($wifidog_auth_servers);?>
		
		
		</fieldset></p>

		
		
		
		
		
		
		
	
		<input type ="hidden" name="action" value = "save_captiveportal_settings">
		<input type="submit" value="Commit Changes">
		</form><?
		break;
	case "save_captiveportal_settings":
		echo "<h5>Saving captive portal settings...</h5><ul>";

		
		$ifs = "";
		foreach ( array_keys($_GET) as $param) {
		   
		    if(substr($param,0,14) == "wifidog_ext_if") {
		    $ifs = "$_GET[$param]";
		   
		  }
		}
		
		if($ifs) {
		  echo "<li>Updating Wifidog Internet Interface</li>";
	          $ifline = "ExternalInterface $ifs";
		  file_replace("/etc/wifidog.conf","#ExternalInterface .*", "$ifline\n");
	        
	         $ifline = "ExternalInterface $ifs";
		  file_replace("/etc/wifidog.conf","ExternalInterface .*", "$ifline\n");
	        }  
		
		
		$ifs = "";
		foreach ( array_keys($_GET) as $param) {
		  if(substr($param, 0, 13) == "wifidog_gw_if") {
		    $ifs = "$_GET[$param]";
		   
		  }
		}
		
		if($ifs) {
		  echo "<li>Updating Wifidog Gateway Interface</li>";
	          $ifline = "GatewayInterface $ifs";
		  file_replace("/etc/wifidog.conf","#GatewayInterface .*", "$ifline\n");
	        
	         $ifline = "GatewayInterface $ifs";
		  file_replace("/etc/wifidog.conf","GatewayInterface .*", "$ifline\n");
	        }  
		
		if(isset($_GET["authserverhostname"])) {
		    $wifidogconfline = "    Hostname $_GET[authserverhostname]" ;
		    $newwifidogconfline = trim($wifidogconfline); 
		    file_replace("/etc/wifidog.conf","    Hostname .*", "$newwifidogconfline \n");
		    $wifidogconfline = "";
		    $newwifidogconfline = "";
		}
		
		if(isset($_GET["authserverpath"])) {
			$wifidogconfline = "    Path $_GET[authserverpath]" ;
		        $newwifidogconfline = trim($wifidogconfline);
		        file_replace("/etc/wifidog.conf","    Path .*", "$newwifidogconfline \n");
			$wifidogconfline;
		        $newwifidogconfline = "";
		}
		
		if(isset($_GET["authserverssl"])) {
			$wifidogconfline = "    SSLAvailable $_GET[authserverssl]" ;
	        	$newwifidogconfline = trim($wifidogconfline);
		    file_replace("/etc/wifidog.conf","    SSLAvailable .*", "$newwifidogconfline \n");
			$wifidogconfline;
			$newwifidogconfline = "";
		}
		
		
		
		if(isset($_GET["wifidog"])) {
		  if(!get_rc("S99wifidog")) {
		    echo "<li>Enabling WifiDog daemon.</li>";
		    $wifidog_status = set_rc("wifidog",99);
		    if (ereg('OK', $wifidog_status)) {
			echo "<li>WifiDog started successfully</li>";
			} 
		    else {
			echo "<li>Error enabling WifiDog, please check /etc/wifidog.conf</li>";
			//sleep togive wifidog "start" time to finish before stopping
			sleep(7);
			set_rc("wifidog",0);
			}
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
