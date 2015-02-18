<?php
session_start();

require 'query/look_up_query.php';
require 'query/update_listing.php';
require 'print_error.php';
require 'functions.php';

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
$results = lookUp($id);
$hasPermission = hasUpdatePermission($id, $_SESSION['Email'], $_SESSION["Type"]);
$update = null;

if(isset($_POST['name'], $_POST['type'], $_POST['description']) and $hasPermission === true) {
	$name = $_POST['name'];
	$type = (int) $_POST['type'];
	$description = $_POST['description'];
	$update = updateListing($id, $name, $type, $description, null);
	
	// If update was successful, redirect to business page
	if(isset($update["Success"])) {
		redirect("listing.php?id={$id}");
		exit;
	}
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>IMBD
<?php
if(!isset($results["Error"])) {
	echo " - " . htmlspecialchars($results["Data"]["Name"]);
}
?>
</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
</head>
<body>
<h1>Indiana Music Business Directory</h1>
<?php require 'header.php'; ?>
<section>
<?php
// Error when connecting to database or could not find ID in database
if(isset($results['Error'])) {
	printErrorFromCode($results["Code"]);
}
// If there is an error, $hasPermission will be an error array
else if(is_array($hasPermission)) {
	printErrorFromCode($hasPermission["Code"]);
}
// User does not have permission to edit
else if(!$hasPermission) {
	printErrorFromCode(6, "listing.php?id={$id}");
}
//User has permission to edit
else {
	$name = htmlspecialchars($results["Data"]["Name"]);
	$type = spTypeToString($results["Data"]["Type"]);
	$description = htmlspecialchars($results["Data"]["Description"]);

	echo "<h2>{$name}</h2>\n";
	echo "<div class='content'>\n";
	echo "<p><a href='listing.php?id={$id}'>Back</a></p>";
	echo "<form action='update.php?id={$id}' method='POST'>\n";
	
	$nameRow = tr(td("Name: "), td("<input type='text' name='name' value='{$name}'>"));
	
	$selectedValue = $results["Data"]["Type"];
	$typeDropDown = select('type',
		option("Individual", 0, $selectedValue),
		option("Group", 1, $selectedValue),
		option("Business", 2, $selectedValue),
		option("Organization", 3, $selectedValue));
	$typeRow = tr(td("Type: "), td($typeDropDown));
	
	$descriptionRow = tr(td("Description: "), td(textarea('description', 50, 6, 255, $description)));
	
	$submitRow = tr(td("<input type='submit' value='Submit'>"), td(""));
	
	echo table($nameRow, $typeRow, $descriptionRow, $submitRow);
	echo "<input type='hidden' name='id' value='{$id}'>";
	echo "</form>\n";
	echo "</div>\n";
	
	// If update was unsuccessful, an error will be printed below.
	if(!is_null($update) and isset($update["Error"])) {
		printErrorFromCode($update["Code"]);
	}
}
?>
</section>
</body>
</html>