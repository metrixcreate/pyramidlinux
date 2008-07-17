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

/************************************************
* Functions file				*
*************************************************

/************************************************
* Bytes to MB, GB, TB function                  *
*************************************************/
function ByteSize($bytes) {
    $size = $bytes / 1024;
    if($size < 1024){
        $size = number_format($size, 2);
        $size .= 'KB';
    } else {
        if($size / 1024 < 1024) {
            $size = number_format($size / 1024, 2);
            $size .= 'MB';
        } elseif($size / 1024 / 1024 < 1024) {
            $size = number_format($size / 1024 / 1024, 2);
            $size .= 'GB';
        } else {
            $size = number_format($size / 1024 / 1024 / 1024, 2);
            $size .= 'TB';
        }
    }
    return $size;
}
function kernel () {
	if ($fd = fopen('/proc/version', 'r')) {
        	$buf = fgets($fd, 4096);
		fclose($fd);
     		if (preg_match('/version (.*?) /', $buf, $ar_buf)) {
             		$result = $ar_buf[1];
             		if (preg_match('/SMP/', $buf)) {
             		$result .= ' (SMP)';
        	}
     		} else {
        		$result = 'N.A.';
     		}
    	} else {
    		$result = 'N.A.';
     	}
     	return $result;
}


function uptime () {
     $fd = fopen('/proc/uptime', 'r');
     $ar_buf = split(' ', fgets($fd, 4096));
     fclose($fd);
     $sys_ticks = trim($ar_buf[0]);
     $min = $sys_ticks / 60;
     $hours = $min / 60;
     $days = floor($hours / 24);
     $hours = floor($hours - ($days * 24));
     $min = floor($min - ($days * 60 * 24) - ($hours * 60));
     $result = "";
     if ($days != 0) {
          if ($days > 1)
     		$result = "$days " ." days ";
	  else
	  	$result = "$days " ." day ";
     }
     if ($hours != 0) {
	 if ($hours > 1)
         	$result .= "$hours " ." hours ";
	 else
	 	$result .= "$hours " ." hour ";
     }
     if ($min > 1 || $min == 0)
        $result .= "$min " ." minutes ";
     elseif ($min == 1) 
	$result .= "$min " ." minute ";
	  
     return $result;
}

function chostname () {
      if ($fp = fopen('/proc/sys/kernel/hostname', 'r')) {
		$result = trim(fgets($fp, 4096));
	        fclose($fp);
		//$result = gethostbyaddr(gethostbyname($result));
	} else {
		$result = '';
	}
	return $result;
}

##
# Return the domain name of the machine
# as defined in dnsmasq.conf 
function get_domain() {
  if ($fp = fopen('/etc/dnsmasq.conf', 'r')) {
    while(!feof($fp)) {
      $line = fgets($fp);
      if(ereg('domain=(.*)',$line,$regs)) {
        fclose($fp);
        return trim($regs[1]);
      }
    }
    fclose($fp);
  }
  return "";
}

##
# Set the domain name in dnsmasq.conf
#
function set_domain($domain){
  if (!$domain) 
    return false;
    
  file_replace("/etc/dnsmasq.conf","^domain=.*", "domain=$domain\n");
  file_replace("/etc/dnsmasq.conf","^dhcp-option=119,.*", "dhcp-option=119,$domain\n");

#  file_replace("/etc/olsrd.conf","  PlParam \"suffix\".*", "  PlParam \"suffix\" \".$domain\"\n");

  privcmd("killall dnsmasq", false);	# Restarts in init
#  privcmd("killall olsrd", false);	# Restarts in init

  return true;
}
					    
function parse_assoc($associations)
{
     $list = preg_split("/[\s]+/", $associations);
     $tx_bytes = array();
     $rx_bytes = array();
     $macs = array();
     $noise = array();
     $signal = array();
     $rates = array();
     
     for($i=0;$i<count($list)-1;$i++)
     {
                // for HostAP
                if(!(strpos($list[$i],"STA=" )=== FALSE))
                $macs[] = str_replace("STA=","",$list[$i]);

	        elseif (!(strpos($list[$i],"rx_bytes=")=== FALSE))
                $rx_bytes[] = str_replace("rx_bytes=","",$list[$i]);

                elseif (!(strpos($list[$i],"tx_bytes=")=== FALSE))
                $tx_bytes[] =  str_replace("tx_bytes=","",$list[$i]);

                elseif (!(strpos($list[$i],"silence=")=== FALSE))
                $noise[] = str_replace("silence=","",$list[$i]);

                elseif (!(strpos($list[$i],"signal=")=== FALSE))
                $signal[] = str_replace("signal=","",$list[$i]);

                // for madwifi
                elseif(preg_match("/<(.*)>/",$list[$i],$regs)) {
                  $macs[] = $regs[1];
                  $rx_bytes[] = 0;
                  $tx_bytes[] = 0;
                  $noise[] = 0;
                  $signal[] = 0;
                }
	}

for($i=0;$i<count($tx_bytes);$i++)
{
    if ($rx_bytes[$i] == 0){
	$rates[$i] =0;
	continue;
    }
    if($tx_bytes[$i]==0)
    $rates[$i] = 0;
    else
    $rates[$i] = round($rx_bytes[$i]/$tx_bytes[$i],4);
    $rates[$i] = $rates[$i]*100;
}

return $ans = array('macs'=>$macs,'rx_bytes'=>$rx_bytes,'tx_bytes'=>$tx_bytes,'rates'=>$rates,'signal'=>$signal,'noise'=>$noise);
}


