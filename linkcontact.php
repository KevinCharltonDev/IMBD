<?php
session_start();

require 'query/look_up_query.php';
require 'query/update_listing.php';
require 'php/functions.php';
require 'connect/config.php';
require 'query/add_query.php';

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
$contactid = (int) $_GET['contactid'];
$fname = $_GET['fname'];
$results = lookUp($conn, $id);
$hasPermission = hasUpdatePermission($conn, $id, $_SESSION['Email'], $_SESSION["Type"]);
$update = null;

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
<?php require 'php/header.php'; ?>
<section>
<?php
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
	echo "<p><a href='update.php?id={$id}'>Back</a></p>";
	echo "<form action='linkContact.php?id={$id}&contactid={$contactid}&fname={$fname}' method='POST'>\n";
	echo "<h3>Select a location to link contact {$_GET['fname']} to.</h3><hr>\n";
	
	// Location Tables
	for($i = 0; $i < sizeof($results["Locations"]); $i++){
		echo "<div class='content'><br>\n";
		$address1 = htmlspecialchars($results["Locations"][$i]["Address1"]);
		$address2 = htmlspecialchars($results["Locations"][$i]["Address2"]);
		$city = htmlspecialchars($results["Locations"][$i]["City"]);
		$zip = htmlspecialchars($results["Locations"][$i]["Zip"]);
		$locationid = htmlspecialchars($results["Locations"][$i]["L_Id"]);
		
		$table = new HTMLTable();
	
		$radioButton = HTMLTag::create("input", true, true)->attribute("type", "radio")->attribute("name", "selection")->attribute("value", $locationid);
		$table->cell($radioButton->html())->nextRow();
	
		$table->cell("Address 1: ")->cell($address1)->nextRow();
	
		$table->cell("Address 2: ")->cell($address2)->nextRow();
		
		$table->cell("City: ")->cell($city)->nextRow();

		$table->cell("Zip code: ")->cell($zip)->nextRow();
		
		echo $table->html();

		echo "<hr></div>\n";
	}
	
	$table = new HTMLTable();
	$table->cell('<input type="submit" value="Submit">')->cell('&nbsp;');
	echo $table->html();
	echo "<input type='hidden' name='id' value='{$id}'>";
	echo "</form>\n";
	echo "</div>\n";
}
?>
<?php
$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
if(isset($_POST['selection']) and $hasPermission === true) {
	$locationid = $_POST['selection'];
	
	$update = linkLocationContact($conn, $locationid, $contactid);
	
	// If update was successful, redirect to business page
	if(isset($update["Success"])) {
		echo "The contact was successfully linked to the location.";
	}
	else{
		echo "There was a problem linking the contact to the location or they were already linked.";
	}
}
$conn->close();
?>
</section>
</body>
</html>
