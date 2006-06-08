<?
/*+-------------------------------------------------------------------------+
  | Copyright (C) 2004 Panousis Thanos - Dimopoulos Thimios                 |
  |                                                                         |
  | This program is free software; you can redistribute it and/or           |
  | modify it under the terms of the GNU General Public License             |
  | as published by the Free Software Foundation; either version 2          |
  | of the License, or (at your option) any later version.                  |
  |                                                                         |
  | This program is distributed in the hope that it will be useful,         |
  | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
  | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
  | GNU General Public License for more details.                            |
  +-------------------------------------------------------------------------+
  | WifiAdmin: The Free WiFi Web Interface				    |
  +-------------------------------------------------------------------------+
  | Send comments to  							    |
  | - panousis@ceid.upatras.gr						    |
  | - dimopule@ceid.upatras.gr						    |
  +-------------------------------------------------------------------------+*/

/**********************************************
* User can choose which device to  
* configure. Points to ifsettings.php
***********************************************/

include("./include/header.php");	//echo the default Header entries
include("./include/if_functions.php");
include("./include/ip_functions.php");

?>
<script src="include/submitonce.js"></script>
<script src="include/disabler.js"></script>
<h2>Network Settings</h2>

<? 
//check privileges
if ($_SESSION["access_ifs"]!="true"){
	echo "<p class = \"error\">You have no permission to access this section of WiFiAdmin</p>";
	exit();
}

$ethernet_statuses = get_ethernet_status();

$i=0;
foreach ( $ethernet_statuses as $device => $ethernet_status )
{
	$ifs[$i]=$device;
	$i+=1;
}        

//set default selected device and action
if(!isset($_GET['device']))
	$_GET['device'] = $ifs[0];

if(!isset($_GET['action']))
	$_GET['action'] = "show_device_status";

//make tabs
echo "\n<ul id=\"tabnav\">\n";
for($i=0;$i<sizeof($ifs);$i+=1)
{
        if($_GET['device'] == $ifs[$i] && ($_GET['action'] == "show_device_status" || $_GET['action'] == "save_device_changes"))
        	echo "<li id=\"active-tab\"><a href=\"".$_SERVER['PHP_SELF']."?device=".$ifs[$i]."\">".$ifs[$i]."</a></li>\n";      
        else
        	echo "<li class=\"tab\"><a href=\"".$_SERVER['PHP_SELF']."?device=".$ifs[$i]."\">".$ifs[$i]."</a></li>\n";
}
echo "</ul>";

