<?php
// Connection Errors
define("ERROR1", "Could not connect to the database");
define("ERROR2", "The connection strings could not be found.");
define("ERROR3", "The SQL statement could not be prepared.");

// Search Errors
define("ERROR4", "No results were found.");
define("ERROR5", "No listing was found for this id.");

// Account Errors
define("ERROR6", "This account does not have permission to perform this update.");
define("ERROR7", "Username or password is incorrect");

// Update Errors
define("ERROR8", "A business with that name is already in the database.");
define("ERROR9", "A contact with the same name is already in the database for this business.");
define("ERROR10", "A location with the same address is already in the database for this business.");


define("SUCCESS1", "Update was successful");
define("SUCCESS2", "Insert was successful");
define("SUCCESS3", "User has update permission");

function getErrorArray($code) {
	return array("Error" => constant("ERROR" . $code), "Code" => $code);
}

function getSuccessArray($code) {
	return array("Success" => constant("SUCCESS" . $code), "Code" => $code);
}
?>