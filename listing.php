<?php
session_start();

require 'query/look_up_query.php';
require 'query/update_listing.php';
require 'query/review_query.php';
require 'functions.php';
require 'connect/config.php';

// An ID is needed to view this page so redirect to home page if not set
if(!isset($_GET['id'])) {
	redirect();
	exit;
}

$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
$id = (int) $_GET['id'];
$results = lookUp($conn, $id);
$hasPermission = isset($_SESSION['Email']) ?
	hasUpdatePermission($conn, $id, $_SESSION['Email'], $_SESSION["Type"]) :
	false;
	
if(isset($_POST['rating'], $_POST['comment'], $_SESSION['Email'])) {
	$exists = reviewExists($conn, $id, $_SESSION['Email']);
	if($exists === true) {
		updateReview($conn, $id, (int) $_POST['rating'], $_POST['comment'], $_SESSION['Email']);
		redirect("listing.php?id={$id}");
	}
	else if($exists === false) {
		insertReview($conn, $id, (int) $_POST['rating'], $_POST['comment'], $_SESSION['Email']);
		redirect("listing.php?id={$id}");
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
<script src="js/functions.js"></script>
</head>
<body>
<?php require 'header.php'; ?>
<section>
<?php
// Error when connecting to database or could not find ID in database
if(isset($results['Error'])) {
	printError($results["Message"], "index.php");
}
else {
	$name = htmlspecialchars($results["Data"]["Name"]);
	$type = spTypeToString($results["Data"]["Type"]);
	$description = htmlspecialchars($results["Data"]["Description"]);
	
	echo "<h2>{$name}</h2>\n";
	echo "<div class='content'>";
	
	if($hasPermission === true) {
		echo "<p><a href='update.php?id={$id}'>Edit</a> | ";
		echo "<a href='addlocation.php?id={$id}'>Add a Location</a> | ";
		echo "<a href='addcontact.php?id={$id}'>Add a Contact</a></p>";
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
		echo "<br/>\n";
	}
	
	$reviews = $results["Reviews"];
	if(count($reviews) > 0)
		echo "<h3>Reviews</h3>\n";
	
	if(isset($_SESSION['Email'])) {
		echo "<div class='review'>\n";
		echo "<h4 onmousedown='toggleDisplay(\"reviewHidden\")'>Write a review</h4>\n";
		echo "<div id='reviewHidden'>\n";
		echo "<script type='text/javascript'>toggleDisplay(\"reviewHidden\");</script>";
		echo "<hr>\n";
		echo "<form action='listing.php?id={$id}' method='POST'>\n";
		echo "<noscript>Rating: <input type='text' name='rating'/><br></noscript>\n";
		echo "<script type='text/javascript'>\n";
		echo "var stars = new Stars(\"star\", 5, 3, false);\n";
		echo "stars.printStars();\n";
		echo "stars.printRatingInput(\"rating\");\n";
		echo "stars.attachListeners();\n";
		echo "</script>\n";
		echo "<textarea name='comment'></textarea><br>\n";
		echo "<input type='submit' value='Submit'>";
		echo "</form>\n";
		echo "</div>\n";
		echo "</div>\n";
	}
	
	foreach($reviews as $review) {
		printReview($review);
	}
	
	echo "</div>\n";
}
?>
</section>
</body>
</html>