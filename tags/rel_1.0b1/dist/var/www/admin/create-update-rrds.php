#!/usr/bin/php 
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


/*This is supposed to run periodically and update the 
rrd databases, with signal and rate, of all wifs*/

include("include/config.php");
include("include/functions.php");

//get status of wireless ifs
$status = get_wireless_status();

//change current working directory to the directory where the script is located
//$script_path=dirname($_SERVER["PHP_SELF"]);
//chdir($script_path);

//check if rrd database path exists, if not, create
if (!is_dir( $rrd_database_path))
	mkdir( $rrd_database_path, 0777);

// For each wireless if get wif name, signal, and rate into rrds
foreach ( $status as $device => $device_status)
{
	//if device in master mode, create-update number of connected users rrd
	if ( $device_status["mode"] == "Master"){
		$nusers_rrd_filename = $rrd_database_path.$device."-nusers.rrd";
		//check whether the coresponding rrd exists, if not, create
		if (!file_exists($nusers_rrd_filename)) {
			print "creating nusers database for $device\n";
			$rrd_create_cmd = $rrdtool_bin." create ".$nusers_rrd_filename."\
				--step 300\
				DS:nusers:GAUGE:600:U:U\
				RRA:AVERAGE:0.5:1:600\
				RRA:AVERAGE:0.5:6:700\
				RRA:AVERAGE:0.5:24:775\
				RRA:AVERAGE:0.5:288:797\
				RRA:MAX:0.5:1:600\
				RRA:MAX:0.5:6:700\
				RRA:MAX:0.5:24:775\
				RRA:MAX:0.5:288:797";
			echo "create_command ".$rrd_create_cmd."\n";
			$out = `$rrd_create_cmd`;
			echo $out."\n";
		}
		//insert nusers into rrd
		$nusers = get_connected_users_num( $device );
		$rrd_update_cmd = $rrdupdate_bin."-gauge ".$device;
		echo "Updating nusers rrd for ".$device."\n".$rrd_update_cmd."\n";
		$out = `$rrd_update_cmd`;
		echo $out;
		continue;
	}

	//if device not in master mode, create signal-rate graphs
	$rrd_filename = $rrd_database_path.$device."-radio.rrd";
	//check whether the coresponding rrd exists, if not, create
	if (!file_exists($rrd_filename)) {
		print "creating signal-rate database $rrd_filename for $device\n";
		$rrd_create_cmd = $rrdtool_bin." create ".$rrd_filename."\
			--step 300\
			DS:signal:GAUGE:600:U:U\
			DS:rate:GAUGE:600:U:U\
			RRA:AVERAGE:0.5:1:600\
			RRA:AVERAGE:0.5:6:700\
			RRA:AVERAGE:0.5:24:775\
			RRA:AVERAGE:0.5:288:797\
			RRA:MAX:0.5:1:600\
			RRA:MAX:0.5:6:700\
			RRA:MAX:0.5:24:775\
			RRA:MAX:0.5:288:797";
		echo "create_command ".$rrd_create_cmd."\n";
		$out = `$rrd_create_cmd`;
		echo $out."\n";
	}
	
	//insert signal, rate into rrd
	$rrd_update_cmd = $rrdupdate_bin."-radio ".$device;
	echo "Updating rrd for ".$device."\n".$rrd_update_cmd."\n";
	$out = `$rrd_update_cmd`;
	echo $out;
}

//for all ethernet interfaces (except loopback), create-update, traffic rrds

//get ethernet ifs
$devices_status = get_ethernet_status();

foreach ($devices_status as $device => $device_status){
	//if tx, rx are empty (because of a fake device for example)
	if ( $device_status["tx"]=="")
		continue;
	$traffic_rrd_filename = $rrd_database_path.$device."-traffic.rrd";
	if (!file_exists($traffic_rrd_filename)) {
		print "creating traffic database for $device\n";
		$rrd_create_cmd = $rrdtool_bin." create ".$traffic_rrd_filename."\
			--step 300  \
			DS:in:COUNTER:600:U:U \
			DS:out:COUNTER:600:U:U \
			RRA:AVERAGE:0.5:1:600 \
			RRA:AVERAGE:0.5:6:700 \
			RRA:AVERAGE:0.5:24:775 \
			RRA:AVERAGE:0.5:288:797 \
			RRA:MAX:0.5:1:600 \
			RRA:MAX:0.5:6:700 \
			RRA:MAX:0.5:24:775 \
			RRA:MAX:0.5:288:797";
		echo "create_command ".$rrd_create_cmd."\n";
		$out = `$rrd_create_cmd`;
		echo $out."\n";
	}
	
	//insert tx,rx into rrd
	$rrd_update_cmd = $rrdupdate_bin." ".$device;
	echo "Updating rrd for ".$device."\n".$rrd_update_cmd."\n";
	$out = `$rrd_update_cmd`;
	echo $out;
}
	







?>
