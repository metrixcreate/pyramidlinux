<?php

/*
 * Interface related functions
 */

/**************************************
* strip_comments()
* Helper to remove # embedded comments
**************************************/
function strip_comments($thing) {
  if(ereg("(.*)#.*",$thing,$regs)) {
    $thing = str_replace("#","",$regs[1]);
  }
  return trim($thing);
}
 
/**************************************
* get_disabled()
* Is the given interface disabled
* in /etc/network/interfaces?
**************************************/
function get_disabled($iface) {
  $filename = '/etc/network/interfaces';
  $fp = fopen($filename,"r");
  $file_contents=fread($fp,filesize($filename));
  fclose($fp);
  $whole_lines=explode("\n",$file_contents);
  
  foreach ($whole_lines as $whole_line){
    $whole_line = trim($whole_line);
    if (strstr($whole_line,"auto $iface")) {
      if($whole_line == "auto $iface") {
        return false;
      }
      else {
        return true;
      }
    }
  }
}

/**************************************
* set_disabled()
* Write an auto line for an interface
* to /etc/network/interfaces
**************************************/
function set_disabled($iface,$disabled) {
  $filename = "/etc/network/interfaces";
  $tmpfile = "/var/tmp/ifaces";
  
  $fp = fopen($filename,"r");
  $file_contents=fread($fp,filesize($filename));
  fclose($fp);
  $whole_lines=explode("\n",$file_contents);

  $fp = fopen($tmpfile,"w");
  foreach ($whole_lines as $whole_line) {
    if($whole_line == "")
      continue;
      
    if(ereg("^.?auto",$whole_line)) {
      if(strstr($whole_line, "auto $iface")) {
        if($disabled)
          fwrite($fp,"\n#auto $iface\n");
        else
        fwrite($fp,"\nauto $iface\n");
      }
      else {
        fwrite($fp,"\n$whole_line\n");
      }
    }
    else {
      fwrite($fp,"$whole_line\n");
    }
  }
  fclose($fp);

  if($disabled) {
    privcmd("ifconfig $iface down",false);
  }

  privcmd("cp $tmpfile $filename",true);
  `rm -f $tmpfile`;

  if(!$disabled) {
    privcmd("ifup $iface", false);
    //echo "<pre>".`ifconfig $iface`."</pre>";
  }
  
  return true;
}

/**************************************
* get_dhcp()
* Is the given interface using dhcp
* in /etc/network/interfaces?
**************************************/
function get_dhcp($iface) {
  $filename = '/etc/network/interfaces';
  $fp = fopen($filename,"r");
  $file_contents=fread($fp,filesize($filename));
  fclose($fp);
  $whole_lines=explode("\n",$file_contents);

  foreach ($whole_lines as $whole_line){
    $whole_line = trim($whole_line);
    if (strstr($whole_line,"iface $iface")) {
      if($whole_line == "iface $iface inet dhcp") {
        return true;
      }
      else {
        return false;
      }
    }
  }
}

/**************************************
* get_nat()
* Are we using masquerading on this 
* interface?
**************************************/
function get_nat($iface) {
  $filename = '/etc/default/nat';
  $fp = fopen($filename,"r");
  $file_contents=fread($fp,filesize($filename));
  fclose($fp);
  
  if(strstr($file_contents,$iface))
    return true;
  else
    return false;
}