/************************************************
* freq2channel($freq)
* returns the channel # of a given frequency of 802.11
************************************************/
function freq2channel($freq)
{
	$out = array(".","GHz");
	$freq = str_replace($out,"",$freq);
	$chan = ($freq - 2412)/5 + 1;
	$chan = ($chan < 10) ? "0$chan" : $chan;
	return ($chan < 1) ? false : $chan;
}
/************************************************
* channel2freq($channel)
* returns the frequency of a given channel of 802.11
************************************************/
function channel2freq($channel)
{
        if(is_int($channel))
        return ($channel+1 * 5) + 2.412;
        else return -1;
}

/************************************************
* privcmd()
* Run a command under sudo, remounting as necessary
*************************************************/
function privcmd($cmd,$mount) {
  if($mount) {
    return `sudo remountrw; sudo $cmd; sudo remountro`;
  }
  else {
    return `sudo $cmd`;
  }
}

/************************************************
* bgprivcmd()
* Run a command under sudo in the background
*************************************************/
function bgprivcmd($cmd) {
  shell_exec('sudo $cmd &');
  return true;
}

/************************************************
* rebootsys()
* Reboot the system
*************************************************/
function rebootsys() {
  return `/var/www/admin/include/reboot.sh &`;
}

/************************************************
* get_modem()
* Return the currently selected cellular modem
*************************************************/
function get_modem() {
  if(! file_exists('/dev/cellmodem')) {
    return "No modem found or modem not enabled";
  }
  
  $modem = readlink('/dev/cellmodem');
  if($modem == "/dev/tts/2") {
    $modem = "1xRTT";
  }
  elseif($modem == "/dev/usb/tts/0") {
    $modem = "EVDO";
  }
  elseif($modem == "/dev/ttyUSB0") {
    $modem = "EVDO or HSDPA";
  }
  else {
    $modem = "No modem found or modem not enabled";
  }
  return $modem;
}

/************************************************
* get_ppp_status()
* What is PPP up to?
*************************************************/
function get_ppp_status() {
  $filename = "/var/tmp/ppp-status";
  if(! file_exists($filename)) {
    return "";
  }

  $fd = fopen($filename,"r");
  $contents = fread($fd,filesize($filename));
  fclose($fd);

  return $contents;
}

/************************************************
* get_zone()
* Return the current timezone
*************************************************/
function get_zone() {
  $zone = readlink('/etc/localtime');
  $zone = substr($zone, 20);	// strip /usr/share/zoneinfo/
  return $zone;
}

/************************************************
* set_zone()
* Change the timezone
*************************************************/
function set_zone($zone) {
  privcmd("ln -sf /usr/share/zoneinfo/$zone /etc/localtime",true);
  return true;
}

/************************************************
* get_rc()
* Is this service enabled at boot?
*************************************************/
function get_rc($file) {
  if(file_exists("/etc/rc2.d/$file")) {
    return true;
  }
  else {
    return false;
  }
}

/************************************************
* set_rc()
* Enable or disable a service
*************************************************/
function set_rc($file,$priority) {
  if($priority == 0) {
    privcmd("/etc/init.d/$file stop",false);
    privcmd("rm -f /etc/rc2.d/S*$file",true);
  }
  else {
    privcmd("ln -sf /etc/init.d/$file /etc/rc2.d/S$priority$file",true);
    return privcmd("/etc/init.d/$file start",false);
  }
  //return true;
}

/************************************************
* set_rc_no_start()
* Enable or disable a service
*************************************************/
function set_rc_no_start($file,$priority) {
  if($priority == 0) {
    privcmd("rm -f /etc/rc2.d/S*$file",true);
  }
  else {
    privcmd("ln -sf /etc/init.d/$file /etc/rc2.d/S$priority$file",true);
  }
  //return true;
}

/************************************************
* set_hostname()
* Change the hostname
*************************************************/
function set_hostname($hostname) {
  privcmd("hostname $hostname",false);
  `echo $hostname > /var/tmp/hostname`;
  privcmd("cp /var/tmp/hostname /ro/etc/hostname",true);
  `rm -f /var/tmp/hostname`;
  return true;
}

/************************************************
* get_sshkeys()
* Return root's authorized_keys2
*************************************************/
function get_sshkeys() {
  $filename = '/root/.ssh/authorized_keys2';
  if(file_exists($filename) && (filesize($filename) > 0)) {
    $fd = fopen($filename, 'r');
    $file_contents=fread($fd,filesize($filename));
    fclose($fd);
    return $file_contents;
  }
  else {
    return "";
  }
}

/************************************************
* set_sshkeys()
* Install new ssh keys for root
*************************************************/
function set_sshkeys($sshkeys) {
  $sshkeys = "$sshkeys\n";
  $rootkeys = "/root/.ssh/authorized_keys2";
  if(file_exists($rootkeys)) {
    $fd = fopen($rootkeys,"r");
    $contents = fread($fd,filesize($rootkeys));
    fclose($fd);
    if($contents == $sshkeys) {
      // Nothing has changed, so no need to update
      return false;
    }
  }
  $fd = fopen("/var/tmp/tmpkeys","w");
  fwrite($fd,"$sshkeys");
  fclose($fd);
  privcmd("cp /var/tmp/tmpkeys /ro/$rootkeys",true);
  privcmd("cp /var/tmp/tmpkeys $rootkeys",false);
  `rm -f /var/tmp/tmpkeys`;
  return true;
}

