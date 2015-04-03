<?php
session_start();

require 'query/add_query.php';
require 'php/functions.php';
require 'php/data.php';
require 'connect/config.php';

// Redirect to login page if not logged in
if(!isset($_SESSION['Email'])) {
	$_SESSION['Redirect'] = "add.php";
	redirect("login.php");
	exit;
}

$business = isset($_SESSION['Business']) ? $_SESSION['Business'] : defaultBusiness();
$location = isset($_SESSION['Location']) ? $_SESSION['Location'] : defaultLocation();
$contact = isset($_SESSION['Contact']) ? $_SESSION['Contact'] : defaultContact();
unset($_SESSION['Business']);
unset($_SESSION['Location']);
unset($_SESSION['Contact']);

$allPostSet = isPostSet('name', 'type', 'description', 'websites',
	'address1', 'address2', 'city', 'state', 'zip',
	'first', 'last', 'email', 'job', 'phone', 'extension');

if($allPostSet === true) {
	$business = businessFromPost();
	$location = locationFromPost();
	$contact = contactFromPost();
	
	$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
	$addResult = add($conn, $business, $_SESSION['Email']);
	setResult($addResult);
	
	if(isset($addResult["Success"])) {
		$id = (int) $addResult["Id"];
		$addLocationResult = null;
		$addContactResult = null;
		
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
		redirect("add.php");
		exit;
	}
	
	$conn->close();
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Add a Business</title>
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
<form action="add.php" method="POST">

<div class='content'>
<h3>Add a Business</h3>
<?php businessForm($business); ?>
</div>

<div class='content'>
<h3>Contact</h3>
<?php contactForm($contact); ?>
</div>

<div class='content'>
<h3>Location</h3>
<?php locationForm($location); ?>
<input type="submit" value="Submit"/>
</div>

</form>
</section>
</body>
</html>