/**************************************
* set_nat()
* Use masquerading
**************************************/
function set_nat($iface,$enabled) {
  $filename = '/etc/default/nat';
  $tmpfile = '/var/tmp/nat-defaults';
  
  $fp = fopen($filename,"r");
  $file_contents=fread($fp,filesize($filename));
  fclose($fp);
  
  $fp = fopen($tmpfile,"w");
  if(!$fp)	
    return false;
    
  $whole_lines=explode("\n",$file_contents);
  foreach ($whole_lines as $whole_line) {
    if(ereg("^INTERFACES=\"(.*)\"",$whole_line,$regs)) {
      $ifaces = explode(" ",$regs[1]);
    }
  }
  
  fwrite($fp,'INTERFACES="');
  if($enabled)
    $ifaceline = "$iface ";
  else
    $ifaceline = "";
    
  foreach($ifaces as $curiface) {
    if($curiface != $iface) {
      $ifaceline .= "$curiface ";
    }
  }
  $ifaceline = trim($ifaceline);
  fwrite($fp,"$ifaceline\"\n");
  fclose($fp);
  
  privcmd("/etc/init.d/nat stop",false);
  privcmd("mv -f $tmpfile $filename",true);
  privcmd("/etc/init.d/nat start",false);
  
  return true;
}


/**************************************
* get_dhcpd_status()
* Are we serving dhcp leases on this 
* interface, or using dhcrelay?
**************************************/
function get_dhcpd_status($iface) {
  if(get_dnsmasq_dhcpd($iface)) {
    return "enabled";
  }
  elseif(get_dhcrelay($iface)) {
    return "dhcrelay";
  }
  else {
    return "disabled";
  }
}

/**************************************
* set_dhcpd_status()
* Serve dhcp leases on this interface,
* or not, or use dhcrelay
**************************************/
function set_dhcpd_status($iface,$status,$dhcserver) {
  $filename = "/etc/dnsmasq.conf";
  $tmpfile = "/var/tmp/dhcrelay.tmp";
  $cmd = "rm -f /etc/default/dhcrelay.$iface";
  
  if($status == "enabled") {
    comment_file($filename,"no-dhcp-interface=$iface",true);
    privcmd($cmd,true);
  }
  elseif($status == "disabled") {
    comment_file($filename,"no-dhcp-interface=$iface",false);
    echo "<h1>";
    echo privcmd($cmd,true);
    echo "</h1>";
  }
  elseif($status == "dhcrelay") {
    comment_file($filename,"no-dhcp-interface=$iface",false);

    $fw = fopen($tmpfile,"w");
    if(!$fw)	
      return false;
    fwrite($fw,"INTERFACE=\"$iface\"\n");
    fwrite($fw,"SERVER=\"$dhcserver\"\n");
    fclose($fw);

    privcmd("mv -f $tmpfile /etc/default/dhcrelay.$iface",true);
  }
  else {
    return false;
  }

  privcmd("/etc/init.d/dhcrelay.sh restart", false);
  privcmd("killall dnsmasq", false);	# Restarts in init

  return true;
}

/**************************************
* get_dhcrelay()
* Are we using dhcrelay on this
* interface?
**************************************/
function get_dhcrelay($iface) {
  if(is_readable("/etc/default/dhcrelay.$iface"))
    return true;
  else
    return false;
}

/**************************************
* get_dhcrelay_server()
* Which dhcrelay is defined? 
**************************************/
function get_dhcrelay_server($iface) {
  if(!get_dhcrelay($iface)) 
    return "";
    
  $filename = "/etc/default/dhcrelay.$iface";
  $fp = fopen($filename,"r");

  while(!feof($fp)) {
    $line = trim(fgets($fp));
    if(ereg("^SERVER=\"(.*)\"",$line,$regs)) {
      return $regs[1];
    }
  }
  fclose($fp);
  return "";
}

/**************************************
* get_dnsmasq_dhcpd()
* Are we serving dhcp leases on this 
* interface?
**************************************/
function get_dnsmasq_dhcpd($iface) {
  $filename = '/etc/dnsmasq.conf';
  $fp = fopen($filename,"r");

  while(!feof($fp)) {
    $line = trim(fgets($fp));
    if($line == "no-dhcp-interface=$iface") {
      return false;
    }
    elseif($line == "#no-dhcp-interface=$iface") {
      return true;
    }
  }

  return true;
}