/************************************************
* get_wif() 
* returns an array in the following format
* ans[i] = ith interface's name
* ans[i+1] = ith interface's mode
************************************************/	
function get_wifs()
{
	global $iwconfig_bin;
	//wifs contains the names of the wifi devices
	$wifs = explode("\n",trim(`ls /proc/net/hostap/`));
	//gets the modes of the wifi devs
	$i = 0;
	foreach($wifs as $var)	//finds modes of every wifi
	{
		$cmd = "$iwconfig_bin ".$var;
		$temp = trim(`$cmd`);
		$pos = strpos($temp,"Master");
		if(!($pos === false))
		{$ans[$i] = $var;
		$ans[$i+1] = "Master";}
		else
		{
			$pos = strpos($temp,"Managed");
			if(!($pos === false))
			{$ans[$i] = $var;
			$ans[$i+1] = "Managed";}
			else
			{
				$pos = strpos($temp,"Ad-Hoc");
				if(!($pos === false))
				{$ans[$i] = $var;
				$ans[$i+1] = "Ad-Hoc";
				}
				else
				{$ans[$i] = $var;
				$ans[$i+1] = "unknown";}
			}
		}		
		$i=$i+2;
	}
	return $ans;
}
/************************************************************
* Returns the security policy of the specified device
* Device must be in master mode or else garbage is returned
*************************************************************/
function get_policy($wif)
{
	$ap_control_file = "/proc/net/hostap/".$wif."/ap_control";
        $data = file($ap_control_file);
        sscanf($data[0],"MAC policy: %s", $security['policy']);
	if(sizeof($data) > 3)
	{
		for($i=3;$i<sizeof($data);$i++)
                        $security[$i-3] = $data[$i];
        }
	return $security;
}
/************************************************************
* Bans a MAC address from the master mode device given.
* operation varies depending on current Access control list
* policy.
************************************************************/
function ban_user($wif,$MAC)
{
	global $iwpriv_bin;
	$security = get_policy($wif);
	$wif = escapeshellarg($wif);
	$MAC = escapeshellarg($MAC);
	if( $security['policy'] == "open" )
	{
		$cmd =$iwpriv_bin." ".$wif." maccmd 2"; 
		
		echo `$cmd`;	//change to deny policy
		echo `$iwpriv_bin $wif addmac $MAC`;	//add the MAC to the deny list
		echo `$iwpriv_bin $wif kickmac $MAC`;	//and finally kick the bastard!!
	}
	elseif( $security['policy'] == "allow" )
        {	
		$cmd = $iwpriv_bin." ".$wif ." delmac ".$MAC; 
                echo `$cmd`;      //delete the MAC from the allow list
                echo `$iwpriv_bin $wif kickmac $MAC`;     //and finally kick the bastard!!
        }
	elseif( $security['policy'] == "deny" )
        {
		$cmd = $iwpriv_bin." ".$wif ." addmac ". $MAC; 
                echo `$cmd`;      //delete the MAC from the allow list
                echo `$iwpriv_bin $wif kickmac $MAC`;     //and finally kick the bastard!!
        }
	else
	echo "<p class=\"error\">An error has occured in the ban proccess</p>";
}
/*an1     IEEE 802.11b  ESSID:"PWN_aroi"
          Mode:Managed  Frequency:2.432GHz  Access Point: 00:40:96:38:9:0
          Bit Rate:2Mb/s   Tx-Power:128 dBm   Sensitivity=1/3
          Retry limit:8   RTS thr:off   Fragment thr:off
          Encryption key:off
          Power Management:off
          Link Quality:24/92  Signal level:-68 dBm  Noise level:-95 dBm
          Rx invalid nwid:0  Rx invalid crypt:42  Rx invalid frag:0
          Tx excessive retries:416352  Invalid misc:70361034   Missed beacon:0 */
//use for use within parse_iwconfig
function getnext($haystack,$needle)
{
        $pos = strpos($haystack,$needle);
	if($pos == true)
	{
		$pos+= strlen($needle);
		$str = substr($haystack, $pos+1);
		$str= ltrim($str);
		return trim(substr($str,0,strpos($str," ")));
	}
	else
	return "";
}


function getmicrotime()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function error_echo($string)
{
	return "<p class=\"error\">".$string."</p>";
}

//this works for hostap only
function get_connected_users_num( $device ){

if(ereg("^wlan.*",$device))
   {

	global $hostap_device_path;
	$dh  = opendir($hostap_device_path);
	while (false !== ($filename = readdir($dh))) {
		$device_dirs[] = $filename;
	}
	if ( array_search( $device, $device_dirs) == false)
		return false;
	$dh  = opendir($hostap_device_path.$device);
	while (false !== ($filename = readdir($dh))) {
		$files[] = $filename;
	}
	$users_num = 0;
	foreach( $files as $file){
		if (strstr( $file , ':') != false)
			$users_num++;
	}
	echo "Prism2";
	echo $users_num;
	return $users_num;
   }

if(ereg("^ath.*",$device))
   {
	global $madwifi_device_path;
        $filename = "/proc/net/madwifi/".$madwifi_device_path.$device."/associated_sta";
        $file_contents=file_get_contents($filename);
  	$whole_lines=explode("\n",$file_contents);
        $users_num = 0;	
  	foreach ($whole_lines as $whole_line) {
    	if(ereg("macaddr",$whole_line)) {
      	$users_num++;
    					}
  	}
	echo "Atheros";
	echo $users_num;
    
    return $users_num;
    }
}

