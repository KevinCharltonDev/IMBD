<?php
session_start();

require 'query/look_up_query.php';
require 'query/update_listing.php';
require 'print_error.php';
require 'functions.php';

// An ID is needed to view this page so redirect to home page if not set
if(!isset($_GET['id'])) {
	redirect();
	exit;
}

$hasPermission = false;
$id = (int) $_GET['id'];
$results = lookUp($id);

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
if(!isset($results["Error"])) {
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
// Error when connecting to database or could not find ID in database
if(isset($results['Error'])) {
	printErrorFromCode($results["Code"]);
}
else {
	$name = htmlspecialchars($results["Data"]["Name"]);
	$type = spTypeToString($results["Data"]["Type"]);
	$description = htmlspecialchars($results["Data"]["Description"]);
	
	echo "<h2>{$name}</h2>\n";
	echo "<div class='content'>";
	
	if($hasPermission === true) {
		echo "<p><a href='update.php?id={$id}'>Edit</a></p>";
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
	if(isset($_SESSION['Email'])) {
	echo "<h4 onclick = 'reviewBox(\"{$id}\")' style='position:relative; bottom:20px; left:16px'><u>Review this listing</u></h4>";
	echo "<form action='review.php' id = '{$id}' style='margin-left:24px' method='POST' hidden><textarea name = 'id' hidden>{$id}</textarea>";
	echo "<textarea name ='review'></textarea><br><input type='submit' value='Submit'></form>";
	}
	echo "</div>";
}
?>
<script type = "text/javascript">
				function reviewBox(id){
					var string = id;
					var input = document.getElementById(id);
					 if(input.style.display == 'block')
						input.style.display = 'none';
					else
						input.style.display = 'block';
				}
</script>
</section>
</body>
</html>