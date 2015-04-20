<?php
session_start();

require 'query/look_up_query.php';
require 'query/update_listing.php';
require 'query/add_query.php';
require 'query/delete_query.php';
require 'php/functions.php';
require 'php/data.php';
require 'connect/config.php';

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
$sp_id = (int) $_GET['id'];

// User must have update permission to view this page
$hasPermission = hasUpdatePermission($conn, $sp_id, $_SESSION['Email'], $_SESSION["Type"]);
if($hasPermission !== true) {
	redirect("listing.php?id={$sp_id}");
	exit;
}

$contacts = contacts($conn, $sp_id);
$locations = locations($conn, $sp_id);
$selectedLocationsList = array();

foreach($contacts as $contact) {
	$selectedLocations = locationsForContact($conn, $sp_id, $contact['C_Id']);
	$locationIdArray = array();
	foreach($selectedLocations as $selectedLocation) {
		$locationIdArray[] = $selectedLocation['L_Id'];
	}
	
	$selectedLocationsList[(string)$contact['C_Id']] = $locationIdArray;
}

if(isPostSet('cid', 'first', 'last', 'email', 'job', 'phone', 'extension')) {
	$c_id = (int) $_POST['cid'];
	$contact = contactFromPost();
	
	$selectedLocations = isset($_POST['locations']) ? $_POST['locations'] : array();
	
	$hasContactPermission = hasContactUpdatePermission($conn, $c_id, $_SESSION['Email'], $_SESSION["Type"]);
	if($hasContactPermission === true) {
		$updateContact = updateContact($conn, $contact, $c_id);
		setResult($updateContact);
		
		if(isset($updateContact['Success'])) {
			linkManyLocationsContact($conn, $selectedLocations, $c_id);
			redirect("listing.php?id={$sp_id}");
			exit;
		}
		else {
			redirect("updatecontact.php?id={$sp_id}");
			exit;
		}
	}
	else if($hasContactPermission === false) {
		setMessage("You do not have permission to update this contact.", true);
		redirect("listing.php?id={$sp_id}");
		exit;
	}
	else {
		setResult($hasContactPermission);
		redirect("updatecontact.php?id={$sp_id}");
		exit;
	}
}

if(isPostSet('delete')) {
	$c_id = (int) $_POST['delete'];
	
	$hasContactPermission = hasContactUpdatePermission($conn, $c_id, $_SESSION['Email'], $_SESSION["Type"]);
	if($hasContactPermission === true) {
		$deleteContact = deleteContact($conn, $c_id);
		setResult($deleteContact);
		redirect("listing.php?id={$sp_id}");
		exit;
	}
	else if($hasContactPermission === false) {
		setMessage("You do not have permission to delete this contact.", true);
		redirect("listing.php?id={$sp_id}");
		exit;
	}
	else {
		setResult($hasContactPermission);
		redirect("updatecontact.php?id={$sp_id}");
		exit;
	}
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Update Contacts</title>
<link rel="icon" type="image/x-icon" href="images/favicon.ico">
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
<script src="js/functions.js"></script>
</head>
<body>
<?php require 'php/header.php';?>
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

echo "<h2>Update Contacts</h2>\n";
echo "<div class='content'>\n";
echo "<p><a href='listing.php?id={$sp_id}'>Back</a></p>";
$count = count($contacts);

foreach($contacts as $i => $contact) {
	$c_id = (int) $contact["C_Id"];
	$n = $i + 1;
	echo "<div id='contact{$n}' style='padding:0;'>";
	echo "<form action='updatecontact.php?id={$sp_id}' method='POST'>\n";
	echo "<h3>Contact {$n}</h3>\n";
	contactForm($contact);
	echo "<br>\n";
	echo "<h3>This contact information is for the following locations.</h3>";
	locationsForContactForm($locations, $selectedLocationsList[(string)$c_id]);
	echo "<input type='hidden' name='cid' value='{$c_id}'/>\n";
	
	$prev = HTMLTag::create("input", true, true)->
		attribute("type", "button")->
		attribute("value", "Previous")->
		attribute("onclick", "showPrev('contact', {$n});");
	if($i == 0)
		$prev->attribute("disabled", "disabled");
	
	echo $prev->html();
		
	$next = HTMLTag::create("input", true, true)->
		attribute("type", "button")->
		attribute("value", "Next")->
		attribute("onclick", "showNext('contact', {$n});");
	if($i == $count - 1)
		$next->attribute("disabled", "disabled");
	
	echo $next->html();
	
	echo "<br/>\n";
	echo "<hr>\n";
	echo '<input type="submit" value="Submit"/>';
	echo "</form>\n";
	echo "<br>\n";
	echo "<br>\n";
	echo "<form action='updatecontact.php?id={$sp_id}' method='POST'>";
	echo "<input type='hidden' name='delete' value='{$c_id}'>";
	echo '<input type="submit" value="Delete"/>';
	echo "</form>\n";
	echo "</div>\n";
}
?>
<script type="text/javascript">
toggleMultipleDisplay("contact", 2, <?php echo $count; ?>);
</script>
</div>
</section>
<?php include 'php/footer.php'; ?>
</body>
</html>