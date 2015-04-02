<?php
session_start();

require 'query/add_query.php';
require 'query/update_listing.php';
require 'query/look_up_query.php';
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

$id = (int) $_GET['id'];
$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);

// User must have update permission to view this page
$hasPermission = hasUpdatePermission($conn, $id, $_SESSION['Email'], $_SESSION["Type"]);
if($hasPermission !== true) {
	redirect("listing.php?id={$id}");
	exit;
}

$location = isset($_SESSION['Location']) ? $_SESSION['Location'] : defaultLocation();
$selectedContacts = isset($_SESSION['Contacts']) ? $_SESSION['Contacts'] : array();
unset($_SESSION['Location']);
unset($_SESSION['Contacts']);

$contacts = contacts($conn, $id);

if(isPostSet('address1', 'address2', 'city', 'state', 'zip')) {
	$location = locationFromPost();
	
	if(isPostSet('contacts')) {
		$selectedContacts = $_POST['contacts'];
	}
		
	$addLocation = addLocation($conn, $location, $id);
	setResult($addLocation);
	
	if(isset($addLocation['Success'])) {
		linkManyContactsLocation($conn, $selectedContacts, $addLocation['Id']);
		redirect("listing.php?id={$id}");
		exit;
	}
	else {
		$_SESSION['Location'] = $location;
		$_SESSION['Contacts'] = $selectedContacts;
		redirect("addlocation.php?id={$id}");
		exit;
	}
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Add a Location</title>
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
?>
<h2>Add a Location</h2>
<div class="content">
<p><a href="listing.php?id=<?php echo $id; ?>">Back</a></p>
<form action="addlocation.php?id=<?php echo $id; ?>" method="POST">
<?php
echo "<h3>Fill out all appropriate fields.</h3>\n";
locationForm($location);
if(!isset($contacts['Error']) && count($contacts) > 0) {
	echo "<h3>Choose the people that can be contacted for this location.</h3>\n";
	contactsForLocationForm($contacts, $selectedContacts);
}
?>
<input type="submit" value="Submit"/>
</form>
</div>
</section>
</body>
</html>