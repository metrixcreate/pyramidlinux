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
  
  
include_once("config.php");
include_once("functions.php");
function connect_to_users_db() {
	global $USERS_DBHOST,$USERS_DB, $USERS_DBUSER, $USERS_DBPASS;
	$link = mysql_connect($USERS_DBHOST, $USERS_DBUSER, $USERS_DBPASS) 
		or 
		die("could not connect $USERS_DBHOST, $USERS_DBUSER , $USERS_DBPASS ". mysql_error());
	
	if (!mysql_select_db($USERS_DB)) {
		// couldn't connect
		echo "could not select ($USERS_DB)";
	}
	return $link;
}

function mydie($string)
{
	global $count_time, $time_start;
	echo error_echo($string);
	include_once("./include/footer.php");
	exit();
}

function send_confirmation($email,$token, $WiFiAdmin_BASE_URL) {
	global $WEB_MASTER,$WEB_MASTER_EMAIL;
	mail($email, 
	"Welcome to Wifiadmin, please confirm your email address",
	"Thank you for signing up. This email has been sent to you automatically. Please click the link below in order to confirm your account.\n\n". $WiFiAdmin_BASE_URL."confirm_account.php?token=".base64_encode($token)."\n\nThanks,\n".$WEB_MASTER,
	 "From: $WEB_MASTER <$WEB_MASTER_EMAIL>\n");    
}

function echo_user_add_form(){

	?>
	<h2>New User Registration</h2>
	<form name="register" onSubmit="submitonce(this)" action="<?echo $_SERVER['PHP_SELF'];?>" method="POST">
	<table class="t1" align="center">
		<tr><td>Username *</td><td><input type="text" name="username"></td></tr>
		<tr><td>Password *</td><td><input type="password" name="password"></td></tr>
		<tr><td>Retype password *</td><td><input type="password" name="retype"></td></tr>
		<tr><td>Your email *</td><td><input type="text" name="email"></td></tr>
		<tr><td>MAC address *</td><td><input type="text" name="mac"></td></tr>
		<tr><td>IP address *</td><td><input type="text" name="ip"></td></tr>
		<tr><td>Name your subnet</td><td><input type="text" name="subnet"></td></tr>
		<tr><td>First Name</td><td><input type="text" name="firstname"></td></tr>
		<tr><td>Last Name</td><td><input type="text" name="lastname"></td></tr>
		<tr><td>Phone 1</td><td><input type="text" name="phone1"></td></tr>
		<tr><td>Phone 2</td><td><input type="text" name="phone2"></td></tr>
		<tr><td>Antenna type</td><td><input type="text" name="antenna"></td></tr>
		<tr><td>NodeDB ID number</td><td><input type="text" name="nodedb_id"></td></tr>
		<tr><td valign="top">Services</td><td><textarea rows="5" cols="25" name="services"></textarea></td></tr>
		<tr><td>Your Comment</td><td><input type="text" name="comment"></td></tr>
		
	</table>
	<input type="hidden" name="action" value="add_user">
	<div align="center"><input type="submit" value="register"></div>
	</form>
	<p>* starred entries are mandatory.</p>
	
	<?
}

function add_user( $user_info, $send_confirmation_email,$WiFiAdmin_BASE_URL){
	global $USERS_DB;
	//form completion errors
	$errors = "";
	if(empty($user_info['username']))
		$errors = error_echo("You did not supply a username");
	if(empty($user_info['password']))
		$errors .= error_echo("You did not supply a password.");
	if($user_info['password']!=$user_info['retype'])
		$errors .= error_echo("Password fields do not match.");
	if(empty($user_info['email']))
		$errors .= error_echo("You did not supply an email.");
	if(empty($user_info['mac']) || !isset($user_info['ip']))
		$errors .= error_echo("You did not supply a MAC address and/or IP address.");

	if(strlen($errors))		//if there are user errors, print and die
	mydie($errors);

	//database errors
	connect_to_users_db();

	foreach($user_info as $key=>$var)
	{
		$user_info[$key]=mysql_escape_string($var);
		if(!isset($user_info[$key]))
			$user_info[$key]="";
	}

	$sql = "SELECT username FROM $USERS_DB.user WHERE username = '".$user_info['username']."'";
	$user = mysql_query($sql) or
		mydie(" Error. ". mysql_error() );
	if(mysql_fetch_array($user))
		mydie("Username already exists. Please use another one.");

	$sql = "SELECT email FROM $USERS_DB.user WHERE email = '".$user_info['email']."'";
	$email = mysql_query($sql) or
		mydie(" Error. ". mysql_error());
	if(mysql_fetch_array($email))
		mydie("Email address already exists. Please use another one.");

////Insert DATA into user database!
	 $insert =	"INSERT INTO $USERS_DB.user
	(username,password,email,mac,ip,owns_subnet,services,phone1,phone2,antenna,nodedb_id,comment,firstname,lastname,password_string)
     VALUES ('" .$user_info['username']. "', '" .md5($user_info['password']). "', '" .$user_info['email']. "',
     '" .$user_info['mac']. "', '" .$user_info['ip']. "','" .$user_info['subnet']. "', '" .$user_info['services'].
     "', '" .$user_info['phone1']. "', '" .$user_info['phone2']. "', '" .$user_info['antenna']."', '" .$user_info['nodedb_id']."', '"
     .$user_info['comment']. "', '" .$user_info['firstname']."', '" .$user_info['lastname']."', '".$user_info['password']."')";

	$privilege = "INSERT INTO $USERS_DB.privileges
	(username,view_status,view_macs,ban_users,access_ifs,edit_users, edit_privileges,  add_users  )
	VALUES ('".$user_info['username']."','true','false','false','false','false','false','false')";

 	mysql_query($insert)
  		or mydie("User registration operation failed: ". mysql_error());

  	mysql_query($privilege)
  		or mydie("User priv registration operation failed: ". mysql_error());
	
	if ($send_confirmation_email){
		$token = time() . "::".$user_info["username"];
		$sql = "INSERT INTO user_tokens (username,status,token) VALUES ('".$user_info['username']."','unconfirmed','".mysql_escape_string($token)."')";
		mysql_query($sql) or die("Error in query: $sql - ".mysql_error());
		send_confirmation($user_info["email"],$token, $WiFiAdmin_BASE_URL);
		echo ("<p>In order to confirm that the email address you entered is valid,
		 you have been automatically sent an email . Please follow its instructions.</p>");
	}
	else{
		$token = time() . "::".$user_info["username"];
		$sql = "INSERT INTO user_tokens (username,status,token) VALUES ('".$user_info['username']."','enabled','".mysql_escape_string($token)."')";
		mysql_query($sql) or die("Error in query: $sql - ".mysql_error());
		echo "<p>User registration was succesful! Note that user data can be viewed by everyone.</p>";
	}
}




?>