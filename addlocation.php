<?php
session_start();

require 'query/add_query.php';
require 'query/update_listing.php';
require 'functions.php';
require 'connect/config.php';
require 'listStates.php';

// Redirect to login page if not logged in
if(!isset($_SESSION['Email'])) {
	redirect("login.php");
	exit;
}

// An ID is needed to view this page so redirect to home page if not set
if(!isset($_GET['id'])) {
	redirect();
	exit;
}

$id = (int) $_GET['id'];
$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);

// User must have update permission to view this page
$hasPermission = hasUpdatePermission($conn, $id, $_SESSION['Email'], $_SESSION["Type"]);
if($hasPermission !== true) {
	redirect("listing.php?id={$id}");
	exit;
}

$address1 = '';
$address2 = '';
$city = '';
$state = '';
$zip = '';

$addLocation = null;

if(isset($_POST['address1'], $_POST['address2'], $_POST['city'], $_POST['state'], $_POST['zip'])) {
	$address1 = $_POST['address1'];
	$address2 = $_POST['address2'];
	$city = $_POST['city'];
	$state = $_POST['state'];
	$zip = $_POST['zip'];
		
	$addLocation = addLocation($conn, $address1, $address2, $city, $state, $zip, $id);
	
	if(isset($addLocation['Success'])) {
		redirect("listing.php?id={$id}");
		exit;
	}
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>IMBD - Add a Location
</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php require 'header.php';?>
<section>
<?php
if(!is_null($addLocation) and isset($addLocation["Error"])) {
	printError($addLocation["Message"]);
}
?>
<h2>Add a Location</h2>
<div class="content">
<?php
echo "<p><a href='listing.php?id={$id}'>Back</a></p>";
echo "<form action='addlocation.php?id={$id}' method='POST'>\n";
locationForm($address1, $address2, $city, $state, $zip);
?>
<input type="submit" value="Submit"/>
</form>
</div>
</section>
</body>
</html>