<?php
function printErrorFromCode($code) {
	if(in_array($code, array(1, 2, 3), true)) {
		printConnectionError();
		return true;
	}
	else if($code === 4) {
		printNoResultsError();
		return true;
	}
	else if($code === 5) {
		printUpdateFailedError();
		return true;
	}
	else if($code === 6) {
		printNoPermissionToChangeError();
		return true;
	}
	else if($code === 7) {
		printLoginError();
		return true;
	}
	
	return false;
}

function printConnectionError() {
	echo "<div class='content'>\n";
	echo "<p>Could not connect to the database at this time.  Please try again later.</p>\n";
	echo "</div>\n";
}

function printNoResultsError() {
	echo "<div class='content'>\n";
	echo "<p>No results were found. Please click <a href='index.php'>here</a> to go back to the home page.</p>\n";
	echo "</div>\n";
}

function printUpdateFailedError() {
	echo "<div class='content'>\n";
	echo "<p>Update failed.</p>\n";
	echo "</div>\n";
}

function printNoPermissionToChangeError() {
	echo "<div class='content'>\n";
	echo "<p>You do not have permission to update this. Please click <a href='index.php'>here</a> to go back to the home page.</p>\n";
	echo "</div>\n";
}

function printNoPermissionToViewError() {
	echo "<div class='content'>\n";
	echo "<p>You do not have permission to view this page. Please click <a href='index.php'>here</a> to go back to the home page.</p>\n";
	echo "</div>\n";
}

function printLoginError() {
	echo "<div class='content'>\n";
	echo "<p>Username or password is incorrect.</p>\n";
	echo "</div>\n";
}

function printIdNotSetError() {
	echo "<div class='content'>\n";
	echo "<p>A business has not been chosen.  Please click <a href='index.php'>here</a> to go back to the home page.</p>\n";
	echo "</div>\n";
}
?>