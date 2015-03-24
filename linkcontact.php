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
$id = (int) $_GET['id'];
$results = lookUp($conn, $id);
$hasPermission = hasUpdatePermission($conn, $id, $_SESSION['Email'], $_SESSION["Type"]);
$update = null;

if(isset($_POST['fname'],$_POST['lname'], $_POST['id'], $_POST['address1']) and $hasPermission === true) {
	$fname = $_POST['fname'];
	$lname = $_POST['lname'];
	$id = $_POST['id'];
	$address1 = $_POST['address1'];
	
	//$update = linkContact($conn, $id, $fname, $lname, $address1);
	
	// If update was successful, redirect to business page
	if(isset($update["Success"])) {
		$conn->close();
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
<title>Link Contacts to Locations</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php require 'header.php'; ?>
<section>
<?php
// Error when connecting to database or could not find ID in database
if(isset($results['Error'])) {
	printError($results["Message"], "index.php");
}
// If there is an error, $hasPermission will be an error array
else if(is_array($hasPermission)) {
	printError($hasPermission["Message"]);
}
// User does not have permission to edit
else if(!$hasPermission) {
	printError("You do not have permission to update this.", "listing.php?id={$id}");
}
//User has permission to edit
else {
	$name = htmlspecialchars($results["Data"]["Name"]);
	
	echo "<h2>{$name}</h2>\n";
	echo "<div class='content'>\n";
	echo "<p><a href='update.php?id={$id}'>Back</a></p>";
	echo "<form action='linkContact.php?id={$id}' method='POST'>\n";
	echo "<input type='text' name='fname' value = {$_GET['fname']} hidden/>\n";
	echo "<input type='text' name='lname' value = {$_GET['lname']} hidden/>\n";
	echo "<input type='text' name='id' value = {$_GET['id']} hidden/>\n";
	
	$table = new HTMLTable();
	$table->cell('<input type="submit" value="Submit">')->cell('&nbsp;');
	echo $table->html();
	echo "<input type='hidden' name='id' value='{$id}'>";
	echo "</form>\n";
	echo "</div>\n";
}
?>
</section>
</body>
</html>
