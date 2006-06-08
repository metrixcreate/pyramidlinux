<?php

include_once("./include/functions.php");		//These are NOT wrong includes. Including here AND later with header.php does matter!

include("./include/header.php");	//echo the default Header entries

echo "<p class=\"index\"><img src=\"/images/pyramid.png\" width=\"100\"><br /><br /><font color='#900040'>".chostname()."</font> is rebooting, please wait...</p><br><br>";

include("./include/footer.php");
?>
