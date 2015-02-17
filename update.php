<?php
session_start();

require 'query/look_up_query.php';
require 'query/update_listing.php';
require 'print_error.php';
require 'functions.php';

$results = null;
$hasPermission = false;
$id = 0;

if(isset($_SESSION['Email'])) {
	$hasPermission = hasUpdatePermission($id, $_SESSION['Email'], $_SESSION["Type"]);
}

if(isset($_POST['id'])) {
	$id = (int) $_POST['id'];
	$results = lookUp($id);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>IMBD - Update
</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
</head>
<body>
<h1>Indiana Music Business Directory</h1>
<?php require 'header.php'; ?>
<section>
<?php
// If there is no ID, the results will be null
if(is_null($results)) {
	printUpdateFailedError();
}
// Check the user's permissions
else if(is_array($hasPermission)) {
	printErrorFromCode($hasPermission["Code"]);
}
else if(!$hasPermission) {
	printNoPermissionToChangeError();
}
// The connection failed or ID was not found in database
else if(isset($results["Error"])) {
	printErrorFromCode($results["Code"]);
}
// Post variables are not set
else if(!isset($_POST['name']) or !isset($_POST['type']) or !isset($_POST['description'])) {
	printUpdateFailedError();
}
// Update
else {
	$name = $_POST['name'];
	$type = (int) $_POST['type'];
	$description = $_POST['description'];
	$update = updateListing($id, $name, $type, $description, null);
	echo "<div class='content'>";
	echo "Update was successful. Click <a href='listing.php?id={$id}'>here</a> to continue.";
	echo "</div>";
}

?>
</section>
</body>
</html>