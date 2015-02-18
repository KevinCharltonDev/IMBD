<?php
function printErrorFromCode($code, $link = 'index.php') {
	// Connection Error
	if(in_array($code, array(1, 2, 3), true)) {
		printErrorMessage("Could not connect to the database at this time.  Please click <a href='{$link}'>here</a> to continue.");
	}
	// No search results found or ID was not found
	else if(in_array($code, array(4, 5), true)) {
		printErrorMessage("No results were found. Please click <a href='{$link}'>here</a> to go back to the home page.");
	}
	else if($code === 6) {
		printErrorMessage("You do not have permission to update this. Please click <a href='{$link}'>here</a> to continue.");
	}
	else if($code === 7) {
		printErrorMessage("Username or password is incorrect.");
	}
	// Update or insert fails because data is already in database
	else if($code === 8) {
		printErrorMessage("The name you entered has already been taken.");
	}
	else if($code === 9) {
		printErrorMessage("You have already entered a contact with that name.");
	}
	else if($code === 10) {
		printErrorMessage("You have already entered a location with that address.");
	}
}

function printErrorMessage($text) {
	echo "<div class='content'>\n";
	echo "<p>{$text}</p>\n";
	echo "</div>\n";
}
?>