/**************************************
* get_dhcpd()
* *** DEPRECATED *** 
* Use get_dhcpd_status() instead
**************************************/
function get_dhcpd($iface) {
  $filename = '/etc/default/dhcp';
  $fp = fopen($filename,"r");
  $file_contents=fread($fp,filesize($filename));
  fclose($fp);
  
  if(strstr($file_contents,$iface))
    return true;
  else
    return false;
}

/**************************************
* set_dhcpd()
* Serve dhcp leases on this interface
**************************************/
function set_dhcpd($iface,$enabled) {
  $filename = '/etc/default/dhcp';
  $tmpfile = '/var/tmp/dhcp-defaults';
  
  $fp = fopen($filename,"r");
  $file_contents=fread($fp,filesize($filename));
  fclose($fp);
  
  $fp = fopen($tmpfile,"w");
  if(!$fp)	
    return false;
    
  $whole_lines=explode("\n",$file_contents);
  foreach ($whole_lines as $whole_line) {
    if(ereg("^INTERFACES=\"(.*)\"",$whole_line,$regs)) {
      $ifaces = explode(" ",$regs[1]);
    }
  }
  
  fwrite($fp,'INTERFACES="');
  if($enabled)
    $ifaceline = "$iface ";
  else
    $ifaceline = "";
    
  foreach($ifaces as $curiface) {
    if($curiface != $iface) {
      $ifaceline .= "$curiface ";
    }
  }
  $ifaceline = trim($ifaceline);
  fwrite($fp,"$ifaceline\"\n");
  fclose($fp);
  
  privcmd("/etc/init.d/udhcpd stop",false);
  privcmd("mv -f $tmpfile $filename",true);
  privcmd("/etc/init.d/udhcpd start",false);
  
  return true;
}

/**************************************
* get_dnsmasq_dhcpdconf()
* What are the current dnsmasq settings
* for DHCP for this interface?
**************************************/
function get_dnsmasq_dhcpdconf($iface) {
  $filename = "/etc/dnsmasq.conf";

  $reply['dhtime']="";
  $reply['dhstart']="";
  $reply['dhend']="";
  $reply['dhsubnet']="";
  $reply['dhgw']=true;

  if (!file_exists ($filename)) {
    return $reply;
  }
  
  $fp = fopen($filename,"r");
  while(!feof($fp)) {
    $line = trim(fgets($fp));

    if(ereg("^#",$line))
      continue;
    
    if(ereg("dhcp-range=(.*),(.*),(.*),(.*)	# $iface",$line,$regs)) {
      $reply['dhstart']=$regs[1];
      $reply['dhend']=$regs[2];
      $reply['dhsubnet']=$regs[3];
      $reply['dhtime']=$regs[4];
    }
    elseif($line == "dhcp-option=3") {
      $reply['dhgw']=false;
    }
  }
  
  return $reply;
}

/**************************************
* set_dnsmasq_dhcpdconf()
* Use new settings for dhcp in 
* /etc/dnsmasq.conf
**************************************/
function set_dnsmasq_dhcpdconf($iface,$config) {
  $filename = "/etc/dnsmasq.conf";

  if(!$config['dhtime'])
    $config['dhtime']="2h";

  $newline = "dhcp-range=" 
           . $config['dhstart'] . "," 
           . $config['dhend'] . "," 
           . $config['dhsubnet'] . "," 
           . $config['dhtime'] . "	# $iface\n";

  file_replace("/etc/dnsmasq.conf","dhcp-range.*# $iface\n", $newline);

  if($config['dhgw'] === "enabled") {
    file_replace("/etc/dnsmasq.conf","dhcp-option=3.*", "#dhcp-option=3\n");
  }
  else {
    file_replace("/etc/dnsmasq.conf","#+dhcp-option=3.*", "dhcp-option=3\n");
  }
  
  privcmd("killall dnsmasq",false);
  return true;
}

