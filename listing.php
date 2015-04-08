<?php
session_start();

require 'query/look_up_query.php';
require 'query/update_listing.php';
require 'query/review_query.php';
require 'query/account_query.php';
require 'query/business_query.php';
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

if(!isset($_SESSION['Email'])) {
	$_SESSION['Redirect'] = "listing.php?id={$id}";
}

$results = lookUp($conn, $id);
$hasPermission = isset($_SESSION['Email']) ?
	hasUpdatePermission($conn, $id, $_SESSION['Email'], $_SESSION["Type"]) :
	false;
	
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
	$delete = deleteReview($conn, $_SESSION['ScreenName'], $id);
	setResult($delete);
	redirect("listing.php?id={$id}");
	exit;
}

if(isset($_POST['reportaccount'], $_SESSION['Email'], $_SESSION['Type']) && $_SESSION['Type'] === 2) {
	$report = reportAccount($conn, $_POST['reportaccount']);
	setResult($report);
	redirect("listing.php?id={$id}");
	exit;
}

if(isset($_POST['report'], $_SESSION['Email'], $_SESSION['Type']) && $_SESSION['Type'] > 0){
	$report = reportReview($conn, $_POST['report'], $id);
	setResult($report);
	redirect("listing.php?id={$id}");
	exit;
}

if(isset($_POST['reportbusiness'], $_SESSION['Email'], $_SESSION['Type']) && $_SESSION['Type'] > 0){
	$report = reportBusiness($conn, $id);
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
	
	if(isset($_SESSION['Email']) AND $hasPermission === false){
		echo "<p>Is this your business? If so, you can <a href='requestedit.php?id={$id}'> request permission to edit.</a></p>";
	}
	
	echo "<h3>{$type}</h3>";
	echo "<p>{$description}</p>\n";
	
	$websites = $results["Data"]["Websites"];
	foreach($websites as $website) {
		echo safeLink($website);
	}
	
	$contacts = $results["Contacts"];
	if(count($contacts) > 0)
		echo "<hr>\n<h3>Contacts</h3>\n";
	
	foreach($contacts as $contact) {
		echo "<div>\n";
		printNotEmpty($contact['First'] . ' ' . $contact['Last']);
		printNotEmpty($contact['Job']);
		printNotEmpty(formatPhoneNumber($contact['Phone']));
		printNotEmpty($contact['Email']);
		echo "</div>\n";
		echo "<br>\n";
	}
	
	$locations = $results["Locations"];
	if(count($locations) > 0)
		echo "<hr>\n<h3>Locations</h3>\n";
	
	foreach($locations as $location) {
		echo "<div>\n";
		printNotEmpty($location['Address1']);
		printNotEmpty($location['Address2']);
		printNotEmpty($location['City'] . ', ' . $location['State'] . ' ' . $location['Zip']);
		
		$contacts = $location["Contacts"];
		if(count($contacts) > 0) {
			echo "<h4>Contacts for this location</h4>\n";
		}
		
		foreach($contacts as $contact) {
			printNotEmpty($contact["Name"]);
		}
		
		echo "</div>\n";
		echo "<br/>\n";
	}
	
	$services = $results['Services'];
	foreach($services as $serviceName => $service) {
		echo "<hr>\n<h3>" . htmlspecialchars($serviceName) . "</h3>\n";
		echo "<div>\n";
		$table = new HTMLTable();
		foreach($service as $columnName => $columnValue) {
			if($columnName === 'Sp_Id') {
				continue;
			}
			
			if(is_string($columnValue) && trim($columnValue) === '') {
				continue;
			}
			
			if(is_bool($columnValue)) {
				$value = $columnValue ? "Yes" : "No";
				$table->cell(htmlspecialchars($columnName) . ": ")->cell($value)->nextRow();
			}
			else {
				$value =str_replace(',', ', ', $columnValue);
				$table->cell(htmlspecialchars($columnName . ": "))->cell(htmlspecialchars($value))->nextRow();
			}
		}
		echo $table->html();
		echo "</div>\n";
	}
	
	if(isset($_SESSION['Email'], $_SESSION['Type'])) {
		if($_SESSION['Type'] > 0) {
			
echo <<<HTML
<br><form action="listing.php?id={$id}" method="POST" style="display: inline;">
<input type="hidden" name="reportbusiness" value="{$id}">
<input type="submit" value="Flag Business as Inappropriate">
</form>
HTML;
		}
	}
	
	echo "</div>\n";
	echo "</section><br>\n";
	echo "<section>\n";
	echo "<h2>Reviews</h2>\n";
	echo "<div class='content'>\n";
	
	$reviews = $results["Reviews"];
	
	if(isset($_SESSION['Email'])) {
		
echo <<<HTML

<div class="review">
<h4 onmousedown="toggleDisplay('reviewHidden')">Write a review</h4>
<div id="reviewHidden">
<script type="text/javascript">
	toggleDisplay("reviewHidden");
</script>
<hr>
<form action="listing.php?id={$id}" method="POST">
<noscript>Rating: <input type="text" name="rating"/><br>
</noscript>
<script type="text/javascript">
	var stars = new Stars("star", 5, 3, false);
	stars.printStars();
	stars.printRatingInput("rating");
	stars.attachListeners();
</script>
<textarea name="comment" placeholder="Please note that submitting this will overwrite any existing comment you have.">
</textarea><br>
<input type="submit" value="Submit">
</form>
</div>
</div>
HTML;

	}
	
	$count = 0;
	foreach($reviews as $review) {
		$comment = htmlspecialchars($review["Comment"]);
		$rating = htmlspecialchars($review["Rating"]);
		$date = htmlspecialchars($review["Date"]);
		$name = htmlspecialchars($review["Name"]);
		$count++;
		
echo <<<HTML

<div class="review">
<h4 onmousedown="toggleDisplay('review{$count}')">
{$name} - {$date}
</h4>
<div id="review{$count}">
<hr>
<noscript>{$rating} / 5</noscript>
<script type="text/javascript">
	var stars = new Stars("star{$count}", 5, "{$rating}", false);
	stars.printStars();
</script>
<br>
<p>{$comment}</p>
HTML;

		if(isset($_SESSION['Email'], $_SESSION['Type'])) {
			if($_SESSION['Type'] > 0) {
				
echo <<<HTML

<form action="listing.php?id={$id}" method="POST" style="display: inline;">
<input type="hidden" name="report" value="{$name}">
<input type="submit" value="Flag as Inappropriate">
</form>
HTML;

			}
			if($_SESSION['Type'] == 2) {
				
echo <<<HTML

<form action="listing.php?id={$id}" method="POST" style="display: inline;">
<input type="hidden" name="reportaccount" value="{$name}">
<input type="submit" value="Flag this Account">
</form>
HTML;

			}
			if($_SESSION['ScreenName'] === $review['Name']) {
				
echo <<<HTML

<form action="listing.php?id={$id}" method="POST" style="display: inline;">
<input type="hidden" name="delete" value="delete">
<input type="submit" value="Delete">
</form>
HTML;

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
