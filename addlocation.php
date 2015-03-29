<?php
session_start();

require 'query/add_query.php';
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

$id = (int) $_GET['id'];
$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);

// User must have update permission to view this page
$hasPermission = hasUpdatePermission($conn, $id, $_SESSION['Email'], $_SESSION["Type"]);
if($hasPermission !== true) {
	redirect("listing.php?id={$id}");
	exit;
}

$location = array(
	"Address1" => '',
	"Address2" => '',
	"City" => '',
	"State" => 'IN',
	"Zip" => '');
	
if(isset($_SESSION['Location'])) {
	$location = $_SESSION['Location'];
	unset($_SESSION['Location']);
}

if(isPostSet('address1', 'address2', 'city', 'state', 'zip')) {
	$location['Address1'] = $_POST['address1'];
	$location['Address2'] = $_POST['address2'];
	$location['City'] = $_POST['city'];
	$location['State'] = $_POST['state'];
	$location['Zip'] = $_POST['zip'];
		
	$addLocation = addLocation($conn,
		$location['Address1'],
		$location['Address2'],
		$location['City'],
		$location['State'],
		$location['Zip'], $id);
		
	setResult($addLocation);
	
	if(isset($addLocation['Success'])) {
		redirect("listing.php?id={$id}");
		exit;
	}
	else {
		$_SESSION['Location'] = $location;
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
<h2>Add a Location</h2>
<div class="content">
<p><a href="listing.php?id=<?php echo $id; ?>">Back</a></p>
<form action="addlocation.php?id=<?php echo $id; ?>" method="POST">
<?php locationForm($location['Address1'], $location['Address2'], $location['City'], $location['State'], $location['Zip']); ?>
<input type="submit" value="Submit"/>
</form>
</div>
</section>
</body>
</html>