/**************************************
* set_dnsmasq_dhcpd()
* Enable, disable, or use dhcrelay
* on a given interface.
**************************************/
function set_dnsmasq_dhcpd($iface,$config) {
  $filename = "/etc/dnsmasq.conf";

  if(!$config['dhtime'])
    $config['dhtime']="2h";

  $newline = "dhcp-range=" 
           . $config['dhstart'] . "," 
           . $config['dhend'] . "," 
           . $config['dhsubnet'] . "," 
           . $config['dhtime'] . "	# $iface\n";

  file_replace("/etc/dnsmasq.conf","dhcp-range.*# $iface\n", $newline);
  privcmd("killall dnsmasq",false);
  return true;
}

/**************************************
* get_dhcpdconf()
* *** DEPRECATED ***
* Use get_dnsmasq_dhcpdconf() instead
**************************************/
function get_dhcpdconf($iface) {
  $filename = "/etc/udhcpd/udhcpd.$iface.conf";
  if (!file_exists ($filename)) {
    $reply['dhtime']="";
    $reply['dhstart']="";
    $reply['dhend']="";
    $reply['dhgateway']="";
    $reply['dhdomain']="";
    $reply['dhsubnet']="";
    $reply['dhdns']="";
    return $reply;
  }
  
  $fp = fopen($filename,"r");
  $file_contents=fread($fp,filesize($filename));
  fclose($fp);
  $whole_lines=explode("\n",$file_contents);

  $reply['dhdns']="";
  foreach ($whole_lines as $whole_line) {
    if(ereg("^#",$whole_line))
      continue;
    
    if (ereg("^start[[:space:]]+([[:digit:]]+\.[[:digit:]]+\.[[:digit:]]+\.[[:digit:]]+)",$whole_line,$regs)) {
      $reply['dhstart']=strip_comments($regs[1]);
    }
    elseif (ereg("^end[[:space:]]+([[:digit:]]+\.[[:digit:]]+\.[[:digit:]]+\.[[:digit:]]+)",$whole_line,$regs)) {
      $reply['dhend']=strip_comments($regs[1]);
    }
    elseif(ereg("^opt(ion)?[[:space:]]+lease[[:space:]]+([[:digit:]]+)",$whole_line,$regs)) {
      $reply['dhtime']=strip_comments($regs[2]);
    }
    elseif(ereg("^opt(ion)?[[:space:]]+router[[:space:]]+([[:digit:]]+\.[[:digit:]]+\.[[:digit:]]+\.[[:digit:]]+)",$whole_line,$regs)) {
      $reply['dhgateway']=strip_comments($regs[2]);
    }
    elseif(ereg("^opt(ion)?[[:space:]]+domain[[:space:]]+(.*)",$whole_line,$regs)) {
      $reply['dhdomain']=strip_comments($regs[2]);
    }
    elseif(ereg("^opt(ion)?[[:space:]]+subnet[[:space:]]+(.*)",$whole_line,$regs)) {
      $reply['dhsubnet']=strip_comments($regs[2]);
    }
    elseif(ereg("^opt(ion)?[[:space:]]+dns[[:space:]]+(.*)",$whole_line,$regs)) {
      $reply['dhdns'].=strip_comments($regs[2])." ";
    }
  }
  $reply['dhdns'] = str_replace(" ","\n",trim($reply['dhdns']));
  
  return $reply;
}

