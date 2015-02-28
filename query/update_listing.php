<?php
function hasUpdatePermission($conn, $id, $email, $accountType) {
	if($accountType === 1 or $accountType === 2) {
		return true;
	}
	
	require_once "query/error.php";
	
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
	
	return $hasPermission;
}

function update($conn, $id, $name, $type, $description, $websites) {
	require_once "query/error.php";
	
	if ($conn->connect_error) {
		return getErrorArray(1);
	}
	
	$results = getSuccessArray(1);
	
	$sql = "UPDATE SERVICE_PROVIDER SET " .
	"`Name` = ?, `Type` = ?, `Description` = ? " .
	"WHERE `Sp_Id` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('sisi', $name, $type, $description, $id);
		if(!$stmt->execute()) {
			$results = getErrorArray(8);
		}
		
		$stmt->close();
	}
	else {
		//Statement could not be prepared
		$results = getErrorArray(3);
	}
	
	$sql = "DELETE FROM WEBSITE WHERE `Sp_Id` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('i', $id);
		$stmt->execute();
		$stmt->close();
	}
	else {
		//Statement could not be prepared
		$results = getErrorArray(3);
	}
	
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
		$results = getErrorArray(3);
	}
	
	return $results;
}
?>