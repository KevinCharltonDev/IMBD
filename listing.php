<?php
session_start();

require 'query/look_up_query.php';
require 'query/update_listing.php';
require 'print_error.php';
require 'functions.php';

$results = null;
$hasPermission = false;
$id = 0;

if(isset($_GET['id'])) {
	$id = (int) $_GET['id'];
	$results = lookUp($id);
}

// User is logged in
if(isset($_SESSION['Email'])) {
	$hasPermission = hasUpdatePermission($id, $_SESSION['Email'], $_SESSION["Type"]);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>IMBD
<?php
if(!is_null($results) and !isset($results["Error"])) {
	echo " - " . htmlspecialchars($results["Data"]["Name"]);
}
?>
</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
</head>
<body>
<h1>Indiana Music Business Directory</h1>
<?php require 'header.php'; ?>
<section>
<?php

// An ID is necessary to view this page
if(!isset($_GET['id'])) {
	printIdNotSetError();
}
// Error when connecting to database or could not find ID in database
else if(isset($results['Error'])) {
	printErrorFromCode($results["Code"]);
}
// User wants to edit the page
else if(isset($_GET["edit"])) {
	
	// Redirect to login page if not logged in
	if(!isset($_SESSION['Email'])) {
		redirect("login.php");
		exit;
	}
	
	// If there is an error, $hasPermission will be an error array
	if(is_array($hasPermission)) {
		printErrorFromCode($hasPermission["Code"]);
	}
	// User does not have permission to edit
	else if(!$hasPermission) {
		printNoPermissionToChangeError();
	}
	//User has permission to edit
	else {
		$name = htmlspecialchars($results["Data"]["Name"]);
		$type = spTypeToString($results["Data"]["Type"]);
		$description = htmlspecialchars($results["Data"]["Description"]);

		echo "<h2>{$name}</h2>\n";
		echo "<div class='content'>\n";
		echo "<p><a href='listing.php?id={$id}'>Back</a></p>";
		echo "<form action='update.php' method='POST'>\n";
		
		$nameRow = tr(td("Name: "), td("<input type='text' name='name' value='{$name}'>"));
		
		$selectedValue = $results["Data"]["Type"];
		$typeDropDown = select('type',
			option("Individual", 0, $selectedValue),
			option("Group", 1, $selectedValue),
			option("Business", 2, $selectedValue),
			option("Organization", 3, $selectedValue));
		$typeRow = tr(td("Type: "), td($typeDropDown));
		
		$descriptionRow = tr(td("Description: "), td(textarea('description', 50, 6, 255, $description)));
		
		$submitRow = tr(td("<input type='submit' value='Submit'>"), td(""));
		
		echo table($nameRow, $typeRow, $descriptionRow, $submitRow);
		echo "<input type='hidden' name='id' value='{$id}'>";
		echo "</form>\n";
		echo "</div>\n";
	}
}
// User just wants to view the data
else {
	$name = htmlspecialchars($results["Data"]["Name"]);
	$type = spTypeToString($results["Data"]["Type"]);
	$description = htmlspecialchars($results["Data"]["Description"]);
	
	echo "<h2>{$name}</h2>\n";
	echo "<div class='content'>";
	
	if($hasPermission === true) {
		echo "<p><a href='listing.php?id={$id}&amp;edit='>Edit</a></p>";
	}
	
	echo "<h3>{$type}</h3>";
	echo "<p>{$description}</p>\n";
	
	$websites = $results["Data"]["Websites"];
	if(count($websites) > 0)
		echo "<h3>Websites</h3>\n";
	
	foreach($websites as $website) {
		echo htmlspecialchars($website) . "<br>\n";
	}
	
	$contacts = $results["Contacts"];
	if(count($contacts) > 0)
		echo "<h3>Contacts</h3>\n";
	
	foreach($contacts as $contact) {
		printContact($contact);
		echo "<br>\n";
	}
	
	$locations = $results["Locations"];
	if(count($locations) > 0)
		echo "<h3>Locations</h3>\n";
	
	foreach($locations as $location) {
		printLocation($location);
	}
	
	$reviews = $results["Reviews"];
	if(count($reviews) > 0)
		echo "<h3>Reviews</h3>\n";
	
	foreach($reviews as $review) {
		printReview($review);
	}
	echo "</div>";
}
?>
</section>
</body>
</html>