/**************************************
* set_dhcpdconf()
* *** DEPRECATED ***
* Use set_dyndns_dhcpdconf instead.
**************************************/
function set_dhcpdconf($iface,$config) {
  $filename = "/etc/udhcpd/udhcpd.$iface.conf";
  $tmpfile = "/var/tmp/udhcpd.$iface.conf";

  if(!($config['dhstart'] && $config['dhend']))
    return false;
  
  $fp = fopen($tmpfile,"w");
  if(!$fp) 
    return false;
  
  fwrite($fp,"### WiFiAdmin configuration begins ###\n");
  fwrite($fp,"start		".$config['dhstart']."\n");
  fwrite($fp,"end		".$config['dhend']."\n");

  if($config['dhtime'])
    fwrite($fp,"opt lease	".$config['dhtime']."\n");
  if($config['dhgateway'])
    fwrite($fp,"opt router	".$config['dhgateway']."\n");
  if($config['dhdomain'])
    fwrite($fp,"opt domain	".$config['dhdomain']."\n");
  if($config['dhsubnet'])
    fwrite($fp,"opt subnet	".$config['dhsubnet']."\n");
  if($config['dhdns']) {
    $dnsline = str_replace("\n"," ",$config['dhdns']);
    fwrite($fp,"opt dns		".$dnsline."\n");
  }

  fwrite($fp,"interface       ".$iface."\n");
  fwrite($fp,"auto_time       0\n");
  fwrite($fp,"pidfile         /var/run/udhcpd.".$iface.".pid\n");
  fwrite($fp,"### WiFiAdmin configuration ends ###\n");

  // Preserve all other settings, if any
  if(file_exists($filename)) {
    $rp = fopen($filename,"r");
    $file_contents=fread($rp,filesize($filename));
    fclose($rp);
    $whole_lines=explode("\n",$file_contents);

    $skipping = 0;
    foreach ($whole_lines as $whole_line) {
      if($whole_line == "### WiFiAdmin configuration begins ###") {
        $skipping = 1;
        continue;
      }
      if($whole_line == "### WiFiAdmin configuration ends ###") {
        $skipping = 0;
        continue;
      }
      if($skipping)
        continue;

      // reprint any line we don't manage...
      if(!ereg("^(pidfile|interface|auto_time|start|end|opt(ion)?[[:space:]]+(dns|domain|router|lease|subnet))",$whole_line))
        fwrite($fp,"$whole_line\n");
      // ...and comment out the ones we do.
      else
        fwrite($fp,"#$whole_line\n");
    }
  }
  fclose($fp);  

  privcmd("mv -f $tmpfile $filename", true);
  privcmd("/etc/init.d/udhcpd restart", false);
  
  return true;
}


/**************************************
* get_portforwards()
* Return all defined port forwarding
**************************************/
function get_portforwards() {
  $pf = array();
  $protos = array();
  $dports = array();
  $tips = array();
  $tports = array();
  
  $filename = '/etc/default/portforwarding';
  $fp = fopen($filename,"r");
  $file_contents=fread($fp,filesize($filename));
  fclose($fp);
  $whole_lines=explode("\n",$file_contents);
  
  foreach ($whole_lines as $whole_line) {
    $whole_line = trim($whole_line);
    if($whole_line == "") {	# skip blank lines
      continue;
    }

    if (strstr($whole_line,"PROTO")) {
      $x = substr($whole_line,7);
      $x = trim($x," )");
      $protos = explode(" ",$x);
    }
    elseif (strstr($whole_line,"DPORT")) {
      $x = substr($whole_line,7);
      $x = trim($x," )");
      $dports = explode(" ",$x);
    }
    elseif (strstr($whole_line,"TIP")) {
      $x = substr($whole_line,5);
      $x = trim($x," )");
      $tips = explode(" ",$x);
    }
    elseif (strstr($whole_line,"TPORT")) {
      $x = substr($whole_line,7);
      $x = trim($x," )");
      $tports = explode(" ",$x);
    }
  }

  for ($i = 0; $i < sizeof($protos); $i++) {
    if(!$protos[$i]) {
      return;
    }
    array_push($pf,$protos[$i],$dports[$i],$tips[$i],$tports[$i]);
  }
  
  return $pf;
}

