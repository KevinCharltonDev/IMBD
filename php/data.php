<?php

function businessTypeString($type) {
	if($type === 0) {
		return "Individual";
	}
	else if($type === 1) {
		return "Group";
	}
	else if($type === 2) {
		return "Business";
	}
	else if($type === 3) {
		return "Organization";
	}
	else {
		return "";
	}
}

function websitesFromString($websitesString) {
	return separate($websitesString, "\n");
}

function formatPhoneNumber($phone, $extension = '') {
	$formatted = '';
	
	if(strlen($phone) == 7) {
		$formatted = substr($phone, 0, 3) . '-' . substr($phone, 3);
	}
	else if(strlen($phone) == 10) {
		$formatted =  '(' . substr($phone, 0, 3) . ') ' . formatPhoneNumber(substr($phone, 3));
	}
	else if(strlen($phone) == 11) {
		$formatted = substr($phone, 0, 1) . '-' . substr($phone, 1, 3) . '-' . formatPhoneNumber(substr($phone, 4));
	}
	else {
		$formatted = $phone;
	}
	
	if(!empty($extension)) {
		$formatted .= ', ext. ' . $extension;
	}
	
	return $formatted;
}

function statesDropDown($selectedValue) {
	$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
	$sql = "SELECT * FROM STATE";
	$results = $conn->query($sql);
	
	$stateDropDown = new HTMLDropDown("state");
	$stateDropDown->selectedValue($selectedValue);
	
	while($row = $results->fetch_assoc()) {
		$stateDropDown->option($row['Name'], $row['Abbreviation']);
	}
	
	$results->close();
	$conn->close();
	return $stateDropDown->html();
}

function defaultContact() {
	return array("First" => '', "Last" => '', "Email" => '', "Job" => '', "Phone" => '', "Extension" => '');
}

function defaultLocation() {
	return array("Address1" => '', "Address2" => '', "City" => '', "State" => 'IN', "Zip" => '');
}

function defaultBusiness() {
	return array("Name" => '', "Type" => 2, "Description" => '', "Websites" => array());
}

function contactFromData($first, $last, $email, $job, $phone, $extension) {
	return array("First" => $first, "Last" => $last, "Email" => $email,
		"Job" => $job, "Phone" => $phone, "Extension" => $extension);
}

function locationFromData($address1, $address2, $city, $state, $zip) {
	return array("Address1" => $address1, "Address2" => $address2, "City" => $city,
		"State" => $state, "Zip" => $zip);
}

function businessFromData($name, $type, $description, $websites) {
	return array("Name" => $name, "Type" => $type, "Description" => $description, "Websites" => $websites);
}

function contactFromPost() {
	return array(
		"First" => $_POST['first'],
		"Last" => $_POST['last'],
		"Email" => $_POST['email'],
		"Job" => $_POST['job'],
		"Phone" => $_POST['phone'],
		"Extension" => $_POST['extension']);
}

function locationFromPost() {
	return array(
		"Address1" => $_POST['address1'],
		"Address2" => $_POST['address2'],
		"City" => $_POST['city'],
		"State" => $_POST['state'],
		"Zip" => $_POST['zip']);
}

function businessFromPost() {
	return array(
		"Name" => $_POST['name'],
		"Type" => (int) $_POST['type'],
		"Description" => $_POST['description'],
		"Websites" => websitesFromString($_POST['websites']));
}

function printContact($contact) {
	$first = htmlspecialchars($contact["First"]);
	$last = htmlspecialchars($contact["Last"]);
	$email = htmlspecialchars($contact["Email"]);
	$job = htmlspecialchars($contact["Job"]);
	$phone = htmlspecialchars(formatPhoneNumber($contact["Phone"], $contact["Extension"]));
	
	echo "<div>\n";
	printNotEmpty($first . ' ' . $last);
	printNotEmpty($job);
	printNotEmpty($phone);
	printNotEmpty($email);
	echo "</div>\n";
}

