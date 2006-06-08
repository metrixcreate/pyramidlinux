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
  | WifiAdmin: The Free WiFi Web Interface				    				|
  +-------------------------------------------------------------------------+
  | Send comments to  							  	  						|
  | - panousis@ceid.upatras.gr						 						|
  | - dimopule@ceid.upatras.gr						    					|
  +-------------------------------------------------------------------------+*/

function apply_policy($wif, $policy)
{
	global $iwpriv_bin;
	if($policy == "open")
		`$iwpriv_bin $wif maccmd 0`;
	elseif($policy == "allow")
		`$iwpriv_bin $wif maccmd 1`;
	elseif($policy == "deny")
		`$iwpriv_bin $wif maccmd 2`;			
}

include("./include/header.php");
?>
<script src="include/submitonce.js"></script>
<h2>Wireless Security Settings (TODO)</h2>
<p class='error'>
  Web based security features have not been implemented yet.  Please use <b>ssh</b> to configure security settings.
</p>

<?
include("./include/footer.php");

/*
//check privileges
if ($_SESSION["ban_users"]!="true"){
	echo "<p class = \"error\">You have no permission to access this section of WiFiAdmin</p>";
	exit();
}

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

/*	
if( $current['mode'] == 'Master' )
{
	$security = get_policy($current['name']);
	echo "<form  method=\"POST\" onSubmit=\"submitonce(this)\" action =\"".$_SERVER['PHP_SELF']."\">
               <input type=\"hidden\" name=\"device\" value=\"".$current['name']."\">";
	echo "<div align=\"center\"><h4>Security settings for device ".$current['name']."</h4></div>\n
		<table>
		<tr><td><p>Access list policy: <i><u>".$security['policy']."</u></i>
			<select name = \"policy\">
				<option>open</option>
				<option>deny</option>
				<option>allow</option>
			</select></p></td>
		</tr>";
	if(isset($security[0]))
	{
		echo "<tr><td>
			Access List
			<table class=\"sortable\">
			<tr><th>remove</th><th>MAC</th></tr>";
		for($i=0;$i<sizeof($security)-1;$i++)
		{
			echo "<tr><td align=\"center\"><input type=\"checkbox\" name=\"".$security[$i]."\"></td>
			<td>$security[$i]</td></tr>";
		}
		echo "</table><br></td></tr>";
	}
	echo "<tr><td>Add new MACs to ACL here, use <b>;</b> as a delimiter for multiple MACs<br><textarea rows=\"5\" cols=\"25\" name=\"addmac\"></textarea></td></tr>
		</table>
		<input type=\"hidden\" name = \"post\" value = \"setsettings\">
		<input type=\"submit\" value=\"Commit changes\">
		</form>";
}
else

echo "<p>Web based security features have not been implemented yet.  Please use <b>ssh</b> to configure security settings.</p>";

if(isset($_POST['device']))		//commit security changes for the device
{
	$wif = $_POST['device'];
	echo "<p>Applying changes for device $wif . . .</p>";
	if(isset($_POST['policy']))				//change acl policy
	{
	apply_policy($wif, $_POST['policy']);
			//echo "<p class=\"error\">Access List policy change failed</p>";
	}
	$MAC = array_search("on",$_POST);		//remove MACs from APs Access List
	while($MAC)				
	{
		`$iwpriv_bin $wif delmac $MAC`;
		unset($_POST[$MAC]);
		$MAC = array_search("on",$_POST);
	}
	if(isset($_POST['addmac']))				//add more MACs to the ACL
	{
		$maclist = explode(";", $_POST['addmac']);
		foreach($maclist as $var)
		`$iwpriv_bin $wif addmac $var`;
	}	
	echo "<p>OK</p>";
}
include("./include/footer.php");
*/
?>
