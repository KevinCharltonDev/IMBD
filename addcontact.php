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

$contact = isset($_SESSION['Contact']) ? $_SESSION['Contact'] : defaultContact();
$selectedLocations = isset($_SESSION['Locations']) ? $_SESSION['Locations'] : array();
unset($_SESSION['Contact']);
unset($_SESSION['Locations']);

$locations = locations($conn, $id);

if(isPostSet('first', 'last', 'email', 'job', 'phone', 'extension')) {
	$contact = contactFromPost();
	
	if(isPostSet('locations')) {
		$selectedLocations = $_POST['locations'];
	}
	
	$addContact = addContact($conn, $contact, $id);
	setResult($addContact);
	
	if(isset($addContact['Success'])) {
		linkManyLocationsContact($conn, $selectedLocations, $addContact['Id']);
		redirect("listing.php?id={$id}");
		exit;
	}
	else {
		$_SESSION['Contact'] = $contact;
		$_SESSION['Locations'] = $selectedLocations;
		redirect("addcontact.php?id={$id}");
		exit;
	}
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Add a Contact</title>
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
?>
<h2>Add a Contact</h2>
<div class="content">
<p><a href="listing.php?id=<?php echo $id; ?>">Back</a></p>
<form action="addcontact.php?id=<?php echo $id; ?>" method="POST">
<?php
echo "<h3>Fill out all appropriate fields.</h3>\n";
contactForm($contact);
if(!isset($locations['Error']) && count($locations) > 0) {
	echo "<br>\n";
	echo "<h3>Choose the locations this contact information is for.</h3>\n";
	locationsForContactForm($locations, $selectedLocations);
}
?>
<hr>
<input type="submit" value="Submit"/>
</form>
</div>
</section>
<?php include 'php/footer.php'; ?>
</body>
</html>