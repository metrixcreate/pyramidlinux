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

/***************************************************
* The general config file. Variables are kept here *
* PLEASE review the whole file                     *
***************************************************/
error_reporting(E_ALL);
$DOCTYPE = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'."\n";

//page iwstatus can refresh every some seconds
//so that that its information is up-to-date
//leave blank for NO refresh
$status_refresh = "360";

$resolve = false;           //should iwstatus resolve the associated clients IPs to DNS names?Might be a little slower, but its nicer
$resolve_timeout = 1;	//in sec, the timeout for reverce DNS lookups
//you probably don't have to mess with these two:
$hostap_device_path = "/proc/net/hostap/";
$wireless_filename = "/proc/net/wireless";

//Paths to binaries
$iwconfig_bin = 'iwconfig';
$iwlist_bin = 'iwlist';
$iwpriv_bin = 'iwpriv';
$rrdtool_bin = 'rrdtool';
$rrdupdate_bin = 'rrd-update';
$ifconfig_bin = 'ifconfig -a';
$route_bin = 'route';
$host_bin = 'host';
$arp_bin = 'arp';

//set true to authenticate users and give each specific privileges, 
//or false to treat everyone as admin
$global_auth = false;

//Using mysql for authentication and user-community information management, gives wifiadmin 
//a lot more funtionality. If you choose false, usernames, privileges and passords will be stored 
//in a plain text file.
$use_mysql = false;

//set these if you chose true above.

$USERS_DBHOST = "localhost";	//host where mysql is running
$USERS_DB = "wifiadmin";	//database name
$USERS_DBUSER = "wifiadmin";	//mysql user name with provileges on  $USERS_DB database
$USERS_DBPASS = "wifiadmin";	//mysql password

//Where to save the passwd file (when $use_mysql is false)
$passwd_filename = "/etc/wifiadmin.passwd";

//where will rrd tool store its database and the produced graph images 
$rrd_database_path = "rrd_database/";
$graphs_path = "graphs/";

//Send emails to remind passwords, or confirm new accounts, see README file
$send_emails = false;

//Should the email address of an acount be confirmed before activating it?
$confirm_new_account = false;

//Should new users be allowed to register themselves?
//If not, only the admin will be able to add new users to the community
$users_register_themselves = false;

// Emails will be sent from this person
$WEB_MASTER       = "Administrator";
$WEB_MASTER_EMAIL = "root@localhost";

//do you want render time to be recorded and displayed at the bottom of the page?
$count_time = false;

// List files here that are allowed to be viewed / edited
$permitted_files = array(
  "olsr" => "/etc/olsrd.conf",
  "motd" => "/etc/motd",
);


?>
