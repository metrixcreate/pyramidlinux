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
<h2>Route Information</h2>

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
echo "\n<ul id=\"tabnav\">\n<pre>";
echo "<li id=\"active-tab\"><a href=\"".$_SERVER['PHP_SELF']."?action=show_arp_table\">OLSR Visualization</a></li>\n";
echo "</ul>";
if (check_inittab("OL")){

    echo "<center>";
    privcmd("/sbin/makegraphs",false);
    echo "<a href=\"olsr/routes.png\" target=top><img src=\"olsr/routes.png\" width=600 border=1></a>";
    echo "<br /><a href=\"olsr/routes.png\">PNG</a> | <a href=\"olsr/routes.svg\">SVG</a> | <a href=\"olsr/routes.dot\">Dot</a></center>";
}
echo "\n<ul id=\"tabnav\">\n<pre>";
echo "<li id=\"active-tab\"><a href=\"".$_SERVER['PHP_SELF']."?action=show_arp_table\">Arp Table</a></li>\n";
echo "</ul>";

echo "<center><pre>", `cat /proc/net/arp`, "</pre></center>";


echo "\n<ul id=\"tabnav\">\n<pre>";

echo "<li id=\"active-tab\"><a href=\"".$_SERVER['PHP_SELF']."?action=show_routing_table\">Routing Table</a></li>\n";
echo "</ul>";

if(!isset($_GET['action']))
	$_GET['action'] = "show_routing_table";

switch ($_GET["action"]){
	case "edit_new_route":
		$devices = array_keys($ethernet_statuses);?>
		<form onSubmit="submitonce(this)" action="<?echo $_SERVER['PHP_SELF']?>" method = "GET">
		<table>
		<tr><td>Target</td><td><input type="text" name="target"></td></tr>
		<tr><td>Netmask</td><td><input type="text" name="netmask"></td></tr>
		<tr><td>Gateway</td><td><input type="text" name="gateway"></td></tr>
		<tr><td>Device</td><td colspan="7"  class="noborder">
        		<select name = "device">
			<option>Determined by Gateway</option>
		<?	foreach($devices as $device){
				echo "<option>".$device."</option>";
			}?>
		        </select>
		</td></tr>
		</table>
		<input type ="hidden" name="action" value = "save_new_route">
		<input type="submit" value="Commit Changes">
		</form><?
		break;
	case "save_new_route":
		echo "<h5>Saving new route</h5>";
		$routes = get_routing_table();
		$route_cmd = $route_bin." add -net ".escapeshellarg($_GET["target"])." netmask ".escapeshellarg($_GET["netmask"]);
		if ($_GET["gateway"] != "")
			$route_cmd = $route_cmd." gw ".escapeshellarg($_GET["gateway"]);
		if ($_GET["device"] != "Determined by Gateway")
			$route_cmd = $route_cmd." dev ".escapeshellarg($_GET["device"]);
		$route_output = `$route_cmd`;
		echo "<pre>$route_cmd $route_output</pre>";
		echo "<a href=\"".$_SERVER['PHP_SELF']."?action=show_routing_table\">view new routing table</a>";
		break;
	case "delete_route":
		echo "<h5>deleting route</h5>";
		$routes = get_routing_table();
		$route_cmd = $route_bin." del -net ".$routes[ $_GET["route_id"] ]["destination"]." netmask ".$routes[ $_GET["route_id"] ]["netmask"]." dev ".$routes[ $_GET["route_id"] ]["iface"];
		$route_output = `$route_cmd`;
		echo "<pre>$route_cmd $route_output</pre>";
		//don't delte another route in case of refresh
		$_GET["action"] = "show_routing_table";
		//don't break, go on and show the new routing table
	case "show_routing_table":
		$routes = get_routing_table();
		echo "<h4>Current routing table</h4>";?>
		<form method = "GET" onSubmit="submitonce(this)" action = "<?echo $_SERVER['PHP_SELF'];?>">
		<table class="t1" align="center" cellspacing="0">
		<tr>
			<th>Destination</th>
			<th>Netmask</th>
			<th>Gateway</th>
			<th>Metric</th>
			<th>Interface</th>
		</tr>
		<?
		foreach($routes as $route_index => $route)
		{
			echo "<tr>";
			echo "<td>". ($route["destination"] == '0.0.0.0' ? 'default' : $route["destination"]) ."</td>";
			echo "<td>". $route["netmask"]."</td>";
			echo "<td>". ($route["gateway"] == '0.0.0.0' ? '*' : $route["gateway"]) ."</td>";
			echo "<td>". $route["metric"]."</td>";
			echo "<td>". $route["iface"]."</td>";
//			echo "<td><a href=\"".$_SERVER['PHP_SELF']."?action=delete_route&route_id=".$route_index."\">delete</a></td>";
			echo "</tr>";
		}
//		echo "</table><div align=\"center\"><a href=\"".$_SERVER['PHP_SELF']."?action=edit_new_route\">add a new route</a></div></form>";
		break;
}


include("./include/footer.php");
?>
