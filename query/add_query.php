<?php
require_once 'query/error.php';

function add($conn, $business, $accountEmail) {
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
	
	$results = success(INSERT_SUCCESS, "A new business has been added.");
	
	$sql = "INSERT INTO SERVICE_PROVIDER " .
		"(`Name`,`Type`,`Description`,`AccountEmail`,`IsFlagged`,`IsSuspended`) " .
		"VALUES (?, ?, ?, ?, 1, 0)";

	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('siss', $name, $type, $description, $accountEmail);
		
		if(!$stmt->execute()) {
			return error(DUPLICATE_KEY, "A business with that name is already in the directory.");
		}

		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
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
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	$permission = grantPermission($conn, $id, $accountEmail, 1);
	if(isset($permission['Error'])) {
		return $permission;
	}
	
	return $results;
}

function grantPermission($conn, $id, $email, $value, $comment='') {
	$results = success(INSERT_SUCCESS, "Permission has been granted to update a business.");

	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "INSERT INTO UPDATE_PERMISSIONS (`Sp_Id`, `AccountEmail`, `HasPermission`, `Comment`) VALUES (?, ?, ?, ?)";
	
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('isis', $id, $email, $value, $comment);
		if(!$stmt->execute()) {
			return error(DUPLICATE_KEY, "You already have permission or have requested permission.");
		}
		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return $results;
}

function requestPermission($conn, $id, $email, $comment) {
	if($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$permissionValue = getPermissionValue($conn, $id, $email);
	
	if($permissionValue === 1) {
		return error(DUPLICATE_KEY, "You already have permission to update this business.");
	}
	else if($permissionValue === 0) {
		$sql = "UPDATE UPDATE_PERMISSIONS SET `Comment` = ? WHERE " .
			"`AccountEmail` = ? AND `Sp_Id` = ?";
		
		if($stmt = $conn->prepare($sql)) {
			$stmt->bind_param('ssi', $comment, $email, $id);
			$stmt->execute();
			$stmt->close();
			return success(INSERT_SUCCESS, "You have already requested permission, but your comment has been updated.");
		}
		else {
			return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
		}
	}
	else {
		grantPermission($conn, $id, $email, 0, $comment);
		return success(INSERT_SUCCESS, "Your request has been sent and will be reviewed by an administrator. Thank you.");
	}
}

function getPermissionValue($conn, $id, $email) {
	if ($conn->connect_error) {
		return -1;
	}
	
	$sql = "SELECT `HasPermission` FROM UPDATE_PERMISSIONS WHERE " .
		"`Sp_Id` = ? AND `AccountEmail` = ?";
		
	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('is', $id, $email);
		$stmt->execute();
		$stmt->bind_result($value);
		if(!$stmt->fetch()) {
			$stmt->close();
			return -1;
		}
		
		$stmt->close();
		return $value;
	}
	else {
		return -1;
	}
}

function addContact($conn, $contact, $spId) {
	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$fname = $contact['First'];
	$lname = $contact['Last'];
	$email = $contact['Email'];
	$jobTitle = $contact['Job'];
	$phone = str_replace(array('-', '+'), '', filter_var($contact['Phone'], FILTER_SANITIZE_NUMBER_INT));
	$extension = str_replace(array('-', '+'), '', filter_var($contact['Extension'], FILTER_SANITIZE_NUMBER_INT));
	$results = success(INSERT_SUCCESS, "A new contact has been added.");
	
	if(strlen($fname) < 1 && strlen($lname) < 1) {
		return error(INVALID_ARGUMENTS, "First or last name is required");
	}
	
	$sql = "INSERT INTO CONTACT " .
		"(`Fname`,`Lname`,`Email`,`JobTitle`,`PhoneNumber`,`Extension`,`Sp_Id`) " .
		"VALUES (?, ?, ?, ?, ?, ?, ?)";

	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('ssssssi', $fname, $lname, $email, $jobTitle, $phone, $extension, $spId);
		
		if(!$stmt->execute()) {
			return error(DUPLICATE_KEY, "The contact you entered already exists for this business.");
		}

		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}

	$id = (int) $conn->insert_id;
	$results['Id'] = $id;
	
	return $results;
}

function addLocation($conn, $location, $spId) {
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
	
	$results = success(INSERT_SUCCESS, "A new location has been added.");
	
	$sql = "INSERT INTO LOCATION " .
		"(`Address1`,`Address2`,`City`,`State`,`Zip`,`Sp_Id`) " .
		"VALUES (?, ?, ?, ?, ?, ?)";

	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('sssssi', $address1, $address2, $city, $state, $zip, $spId);
		
		if(!$stmt->execute()) {
			return error(DUPLICATE_KEY, "The location you entered already exists for this business.");
		}

		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}

	$id = (int) $conn->insert_id;
	$results['Id'] = $id;
	
	return $results;
}

function linkLocationContact($conn, $locationId, $contactId){
	$results = success(INSERT_SUCCESS, "The contact has been linked to a location.");

	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "INSERT INTO LOCATION_TO_CONTACT " .
		"(`C_Id`,`L_Id`) " .
		"VALUES (?, ?)";

	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('ii', $contactId, $locationId);
		
		if(!$stmt->execute()) {
			return error(DUPLICATE_KEY, "The location and contact are already linked.");
		}

		$stmt->close();
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return $results;
}

function linkManyLocationsContact($conn, $locations, $c_id) {
	$results = success(INSERT_SUCCESS, "The contact has been linked to locations.");

	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "DELETE FROM LOCATION_TO_CONTACT WHERE `C_Id` = ?";

	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('i', $c_id);
		$stmt->execute();
		$stmt->close();
		
		foreach($locations as $location) {
			linkLocationContact($conn, $location, $c_id);
		}
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return $results;
}

function linkManyContactsLocation($conn, $contacts, $l_id) {
	$results = success(INSERT_SUCCESS, "The location has been linked to contacts.");

	if ($conn->connect_error) {
		return error(COULD_NOT_CONNECT, COULD_NOT_CONNECT_MESSAGE);
	}
	
	$sql = "DELETE FROM LOCATION_TO_CONTACT WHERE `L_Id` = ?";

	if($stmt = $conn->prepare($sql)) {
		$stmt->bind_param('i', $l_id);
		$stmt->execute();
		$stmt->close();
		
		foreach($contacts as $contact) {
			linkLocationContact($conn, $l_id, $contact);
		}
	}
	else {
		return error(SQL_PREPARE_FAILED, SQL_PREPARE_FAILED_MESSAGE);
	}
	
	return $results;
}
?>