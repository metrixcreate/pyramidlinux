<?php
/*
 * Consolidated IP related functions
 */
 
//returns a 32char string 
function dectobin($dectobin) {
	$cadtemp="";
	$dectobin = decbin($dectobin);
	$numins = 8 - strlen($dectobin);

	for ($i = 0; $i < $numins; $i++) {
		$cadtemp = $cadtemp."0";
	}
return $cadtemp.$dectobin;
}

function broadcast_address($decimal_netmask_octet,$decimal_ip_octet) {
	$tempchar="";
	$bin_bcast="";
	$cadres="";
	$bin_netmask=dectobin($decimal_netmask_octet);
	$bin_ip=dectobin($decimal_ip_octet);

	for ($i=0;$i<8;$i++) {
		$tempchar = substr($bin_netmask,$i,1);
		if ($tempchar=="1") {
			$cadres=$cadres.substr($bin_ip,$i,1);
		} else {
			$cadres=$cadres."1";
		}
	}
	return bindec($cadres);
}

function ipmask($in_ip,$in_mask){
	/* Check netmask */
	if (4 != sscanf($in_mask,"%d.%d.%d.%d", $mask_octets[0], $mask_octets[1], $mask_octets[2], $mask_octets[3])) {
		echo "<p class = \"error\">Invalid netmask \"$in_mask\".</p>";
        	return false;
	}
	foreach ($mask_octets as $mask_octet) {
		if ($mask_octet < 0 || $mask_octet > 255) {
			echo "<p class = \"error\">Invalid octet $mask_octet in \"$in_mask\".</p>";
        		return false;
		}
	}
	
	/* Check IP address */
	if (4 != sscanf($in_ip,"%d.%d.%d.%d", $ip_octets[0],$ip_octets[1],$ip_octets[2],$ip_octets[3])) {
		echo "<p class = \"error\">Invalid ip address \"$in_ip\".</p>";
        	return false;
	}
	foreach ($ip_octets as $ip_octet) {
		if ($ip_octet < 0 || $ip_octet > 255) {
			echo "<p class = \"error\">Invalid octet $ip_octet in \"$in_ip\".</p>";
        		return false;
		}
	}
	
	for($n_octet = 0; $n_octet < 4; $n_octet++)
	{
		$bcast_octets[$n_octet] = broadcast_address($mask_octets[$n_octet], $ip_octets[$n_octet]);
	}
	
	
	$string_bcast = sprintf ("%d.%d.%d.%d",$bcast_octets[0], $bcast_octets[1], $bcast_octets[2], $bcast_octets[3]);
	
	$array["bcast"]= $string_bcast;

	return $array;
}
?>