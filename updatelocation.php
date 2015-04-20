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

$locations = locations($conn, $sp_id);
$contacts = contacts($conn, $sp_id);
$selectedContactsList = array();

foreach($locations as $location) {
	$selectedContacts = contactsForLocation($conn, $sp_id, $location['L_Id']);
	$contactIdArray = array();
	foreach($selectedContacts as $selectedContact) {
		$contactIdArray[] = $selectedContact['C_Id'];
	}
	
	$selectedContactsList[(string)$location['L_Id']] = $contactIdArray;
}

if(isPostSet('lid', 'address1', 'address2', 'city', 'state', 'zip')) {
	$l_id = (int) $_POST['lid'];
	$location = locationFromPost();
	
	$selectedContacts = isset($_POST['contacts']) ? $_POST['contacts'] : array();
	
	$hasLocationPermission = hasLocationUpdatePermission($conn, $l_id, $_SESSION['Email'], $_SESSION["Type"]);
	if($hasLocationPermission === true) {
		$updateLocation = updateLocation($conn, $location, $l_id);
		setResult($updateLocation);
		
		if(isset($updateLocation['Success'])) {
			linkManyContactsLocation($conn, $selectedContacts, $l_id);
			redirect("listing.php?id={$sp_id}");
			exit;
		}
		else {
			redirect("updatelocation.php?id={$sp_id}");
			exit;
		}
	}
	else if($hasLocationPermission === false) {
		setMessage("You do not have permission to update this location.", true);
		redirect("listing.php?id={$sp_id}");
		exit;
	}
	else {
		setResult($hasLocationPermission);
		redirect("updatelocation.php?id={$sp_id}");
		exit;
	}
}

if(isPostSet('delete')) {
	$l_id = (int) $_POST['delete'];
	
	$hasLocationPermission = hasLocationUpdatePermission($conn, $l_id, $_SESSION['Email'], $_SESSION["Type"]);
	if($hasLocationPermission === true) {
		$deleteLocation = deleteLocation($conn, $l_id);
		setResult($deleteLocation);
		redirect("listing.php?id={$sp_id}");
		exit;
	}
	else if($hasLocationPermission === false) {
		setMessage("You do not have permission to delete this location.", true);
		redirect("listing.php?id={$sp_id}");
		exit;
	}
	else {
		setResult($hasLocationPermission);
		redirect("updatelocation.php?id={$sp_id}");
		exit;
	}
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Update Locations</title>
<link rel="icon" type="image/x-icon" href="images/favicon.ico">
<?php require 'php/css_include.php'; ?>
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

echo "<h2>Update Locations</h2>\n";
echo "<div class='content'>\n";
echo "<p><a href='listing.php?id={$sp_id}'>Back</a></p>";
$count = count($locations);

foreach($locations as $i => $location) {
	$l_id = (int) $location["L_Id"];
	$n = $i + 1;
	echo "<div id='location{$n}' style='padding:0;'>";
	echo "<form action='updatelocation.php?id={$sp_id}' method='POST'>\n";
	echo "<h3>Location {$n}</h3>\n";
	locationForm($location);
	echo "<br>\n";
	echo "<h3>This following contacts are for this location.</h3>";
	contactsForLocationForm($contacts, $selectedContactsList[(string)$l_id]);
	echo "<input type='hidden' name='lid' value='{$l_id}'/>\n";
	
	$prev = HTMLTag::create("input", true, true)->
		attribute("type", "button")->
		attribute("value", "Previous")->
		attribute("onclick", "showPrev('location', {$n});");
	if($i == 0)
		$prev->attribute("disabled", "disabled");
	
	echo $prev->html();
		
	$next = HTMLTag::create("input", true, true)->
		attribute("type", "button")->
		attribute("value", "Next")->
		attribute("onclick", "showNext('location', {$n});");
	if($i == $count - 1)
		$next->attribute("disabled", "disabled");
	
	echo $next->html();
		
	echo "<br/>\n";
	echo "<hr>\n";
	echo '<input type="submit" value="Submit"/>';
	echo "</form>";
	echo "<br>\n";
	echo "<br>\n";
	echo "<form action='updatelocation.php?id={$sp_id}' method='POST'>\n";
	echo "<input type='hidden' name='delete' value='{$l_id}'>";
	echo '<input type="submit" value="Delete"/>';
	echo "</form>\n";
	echo "</div>\n";
}
?>
<script type="text/javascript">
toggleMultipleDisplay("location", 2, <?php echo $count; ?>);
</script>
</div>
</section>
<?php include 'php/footer.php'; ?>
</body>
</html>