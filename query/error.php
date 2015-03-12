<?php
// Error Codes
define("COULD_NOT_CONNECT", 1);
define("SQL_PREPARE_FAILED", 2);
define("NOT_FOUND", 3);
define("NO_PERMISSION", 4);
define("DUPLICATE_KEY", 5);
define("ACCOUNT_INVALID", 6);

// Success Codes
define("UPDATE_SUCCESS", 1);
define("INSERT_SUCCESS", 2);
define("DELETE_SUCCESS", 3);

// Error Messages
define("COULD_NOT_CONNECT_MESSAGE", "A connection to the database could not be established.  Please try again later.");
define("SQL_PREPARE_FAILED_MESSAGE", "There was a problem after connecting to the database.  Please try again.");

function error($code, $message) {
	return array("Error" => true, "Code" => $code, "Message" => $message);
}

function success($code, $message) {
	return array("Success" => true, "Code" => $code, "Message" => $message);
}
?>