function get_value($input_string, $attribute){
	sscanf(strstr($input_string, $attribute) ,$attribute."%s",$out);
	return trim($out);
}

function get_flag($input_string, $attribute) {
	if(strstr($input_string, $attribute)) {
	  return true;
	}  
	else {
	  return false;
	}
}

function get_all_values($input_string, $attribute){
	$values = array();
	$count = 0;
	$next_pos = strpos($input_string, $attribute);
	while(!($next_pos === false)){
		sscanf(strstr($input_string, $attribute), $attribute."%s",$value);
		$values[$count] = trim($value);
		$count++;
		$input_string = substr($input_string, $next_pos + strlen($attribute));
		$next_pos = strpos($input_string, $attribute);
	}
	return $values;
}

//returns the following attributes of a wifi device
//given its "iwconfig wlanX" output 
function parse_iwconfig($wif)
{
	$output = `iwconfig $wif`;
	//IWCONFIG OUTPUT PARSING!!
	$data['type'] = getnext($output,"IEEE");
	$data['nick'] = getnext($output,"Nickname");
//	$data['essid'] = getnext($output,"ESSID");
	ereg('ESSID:"(.*)" .*',$output,$regs);
	$data['essid'] = $regs[1];
	$data['mode'] =  getnext($output,"Mode");
//	$data['channel'] =  freq2channel(str_replace("GHz","",getnext($output,"Frequency")));
        $data['channel'] = get_channel($wif);
	$data['ap'] =  getnext($output,"Access Point");
	$data['cell'] =  getnext($output,"Cell");
	$data['quality'] =  getnext($output,"Link Quality");
	$data['signal'] =  getnext($output,"Signal level");
	$data['noise'] =  getnext($output,"Noise level");
	$data['rate'] =  getnext($output,"Rate");
	$data['sens'] =  getnext($output,"Sensitivity");
//	$data['retry'] =  getnext($output,"limit");//fix "retry min limit" and "retry limit" that exist in different versions of hostap/witools
        ereg("Retry( min)?( limit)?:([[:alnum:]]+) ",$output,$regs);
        $data['retry'] = $regs[3];
	$data['rts'] =  getnext($output,"RTS thr");
        if($data['rts'] >= 65534)
          $data['rts'] = "off";
	$data['frag'] =  getnext($output,"Fragment thr");
        if($data['frag'] >= 65534)
          $data['frag'] = "off";
	$data['power'] =  getnext($output,"Power Management");
	return $data;
}
/*iwconfig output in an array with device names as keys*/
function get_wireless_status(){
	$iwconfig_output = trim(`iwconfig`);
	$device_status_strings = preg_split("/\n(\W+)?\n/", $iwconfig_output);
	$devices_data = false;
	
	foreach($device_status_strings as $device_status_string){
		sscanf($device_status_string,"%s ",$device);
		if (strstr($device_status_string, "no wireless extensions"))
			continue;
		// don't return wifi0 et al
		if (strstr($device, "wifi"))
			continue;
		// don't return hostap0 et al
		if (strstr($device, "hostap"))
			continue;
                // skip sit* (the V6-in-V4 tunnel)
		if (strstr($device, "sit"))
			continue;
		$devices_data[$device] = parse_iwconfig($device);
		$devices_data[$device]["name"] = $device;
	}
	return $devices_data;
}

/* device, inet addr, Bcast, mask,  in an associative array with device name as key*/
function get_ethernet_status(){
	global $ifconfig_bin;
	$ifconfig_cmd = $ifconfig_bin;
	$ifconfig_output = rtrim(`$ifconfig_cmd`, " \n");
	$device_status_strings = explode("\n\n", $ifconfig_output);
	
	foreach($device_status_strings as $device_status_string){
		sscanf($device_status_string,"%s ",$device);
		//skip loopback interface
		if ($device == "lo")
			continue;
		// Ignore wifi0 et al
		if (strstr($device,"wifi"))
			continue;
		// Ignore hostap0 et al
		if (strstr($device,"hostap"))
			continue;
                // skip sit* (the V6-in-V4 tunnel)
		if (strstr($device, "sit"))
			continue;
		// Ignore ppp*; pppd manages them
		if (strstr($device,"ppp"))
			continue;
		$device_status["Link encap"] = get_value($device_status_string,"Link encap:");
		$device_status["hwaddr"] = get_value($device_status_string,"HWaddr");
		$device_status["ipaddr"] = get_value($device_status_string,"inet addr:");
		$device_status["bcast"] = get_value($device_status_string,"Bcast:");
		$device_status["mask"] = get_value($device_status_string,"Mask:");
		$device_status["mtu"] = get_value($device_status_string,"MTU:");
		$device_status["Metric"] = get_value($device_status_string,"Metric:");
		$device_status["tx"] = get_value($device_status_string,"TX bytes:");
		$device_status["rx"] = get_value($device_status_string,"RX bytes:");
		$device_status["UP"] = get_flag($device_status_string,"UP");
		
		$device_status["gateway"] = get_gateway($device);
		$device_status["dns"] = get_static_dns();
		$device_statuses[$device] = $device_status;
	}
	return $device_statuses;
}

