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
include_once("config.php");
//include_once( "community_functions.php");

function check_real_password ($username, $pw)
{
  $line = `grep $username /etc/shadow`;
  if ($line == "")
    return false;
  list ($user, $pass, $uid, $gid, $gecos, $home, $shell) =
    explode (":", $line);

  if (strlen ($pass) != 34)
    return false;

  $salt = substr ($pass, 0, 12);
  $rest = substr ($pass, 12);
  if ($pass == crypt ($pw, $salt))
    return true;
  else
    return false;
}

/*passwd file format:

NOTE: Password field is no longer used.  Use the system passwd mechanism.

username:md5(password):boolean edit_settings*/

function create_default_passwd_file ()
{
  global $passwd_filename;
  $filename = $passwd_filename;
  if (($fp = fopen ($filename, "w")) == false)
    {
      echo "<h3> passwd file could not be created, check filesystem permissions, httpd user, should have permission to write in include directory </h3>";
      return false;
    }
  fwrite ($fp,
"username:password:view_status:view_macs:ban_users:access_ifs:edit_users
root:x:true:true:true:true:true
guest:x:false:false:false:false:false\n");
  fclose ($fp);
}

	//returns an array of passwd file lines         
function read_passwd_file ()
{
  global $passwd_filename;
  $filename = $passwd_filename;
  //if passwd file does not exist, create the default one
  if (!file_exists ($filename))
    {
      privcmd ("remountrw", false);
      create_default_passwd_file ();
      privcmd ("remountro", false);
    }
  $fp = fopen ($filename, "r");
  $file_contents = fread ($fp, filesize ($filename));
  fclose ($fp);
  $whole_lines = explode ("\n", $file_contents);

  $lines_count = 0;
  foreach ($whole_lines as $whole_line)
  {
    $whole_line = trim($whole_line);
    //ignore empty lines
    if ($whole_line == "")
      continue;
    $lines[$lines_count] = explode (":", $whole_line);
    $lines_count++;
  }
  //first line of file is the attribute names
  $attribute_names = $lines[0];

  //for each of the lines
  for ($line_index = 1; $line_index < count ($lines); $line_index++)
    {
      //for each attribute
      $attribute_index = 0;
      foreach ($attribute_names as $attribute_name)
      {
	$user_attributes[$attribute_name] =
	  $lines[$line_index][$attribute_index];
	$attribute_index++;
      }
      $return_array[$line_index - 1] = $user_attributes;
    }
  return $return_array;
}

function write_passwd_file ($users_attributes)
{
  global $passwd_filename;
  $filename = $passwd_filename;
  if (($fp = fopen ($filename, "r+")) == false)
    {
      echo "<h3> could not open passwd file, check permissions </h3>";
      return false;
    }
  $fp = fopen ($filename, "r+");
  $attribute_index = 0;
  foreach ($users_attributes[0] as $attribute_name => $attribute_value)
  {
    if ($attribute_index == 0)
      $passwd_string = $attribute_name;
    else
      $passwd_string = $passwd_string.":".$attribute_name;
    $attribute_index++;
  }

  foreach ($users_attributes as $user_attributes)
  {
    $passwd_string = $passwd_string."\n";
    $attribute_index = 0;
    foreach ($user_attributes as $attribute_name => $attribute_value)
    {
      if ($attribute_index == 0)
	$passwd_string = $passwd_string.$attribute_value;
      else
	$passwd_string = $passwd_string.":".$attribute_value;
      $attribute_index++;
    }
  }
  ftruncate ($fp, 0);
  fwrite ($fp, $passwd_string);
  fclose ($fp);
}

	//assign privileges for the user $_SESSION["username"]
function assign_privileges ()
{
  global $use_mysql;
  if ($use_mysql == true)
    {
      $link = connect_to_users_db ();
      $sql =
	'SELECT * FROM `privileges` WHERE username = "'.$_SESSION["username"].
	'"';
      $result =
	mysql_query ($sql) or die (" $result Error: $sql<p>".mysql_error ());
      while ($row = mysql_fetch_assoc ($result))
	{
	  if ($row["username"] == $_SESSION["username"])
	    {
	      /* set the php session */
	      foreach ($row as $attribute_name => $attribute_value)
	      {
		$_SESSION[$attribute_name] = $attribute_value;
	      }
	      break;
	    }
	}
      /* Free resultset */
      mysql_free_result ($result);
      /* Closing connection */
      mysql_close ($link);
    }
  else
    {
      $users_attributes = read_passwd_file ();
      foreach ($users_attributes as $user_attributes)
      {
	if ($user_attributes["username"] == $_SESSION["username"])
	  {
	    /* set the php session */
	    foreach ($user_attributes as $attribute_name => $attribute_value)
	    {
	      $_SESSION[$attribute_name] = $attribute_value;
	    }
	    break;
	  }
      }
    }
}

function authenticate_user ($given_username, $given_password)
{
  global $use_mysql, $confirm_new_account;
  $found = 0;
  $return = '';
  if ($use_mysql == true)
    {
      $link = connect_to_users_db ();
      $sql = 'SELECT *  FROM `user` WHERE username = "'.$given_username.'"';
      $result =
	mysql_query ($sql) or die (" $result Error: $sql<p>".mysql_error ());
      $row = mysql_fetch_assoc ($result);
      if ($row["password"] == md5 ($given_password))
	{
	  //check if account is confirmed (if needed)
	  if ($confirm_new_account)
	    {
	      $sql =
		'SELECT *  FROM `user_tokens` WHERE username = "'.
		$given_username.'"';
	      $query =
		mysql_query ($sql) or die (" $result Error: $sql<p>".
					   mysql_error ());
	      if (!$query || (mysql_num_rows ($query) == 0))
		{
		  return
		    "<p class=\"error\">No token exists for such a user.</p>";
		}
	      $row = mysql_fetch_assoc ($query);
	      if ($row["status"] == "unconfirmed")
		{
		  return
		    "<p class=\"error\">This account has not yet been confirmed.Check your email</p>";
		}
	    }
	  $_SESSION["username"] = $given_username;
	  assign_privileges ();
	  $found = 1;
	}

    }
  else
    {
      $users_attributes = read_passwd_file ();
      foreach ($users_attributes as $user_attributes)
      {
//                              if(($user_attributes["username"] == $given_username) && ($user_attributes["password"] == md5($given_password) ))
	if (($user_attributes["username"] == $given_username) &&
	    (check_real_password($user_attributes["username"], $given_password)))
	  {
	    /* set the php session */
	    $_SESSION["username"] = $_POST["username"];
	    assign_privileges ();
	    $found = 1;
	    break;
	  }
      }
    }
  if (!$found)
    return
      "<p class=\"error\">Wrong username and/or password. Authentication failed.</p>";
}

?>
