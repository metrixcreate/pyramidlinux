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
  | WifiAdmin: The Free WiFi Web Interface				  					|
  +-------------------------------------------------------------------------+
  | Send comments to  							    						|
  | - panousis@ceid.upatras.gr						    					|
  | - dimopule@ceid.upatras.gr						    					|
  +-------------------------------------------------------------------------+*/

include("./include/if_functions.php");

//function that safely resolves hosts to names using a timeout
//It uses the linux command host, for it provides a timeout mechanism
function resolve_mac($mac_addr)
{
	global $arp_bin;
	$ip_addr = trim(exec( $arp_bin." -n | grep -i ".$mac_addr." | cut -d' ' -f1"));	//get IP from mac
	if($ip_addr == "")
		$ip_addr = false;
	return $ip_addr;
}

function ar_gethostbyaddr($ip) 
{
	global $host_bin;
	global $resolve_timeout;
	$output = `$host_bin -W $resolve_timeout $ip`;
	if (ereg('.*pointer ([A-Za-z0-9.-]+)\..*',$output,$regs)) {
	return $regs[1];
	}
	return $ip;
}


include("./include/header.php");
?>
<script src="include/submitonce.js"></script>
<script src="include/sorttable.js"></script>

<h2>Statistics</h2>
<?php
//check priviledges
if ($_SESSION["view_status"]!="true"){
	echo "<p class = \"error\">You have no permission to access this section of WiFiAdmin</p>";
	exit();
}
echo '<meta http-equiv="refresh" content="'.$status_refresh.'"/>';

$iw_data = get_wireless_status();				//function to get ALL wireless info of the box

//START TAB
if(!isset($_GET['device']) || !array_key_exists ($_GET['device'],$iw_data) ) {		//we need an interface to show
	foreach($iw_data as $device){
			$current = $device;
			break;
			}
}
else{
	$current = $iw_data[$_GET['device']];
}

echo "\n<ul id=\"tabnav\">\n";					//create tabs
foreach ($iw_data as $device)
{
	if($current['name'] == $device['name'] )
		echo "<li id=\"active-tab\"><a href=\"".$_SERVER['PHP_SELF']."?device=".$device['name']."\">".$device['name']."</a></li>\n";      //active-tab for the specific wifi interface
	else
		echo "<li class=\"tab\"><a href=\"".$_SERVER['PHP_SELF']."?device=".$device['name']."\">".$device['name']."</a></li>\n";      //non active tab

}
echo "</ul>";
//END TAB CREATION

/*************************************************************************************************
	* proccess request to kick users from the AP device that the user asked
	* if there are for exaple 2 AP devices, the kicking proccess takes place only on the right one
	***************************************************************************************************/
	if(isset($_POST['ban'])){
		if($_SESSION["ban_users"] == 'true'){          			//CAN THE USER BAN OTHER USERS?
			$MAC = array_search("on",$_POST);
			while($MAC){
				ban_user($_POST['ban'],$MAC);
		        unset($_POST[$MAC]);
		        $MAC = array_search("on",$_POST);
	        }
	    }
		else{
		echo "<p class=\"error\">You have no permission to perform this action</p>";
		exit();
        }
	}//end of if-kick clients

/************************************************
* Main case for printing the device chosen
*************************************************/

include("./update-graphs.php");

