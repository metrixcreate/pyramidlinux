<?
/**********************************************
* mesh.php
* Mesh networking (OLSR)
***********************************************/

include("./include/header.php");
?>
<script src="include/submitonce.js"></script>
<h2>Mesh Networking</h2>
<p>
<? 
//check privileges
if ($_SESSION["access_ifs"]!="true"){
	echo "<p class = \"error\">You have no permission to access this section of WiFiAdmin</p>";
	exit();
}

// default action
if(!isset($_GET['action']))
	$_GET['action'] = "show_mesh_settings";

switch ($_GET["action"]){
	case "show_mesh_settings":
		$ethernet_statuses = get_ethernet_status();
		$olsr_ifs = array();
		$olsr_ifs = get_olsr_interfaces();
		$httpinfo_opts = get_olsr_plugin_settings("olsrd_httpinfo");
		$dyn_gw_opts = get_olsr_plugin_settings("olsrd_dyn_gw");
		
?>		<form onSubmit="submitonce(this)" action="<?echo $_SERVER['PHP_SELF']?>" method="GET">
		<p><fieldset title="OLSR">
		<legend><input type="checkbox" name="enableme" value="on" <?echo check_inittab("OL") ? "checked" : "" ?>>
		<b>Enable OLSR</b></legend>
		<table>
		<tr><td>Use OLSR on:</td><td>
<?
		$i = 0;
		foreach ( $ethernet_statuses as $device => $ethernet_status ) {
		  echo "<input type='checkbox' name='olsr_if$i' value='$device'";
		  if(in_array($device,$olsr_ifs)) echo 'checked';
		  echo "> $device &nbsp;&nbsp; ";
		  $i++;
		}
?>
	  	</td></tr>
	  	<tr><td>&nbsp;</td></tr>
	  	<tr><td colspan='2'><b>Plugins</b></td></tr>

		<tr><td align="right"><i><a href="http://<?echo $_SERVER['HTTP_HOST'] . ":" . $httpinfo_opts["port"]?>/" target="httpinfo">httpinfo</a></i></td><td><input type='checkbox' name='http_info_enable' <?echo get_olsr_plugin_status("olsrd_httpinfo") ? "checked" : ""?>> Enabled</td></tr>
		<tr><td align="right">Port</td><td><input type='text' size="5" name='http_info_port' value='<?echo $httpinfo_opts["port"]?>'></td></tr>
	  	<tr><td>&nbsp;</td></tr>

		<tr><td align="right"><i>dyn_gw</i></td><td><input type='checkbox' name='dyn_gw_enable' <?echo get_olsr_plugin_status("olsrd_dyn_gw") ? "checked" : ""?>> Enabled</td></tr>
		<tr><td align="right">Ping</td><td><input type='text' size="20" name='dyn_gw_ping' value='<?echo $dyn_gw_opts["Ping"]?>'></td></tr>
	  	<tr><td>&nbsp;</td></tr>

		<tr><td valign="top">Advanced:</td><td><a href="edit.php?file=olsr&from=<?echo $_SERVER['PHP_SELF']?>">Edit /etc/olsrd.conf</a></td></tr>
		</table>
		</fieldset></p>

		<input type ="hidden" name="action" value = "save_mesh_settings">
		<input type="submit" value="Commit Changes">
		</form><?
		break;

	case "save_mesh_settings":
		echo "<h5>Saving Mesh settings...</h5><ul>";
		$httpinfo_opts = get_olsr_plugin_settings("olsrd_httpinfo");
		$dyn_gw_opts = get_olsr_plugin_settings("olsrd_dyn_gw");

		if(!isset($_GET["enableme"]) && check_inittab("OL")) {
		  echo "<li>Disabling OLSR</li>";
		  update_inittab("OL",false);
		  echo '</ul><p><a href="'.$_SERVER['PHP_SELF'].'">Back to Mesh Settings</a>';
		  exit();
		}
		elseif(isset($_GET["enableme"]) && !check_inittab("OL")) {
		  echo "<li>Enabling OLSR</li>";
		  update_inittab("OL",true);
		}
		
		$ifs = "";
		foreach ( array_keys($_GET) as $param) {
		  if(substr($param, 0, 7) == "olsr_if") {
		    $ifs .= "\"$_GET[$param]\" ";
		  }
		}
		if($ifs) {
		  echo "<li>Updating olsr interfaces.</li>";
	          $ifline = "Interface $ifs";
		  file_replace("/etc/olsrd.conf","Interface .*", "$ifline\n");
	        }

		if(isset($_GET["http_info_enable"]) && !get_olsr_plugin_status("olsrd_httpinfo")) {
		  echo "<li>Enabling httpinfo plugin</li>";
		  set_olsr_plugin_status("olsrd_httpinfo",true);
		}
		elseif(!isset($_GET["http_info_enable"]) && get_olsr_plugin_status("olsrd_httpinfo")) {
		  echo "<li>Disabling httpinfo plugin</li>";
		  set_olsr_plugin_status("olsrd_httpinfo",false);
		}

		if(isset($_GET["dyn_gw_enable"]) && !get_olsr_plugin_status("olsrd_dyn_gw")) {
		  echo "<li>Enabling dyn_gw plugin</li>";
		  set_olsr_plugin_status("olsrd_dyn_gw",true);
		}
		elseif(!isset($_GET["dyn_gw_enable"]) && get_olsr_plugin_status("olsrd_dyn_gw")) {
		  echo "<li>Disabling dyn_gw plugin</li>";
		  set_olsr_plugin_status("olsrd_dyn_gw",false);
		}

		if(isset($_GET["http_info_port"]) && ($_GET["http_info_port"] != $httpinfo_opts["port"])) {
		  echo "<li>Setting httpinfo: port</li>";
		  set_olsr_plugin_settings("httpinfo","port",$_GET["http_info_port"]);
		}

		if(isset($_GET["dyn_gw_ping"]) && ($_GET["dyn_gw_ping"] != $dyn_gw_opts["Ping"])) {
		  echo "<li>Setting dyn_gw: Ping</li>";
		  set_olsr_plugin_settings("dyn_gw","Ping",$_GET["dyn_gw_ping"]);
		}

	        privcmd("killall olsrd",false);
	        privcmd("kill -HUP 1",false);

		echo '</ul><p><a href="'.$_SERVER['PHP_SELF'].'">Back to Mesh Settings</a>';
}

include("./include/footer.php");
?>