/*each element of routes is an associative array with key the attribute name*/
function get_routing_table(){
	global $route_bin;
	$route_cmd = $route_bin." -n";
	$route_output = rtrim(`$route_cmd`, " \n");
	$route_strings = explode("\n", $route_output);
	$index=0;
	foreach($route_strings as $route_string_index => $route_string){
		if($route_string_index == 0 || $route_string_index ==1)
			continue;
		list($routes[$index]["destination"],
			$routes[$index]["gateway"],
			$routes[$index]["netmask"],
			$routes[$index]["flags"],
			$routes[$index]["metric"],
			$routes[$index]["ref"],
			$routes[$index]["use"],
			$routes[$index]["iface"]) = sscanf($route_string, "%s %s %s %s %s %s %s %s");
		$index++;
	}
	return $routes;
}

/*returns false if device doesn't support scanning, empty array if there are no scan results
*/
function get_scan_results($device){
	global $iwlist_bin;

	$iwlist_output = rtrim(do_scan($device), " \n");

	if (!(stristr( $iwlist_output, "Operation not supported") === false))
		return false;
	//remove first line
	$iwlist_output = ltrim (substr($iwlist_output,strcspn($iwlist_output, "\n")));
	//shift the first empty element
	$cell_strings = array_slice(explode("Cell", $iwlist_output),1);
	
	$cell_infos = array();
	foreach( $cell_strings as $cell_index => $cell_string){
		$cell_info["address"] = get_value($cell_string,"Address:");
//		$cell_info["essid"] = get_value($cell_string,"ESSID:");
	        ereg('ESSID:"(.*)"',$cell_string,$regs);
	        $cell_info["essid"] = $regs[1];
		$cell_info["mode"] = get_value($cell_string,"Mode:");
		$cell_info["frequency"] = get_value($cell_string,"Frequency:");
		$cell_info["channel"] = freq2channel($cell_info["frequency"]);
		$cell_info["quality"] = get_value($cell_string,"Quality:");
		$cell_info["signal"] = get_value($cell_string,"Signal level:");
		$cell_info["noise"] = get_value($cell_string,"Noise level:");
		$cell_info["encryption"] = get_value($cell_string,"Encryption key:");
		if($cell_info["encryption"] == "on")
		  $cell_info["encryption"] = "<font color='red'>on</font>";
		$rates = get_all_values($cell_string,"Bit Rate:");
		$cell_info["rates"] = array_shift($rates);
		foreach ($rates as $rate)
			$cell_info["rates"] = $cell_info["rates"].", ".$rate;
		
		$extras = get_all_values($cell_string,"Extra:");
		$cell_info["extras"] = array_shift($extras);
		foreach ($extras as $extra)
			$cell_info["extras"] = $cell_info["extras"].", ".$extra;
		$cell_infos[$cell_index] = $cell_info;
	}
	return($cell_infos);
}

/*******************************
* get_channels($iface)
* Find all available channels
* for the given interface
*******************************/
function get_channels($iface){
  $reply = array();
  $whole_lines = explode("\n",`iwlist $iface freq`);
  foreach ($whole_lines as $whole_line) {
    if(strstr($whole_line," : ")) {
      array_push($reply,trim($whole_line));
    }
  }
  return $reply;
}

/*******************************
* get_channel($iface)
* What channel are we on?
*******************************/
function get_channel($iface){
  if(ereg("\(Channel ([[:digit:]]+)\)",`iwlist $iface freq`,$regs)) 
    $reply = $regs[1];
  else
    return false;
    
  if($reply < 10)
    $reply = "0" . $reply;

  return $reply;
}

/*******************************
* get_rates($iface)
* Find all available rates
* for the given interface
*******************************/
function get_rates($iface){
  $reply = array();
  $whole_lines = explode("\n",`iwlist $iface rate`);
  foreach ($whole_lines as $whole_line) {
//    if(ereg("Current Bit Rate:(.*)",$whole_line,$regs)) {
    if(strstr($whole_line,"Current Bit Rate"))
      break;
    if(preg_match("/\s+(.* Mb\/s)/",$whole_line,$regs))
      array_push($reply,$regs[1]);
  }
  return $reply;
}

/*******************************
* get_networks($iface)
* Do a quick network scan
*******************************/
function get_networks($iface){
  $reply = array();

  $whole_lines = explode("\n",do_scan($iface));

  foreach ($whole_lines as $whole_line) {
    if(!ereg("ESSID:\"(.*)\"",$whole_line,$regs))
      continue;
    $net = $regs[1];
    if(!$net)
      continue;
    if(!in_array($net,$reply))
      array_push($reply,$net);
  }

  return $reply;
}

/*******************************
* get_mode($iface)
* What STA mode is $iface in?
*******************************/
function get_mode($iface) {
  if(ereg("Mode:([[:alnum:]]+) ",`iwconfig $iface`,$regs))
    return $regs[1];
  else
    return false;
}

/*******************************
* do_scan($iface)
* Perform an 'iwlist scanning'
* on an interface.  Safely.
*******************************/
function do_scan($iface) {

  $mode = get_mode($iface);

  // Don't even try if we don't recognize the current mode.
  if(!ereg("Master|Managed|Ad-Hoc|Monitor|Repeater",$mode))
    return false;

  if($mode != "Managed")
    privcmd("iwconfig $iface mode managed",false);

  $reply = `iwlist $iface scanning`;

  if($mode != "Managed")
    privcmd("iwconfig $iface mode $mode",false);

  return $reply;
}


