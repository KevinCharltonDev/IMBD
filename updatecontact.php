<?php
session_start();

require 'query/look_up_query.php';
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

$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
$sp_id = (int) $_GET['id'];
$contacts = contacts($conn, $sp_id);

if(isset($contacts["Error"])) {
	redirect();
	exit;
}

$updateContact = null;
$hasPermission = hasUpdatePermission($conn, $sp_id, $_SESSION['Email'], $_SESSION["Type"]);

if(isset($_POST['cid'], $_POST['first'], $_POST['last'], $_POST['email'], $_POST['job'], $_POST['phone'], $_POST['extension'])) {
	$c_id = (int) $_POST['cid'];
	$first = $_POST['first'];
	$last = $_POST['last'];
	$email = $_POST['email'];
	$job = $_POST['job'];
	$phone = $_POST['phone'];
	$extension = $_POST['extension'];
	
	$hasContactPermission = hasContactUpdatePermission($conn, $c_id, $_SESSION['Email'], $_SESSION["Type"]);
	if($hasContactPermission === true) {
		$updateContact = updateContact($conn, $first, $last, $email, $job, $phone, $extension, $c_id);
		
		if(isset($updateContact['Success'])) {
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
<title>IMBD - Update Contact Information
</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php require 'header.php';?>
<section>
<?php
if(!is_null($updateContact) and isset($updateContact["Error"])) {
	printError($updateContact["Message"]);
}
// If there is an error, $hasPermission will be an error array
else if(is_array($hasPermission)) {
	printError($hasPermission["Message"]);
}
// User does not have permission to edit
else if(!$hasPermission) {
	printError("You do not have permission to update this.", "listing.php?id={$sp_id}");
}

if($hasPermission === true) {
	echo "<h2>Update Contact Information</h2>\n";
	echo "<div class='content'>\n";
	echo "<p><a href='listing.php?id={$sp_id}'>Back</a></p>";
	
	foreach($contacts as $contact) {
		$c_id = (int) $contact["C_Id"];
		echo "<form action='updatecontact.php?id={$sp_id}' method='POST'>\n";
		contactForm($contact['First'], $contact['Last'], $contact['Email'], $contact['Job'], $contact['Phone'], $contact['Extension']);
		echo "<input type='hidden' name='cid' value='{$c_id}'/>\n";
		echo '<input type="submit" value="Submit"/>';
		echo "</form>\n";
	}
	echo "</div>\n";
}
?>
</section>
</body>
</html>