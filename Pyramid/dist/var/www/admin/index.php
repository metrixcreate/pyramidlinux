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

include_once("./include/functions.php");		//These are NOT wrong includes. Including here AND later with header.php does matter!
include_once("./include/auth.php");
include_once("./include/passwd_functions.php");


//AUTHENTICATION
$auth_results = 0;
if( !empty($_POST["username"]) && !empty($_POST["password"])&& $_SESSION["username"] == "guest" )
{
	$auth_results = authenticate_user($_POST["username"], $_POST["password"]);
}

include("./include/header.php");	//echo the default Header entries

if($auth_results)
	echo $auth_results;

if($_SESSION["username"] == "guest")
{
	?>
	<table width="100%">
        <tr>
        <td><table  align="center">

        <tr>

        <td>
                <fieldset title="login here">
                <legend>Login to node <b><?echo chostname()?></b></legend>
                <form name="login" method="post" action="<?echo $_SERVER['PHP_SELF'];?>">
                <table>
                        <tr><td>Username</td><td><input type="text" name="username" class="text"></td></tr>
                        <tr><td>Password</td><td><input type="password" name="password" class="text"></td></tr>
                        <tr><td colspan="2" align="right"><input type="submit" value=" Login "></td></tr>
                </table>
                <input type="hidden" name="action" value="login">
                </form>
                </fieldset>
        </td></tr>
        </table></td></tr>
        </table>
	<?
}
else {
  echo "<p class=\"index\"><img src=\"/images/pyramid.png\" width=\"100\"><br /><br /><font color='#900040'>".chostname().
	"</font> online</p><p class=\"index\">Uptime: ".uptime();
  if($vf = @fopen("/etc/pyramid_version", "r")) {
  	$pyramid_version = fgets($vf, 4096);
  	echo "<br>Pyramid Version: $pyramid_version";
  }
  echo "</p><br><br>";
}

include("./include/footer.php");
?>