/************************************
* get_static_dns()
* Return the static DNS entry, if any
*************************************/
function get_static_dns() {
  $filename = '/etc/resolv/static';

  if(!is_readable($filename))
    return false;
    
  $fp = fopen($filename,"r");
  if(!$fp)
    return false;

  $file_contents=fread($fp,filesize($filename));
  fclose($fp);

  $whole_lines=explode("\n",$file_contents);
  foreach ($whole_lines as $whole_line) {
    if(ereg("nameserver (.*)",$whole_line,$regs)) {
      return $regs[1];
    }
  }
  return "";
}

/************************************
* set_static_dns()
* Set the static DNS entry
*************************************/
function set_static_dns($server) {
  $tmpfile = "/var/tmp/static";
  $filename = '/etc/resolv/static';

  if(!$server) 
    return false;
  
  $fp = fopen($tmpfile,"w");
  if(!$fp)
    return false;

  fwrite($fp,"nameserver $server\n");
  fclose($fp);
  
  privcmd("mv -f $tmpfile /ro/etc/resolv/static", true);
  privcmd("cp /ro/etc/resolv/static $filename", false);
  
  return true;
}

/************************************
* get_gateway($iface)
* Return default gateway for $iface
*************************************/
function get_gateway($iface) {
  $filename = '/etc/network/interfaces';
  $fp = fopen($filename,"r");
  
  if(!$fp)
    return false;

  $file_contents=fread($fp,filesize($filename));
  fclose($fp);

  $thisif = 0;
  $whole_lines=explode("\n",$file_contents);
  foreach ($whole_lines as $whole_line) {
    // Is this where we get off?
    if(ereg("^(auto|iface) $iface",$whole_line)) {
      $thisif = 1;
      continue;
    }

    // Is this where we get back on?
    if(ereg("^(auto|iface)",$whole_line)) {
      $thisif = 0;
      continue;
    }

    if($thisif && ereg("	gateway (.*)",$whole_line,$regs))
        return $regs[1];
  }

  return false;
}

/*********************************
* get_auto_rate($iface)
* Is this radio in auto rate mode?
*********************************/
function get_auto_rate($iface) {
  $filename = '/etc/network/interfaces';
  $fp = fopen($filename,"r");
  
  if(!$fp)
    return false;

  $file_contents=fread($fp,filesize($filename));
  fclose($fp);
  $whole_lines=explode("\n",$file_contents);

  foreach ($whole_lines as $whole_line) {
    // rate line for our if with optional rate and auto?  True.
    if(ereg("	pre-up.*$iface.*rate ?[[:digit:]]?\.?[[:digit:]]?[[:digit:]]?M? auto",$whole_line))
      return true;
    // rate line for our if with an explicit rate only?  False.
    if(ereg("	pre-up.*$iface.*rate.*M ",$whole_line))
      return false;
  }

  // No rate line at all?  True.
  return true;
}

/*********************************
* get_numeric_rate($iface)
* How fast should this radio go?
*********************************/
function get_numeric_rate($iface) {
  $filename = '/etc/network/interfaces';
  $fp = fopen($filename,"r");
  
  if(!$fp)
    return false;

  $file_contents=fread($fp,filesize($filename));
  fclose($fp);
  $whole_lines=explode("\n",$file_contents);

  foreach ($whole_lines as $whole_line) {
    if(ereg("	pre-up.*$iface.*rate ([[:digit:]]?\.?[[:digit:]]?[[:digit:]]?)M",$whole_line,$regs))
      return $regs[1] . " Mb/s";
  }

  return "best";
}

/************************************
* get_athctrl($iface)
* Return athctrl distance for $iface
*************************************/
function get_athctrl($iface) {
  $filename = '/etc/network/interfaces';
  $fp = fopen($filename,"r");
  
  if(!$fp)
    return false;

  $file_contents=fread($fp,filesize($filename));
  fclose($fp);
  $whole_lines=explode("\n",$file_contents);

  foreach ($whole_lines as $whole_line) {
    if(ereg("	pre-up athctrl -i $iface -d ([[:digit:]]+)",$whole_line,$regs))
      return $regs[1];
  }

  return false;
}

/************************************
* get_dyndns()
* Return ddnsu conf
*************************************/
function get_dyndns() {
  $filename = '/etc/ddnsu.conf';
  $fp = fopen($filename,"r");
  if(!$fp)
    return false;

  $file_contents=fread($fp,filesize($filename));
  fclose($fp);
  return $file_contents;
}

/************************************
* set_dyndns()
* Save ddnsu conf
*************************************/
function set_dyndns($conf) {
  $filename = '/var/tmp/ddnsu.conf';
  $fp = fopen($filename,"w");
  if(!$fp)
    return false;

  fwrite($fp,"$conf");
  fclose($fp);
  privcmd("mv -f $filename /etc/ddnsu.conf",true);
  return true;
}

/************************************
* update_dyndns()
* Run ddnsu once
*************************************/
function update_dyndns() {
  $filename = '/etc/ddnsu.conf';
  $fp = fopen($filename,"r");
  if(!$fp)
    return false;

  $options=fread($fp,filesize($filename));
  trim($options);

  bgprivcmd("/sbin/ddnsu $options");
  return true;
}

