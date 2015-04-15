<?php
require_once 'query/error.php';

function hasUpdatePermission($conn, $id, $email, $accountType) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
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
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	if($accountType === 1 or $accountType === 2) {
		return $hasPermission = true;
	}
	
	return $hasPermission;
}

function hasLocationUpdatePermission($conn, $l_id, $email, $accountType) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "SELECT `Sp_Id` FROM LOCATION WHERE `L_Id` = ?";
	$hasPermission = false;
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('i', $l_id);
		$stmt->execute();
		$stmt->bind_result($id);
		
		if($stmt->fetch()) {
			$stmt->close();
			$hasPermission = hasUpdatePermission($conn, $id, $email, $accountType);
		}
		
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return $hasPermission;
}

function hasContactUpdatePermission($conn, $c_id, $email, $accountType) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "SELECT `Sp_Id` FROM CONTACT WHERE `C_Id` = ?";
	$hasPermission = false;
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('i', $c_id);
		$stmt->execute();
		$stmt->bind_result($id);
		
		if($stmt->fetch()) {
			$stmt->close();
			$hasPermission = hasUpdatePermission($conn, $id, $email, $accountType);
		}
		
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return $hasPermission;
}

function update($conn, $id, $business) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$name = $business['Name'];
	$type = $business['Type'];
	$description = $business['Description'];
	$websites = $business['Websites'];
	
	if(strlen($name) > 60 || strlen($name) < 3) {
		return error(INVALID_ARGUMENTS, "The business name must be between 3 and 60 characters.");
	}
	
	if($type > 3 || $type < 0) {
		return error(INVALID_ARGUMENTS, "Invalid business type");
	}
	
	$results = success(UPDATE_SUCCESS, "The business information has been updated.");
	
	$sql = "UPDATE SERVICE_PROVIDER SET " .
	"`Name` = ?, `Type` = ?, `Description` = ? " .
	"WHERE `Sp_Id` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('sisi', $name, $type, $description, $id);
		if(!$stmt->execute()) {
			$results = error(DUPLICATE_KEY, "A business with that name is already in the directory.");
		}
		
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	$sql = "DELETE FROM WEBSITE WHERE `Sp_Id` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('i', $id);
		$stmt->execute();
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
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
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return $results;
}

function updateLocation($conn, $location, $l_id) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$address1 = $location['Address1'];
	$address2 = $location['Address2'];
	$city = $location['City'];
	$state = $location['State'];
	$zip = $location['Zip'];
	
	if(strlen($address1) < 1) {
		return error(INVALID_ARGUMENTS, "Address 1 is required");
	}
	
	$sql = "UPDATE LOCATION SET " .
		"`Address1` = ?, `Address2` = ?, `City` = ?, `State` = ?, `Zip` = ? " .
		"WHERE `L_Id` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('sssssi', $address1, $address2, $city, $state, $zip, $l_id);
		if(!$stmt->execute()) {
			return error(DUPLICATE_KEY, "The location you entered already exists for this business.");
		}
		
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return success(UPDATE_SUCCESS, "The location information has been updated.");
}

function updateContact($conn, $contact, $c_id) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$first = $contact['First'];
	$last = $contact['Last'];
	$contactEmail = $contact['Email'];
	$job = $contact['Job'];
	$phone = str_replace(array('-', '+'), '', filter_var($contact['Phone'], FILTER_SANITIZE_NUMBER_INT));
	$extension = str_replace(array('-', '+'), '', filter_var($contact['Extension'], FILTER_SANITIZE_NUMBER_INT));
	
	if(strlen($first) < 1 && strlen($last) < 1) {
		return error(INVALID_ARGUMENTS, "First or last name is required");
	}
	
	$sql = "UPDATE CONTACT SET " .
		"`Fname` = ?, `Lname` = ?, `Email` = ?, `JobTitle` = ?, `PhoneNumber` = ?, `Extension` = ? " .
		"WHERE `C_Id` = ?";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('ssssssi', $first, $last, $contactEmail, $job, $phone, $extension, $c_id);
		if(!$stmt->execute()) {
			return error(DUPLICATE_KEY, "The contact you entered already exists for this business.");
		}
		
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return success(UPDATE_SUCCESS, "The contact information has been updated.");
}
?>