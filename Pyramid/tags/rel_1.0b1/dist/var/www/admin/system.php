<?
/**********************************************
* system.php
* System settings (ntp server, hostname, etc.)
***********************************************/

include("./include/header.php");	//echo the default Header entries

$zones = array(
"US/Alaska", "US/Pacific", "US/Hawaii", "US/Samoa", "US/Michigan",
"US/Arizona", "US/Mountain", "US/Central", "US/East-Indiana", "US/Aleutian",
"US/Eastern", "US/Indiana-Starke", "UTC", "Greenwich", "WET", "CET", "MET",
"EET", "EST5EDT", "CST6CDT", "MST7MDT", "PST8PDT", "EST", "MST", "HST",
"Etc/GMT-14", "Etc/GMT", "Etc/UTC", "Etc/UCT", "Etc/Universal",
"Etc/GMT-13", "Etc/GMT-12", "Etc/GMT-11", "Etc/GMT-10", "Etc/GMT-9",
"Etc/GMT-8", "Etc/GMT-7", "Etc/GMT-6", "Etc/GMT-5", "Etc/GMT-4",
"Etc/GMT-3", "Etc/GMT-2", "Etc/GMT-1", "Etc/GMT+1", "Etc/GMT+2",
"Etc/GMT+3", "Etc/GMT+4", "Etc/GMT+5", "Etc/GMT+6", "Etc/GMT+7",
"Etc/GMT+8", "Etc/GMT+9", "Etc/GMT+10", "Etc/GMT+11", "Etc/GMT+12",
"Etc/Greenwich", "Etc/Zulu", "Etc/GMT-0", "Etc/GMT+0", "Etc/GMT0", "GMT",
"Factory", "GB", "GMT+0", "GMT-0", "GMT0", "Hongkong", "Iceland", "Iran",
"Israel", "Jamaica", "Japan", "Kwajalein", "Libya", "Navajo", "NZ",
"NZ-CHAT", "Poland", "Portugal", "PRC", "ROC", "ROK", "Singapore", "Turkey",
"UCT", "Universal", "W-SU", "SystemV/AST4ADT", "SystemV/EST5EDT",
"SystemV/CST6CDT", "SystemV/MST7MDT", "SystemV/PST8PDT", "SystemV/YST9YDT",
"SystemV/AST4", "SystemV/EST5", "SystemV/CST6", "SystemV/MST7",
"SystemV/PST8", "SystemV/YST9", "SystemV/HST10"
);

?>

<script src="include/submitonce.js"></script>
<h2>System Services</h2>
<p>
<? 
//check privileges
if ($_SESSION["access_ifs"]!="true"){
	echo "<p class = \"error\">You have no permission to access this section of WiFiAdmin</p>";
	exit();
}

// default action
if(!isset($_GET['action']))
	$_GET['action'] = "show_system_settings";

