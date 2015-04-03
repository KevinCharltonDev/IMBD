<?php
function editRequests($conn) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$results = array();
	
	$sql = "SELECT UPDATE_PERMISSIONS.Sp_Id, UPDATE_PERMISSIONS.AccountEmail, `Comment`, `Name` " .
	"FROM UPDATE_PERMISSIONS " .
	"JOIN SERVICE_PROVIDER " .
	"ON UPDATE_PERMISSIONS.Sp_Id = SERVICE_PROVIDER.Sp_Id " .
	"WHERE HasPermission = 0";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->execute();
		$stmt->bind_result($sp_id, $email, $comment, $name);
		
		$results = array();
		while($stmt->fetch()) {
			$resultsArray = array("Sp_Id" => $sp_id, "Email" => $email, "Comment" => $comment, "Name" => $name);
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
?>