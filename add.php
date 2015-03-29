<?php
session_start();

require 'query/add_query.php';
require 'functions.php';
require 'connect/config.php';

// Redirect to login page if not logged in
if(!isset($_SESSION['Email'])) {
	redirect("login.php");
	exit;
}

$business = array(
	"Name" => '',
	"Type" => 2,
	"Description" => '',
	"Websites" => array());
	
$location = array(
	"Address1" => '',
	"Address2" => '',
	"City" => '',
	"State" => 'IN',
	"Zip" => '');

$contact = array(
	"First" => '',
	"Last" => '',
	"Email" => '',
	"Job" => '',
	"Phone" => '',
	"Extension" => '');
	
if(isset($_SESSION['Business'])) {
	$business = $_SESSION['Business'];
	unset($_SESSION['Business']);
}
if(isset($_SESSION['Contact'])) {
	$contact = $_SESSION['Contact'];
	unset($_SESSION['Contact']);
}
if(isset($_SESSION['Location'])) {
	$location = $_SESSION['Location'];
	unset($_SESSION['Location']);
}

$allPostSet = isPostSet('name', 'type', 'description', 'websites',
	'address1', 'address2', 'city', 'state', 'zip',
	'first', 'last', 'email', 'job', 'phone', 'extension');

if($allPostSet === true) {
	$business = array(
		"Name" => $_POST['name'],
		"Type" => (int) $_POST['type'],
		"Description" => $_POST['description'],
		"Websites" => websitesFromString($_POST['websites']));
		
	$location = array(
		"Address1" => $_POST['address1'],
		"Address2" => $_POST['address2'],
		"City" => $_POST['city'],
		"State" => $_POST['state'],
		"Zip" => $_POST['zip']);
		
	$contact = array(
		"First" => $_POST['first'],
		"Last" => $_POST['last'],
		"Email" => $_POST['email'],
		"Job" => $_POST['job'],
		"Phone" => $_POST['phone'],
		"Extension" => $_POST['extension']);
	
	$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
	$addResult = add($conn,
		$business['Name'],
		$business['Type'],
		$business['Description'],
		$business['Websites'],
		$_SESSION['Email']);
	setResult($addResult);
	
	if(isset($addResult["Success"])) {
		$id = (int) $addResult["Id"];
		$addLocationResult = null;
		$addContactResult = null;
		
		if(trim($location['Address1']) != '') {
			$addLocationResult = addLocation($conn, 
				$location['Address1'],
				$location['Address2'],
				$location['City'],
				$location['State'],
				$location['Zip'], $id);
		}
		if(trim($contact['First']) != '') {
			$addContactResult = addContact($conn,
				$contact['First'],
				$contact['Last'],
				$contact['Email'],
				$contact['Job'],
				$contact['Phone'],
				$contact['Extension'], $id);
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
</head>
<body>
<?php require 'header.php';?>
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
<?php businessForm($business['Name'], $business['Type'], $business['Description'], $business['Websites']); ?>
</div>

<div class='content'>
<h3>Contact</h3>
<?php contactForm($contact["First"], $contact["Last"], $contact["Email"], $contact["Job"], $contact["Phone"], $contact["Extension"]); ?>
</div>

<div class='content'>
<h3>Location</h3>
<?php locationForm($location['Address1'], $location['Address2'], $location['City'], $location['State'], $location['Zip']); ?>
<input type="submit" value="Submit"/>
</div>

</form>
</section>
</body>
</html>