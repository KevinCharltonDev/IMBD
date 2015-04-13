<?php
function editRequests($conn) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$results = array();
	
	$sql = "SELECT UPDATE_PERMISSIONS.Sp_Id, UPDATE_PERMISSIONS.AccountEmail, `Comment`, `Name`, `Type` " .
	"FROM UPDATE_PERMISSIONS " .
	"JOIN SERVICE_PROVIDER " .
	"ON UPDATE_PERMISSIONS.Sp_Id = SERVICE_PROVIDER.Sp_Id " .
	"WHERE HasPermission = 0";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->execute();
		$stmt->bind_result($sp_id, $email, $comment, $name, $type);
		
		$results = array();
		while($stmt->fetch()) {
			$resultsArray = array("Sp_Id" => $sp_id, "Email" => $email, "Comment" => $comment, "Name" => $name, "Type" => $type);
			array_push($results, $resultsArray);
		}
		
		$stmt->close();
		return $results;
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function grantPermission($conn, $email, $id) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "UPDATE UPDATE_PERMISSIONS " .
		"SET HasPermission = 1 " .
		"WHERE AccountEmail = ? AND Sp_Id = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('si', $email, $id);
		$stmt->execute();
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return success(UPDATE_SUCCESS, "The account now has permission to update the service provider.");
}

function denyPermission($conn, $email, $id) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "DELETE FROM UPDATE_PERMISSIONS " .
		"WHERE AccountEmail = ? AND Sp_Id = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('si', $email, $id);
		$stmt->execute();
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return success(UPDATE_SUCCESS, "The request to update the service provider was successfully denied.");
}

function reportBusiness($conn, $id) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "UPDATE SERVICE_PROVIDER " .
		"SET `IsFlagged` = 1 " .
		"WHERE `Sp_Id` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('s', $id);
		$stmt->execute();
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return success(UPDATE_SUCCESS, "The business has been flagged.  Thank you.");
}

function flaggedBusinesses($conn) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$results = array();
	
	$sql = "SELECT `Sp_Id`, `Name`, `Type`, `Description`, `AccountEmail` FROM SERVICE_PROVIDER " .
		"WHERE `IsFlagged` = 1 AND `IsSuspended` = 0";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->execute();
		$stmt->bind_result($sp_id, $name, $type, $description, $email);
		
		
		$results = array();
		while($stmt->fetch()) {
			$resultsArray = array("Sp_Id" => $sp_id, "Name" => $name, "Type" => $type, "Description" => $description, "Email" => $email);
			array_push($results, $resultsArray);
		}
		
		$stmt->close();
		return $results;
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function suspendedBusinesses($conn) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$results = array();
	
	$sql = "SELECT `Sp_Id`, `Name`, `Type`, `Description`, `AccountEmail` FROM SERVICE_PROVIDER " .
		"WHERE `IsSuspended` = 1";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->execute();
		$stmt->bind_result($sp_id, $name, $type, $description, $email);
		
		
		$results = array();
		while($stmt->fetch()) {
			$resultsArray = array("Sp_Id" => $sp_id, "Name" => $name, "Type" => $type, "Description" => $description, "Email" => $email);
			array_push($results, $resultsArray);
		}
		
		$stmt->close();
		return $results;
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
}

function suspendBusiness($conn, $id) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "UPDATE SERVICE_PROVIDER " .
		"SET `IsFlagged` = 0, `IsSuspended` = 1 " .
		"WHERE `Sp_Id` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('i', $id);
		$stmt->execute();
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return success(UPDATE_SUCCESS, "The business has been suspended.  Thank you.");
}

function validateBusiness($conn, $id) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "UPDATE SERVICE_PROVIDER " .
		"SET `IsFlagged` = 0, `IsSuspended` = 0 " .
		"WHERE `Sp_Id` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('i', $id);
		$stmt->execute();
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return success(UPDATE_SUCCESS, "The business has been validated.  Thank you.");
}
?>