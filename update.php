<?php
session_start();

require 'query/look_up_query.php';
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

$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
$id = (int) $_GET['id'];
$results = lookUp($conn, $id);
$hasPermission = hasUpdatePermission($conn, $id, $_SESSION['Email'], $_SESSION["Type"]);
$update = null;

if(isset($_POST['name'], $_POST['type'], $_POST['description'], $_POST['websites']) and $hasPermission === true) {
	$name = $_POST['name'];
	$type = (int) $_POST['type'];
	$description = $_POST['description'];
	$websites = websitesFromString($_POST['websites']);
	
	$update = update($conn, $id, $name, $type, $description, $websites);
	
	// If update was successful, redirect to business page
	if(isset($update["Success"])) {
		$conn->close();
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
<title>IMBD
<?php
if(!isset($results["Error"])) {
	echo " - " . htmlspecialchars($results["Data"]["Name"]);
}
?>
</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php require 'header.php'; ?>
<section>
<?php
// If update was unsuccessful, an error will be printed below.
if(!is_null($update) and isset($update["Error"])) {
	printError($update["Message"]);
}

// Error when connecting to database or could not find ID in database
if(isset($results['Error'])) {
	printError($results["Message"], "index.php");
}
// If there is an error, $hasPermission will be an error array
else if(is_array($hasPermission)) {
	printError($hasPermission["Message"]);
}
// User does not have permission to edit
else if(!$hasPermission) {
	printError("You do not have permission to update this.", "listing.php?id={$id}");
}
//User has permission to edit
else {
	$name = htmlspecialchars($results["Data"]["Name"]);

	echo "<h2>{$name}</h2>\n";
	echo "<div class='content'>\n";
	echo "<p><a href='listing.php?id={$id}'>Back</a></p>";
	echo "<h3>Information</h3>\n";
	echo "<form action='update.php?id={$id}' method='POST'>\n";
	
	businessForm($name, $results["Data"]["Type"], $results["Data"]["Description"], $results["Data"]["Websites"]);
	echo "<input type='submit' value='Submit'/>\n";
	
	foreach($results["Locations"] as $location) {
		echo "<h3>Location</h3>\n";
		locationForm($location["Address1"], $location["Address2"], $location["City"], '', $location["Zip"]);

		foreach($location["Contacts"] as $contact) {
			echo "<div>\n";
			echo "<h4>Contact</h4>\n";
			contactForm($contact["First"], $contact["Last"], $contact["Email"], $contact["Job"], $contact["Phone"], $contact["Extension"]);
			echo "</div>\n";
		}
	}
	
	//Contact Tables without locations
	foreach($results["Contacts"] as $contact) {
		echo "<h3>Contact</h3>\n";
		$fname = htmlspecialchars($results["Contacts"][$j]["First"]);
		$lname = htmlspecialchars($results["Contacts"][$j]["Last"]);
		$email = htmlspecialchars($results["Contacts"][$j]["Email"]);
		$jobtitle = htmlspecialchars($results["Contacts"][$j]["Job"]);
		$phone = htmlspecialchars($results["Contacts"][$j]["Phone"]);
		$extension = htmlspecialchars($results["Contacts"][$j]["Extension"]);
		contactForm($contact["First"], $contact["Last"], $contact["Email"], $contact["Job"], $contact["Phone"], $contact["Extension"]);
	}
	
	echo '<input type="submit" value="Submit">';
	echo "<input type='hidden' name='id' value='{$id}'>";
	
	echo "</form>\n";
	echo "</div>\n";
}
?>
</section>
</body>
</html>