/**************************************
* delete_portforward()
* Delete a port forwarding entry
**************************************/
function delete_portforward($index) {
  $protos = array();
  $dports = array();
  $tips = array();
  $tports = array();
  
  $filename = '/etc/default/portforwarding';
  $fp = fopen($filename,"r");
  $file_contents=fread($fp,filesize($filename));
  fclose($fp);
  $whole_lines=explode("\n",$file_contents);
  
  foreach ($whole_lines as $whole_line) {
    $whole_line = trim($whole_line);
    if($whole_line == "") {	# skip blank lines
      continue;
    }

    if (strstr($whole_line,"PROTO")) {
      $x = substr($whole_line,7);
      $x = trim($x," )");
      $protos = explode(" ",$x);
    }
    elseif (strstr($whole_line,"DPORT")) {
      $x = substr($whole_line,7);
      $x = trim($x," )");
      $dports = explode(" ",$x);
    }
    elseif (strstr($whole_line,"TIP")) {
      $x = substr($whole_line,5);
      $x = trim($x," )");
      $tips = explode(" ",$x);
    }
    elseif (strstr($whole_line,"TPORT")) {
      $x = substr($whole_line,7);
      $x = trim($x," )");
      $tports = explode(" ",$x);
    }
  }

  unset($protos[$index]);
  unset($dports[$index]);
  unset($tips[$index]);
  unset($tports[$index]);
  
  $tmpfile = "/var/tmp/portforwarding";
  $fp = fopen($tmpfile,"w");
  fwrite($fp,"PROTO=( ");
  foreach($protos as $proto) {
    fwrite($fp,"$proto ");
  }
  fwrite($fp,")\n");
  fwrite($fp,"DPORT=( ");
  foreach($dports as $dport) {
    fwrite($fp,"$dport ");
  }
  fwrite($fp,")\n");
  fwrite($fp,"TIP=( ");
  foreach($tips as $tip) {
    fwrite($fp,"$tip ");
  }
  fwrite($fp,")\n");
  fwrite($fp,"TPORT=( ");
  foreach($tports as $tport) {
    fwrite($fp,"$tport ");
  }
  fwrite($fp,")\n");
  fclose($fp);

  privcmd("mv -f $tmpfile /etc/default/",true);
  return;
}

/**************************************
* add_portforward()
* Add a port forwarding entry
**************************************/
function add_portforward($proto,$dport,$tip,$tport) {
  $protos = array();
  $dports = array();
  $tips = array();
  $tports = array();
  
  $filename = '/etc/default/portforwarding';
  $fp = fopen($filename,"r");
  $file_contents=fread($fp,filesize($filename));
  fclose($fp);
  $whole_lines=explode("\n",$file_contents);
  
  foreach ($whole_lines as $whole_line) {
    $whole_line = trim($whole_line);
    if($whole_line == "") {	# skip blank lines
      continue;
    }

    if (strstr($whole_line,"PROTO")) {
      $x = substr($whole_line,7);
      $x = trim($x," )");
      $protos = explode(" ",$x);
    }
    elseif (strstr($whole_line,"DPORT")) {
      $x = substr($whole_line,7);
      $x = trim($x," )");
      $dports = explode(" ",$x);
    }
    elseif (strstr($whole_line,"TIP")) {
      $x = substr($whole_line,5);
      $x = trim($x," )");
      $tips = explode(" ",$x);
    }
    elseif (strstr($whole_line,"TPORT")) {
      $x = substr($whole_line,7);
      $x = trim($x," )");
      $tports = explode(" ",$x);
    }
  }

  $tmpfile = "/var/tmp/portforwarding";
  $fp = fopen($tmpfile,"w");
  fwrite($fp,"PROTO=( ");
  foreach($protos as $p) {
    fwrite($fp,"$p ");
  }
  fwrite($fp,"$proto )\n");
  fwrite($fp,"DPORT=( ");
  foreach($dports as $p) {
    fwrite($fp,"$p ");
  }
  fwrite($fp,"$dport )\n");
  fwrite($fp,"TIP=( ");
  foreach($tips as $p) {
    fwrite($fp,"$p ");
  }
  fwrite($fp,"$tip )\n");
  fwrite($fp,"TPORT=( ");
  foreach($tports as $p) {
    fwrite($fp,"$p ");
  }
  fwrite($fp,"$tport )\n");
  fclose($fp);
  
  privcmd("mv -f $tmpfile /etc/default/",true);
  return;
}

