<?php
define("ERROR1", "The connection strings could not be found.");
define("ERROR2", "Could not connect to database");
define("ERROR3", "The SQL statement could not be prepared.");
define("ERROR4", "No listing was found for this id.");

function getErrorArray($code) {
	return array("Error" => constant("ERROR" . $code), "Code" => $code);
}
?>