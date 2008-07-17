<?
/**********************************************
* usbfs.php
* dump usb fs info 
***********************************************/

include("./include/header.php");
?>
<script src="include/submitonce.js"></script>
<h2>USB FS</h2>
<p>
<?
//check privileges
if ($_SESSION["access_ifs"]!="true"){
        echo "<p class = \"error\">You have no permission to access this section of WiFiAdmin</p>";
        exit();
}


$devices = implode("<p>", file("/proc/bus/usb/devices"));

echo $devices;


?>
