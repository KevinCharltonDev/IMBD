<?php
session_start();

require 'query/look_up_query.php';
$results = null;

if(isset($_GET['id'])) {
	$id = (int) $_GET['id'];
	$results = lookUp($id);
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
if(!isset($_GET['id'])) {
	echo "<div class='content'><p>No listing is specified.  Please click <a href='index.php'>here</a> to go back to the home page.</p></div>";
}
else if(isset($results['Error'])) {
	$errorCode = $results["Code"];
	if($errorCode === 4) {
		echo "<div class='content'><p>No listing was found for this id.</p></div>";
	}
	else {
		echo "<div class='content'><p>Could not connect to the database</p></div>";
	}
}
else {
	require 'functions.php';
	
	$name = htmlspecialchars($results["Data"]["Name"]);
	$type = spTypeToString($results["Data"]["Type"]);
	$description = htmlspecialchars($results["Data"]["Description"]);
	
	echo "<h2>{$name}</h2>\n";
	echo "<div class='content'>";
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