function printLocation($location) {
	$address1 = htmlspecialchars($location["Address1"]);
	$address2 = htmlspecialchars($location["Address2"]);
	$city = htmlspecialchars($location["City"]);
	$state = htmlspecialchars($location["State"]);
	$zip = htmlspecialchars($location["Zip"]);
	
	echo "<div>\n";
	printNotEmpty($address1);
	printNotEmpty($address2);
	printNotEmpty($city . ', ' . $state . ' ' . $zip);
	
	$contacts = $location["Contacts"];
	if(count($contacts) > 0) {
		echo "<h4>Contacts for this location</h4>\n";
	}
	
	foreach($contacts as $contact) {
		echo $contact["Name"];
		echo "<br/>\n";
	}
	
	echo "</div>\n";
}

function printReview($review) {
	$comment = htmlspecialchars($review["Comment"]);
	$rating = htmlspecialchars($review["Rating"]);
	$date = htmlspecialchars($review["Date"]);
	$name = htmlspecialchars($review["Name"]);
	
	static $count = 0;
	$count++;
	
	echo "<div class='review'>";
	echo "<h4 onmousedown='toggleDisplay(\"review{$count}\")'>{$name} - {$date}</h4>\n";
	echo "<div id='review{$count}'>\n<hr>";
	echo "<noscript>{$rating} / 5</noscript>\n";
	echo "<script type='text/javascript'>\n";
	echo "var stars = new Stars(\"star{$count}\", 5, {$rating}, false);\n";
	echo "stars.printStars();\n";
	echo "</script>\n";
	echo "<br>\n";
	echo "<p>{$comment}</p>\n";
	echo "<input type='hidden' name='report' value='{$name}'>\n";
	echo "<input type='submit' value='Report review.'>\n";
	echo "</div>\n";
	echo "</div>\n";
}

function printMyReview($review) {
	$comment = htmlspecialchars($review["Comment"]);
	$rating = htmlspecialchars($review["Rating"]);
	$date = htmlspecialchars($review["Date"]);
	$name = htmlspecialchars($review["Name"]);
	
	echo "<div class='review'>";
	echo "<h4 onmousedown='toggleDisplay(\"myreview\")'>{$name} - {$date}</h4>\n";
	echo "<div id='myreview'>\n<hr>";
	echo "<noscript>{$rating} / 5</noscript>\n";
	echo "<script type='text/javascript'>\n";
	echo "var stars = new Stars(\"mystars\", 5, {$rating}, false);\n";
	echo "stars.printStars();\n";
	echo "</script>\n";
	echo "<br>\n";
	echo "<p>{$comment}</p>\n";
	echo "<input type='hidden' name='delete' value='review'>\n";
	echo "<input type='submit' value='Delete my review.'>\n";
	echo "</div>\n";
	echo "</div>\n";
}

function printNotEmpty($s, $lineBreak = true) {
	if(trim($s) != '') {
		echo $s;
		if($lineBreak)
			echo "<br>\n";
	}
}

function businessForm($business) {
	$nameInput = HTMLTag::create("input", true, true)->
		attribute("type", "text")->
		attribute("name", "name")->
		attribute("maxlength", "60")->
		attribute("value", htmlspecialchars($business['Name']));
		
	$typeDropDown = new HTMLDropDown("type");
	$typeDropDown->
		selectedValue($business['Type'])->
		option("Individual", 0)->
		option("Group", 1)->
		option("Business", 2)->
		option("Organization", 3);
		
	$descriptionTextArea = HTMLTag::create("textarea")->
		attribute("name", "description")->
		attribute("cols", "75")->
		attribute("rows", "6")->
		attribute("maxlength", "1000")->
		innerHTML(htmlspecialchars($business['Description']));
		
	$websiteString = "";
	foreach($business['Websites'] as $website) {
		// &#13;&#10; is a line feed followed by a carriage return in html for the textarea
		$websiteString .= htmlspecialchars($website) . '&#13;&#10;';
	}
	
	$websiteTextArea = HTMLTag::create("textarea")->
		attribute("name", "websites")->
		attribute("cols", "75")->
		attribute("rows", "6")->
		attribute("maxlength", "2000")->
		innerHTML($websiteString);
		
	$table = new HTMLTable();
		
	$table->
		cell("Name: ")->
		cell($nameInput->html())->
		nextRow()->
		cell("Type: ")->
		cell($typeDropDown->html())->
		nextRow()->
		cell("Description: ")->
		cell($descriptionTextArea->html())->
		nextRow()->
		cell("Websites: ")->
		cell($websiteTextArea->html());

	echo $table->html();
}

