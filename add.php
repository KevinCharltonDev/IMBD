<?php
session_start();

require 'query/add_query.php';
require 'php/functions.php';
require 'php/data.php';
require 'connect/config.php';
require 'query/service_query.php';

// Redirect to login page if not logged in
if(!isset($_SESSION['Email'])) {
	$_SESSION['Redirect'] = "add.php";
	redirect("login.php");
	exit;
}

$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
$allServices = getAllServices($conn);

$business = isset($_SESSION['Business']) ? $_SESSION['Business'] : defaultBusiness();
$location = isset($_SESSION['Location']) ? $_SESSION['Location'] : defaultLocation();
$contact = isset($_SESSION['Contact']) ? $_SESSION['Contact'] : defaultContact();
$services = isset($_SESSION['Services']) ? $_SESSION['Services'] : array();
unset($_SESSION['Business']);
unset($_SESSION['Location']);
unset($_SESSION['Contact']);
unset($_SESSION['Services']);

$allPostSet = isPostSet('name', 'type', 'description', 'websites',
	'address1', 'address2', 'city', 'state', 'zip',
	'first', 'last', 'email', 'job', 'phone', 'extension');

if($allPostSet === true) {
	$business = businessFromPost();
	$location = locationFromPost();
	$contact = contactFromPost();
	
	if(isPostSet('services')) {
		$services = servicesFromPost();
	}
	
	$addResult = add($conn, $business, $_SESSION['Email']);
	setResult($addResult);
	
	if(isset($addResult["Success"])) {
		$id = (int) $addResult["Id"];
		$addLocationResult = null;
		$addContactResult = null;
		
		if(isPostSet('services')) {
			foreach($services as $service) {
				chooseService($conn, $service['Name'], $id);
			}
		}
		if(trim($location['Address1']) != '') {
			$addLocationResult = addLocation($conn, $location, $id);
		}
		if(trim($contact['First']) != '') {
			$addContactResult = addContact($conn, $contact, $id);
		}

		if(!is_null($addContactResult) && !is_null($addLocationResult)) {
			linkLocationContact($conn, $addLocationResult['Id'], $addContactResult['Id']);
		}
		
		redirect("listing.php?id={$id}");
		exit;
	}
	else {
		$_SESSION["Business"] = $business;
		$_SESSION["Location"] = $location;
		$_SESSION["Contact"] = $contact;
		$_SESSION["Services"] = $services;
		redirect("add.php");
		exit;
	}
}

$conn->close();

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Add a Business</title>
<link rel="icon" type="image/x-icon" href="images/favicon.ico">
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
<script src="js/functions.js"></script>
</head>
<body>
<?php require 'php/header.php';?>
<form action="add.php" method="POST">
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
<h2>Add a Business</h2>
<div class="content">
<?php
businessForm($business, $allServices, $services); 
?>
</div>
</section>
<br>
<section>
<h2>Additional Information</h2>
<div class="content">
<p>The following fields are optional.</p>
<h3>Contact</h3>
<?php contactForm($contact); ?>
<br>
<h3>Location</h3>
<?php locationForm($location); ?>
<hr>
<input type="submit" value="Submit"/>
</div>
</section>
</form>
</body>
</html>