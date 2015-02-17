<?php
function hasUpdatePermission($id, $email, $accountType, $fromApp = false) {
	if($accountType === 1 or $accountType === 2) {
		return true;
	}
	
	$fileName = 'connect/config.php';
	$errorFile = 'query/error.php';
	
	if($fromApp) {
		$fileName = '../connect/config.php';
		$errorFile = 'error.php';
	}
	
	require_once $errorFile;
	require_once $fileName;
	
	$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
	
	if ($conn->connect_error) {
		return getErrorArray(1);
	}
	
	$sql = "SELECT `HasPermission` FROM UPDATE_PERMISSIONS " .
	"WHERE `Sp_Id` = ? AND `AccountEmail` = ?";
	$hasPermission = false;
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('is', $id, $email);
		$stmt->execute();
		$stmt->bind_result($permission);
		$stmt->fetch();
		
		if(is_null($permission) or $permission === 0)
			$hasPermission = false;
		else
			$hasPermission = true;
		
		$stmt->close();
	}
	else {
		//Statement could not be prepared
		return getErrorArray(3);
	}
	
	$conn->close();
	return $hasPermission;
}

function updateListing($id, $name, $type, $description, $websites, $fromApp = false) {
	$fileName = 'connect/config.php';
	$errorFile = 'query/error.php';
	
	if($fromApp) {
		$fileName = '../connect/config.php';
		$errorFile = 'error.php';
	}
	
	require_once $errorFile;
	require_once $fileName;
	
	$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
	$results = array();
	
	$sql = "UPDATE SERVICE_PROVIDER SET " .
	"`Name` = ?, `Type` = ?, `Description` = ? " .
	"WHERE `Sp_Id` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('sisi', $name, $type, $description, $id);
		if($stmt->execute()) {
			//Success
			$results = getErrorArray(0);
		}
		else {
			$results = getErrorArray(5);
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