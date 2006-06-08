WiFiAdmin - A Web Interface For Wireless Tools
==============================================

Authors:
Panousis Thanos <panousis@ceid.upatras.gr>
Dimopoulos Eythimios <dimopule@ceid.upatras.gr> 

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License version 2 as
published by the Free Software Foundation. See COPYING for more
details.

Wifiadmin is a free php graphical interface to mainly hostap, and
wireless tools in general, although most of this code has been
debugged only under a hostap environment. Please submit errors
found when running with other linux wireless drivers, that 
support wireless extensions. We are trying so that wifiadmin
is as self-configuring and distribution-independent as possible.

Prerequisites
=============

A fairly recent version of RRD-tool must be installed on the system.
An http server with PHP support must also be running. You will also
need the command line interface(CLI) for php. If 'which php' returns
some path, CLI is present in your system.

WiFiAdmin has a community section, that holds information about conected
clients usind a mysql database. You can disable mysql usage, by the 
$use_mysql variable in the include/config.php file, which will disable
the community section. Note that if you use mysql, usernames, passwords and
user privileges will be saved in the database.

Wifiadmin can send emails, in order to sonfirm new accounts or remind user passwords
You can disable this functionality using $send_emails variable in config.php 

 According to the php manual:
  For the Mail functions to be available, PHP must have access to the sendmail binary
  on your system during compile time. If you use another mail program, such as qmail
  or postfix, be sure to use the appropriate sendmail wrappers that come with them.
  PHP will first look for sendmail in your PATH, and then in the following:
  /usr/bin:/usr/sbin:/usr/etc:/etc:/usr/ucblib:/usr/lib. It's highly recommended to
  have sendmail available from your PATH. Also, the user that compiled PHP must have
  permission to access the sendmail binary.
  
This usually means that if a normal user of your system is able to send emails, then
wifiadmin can do that too.


Installation
============

Grab the latest release from http://sourceforge.net and procceed
with the followning steps:

1) Decompress in a directory reachable from your web server(htdocs),
  keeping in mind the directory structure.

2) If you disable mysql usage($use_mysql variable in the include/config.php
  file):
  
  Give write access to the user that you are running PHP, to the file
  include/passwd .For example, from wifiadmin root directory type
  'chown apache:/ include; chown apache:/ include/passwd', depending
  on your local configuration.
  
  If you enable mysql usage:
  
  Within the directory, you'll find a a file named "mysql.sql". 
  Contained in this file are the SQL statements necessary to create the 
  WiFiAdmin database. Log onto your database server under the root account (or 
  other account allowed to create databases), create a database for wifiadmin, and
  then run the contents of mysql.sql to create the tables and initial data.
  
  For example:

  mysqladmin -u [user] -p create [database-name]
  mysql -u [user] -p [database-name] < mysql.sql
  
  Or you can use phpMyAdmin to do the same.
  
  In either case, you have to edit the include/config.php file and edit the
  following variables:
  
  $USERS_DBHOST = "localhost";       host where mysql is running
  $USERS_DB = "wifiadmin";           [database-name]
  $USERS_DBUSER = "wifiadmin";	     [user] 
  $USERS_DBPASS = "wifiadmin";       the password of [user]
  

3) Change your sudo configuration.
  Wifiadmin needs superuser access to specific executables. For the moment,
  this is done by giving superuser access to the user apache runs with,
  using the sudo mechanism. You can find out the user that apache runs with, by:
  
  ps aux | grep http
  
  or
  
  ps aux | grep apache
  
  You should use the visudo executable, or manually edit the file 'sudoers'.
  Add the following lines, replace www-data with the user apache runs with.
  
  # Cmnd alias specification
  Cmnd_Alias      WIFIADMIN = /sbin/iwconfig, /sbin/ifconfig, /sbin/iwlist,
  /sbin/iwpriv,  /sbin/route, /usr/bin/host, /usr/sbin/arp

  # User privilege specification
  www-data ALL=(ALL) NOPASSWD: WIFIADMIN

  Change the paths if iwconfig, ifconfig etc executables are located
  elsewhere in your system.
  
  WARNING
  This configuration, gives superuser priviledges for the specific commandsto the user your 
  web server runs with. This might have implications on system security.

4) Add the following lines at the end of your crontab (you can use the
  crontab -e command, or edit your /etc/crontab directly)

*/2 * * * *  root php /path/to/wifiadmin/create-update-rrds.php > /dev/null 2>&1
*/10 * * * * root php /path/to/wifiadmin/create_graphs.php > /dev/null 2>&1
  
  The first line creates(for the first time) and updates the rrd databases. The second
  creates the updated png graphs from the databases. RRD updates happen every 2 minutes,
  and graphs are crated every 10 minutes. Change these values to suit your needs. Mind 
  space characters from copy-pasting into your crontab!!

5) Wifiadmin can send emails to confirm new user accounts, or remind user passwords.
  If $send_emails = true; in config.php file, wifiadmin will send emails.
  If you set $confirm_new_account = false; while $send_emails = true;, wifiamdin will
  send emails to remind passwords, but no email confirmation will be needed in order
  to set up an account.
   
6) Fire up your favorite browser, and point to
  'http://target-hostname/wifiadmin/'
  ATTENTION: WIFIADMIN HAS A DEFAULT ADMIN PASSWORD. Change the admin
  password as soon as possible, by logging in as user 'admin' with
  password 'wifiadmin'. Go to user management, choose 'admin', and 
  type a different password. You can LOOSE complete network access
  to your box if you are careless enough...

7) Enjoy :)

UPGRADING
=========

If you are upgrading from a previous version, you really have less to do.
The only thing that needs altering if are installing a version greater than
0.0.3 is your sudo configuration.
Cron jobs are the same.


NOTES
=====

Users
Wifiadmin supports users with different priviledges. You can create
different classes of users. A non-logged in user, defaults to the
'guest' user. You can change the amount of information guest can
access. By design, you cannot get locked out of the system by
deleting the last user that has an edit-users priviledge.

Banning MACs
Banning a MAC with wifiadmin is easy, through the wireless status page.
You can unban MACs, view and alter Access Control policies in wireless
security page. Note that banning a MAC on an open-policy AP will result
in a deny policy.

Manual Configuration
Some things can be manually configured in the include/config.php file. The 
variables that are suppossed to be messed with, are marked by comments
Change other variables at your own risk.

RRD
Wifiadmin makes use of round robin dbs, and we take for granted that you have a
recent version of rrd-tool.
