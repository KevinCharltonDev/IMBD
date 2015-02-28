<?php
function add($conn, $name, $type, $description, $websites, $accountEmail) {
	require_once 'query/error.php';
	
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
			return getErrorArray(8);
		}

		$stmt->close();
	}
	else {
		//Statement could not be prepared
		return getErrorArray(3);
	}
	
	$id = (int) $conn->insert_id;
	$results['Id'] = $id;
	
	$sql = "INSERT INTO WEBSITE (`Sp_Id`,`Url`) VALUES(?,?)";
	
	if($stmt = $conn->prepare($sql)) {
		foreach($websites as $website) {
			$stmt->bind_param('is', $id, $website);
			$stmt->execute();
		}
		
		$stmt->close();
	}
	else {
		//Statement could not be prepared
		return getErrorArray(3);
	}
	
	$sql = "INSERT INTO UPDATE_PERMISSIONS (`Sp_Id`, `AccountEmail`, `HasPermission`) VALUES (?, ?, 1)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('is', $id, $accountEmail);
		$stmt->execute();
		$stmt->close();
	}
	else {
		//Statement could not be prepared
		return getErrorArray(3);
	}
	
	return $results;
}
?>