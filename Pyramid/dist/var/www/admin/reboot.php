<?php

include_once("./include/functions.php");		//These are NOT wrong includes. Including here AND later with header.php does matter!




include("./include/header.php");	//echo the default Header entries

//check privileges
if ($_SESSION["access_ifs"]!="true"){
        echo "<p class = \"error\">You have no permission to access this section of WiFiAdmin</p>";
        exit();
}

//check for reboot
if (isset($_GET["reboot"])) {
                  echo "<li><b>Rebooting system, please wait a few minutes and refresh your browser.</b></li>";
		  echo " <script type=\"text/javascript\">
			<!--
			window.location = \"/admin/\" 
			//-->
			</script>";

                  rebootsys();
                  exit;
                }






echo "

<form name=\"reboot\" action=\"/admin/reboot.php?reboot=1\" method=\"POST\">

<p class=\"index\"><img src=\"/images/pyramid.png\" width=\"100\"><br /><br /></br><input type=\"submit\" value=\"Reboot\">   </br><font color='#900040'>".chostname()."</font>"; 

include("./include/footer.php");

?>
