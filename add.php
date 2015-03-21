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
	$locationId = 0;
	$contactId = 0;

	
	$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
	$add = add($conn, $name, $type, $description, $websites, $_SESSION['Email']);
	$conn->close();
	
	// If successful, redirect to business page
	if(isset($add["Success"])) {
		$id = $add["Id"];
		$locationSuccess = addLoc($id);
		if(isset($locationSuccess["Success"])) {
			$locationId = $locationSuccess["Id"];
		}
		$contactSuccess = addCon($id);
		if(isset($locationSuccess["Success"])) {
			$contactId = $contactSuccess["Id"];
		}
		tryLinking($locationId, $contactId);
		redirect("listing.php?id={$id}");
		exit;
	}
}

function addLoc($id){
	if(isset($_POST['city'], $_POST['state'], $_POST['zip']) and $_POST['address1']!="") {
		$address1 = $_POST['address1'];
		$address2 = $_POST['address2'];
		$city = $_POST['city'];
		$state = $_POST['state'];
		$zip = $_POST['zip'];
		$spId = (int) $id;
			
		$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
		$add = addLocation($conn, $address1, $address2, $city, $state, $zip, $spId);
		$conn->close();
		
		if(isset($add["Success"])) {
			return $add;
		}
	}
}

function addCon($id){
	if(isset($_POST['jobtitle'], $_POST['lname'], $_POST['email']) and $_POST['fname']!="") {
		$fname = $_POST['fname'];
		$lname = $_POST['lname'];
		$email = $_POST['email'];
		$jobTitle = $_POST['jobtitle'];
		$phone = $_POST['phone'];
		$extension = $_POST['extension'];
		$spId = (int) $id;

		$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
		$add = addContact($conn, $fname, $lname, $email, $jobTitle, $phone, $extension, $spId);
		$conn->close();
		
		if(isset($add["Success"])) {
			return $add;
		}
	}
}

function tryLinking($lId, $cId){
	$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
	$add = linkLocationContact($conn, $lId, $cId);
	$conn->close();
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

echo "<form action='add.php' method='POST'>\n";

// Business Table
echo "<div class='content'>\n";
echo "<h3>Add a Business</h3>\n";

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
$table->cell("Websites: ")->cell($websiteTextArea->html());

echo $table->html();
echo "</div>\n";

// Location Table
echo "<div class='content'>\n";
echo "<h3>Location</h3>\n";

$table = new HTMLTable();
	
$address1TextArea = HTMLTag::create("textarea")->attribute("name", "address1")->attribute("maxlength", "60")->attribute("placeholder", "This box is required to add a location.");
$table->cell("Address 1: ")->cell($address1TextArea->html())->nextRow();

$address2TextArea = HTMLTag::create("textarea")->attribute("name", "address2")->attribute("maxlength", "60");
$table->cell("Address 2: ")->cell($address2TextArea->html())->nextRow();

$cityInput = HTMLTag::create("input", true, true)->attribute("type", "text")->attribute("name", "city")->attribute("maxlength", "30");
$table->cell("City: ")->cell($cityInput->html())->nextRow();

$table->cell("State: ")->cell(stateList())->nextRow();

$zipInput = HTMLTag::create("input", true, true)->attribute("name", "zip")->attribute("maxlength", "5");
$table->cell("Zip code: ")->cell($zipInput->html());

echo $table->html();

echo "</div>\n";

// Contact Table
echo "<div class='content'>\n";
echo "<h3>Contact</h3>\n";

$table = new HTMLTable();

$fnameTextArea = HTMLTag::create("textarea")->attribute("name", "fname")->attribute("maxlength", "25")->attribute("placeholder", "This box is required to add a contact.");
$table->cell("First Name: ")->cell($fnameTextArea->html())->nextRow();

$lnameTextArea = HTMLTag::create("input", true, true)->attribute("type", "text")->attribute("name", "lname")->attribute("maxlength", "40");
$table->cell("Last Name: ")->cell($lnameTextArea->html())->nextRow();

$emailTextArea = HTMLTag::create("input", true, true)->attribute("type", "text")->attribute("name", "email")->attribute("maxlength", "60");
$table->cell("Email: ")->cell($emailTextArea->html())->nextRow();

$jobTitleTextArea = HTMLTag::create("input", true, true)->attribute("type", "text")->attribute("name", "jobtitle")->attribute("maxlength", "30");
$table->cell("Job title: ")->cell($jobTitleTextArea->html())->nextRow();

$phoneInput = HTMLTag::create("input", true, true)->attribute("type", "text")->attribute("name", "phone")->attribute("maxlength", "11");
$table->cell("Phone Number: ")->cell($phoneInput->html())->nextRow();

$extensionInput = HTMLTag::create("input", true, true)->attribute("type", "text")->attribute("name", "extension")->attribute("maxlength", "4");
$table->cell("Extension: ")->cell($extensionInput->html())->nextRow();

$table->cell('<input type="submit" value="Submit">')->cell('&nbsp;');

echo $table->html();
echo "</div>\n";
echo "</form>\n";
?>
</section>
</body>
</html>