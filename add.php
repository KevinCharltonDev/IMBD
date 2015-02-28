<?php
session_start();

require 'query/add_service_provider.php';
require 'print_error.php';
require 'functions.php';
require 'connect/config.php';

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
	$conn->close();
	
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
<link href="css/custom.css" rel="stylesheet" type="text/css">
</head>
<body>
<h1>Indiana Music Business Directory</h1>
<?php require 'header.php'; ?>
<section>
<?php
echo "<div class='content'>\n";
echo "<form action='add.php' method='POST'>\n";

$table = new HTMLTable();
	
$nameInput = HTMLTag::create("input", true, true)->attribute("type", "text")->attribute("name", "name")->
	attribute("maxlength", "60");
	
$table->cell("Name: ")->cell($nameInput->html())->nextRow();

$typeDropDown = new HTMLDropDown("type");
$typeDropDown->selectedValue(2)->option("Individual", 0)->option("Group", 1)->
	option("Business", 2)->option("Organization", 3);
	
$table->cell("Type: ")->cell($typeDropDown->html())->nextRow();

$descriptionTextArea = HTMLTag::create("textarea")->attribute("name", "description")->attribute("cols", "75")->
	attribute("rows", "6")->attribute("maxlength", "255");
	
$table->cell("Description: ")->cell($descriptionTextArea->html())->nextRow();

$websiteTextArea = HTMLTag::create("textarea")->attribute("name", "websites")->attribute("cols", "75")->
	attribute("rows", "6")->attribute("maxlength", "2000");
	
$table->cell("Websites: ")->cell($websiteTextArea->html())->nextRow();

$table->cell('<input type="submit" value="Submit">')->cell("");

echo $table->html();
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