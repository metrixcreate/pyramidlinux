<?php
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

//File that holds all headers,title, menu data in tables
include_once ( "./include/auth.php");
include_once ( "./include/functions.php");
include_once ( "./include/config.php");

if(isset($_POST['logout']) && $_SESSION['username'] != "guest" )	//destroy session if asked to
{
	session_destroy();
	include_once ( "./include/auth.php");				//and reinstanciate
}

if($count_time)
{ 
	$time_start = getmicrotime();
}

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
        <html>
	<head>
		<title>Pyramid: '.chostname().'</title>
        <link rel = "stylesheet" type = "text/css" href = "./include/global.css">
	</head>
        <body>';
if(isset($_POST['logout']) && $_SESSION['username'] != "guest" ) {
	echo '<meta http-equiv="refresh" content="0;index.php"/>';
	echo "<p align='center'><a href='index.php'>Logging out...</a></p></body></html>";
	exit;
}

echo	'<a name="top"></a>';
echo	'<div id="header"><div id="login">';

if($_SESSION['username'] == 'guest'){
//	echo 'You are logged in as <b>'.$_SESSION['username'].'</b>, login <a href="./index.php">here</a>';
//	if ($users_register_themselves)
//		echo ', or <a href="./register.php">register</a> to our community.';
}

else
{
        echo "Logged in as: <b>".$_SESSION['username']."</b> 
        <form name = \"logout\" method = \"POST\" action = \"".$_SERVER['PHP_SELF']."\">
        <input type = \"hidden\" name = \"logout\" value = \"1\">
        <a href=\"javascript:document.logout.submit();\">[logout]</a>
	</form>";
}
	echo '</div>
	<h1><i>Pyramid Linux</i></h1><p class=moto>Brought to you by <a href="http://metrix.net/" class="anchor">Metrix Communication LLC</a></i> </p>
	</div>';
  echo '<table width=100% border=0>
        <tr><td>&nbsp;</td></tr>
	<tr><td valign="top" id="menu">
	<div id="menu">
	<ul id="menu">
        <li><a href="./index.php" class="menulink" >Overview</a></li>';

	if(@($_SESSION["access_ifs"] == "true")){
		echo '
			<li><a href="./system.php" class="menulink">System Services</a></li>
			<li><a href="./iwsettings.php" class="menulink">Wireless Settings</a></li>
			<li><a href="./ifsettings.php" class="menulink">Network Interfaces</a></li>
			<li><a href="./mesh.php" class="menulink">Mesh Networking</a></li>
                        <li><a href="./firewall.php" class="menulink">Port Forwarding</a></li> 
			';
        }
	if(@($_SESSION["view_status"] == "true")){
          echo '<li><a href="./iwstatus.php" class="menulink">Statistics</a></li>';
        }
	if(@($_SESSION["access_ifs"] == "true")){
		echo '
			<li><a href="./routing.php" class="menulink">Route Info</a></li>
                      ';
	}
//	if(@($_SESSION["ban_users"] == "true")){
//		echo '<li><a href="./iwsecurity.php" class="menulink">Security</a></li>';
//	}
	if(@($_SESSION["edit_users"]=="true")) {
		echo '<li><a href="./users.php" class="menulink">Edit Users</a></li>';
        }		
echo '
	</ul>
	</div>
	</td>
	<td id="content">
        <!main window!>';
?>
