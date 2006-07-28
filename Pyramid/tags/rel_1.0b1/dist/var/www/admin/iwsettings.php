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
* Script produces a form for the  
* user to apply new settings to a specific 
* wireless device
***********************************************/

include("./include/if_functions.php");

/*********************************************************
* set_iwconfig_params($post): 				
* Make it so.
**********************************************************/
function set_iwconfig_params() {
  if(!ctype_alnum($_POST['device']))
    exit("Illegal characters found in device name string: ".$_POST['device'].", exiting...");
  
  $cmd = "";
  $extra_cmds = array();
  $wif = $_POST["device"];

  echo "<p>Changing settings for device <b>$wif</b></p><p>\n";
    
  // ESSID
  $essid = escapeshellcmd($_POST['essid']);
  // escapeshellcmd does strange escaping of ', and we don't need &.
  $essid = str_replace("\\\\\\'","'",$essid);
  $essid = str_replace("\&","&",$essid);
  $cmd = "essid \"$essid\"";

  // Mode set by wlanconfig for ath*
  if(!strstr($wif,"ath")) {
    if(!ereg("(Master|Managed|Ad-Hoc|Monitor) ?",$_POST['mode'],$regs))
      exit("Bad mode: ".$_POST['mode'].", exiting.");
    $cmd .= " mode $regs[1]";
  }
  
  // Channel.  Don't set it for Managed mode.
  if(!strstr($_POST['mode'],"Managed")) {
    if(!ereg("Channel ([[:digit:]]+)",$_POST['channel'],$regs))
      exit("Bad channel: ".$_POST['channel'].", exiting.");
    $cmd .= " channel $regs[1]";
  }
  
  // Rate
  if($_POST['rate'] == "best")
    $cmd .= " rate auto";
  else {
    if(!ereg("([[:digit:]]\.?[[:digit:]]?[[:digit:]]?) Mb/s",$_POST['rate'],$regs))
      exit("Bad rate: ".$_POST['rate'].", exiting.");
    $cmd .= " rate $regs[1]M";
    
    if($_POST['autorate'] == "auto")
      $cmd .= " auto";
  }
  
  // Retry
  if(isset($_POST['retry']) && ($_POST['retry']))
    $cmd .= " retry ".escapeshellcmd($_POST['retry']);

  // RTS.  Some drivers (HostAP) report 65534 or 65535 when they mean 'off'.
  if(isset($_POST['rts']) && ($_POST['rts'])) {
    if($_POST['rts'] >= 65534)
      $rts = "off";
    else
      $rts = escapeshellcmd($_POST['rts']);
    $cmd .= " rts $rts";
  }

  // Frag.  Same as for RTS.
  if(isset($_POST['frag']) && ($_POST['frag'])) {
    if($_POST['frag'] >= 65534)
      $frag = "off";
    else
      $frag = escapeshellcmd($_POST['frag']);
    $cmd .= " frag $frag";
  }

  // TODO: Verify madwifi driver here.  VAP could be anything (not just ath)
  if(strstr($wif,"ath")) {
    if(strstr($_POST['mode'], "Master")) {
      $mode = "ap";
    }
    elseif(strstr($_POST['mode'], "Managed")) {
      $mode = "sta"; 
    }
    elseif(strstr($_POST['mode'], "Ad-Hoc")) {
      $mode = "ad-hoc";
    }
    else {
      $mode = "monitor";
    }
    
    if($wif == "ath0") {
      $wlandev = "wifi0";
    }
    else {
      $wlandev = "wifi1";
    }
      
    array_push($extra_cmds, "wlanconfig $wif create wlandev $wlandev wlanmode $mode");
  }

  array_push($extra_cmds, "iwconfig $wif $cmd");

  // athctrl
  if(isset($_POST['athctrl']) && ($_POST['athctrl']))
    array_push($extra_cmds, "athctrl -i $wif -d ".$_POST['athctrl']);

  // TODO: Verify madwifi driver here.  VAP could be anything (not just ath)
  if(strstr($wif,"ath")) {
    // Ugh.  madwifi-ng won't even attempt to associate until the device is "up".
    array_push($extra_cmds, "ifconfig $wif up");
    // ...and it can take several seconds for a sta to be "ready".  Ouch.
    array_push($extra_cmds, "sleep 3");
  }
  
  // Save our work
  set_iface_extras($wif,$extra_cmds);

  privcmd("ifdown --force $wif",false);
  privcmd("ifup $wif",false);
  
  return 0;
}

include("./include/header.php");
?>
<script src="include/submitonce.js"></script>
<script src="include/sorttable.js"></script>
<script src="include/popup.js"></script>
<h2>Wireless Settings</h2>
<?
if($_SESSION["access_ifs"]!= "true"){
	echo "<p class = \"error\">You have no permission to access this section of WiFiAdmin</p class = \"error\">";
	exit();
}

$iw_data = get_wireless_status();	//function to get ALL wireless info of the box

if(! $iw_data) {
  echo "<p class=\"error\">No wireless interfaces detected.</p>";
  exit();
}

//START TAB
if(!isset($_GET['device']) || !array_key_exists ($_GET['device'],$iw_data)) {		//we need an interface to show
	foreach($iw_data as $device){
		$current = $device;
		break;
	}
}
else {
	$current = $iw_data[$_GET['device']];
}


