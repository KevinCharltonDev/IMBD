<?php
function addListing($name, $type, $description, $accountEmail) {
	$fileName = 'connect/config.php';
	$errorFile = 'query/error.php';
	
	if($isPost) {
		$fileName = '../connect/config.php';
		$errorFile = 'error.php';
	}
	
	require_once $errorFile;
	require_once $fileName;
	
	$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
	$results = getSuccessArray(2);

	if ($conn->connect_error) {
		return getErrorArray(1);
	}
	
	$sql = "INSERT INTO SERVICE_PROVIDER " .
		"(`Name`,`Type`,`Description`,`AccountEmail`,`IsFlagged`,`IsSuspended`) " .
		"VALUES (?, ?, ?, ?, 1, 0)";

	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('siss', $name, $type, $description, $accountEmail);
		
		if(!$stmt->execute()) {
			$results = getErrorArray(8);
		}

		$stmt->close();
	}
	else {
		//Statement could not be prepared
		$results = getErrorArray(3);
	}
	
	$conn->close();
	return $results;
}
?>