/***************************************
* update_inittab()
* Enable or disable an entry in inittab
****************************************/
function update_inittab($entry, $enabled) {
  $filename = '/etc/inittab';
  $tmpfile = '/var/tmp/inittab.tmp';
  
  $fp = fopen($filename,"r");
  if(!$fp)
    return false;

  $fw = fopen($tmpfile,"w");
  if(!$fw) {
    close($fp);
    return false;
  }
  
  while(!feof($fp)) {
    $line = fgets($fp);
    if(strstr($line,"$entry:")) {
      if($enabled) {
        // strip all comments
        $line = ereg_replace("#","",$line);
        fwrite($fw,"$line");
      }
      else {
        // just comment it out
        fwrite($fw,"#$line");
      }
    }
    else {
      fwrite($fw,$line);
    }
  }

  fclose($fp);
  fclose($fw);
  
  privcmd("mv -f $tmpfile $filename", true);
  privcmd("kill -HUP 1", false);
  return true;
}

/***************************************
* check_inittab()
* See if an entry is eabled in inittab
****************************************/
function check_inittab($entry) {
  $filename = '/etc/inittab';
  
  $fp = fopen($filename,"r");
  if(!$fp)
    return false;

  while(!feof($fp)) {
    $line = fgets($fp);
    if(strstr($line,"$entry:")) {
      if(substr($line,0,1) == "#") {
        return false;
      }
      else {
        return true;
      }
    }
  }
  // Never heard of it.
  echo "<h1>Couldn't find line for $entry</h1>";
  return false;
}

/***********************************************
* file_replace()
* Replace a string with another in a given file
* $search is a regular expression
************************************************/
function file_replace($filename,$search,$replace) {
  
  $fp = fopen($filename,"r");
  if(!$fp)
    return false;

  $tmpfile = "/var/tmp/filereplace.tmp";
  $fw = fopen($tmpfile,"w");
  if(!$fw)
    return false;
    
  while(!feof($fp)) {
    $line = ereg_replace($search,$replace,fgets($fp));
    fwrite($fw,$line);
  }
  
  fclose($fp);
  fclose($fw);  

  privcmd("mv -f $tmpfile $filename",true);
  
  return true;
}

/***************************************
* comment_file()
* (un)comment a line in a file
****************************************/
function comment_file($filename, $entry, $commented) {
  $tmpfile = '/var/tmp/commentfile.tmp';
  
  $fp = fopen($filename,"r");
  if(!$fp)
    return false;

  $fw = fopen($tmpfile,"w");
  if(!$fw) {
    close($fp);
    return false;
  }
  while(!feof($fp)) {
    $line = fgets($fp);
    if(ereg($entry,$line)) {
      // strip all leading comments
      $line = ereg_replace("^#+","",$line);
      $line = ltrim($line);
      if($commented) {
        // just comment it out
        fwrite($fw,"#$line");
      }
      else {
        fwrite($fw,"$line");
      }
    }
    else {
      fwrite($fw,$line);
    }
  }

  fclose($fp);
  fclose($fw);
  
  privcmd("mv -f $tmpfile $filename", true);
  return true;
}

/****************************************************
* get_olsr_interfaces()
* Returns array of interfaces defined in olsrd.conf
*****************************************************/
function get_olsr_interfaces(){
  $fp = fopen("/etc/olsrd.conf","r");
  if(!$fp)
    return false;
  
  while(!feof($fp)) {
    $line = trim(fgets($fp));
    if(strstr($line,"Interface ")) {
      $line = ereg_replace(".*Interface ","",$line);
      $line = ereg_replace('"','',$line);
      return explode(" ", $line);
    }
  }  
  return false;
}

/****************************************************
* get_olsr_plugin_status()
* See if this plugin is enabled in olsrd.conf
*****************************************************/
function get_olsr_plugin_status($plugin){
  $fp = fopen("/etc/olsrd.conf","r");
  if(!$fp)
    return false;
  
  while(!feof($fp)) {
    $line = fgets($fp);
    if(strstr($line,$plugin)) {
      fclose($fp);
      if(substr($line,0,1) == "#")
        return false;
      else 
        return true;
    }
  }
  fclose($fp);
  return false;
}

/****************************************************
* set_olsr_plugin_status()
* Enable / disable plugin in olsrd.conf
*****************************************************/
function set_olsr_plugin_status($plugin,$enabled){
  $filename = "/etc/olsrd.conf";
  $tmpfile = "/var/tmp/olsrd.conf.temp";
  
  if($enabled && get_olsr_plugin_status($plugin))
    return true;
  if(!$enabled && !get_olsr_plugin_status($plugin))
    return true;
    
  $fp = fopen($filename,"r");
  if(!$fp)
    return false;
  
  $fw = fopen($tmpfile,"w");
  if(!$fw)
    return false;
  
  while(!feof($fp)) {
    $line = fgets($fp);
    if(strstr($line,$plugin)) {
      if($enabled) {
        $line = ereg_replace("#","",$line);
        fwrite($fw,$line);
        while(!strstr($line,"}") && !feof($fp)) {
          $line = fgets($fp);
          $line = ereg_replace("#","",$line);
          fwrite($fw,$line);
        }
      }
      else {
        fwrite($fw,"#$line");
        while(!strstr($line,"}") && !feof($fp)) {
          $line = fgets($fp);
          fwrite($fw,"#$line");
        }
      }
    }
    else {
      fwrite($fw,$line);
    }
  }

  fclose($fp);
  privcmd("mv -f $tmpfile $filename",true);
  
  return true;
}