/**************************************
* set_iface()
* Configure a given interface
* in /etc/network/interfaces
**************************************/
function set_iface($iface,$ipaddr,$mask,$bcast,$gateway) {
  $filename = '/etc/network/interfaces';
  $tmpfile = '/var/tmp/ifaces';
  
  if(!$ipaddr)
    return false;
    
  $fp = fopen($filename,"r");
  $file_contents=fread($fp,filesize($filename));
  fclose($fp);
  $whole_lines=explode("\n",$file_contents);

  $fp = fopen($tmpfile,"w");
  $nextif = 0;
  foreach ($whole_lines as $whole_line) {
    if($whole_line == "")
      continue;
      
    if (strstr($whole_line,"iface $iface")) {
      if($ipaddr == "dhcp") {
        fwrite($fp, "iface $iface inet dhcp\n");
      }
      else {
        fwrite($fp, "iface $iface inet static\n	address $ipaddr\n");
        if($mask)
          fwrite($fp,"	netmask $mask\n");
	if($bcast)
	  fwrite($fp,"	broadcast $bcast\n");
        if($gateway)
          fwrite($fp,"	gateway $gateway\n");
      }
      $nextif = 1;
    }
    elseif($nextif) {
      if(ereg("up|down|pre-up|post-down",$whole_line)) {
        fwrite($fp, "$whole_line\n");
      }
      elseif(ereg("^#?(iface|auto)",$whole_line)) {
        fwrite($fp,"\n$whole_line\n");
        $nextif = 0;
      }
    }
    elseif (ereg("^.?auto",$whole_line)) {
      fwrite($fp, "\n$whole_line\n");
    }
    else {
      fwrite($fp,"$whole_line\n");
    }
  }
  fclose($fp);
  
  privcmd("ifdown --force $iface", false);

  privcmd("mv -f $tmpfile $filename", true);
  `rm -f $tmpfile`;

  if(!get_disabled($iface)) {
    privcmd("ifup $iface", false);
    //echo "<pre>".`ifconfig $iface`."</pre>";
  }
  
  return true;
}

/**************************************
* set_iface_extras()
* Configure extra pre-up lines for a given
* interface in /etc/network/interfaces
**************************************/
function set_iface_extras($iface,$newlines) {
  $filename = '/etc/network/interfaces';
  $tmpfile = '/var/tmp/ifaces';
  $tagline = '# Set by WiFiAdmin';
  
  if(!$iface)
    return false;
  $fp = fopen($filename,"r");
  $file_contents=fread($fp,filesize($filename));
  fclose($fp);
  $whole_lines=explode("\n",$file_contents);

  $fp = fopen($tmpfile,"w");
  $thisif = 0;
  $dumped = 0;
  foreach ($whole_lines as $whole_line) {
    if($whole_line == "")
      continue;

   if(ereg("^#?auto",$whole_line) && (!$thisif))
      fwrite($fp,"\n");

    // Is this where we get off?
    if(strstr($whole_line,"auto $iface")) {
      fwrite($fp,"$whole_line\n");
      $thisif = 1;
      continue;
    }

    if($thisif) {
      // Skip any old lines
      if(strstr($whole_line,$tagline))
        continue;

      // Put our lines at the end
      if(ereg("^#?(iface|auto)",$whole_line) && (!strstr($whole_line,$iface)) ) {
        foreach($newlines as $nl) 
          fwrite($fp,"	pre-up $nl $tagline\n");
          
        fwrite($fp,"\n$whole_line\n");
        $thisif = 0;
        $dumped = 1;
        continue;
      }
      else {
        fwrite($fp,"$whole_line\n");
      }
    }
    else {
      fwrite($fp,"$whole_line\n");
    }
  }

  if(!$dumped) {
    foreach($newlines as $nl)
      fwrite($fp,"	pre-up $nl $tagline\n");
  }

  fclose($fp);

  privcmd("mv -f $tmpfile $filename", true);
  `rm -f $tmpfile`;

  return true;
}

?>
