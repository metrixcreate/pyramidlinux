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


include_once("./include/community_functions.php");
include_once("./include/header.php");

//just siplifies the following code
if(isset($_POST["action"]))
	$_GET["action"] = $_POST["action"];

if(isset($_POST["id"]))
	$_GET["id"] = $_POST["id"];


function check_permission_to_edit(){
	if ( ($_SESSION["edit_users"] == "false" && $_SESSION["username"]!= $_GET["id"]) || $_SESSION["username"] =="guest" ) {
		echo error_echo("You don't have permission to edit this user");
		exit();
	}
}

//set default action
if (!isset ($_GET["action"]))
	$_GET["action"] = "list_users";

connect_to_users_db();
switch ($_GET["action"]){
	case "list_users":
		echo "\n<ul id=\"tabnav\">\n";
		echo "<li id=\"active-tab\"><a href=".$_SERVER['PHP_SELF']."?category=User_Managment&action=list_users>Users list</a></li>\n";
		if ($_SESSION["add_users"] == "true"){
			echo "<li class=\"tab\"><a href=users_edit.php?action=add_user_form >Add a new user</a></li>\n";
		}
		echo "</ul>";

		if (isset($_GET['order']))
			$order = $_GET['order'];
		else
			$order = "email";
		//if you want to see more fields in the user list, just name them here

		//if user can edit users, show unconfirmed ones too
		if ($_SESSION["edit_users"]== "true"){
			$sql = "SELECT user.username,ip,firstname,lastname,status 
				FROM `user`,`user_tokens` 
				WHERE
				user.username=user_tokens.username 
				ORDER BY $order";
		}
		else{
			$sql = "SELECT user.username,ip,firstname,lastname FROM `user`,`user_tokens`
			WHERE
			user.username=user_tokens.username AND status=\"enabled\" ORDER BY $order";
		}
		$query = mysql_query($sql) or die("Error: $sql<p>" . mysql_error());

		//if there are any users
		if (mysql_num_rows($query) > 0) {
			?>
			<h4>List of users registered in our community</h4>
			<table class="t1" align="center" cellspacing="0">
			<tr bgcolor="#BEC8D1">
			<?php
			//table title
			for ($i = 0; $i < mysql_num_fields($query); $i++) {
				$field_name = mysql_field_name($query, $i);
				echo "<th><a href=".$_SERVER['SCRIPT_NAME']."?order=$field_name>$field_name</a></th>\n";
			}
			
			if ($_SESSION["edit_users"]== "true")
				echo "<th>action</th></tr>";
			if ($query && (mysql_num_rows($query) > 0)) {
				$i = 0;
				while ($row = mysql_fetch_assoc($query)) {
					echo "<tr".(++$i % 2 == 0 ? " class=\"dark\"" : "").">\n";
					foreach ($row as $attribute_name => $attribute_value){
						if($attribute_name=='password' || $attribute_name == 'password_string' )
							continue;
						if($attribute_name =='username')
							echo "<td><a href=".$_SERVER['SCRIPT_NAME']."?action=full_user_info&id=$attribute_value>$attribute_value</a></td>";
						else
							echo "<td>".stripslashes($attribute_value)."</td>";
					}
					if ($_SESSION["edit_users"]== "true"){
						echo "<td><a href=users_edit.php?id=".$row["username"]."&action=edit>Edit</a>";
						if ($row["username"]!= "admin" && $row["username"]!='guest')
							echo "<a href=users_edit.php?id=".$row["username"]."&action=delete_user_confirm>   Del</a>";
						if ($row["status"]=="unconfirmed")
							echo "<a href=users_edit.php?id=".$row["username"]."&action=enable_user>   Enable</a>";
						echo "</td>";
					}
					echo "  </tr>\n";
				}
			}
			echo "</table>\n";
		}
		else 
			echo "<p>No registered users found.</p>\n";
		break;
	case "full_user_info":
		$sql = "SELECT * FROM `user` WHERE username=\"".$_GET["id"]."\"";
		$query = mysql_query($sql) or die("Error: $sql<p>" . mysql_error());
		$row = mysql_fetch_assoc($query);
		echo "\n<ul id=\"tabnav\">\n";
		echo "<li id=\"tab\"><a href=".$_SERVER['PHP_SELF']."?category=User_Managment&action=list_users>Users list</a></li>\n";
		if ($_SESSION["add_users"] == "true"){
			echo "<li class=\"tab\"><a href=users_edit.php?action=add_user_form >Add a new user</a></li>\n";
		}
		//if such a user exists
		if (mysql_num_rows($query) > 0) {
			echo "<li class=\"tab\" id=\"active-tab\"><a href=".$_SERVER['SCRIPT_NAME']."?action=full_user_info&id=".$row["username"].">".$row["username"]." info</a></li>";
			if ( !(($_SESSION["edit_users"] == "false" && $_SESSION["username"]!= $_GET['id']) || $_SESSION["username"] =="guest" )){
					echo "<li class=\"tab\"><a href=users_edit.php?id=".$row["username"]."&action=edit>Edit ".$row["username"]."</a></li>";
					echo "<li class=\"tab\"><a href=".$_SERVER['PHP_SELF']."?action=delete_user_confirm&id=".$row["username"].">Delete ".$row["username"]."</a></p>";
				}
		}
		echo "</ul>";
		
		//if there are any users
		if (mysql_num_rows($query) > 0) {
			echo "<h4>Full ".$row["username"]." info</h4>"?>
			<table class='t1' align="center">
			<?php
			
			foreach ($row as $attribute_name => $attribute_value){
				if ($attribute_name == "password" || $attribute_name == "password_string")
					continue;
				if ($attribute_name == "mac" && $_SESSION["view_macs"] == "false")
					continue;
				echo "<tr><th>$attribute_name</th><td>$attribute_value</td></tr>\n";
			}
			echo "</table><div align=\"center\">";
		}
		else {
			echo "<p>No users found.</p>\n";
		}
		break;
	case "edit":
		check_permission_to_edit();
		$sql = 'SELECT * FROM `user` WHERE username = "'.$_GET["id"].'"';
		$result = mysql_query($sql) or die("Error: $sql<p>" . mysql_error());
		$row = mysql_fetch_assoc($result);
		
		echo "\n<ul id=\"tabnav\">\n";
		echo "<li id=\"tab\"><a href=".$_SERVER['PHP_SELF']."?category=User_Managment&action=list_users>Users list</a></li>\n";
		if ($_SESSION["add_users"] == "true"){
			echo "<li class=\"tab\"><a href=users_edit.php?action=add_user_form >Add a new user</a></li>\n";
		}
		//if such a user exists
		if (mysql_num_rows($result) > 0) {
			echo "<li class=\"tab\" ><a href=".$_SERVER['SCRIPT_NAME']."?action=full_user_info&id=".$row["username"].">".$row["username"]." info</a></li>";
			if ( !(($_SESSION["edit_users"] == "false" && $_SESSION["username"]!= $_GET['id']) || $_SESSION["username"] =="guest" )){
					echo "<li class=\"tab\" id=\"active-tab\"><a href=users_edit.php?id=".$row["username"]."&action=edit>Edit ".$row["username"]."</a></li>";
					echo "<li class=\"tab\"><a href=".$_SERVER['PHP_SELF']."?action=delete_user_confirm&id=".$row["username"].">Delete ".$row["username"]."</a></p>";
				}
		}
		echo "</ul>";
		
		//if there are any users
		if (!mysql_num_rows($result) > 0)
			echo mydie("No such user");
		
		?>
		<form name="edit" onSubmit="submitonce(this)" action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
		<?php echo("
		<h4>Edit user ".$row["username"]."</h4>
		<table class=\"t1\" align=\"center\">
		
			<tr><td>Username *</td><td><input type=\"text\" name=\"username\" value=\"".$row["username"]."\" class=\"userattribute\"></td></tr>
			<tr><td>Password *</td><td><input type=\"password\" name=\"password\" value=\"".$row["password_string"]."\" class=\"userattribute\" ></td></tr>
			<tr><td>Retype password *</td><td><input type=\"password\" name=\"retype\" value=\"".$row["password_string"]."\" class=\"userattribute\"></td></tr>
			<tr><td>Your email *</td><td><input type=\"text\" name=\"email\" value=\"".$row["email"]."\" class=\"userattribute\"></td></tr>
			<tr><td>MAC address *</td><td><input type=\"text\" name=\"mac\" value=\"".$row["mac"]."\" class=\"userattribute\"></td></tr>
			<tr><td>IP address *</td><td><input type=\"text\" name=\"ip\" value=\"".$row["ip"]."\" class=\"userattribute\"></td></tr>
			<tr><td>Name your subnet</td><td><input type=\"text\" name=\"owns_subnet\" value=\"".$row["owns_subnet"]."\" class=\"userattribute\"></td></tr>
			<tr><td>First Name</td><td><input type=\"text\" name=\"firstname\" value=\"".$row["firstname"]."\" class=\"userattribute\"></td></tr>
			<tr><td>Last Name</td><td><input type=\"text\" name=\"lastname\" value=\"".$row["lastname"]."\" class=\"userattribute\"></td></tr>
			<tr><td>Phone 1</td><td><input type=\"text\" name=\"phone1\" value=\"".$row["phone1"]."\" class=\"userattribute\"></td></tr>
			<tr><td>Phone 2</td><td><input type=\"text\" name=\"phone2\" value=\"".$row["phone2"]."\" class=\"userattribute\"></td></tr>
			<tr><td>Antenna type</td><td><input type=\"text\" name=\"antenna\" value=\"".$row["antenna"]."\" class=\"userattribute\"></td></tr>
			<tr><td>NodeDB ID number</td><td><input type=\"text\" name=\"nodedb_id\" value=\"".$row["nodedb_id"]."\" class=\"userattribute\"></td></tr>
			<tr><td valign=\"top\">Services</td><td><textarea rows=\"5\" cols=\"25\" name=\"services\"  class=\"userattribute\">".$row["services"]."</textarea></td></tr>
			<tr><td>Your Comment</td><td><input type=\"text\" name=\"comment\" value=\"".$row["comment"]."\" class=\"userattribute\"></td></tr>");
		$sql = 'SELECT * FROM `privileges` WHERE username = "'.$_GET["id"].'"';
		$result = mysql_query($sql) or die("Error: $sql<p>" . mysql_error());
		//if there are any users
		if (!mysql_num_rows($result) > 0)
			echo mydie("No privileges for such user (possible bug)");
		if($_SESSION["edit_privileges"]=="true")
		{
			$row = mysql_fetch_assoc($result);
				foreach ($row as $privilege_name => $privilege_value){
					if ($privilege_name == "username")
						continue;
					echo "<tr> <td class=\"capitalize\">". str_replace("_"," ",$privilege_name)."</td> <td> <input type=checkbox name=\"$privilege_name\"";
					if ($row[$privilege_name] == "true")
						echo " checked></td>";
					else
						echo " ></td>";
					echo "</tr>";
				}
		}?>
		</table><div align="center">
		<input type=hidden name="action" value="save_user">
		<input type=hidden name="id"<?php echo "value=".$_GET["id"]?>>
		<input type="submit" value="save">
		</form></div>
<?php
		break;
	case "save_user":
		check_permission_to_edit();
		//form completion errors
		$errors = "";
		if(empty($_POST['username']))
			$errors = error_echo("You did not supply a username");
		if($_POST['password']!=$_POST['retype'])
			$errors .= error_echo("Password fields do not match.");
		if(empty($_POST['email']))
			$errors .= error_echo("You did not supply an email.");
		if(empty($_POST['mac']) || !isset($_POST['ip']))
			$errors .= error_echo("You did not supply a MAC address and/or IP address.");
		if(strlen($errors))		//if there are user errors, print and die
			mydie($errors);
		$_POST['password_string'] = $_POST['password'];
		$_POST['password'] = md5($_POST['password']);


		$user_fields = mysql_list_fields($USERS_DB, "user");
		$columns = mysql_num_fields($user_fields);

		//update user table
		$sql = "UPDATE `user` SET ";
		for ($i = 0; $i < $columns; $i++) {
			if ($i == 0)
				$sql .= mysql_field_name($user_fields, $i)."=\"".$_POST[mysql_field_name($user_fields, $i)]."\" ";
			else
				$sql .= ",".mysql_field_name($user_fields, $i)."=\"".$_POST[mysql_field_name($user_fields, $i)]."\" ";
		}
		$sql .= "WHERE username=\"".$_POST["id"]."\"";
		mysql_query($sql) or die("Error: $sql<p>" . mysql_error());
		$privileges_fields = mysql_list_fields($USERS_DB, "privileges");
		$columns = mysql_num_fields($privileges_fields);

		//update privileges table if current user can edit privileges
		if($_SESSION["edit_privileges"]){
			$sql = "UPDATE `privileges` SET ";
			for ($i = 0; $i < $columns; $i++) {
				if (mysql_field_name($privileges_fields, $i) == "username")
					continue;
				if ($i != 1)
					$sql .=", ";
				$sql .= mysql_field_name($privileges_fields, $i)."=";
				if (isset ($_POST[mysql_field_name($privileges_fields, $i)]) )
					$sql .= "\"true\" ";
				else
					$sql .= "\"false\" ";
			}

			$sql .= "WHERE username=\"".$_POST["id"]."\"";
			mysql_query($sql) or die("Error: $sql<p>" . mysql_error());
		}
		echo "<p>User information updated successfuly, just a moment please</p><meta http-equiv=\"refresh\" content=\"0;URL=".$_SERVER['PHP_SELF']."?action=full_user_info&id=".$_GET["id"]."\"/>";
		break;
	case "delete_user_confirm":
		echo "\n<ul id=\"tabnav\">\n";
		echo "<li id=\"tab\"><a href=".$_SERVER['PHP_SELF']."?category=User_Managment&action=list_users>Users list</a></li>\n";
		if ($_SESSION["add_users"] == "true"){
			echo "<li class=\"tab\"><a href=users_edit.php?action=add_user_form >Add a new user</a></li>\n";
		}
		//if such a user exists
		echo "<li class=\"tab\" ><a href=".$_SERVER['SCRIPT_NAME']."?action=full_user_info&id=".$_GET["id"].">".$_GET["id"]." info</a></li>";
		if ( !(($_SESSION["edit_users"] == "false" && $_SESSION["username"]!= $_GET['id']) || $_SESSION["username"] =="guest" )){
				echo "<li class=\"tab\" ><a href=users_edit.php?id=".$_GET["id"]."&action=edit>Edit ".$_GET["id"]."</a></li>";
				echo "<li class=\"tab\" id=\"active-tab\"><a href=".$_SERVER['PHP_SELF']."?action=delete_user_confirm&id=".$_GET["id"].">Delete ".$_GET["id"]."</a></p>";
			}

		echo "</ul>";
		check_permission_to_edit();
		echo "<H3>You are about to delete user ".$_GET["id"].". Are you sure?</H3>
			<div align=\"center\"><big><a href=".$_SERVER['PHP_SELF']."?category=User_Managment&action=delete_user&id=".$_GET["id"].">Yes</a>
			<a href=".$_SERVER['PHP_SELF']."?category=User_Managment&action=edit&id=".$_GET["id"].">No</a>
			</big></div>";
		break;
	case "delete_user":
		check_permission_to_edit();
		$sql = "DELETE FROM `user` WHERE `username` = '".$_GET["id"]."' LIMIT 1";
		mysql_query($sql) or die("Error: $sql<p>" . mysql_error());
		$sql = "DELETE FROM `privileges` WHERE `username` = '".$_GET["id"]."' LIMIT 1";
		mysql_query($sql) or die("Error: $sql<p>" . mysql_error());
		$sql = "DELETE FROM `user_tokens` WHERE `username` = '".$_GET["id"]."' LIMIT 1";
		mysql_query($sql) or die("Error: $sql<p>" . mysql_error());
		echo "<p><h3>User deleted successfuly, just a moment please</h3></p><meta http-equiv=\"refresh\" content=\"0;URL=".$_SERVER['PHP_SELF']."?action=list_users\"/>";
		break;
	case "add_user_form":
		echo "\n<ul id=\"tabnav\">\n";
		echo "<li class=\"tab\"><a href=".$_SERVER['PHP_SELF']."?category=User_Managment&action=list_users>Users list</a></li>\n";
		if ($_SESSION["add_users"] == "true"){
			echo "<li class=\"tab\" id=\"active-tab\"><a href=users_edit.php?action=add_user_form >Add a new user</a></li>\n";
		}
		echo "</ul>";
		if ($_SESSION["add_users"] == "false"){
			echo error_echo("You don't have permission to add a user");
			break;
		}
		echo_user_add_form();
		break;
	case "add_user":
		if ($_SESSION["add_users"] == "false"){
			echo error_echo("You don't have permission to add a user");
			break;
		}
		//add the user without sending email confirmation.
		add_user( $_POST, false, "");
		echo "<p><h3>User added successfuly, just a moment please</h3></p><meta http-equiv=\"refresh\" content=\"0;URL=".$_SERVER['PHP_SELF']."?action=list_users\"/>";
		break;
	case "enable_user":
		$sql = "UPDATE user_tokens SET status='enabled' WHERE username='".$_GET["id"]."'";
		mysql_query($sql) or die("error in query: $sql - ".mysql_error());
		echo "<p><h3>User ".$_GET["id"]." enabled, just a moment please</h3></p><meta http-equiv=\"refresh\" content=\"0;URL=".$_SERVER['PHP_SELF']."?action=list_users\"/>";
		break;
}
include("./include/footer.php");
?>