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
$locationid = (int) $_GET['locationid'];
$address1 = $_GET['address'];
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
	echo "<form action='linklocation.php?id={$id}&locationid={$locationid}&address={$address1}' method='POST'>\n";
	echo "<h3>Select a contact to link to location at {$_GET['address']} to.</h3><hr>\n";
	
	// Contact Tables
	for($i = 0; $i < sizeof($results["Contacts"]); $i++){
		echo "<div class='content'><br>\n";
		$fname = htmlspecialchars($results["Contacts"][$i]["First"]);
		$lname = htmlspecialchars($results["Contacts"][$i]["Last"]);
		$email = htmlspecialchars($results["Contacts"][$i]["Email"]);
		$job = htmlspecialchars($results["Contacts"][$i]["Job"]);
		$phone = htmlspecialchars($results["Contacts"][$i]["Phone"]);
		$contactid = htmlspecialchars($results["Contacts"][$i]["C_Id"]);
		
		$table = new HTMLTable();
	
		$radioButton = HTMLTag::create("input", true, true)->attribute("type", "radio")->attribute("name", "selection")->attribute("value", $contactid);
		$table->cell($radioButton->html())->nextRow();
	
		$table->cell("First name: ")->cell($fname)->nextRow();
	
		$table->cell("Last name: ")->cell($lname)->nextRow();
		
		$table->cell("email: ")->cell($email)->nextRow();

		$table->cell("Job title: ")->cell($job)->nextRow();
		
		$table->cell("Phone: ")->cell($phone)->nextRow();
		
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
	$contactid = $_POST['selection'];
	
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
