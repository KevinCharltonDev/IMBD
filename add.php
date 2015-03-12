<?php
session_start();

require 'query/add_service_provider.php';
require 'query/add_location.php';
require 'query/add_contact.php';
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
</head>
<body>
<h1>Indiana Music Business Directory</h1>
<?php require 'header.php';?>
<section>
<?php
$error = null;
if(!is_null($add) and isset($add["Error"])) {
	printError($add["Message"]);
}

echo "<div class='content'>\n";
echo "<form action='add.php' method='POST'>\n";

$phoneLineString = 'Phone #: </td><td><input name="phone" size="9" maxlength="11" onkeypress="return event.charCode >= 48 && event.charCode <= 57"></input>' .
' Extension: <input name="extension" maxlength="4" size="2" onkeypress="return event.charCode >= 48 && event.charCode <= 57"></input>';

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

$locationStartString = HTMLTag::create("table")->innerHTML("<b>Location</b>");

$table->cell($locationStartString->html())->nextRow();
	
$address1TextArea = HTMLTag::create("textarea")->attribute("name", "address1")->attribute("maxlength", "60")->attribute("placeholder", "This box is required to add a location.");
	
$table->cell("Address 1: ")->cell($address1TextArea->html())->nextRow();

$address2TextArea = HTMLTag::create("textarea")->attribute("name", "address2")->attribute("maxlength", "60");

$table->cell("Address 2: ")->cell($address2TextArea->html())->nextRow();

$cityTextArea = HTMLTag::create("input")->attribute("name", "city")->attribute("maxlength", "30");

$table->cell("City: ")->cell($cityTextArea->html())->nextRow();

$table->cell("State: ")->cell(stateList())->nextRow();

$zipTextArea = HTMLTag::create("input")->attribute("name", "zip")->attribute("onkeypress", "return event.charCode >= 48 && event.charCode <= 57")->attribute("maxlength", "5");

$table->cell("Zip code: ")->cell($zipTextArea->html())->nextRow();

$contactStartString = HTMLTag::create("table")->innerHTML("<b>Contact</b>");

$table->cell($contactStartString->html())->nextRow();

$fnameTextArea = HTMLTag::create("textarea")->attribute("name", "fname")->attribute("maxlength", "25")->attribute("placeholder", "This box is required to add a contact.");

$table->cell("First name: ")->cell($fnameTextArea->html())->nextRow();

$lnameTextArea = HTMLTag::create("input")->attribute("name", "lname")->attribute("maxlength", "40");

$table->cell("Last name: ")->cell($lnameTextArea->html())->nextRow();

$emailTextArea = HTMLTag::create("input")->attribute("name", "email")->attribute("maxlength", "60");

$table->cell("Email: ")->cell($emailTextArea->html())->nextRow();

$jobTitleTextArea = HTMLTag::create("input")->attribute("name", "jobtitle")->attribute("maxlength", "30");

$table->cell("Job title: ")->cell($jobTitleTextArea->html())->nextRow();

$phoneTextArea = HTMLTag::create("p")->innerHTML($phoneLineString);

$table->cell($phoneTextArea->html())->nextRow();

$hiddenTextArea = HTMLTag::create("input")->attribute("name", "empty")->attribute("hidden", "");

$table->cell("")->cell($hiddenTextArea->html())->nextRow();

$table->cell('<input type="submit" value="Submit">')->cell("");

echo $table->html();
echo "</form>\n";
echo "</div>\n";
?>
</section>
</body>
</html>