/****************************************************
* get_olsr_plugin_settings()
* Get settings for this plugin in olsrd.conf
*****************************************************/
function get_olsr_plugin_settings($plugin){
  $options = array();
  $opts = array();
  $fp = fopen("/etc/olsrd.conf","r");
  if(!$fp)
    return false;
  
  while(!feof($fp)) {
    $line = fgets($fp);
    if(strstr($line,$plugin)) {
      while(!strstr($line,"}") && !feof($fp)) {
        $line = ereg_replace("#","",fgets($fp));
        if(strstr($line,"PlParam")) {
          $line = ereg_replace("PlParam","",$line);
          $line = trim($line);
          $opts = explode(" ",$line,2);
          $name = trim($opts[0]);
          $setting = trim($opts[1]);
          $options[ereg_replace('"','',$name)] = ereg_replace('"','',$setting);
        }
      }
      return $options;
    }
  }
  fclose($fp);
  return false;
}

/****************************************************
* set_olsr_plugin_settings()
* Set a setting for this plugin in olsrd.conf
*****************************************************/
function set_olsr_plugin_settings($plugin, $name, $setting) {
  $options = array();
  $opts = array();
  $filename = "/etc/olsrd.conf";
  $tmpfile = "/var/tmp/olsrd.conf.tmp";
  
  $fp = fopen($filename,"r");
  if(!$fp)
    return false;
  
  $fw = fopen($tmpfile,"w");
  if(!$fw)
    return false;
  
  while(!feof($fp)) {
    $line = fgets($fp);
    if(strstr($line,$plugin)) {
      fwrite($fw,$line);
      while(!strstr($line,"}") && !feof($fp)) {
        $line = fgets($fp);
        if(strstr($line,$name)) {
          $newline = "  PlParam \"$name\" \"$setting\"\n";
          fwrite($fw,$newline);
        }
        else {
          fwrite($fw,$line);
        }
      }
    }
    else {
      fwrite($fw,$line);
    }
  }
  fclose($fp);
  fclose($fw);
  
  privcmd("mv -f $tmpfile $filename", true);
  return true;
}


/* ***********************************
*get_wifidog_auth_servers()
* Return wifidog auth server config if any
**************************************/
function get_wifidog_auth_servers(){
	 $filename='/etc/wifidog.conf';
	 $comment='#';
	 if(!is_readable($filename)) return false;
	 $fp = fopen($filename,"r");
	 if(!$fp)
    	return false;
     $inside_authserver="";
     //$auth_server_params="";
     $i=0;
     $list='';
     while (!feof($fp)){
     	
     	$line = trim(fgets($fp));
     	if (strstr($line, "{") && ereg("^AuthServer", $line)) {
     		$inside_authserver=true;
     		
     	}
     	elseif ($inside_authserver == true && !ereg("}", $line)) {
     		
     		
            $arr=array();
     		$arr = explode(' ', $line);
     		$auth_server_params[$arr[0]]= $arr[1];
     		
     	}
     	else {
     		$inside_authserver=false;	
     		
     	}
     
     }
 
     return $auth_server_params;
  
     
}
        



/************************************
* get_wifidog_ints()
* Return wifidog interface configuration if any
*************************************/
function get_wifidog_ints() {
   $wifidog_ext_int="";
   $wifidog_gw_int="";
   $wifidog_ints=array();  
	
  $filename = '/etc/wifidog.conf';

  if(!is_readable($filename))
    return false;
    
  $fp = fopen($filename,"r");
  if(!$fp)
    return false;

  $file_contents=fread($fp,filesize($filename));
  fclose($fp);

  $whole_lines=explode("\n",$file_contents);
  foreach ($whole_lines as $whole_line) {
  	if(ereg("^ExternalInterface (.*)",$whole_line,$regs)) {
  		$wifidog_ext_int=$regs[1];
    }
  }
  foreach ($whole_lines as $whole_line) {
  	if(ereg("^GatewayInterface (.*)",$whole_line,$regs)) {
  		$wifidog_gw_int=$regs[1];
    }
  }
  $wifidog_ints[0]=$wifidog_ext_int;
  $wifidog_ints[1]=$wifidog_gw_int;
  
  return $wifidog_ints;
}

	
	//Note that this dumps an array out in a form suitable to be
	//used with eval() to recreate the array!
	
	function arrayDump(&$array, $col=0){
		if (is_array($array)){
			echo "\n";
			$i = 0;
			$count = count($array);
			while($i < $count && list($key, $val) = each($array)){
				echo $key, ' ==> ';
				arrayDump($val, $col+1);
				$i++;
				if ($i < $count){
					echo ',';
				}
				echo "\n";
			}
			echo "\n";
		}
		else{
			echo $array;
		}
	}
	
	for ($i = 0; $i < 5; $i++){
		for ($j = $i; $j < 4; $j++){
			for ($k = $j; $k < 3; $k++){
				$data[$i][$j][$k] = $i * $j + $k;
			}
		}
	}
	
	




function explodeAssoc($glue,$str)
{
   $arr=explode($glue,$str);

   $size=count($arr);

   for ($i=0; $i < $size/2; $i++)
       $out[$arr[$i]]=$arr[$i+($size/2)];

   return($out);
};

?>
