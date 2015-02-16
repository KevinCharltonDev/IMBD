<?php
function spTypeToString($type) {
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
		echo "<h4>Contacts</h4>\n";
	}
	
	foreach($contacts as $contact) {
		printContact($contact);
		echo "<br>\n";
	}
	
	echo "</div>\n";
}

function printReview($review) {
	$comment = htmlspecialchars($review["Comment"]);
	$rating = htmlspecialchars($review["Rating"]);
	$date = htmlspecialchars($review["Date"]);
	$name = htmlspecialchars($review["Name"]);
	
	echo "<div>";
	echo "Comment: {$comment}<br>\n";
	echo "Rating: {$rating}<br>\n";
	echo "Date: {$date}<br>\n";
	echo "Review By: {$name}<br><br>\n";
	echo "</div>";
}

function printNotEmpty($s, $lineBreak = true) {
	if(trim($s) != '') {
		echo $s;
		if($lineBreak)
			echo "<br>\n";
	}
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
?>