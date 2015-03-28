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

// User must have update permission to view this page
$hasPermission = hasUpdatePermission($conn, $id, $_SESSION['Email'], $_SESSION["Type"]);
if($hasPermission !== true) {
	redirect("listing.php?id={$id}");
	exit;
}

$results = lookUp($conn, $id);
$update = null;

if(isset($_POST['name'], $_POST['type'], $_POST['description'], $_POST['websites'])) {
	$name = $_POST['name'];
	$type = (int) $_POST['type'];
	$description = $_POST['description'];
	$websites = websitesFromString($_POST['websites']);
	
	$update = update($conn, $id, $name, $type, $description, $websites);
	
	// If update was successful, redirect to business page
	if(isset($update["Success"])) {
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
<title>
<?php
if(!isset($results["Error"])) {
	echo htmlspecialchars($results["Data"]["Name"]);
}
else {
	echo "Indiana Music Business Directory";
}
?>
</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php require 'header.php'; ?>
<section>
<?php
// If update was unsuccessful, an error will be printed below.
if(!is_null($update) and isset($update["Error"])) {
	printError($update["Message"]);
}

// Error when connecting to database or could not find ID in database
if(isset($results['Error'])) {
	printError($results["Message"], "index.php");
}

$name = htmlspecialchars($results["Data"]["Name"]);

echo "<h2>{$name}</h2>\n";
echo "<div class='content'>\n";
echo "<p><a href='listing.php?id={$id}'>Back</a></p>";
echo "<h3>Information</h3>\n";
echo "<form action='update.php?id={$id}' method='POST'>\n";
businessForm($name, $results["Data"]["Type"], $results["Data"]["Description"], $results["Data"]["Websites"]);
echo '<input type="submit" value="Submit">';
echo "</form>\n";
echo "</div>\n";
?>
</section>
</body>
</html>