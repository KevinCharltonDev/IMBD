<?php
session_start();

require 'query/look_up_query.php';
require 'query/update_listing.php';
require 'functions.php';
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
$hasLocationPermission = null;
$updateLocation = null;

if(isset($_POST['lid'], $_POST['address1'], $_POST['address2'], $_POST['city'], $_POST['state'], $_POST['zip'])) {
	$l_id = (int) $_POST['lid'];
	$address1 = $_POST['address1'];
	$address2 = $_POST['address2'];
	$city = $_POST['city'];
	$state = $_POST['state'];
	$zip = $_POST['zip'];
	
	$hasLocationPermission = hasLocationUpdatePermission($conn, $l_id, $_SESSION['Email'], $_SESSION["Type"]);
	if($hasLocationPermission === true) {
		$updateLocation = updateLocation($conn, $address1, $address2, $city, $state, $zip, $l_id);
		
		if(isset($updateLocation['Success'])) {
			redirect("listing.php?id={$sp_id}");
			exit;
		}
	}
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Update Locations</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
<script src="js/functions.js"></script>
</head>
<body>
<?php require 'header.php';?>
<section>
<?php
if(!is_null($hasLocationPermission) and isset($hasLocationPermission["Error"])) {
	printError($hasLocationPermission["Error"]);
}
else if(!is_null($hasLocationPermission) and $hasLocationPermission === false) {
	printError("You do not have permission to update this location.");
}
else if(!is_null($updateLocation) and isset($updateLocation["Error"])) {
	printError($updateLocation["Message"]);
}

echo "<h2>Update Locations</h2>\n";
echo "<div class='content'>\n";
echo "<p><a href='listing.php?id={$sp_id}'>Back</a></p>";
$count = count($locations);

foreach($locations as $i => $location) {
	$l_id = (int) $location["L_Id"];
	$n = $i + 1;
	echo "<form action='updatelocation.php?id={$sp_id}' method='POST' id='location{$n}'>\n";
	echo "<h3>Location {$n}</h3>\n";
	locationForm($location['Address1'], $location['Address2'], $location['City'], $location['State'], $location['Zip']);
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
	echo "<br/>\n";
	echo '<input type="submit" value="Submit"/>';
	echo "</form>";
}
?>
<script type="text/javascript">
toggleMultipleDisplay("location", 2, <?php echo $count; ?>);
</script>
</div>
</section>
</body>
</html>