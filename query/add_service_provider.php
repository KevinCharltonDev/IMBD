<?php

function add($id, $name, $type, $description, $accountEmail){
	
	$fileName = 'connect/config.php';
	$errorFile = 'query/error.php';
	
	if($isPost) {
		$fileName = '../connect/config.php';
		$errorFile = 'error.php';
	}
	
	require_once $errorFile;
	require_once $fileName;
	
	$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
	$results = array();

	if ($conn->connect_error) {
		return getErrorArray(1);
	}
	
	$sql = "INSERT INTO SERVICE_PROVIDER " .
	"('name', 'type', 'description', 'AccountEmail', 'IsFlagged', 'IsSuspended') " . 
	"VALUES (?, ?, ?, ?, 1, 0)";

	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('sis', $name, $type, $description, $accountEmail);
		$stmt->execute();

		$stmt->close();
	}
	else {
		//Statement could not be prepared
		return getErrorArray(3);
	}
	
	$conn->close();
}
?>