switch ($_GET["action"]){
	case "show_system_settings":
?>		<form onSubmit="submitonce(this)" action="<?echo $_SERVER['PHP_SELF']?>" method="GET">
		<p><fieldset title="System Settings">
		<legend>System settings</legend>
		<table>
		<tr><td>Hostname</td><td><input type="text" name="hostname" value="<?echo chostname()?>"></td></tr>
		<tr><td>Domain</td><td><input type="text" name="domain" value="<?echo get_domain()?>"></td></tr>
		<tr><td>SSH server</td><td><input type="checkbox" name="sshd" value="on" <?if(get_rc("S20ssh")) echo 'checked';?>></td></tr>
		<tr><td valign="top">SSH keys</td><td><textarea name="sshkeys" cols="40" rows="4" wrap="off"><?echo get_sshkeys()?></textarea></td></tr>
		</table>
		</fieldset></p>

		<p><fieldset title="Cellular Network Support">
		<legend>Cellular Network</legend>
		Cellular modem detected: <b><?echo get_modem();?></b> <i><?echo get_ppp_status();?></i>
		</fieldset></p>
		
		<p><fieldset title="Dynamic DNS">
		<legend>Dynamic DNS</legend>
		<input type="text" name="dyndns" size="50" maxlength="255" value="<?echo get_dyndns();?>">
		<input type="checkbox" name="dyndnsupdate" value="1">Update now
		</fieldset></p>
		
		<p><fieldset title="Time &amp; Date">
		<legend>Time &amp; Date</legend>
		<table>
		<tr><td>Set clock using NTP</td><td><input type="checkbox" name="ntpd" value="on" <?if(get_rc("S15ntpd")) echo 'checked';?>></td></tr>
		<tr><td>Timezone</td><td><select name="timezone">
<?
	foreach($zones as $zone) {
	  if($zone == get_zone()) {
	    echo "<option selected>$zone</option>";
	  }
	  else {
	    echo "<option>$zone</option>";
	  }
	} 
?>		</select></td></tr>
		<tr><td>Current date</td><td><?print `date`?></td></tr>
		</table>
		</fieldset></p>

		<input type ="hidden" name="action" value = "save_system_settings">
		<input type="submit" value="Commit Changes">
		</form><?
		break;
	case "save_system_settings":
		echo "<h5>Saving System settings...</h5><ul>";

		if(isset($_GET["hostname"])) {
		  $hostname = escapeshellarg($_GET["hostname"]);
		  if($hostname != "'".chostname()."'") {
		    set_hostname($hostname);
		    echo "<li>Hostname set to <b>".$hostname."</b>.</li>";
		  }
		}

		if(isset($_GET["domain"])) {
		  $domain = $_GET["domain"];
		  if($domain != get_domain()) {
		    set_domain($domain);
		    echo "<li>Domain set to <b>".$domain."</b>.</li>";
		  }
		}

		if(isset($_GET["ntpd"])) {
		  if(!get_rc("S15ntpd")) {
		    echo "<li>Enabling NTP daemon.</li>";
		    set_rc("ntpd",15);
		  }
		}
		else {
		  if(get_rc("S15ntpd")) {  
		    echo "<li>Disabling NTP daemon.</li>";
		    set_rc("ntpd",0);
		  }
		}
	
		if(isset($_GET["sshd"])) {
		  if(!get_rc("S20ssh")) {
		    echo "<li>Enabling SSH daemon.</li>";
		    set_rc("ssh",20);
		  }
		}
		else {
		  if(get_rc("S20ssh")) {  
		    echo "<li>Disabling SSH daemon.</li>";
		    set_rc("ssh",0);
		  }
		}
	
/*		if(isset($_GET["dhcpd"])) {
		  if(!get_rc("S20dhcp")) {
		    echo "<li>Enabling dhcp server.</li>";
		    set_rc("dhcp",20);
		  }
		}
		else {
		  if(get_rc("S20dhcp")) {  
		    echo "<li>Disabling dhcp server.</li>";
		    set_rc("dhcp",0);
		  }
		}
*/
		if(isset($_GET["bind"])) {
		  if(!get_rc("S12bind")) {
		    echo "<li>Enabling caching DNS server.</li>";
		    set_rc("bind",12);
		  }
		}
		else {
		  if(get_rc("S12bind")) {  
		    echo "<li>Disabling caching DNS server.</li>";
		    set_rc("bind",0);
		  }
		}
	
		if(isset($_GET["modem"])) {
		  if($_GET["modem"] != get_modem()) {
		    // Only accept zones that are in the array
		    foreach($modems as $modem) {
		      if($modem == $_GET["modem"]) {
		        if($modem == "None") {
		          echo "<li>Disabling cellular modem support.</li>";
		        }
		        else {
		      	  echo "<li>Enabling <b>".$modem."</b> cellular modem support.</li>";
		      	}
		        set_modem($modem);
		      }
		    }
		  }
		}
		
		if(isset($_GET["dyndns"]) && ($_GET["dyndns"] != "")) {
		  if(get_dyndns() != $_GET["dyndns"]) {
		    echo "<li>Setting up dyndns</li>";
		    set_dyndns($_GET["dyndns"]);
		  }
		}

		if(isset($_GET["dyndnsupdate"]) && ($_GET["dyndnsupdate"] != "")) {
		  echo "<li>Sending dyndns update</li>";
	          update_dyndns();
		}

		if(isset($_GET["timezone"])) {
		  if($_GET["timezone"] != get_zone()) {
		    // Only accept zones that are in the array
		    foreach($zones as $zone) {
		      if($zone == $_GET["timezone"]) {
		        set_zone($zone);
		      	echo "<li>Setting timezone to <b>".$zone."</b>.</li>";
		      }
		    }
		  }
		}
		
		if(isset($_GET["sshkeys"])) {
		  if(set_sshkeys($_GET["sshkeys"])) {
		    echo "<li>Installing new SSH keys for <b>root</b></li>";
		  }
		}
		
		echo '</ul><p><a href="'.$_SERVER['PHP_SELF'].'">Back to System Settings</a>';
		break;
}


include("./include/footer.php");
?>