//BEGIN TAB CREATION
echo "\n<ul id=\"tabnav\">\n";
foreach ($iw_data as $device)
{
	if($current['name'] == $device['name'] )
		echo "<li id=\"active-tab\"><a href=\"".$_SERVER['PHP_SELF']."?device=".$device['name']."\">".$device['name']."</a></li>\n";      //active-tab for the specific wifi interface
	else
		echo "<li class=\"tab\"><a href=\"".$_SERVER['PHP_SELF']."?device=".$device['name']."\">".$device['name']."</a></li>\n";      //non active tab

}
echo "</ul>";
//END TAB CREATION

//check whether the user has asked to change setting
if(isset($_POST['device'])) {
  $error = set_iwconfig_params();
  if($error) 
    echo "<p class=error><pre>$error</pre>Some values may not have been applied.<p>";

  echo "<a href=\"".$_SERVER['PHP_SELF']."?device=".$current['name']."\">Back to settings</a>";
}

//print site survey
elseif(isset($_GET['survey'])) {
        $wif = $current['name'];
	$data = parse_iwconfig($wif);

	$scan_results = get_scan_results($wif);
	
	if ($scan_results === false)
		echo "<p class=\"error\"> Device does not support scanning </p>";
	if (count($scan_results) == 0)
		echo "<h4>No scan results</h4>";
	else{
		echo '<h4>Scan results for device '.$wif.'</h4> 
		<table class="sortable" id="sort" align="center" cellspacing="0">
		<tr> <th>#</th>';
		foreach(array_keys($scan_results[0]) as $header)
			echo "<th>".$header."</th>";
		echo "</tr>";
		foreach ($scan_results as $num => $scan_result) {
			echo "<tr>";
			echo "<td>$num</td>";
			$count = 0;
			foreach ($scan_result as $value)
				if($count++ == 1) {
				  echo "<td><b>".$value."</b></td>";
				}
				else {
				  echo "<td>".$value."</td>";
				}
			echo "</tr>";
		}
	echo "</table> <p><a href=\"".$_SERVER['PHP_SELF']."?device=".$wif."\">Back to Wireless Settings</a>";
	}
}
else
{
	$wif = $current['name'];
	echo "<h4>Current settings for device ".$wif."</h4>";
	$data = parse_iwconfig($wif);

//        echo "<p align=\"center\"><a href=\"".$_SERVER['PHP_SELF']."?device=".$current['name']."&survey=1\">[ Site Survey ]</a></p>";

//        $networks = get_networks($wif);
	$networks = array();
?>

<table>
<tr><td>
<form name="settings" onSubmit="submitonce(this)" action="<?echo $_SERVER['PHP_SELF']."?device=".$current['name']?>" method = "POST">
<input type="hidden" name="device" value="<?echo $wif; ?>">
<table>
	<tr><td>ESSID: </td>
	  <td><input type="text" name="essid" value="<?if(!in_array($data['essid'],$networks)) echo $data['essid']?>"></td></tr>

	<tr><td>Mode: </td><td><select name="mode">
	<?
	  foreach(array("Master (access point)","Managed (client)","Ad-Hoc (peer-to-peer)","Monitor") as $mode) {
            if(ereg("^$data[mode]",$mode)) {
              echo "<option selected>$mode</option>";
            }
            else {
              echo "<option>$mode</option>";
            }
          }
        ?>
        </td></tr>

	<tr><td>Channel:</td>
	  <td><select name="channel">
	  <?
	    foreach(get_channels($wif) as $chan) {
	      if(strstr($chan,"Channel ".$data['channel']." :"))
	        echo "<option selected>$chan</option>\n";
	      else
	        echo "<option>$chan</option>\n";
	    }
	  ?>
	  </select>
	</td></tr>

	<tr><td><a href="javascript:popup('help/rate.html')">Bit Rate</a>: </td>
	  <td><select name="autorate">
	  <?
	    if(get_auto_rate($wif))
	      echo "<option selected>auto</option>\n<option>fixed</option>";
	    else
	      echo "<option>auto</option>\n<option selected>fixed</option>";
	  ?>
	  </select>
	  <select name="rate">
	  <option>best</option>
	  <?
	    foreach(get_rates($wif) as $rate) {
	      if($rate == get_numeric_rate($wif))
	        echo "<option selected>$rate</option>\n";
	      else
	        echo "<option>$rate</option>\n";
	    }
	  ?>
	  </select>
	</td></tr>

	<tr><td><a href="javascript:popup('help/rts.html')">RTS threshold</a>: </td><td><input type="text" name="rts" value="<?echo $data['rts']?>"></td></tr>
	<tr><td><a href="javascript:popup('help/frag.html')">Fragment threshold</a>: </td><td><input type="text" name="frag" value="<?echo $data['frag']?>"></td></tr>
	<?
	  // HostAP only
	  if(strstr($wif,"wlan"))
	    echo '<tr><td><a href="javascript:popup(\'help/retry.html\')">Retry Limit</a>: </td><td><input type="text" name="retry" value="'.$data['retry'].'"></td></tr>';
	  // MadWiFi only
	  if(strstr($wif,"ath"))
	    echo '<tr valign=top><td><a href="javascript:popup(\'help/athctrl.html\')">Max station distance</a>:<br />(meters)</td><td><input type="text" name="athctrl" value="'.get_athctrl($wif).'"></td></tr>';
	?>	    
</table>
<p>
<input type="submit" value="Commit Changes">
</form>
</td></tr>
</table>
<?
//	echo "<pre>" . `iwconfig $wif` . "</pre>";
}
include("./include/footer.php");
?>
