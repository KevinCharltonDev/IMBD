<?php
define("ERROR0", "Success");
define("ERROR1", "Could not connect to the database");
define("ERROR2", "The connection strings could not be found.");
define("ERROR3", "The SQL statement could not be prepared.");
define("ERROR4", "No listing was found for this id.");
define("ERROR5", "Update could not be performed");
define("ERROR6", "This account does not have permission to perform this update.");
define("ERROR7", "Username or password is incorrect");

function getErrorArray($code) {
	return array("Error" => constant("ERROR" . $code), "Code" => $code);
}
?>