switch ($current['mode']){

	case 'Master':													//do a Master device

          
	echo "<h4>Associated MACs list on Master-mode device ".$current['name']."</h4>\n";

        if(strstr($current['name'],"wlan")) {
	  $command = "cat ".$hostap_device_path.$current['name']."/*:*";
        }
        elseif(strstr($current['name'],"ath")) {
          $command = "cat /proc/net/madwifi/".$current['name']."/associated_sta";
        }
	$associations = trim(`$command`);
	if($associations == "")
		echo "<h4>There are currently no associations on ".$current['name']."</h4>\n";
	else
	{
	$data = parse_assoc($associations);									//Call function to get the data that we want for associations
	$tx_max = max($data['tx_bytes']);									//calculate maxes for later notification
	$rx_max = max($data['rx_bytes']);			

	?>
<table class = "invisible" align="center">
	<tr><td>
	<form method = "POST" onSubmit="submitonce(this)" action = "<?echo $_SERVER['PHP_SELF'];?>">
	<table class="sortable" id="sort" align="center" cellspacing="0">
	<tr><?if($_SESSION['ban_users'] == "true")
			echo "<th class=\"invisible\">&nbsp;</th>";?>
	<th>#</th>
<? if($_SESSION['view_macs'] == 'true'){
	echo "<th>MAC</th>";
}
?>
		<th>Name</th>
 		<th>Uploaded</th>
        <th>Downloaded</th>
        <th>U/D</th>
	    <th>Signal</th>
        <th>Noise</th>
	</tr>
	<?
	for($j=0;$j < count($data['macs']);$j++)							//every loop creates one line of the assoc table
	{
		echo "<tr>\n";
		if($_SESSION['ban_users'] == "true")
			echo "<td><input type=checkbox name=\"".$data['macs'][$j]."\"></td>\n";
		echo "<td>".($j+1)."</td>";		//echo number
		if($_SESSION['view_macs'] == 'true')            				//check users priviledge to see MAC addresses
			echo "<td>".$data['macs'][$j]."</td>\n";
		
		if($IP = resolve_mac($data['macs'][$j]))						//get IP or name from a MAC address
		{
			if($resolve)    //resolve the IPs found associated?
				$name = ar_gethostbyaddr($IP);
			else
			$name = $IP;
			$name = "<a href=\"http://".$name."\">".$name."</a>";		//make $name a valid URL that points to "name"
		}
		else
		$name = "<i>unresolved</i>";
		echo "<td>".$name."</td>\n";
		if($rx_max == $data['rx_bytes'][$j])							//RX bytes
			echo "<td><div class=\"error\">".ByteSize($data['rx_bytes'][$j])."</div></td>";
		else
			echo "<td>".ByteSize($data['rx_bytes'][$j])."</td>";

        if($tx_max == $data['tx_bytes'][$j])							//TX bytes
        	echo "<td><div class=\"error\">".ByteSize($data['tx_bytes'][$j])."</div></td>";
		else
             echo "<td>".ByteSize($data['tx_bytes'][$j])."</td>";

		echo "<td>".$data['rates'][$j]."%</td>\n";						//RX-TX rates
		echo "<td>".$data['signal'][$j]."</td>\n";
        echo "<td>".$data['noise'][$j]."</td>\n";
		echo "</tr>\n";
	}
	echo "</table>
	</td></tr>";
	if($_SESSION['ban_users'] == "true")
		echo "<tr><td><input type=hidden name=\"ban\" value=\"".$current['name']."\"> 
				<input type = \"submit\" value = \"Ban user\"></td></tr>";
	echo "
		</form></table>

	<table class=\"invisible\" cellspacing=\"0\" align=\"center\">
	";
	
	if(!isset($_POST['time']))
		$_POST["time"] = "daily";
        echo "<tr><td><img src=\"./graphs/".$current['name']. "-nusers-".$_POST["time"].".png\" alt=\"number of users graph unavailable\"></td></tr>";
	echo "<tr><td><img src=\"./graphs/".$current['name']. "-traffic-".$_POST["time"].".png\" alt=\"traffic graph unavailable\"></td></tr>";
	?>

		<tr><td>
			<form method = "POST" onSubmit="submitonce(this)" action ="<?echo $_SERVER['PHP_SELF']."?device=".$current['name'];?>">
				<select name = "time">
		            <option <?php if ($_POST["time"]== "daily") echo "selected"?>>daily</option>
		            <option <?php if ($_POST["time"]== "weekly") echo "selected"?>>weekly</option>
		            <option <?php if ($_POST["time"]== "monthly") echo "selected"?>>monthly</option>
		            <option <?php if ($_POST["time"]== "yearly") echo "selected"?>>yearly</option>
				</select>
				<input type="hidden" name ="nusers" value="<?echo $current['name'];?>">
	            <input type = "submit" value = "Select graph">
			</form>
		</td></tr>
		</table>

<?
	}//end if no clients
break;//end printing Master device status

case 'Managed' : 

	echo "<h4>".$current['name']." link status</h4>\n";
	echo "<table align=\"center\" class=\"t1\">
		<tr>
			<th>Type</th>
			<th>mode</th>
			<th>ESSID</th>
			<th>Channel</th>";
	if($_SESSION['view_macs'] == 'true'){								//check users priviledge to see MAC addresses
		echo "<th>Remote MAC</th>";
	}
	echo "	<th>Quality</th>
			<th>Signal</th>
			<th>Noise</th>
		</tr>";
		
		$data = parse_iwconfig($current['name']);
		echo "<tr>
			<td>".$data['type']."</td><td>".$data['mode']."</td><td>".$data['essid']."</td><td>".$data['channel']."</td>";
			if($_SESSION['view_macs'] == 'true'){            //check users priviledge to see MAC addresses
				echo "<td>".$data['ap']."</td>";
			}
			echo "<td>".$data['quality']."</td><td>".$data['signal']."</td><td>".$data['noise']."</td>
		</tr>";
		if (!isset($_POST["time"]))
			$_POST["time"] = "daily";
		echo "<tr><td colspan=\"8\" align =\"right\" class=\"noborder\"><img src=\"./graphs/".$current['name']. "-signal-".$_POST["time"].".png\" alt=\"singal-noise graph unavailable\"></td></tr>";
		echo "<tr><td colspan=\"8\" align =\"right\" class=\"noborder\"><img src=\"./graphs/".$current['name']. "-rate-".$_POST["time"].".png\" alt=\"rate graph unavailable\"></td></tr>";
		echo "<tr><td colspan=\"8\" align =\"right\" class=\"noborder\"><img src=\"./graphs/".$current['name']. "-traffic-".$_POST["time"].".png\" alt=\"traffic graph unavailable\"></td></tr>";
	?>
	<tr><td colspan="8"  class="noborder">
	<form method = "POST" onSubmit="submitonce(this)" action = "<?echo $_SERVER['PHP_SELF']."?device=".$current['name'];?>">
		<select name = "time">
			<option <?php if ($_POST["time"]== "daily") echo "selected"?>>daily</option>
			<option <?php if ($_POST["time"]== "weekly") echo "selected"?>>weekly</option>
			<option <?php if ($_POST["time"]== "monthly") echo "selected"?>>monthly</option>
			<option <?php if ($_POST["time"]== "yearly") echo "selected"?>>yearly</option>
		</select>
		<input type="hidden" name ="graph" value="<?echo $current['name'];?>">
		<input type = "submit" value = "Select graph">
	</form>
	</td></tr>
	<?
	echo "</table>\n";
	break;

case 'Ad-Hoc':
	echo "<h4>".$current['name']." link status</h4>\n";
	echo "<table align=\"center\" class=\"t1\">
		<tr>
			<th>Type</th>
			<th>mode</th>
			<th>ESSID</th>
			<th>Channel</th>";
	if($_SESSION['view_macs'] == 'true'){            //check users priviledge to see MAC addresses
		echo "<th>Cell</th>";
	}
	echo "	<th>Quality</th>
			<th>Signal</th>
			<th>Noise</th>
		</tr>";
		
		$data = parse_iwconfig($current['name']);
		echo "<tr>
			<td>".$data['type']."</td><td>".$data['mode']."</td><td>".$data['essid']."</td><td>".$data['channel']."</td>";
			if($_SESSION['view_macs'] == 'true'){            //check users priviledge to see MAC addresses
				echo "<td>".$data['cell']."</td>";
			}
			echo "<td>".$data['quality']."</td><td>".$data['signal']."</td><td>".$data['noise']."</td>
		</tr>";
		if (!isset($_POST["time"]))
			$_POST["time"] = "daily";
		echo "<tr><td colspan=\"8\" align =\"right\" class=\"noborder\"><img src=\"./graphs/".$current['name']. "-signal-".$_POST["time"].".png\" alt=\"singal-noise graph unavailable\"></td></tr>";
		echo "<tr><td colspan=\"8\" align =\"right\" class=\"noborder\"><img src=\"./graphs/".$current['name']. "-rate-".$_POST["time"].".png\" alt=\"rate graph unavailable\"></td></tr>";
		echo "<tr><td colspan=\"8\" align =\"right\" class=\"noborder\"><img src=\"./graphs/".$current['name']. "-traffic-".$_POST["time"].".png\" alt=\"traffic graph unavailable\"></td></tr>";
	?>
	<tr><td colspan="8"  class="noborder">
	<form method = "POST" onSubmit="submitonce(this)" action = "<?echo $_SERVER['PHP_SELF']."?device=".$current['name'];?>">
		<select name = "time">
			<option <?php if ($_POST["time"]== "daily") echo "selected"?>>daily</option>
			<option <?php if ($_POST["time"]== "weekly") echo "selected"?>>weekly</option>
			<option <?php if ($_POST["time"]== "monthly") echo "selected"?>>monthly</option>
			<option <?php if ($_POST["time"]== "yearly") echo "selected"?>>yearly</option>
		</select>
		<input type="hidden" name ="graph" value="<?echo $current['name'];?>">
		<input type = "submit" value = "Select graph">
	</form>
	</td></tr>
	<?
	echo "</table>\n";
	break;

default:
	echo "<h4>".$current['name']." status</h4>
	<p class =\"error\">This device\'s mode is not supported.</p>";
	break;
}//end case!

/************************************************
* Log output
*************************************************/
if(isset($_GET['logs']) &&($_SESSION['view_macs'] == 'true')){
	$num_of_lines = 35;
	echo "<h4>Last $num_of_lines lines of kernel wireless messages</h4>";
	echo "<p class=\"pre\">".str_replace("\n","<br>",`dmesg |grep wlan |tail -n $num_of_lines`)."</p>";
}
//show logs only if user has permission to see macs
if($_SESSION['view_macs'] == 'true'){            //check users priviledge to see MAC addresses
    echo "<div align=\"center\"><a href=\"".$_SERVER['PHP_SELF']."?device=".$current['name']."&logs=1\">[ View Wireless Logs ]</a></div>";
}
include("./include/footer.php");
?>

