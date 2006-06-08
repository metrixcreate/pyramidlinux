<?php

$status = get_wireless_status();

//check if graphs path exists, if not, create
if (!is_dir( $graphs_path))
	mkdir( $graphs_path);

//for wireless devices
foreach ( $status as $device => $device_status)
{
	//echo print_r($device_status);
	//if device in master mode create only nusers graphs
	if ( $device_status["mode"] == "Master"){
		//check whether the coresponding rrd exists, if not, complain
		$nusers_rrd_filename = $rrd_database_path.$device."-nusers.rrd";
		if (!file_exists($nusers_rrd_filename)) {
			//print "nusers rrd file for $device does not exist";
			return;
		}
		create_nusers_graphs($device);
		continue;
	}
	
	//if device not in master mode, create signal-noise graphs
	$rrd_filename = $rrd_database_path.$device."-radio.rrd";
	
	//check whether the coresponding rrd exists, if not, complain
	if (!file_exists($rrd_filename)) {
		//print "rrd file for $device does not exist";
		return;
	}
	create_signal_noise_graphs($device);
}

//for all ethernet devices
$devices_status = get_ethernet_status();

foreach ($devices_status as $device => $device_status){
	//if tx, rx are empty (because of a fake device for example)
	if ( $device_status["tx"]=="")
		continue;
	$rrd_filename = $rrd_database_path.$device."-traffic.rrd";
	if (!file_exists($rrd_filename)) {
		//print "rrd file for $device does not exist";
		return;
	}
	create_traffic_graphs($device);
}

function create_signal_noise_graphs($device){
// creates signal - noise and link graphs
// inputs: $device: interface name (ie, eth0 wlan1)
	global $rrd_database_path, $rrdtool_bin, $graphs_path;
	$intervals = array("daily" => -86400, "weekly" =>-604800, "monthly" =>-2678400, "yearly" => -33053184);
	
	foreach ( $intervals as $interval => $magic_number)
	{
		# generate signal/noise graph
		$graph_create_cmd = $rrdtool_bin." graph ".
		$graphs_path."$device-signal-$interval.png\
		--lazy\
		--imgformat=PNG \
		--start=$magic_number\
		--title=\"$interval $device signal-noise\"\
		--rigid \
		--base=1000\
		--height=120\
		--width=500\
		--upper-limit=100\
		--lower-limit=40 \
		--vertical-label=dBm\
		DEF:signal=\"".$rrd_database_path.$device."-radio.rrd\":signal:AVERAGE \
		AREA:signal#74C366:\"signal\" \
		GPRINT:signal:MIN:\"Min\:-%3.0lf dBm\" \
		GPRINT:signal:MAX:\"Max\:-%3.0lf dBm\" \
		GPRINT:signal:AVERAGE:\"Avg\:-%3.0lf dBm\" \
		GPRINT:signal:LAST:\"Current\:-%3.0lf dBm\\n\" \
		DEF:noise=\"".$rrd_database_path.$device."-radio.rrd\":noise:AVERAGE  \
		LINE2:noise#FF0000:\"noise \" \
		GPRINT:noise:MIN:\"Min\:-%3.0lf dBm\" \
		GPRINT:noise:MAX:\"Max\:-%3.0lf dBm\" \
		GPRINT:noise:AVERAGE:\"Avg\:-%3.0lf dBm\"\
		GPRINT:noise:LAST:\"Current\:-%3.0lf dBm\"";
		//echo $graph_create_cmd ."\n";
		$out = `$graph_create_cmd`;
		//echo $out."\n";
		//generate rate graph
		$graph_create_cmd = $rrdtool_bin." graph ".
		$graphs_path."$device-rate-$interval.png\
		--lazy\
		--imgformat=PNG\
		--start=$magic_number\
		--title=\" $device rate $interval graph\"\
		--rigid \
		--base=1000\
		--height=120\
		--width=500\
		--alt-autoscale-max\
		--lower-limit=0 \
		--vertical-label=\"Mbps\" \
		DEF:rate=\"".$rrd_database_path.$device."-radio.rrd\":rate:AVERAGE \
		AREA:rate#0000FF:\"Link Rate\" \
		GPRINT:rate:MIN:\"Min\:%3.0lf\" \
		GPRINT:rate:MAX:\"Max\:%3.0lf\" \
		GPRINT:rate:AVERAGE:\"Avg\:%3.0lf\" \
		GPRINT:rate:LAST:\"Current\:%3.0lf\"";
		
		/*GPRINT:rate:LAST:\"Current\:%8.2lf %s\" \*/
		
		/*
		GPRINT:out:LAST:\"Current\:%8.2lf %s\"  \
		GPRINT:out:AVERAGE:\"Average\:%8.2lf %s\"  \
		GPRINT:out:MAX:\"Maximum\:%8.2lf %s\"";*/
		//echo $graph_create_cmd ."\n";
		$out = `$graph_create_cmd`;
		//echo $out."\n"; 
	}
}

