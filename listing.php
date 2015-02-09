<!DOCTYPE html>
<?php
require 'query/look_up_query.php';
$results = null;

if(isset($_GET['id'])) {
	$id = (int) $_GET['id'];
	$results = lookUp($id);
}
?>
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

<header>
	<div class="left">
	<a href="index.php">Home</a>
	</div>
	<div class="right">
	Sign in
	</div>
</header>
<article>
	<h1>Indiana Music Business Directory</h1>
</article>
<article>
<?php
if(!isset($_GET['id'])) {
	echo "<p>No listing is specified.  Please click <a href='index.php'>here</a> to go back to the home page.</p>";
}

if(!isset($results["Error"])) {
	//The following code is just to output the data.  It will be deleted and replaced later.
	$name = htmlspecialchars($results["Data"]["Name"]);
	$id = htmlspecialchars($results["Data"]["Id"]);
	$type = htmlspecialchars($results["Data"]["Type"]);
	$description = htmlspecialchars($results["Data"]["Description"]);
	
	echo "<h2>{$name} ({$type})</h2>\n";
	echo "ID: {$id}<br>\n";
	echo "{$description}<br><br>\n";
	
	$websites = $results["Data"]["Websites"];
	foreach($websites as $website) {
		$formatted = htmlspecialchars($website);
		echo "{$formatted}<br>";
	}
	
	function printContact($contact) {
		$first = htmlspecialchars($contact["First"]);
		$last = htmlspecialchars($contact["Last"]);
		$email = htmlspecialchars($contact["Email"]);
		$job = htmlspecialchars($contact["Job"]);
		$phone = htmlspecialchars($contact["Phone"]);
		$extension = htmlspecialchars($contact["Extension"]);
		
		echo "First Name: {$first}<br>\n";
		echo "Last Name: {$last}<br>\n";
		echo "Email: {$email}<br>\n";
		echo "Job: {$job}<br>\n";
		echo "Phone Number: {$phone}<br>\n";
		echo "Extension: {$extension}<br>\n";
	}
	
	echo "<h3>Contacts</h3>\n";
	foreach($results["Contacts"] as $contact) {
		printContact($contact);
	}
	
	function printLocation($location) {
		$address1 = htmlspecialchars($location["Address1"]);
		$address2 = htmlspecialchars($location["Address2"]);
		$city = htmlspecialchars($location["City"]);
		$state = htmlspecialchars($location["State"]);
		$zip = htmlspecialchars($location["Zip"]);
		
		echo "{$address1} {$address2}<br>\n{$city}, {$state} {$zip}";
		foreach($location["Contacts"] as $contact) {
			echo "<p>";
			printContact($contact);
			echo "</p>";
		}
	}
	
	echo "<h3>Locations</h3>\n";
	foreach($results["Locations"] as $location) {
		printLocation($location);
	}
	
	function printReview($review) {
		$comment = htmlspecialchars($review["Comment"]);
		$rating = htmlspecialchars($review["Rating"]);
		$date = htmlspecialchars($review["Date"]);
		$name = htmlspecialchars($review["Name"]);
		
		echo "Comment: {$comment}<br>\n";
		echo "Rating: {$rating}<br>\n";
		echo "Date: {$date}<br>\n";
		echo "Review By: {$name}<br><br>\n";
	}
	
	echo "<h3>Reviews</h3>";
	foreach($results["Reviews"] as $review) {
		printReview($review);
	}
}
else {
	$errorCode = $results["Code"];
	if($errorCode === 4) {
		echo "<p>No listing was found for this id.</p>";
	}
	else {
		echo "<p>Could not connect to the database</p>";
	}
}
?>
</article>
<footer>
This is the footer.
</footer>
</body>
</html>