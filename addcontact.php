<?php
session_start();

require 'query/add_query.php';
require 'query/update_listing.php';
require 'functions.php';
require 'connect/config.php';
require 'listStates.php';

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
$first = '';
$last = '';
$email = '';
$job = '';
$phone = '';
$extension = '';

$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
$addContact = null;
$hasPermission = hasUpdatePermission($conn, $id, $_SESSION['Email'], $_SESSION["Type"]);

if(isset($_POST['first'], $_POST['last'], $_POST['email'], $_POST['job'], $_POST['phone'], $_POST['extension'])) {
	$first = $_POST['first'];
	$last = $_POST['last'];
	$email = $_POST['email'];
	$job = $_POST['job'];
	$phone = $_POST['phone'];
	$extension = $_POST['extension'];

	$addContact = addContact($conn, $first, $last, $email, $job, $phone, $extension, $id);
	
	if(isset($addContact["Success"])) {
		redirect("listing.php?id={$id}");
		exit;
	}
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>IMBD - Add Contact
</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php require 'header.php';?>
<section>
<?php
if(!is_null($addContact) and isset($addContact["Error"])) {
	printError($addContact["Message"]);
}
// If there is an error, $hasPermission will be an error array
else if(is_array($hasPermission)) {
	printError($hasPermission["Message"]);
}
// User does not have permission to edit
else if(!$hasPermission) {
	printError("You do not have permission to update this.", "listing.php?id={$id}");
}

if($hasPermission === true) {
	echo "<h2>Add a Contact</h2>\n";
	echo "<div class='content'>\n";
	echo "<p><a href='listing.php?id={$id}'>Back</a></p>";
	echo "<form action='addcontact.php?id={$id}' method='POST'>\n";
	contactForm($first, $last, $email, $job, $phone, $extension);
	echo '<input type="submit" value="Submit"/>';
	echo "</form>\n";
	echo "</div>\n";
}
?>
</section>
</body>
</html>