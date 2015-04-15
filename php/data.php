<?php

function businessTypeString($type) {
	if($type == 0) {
		return "Individual";
	}
	else if($type == 1) {
		return "Group";
	}
	else if($type == 2) {
		return "Business";
	}
	else if($type == 3) {
		return "Organization";
	}
	else {
		return "";
	}
}

function accountTypeString($type) {
	if($type == 0) {
		return "Normal Account";
	}
	else if($type == 1) {
		return "Student Account";
	}
	else if($type == 2) {
		return "Moderator Account";
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

function safeLink($link) {
	$href = htmlspecialchars($link);
	$javascript = "return confirmClick(&quot;{$href}&quot;);";
	if(!startsWith($href, 'http'))
		$href = 'http://' . $href;
	
	return HTMLTag::create("a", false, true)->
		attribute("href", $href)->
		attribute("target", "_blank")->
		attribute("onclick", $javascript)->
		innerHTML(htmlspecialchars($link))->
		html() . "<br/>\n";
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

function servicesFromPost() {
	$services = array();
	foreach($_POST['services'] as $name) {
		$services[] = array('Name' => $name);
	}
	
	return $services;
}

function printNotEmpty($s) {
	if(trim($s) != '') {
		echo htmlspecialchars($s);
		echo "<br>\n";
	}
}

function businessForm($business, $allServices, $selectedServices) {
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
	echo "<br>\n";
	
	$table = new HTMLTable();
	$table->setClass("services");
	$count = 0;
	foreach($allServices as $service) {
		$input = HTMLTag::create("input", true, true)->
			attribute("type", "checkbox")->
			attribute("name", "services[]")->
			attribute("value", htmlspecialchars($service['Name']));
			
		foreach($selectedServices as $selected) {
			if($service['Name'] === $selected['Name']) {
				$input->attribute("checked", "checked");
			}
		}
			
		$table->
			cell($input->html() . htmlspecialchars($service['Name']));
			
		if($count % 3 == 2)
			$table->nextRow();
		$count++;
	}
	
	echo $table->html();
}

function locationForm($location) {
	$address1TextArea = HTMLTag::create("input", true, true)->
		attribute("type", "text")->
		attribute("name", "address1")->
		attribute("maxlength", "60")->
		attribute("value", htmlspecialchars($location['Address1']));
		
	$address2TextArea = HTMLTag::create("input", true, true)->
		attribute("type", "text")->
		attribute("name", "address2")->
		attribute("maxlength", "60")->
		attribute("value", htmlspecialchars($location['Address2']));
		
	$cityInput = HTMLTag::create("input", true, true)->
		attribute("type", "text")->
		attribute("name", "city")->
		attribute("maxlength", "30")->
		attribute("value", htmlspecialchars($location['City']));
		
	$zipInput = HTMLTag::create("input", true, true)->
		attribute("type", "text")->
		attribute("name", "zip")->
		attribute("maxlength", "5")->
		attribute("value", htmlspecialchars($location['Zip']));
		
	$table = new HTMLTable();
		
	$table->
		cell("Address Line 1: ")->
		cell($address1TextArea->html())->
		nextRow()->
		cell("Address Line 2: ")->
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
	$fnameTextArea = HTMLTag::create("input", true, true)->
		attribute("type", "text")->
		attribute("name", "first")->
		attribute("maxlength", "25")->
		attribute("value", htmlspecialchars($contact['First']));
		
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
		attribute("maxlength", "100")->
		attribute("value", htmlspecialchars($contact['Job']));
		
	$phoneInput = HTMLTag::create("input", true, true)->
		attribute("type", "text")->
		attribute("name", "phone")->
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

function locationsForContactForm($allLocations, $selectedLocations) {
	$table = new HTMLTable();
	foreach($allLocations as $location) {
		$checkbox = HTMLTag::create("input", true, true)->
			attribute("type", "checkbox")->
			attribute("name", "locations[]")->
			attribute("value", $location['L_Id']);
			
		$selected = false;
		foreach($selectedLocations as $l_id) {
			if($location['L_Id'] == $l_id) {
				$selected = true;
				break;
			}
		}
		
		if($selected) {
			$checkbox->attribute("checked", "checked");
		}
		
		$table->cell(htmlspecialchars($location['Address1']))->cell($checkbox->html())->nextRow();
	}
	
	echo $table->html();
}

function contactsForLocationForm($allContacts, $selectedContacts) {
	$table = new HTMLTable();
	foreach($allContacts as $contact) {
		$checkbox = HTMLTag::create("input", true, true)->
			attribute("type", "checkbox")->
			attribute("name", "contacts[]")->
			attribute("value", $contact['C_Id']);
			
		$selected = false;
		foreach($selectedContacts as $c_id) {
			if($contact['C_Id'] == $c_id) {
				$selected = true;
				break;
			}
		}
		
		if($selected) {
			$checkbox->attribute("checked", "checked");
		}
		
		$table->cell(htmlspecialchars($contact['First'] . " " . $contact["Last"]))->
			cell($checkbox->html())->nextRow();
	}
	
	echo $table->html();
}

?>