function create_nusers_graphs($device){
// creates nusers graphs
// inputs: $device: interface name (ie, eth0 wlan1)
	global $rrd_database_path, $rrdtool_bin, $graphs_path;
	$intervals = array("daily" => -86400, "weekly" =>-604800, "monthly" =>-2678400, "yearly" => -33053184);
	
	foreach ( $intervals as $interval => $magic_number)
	{
		# generate nusers graph
		$graph_create_cmd = $rrdtool_bin." graph ".
		$graphs_path."$device-nusers-$interval.png\
		--lazy\
		--imgformat=PNG\
		--start=$magic_number\
		--title=\"$interval associations on device $device\"\
		--rigid \
		--base=1000\
		--height=120\
		--width=500\
		--alt-autoscale-max \
		--lower-limit=0 \
		--vertical-label=users\
		DEF:nusers=\"".$rrd_database_path.$device."-nusers.rrd\":nusers:AVERAGE \
		AREA:nusers#74C366:\"number of users\" \
		GPRINT:nusers:MIN:\"Min\:%4.0lf\" \
		GPRINT:nusers:MAX:\"Max\:%4.0lf\" \
		GPRINT:nusers:AVERAGE:\"Avg\:%4.0lf\" \
		GPRINT:nusers:LAST:\"Current\:%4.0lf\"";
		//echo $graph_create_cmd ."\n";
		$out = `$graph_create_cmd`;
		//echo $out."\n";
		}
}

function create_traffic_graphs($device){
	global $rrd_database_path, $rrdtool_bin, $graphs_path;
	$intervals = array("daily" => -86400, "weekly" =>-604800, "monthly" =>-2678400, "yearly" => -33053184);
	foreach ( $intervals as $interval => $magic_number)
	{
		$graph_create_cmd = $rrdtool_bin." graph ".
		$graphs_path."$device-traffic-$interval.png\
		--lazy\
		--imgformat=PNG \
		--start=$magic_number\
		--title=\"$device $interval traffic\" \
		--rigid \
		--base=1000 \
		--height=120 \
		--width=500 \
		--alt-autoscale-max \
		--lower-limit=0 \
		--vertical-label=\"bytes per second\" \
		DEF:in=\"".$rrd_database_path.$device."-traffic.rrd\":in:AVERAGE \
		AREA:in#00CF00:\"Inbound \"  \
		GPRINT:in:MIN:\"Min\:%8.2lf %s\"  \
		GPRINT:in:MAX:\"Max\:%8.2lf %s\"  \
		GPRINT:in:AVERAGE:\"Avg\:%8.2lf %s\"  \
		GPRINT:in:LAST:\"Current\:%8.2lf %s\\n\"  \
		DEF:out=\"".$rrd_database_path.$device."-traffic.rrd\":out:AVERAGE \
		LINE1:out#002A97:\"Outbound\"  \
		GPRINT:out:MIN:\"Min\:%8.2lf %s\"  \
		GPRINT:out:MAX:\"Max\:%8.2lf %s\" \
		GPRINT:out:AVERAGE:\"Avg\:%8.2lf %s\"  \
		GPRINT:out:LAST:\"Current\:%8.2lf %s\"";
//		echo "<pre>".$graph_create_cmd ."\n";
		$out = `$graph_create_cmd`;
//		echo $out."\n</pre>"; 
	}
}

?>