switch ($_GET["action"]){
	case "save_device_changes":
	        $changes = 0;
		$selected_device = $_GET["device"];
		$device_status = $ethernet_statuses[$selected_device];

		echo "<p><ul>";
		if(isset($_GET["disableme"])) {
		  if(!get_disabled($selected_device)) {
		    echo "<li>Disabling DHCP server on <b>$selected_device</b></li>";
		    set_dhcpd($selected_device,false);
		    echo "<li>Disabling <b>$selected_device</b></li>";
		    set_disabled($selected_device,true);
		    $changes = 1;
		  }
		}
		else {
		  if(get_disabled($selected_device)) {
		    set_disabled($selected_device,false);
		    echo "<li>Enabling <b>$selected_device</b></li>";
		    $changes = 1;
		  }
		}
		
		// We're not disabled.  Are we changing IP or DHCP?		
		if(!get_disabled($selected_device)) {
		  if(isset($_GET["dhcp"]) && (!get_dhcp($selected_device))) {
		    echo "<li>Enabling DHCP client on <b>$selected_device</b></li>";
		    set_iface($selected_device,"dhcp",null,null,null,null);
		    $changes = 1;
		  } 
		  else {
		    foreach(array("ipaddr","mask","bcast","mtu","gateway") as $setting) {
		      // If one changes, set everything
		      if(isset($_GET[$setting]) && ($device_status[$setting] != $_GET[$setting])) {
		        echo "<li>Configuring IP settings for <b>$selected_device</b></li>";
		        set_iface($selected_device,$_GET["ipaddr"],$_GET["mask"],$_GET["bcast"],$_GET["mtu"],$_GET["gateway"]);
		        $changes = 1;
		        break;
		      }
		    }
		  }

		  if(isset($_GET["dhcpd"]) && (!get_dhcpd($selected_device))) {
		    echo "<li>Enabling DHCP server on <b>$selected_device</b></li>";
		    set_dhcpd($selected_device,true);
		    $changes = 1;
		  }
		  elseif(!isset($_GET["dhcpd"]) && (get_dhcpd($selected_device))) {
		    echo "<li>Disabling DHCP server on <b>$selected_device</b></li>";
		    set_dhcpd($selected_device,false);
		    $changes = 1;
		  }

		  if(isset($_GET["nat"]) && (!get_nat($selected_device))) {
		    echo "<li>Enabling Masquerading (NAT) on <b>$selected_device</b></li>";
		    set_nat($selected_device,true);
		    $changes = 1;
		  }
		  elseif(!isset($_GET["nat"]) && (get_nat($selected_device))) {
		    echo "<li>Disabling Masquerading (NAT) on <b>$selected_device</b></li>";
		    set_nat($selected_device,false);
		    $changes = 1;
		  }

		  // Any changes to the dhcpd conf?
		  if(isset($_GET["dhcpd"])) {
		    $dhchanges = 0;
		    if(!(isset($_GET["dhstart"]) && (isset($_GET["dhend"])) )) {
		      // error: need start and end
		    }
		    $dhcpd_conf = get_dhcpdconf($selected_device);
		    foreach(array_keys($dhcpd_conf) as $prop) {
		      if(!isset($_GET[$prop])) {
		        $dhchanges = 1;
		        break;
		      }
		      if($dhcpd_conf[$prop] != $_GET[$prop]) {
		        $dhchanges = 1;
		        break;
		      }
		    }
		    if($dhchanges) {
		      $dhcpd_conf = "";
		      foreach(array("dhstart","dhend","dhtime","dhgateway","dhdomain","dhsubnet","dhdns") as $prop) {
		        if(isset($_GET[$prop]))
		          $dhcpd_conf[$prop] = $_GET[$prop];
		      }
		      set_dhcpdconf($selected_device,$dhcpd_conf);
		      $changes = 1;
		    }
		  }
		} # if(!disabled)

		echo "</ul></p>";

		if($changes) {
		  echo "<br /><a href=\"".$_SERVER['PHP_SELF']."?device=$selected_device\">Back to Network Settings</a>";
		  break;
		}
		else {
		  $ethernet_statuses = get_ethernet_status();
		}

	case "show_device_status":
		// Print Device information
		$selected_device = $_GET["device"];
		$device_status = $ethernet_statuses[$selected_device];
		//ethernet ifs might have no ip assigned yet
		if ($device_status["ipaddr"] != ""){
			$array= ipmask($device_status["ipaddr"], $device_status["mask"]);
			if ($array == false)
				exit();
			$device_status["bcast"] = $array["bcast"];
		}
		else
		{
			$device_status["bcast"] = "";
			$device_status["mask"] = "";
			$device_status["mtu"] = "";
			$device_status["gateway"] = "";
		}
		$device_status["disabled"] = get_disabled($selected_device);
		$device_status["dhcp"] = get_dhcp($selected_device);
		$device_status["dhcpd"] = get_dhcpd($selected_device);
		$device_status["nat"] = get_nat($selected_device);

		$dhcpd_conf = get_dhcpdconf($selected_device);
		foreach(array("dhstart","dhend","dhtime","dhgateway","dhdomain","dhsubnet","dhdns") as $prop) {
		  if(isset($dhcpd_conf[$prop]))
		    $device_status[$prop] = $dhcpd_conf[$prop];
		  else
		    $device_status[$prop] = "";
		}
		?>

		<p><fieldset name="Network Settings">
		<form name="settings" onSubmit="submitonce(this)" action="<?echo $_SERVER['PHP_SELF']?>" method = "GET">
		<input  type="hidden"  name="device" value="<?echo $selected_device; ?>">
		<legend><input type="checkbox" name="disableme" value="on" <?echo ($device_status["disabled"] ? "checked" : "")?>>
		Disable <b><?echo $selected_device?></b></legend>

		<table width=100%><tr valign=top><td>
		<table>
		<tr><td>Use DHCP</td><td><input type="checkbox" name="dhcp" onclick="enabler(dhcp,ipaddr,mask,bcast,mtu,gateway)" <?echo ($device_status["dhcp"] ? "checked" : "")?> <?echo ($device_status["disabled"] ? "disabled" : "")?>></td></tr>
		<tr><td>IP address</td><td><input type="text" name="ipaddr" value="<?echo $device_status["ipaddr"]?>" <?echo (($device_status["dhcp"]|$device_status["disabled"]) ? "disabled" : "")?>></td></tr>
		<tr><td>Netmask</td><td><input type="text" name="mask" value="<?echo $device_status["mask"]?>" <?echo (($device_status["dhcp"]|$device_status["disabled"]) ? "disabled" : "")?>></td></tr>
		<tr><td>Broadcast</td><td><input type="text" name="bcast" value="<?echo $device_status["bcast"]?>" <?echo (($device_status["dhcp"]|$device_status["disabled"]) ? "disabled" : "")?>></td></tr>
		<tr><td>MTU</td><td><input type="text" name="mtu" value="<?echo $device_status["mtu"]?>" <?echo (($device_status["dhcp"]|$device_status["disabled"]) ? "disabled" : "")?>></td></tr>
		<tr><td>Gateway</td><td><input type="text" name="gateway" value="<?echo $device_status["gateway"]?>" <?echo (($device_status["dhcp"]|$device_status["disabled"]) ? "disabled" : "")?>></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>Masquerading (NAT)</td><td><input type="checkbox" name="nat" onclick="" <?echo ($device_status["nat"] ? "checked" : "")?> <?echo ($device_status["disabled"] ? "disabled" : "")?>></td></tr>
		<tr><td>for all outbound packets</td></tr>
		</table>

		</td><td>

		<table>
		<tr><td>Serve DHCP</td><td><input type="checkbox" name="dhcpd" onclick="disabler(dhcpd,dhtime,dhstart,dhend,dhgateway,dhdomain,dhsubnet,dhdns)" <?echo ($device_status["dhcpd"] ? "checked" : "")?> <?echo ($device_status["disabled"] ? "disabled" : "")?>></td></tr>
		<tr><td>IP range start</td><td><input type="text" name="dhstart" value="<?echo $device_status["dhstart"]?>" <?echo ((!$device_status["dhcpd"]|$device_status["disabled"] ? "disabled" : ""))?>></td></tr>
		<tr><td>IP range end</td><td><input type="text" name="dhend" value="<?echo $device_status["dhend"]?>" <?echo ((!$device_status["dhcpd"]|$device_status["disabled"] ? "disabled" : ""))?>></td></tr>
		<tr><td>Subnet</td><td><input type="text" name="dhsubnet" value="<?echo $device_status["dhsubnet"]?>" <?echo ((!$device_status["dhcpd"]|$device_status["disabled"] ? "disabled" : ""))?>></td></tr>
		<tr><td>Gateway</td><td><input type="text" name="dhgateway" value="<?echo $device_status["dhgateway"]?>" <?echo ((!$device_status["dhcpd"]|$device_status["disabled"] ? "disabled" : ""))?>></td></tr>
		<tr><td>Lease time</td><td><input type="text" name="dhtime" value="<?echo $device_status["dhtime"]?>" <?echo ((!$device_status["dhcpd"]|$device_status["disabled"] ? "disabled" : ""))?>></td></tr>
		<tr><td>DNS domain</td><td><input type="text" name="dhdomain" value="<?echo $device_status["dhdomain"]?>" <?echo ((!$device_status["dhcpd"]|$device_status["disabled"] ? "disabled" : ""))?>></td></tr>
		<tr><td valign="top">DNS servers</td><td><textarea name="dhdns" cols="20" rows="4" wrap="off" <?echo ((!$device_status["dhcpd"]|$device_status["disabled"] ? "disabled" : ""))?>><?echo $device_status["dhdns"]?></textarea></td></tr>
		</table>
		
		</td></tr>
		</table>

		</p><p>

		<table>
		<tr><td><b>Current settings</b></td></tr>
		<tr><td>Ethernet address:</td><td><?echo $device_status["hwaddr"]?></td></tr>
		<tr><td>IP address:</td><td><?echo ($device_status["UP"] ? $device_status["ipaddr"] : "")?></td></tr>
		<tr><td>Netmask:</td><td><?echo ($device_status["UP"] ? $device_status["mask"] : "")?></td></tr>
		<tr><td>Broadcast:</td><td><?echo ($device_status["UP"] ? $device_status["bcast"] : "")?></td></tr>
		<tr><td>MTU:</td><td><?echo ($device_status["UP"] ? $device_status["mtu"] : "")?></td></tr>
		<tr><td>Gateway:</td><td><?echo ($device_status["UP"] ? $device_status["gateway"] : "")?></td></tr>
		<?
		// If it's a wireless interface, show some more data
		$iw_data = get_wireless_status();
		if(array_key_exists ($selected_device,$iw_data)) {
		  $current = $iw_data[$selected_device];
		  echo "<tr><td></td></tr>\n";
		  echo "<tr><td>ESSID:</td><td>".$current['essid']."</td></tr>\n";
		  echo "<tr><td>Mode:</td><td>".$current['mode']."</td></tr>\n";
		}
		?>
		</table>
		
		</fieldset>
		</p>
		<p>
		<input type ="hidden" name="action" value = "save_device_changes">
		<input type="submit" value="Commit Changes" onclick="return confirm('*** Warning *** \nIncorrect network settings can make this machine unreachable.  Are you sure you want to commit these settings?')">
		</form>
<?
}


include("./include/footer.php");
?>
