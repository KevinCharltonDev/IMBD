<?php
session_start();

require 'query/look_up_query.php';
require 'query/update_listing.php';
require 'query/review_query.php';
require 'query/account_query.php';
require 'php/functions.php';
require 'php/data.php';
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

$account = accountInfo($conn, $_SESSION['Email']);
$isAdmin = ($account['Type']==2);
	
if(isset($_POST['rating'], $_POST['comment'], $_SESSION['Email'])) {
	$exists = reviewExists($conn, $id, $_SESSION['Email']);
	if($exists === true) {
		$update = updateReview($conn, $id, (int) $_POST['rating'], $_POST['comment'], $_SESSION['Email']);
		setResult($update);
	}
	else if($exists === false) {
		$insert = insertReview($conn, $id, (int) $_POST['rating'], $_POST['comment'], $_SESSION['Email']);
		setResult($insert);
	}
	else {
		setResult($exists);
	}
	
	redirect("listing.php?id={$id}");
	exit;
}

if(isset($_POST['delete'], $_SESSION['Email'])){
	$delete = deleteReview($conn, $_SESSION['Email'], $id);
	setResult($delete);
	redirect("listing.php?id={$id}");
	exit;
}

if(isset($_POST['reportaccount'], $_SESSION['Email'])){
	$report = reportAccount($conn, $_POST['reportaccount']);
	setResult($report);
	redirect("listing.php?id={$id}");
	exit;
}

if(isset($_POST['report'], $_SESSION['Email'])){
	$report = reportReview($conn, $_POST['report'], $id);
	setResult($report);
	redirect("listing.php?id={$id}");
	exit;
}

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>
<?php
if(!isset($results["Error"])) {
	echo htmlspecialchars($results["Data"]["Name"]);
}
else {
	echo "Indiana Music Business Directory";
}
?>
</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
<script src="js/functions.js"></script>
</head>
<body>
<?php require 'php/header.php'; ?>
<section>
<?php
if(isset($_SESSION['Error'])) {
	printError($_SESSION['Error']['Message']);
	unsetResult();
}
if(isset($_SESSION['Success'])) {
	printMessage($_SESSION['Success']['Message']);
	unsetResult();
}
if(isset($results['Error'])) {
	printError($results["Message"], "index.php");
}


if(!isset($results['Error'])) {
	$name = htmlspecialchars($results["Data"]["Name"]);
	$type = businessTypeString($results["Data"]["Type"]);
	$description = htmlspecialchars($results["Data"]["Description"]);
	
	echo "<h2>{$name}</h2>\n";
	echo "<div class='content'>";
	
	if($hasPermission === true) {
		echo "<p><a href='update.php?id={$id}'>Edit</a> | ";
		echo "<a href='addlocation.php?id={$id}'>Add a Location</a> | ";
		echo "<a href='addcontact.php?id={$id}'>Add a Contact</a> | ";
		echo "<a href='updatelocation.php?id={$id}'>Update Locations</a> | ";
		echo "<a href='updatecontact.php?id={$id}'>Update Contacts</a></p>";
	}
	
	echo "<h3>{$type}</h3>";
	echo "<p>{$description}</p>\n";
	
	$websites = $results["Data"]["Websites"];
	if(count($websites) > 0)
		echo "<h3>Websites</h3>\n";
	
	foreach($websites as $website) {
		$link = htmlspecialchars($website);
		$javascript = "return confirm('Clicking OK will open the following website in a new tab.\\n    {$link}\\n\\nIf you do not trust this website, click Cancel to go back.');";
		if(substr(strtolower($link), 0, 4) != 'http')
			$link = 'http://' . $link;
		
		
		echo '<a href="' . $link . '" target="_blank" onclick="' . $javascript . '">' .htmlspecialchars($website) . "</a><br>\n";
	}
	
	$contacts = $results["Contacts"];
	if(count($contacts) > 0)
		echo "<h3>Contacts</h3>\n";
	
	foreach($contacts as $contact) {
		printContact($contact);
		if($hasPermission){
			$fname = $contact["First"];
			$lname = $contact["Last"];
			$contactid = $contact["C_Id"];
			echo "<div><a href='linkcontact.php?id={$id}&contactid={$contactid}&fname={$fname}'>Link this contact to a location.</a></div>";
		}
		echo "<br>\n";
	}
	
	$locations = $results["Locations"];
	if(count($locations) > 0)
		echo "<h3>Locations</h3>\n";
	
	foreach($locations as $location) {
		printLocation($location);
		
		if($hasPermission){
			$address1 = $location["Address1"];
			$locationid = $location["L_Id"];
			echo "<a href='linklocation.php?id={$id}&locationid={$locationid}&address={$address1}'>Link this location to a contact.</a><hr>";
		}
		
		echo "<br/>\n";
	}
	
	$services = $results['Services'];
	foreach($services as $serviceName => $service) {
		printService($serviceName, $service);
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
		echo "<textarea name='comment' placeholder='Please note that submitting this will overwrite any existing comment you have.'></textarea><br>\n";
		echo "<input type='submit' value='Submit'>";
		echo "</form>\n";
		echo "</div>\n";
		echo "</div>\n";
	}
	
	foreach($reviews as $review) {
		echo "<form action='listing.php?id={$id}' method='POST'>\n";
		if(isset($_SESSION['ScreenName']) and $review['Name'] == $_SESSION['ScreenName']) {
			printMyReview($review);
		}
		else{
			printReview($review, $isAdmin);
			if($isAdmin){
				echo "<form action='listing.php?id={$id}' method='POST'><input type='hidden' name='reportaccount' value='{$review['Name']}'>\n";
				echo "<input type='submit' value='Flag this account.'></form>\n";
			}
		}
		echo "</div>\n";
		echo "</div>\n";
	}
	
	echo "</div>\n";
}
?>
</section>
</body>
</html>