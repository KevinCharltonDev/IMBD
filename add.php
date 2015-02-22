<?php
session_start();

require 'query/add_service_provider.php';
require 'print_error.php';
require 'functions.php';

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
	
	$add = add($name, $type, $description, $websites, $_SESSION['Email']);
	
	// If successful, redirect to business page
	if(isset($add["Success"])) {
		$id = $add["Id"];
		redirect("listing.php?id={$id}");
		exit;
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
</head>
<body>
<h1>Indiana Music Business Directory</h1>
<?php require 'header.php'; ?>
<section>
<?php
echo "<div class='content'>\n";
echo "<form action='add.php' method='POST'>\n";

$nameRow = tr(td("Name: "), td("<input type='text' name='name' maxlength='60'>"));

$typeDropDown = select('type',
	option("Individual", 0, 2),
	option("Group", 1, 2),
	option("Business", 2, 2),
	option("Organization", 3, 2));
$typeRow = tr(td("Type: "), td($typeDropDown));

$descriptionRow = tr(td("Description: "), td(textarea('description', 75, 6, 255, "wide", "")));

$websitesRow = tr(td("Websites: "), td(textarea('websites', 75, 6, 2000, "wide", "")));

$submitRow = tr(td("<input type='submit' value='Submit'>"), td(""));

echo table($nameRow, $typeRow, $descriptionRow, $websitesRow, $submitRow);
echo "</form>\n";
echo "</div>\n";

// If add was unsuccessful, an error will be printed below.
if(!is_null($add) and isset($add["Error"])) {
	printErrorFromCode($add["Code"]);
}
?>
</section>
</body>
</html>