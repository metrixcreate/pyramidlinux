<?
/**********************************************
* edit.php
* Edit a file.  Yes, this is a bad idea.
***********************************************/

if(isset($_POST['cancel'])) {
  echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $_POST["from"] . "'>";
  exit;
}

include("./include/editing.php");
include("./include/header.php");
?>
<h2>Edit</h2>
<p>
<? 
//check privileges
if ($_SESSION["access_ifs"]!="true"){
	echo "<p class = \"error\">You have no permission to access this section of WiFiAdmin</p>";
	exit();
}


if(isset($_POST['file'])) {
  $file = $_POST['file'];
}
elseif(isset($_GET['file'])) {
  $file = $_GET['file'];
}
else {
  echo "<p class = \"error\">ERROR: No file specified!</p>";
  exit();
}


if(isset($_POST['from'])) {
  $from = $_POST['from'];
}
elseif(isset($_GET['from'])) {
  $from = $_GET['from'];
}
else {
  $from = "/admin/";
}

if(!isset($permitted_files[$file])) {
  echo "<p class = \"error\">ERROR: Invalid file specified.</p>";
  exit();
}

$filename = $permitted_files[$file];


// default action
if(!isset($_POST['action']))
	$_POST['action'] = "show_file";

switch ($_POST["action"]){
	case "show_file":
?>		<form onSubmit="submitonce(this)" action="<?echo $_SERVER['PHP_SELF']?>" method="POST">
		<p><fieldset title="Edit <?echo $filename?>">
		<legend>Edit <b><?echo $filename?></b></legend>
		<textarea name="file_contents" cols="70" rows="20" wrap="off"><?echo get_file($filename)?></textarea>
		<br>
		<input type ="hidden" name="action" value="save_mesh_settings">
		<input type ="hidden" name="from" value="<?echo $from?>">
		<input type="hidden" name="file" value="<?echo $file?>">
		<input type="submit" name="save" value="Save Changes"><input type="submit" name="cancel" value="Cancel">
		</form>
<?
		break;
	case "save_mesh_settings":
		echo "<h5>Saving file changes...</h5><ul>";
		
		$file_contents = stripslashes($_POST['file_contents']);

#		foreach(array_keys($_POST) as $param) {
#		  echo "<li>$param -> $_POST[$param]";
#		 }

		save_file($filename, $file_contents);
		
		echo "<li>$filename saved</li>";
		echo "</ul>";

		echo "</ul><p><a href='$from'>Back to Mesh Settings</a>";
		break;
}

include("./include/footer.php");
?>
