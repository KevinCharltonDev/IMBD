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
$address1 = null;
$address2 = null;
$city = null;
$state = null;
$zip = null;

$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
$addLocation = null;
$hasPermission = hasUpdatePermission($conn, $id, $_SESSION['Email'], $_SESSION["Type"]);

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
<title>IMBD - Add Location
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
// If there is an error, $hasPermission will be an error array
else if(is_array($hasPermission)) {
	printError($hasPermission["Message"]);
}
// User does not have permission to edit
else if(!$hasPermission) {
	printError("You do not have permission to update this.", "listing.php?id={$id}");
}

if($hasPermission === true) {
	echo "<h2>Add a Location</h2>\n";
	echo "<div class='content'>\n";
	echo "<p><a href='listing.php?id={$id}'>Back</a></p>";
	echo "<form action='addlocation.php?id={$id}' method='POST'>\n";

	$table = new HTMLTable();
		
	$address1TextArea = HTMLTag::create("textarea")->attribute("name", "address1")->attribute("maxlength", "60")->attribute("placeholder", "This box is required to add a location.");
	$table->cell("Address 1: ")->cell($address1TextArea->html())->nextRow();

	$address2TextArea = HTMLTag::create("textarea")->attribute("name", "address2")->attribute("maxlength", "60");
	$table->cell("Address 2: ")->cell($address2TextArea->html())->nextRow();

	$cityInput = HTMLTag::create("input", true, true)->attribute("type", "text")->attribute("name", "city")->attribute("maxlength", "30");
	$table->cell("City: ")->cell($cityInput->html())->nextRow();

	$table->cell("State: ")->cell(stateList())->nextRow();

	$zipInput = HTMLTag::create("input", true, true)->attribute("name", "zip")->attribute("maxlength", "5");
	$table->cell("Zip code: ")->cell($zipInput->html())->nextRow();
	
	$table->cell("<input type='submit' value='Submit'/>")->cell("&nbsp;");

	echo $table->html();

	echo "</form>\n";
	echo "</div>\n";
}
?>
</section>
</body>
</html>