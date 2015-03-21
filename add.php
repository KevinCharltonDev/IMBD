<?php
session_start();

require 'query/add_query.php';
require 'functions.php';
require 'connect/config.php';
require 'listStates.php';

// Redirect to login page if not logged in
if(!isset($_SESSION['Email'])) {
	redirect("login.php");
	exit;
}

$add = null;

if(isset($_POST['name'], $_POST['type'], $_POST['description'], $_POST['websites'])) {
	$name = $_POST['name'];
	$type = (int) $_POST['type'];
	$description = $_POST['description'];
	$websites = websitesFromString($_POST['websites']);
	
	$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
	$add = add($conn, $name, $type, $description, $websites, $_SESSION['Email']);
	
	// If successful, redirect to business page
	if(isset($add["Success"])) {
		$id = $add["Id"];
		$location = addLoc($conn, $id);
		$contact = addCon($conn, $id);
		if(isset($location["Success"]) and isset($contact["Success"])) {
			linkLocationContact($conn, $location['Id'], $contact['Id']);
		}
		
		redirect("listing.php?id={$id}");
		exit;
	}
	
	$conn->close();
}

function addLoc($conn, $id){
	if(isset($_POST['address1'], $_POST['address2'], $_POST['city'], $_POST['state'], $_POST['zip']) and trim($_POST['address1'])!="") {
		$address1 = $_POST['address1'];
		$address2 = $_POST['address2'];
		$city = $_POST['city'];
		$state = $_POST['state'];
		$zip = $_POST['zip'];
		$spId = (int) $id;
			
		$add = addLocation($conn, $address1, $address2, $city, $state, $zip, $spId);
		return $add;
	}
}

function addCon($conn, $id){
	if(isset($_POST['first'], $_POST['last'], $_POST['email'], $_POST['job'], $_POST['phone'], $_POST['extension']) and trim($_POST['first'])!="") {
		$fname = $_POST['first'];
		$lname = $_POST['last'];
		$email = $_POST['email'];
		$jobTitle = $_POST['job'];
		$phone = $_POST['phone'];
		$extension = $_POST['extension'];
		$spId = (int) $id;

		$add = addContact($conn, $fname, $lname, $email, $jobTitle, $phone, $extension, $spId);
		return $add;
	}
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>IMBD - Add Business
</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php require 'header.php';?>
<section>
<?php
$error = null;
if(!is_null($add) and isset($add["Error"])) {
	printError($add["Message"]);
}
?>
<form action="add.php" method="POST">

<div class='content'>
<h3>Add a Business</h3>
<?php businessForm(); ?>
</div>

<div class='content'>
<h3>Location</h3>
<?php locationForm(); ?>
</div>

<div class='content'>
<h3>Contact</h3>
<?php contactForm(); ?>
<input type="submit" value="Submit"/>
</div>
</form>
</section>
</body>
</html>