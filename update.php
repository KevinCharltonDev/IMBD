<?php
session_start();

require 'query/look_up_query.php';
require 'query/update_listing.php';
require 'functions.php';
require 'connect/config.php';
require 'listStates.php';

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

if(isset($_POST['name'], $_POST['type'], $_POST['description'], $_POST['websites']) and $hasPermission === true) {
	$name = $_POST['name'];
	$type = (int) $_POST['type'];
	$description = $_POST['description'];
	$websites = websitesFromString($_POST['websites']);
	
	$update = update($conn, $id, $name, $type, $description, $websites);
	
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
<title>IMBD
<?php
if(!isset($results["Error"])) {
	echo " - " . htmlspecialchars($results["Data"]["Name"]);
}
?>
</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
</head>
<body>
<h1>Indiana Music Business Directory</h1>
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
	$type = spTypeToString($results["Data"]["Type"]);
	$description = htmlspecialchars($results["Data"]["Description"]);
	$websites = "";
	
	foreach($results["Data"]["Websites"] as $website) {
		// &#13;&#10; is a line feed followed by a carriage return in html for the textarea
		$websites .= htmlspecialchars($website) . '&#13;&#10;';
	}

	echo "<h2>{$name}</h2>\n";
	echo "<div class='content'>\n";
	echo "<p><a href='listing.php?id={$id}'>Back</a></p>";
	echo "<form action='update.php?id={$id}' method='POST'>\n";
	
	// Business Table
	$table = new HTMLTable();
	
	$nameInput = HTMLTag::create("input", true, true)->attribute("type", "text")->attribute("name", "name")->
		attribute("value", $name)->attribute("maxlength", "60");
		
	$table->cell("Name: ")->cell($nameInput->html())->nextRow();
	
	$selectedValue = $results["Data"]["Type"];
	$typeDropDown = new HTMLDropDown("type");
	$typeDropDown->selectedValue($selectedValue)->option("Individual", 0)->option("Group", 1)->
		option("Business", 2)->option("Organization", 3);
	
	$table->cell("Type: ")->cell($typeDropDown->html())->nextRow();
	
	$descriptionTextArea = HTMLTag::create("textarea")->attribute("name", "description")->attribute("cols", "75")->
		attribute("rows", "6")->attribute("maxlength", "255")->innerHTML($description);
	
	$table->cell("Description: ")->cell($descriptionTextArea->html())->nextRow();
	
	$websiteTextArea = HTMLTag::create("textarea")->attribute("name", "websites")->attribute("cols", "75")->
		attribute("rows", "6")->attribute("maxlength", "2000")->innerHTML($websites);
		
	$table->cell("Websites: ")->cell($websiteTextArea->html())->nextRow();
	
	$table->cell('<input type="submit" value="Submit">')->cell("");
	
	echo $table->html();
	
	// Location Tables
	for($i = 0; $i < sizeof($results["Locations"]); $i++){
		echo "<div class='content'>\n";
		echo "<h3>Location</h3>\n";
		$address1 = htmlspecialchars($results["Locations"][$i]["Address1"]);
		$address2 = htmlspecialchars($results["Locations"][$i]["Address2"]);
		$city = htmlspecialchars($results["Locations"][$i]["City"]);
		$zip = htmlspecialchars($results["Locations"][$i]["Zip"]);
		
		$table = new HTMLTable();
			
		$address1TextArea = HTMLTag::create("textarea")->attribute("name", "address1")->attribute("maxlength", "60")->innerHTML($address1)->attribute("placeholder", "This box is required to add a location.");
		$table->cell("Address 1: ")->cell($address1TextArea->html())->nextRow();

		$address2TextArea = HTMLTag::create("textarea")->attribute("name", "address2")->attribute("maxlength", "60")->innerHTML($address2);
		$table->cell("Address 2: ")->cell($address2TextArea->html())->nextRow();

		$cityInput = HTMLTag::create("input", true, true)->attribute("type", "text")->attribute("name", "city")->attribute("maxlength", "30")->attribute("value", $city);
		$table->cell("City: ")->cell($cityInput->html())->nextRow();
		
		$table->cell("State: ")->cell(stateList())->nextRow();

		$zipInput = HTMLTag::create("input", true, true)->attribute("name", "zip")->attribute("maxlength", "5")->attribute("value", $zip);
		$table->cell("Zip code: ")->cell($zipInput->html());

		echo $table->html();

		// Contact Tables in locations.
		for($j = 0; $j < sizeof($results["Locations"][$i]["Contacts"]); $j++){
			echo "<div class='content'>\n";
			echo "<h3>Contact</h3>\n";
			$fname = htmlspecialchars($results["Locations"][$i]["Contacts"][$j]["First"]);
			$lname = htmlspecialchars($results["Locations"][$i]["Contacts"][$j]["Last"]);
			$email = htmlspecialchars($results["Locations"][$i]["Contacts"][$j]["Email"]);
			$jobtitle = htmlspecialchars($results["Locations"][$i]["Contacts"][$j]["Job"]);
			$phone = htmlspecialchars($results["Locations"][$i]["Contacts"][$j]["Phone"]);
			$extension = htmlspecialchars($results["Locations"][$i]["Contacts"][$j]["Extension"]);

			$table = new HTMLTable();
			
			$table->cell("Location: ")->cell($address1)->nextRow();

			$fnameTextArea = HTMLTag::create("textarea")->attribute("name", "fname")->attribute("maxlength", "25")->attribute("placeholder", "This box is required to add a contact.")->innerHTML($fname);
			$table->cell("First Name: ")->cell($fnameTextArea->html())->nextRow();

			$lnameTextArea = HTMLTag::create("input", true, true)->attribute("type", "text")->attribute("name", "lname")->attribute("maxlength", "40")->attribute("value", $lname);
			$table->cell("Last Name: ")->cell($lnameTextArea->html())->nextRow();

			$emailTextArea = HTMLTag::create("input", true, true)->attribute("type", "text")->attribute("name", "email")->attribute("maxlength", "60")->attribute("value", $email);
			$table->cell("Email: ")->cell($emailTextArea->html())->nextRow();

			$jobTitleTextArea = HTMLTag::create("input", true, true)->attribute("type", "text")->attribute("name", "jobtitle")->attribute("maxlength", "30")->attribute("value", $jobtitle);
			$table->cell("Job title: ")->cell($jobTitleTextArea->html())->nextRow();

			$phoneInput = HTMLTag::create("input", true, true)->attribute("type", "text")->attribute("name", "phone")->attribute("maxlength", "11")->attribute("value", $phone);
			$table->cell("Phone Number: ")->cell($phoneInput->html())->nextRow();

			$extensionInput = HTMLTag::create("input", true, true)->attribute("type", "text")->attribute("name", "extension")->attribute("maxlength", "4")->attribute("value", $extension);
			$table->cell("Extension: ")->cell($extensionInput->html())->nextRow();
			
			echo $table->html();
			echo "<a href='linkcontact.php?id={$id}&fname={$fname}&lname={$lname}'>Link this contact to a location.</a>";
			echo "</div>\n";
			}
		echo "</div>\n";
	}
	
	//Contact Tables without locations
	for($j = 0; $j < sizeof($results["Contacts"]); $j++){
		echo "<div class='content'>\n";
		echo "<h3>Contact</h3>\n";
		$fname = htmlspecialchars($results["Contacts"][$j]["First"]);
		$lname = htmlspecialchars($results["Contacts"][$j]["Last"]);
		$email = htmlspecialchars($results["Contacts"][$j]["Email"]);
		$jobtitle = htmlspecialchars($results["Contacts"][$j]["Job"]);
		$phone = htmlspecialchars($results["Contacts"][$j]["Phone"]);
		$extension = htmlspecialchars($results["Contacts"][$j]["Extension"]);

		$table = new HTMLTable();

		$fnameTextArea = HTMLTag::create("textarea")->attribute("name", "fname")->attribute("maxlength", "25")->attribute("placeholder", "This box is required to add a contact.")->innerHTML($fname);
		$table->cell("First Name: ")->cell($fnameTextArea->html())->nextRow();

		$lnameTextArea = HTMLTag::create("input", true, true)->attribute("type", "text")->attribute("name", "lname")->attribute("maxlength", "40")->attribute("value", $lname);
		$table->cell("Last Name: ")->cell($lnameTextArea->html())->nextRow();

		$emailTextArea = HTMLTag::create("input", true, true)->attribute("type", "text")->attribute("name", "email")->attribute("maxlength", "60")->attribute("value", $email);
		$table->cell("Email: ")->cell($emailTextArea->html())->nextRow();

		$jobTitleTextArea = HTMLTag::create("input", true, true)->attribute("type", "text")->attribute("name", "jobtitle")->attribute("maxlength", "30")->attribute("value", $jobtitle);
		$table->cell("Job title: ")->cell($jobTitleTextArea->html())->nextRow();

		$phoneInput = HTMLTag::create("input", true, true)->attribute("type", "text")->attribute("name", "phone")->attribute("maxlength", "11")->attribute("value", $phone);
		$table->cell("Phone Number: ")->cell($phoneInput->html())->nextRow();

		$extensionInput = HTMLTag::create("input", true, true)->attribute("type", "text")->attribute("name", "extension")->attribute("maxlength", "4")->attribute("value", $extension);
		$table->cell("Extension: ")->cell($extensionInput->html())->nextRow();
		
		
		echo $table->html();
		echo "<a href='linkcontact.php?id={$id}&fname={$fname}&lname={$lname}'>Link this contact to a location.</a>";
		echo "</div>\n";
	}
	
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