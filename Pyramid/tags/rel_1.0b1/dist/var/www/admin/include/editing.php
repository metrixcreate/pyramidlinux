<?
/************************************************
* File editing functions
************************************************/

include_once("config.php");

/************************************************
* get_file()
* Display contents of a given file.  
*************************************************/
function get_file($filename) {
  if(! file_exists($filename)) {
    return "$filename does not exist!";
  }
  
  $fp = fopen($filename,"r");
  if(!$fp)
    return false;

  $text=fread($fp,filesize($filename));
  fclose($fp);
  return $text;
}


/************************************************
* save_file()
* Save a given file to disk.  Yep, bad idea.
*************************************************/
function save_file($filename, $text) {
  
  $fp = fopen("/var/tmp/wifiadmin.tmp","w");
  if(!$fp)
    return false;

  // Strip CRs before writing.
  $text = ereg_replace("\r","",$text);
  fwrite($fp,"$text");
  fclose($fp);
  privcmd("cp /var/tmp/wifiadmin.tmp $filename",true);
  `rm -f /var/tmp/wifiadmin.tmp`;

  return;
}
?>