function locationForm($location) {
	$address1TextArea = HTMLTag::create("textarea")->
		attribute("name", "address1")->
		attribute("maxlength", "60")->
		attribute("placeholder", "This box is required to add a location.")->
		innerHTML(htmlspecialchars($location['Address1']));
		
	$address2TextArea = HTMLTag::create("textarea")->
		attribute("name", "address2")->
		attribute("maxlength", "60")->
		innerHTML(htmlspecialchars($location['Address2']));
		
	$cityInput = HTMLTag::create("input", true, true)->
		attribute("type", "text")->
		attribute("name", "city")->
		attribute("maxlength", "30")->
		attribute("value", htmlspecialchars($location['City']));
		
	$zipInput = HTMLTag::create("input", true, true)->
		attribute("name", "zip")->
		attribute("maxlength", "5")->
		attribute("value", htmlspecialchars($location['Zip']));
		
	$table = new HTMLTable();
		
	$table->
		cell("Address 1: ")->
		cell($address1TextArea->html())->
		nextRow()->
		cell("Address 2: ")->
		cell($address2TextArea->html())->
		nextRow()->
		cell("City: ")->
		cell($cityInput->html())->
		nextRow()->
		cell("State: ")->
		cell(statesDropDown($location['State']))->
		nextRow()->
		cell("Zip code: ")->
		cell($zipInput->html())->
		nextRow();

	echo $table->html();
}

function contactForm($contact) {
	$fnameTextArea = HTMLTag::create("textarea")->
		attribute("name", "first")->
		attribute("maxlength", "25")->
		attribute("placeholder", "This box is required to add a contact.")->
		innerHTML(htmlspecialchars($contact['First']));
		
	$lnameTextArea = HTMLTag::create("input", true, true)->
		attribute("type", "text")->
		attribute("name", "last")->
		attribute("maxlength", "40")->
		attribute("value", htmlspecialchars($contact['Last']));
		
	$emailTextArea = HTMLTag::create("input", true, true)->
		attribute("type", "text")->
		attribute("name", "email")->
		attribute("maxlength", "60")->
		attribute("value", htmlspecialchars($contact['Email']));
		
	$jobTitleTextArea = HTMLTag::create("input", true, true)->
		attribute("type", "text")->
		attribute("name", "job")->
		attribute("maxlength", "30")->
		attribute("value", htmlspecialchars($contact['Job']));
		
	$phoneInput = HTMLTag::create("input", true, true)->
		attribute("type", "text")->
		attribute("name", "phone")->
		attribute("maxlength", "11")->
		attribute("value", htmlspecialchars($contact['Phone']));
		
	$extensionInput = HTMLTag::create("input", true, true)->
		attribute("type", "text")->
		attribute("name", "extension")->
		attribute("maxlength", "4")->
		attribute("value", htmlspecialchars($contact['Extension']));
		
	$table = new HTMLTable();
		
	$table->
		cell("First Name: ")->
		cell($fnameTextArea->html())->
		nextRow()->
		cell("Last Name: ")->
		cell($lnameTextArea->html())->
		nextRow()->
		cell("Email: ")->
		cell($emailTextArea->html())->
		nextRow()->
		cell("Job title: ")->
		cell($jobTitleTextArea->html())->
		nextRow()->
		cell("Phone Number: ")->
		cell($phoneInput->html())->
		nextRow()->
		cell("Extension: ")->
		cell($extensionInput->html())->
		nextRow();

	echo $table->html();
}

?>