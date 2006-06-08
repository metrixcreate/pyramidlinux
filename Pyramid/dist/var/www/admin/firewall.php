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

include("./include/header.php");	//echo the default Header entries
include("./include/if_functions.php");
include("./include/ip_functions.php");

?>
<script src="include/submitonce.js"></script>
<script src="include/disabler.js"></script>
<h2>Firewall Settings</h2>

<? 
#check privileges
if ($_SESSION["access_ifs"]!="true"){
	echo "<p class = \"error\">You have no permission to access this section of WiFiAdmin</p>";
	exit();
}

$ethernet_statuses = get_ethernet_status();

$i=0;
foreach ( $ethernet_statuses as $device => $ethernet_status ) {
	$ifs[$i]=$device;
	$i+=1;
}        

// Default action: show status
if(!isset($_GET['action']))
	$_GET['action'] = "show_status";

switch ($_GET["action"]) {
	case "delete_entry":
		if((!isset($_GET['index'])) || ($_GET['index'] == "")) {
		  echo "</ul><p class = \"error\">ERROR: Undefined index.</p><br />";
		  echo "<a href='".$_SERVER['PHP_SELF']."'>Go back</a> and try again.";
		  exit;
		}
		echo "<p><ul>";
		echo "<li>Removing port forwarding entry.</li>";
		privcmd("/etc/init.d/portforwarding stop",false);
		delete_portforward($_GET['index']);
		privcmd("/etc/init.d/portforwarding start",false);
                echo "</ul><a href='".$_SERVER['PHP_SELF']."'>Back to Firewall Settings";
		exit;
	
	case "save_entry":
		$error = 0;
		echo "<p><ul>";
		$vars = array( "proto", "dport", "tip", "tport" );
		foreach($vars as $v) {
		  if((!isset($_GET[$v])) || ($_GET[$v] == "")) {
		    $error = 1;
		  }
		}
		if($error) {
		  echo "</ul><p class = \"error\">ERROR: You must fill in all fields to add an entry.</p><br />";
		  echo "<a href='".$_SERVER['PHP_SELF']."'>Go back</a> and try again.";
		  exit;
		}
		echo "<li>Adding port forward entry</li>";
		privcmd("/etc/init.d/portforwarding stop",false);
		add_portforward($_GET['proto'],$_GET['dport'],$_GET['tip'],$_GET['tport']);
		privcmd("/etc/init.d/portforwarding start",false);
                echo "</ul><a href='".$_SERVER['PHP_SELF']."'>Back to Firewall Settings";
		exit;


	case "show_status":
		?>
		<p><fieldset title="Port Forwarding">
		<legend>Port Forwarding</legend>
		<table>
		<tr bgcolor='#ffffff'><td>Protocol</td><td>Port</td><td>To IP address</td><td>Port</td><td>Action</td></tr>
<?
		$pf = get_portforwards();
		if(sizeof($pf)) {
		  for($i = 0; $i < (sizeof($pf)); $i += 4) {
?>
<tr><td>
<form name="portforwarding" onSubmit="submitonce(this)" action="<?echo $_SERVER['PHP_SELF']?>" method = "GET">
<input type="hidden" name="action" value="delete_entry">
<input type="hidden" name="index" value="<?echo $i / 4?>">
<?
		    echo $pf[$i]."</td><td>".$pf[$i+1]."</td><td>".$pf[$i+2]."</td><td>".$pf[$i+3]."</td>";
		    echo "<td><input type='submit' value='Remove'></form></td>";
		    echo "</tr>";
		  }
		  echo "</tr></td>";
		}
?>
		<form name="portforwarding" onSubmit="submitonce(this)" action="<?echo $_SERVER['PHP_SELF']?>" method = "GET">
		<input type="hidden" name="action" value="save_entry">
		<td>
		<select name="proto">
		<option>tcp</option>
		<option>udp</option>
		</select>
		</td>
		
		<td>
		<input type="text" name="dport" size="4"> ->
		</td>
		<td>
		<input type="text" name="tip" size="15"> :
		</td>
		<td>
		<input type="text" name="tport" size="4">
		</td>
		<td>
		<input type="submit" name="add" value="Add">
		</td>
		</tr></table></form>
		</fieldset>

		<p><fieldset title="Commonly used ports">
		<legend>Commonly used ports</legend>
		<pre>
ssh             22/tcp
telnet          23/tcp
smtp            25/tcp
dns             53/tcp
www             80/tcp
pop3           110/tcp
netbios        139/tcp
imap           143/tcp
https          443/tcp
imaps          993/tcp
pop3s          995/tcp
		</pre>
		</fieldset>
<?
}


include("./include/footer.php");
?>
